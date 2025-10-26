-- ========================================
-- Product Variants Data Migration
-- Version: 1.5.0
-- Date: 2025-10-26
-- ========================================
-- This script migrates existing products to the variant system
-- Creates a default variant for each existing product
-- Migrates stock levels and sale history to variants
-- ========================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ========================================
-- STEP 1: Create default variants for all existing products
-- ========================================
-- For each product in stored_items, create one default variant
-- that inherits the product's code, name, and price

INSERT INTO `product_variants` 
  (`product_id`, `sku`, `barcode`, `name_suffix`, `price`, `cost`, `is_default`, `is_active`, `sort_order`, `data`, `created_at`)
SELECT 
  si.id AS product_id,
  si.code AS sku,
  si.code AS barcode,
  '' AS name_suffix,
  CAST(si.price AS DECIMAL(12,2)) AS price,
  COALESCE(
    CAST(JSON_UNQUOTE(JSON_EXTRACT(si.data, '$.cost')) AS DECIMAL(12,2)),
    0.00
  ) AS cost,
  1 AS is_default,
  1 AS is_active,
  0 AS sort_order,
  si.data AS data,
  NOW() AS created_at
FROM stored_items si
WHERE NOT EXISTS (
  -- Don't create duplicate default variants if migration is run multiple times
  SELECT 1 FROM product_variants pv 
  WHERE pv.product_id = si.id AND pv.is_default = 1
);

-- ========================================
-- STEP 2: Migrate stock levels to variants
-- ========================================
-- Update stock_levels to reference the default variant for each product

UPDATE stock_levels sl
INNER JOIN product_variants pv 
  ON sl.storeditemid = pv.product_id 
  AND pv.is_default = 1
SET sl.variant_id = pv.id
WHERE sl.variant_id IS NULL;

-- ========================================
-- STEP 3: Migrate stock history to variants
-- ========================================
-- Update stock_history to reference the default variant for each product

UPDATE stock_history sh
INNER JOIN product_variants pv 
  ON sh.storeditemid = pv.product_id 
  AND pv.is_default = 1
SET sh.variant_id = pv.id
WHERE sh.variant_id IS NULL;

-- ========================================
-- STEP 4: Migrate sale items to variants
-- ========================================
-- Update sale_items to reference the default variant for each product
-- This allows historical sales to be tracked by variant

UPDATE sale_items si
INNER JOIN product_variants pv 
  ON si.storeditemid = pv.product_id 
  AND pv.is_default = 1
SET si.variant_id = pv.id
WHERE si.variant_id IS NULL;

-- ========================================
-- VERIFICATION QUERIES
-- ========================================
-- Run these to verify the migration was successful:

-- Count products without default variants (should be 0)
-- SELECT COUNT(*) AS products_without_default_variant
-- FROM stored_items si
-- WHERE NOT EXISTS (
--   SELECT 1 FROM product_variants pv 
--   WHERE pv.product_id = si.id AND pv.is_default = 1
-- );

-- Count stock levels without variant assignment (should be 0)
-- SELECT COUNT(*) AS stock_without_variant
-- FROM stock_levels
-- WHERE variant_id IS NULL;

-- Count sale items without variant assignment
-- SELECT COUNT(*) AS sales_without_variant
-- FROM sale_items
-- WHERE variant_id IS NULL;

-- Summary: Products and their variants
-- SELECT 
--   si.id, 
--   si.name, 
--   COUNT(pv.id) AS variant_count,
--   SUM(pv.is_default) AS default_variants
-- FROM stored_items si
-- LEFT JOIN product_variants pv ON si.id = pv.product_id
-- GROUP BY si.id, si.name
-- ORDER BY variant_count DESC;

-- ========================================
-- NOTES
-- ========================================
-- After this migration:
-- 1. Every product has at least one default variant
-- 2. All stock is now tracked by variant_id
-- 3. Historical sales are linked to default variants
-- 4. New variants can be added through the admin UI
-- 5. The system is backward compatible - old code can still work with storeditemid
-- ========================================
