# Deploy this PHP blog to Vercel and Aiven MySQL

This project stays in PHP. Vercel runs it through the community-maintained
`vercel-php@0.9.0` runtime (PHP 8.5, Node.js 22). Aiven stores users, posts, and
login sessions. No Next.js, npm install, or Composer dependencies are needed.

## 1. Create your free Aiven MySQL service

1. Sign in at <https://console.aiven.io/> and create/select a project.
2. Open **Services → Create service → MySQL**.
3. Select the **Free** tier. Check that the summary says **$0**, rather than a
   paid plan covered by trial credits. Name the service, for example `blog-mysql`.
4. Create it and wait until its status is **Running**. The free tier assigns its
   cloud and region; you do not need to choose them.
5. In **Overview → Connection information** (or **Quick connect**), keep these
   values available: **Host, Port, Database, User, Password**. The initial database
   is normally `defaultdb` and the user normally `avnadmin`; use your actual values.
6. Download the service's **CA certificate**, usually named `ca.pem`.

Do not put the password or an exported database in GitHub. The repository ignores
local configuration, PEM files, and a `backups/` folder. Paste credentials directly
into Vercel's environment-variable settings later.

Reference: [Aiven setup](https://aiven.io/docs/products/mysql/get-started).
The [free tier](https://aiven.io/docs/products/mysql/concepts/mysql-free-tier)
currently has 1 GB disk storage and may be powered off after inactivity.

## 2. Connect from your computer and create the tables

Use [MySQL Workbench](https://dev.mysql.com/downloads/workbench/) as the graphical SQL client.

1. Add a new connection named `Blog Aiven`, using **Standard TCP/IP**.
2. Copy Aiven's host, port, and username into the corresponding fields. Store or
   enter the Aiven password when prompted. Do not assume port `3306`.
3. In the **SSL** tab, select **Require and Verify Identity**, and select the
   downloaded `ca.pem` as **SSL CA File**. Leave client certificate/key empty.
4. Test and open the connection. Use the exact Aiven hostname, not an IP address.
5. In the Schemas panel, double-click `defaultdb` (or your chosen database) so it
   becomes the default schema.
6. For a **new, empty database**, use **File → Open SQL Script** and open the
   repository's `database/schema.sql`. Run the entire script with the lightning
   button. This creates `users`, `posts`, and `sessions`.
7. Refresh Schemas and verify all three tables exist.

If you already imported an old database's structure and data, do **not** run the
full schema again. Run only `database/sessions.sql` to add the session table.
Neither script automatically runs during a Vercel deployment.

[Aiven's Workbench instructions](https://aiven.io/docs/products/mysql/howto/connect-from-mysql-workbench).

## 3. Optional: keep your existing InfinityFree users and posts

Skip this section for a fresh, empty blog. To preserve your current content:

1. Open your InfinityFree hosting control panel, then **MySQL Databases →
   phpMyAdmin** for your existing blog database.
2. Export a full SQL backup and keep it outside Git, for example in `backups/`.
3. For transfer into the new schema from step 2, select **Export → Custom → SQL**,
   select only `users` and `posts`, and export **data only**, with **column names
   in INSERT statements** (complete inserts). Keep IDs and password hashes intact.
   Do not include CREATE DATABASE, USE, DROP TABLE, or CREATE TABLE statements.
4. In Workbench, select the new Aiven database and open the data-only SQL file.
   Inspect it to ensure it targets the selected database, not the old InfinityFree
   database name. Import `users` before `posts` to satisfy the foreign key.
   Run this only against empty destination tables; do not repeatedly import it.
5. Compare counts on the old and new databases:

   ```sql
   SELECT COUNT(*) AS users_count FROM users;
   SELECT COUNT(*) AS posts_count FROM posts;
   ```

Existing passwords continue to work because their hashes are copied. People will
need to sign in again because old hosting sessions are not transferred.
Stop creating/editing content on the old site during the final export and switch,
so changes do not get lost between databases. Keep the old site and backup until
the new deployment has passed the checks below.

## 4. Push the updated project to GitHub

Use your editor's Git/source-control panel to review, commit, and push the changed
project files to your existing repository:
<https://github.com/kdinethdovindu/blog-Web>.

Include `api/`, `vercel.json`, `.vercelignore`, the PHP changes, and SQL scripts.
Do not add `config/.env.php`, passwords, `ca.pem`, database exports, or `tests/.tmp/`.
The old local `config/.env.php` is ignored on Vercel and has not been modified.

## 5. Import the repository into Vercel

1. Sign in at <https://vercel.com/>. For a personal, non-commercial blog, select
   the **Hobby** plan.
2. Choose **Add New → Project**, connect GitHub if necessary, and import `blog-Web`.
3. Set **Framework Preset: Other**.
4. Keep **Root Directory** at the repository root (`./`), **not** `public` or `api`.
5. The committed `vercel.json` supplies these settings: **Build Command: empty**,
   **Install Command: empty**, **Output Directory: `.`**. Do not set an npm command.
6. Set **Node.js Version: 22.x** in project Build and Deployment settings. If it is
   not offered in the import form, set it immediately after creating the project,
   then redeploy. The PHP runtime uses Node for its launcher; your app stays PHP.
7. Leave **Automatically expose System Environment Variables** enabled. The app
   detects Vercel using its automatic `VERCEL=1` variable.
8. Before the production deployment, add the variables below under **Environment
   Variables**, selecting **Production**. If the import already deployed, add them
   under **Project → Settings → Environment Variables** and redeploy afterward.

| Variable | Value |
| --- | --- |
| `DB_HOST` | Aiven Host, without `https://`, a port, or a connection URI |
| `DB_PORT` | Aiven Port, copied exactly |
| `DB_NAME` | `defaultdb`, or the database you imported into |
| `DB_USER` | Aiven User, normally `avnadmin` |
| `DB_PASS` | Aiven Password, with no extra quotes |
| `DB_SSL_CA_CERT` | Entire text of `ca.pem`, including BEGIN/END CERTIFICATE lines |

Open `ca.pem` in a text editor and paste all its text as a multiline value into
`DB_SSL_CA_CERT`. This is the certificate contents, **not** its filename or path.
Do not turn off certificate verification if a connection fails.

**Do not add `BASE_URL`.** Relative URLs automatically work on your Vercel domain
and any later custom domain. Do not copy the old InfinityFree URL. Database
sessions and secure cookies are selected automatically on Vercel, so there is no
need to add `SESSION_DRIVER` either.

Use separate database credentials if you later enable **Preview** deployments;
giving previews the production credentials would let them change real blog data.
Without preview credentials, preview pages requiring the database will return 500.

9. Click **Deploy**, or **Deployments → latest deployment → Redeploy** after any
   settings change. Environment changes take effect only in a new deployment.
10. Open your assigned `https://your-project.vercel.app` address. The home page is
    available at `/`; existing `/public/*.php` URLs also work.

[PHP runtime documentation](https://github.com/vercel-community/php).
[Vercel configuration](https://vercel.com/docs/project-configuration/vercel-json).
[Hobby use rules](https://vercel.com/docs/limits/fair-use-guidelines).

## 6. Check the new live site

1. Open the home page and confirm styling and existing posts appear.
2. Register a test account; verify the redirect succeeds and you stay signed in.
3. Create, view, edit, and delete a test post.
4. Log out and sign in again. If you imported users, test an existing account too.
5. Open `/public/new.php` in a private browser window. It should redirect to login.
6. Request `/config/.env.php`, `/config/db.php`, `/database/schema.sql`, and
   `/api/php.ini`. Each must return **404**, without any source code or credentials.
7. Verify that the database contains session rows after visiting the site.

The app uses one PHP function, allows only known routes/assets, and stores sessions
in MySQL with a two-hour inactivity expiry and probabilistic expired-row cleanup.
The CA file alone is written to temporary storage; session data is not stored there.

## Troubleshooting

| Problem | Check |
| --- | --- |
| Build fails before PHP starts | Framework Other, Node 22.x, repository root, committed `api/index.php` and `vercel.json`; inspect Build Logs. |
| 500 / site temporarily unavailable | Inspect Vercel runtime logs. Check all six variables and that you redeployed after adding them. |
| `DB_SSL_CA_CERT is required` | Paste the complete certificate text into the Production environment, then redeploy. |
| `PDOException` code `HY000` / connection failure | Aiven service is Running; exact host/port/user/password; current certificate; service IP restrictions permit Vercel connections. |
| Certificate error | Re-download the CA from this Aiven service; use its exact hostname, and keep verification enabled. |
| `PDOException` code `42S02` | A table is missing: check `DB_NAME`, then run the appropriate schema/session SQL. |
| Login does not persist | Confirm `sessions` exists and the app user can SELECT, INSERT, UPDATE, DELETE and use MySQL GET_LOCK/RELEASE_LOCK. |
| Links go to InfinityFree | Remove `BASE_URL` from Vercel environment variables and redeploy. |
| Preview fails, production works | Preview does not have database credentials; use a separate test database to enable it. |

## Local verification

The integration test uses XAMPP PHP and an isolated temporary MariaDB instance,
never the configured InfinityFree or Aiven database:

```powershell
node tests/deployment-smoke.mjs
```

Override `PHP_BIN` and `MYSQL_BIN_DIR` if XAMPP is installed elsewhere. Test files
and logs stay in the ignored `tests/.tmp/` directory. The test stops its processes.
It covers source-file blocking, redirects, registration/login/logout, sessions
shared by two PHP processes, session expiry and replay protection, CSRF, post
ownership, CRUD, and the requirement for a CA certificate on Vercel. A live Vercel
deployment and a real Aiven TLS connection still need the manual checks above.
