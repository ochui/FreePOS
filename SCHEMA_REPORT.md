# Product Variants - Schema Report

## Executive Summary

This document details the database schema changes made to FreePOS to support Product Variants. The implementation follows the QuickBooks POS model where each sellable variant has its own SKU, barcode, price, cost, and stock tracking.

**Date**: 2025-10-26  
**Version**: 1.5.0  
**Schema Changes**: 4 new tables, 3 existing tables extended

---

## Existing Schema Analysis

### Tables That Existed Before Variants

#### 1. `stored_items` (Products Table)
**Purpose**: Main products/items table  
**Key Columns**:
- `id` - Primary key
- `data` - JSON field for extended attributes
- `supplierid` - Foreign key to suppliers
- `categoryid` - Foreign key to categories
- `code` - Product code (also used as barcode/SKU)
- `name` - Product name
- `price` - Base price

**Observation**: This table serves as the PARENT product. The `code` field was the only identifier for scanning/lookup. No support for multiple SKUs per product.

#### 2. `stock_levels` (Stock Tracking)
**Purpose**: Track stock quantity per item per location  
**Key Columns**:
- `id` - Primary key
- `storeditemid` - Foreign key to stored_items
- `locationid` - Foreign key to locations
- `stocklevel` - Current quantity on hand
- `dt` - Last updated timestamp

**Observation**: Stock tracked only at product level, not variant level. No way to track different stock for different colors/sizes of same product.

#### 3. `stock_history` (Movement Tracking)
**Purpose**: Record all stock movements  
**Key Columns**:
- `id` - Primary key
- `storeditemid` - Foreign key to stored_items
- `locationid` - Foreign key to locations
- `auxid` - Auxiliary ID (e.g., sale_id for sale movements)
- `auxdir` - Direction (0 or 1)
- `type` - Movement type (sale, purchase, adjustment, etc.)
- `amount` - Quantity moved
- `dt` - Movement timestamp

**Observation**: All movements tied to product, not variant. Historical tracking would need variant support.

#### 4. `sale_items` (Sales Line Items)
**Purpose**: Individual line items in sales transactions  
**Key Columns**:
- `id` - Primary key
- `saleid` - Foreign key to sales
- `storeditemid` - Foreign key to stored_items
- `saleitemid` - Item reference in sale
- `qty` - Quantity sold
- `name` - Item name at time of sale
- `description` - Item description
- `taxid` - Tax rule ID
- `tax` - Tax details (JSON)
- `cost` - Cost at time of sale
- `unit` - Unit price
- `price` - Total price
- `refundqty` - Quantity refunded

**Observation**: Sales only reference product ID. No way to track which variant was sold. Refund tracking exists.

#### 5. Supporting Tables
- **`stored_suppliers`**: Supplier information
- **`stored_categories`**: Product categories
- **`locations`**: Store locations
- **`sales`**: Main sales transactions
- **`tax_items`**: Tax definitions
- **`tax_rules`**: Tax rule configurations

**Observation**: No attributes, options, or variant-related tables existed.

---

## New Schema - What Was Added

### New Tables (4 Total)

#### 1. `attributes`
**Purpose**: Define types of attributes (e.g., Color, Size, Material)

```sql
CREATE TABLE `attributes` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(66) NOT NULL,          -- Display name (e.g., "Color")
  `code` varchar(32) NOT NULL UNIQUE,   -- Machine-readable code (e.g., "color")
  `sort_order` int(11) DEFAULT 0,       -- Display order
  `dt` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

**Indexes**: Primary key on `id`, unique on `code`  
**Why**: Flexible attribute system allows any attribute types

#### 2. `attribute_values`
**Purpose**: Specific values for each attribute

```sql
CREATE TABLE `attribute_values` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `attribute_id` int(11) NOT NULL,      -- FK to attributes
  `value` varchar(66) NOT NULL,         -- Display value (e.g., "Red")
  `code` varchar(32) NOT NULL,          -- Machine-readable code (e.g., "red")
  `sort_order` int(11) DEFAULT 0,       -- Display order
  `dt` datetime DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY (`attribute_id`, `value`),
  FOREIGN KEY (`attribute_id`) REFERENCES `attributes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
```

**Indexes**: Primary key on `id`, composite unique on `(attribute_id, value)`, foreign key on `attribute_id`  
**Cascade**: Deleting an attribute deletes its values  
**Why**: Normalized approach prevents duplicate attribute values

#### 3. `product_variants`
**Purpose**: Sellable variants of products

```sql
CREATE TABLE `product_variants` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,        -- FK to stored_items (parent product)
  `sku` varchar(128) NOT NULL UNIQUE,   -- Unique SKU for this variant
  `barcode` varchar(256) NULL,          -- Optional barcode (can differ from SKU)
  `name_suffix` varchar(128) DEFAULT '', -- e.g., "Red / Large"
  `price` decimal(12,2) DEFAULT 0.00,   -- Variant-specific price
  `cost` decimal(12,2) DEFAULT 0.00,    -- Variant-specific cost
  `is_default` tinyint(1) DEFAULT 0,    -- 1 for auto-created default variant
  `is_active` tinyint(1) DEFAULT 1,     -- Active/inactive toggle
  `sort_order` int(11) DEFAULT 0,       -- Display order
  `data` varchar(2048) DEFAULT '{}',    -- Additional JSON data
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `stored_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
```

**Indexes**: Primary key on `id`, unique on `sku`, foreign key on `product_id`, indexes on `is_active`, `is_default`  
**Cascade**: Deleting a product deletes all its variants  
**Why**: Each variant is independently sellable with own attributes

#### 4. `product_variant_attribute_values`
**Purpose**: Junction table linking variants to their attribute values

```sql
CREATE TABLE `product_variant_attribute_values` (
  `variant_id` int(11) NOT NULL,
  `attribute_value_id` int(11) NOT NULL,
  PRIMARY KEY (`variant_id`, `attribute_value_id`),
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
```

**Indexes**: Composite primary key on `(variant_id, attribute_value_id)`, foreign keys on both columns  
**Cascade**: Deleting a variant or attribute value removes the link  
**Why**: Many-to-many relationship (variant can have multiple attributes)

---

## Modified Tables (3 Total)

### 1. `stock_levels`
**Changes Made**:
```sql
ALTER TABLE `stock_levels` 
  ADD COLUMN `variant_id` int(11) NULL AFTER `storeditemid`,
  ADD KEY `variant_id` (`variant_id`),
  ADD CONSTRAINT `fk_stock_levels_variant` 
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) 
    ON DELETE CASCADE;
```

**Composite Index Added**:
```sql
CREATE INDEX `idx_stock_variant_location` ON `stock_levels` (`variant_id`, `locationid`);
CREATE INDEX `idx_stock_item_location` ON `stock_levels` (`storeditemid`, `locationid`);
```

**Why Nullable**: Backward compatibility. Legacy rows have NULL variant_id. New rows reference specific variants.  
**Impact**: Stock can now be tracked per variant per location.

### 2. `stock_history`
**Changes Made**:
```sql
ALTER TABLE `stock_history` 
  ADD COLUMN `variant_id` int(11) NULL AFTER `storeditemid`,
  ADD KEY `variant_id` (`variant_id`),
  ADD CONSTRAINT `fk_stock_history_variant` 
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) 
    ON DELETE CASCADE;
```

**Why Nullable**: Historical records may not have variant info.  
**Impact**: All future stock movements record which variant was affected.

### 3. `sale_items`
**Changes Made**:
```sql
ALTER TABLE `sale_items` 
  ADD COLUMN `variant_id` int(11) NULL AFTER `storeditemid`,
  ADD KEY `variant_id` (`variant_id`),
  ADD CONSTRAINT `fk_sale_items_variant` 
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) 
    ON DELETE SET NULL;
```

**Why SET NULL on Delete**: If a variant is deleted, historical sales remain but lose variant reference.  
**Impact**: Sales now track which variant was sold, enabling variant-level sales reports.

---

## Data Migration

### Default Variants Creation

Every existing product in `stored_items` gets one default variant:

```sql
INSERT INTO `product_variants` 
  (`product_id`, `sku`, `barcode`, `name_suffix`, `price`, `cost`, `is_default`, ...)
SELECT 
  si.id,                    -- Links to parent product
  si.code,                  -- SKU = product code
  si.code,                  -- Barcode = product code
  '',                       -- No suffix for default variant
  si.price,                 -- Inherits product price
  COALESCE(cost_from_json), -- Extracts cost from JSON data if exists
  1,                        -- Marked as default
  ...
FROM stored_items si;
```

### Stock Migration

All existing stock records updated to reference default variants:

```sql
UPDATE stock_levels sl
INNER JOIN product_variants pv 
  ON sl.storeditemid = pv.product_id AND pv.is_default = 1
SET sl.variant_id = pv.id
WHERE sl.variant_id IS NULL;
```

### Sales History Migration

All historical sale items linked to default variants:

```sql
UPDATE sale_items si
INNER JOIN product_variants pv 
  ON si.storeditemid = pv.product_id AND pv.is_default = 1
SET si.variant_id = pv.id
WHERE si.variant_id IS NULL;
```

---

## Backward Compatibility Strategy

### 1. Nullable Foreign Keys
All `variant_id` columns are nullable, allowing legacy code to work without modification.

### 2. Default Variant Pattern
Every product has at least one variant (the default). Legacy code that only knows about products will transparently use the default variant.

### 3. Dual Key Queries
Queries can filter by `storeditemid` OR `variant_id`, supporting both old and new code paths.

### 4. Helper Methods
`VariantsHelper::resolveVariantId()` converts product_id to appropriate variant_id, bridging old and new code.

---

## Index Strategy

### Primary Indexes
- All PKs are auto-increment integers
- Composite PK on junction table for efficient lookups

### Unique Constraints
- `attributes.code` - Prevents duplicate attribute names
- `attribute_values.(attribute_id, value)` - Prevents duplicate values per attribute
- `product_variants.sku` - Ensures unique SKUs across all variants

### Foreign Key Indexes
- All FK columns automatically indexed
- Enables efficient joins and cascade operations

### Composite Indexes
- `(variant_id, locationid)` on stock_levels - Optimizes variant stock lookups
- `(storeditemid, locationid)` on stock_levels - Maintains old query performance

---

## Storage Impact Estimate

For a typical installation with 1,000 products:

**New Tables**:
- `attributes`: ~10 records × 100 bytes = 1 KB
- `attribute_values`: ~50 records × 100 bytes = 5 KB
- `product_variants`: 1,000 default variants × 200 bytes = 200 KB
- `product_variant_attribute_values`: Initially empty = 0 KB

**Modified Tables**:
- `stock_levels`: +4 bytes per row (variant_id INT)
- `stock_history`: +4 bytes per row
- `sale_items`: +4 bytes per row

**Total Additional Storage**: < 1 MB for typical installation  
**Index Overhead**: ~10-20% of data size

---

## Performance Considerations

### Positive Impacts
- Targeted queries by variant_id (indexed)
- Composite indexes speed up common stock queries
- Cascading deletes handle cleanup automatically

### Potential Concerns
- Joins now include variant tables (mitigated by indexes)
- Stock lookups need variant resolution (cached in helper)
- Attribute generation creates many variants at once (batched)

### Optimization Recommendations
1. Regular `ANALYZE TABLE` on high-traffic tables
2. Consider partitioning stock_history if very large
3. Cache attribute lists (rarely change)
4. Use variant_id directly in POS to avoid resolution overhead

---

## Security Considerations

### SQL Injection
- All queries use PDO parameterized statements
- No raw SQL concatenation

### Cascade Deletes
- Carefully configured to maintain referential integrity
- Prevent orphaned records
- Document: Deleting product deletes ALL variants and their stock

### Unique Constraints
- Prevent duplicate SKUs (could cause scanning conflicts)
- Prevent duplicate attribute values

### Access Control
- Variant management requires admin privileges
- API endpoints need authentication layer (implementation-specific)

---

## Rollback Plan

If migration needs to be reversed:

```sql
-- Remove foreign keys
ALTER TABLE sale_items DROP FOREIGN KEY fk_sale_items_variant;
ALTER TABLE stock_history DROP FOREIGN KEY fk_stock_history_variant;
ALTER TABLE stock_levels DROP FOREIGN KEY fk_stock_levels_variant;

-- Remove columns
ALTER TABLE sale_items DROP COLUMN variant_id;
ALTER TABLE stock_history DROP COLUMN variant_id;
ALTER TABLE stock_levels DROP COLUMN variant_id;

-- Drop new tables
DROP TABLE product_variant_attribute_values;
DROP TABLE product_variants;
DROP TABLE attribute_values;
DROP TABLE attributes;
```

Then restore from pre-migration backup.

---

## Summary

### What Existed
- Simple product table with single code/price
- Stock tracked by product only
- No variant or attribute support
- Sales referenced products, not variants

### What Was Added
- 4 new tables for variants and attributes
- 3 existing tables extended with variant_id
- Complete attribute value system
- Per-variant stock tracking
- Per-variant sales recording

### Design Principles Followed
✅ Backward compatible (nullable FKs, default variants)  
✅ Minimal changes (extended vs. redesigned)  
✅ Normalized schema (prevent duplicates)  
✅ Indexed appropriately (performance)  
✅ Referential integrity (cascades configured)  
✅ Migration-friendly (idempotent scripts)

### Result
A production-ready variant system that:
- Works alongside existing products
- Enables complex SKU management
- Tracks stock per variant
- Reports at variant or product level
- Maintains full backward compatibility
