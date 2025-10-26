-- ========================================
-- Product Variants Migration Schema
-- Version: 1.5.0
-- Date: 2025-10-26
-- ========================================
-- This migration adds support for product variants (QB POS style)
-- Each variant has its own SKU, barcode, price, cost, and stock
-- Backward compatible with existing products
-- ========================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ========================================
-- 1. ATTRIBUTES SYSTEM
-- ========================================

-- Attribute definitions (e.g., Color, Size, Material)
CREATE TABLE IF NOT EXISTS `attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(66) NOT NULL,
  `code` varchar(32) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `dt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Attribute values (e.g., Red, Blue, Small, Large)
CREATE TABLE IF NOT EXISTS `attribute_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(11) NOT NULL,
  `value` varchar(66) NOT NULL,
  `code` varchar(32) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `dt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attribute_value` (`attribute_id`, `value`),
  KEY `attribute_id` (`attribute_id`),
  CONSTRAINT `fk_attribute_values_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ========================================
-- 2. PRODUCT VARIANTS
-- ========================================

-- Product variants table
-- Each variant represents a sellable SKU under a parent product
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL COMMENT 'FK to stored_items (parent product)',
  `sku` varchar(128) NOT NULL COMMENT 'Unique SKU for this variant',
  `barcode` varchar(256) NULL COMMENT 'Primary barcode (can be null if using barcodes table)',
  `name_suffix` varchar(128) NOT NULL DEFAULT '' COMMENT 'e.g., "Red / Large"',
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 for auto-created default variant',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `data` varchar(2048) NOT NULL DEFAULT '{}' COMMENT 'Additional JSON data',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `product_id` (`product_id`),
  KEY `is_active` (`is_active`),
  KEY `is_default` (`is_default`),
  CONSTRAINT `fk_product_variants_product` FOREIGN KEY (`product_id`) REFERENCES `stored_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Junction table linking variants to attribute values
CREATE TABLE IF NOT EXISTS `product_variant_attribute_values` (
  `variant_id` int(11) NOT NULL,
  `attribute_value_id` int(11) NOT NULL,
  PRIMARY KEY (`variant_id`, `attribute_value_id`),
  KEY `attribute_value_id` (`attribute_value_id`),
  CONSTRAINT `fk_pvav_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pvav_attribute_value` FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ========================================
-- 3. EXTEND STOCK TABLES FOR VARIANTS
-- ========================================

-- Add variant_id to stock_levels (nullable for backward compatibility)
ALTER TABLE `stock_levels` 
  ADD COLUMN `variant_id` int(11) NULL AFTER `storeditemid`,
  ADD KEY `variant_id` (`variant_id`),
  ADD CONSTRAINT `fk_stock_levels_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

-- Add variant_id to stock_history (nullable for backward compatibility)
ALTER TABLE `stock_history` 
  ADD COLUMN `variant_id` int(11) NULL AFTER `storeditemid`,
  ADD KEY `variant_id` (`variant_id`),
  ADD CONSTRAINT `fk_stock_history_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

-- Add variant_id to sale_items (nullable for backward compatibility)
ALTER TABLE `sale_items` 
  ADD COLUMN `variant_id` int(11) NULL AFTER `storeditemid`,
  ADD KEY `variant_id` (`variant_id`),
  ADD CONSTRAINT `fk_sale_items_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

-- ========================================
-- 4. INDEX OPTIMIZATION
-- ========================================

-- Composite index for common queries
CREATE INDEX `idx_stock_variant_location` ON `stock_levels` (`variant_id`, `locationid`);
CREATE INDEX `idx_stock_item_location` ON `stock_levels` (`storeditemid`, `locationid`);

-- ========================================
-- ROLLBACK INSTRUCTIONS
-- ========================================
-- To rollback this migration, execute the following in reverse order:
--
-- ALTER TABLE `sale_items` DROP FOREIGN KEY `fk_sale_items_variant`, DROP COLUMN `variant_id`;
-- ALTER TABLE `stock_history` DROP FOREIGN KEY `fk_stock_history_variant`, DROP COLUMN `variant_id`;
-- ALTER TABLE `stock_levels` DROP FOREIGN KEY `fk_stock_levels_variant`, DROP COLUMN `variant_id`;
-- DROP TABLE IF EXISTS `product_variant_attribute_values`;
-- DROP TABLE IF EXISTS `product_variants`;
-- DROP TABLE IF EXISTS `attribute_values`;
-- DROP TABLE IF EXISTS `attributes`;
-- ========================================
