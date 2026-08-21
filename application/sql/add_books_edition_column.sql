-- Adds optional book edition (e.g. "3rd", "2024") for library catalog.
-- Run once against your application database.

ALTER TABLE `books`
  ADD COLUMN `edition` VARCHAR(150) NULL DEFAULT NULL COMMENT 'Book edition' AFTER `author`;
