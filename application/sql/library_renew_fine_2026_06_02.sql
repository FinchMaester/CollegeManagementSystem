-- Library renewal & fine columns for book_issues (also auto-applied on first use).
ALTER TABLE `book_issues`
  ADD COLUMN IF NOT EXISTS `renew_count` INT NOT NULL DEFAULT 0 AFTER `is_returned`,
  ADD COLUMN IF NOT EXISTS `fine_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `renew_count`,
  ADD COLUMN IF NOT EXISTS `fine_paid` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `fine_amount`,
  ADD COLUMN IF NOT EXISTS `last_renewed_at` DATETIME NULL DEFAULT NULL AFTER `fine_paid`;
