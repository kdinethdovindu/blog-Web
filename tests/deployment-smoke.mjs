// Uses only an isolated local database; never reads production credentials.
// Windows example: node tests/deployment-smoke.mjs
// Override PHP_BIN and MYSQL_BIN_DIR if XAMPP is installed elsewhere.
import assert from 'node:assert/strict';
import { spawn, execFileSync } from 'node:child_process';
import { mkdirSync, mkdtempSync, openSync, closeSync } from 'node:fs';
import path from 'node:path';
import net from 'node:net';

const root = process.cwd();
const php = process.env.PHP_BIN || 'C:/xampp/php/php.exe';
const mysqlBin = process.env.MYSQL_BIN_DIR || 'C:/xampp/mysql/bin';
mkdirSync('tests/.tmp', { recursive: true });
const temp = mkdtempSync(path.join(root, 'tests/.tmp/run-'));
const processes = [];
const logs = [];
const delay = ms => new Promise(resolve => setTimeout(resolve, ms));
async function freePort() {
  const server = net.createServer();
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  const port = server.address().port;
  await new Promise(resolve => server.close(resolve));
  return port;
}
async function waitPort(port, child) {
  let lastError = '';
  for (let i = 0; i < 100; i++) {
    if (child.exitCode !== null) throw new Error(`Process exited; see logs in ${temp}`);
    const ready = await new Promise(resolve => {
      const socket = net.connect(port, '127.0.0.1');
      socket.once('connect', () => { socket.destroy(); resolve(true); });
      socket.once('error', error => { lastError = error.code; resolve(false); });
    });
    if (ready) return;
    await delay(100);
  }
  throw new Error(`Server unavailable (${lastError}); see ${temp}`);
}
function launch(command, args, env, name) {
  const log = openSync(path.join(temp, name + '.log'), 'a');
  logs.push(log);
  const child = spawn(command, args, { cwd: root, env, windowsHide: true, stdio: ['ignore', log, log] });
  processes.push(child);
  return child;
}
function sql(query, port) {
  return execFileSync(path.join(mysqlBin, 'mysql.exe'), [
    '--protocol=TCP', '--host=127.0.0.1', `--port=${port}`, '--user=root',
    '--batch', '--skip-column-names', '--execute', query,
  ], { encoding: 'utf8', windowsHide: true }).trim();
}
class Browser {
  cookie = '';
  constructor(base) { this.base = base; }
  async request(route, data) {
    const headers = this.cookie ? { Cookie: this.cookie } : {};
    const response = await fetch(this.base + route, {
      method: data ? 'POST' : 'GET', headers, redirect: 'manual',
      body: data ? new URLSearchParams(data) : undefined,
    });
    const cookie = response.headers.get('set-cookie');
    if (cookie) this.cookie = cookie.split(';')[0];
    return { status: response.status, headers: response.headers, text: await response.text() };
  }
  token(response) {
    const token = response.text.match(/name="csrf" value="([^"]+)"/);
    assert.ok(token, 'CSRF token is present');
    return token[1];
  }
}

try {
  const dbPort = await freePort();
  const data = path.join(temp, 'mysql');
  execFileSync(path.join(mysqlBin, 'mysql_install_db.exe'), [
    `--datadir=${data}`, `--port=${dbPort}`,
  ], { windowsHide: true, stdio: 'pipe' });
  const database = launch(path.join(mysqlBin, 'mysqld.exe'), [
    '--no-defaults', `--basedir=${path.dirname(mysqlBin)}`, `--datadir=${data}`,
    `--port=${dbPort}`, '--bind-address=127.0.0.1', '--console',
  ], process.env, 'mysql');
  await waitPort(dbPort, database);
  sql('CREATE DATABASE blog_vercel_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', dbPort);
  const env = { ...process.env, VERCEL: '0', DB_HOST: '127.0.0.1', DB_PORT: String(dbPort),
    DB_NAME: 'blog_vercel_test', DB_USER: 'root', DB_PASS: '', DB_SSL_CA: '',
    DB_SSL_CA_CERT: '', BASE_URL: '', SESSION_DRIVER: 'database' };
  execFileSync(php, ['-r',
    'require "config/db.php"; pdo()->exec(file_get_contents("database/schema.sql")); pdo()->exec(file_get_contents("database/sessions.sql"));',
  ], { env, windowsHide: true });
  const portA = await freePort();
  const portB = await freePort();
  const serverA = launch(php, ['-d', 'display_errors=0', '-d', 'output_buffering=0', '-S', `127.0.0.1:${portA}`, 'api/index.php'], env, 'php-a');
  const serverB = launch(php, ['-d', 'display_errors=0', '-d', 'output_buffering=0', '-S', `127.0.0.1:${portB}`, 'api/index.php'], env, 'php-b');
  await Promise.all([waitPort(portA, serverA), waitPort(portB, serverB)]);
  const user = new Browser(`http://127.0.0.1:${portA}`);
  assert.equal((await user.request('/')).status, 200);
  assert.match((await user.request('/assets/css/app.css')).headers.get('content-type'), /text\/css/);
  for (const route of ['/config/.env.php', '/config/db.php', '/includes/init.php', '/database/schema.sql', '/api/php.ini', '/README.md', '/.git/config', '/api/index.php']) {
    assert.equal((await user.request(route)).status, 404, route);
  }
  assert.equal((await user.request('/public/new.php')).status, 302);
  const registration = await user.request('/public/register.php');
  const originalCookie = user.cookie;
  const registered = await user.request('/public/register.php', {
    csrf: user.token(registration), username: 'test_author', email: 'author@example.test',
    password: 'Smoke-test-password-123', confirm: 'Smoke-test-password-123',
  });
  assert.equal(registered.status, 302);
  assert.notEqual(user.cookie, originalCookie, 'Registration rotates the session ID');
  assert.match(registered.headers.get('set-cookie'), /HttpOnly/i);
  assert.match(registered.headers.get('set-cookie'), /SameSite=Lax/i);
  // A separate PHP process must see the same authenticated session.
  user.base = `http://127.0.0.1:${portB}`;
  const mine = await user.request('/public/mine.php');
  assert.equal(mine.status, 200);
  assert.match(mine.text, /Hi, test_author/);
  assert.match(mine.text, /Registration successful/);
  console.log('PASS: private paths blocked, registration, redirects, cross-process sessions');

  const create = await user.request('/public/new.php');
  assert.equal((await user.request('/public/new.php', { csrf: 'invalid', title: 'Test', content: 'Should never be saved' })).status, 400);
  const created = await user.request('/public/new.php', { csrf: user.token(create), title: 'Migration test', content: 'Content survives the migration.' });
  assert.equal(created.status, 302);
  const postUrl = created.headers.get('location');
  assert.match((await user.request(postUrl)).text, /Content survives the migration/);
  const postId = sql('SELECT id FROM blog_vercel_test.posts LIMIT 1', dbPort);
  const edited = await user.request(`/public/edit.php?id=${postId}`, { csrf: user.token(create), title: 'Updated migration test', content: 'Updated content for this test.' });
  assert.equal(edited.status, 302);
  assert.match((await user.request(postUrl)).text, /Updated content for this test/);
  assert.equal((await user.request(`/public/delete.php?id=${postId}`)).status, 405);

  const stranger = new Browser(user.base);
  const form = await stranger.request('/public/register.php');
  await stranger.request('/public/register.php', { csrf: stranger.token(form), username: 'other_user',
    email: 'other@example.test', password: 'Smoke-test-password-123', confirm: 'Smoke-test-password-123' });
  assert.equal((await stranger.request(`/public/edit.php?id=${postId}`)).status, 403);
  const otherHome = await stranger.request('/');
  assert.equal((await stranger.request('/public/delete.php', { csrf: stranger.token(otherHome), id: postId })).status, 403);
  assert.equal((await user.request('/public/delete.php', { csrf: user.token(create), id: postId })).status, 302);
  assert.equal((await user.request(postUrl)).status, 404);
  console.log('PASS: create/read/edit/delete, CSRF rejection, ownership enforcement');

  const oldCookie = user.cookie;
  assert.equal((await user.request('/public/logout.php', { csrf: user.token(create) })).status, 302);
  const replay = new Browser(user.base);
  replay.cookie = oldCookie;
  assert.equal((await replay.request('/public/mine.php')).status, 302, 'Old session cannot be replayed after logout');
  const login = await user.request('/public/login.php');
  assert.equal((await user.request('/public/login.php', { csrf: user.token(login), identity: 'author@example.test', password: 'Smoke-test-password-123' })).status, 302);
  const sessionHash = sql('SELECT id FROM blog_vercel_test.sessions WHERE data LIKE \'%test_author%\' LIMIT 1', dbPort);
  assert.ok(sessionHash);
  sql(`UPDATE blog_vercel_test.sessions SET expires_at = 1 WHERE id = '${sessionHash}'`, dbPort);
  assert.equal((await user.request('/public/mine.php')).status, 302, 'Expired sessions cannot authenticate');
  const fixation = new Browser(user.base);
  fixation.cookie = 'PHPSESSID=aaaaaaaaaaaaaaaaaaaaaaaaaa';
  await fixation.request('/public/login.php');
  assert.notEqual(fixation.cookie, 'PHPSESSID=aaaaaaaaaaaaaaaaaaaaaaaaaa');
  const requiredTls = execFileSync(php, ['-r',
    'require "config/db.php"; try { pdo(); exit(1); } catch (RuntimeException $e) { echo $e->getMessage(); }',
  ], { env: { ...env, VERCEL: '1' }, encoding: 'utf8', windowsHide: true });
  assert.match(requiredTls, /DB_SSL_CA_CERT is required/);
  console.log('PASS: login/logout, session replay/expiry/fixation, mandatory TLS on Vercel');
  console.log('All deployment smoke checks passed. Local test logs: ' + temp);
} finally {
  for (const child of processes.reverse()) {
    if (child.exitCode === null) {
      const exited = new Promise(resolve => child.once('exit', resolve));
      child.kill();
      await exited;
    }
  }
  for (const log of logs) closeSync(log);
}
