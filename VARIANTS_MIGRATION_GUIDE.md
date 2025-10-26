# Product Variants Migration Guide

## Overview

This guide explains how to migrate your FreePOS installation to support Product Variants. The variant system allows you to sell multiple variations of the same product (e.g., T-Shirt in different colors and sizes), each with its own SKU, barcode, price, cost, and stock level.

## Features

- **Product Variants**: Each product can have multiple sellable variants
- **Attributes System**: Define attributes (Color, Size, etc.) and their values
- **Individual Stock Tracking**: Each variant has its own stock level per location
- **Barcode/SKU per Variant**: Each variant can have unique barcode and SKU
- **Pricing Flexibility**: Different price and cost for each variant
- **Backward Compatible**: Existing products automatically get a default variant
- **POS Integration**: Barcode scanning and variant picker for multi-variant products
- **Reporting**: Variant-level detail and product-level rollups

## Database Schema Changes

### New Tables

1. **attributes** - Stores attribute definitions (e.g., Color, Size)
2. **attribute_values** - Stores attribute values (e.g., Red, Blue, Small, Large)
3. **product_variants** - Stores product variants with SKU, price, cost, etc.
4. **product_variant_attribute_values** - Links variants to their attribute values

### Modified Tables

The following existing tables have been extended with a `variant_id` column:

1. **stock_levels** - Now tracks stock per variant (nullable for backward compatibility)
2. **stock_history** - Records stock movements per variant (nullable)
3. **sale_items** - Links sale line items to variants (nullable)

## Migration Steps

### Step 1: Backup Your Database

**CRITICAL**: Before proceeding, create a complete backup of your database.

```bash
mysqldump -u your_username -p your_database_name > freepos_backup_$(date +%Y%m%d).sql
```

### Step 2: Run Schema Migration

Execute the schema migration SQL to create new tables and modify existing ones:

```bash
mysql -u your_username -p your_database_name < database/schemas/variants_migration.sql
```

This will:
- Create the 4 new tables (attributes, attribute_values, product_variants, product_variant_attribute_values)
- Add `variant_id` column to stock_levels, stock_history, and sale_items
- Create necessary indexes and foreign keys

### Step 3: Run Data Migration

Execute the data migration SQL to create default variants for all existing products:

```bash
mysql -u your_username -p your_database_name < database/schemas/variants_data_migration.sql
```

This will:
- Create one default variant for each existing product
- Copy the product's SKU, price, and cost to the default variant
- Link all existing stock to the default variants
- Link all historical sales to the default variants

### Step 4: Verify Migration

Run these verification queries to ensure the migration was successful:

```sql
-- All products should have at least one default variant
SELECT COUNT(*) AS products_without_default_variant
FROM stored_items si
WHERE NOT EXISTS (
  SELECT 1 FROM product_variants pv 
  WHERE pv.product_id = si.id AND pv.is_default = 1
);
-- Result should be 0

-- All stock should be assigned to variants
SELECT COUNT(*) AS stock_without_variant
FROM stock_levels
WHERE variant_id IS NULL;
-- Result should be 0

-- Summary of products and variants
SELECT 
  si.id, 
  si.name, 
  COUNT(pv.id) AS variant_count,
  SUM(pv.is_default) AS default_variants
FROM stored_items si
LEFT JOIN product_variants pv ON si.id = pv.product_id
GROUP BY si.id, si.name
ORDER BY variant_count DESC
LIMIT 20;
```

### Step 5: Update File Permissions (if needed)

Ensure the web server has read access to the new model files:

```bash
chown -R www-data:www-data app/Database/ProductVariantsModel.php
chown -R www-data:www-data app/Database/AttributesModel.php
chown -R www-data:www-data app/Utility/VariantsHelper.php
chown -R www-data:www-data app/Controllers/Admin/AdminVariants.php
```

### Step 6: Clear Any Caches

If you're using PHP opcode caching or application caching:

```bash
# Clear PHP opcache (if using)
service php7.4-fpm reload

# Clear application cache if applicable
rm -rf cache/*
```

## Using the Variant System

### Creating Attributes

Before creating variants, define your attributes:

1. Go to Admin → Products → Attributes
2. Create an attribute (e.g., "Color")
3. Add values to the attribute (e.g., "Red", "Blue", "Green")
4. Repeat for other attributes (e.g., "Size" with "Small", "Medium", "Large")

### Creating Variants

There are two ways to create variants:

#### Method 1: Generate from Attributes

1. Edit a product in Admin → Products
2. Scroll to the "Variants" section
3. Select attributes (e.g., Color and Size)
4. Click "Generate Variants"
5. The system will create all combinations (e.g., Red/Small, Red/Medium, Red/Large, etc.)

#### Method 2: Add Manually

1. Edit a product in Admin → Products
2. Scroll to the "Variants" section
3. Click "Add Variant Manually"
4. Enter SKU, price, cost, and other details
5. Save the variant

### Managing Stock

Stock is now tracked per variant:

1. Go to Admin → Stock
2. View stock levels grouped by variant
3. Update stock for each variant individually
4. Stock history shows movements per variant

### POS Usage

#### Barcode Scanning

When you scan a barcode at the POS:
- If the barcode matches a variant SKU, that variant is added to the cart
- If the barcode matches a product code and the product has only one variant, that variant is added
- If the product has multiple variants, a variant picker modal appears

#### Variant Picker

The variant picker shows:
- Product name
- List of all active variants
- Attribute chips for each variant (e.g., "Red" / "Large")
- Stock level for each variant at current location
- Price for each variant
- Select a variant to add it to the cart

#### Receipt Display

Receipts now show:
- Product name with variant suffix (e.g., "T-Shirt - Red / Large")
- Variant SKU
- Price and quantity

## Backward Compatibility

The system is fully backward compatible:

1. **Existing Products**: All existing products automatically get a default variant during migration
2. **Old Code**: Legacy code that doesn't specify variant_id will use the default variant
3. **Stock Tracking**: Stock can still be queried by product_id (returns sum across all variants)
4. **Single-Variant Products**: If a product has only one variant, the variant suffix is hidden in POS

## Rollback Instructions

If you need to rollback the migration:

```sql
-- Remove variant columns from existing tables
ALTER TABLE `sale_items` DROP FOREIGN KEY `fk_sale_items_variant`;
ALTER TABLE `sale_items` DROP COLUMN `variant_id`;

ALTER TABLE `stock_history` DROP FOREIGN KEY `fk_stock_history_variant`;
ALTER TABLE `stock_history` DROP COLUMN `variant_id`;

ALTER TABLE `stock_levels` DROP FOREIGN KEY `fk_stock_levels_variant`;
ALTER TABLE `stock_levels` DROP COLUMN `variant_id`;

-- Drop new tables
DROP TABLE IF EXISTS `product_variant_attribute_values`;
DROP TABLE IF EXISTS `product_variants`;
DROP TABLE IF EXISTS `attribute_values`;
DROP TABLE IF EXISTS `attributes`;
```

Then restore your database backup.

## Performance Considerations

1. **Indexes**: The migration creates appropriate indexes for variant lookups
2. **Stock Queries**: Queries filtering by variant_id are optimized with composite indexes
3. **Large Catalogs**: If you have thousands of variants, consider periodic index optimization

## Troubleshooting

### Issue: Variants not appearing in POS

**Solution**: 
- Check that variants are marked as active (`is_active = 1`)
- Verify the POS is loading the latest data (refresh browser)
- Check browser console for JavaScript errors

### Issue: Stock not decrementing for variants

**Solution**:
- Verify the sale_items table has variant_id populated
- Check that stock_levels has records with matching variant_id
- Review stock_history for the variant to see if movements are recorded

### Issue: Duplicate SKU errors

**Solution**:
- SKUs must be unique across all variants
- When generating variants, the system auto-generates SKUs from product code + attribute codes
- If needed, manually edit variant SKUs to make them unique

## Support

For issues or questions:
1. Check the GitHub repository issues
2. Review the code comments in the model files
3. Contact the FreePOS community

## Version Information

- **Feature Version**: 1.5.0
- **Database Schema Version**: 1.5.0
- **Migration Date**: 2025-10-26
- **Minimum FreePOS Version**: 1.4.3
