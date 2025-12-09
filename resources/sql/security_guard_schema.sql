-- ==========================================
-- Security Guard MySQL Schema
-- Compatible with PDO / DBAL Drivers
-- ==========================================

-- ------------------------------------------
-- Table: sg_attempts
-- ------------------------------------------

CREATE TABLE IF NOT EXISTS `sg_attempts` (
                                             `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                             `ip` VARCHAR(45) NOT NULL,
                                             `subject` VARCHAR(190) NOT NULL,
                                             `occurred_at` INT UNSIGNED NOT NULL, -- UNIX TIMESTAMP
                                             PRIMARY KEY (`id`),

                                             INDEX `idx_attempt_ip_subject` (`ip`, `subject`),
                                             INDEX `idx_attempt_time` (`occurred_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------
-- Table: sg_blocks
-- ------------------------------------------

CREATE TABLE IF NOT EXISTS `sg_blocks` (
                                           `ip` VARCHAR(45) NOT NULL,
                                           `subject` VARCHAR(190) NOT NULL,
                                           `type` VARCHAR(20) NOT NULL,          -- Enum string
                                           `expires_at` INT UNSIGNED NOT NULL,   -- 0 = permanent block
                                           `created_at` INT UNSIGNED NOT NULL,   -- UNIX timestamp of block creation

                                           PRIMARY KEY (`ip`, `subject`),

                                           INDEX `idx_block_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

