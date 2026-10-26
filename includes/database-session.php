<?php

// Shared storage and per-session locks keep parallel serverless requests safe.
final class DatabaseSessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    private PDO $db;
    private int $lifetime;
    private ?string $lock = null;

    public function __construct(PDO $db, int $lifetime = 7200)
    {
        $this->db = $db;
        $this->lifetime = $lifetime;
    }

    private function key(string $id): string
    {
        return hash('sha256', $id);
    }

    private function acquire(string $id): void
    {
        $key = $this->key($id);
        if ($this->lock === $key) {
            return;
        }
        $this->close();
        $query = $this->db->prepare('SELECT GET_LOCK(?, 5)');
        $query->execute([$key]);
        if ((int)$query->fetchColumn() !== 1) {
            throw new RuntimeException('Session is busy. Retry the request.');
        }
        $this->lock = $key;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        if ($this->lock !== null) {
            $query = $this->db->prepare('SELECT RELEASE_LOCK(?)');
            $query->execute([$this->lock]);
            $this->lock = null;
        }
        return true;
    }

    public function validateId(string $id): bool
    {
        $this->acquire($id);
        $query = $this->db->prepare('SELECT 1 FROM sessions WHERE id = ? AND expires_at > ?');
        $query->execute([$this->key($id), time()]);
        return (bool)$query->fetchColumn();
    }

    public function read(string $id): string|false
    {
        $this->acquire($id);
        $query = $this->db->prepare('SELECT data FROM sessions WHERE id = ? AND expires_at > ?');
        $query->execute([$this->key($id), time()]);
        $data = $query->fetchColumn();
        return $data === false ? '' : (string)$data;
    }

    public function write(string $id, string $data): bool
    {
        $this->acquire($id);
        $query = $this->db->prepare(
            'INSERT INTO sessions (id, data, expires_at) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE data = ?, expires_at = ?'
        );
        $expires = time() + $this->lifetime;
        return $query->execute([$this->key($id), $data, $expires, $data, $expires]);
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        return $this->write($id, $data);
    }

    public function destroy(string $id): bool
    {
        $this->acquire($id);
        $query = $this->db->prepare('DELETE FROM sessions WHERE id = ?');
        $result = $query->execute([$this->key($id)]);
        $this->close();
        return $result;
    }

    public function gc(int $max_lifetime): int|false
    {
        $query = $this->db->prepare('DELETE FROM sessions WHERE expires_at <= ? LIMIT 1000');
        $query->execute([time()]);
        return $query->rowCount();
    }
}
