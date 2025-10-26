-- Product Variants Migration
-- Adds support for product variants with attributes, attribute values, and individual variant tracking

-- Create product attributes table (e.g., Color, Size, Material)
CREATE TABLE IF NOT EXISTS `product_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(66) NOT NULL,
  `display_name` varchar(66) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;

-- Create product attribute values table (e.g., Red, Blue, Small, Medium)
CREATE TABLE IF NOT EXISTS `product_attribute_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(11) NOT NULL,
  `value` varchar(66) NOT NULL,
  `display_value` varchar(66) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `attribute_id` (`attribute_id`),
  UNIQUE KEY `attribute_value` (`attribute_id`, `value`),
  CONSTRAINT `fk_attribute_value` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;

-- Create product variants table
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `sku` varchar(256) NOT NULL,
  `barcode` varchar(256) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  UNIQUE KEY `product_sku` (`product_id`, `sku`),
  UNIQUE KEY `barcode` (`barcode`),
  CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`) REFERENCES `stored_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;

-- Create variant attribute combinations table
CREATE TABLE IF NOT EXISTS `variant_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `variant_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `attribute_value_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `variant_attribute` (`variant_id`, `attribute_id`),
  KEY `attribute_id` (`attribute_id`),
  KEY `attribute_value_id` (`attribute_value_id`),
  CONSTRAINT `fk_variant_attr_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_variant_attr_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_variant_attr_value` FOREIGN KEY (`attribute_value_id`) REFERENCES `product_attribute_values` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;

-- Create variant stock levels table (separate from main stock_levels for migration compatibility)
CREATE TABLE IF NOT EXISTS `variant_stock_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `variant_id` int(11) NOT NULL,
  `locationid` int(11) NOT NULL,
  `stocklevel` int(11) NOT NULL,
  `dt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `variant_location` (`variant_id`, `locationid`),
  KEY `locationid` (`locationid`),
  CONSTRAINT `fk_variant_stock_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_variant_stock_location` FOREIGN KEY (`locationid`) REFERENCES `locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;

-- Add variant_id column to sale_items table
ALTER TABLE `sale_items` ADD `variant_id` int(11) DEFAULT NULL AFTER `storeditemid`;
ALTER TABLE `sale_items` ADD KEY `variant_id` (`variant_id`);
ALTER TABLE `sale_items` ADD CONSTRAINT `fk_sale_item_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

-- Add is_variant_parent flag to stored_items table
ALTER TABLE `stored_items` ADD `is_variant_parent` tinyint(1) NOT NULL DEFAULT 0 AFTER `price`;
ALTER TABLE `stored_items` ADD `variant_attributes` varchar(2048) DEFAULT NULL AFTER `is_variant_parent`;

-- Insert some default attributes
INSERT INTO `product_attributes` (`name`, `display_name`, `sort_order`) VALUES
('color', 'Color', 1),
('size', 'Size', 2),
('material', 'Material', 3),
('style', 'Style', 4);
