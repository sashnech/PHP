
CREATE DATABASE IF NOT EXISTS lab4 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lab4;

CREATE TABLE IF NOT EXISTS topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL,
    is_pinned TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lab7_pinned_cache_state (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    revision BIGINT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;
INSERT IGNORE INTO lab7_pinned_cache_state (id, revision) VALUES (1, 0);


DROP TRIGGER IF EXISTS lab7_topics_pinned_ai;
CREATE TRIGGER lab7_topics_pinned_ai AFTER INSERT ON topics
FOR EACH ROW
    UPDATE lab7_pinned_cache_state
    SET revision = revision + IF(NEW.is_pinned = 1, 1, 0)
    WHERE id = 1;

DROP TRIGGER IF EXISTS lab7_topics_pinned_ad;
CREATE TRIGGER lab7_topics_pinned_ad AFTER DELETE ON topics
FOR EACH ROW
    UPDATE lab7_pinned_cache_state
    SET revision = revision + IF(OLD.is_pinned = 1, 1, 0)
    WHERE id = 1;

DROP TRIGGER IF EXISTS lab7_topics_pinned_au;
CREATE TRIGGER lab7_topics_pinned_au AFTER UPDATE ON topics
FOR EACH ROW
    UPDATE lab7_pinned_cache_state
    SET revision = revision + IF(
        OLD.is_pinned <> NEW.is_pinned
        OR (NEW.is_pinned = 1 AND (
            OLD.title <> NEW.title
            OR OLD.author <> NEW.author
            OR OLD.created_at <> NEW.created_at
        )),
        1, 0
    )
    WHERE id = 1;
