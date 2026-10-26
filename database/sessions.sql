-- Run once for an existing database. Safe to run again; does not touch posts/users.
CREATE TABLE IF NOT EXISTS sessions (
    id CHAR(64) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
    data MEDIUMBLOB NOT NULL,
    expires_at BIGINT UNSIGNED NOT NULL,
    INDEX sessions_expiry (expires_at)
) ENGINE=InnoDB;
