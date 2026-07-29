DROP DATABASE IF EXISTS example_db;
CREATE DATABASE example_db;
USE example_db;

DROP TABLE IF EXISTS bbs_entries;
CREATE TABLE `bbs_entries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `body` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `image_filename` TEXT DEFAULT NULL,
    `reply_to` INT UNSIGNED DEFAULT NULL,
    FOREIGN KEY (`reply_to`) REFERENCES `bbs_entries`(`id`) ON DELETE SET NULL
);
