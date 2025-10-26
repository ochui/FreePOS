# Product Variants Feature Documentation

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Database Schema](#database-schema)
4. [PHP Models & Controllers](#php-models--controllers)
5. [API Endpoints](#api-endpoints)
6. [Usage Examples](#usage-examples)
7. [UI Components](#ui-components)
8. [Best Practices](#best-practices)

## Overview

The Product Variants feature enables QuickBooks POS-style variant management in FreePOS. Each sellable variant has its own:
- SKU / Barcode
- Price and Cost
- Stock level (per location)
- Attribute combinations (e.g., Color: Red, Size: Large)

### Key Features

- **Multi-level Product Structure**: Product (parent) → Variants (children)
- **Attributes System**: Flexible attribute-value system for variant properties
- **Stock Tracking**: Independent stock per variant per location
- **POS Integration**: Barcode scanning and variant picker
- **Backward Compatible**: Existing products auto-migrate with default variants
- **Reporting**: Variant-level detail and product-level aggregation

## Architecture

### Design Principles

1. **Minimal Changes**: Extends existing tables rather than redesigning
2. **Backward Compatible**: Legacy code continues to work
3. **Nullable Foreign Keys**: variant_id is nullable in stock/sale tables
4. **Default Variants**: Every product has at least one variant
5. **Cascading Deletes**: Variant deletion cascades to related records

### Data Flow

```
Product (stored_items)
    ↓
Product Variant (product_variants)
    ↓
Attribute Values (product_variant_attribute_values)
    ↓
Stock Levels (stock_levels)
    ↓
Sale Items (sale_items)
```

## Database Schema

### Core Tables

#### `attributes`
Defines attribute types (e.g., Color, Size, Material)

```sql
CREATE TABLE `attributes` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(66) NOT NULL,
  `code` varchar(32) NOT NULL UNIQUE,
  `sort_order` int(11) DEFAULT 0,
  `dt` datetime DEFAULT CURRENT_TIMESTAMP
);
```

#### `attribute_values`
Stores specific values for each attribute

```sql
CREATE TABLE `attribute_values` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `attribute_id` int(11) NOT NULL,
  `value` varchar(66) NOT NULL,
  `code` varchar(32) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `dt` datetime DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY (`attribute_id`, `value`),
  FOREIGN KEY (`attribute_id`) REFERENCES `attributes`(`id`) ON DELETE CASCADE
);
```

#### `product_variants`
Stores sellable variants for each product

```sql
CREATE TABLE `product_variants` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `sku` varchar(128) NOT NULL UNIQUE,
  `barcode` varchar(256) NULL,
  `name_suffix` varchar(128) DEFAULT '',
  `price` decimal(12,2) DEFAULT 0.00,
  `cost` decimal(12,2) DEFAULT 0.00,
  `is_default` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `data` varchar(2048) DEFAULT '{}',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `stored_items`(`id`) ON DELETE CASCADE
);
```

#### `product_variant_attribute_values`
Junction table linking variants to their attribute values

```sql
CREATE TABLE `product_variant_attribute_values` (
  `variant_id` int(11) NOT NULL,
  `attribute_value_id` int(11) NOT NULL,
  PRIMARY KEY (`variant_id`, `attribute_value_id`),
  FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`attribute_value_id`) REFERENCES `attribute_values`(`id`) ON DELETE CASCADE
);
```

### Extended Tables

The following existing tables now include `variant_id`:

- **stock_levels**: Tracks stock per variant per location
- **stock_history**: Records stock movements per variant
- **sale_items**: Links sales to specific variants

## PHP Models & Controllers

### Models

#### `ProductVariantsModel`
Handles CRUD operations for variants

**Key Methods:**
```php
// Create a variant
create($data): int|bool

// Get variants (with filters)
get($id, $product_id, $sku, $barcode, $active_only): array|bool

// Find by code (SKU or barcode)
findByCode($code): array|null

// Get default variant for a product
getDefaultVariant($product_id): array|null

// Update variant
edit($id, $data): int|bool

// Delete variant (cannot delete only variant)
remove($id): int|bool

// Get variants with attributes
getVariantsWithAttributes($product_id): array|bool

// Link attributes to variant
setVariantAttributes($variant_id, $attribute_value_ids): bool
```

#### `AttributesModel`
Manages attributes and their values

**Key Methods:**
```php
// Attributes
createAttribute($data): int|bool
getAttributes($id, $code): array|bool
editAttribute($id, $data): int|bool
removeAttribute($id): int|bool

// Attribute Values
createAttributeValue($data): int|bool
getAttributeValues($id, $attribute_id): array|bool
editAttributeValue($id, $data): int|bool
removeAttributeValue($id): int|bool

// Combined
getAttributesWithValues(): array
generateAttributeCombinations($attribute_ids): array
```

#### `VariantsHelper`
Utility methods for variant operations

**Key Methods:**
```php
// Lookup
findByCode($code): array|null
getVariantForSale($variant_id): array|null
resolveVariantId($stored_item_id, $variant_id): int|null

// Stock
getStockLevel($variant_id, $location_id): int
getProductVariantsWithStock($product_id, $location_id): array
decrementStock($variant_id, $product_id, $location_id, $qty): bool
incrementStock($variant_id, $product_id, $location_id, $qty): bool

// Generation
generateSKU($product_code, $attribute_values): string
generateNameSuffix($attribute_values): string
hasMultipleVariants($product_id): bool
```

### Controllers

#### `AdminVariants`
Admin controller for variant management

**Endpoints:**
- `getVariants`: Load variants for a product
- `createVariant`: Create a new variant
- `updateVariant`: Update variant details
- `deleteVariant`: Delete a variant
- `generateVariants`: Auto-generate from attributes
- `getAttributes`: Load all attributes
- `createAttribute`: Create new attribute
- `createAttributeValue`: Add attribute value
- `updateVariantStock`: Update stock level

## API Endpoints

All variant endpoints should be added to your routing configuration.

### Variant Management

```
POST /api/variants/get
Body: { "product_id": 123 }
Response: { "data": [ {...variant...} ] }

POST /api/variants/create
Body: { "product_id": 123, "sku": "TSHIRT-RED-L", "price": 29.99, ... }
Response: { "data": { "id": 456 } }

POST /api/variants/update
Body: { "id": 456, "price": 24.99, "is_active": 1 }
Response: { "data": { "updated": 1 } }

POST /api/variants/delete
Body: { "id": 456 }
Response: { "data": { "deleted": 1 } }

POST /api/variants/generate
Body: { "product_id": 123, "attribute_ids": [1, 2] }
Response: { "data": { "created_count": 6, "variant_ids": [...] } }
```

### Attributes Management

```
GET /api/variants/attributes
Response: { "data": [ {...attribute with values...} ] }

POST /api/variants/attribute/create
Body: { "name": "Color", "code": "color" }
Response: { "data": { "id": 1 } }

POST /api/variants/attribute/value/create
Body: { "attribute_id": 1, "value": "Red", "code": "red" }
Response: { "data": { "id": 10 } }
```

## Usage Examples

### Example 1: Creating a T-Shirt with Size and Color Variants

```php
// 1. Create the parent product
$itemsMdl = new StoredItemsModel();
$itemData = new StoredItem([
    'code' => 'TSHIRT-001',
    'name' => 'Basic T-Shirt',
    'price' => 19.99,
    'supplierid' => 1,
    'categoryid' => 5
]);
$product_id = $itemsMdl->create($itemData);

// 2. Create attributes (one-time setup)
$attrMdl = new AttributesModel();
$color_id = $attrMdl->createAttribute(['name' => 'Color', 'code' => 'color']);
$size_id = $attrMdl->createAttribute(['name' => 'Size', 'code' => 'size']);

// 3. Create attribute values
$red_id = $attrMdl->createAttributeValue(['attribute_id' => $color_id, 'value' => 'Red', 'code' => 'red']);
$blue_id = $attrMdl->createAttributeValue(['attribute_id' => $color_id, 'value' => 'Blue', 'code' => 'blue']);
$small_id = $attrMdl->createAttributeValue(['attribute_id' => $size_id, 'value' => 'Small', 'code' => 's']);
$large_id = $attrMdl->createAttributeValue(['attribute_id' => $size_id, 'value' => 'Large', 'code' => 'l']);

// 4. Generate variants (creates: Red/Small, Red/Large, Blue/Small, Blue/Large)
$combinations = $attrMdl->generateAttributeCombinations([$color_id, $size_id]);
$variantsMdl = new ProductVariantsModel();

foreach ($combinations as $combo) {
    $sku = VariantsHelper::generateSKU('TSHIRT-001', $combo);
    $name_suffix = VariantsHelper::generateNameSuffix($combo);
    
    $variant_id = $variantsMdl->create([
        'product_id' => $product_id,
        'sku' => $sku,
        'barcode' => $sku,
        'name_suffix' => $name_suffix,
        'price' => 19.99,
        'cost' => 10.00,
        'is_active' => 1
    ]);
    
    $attr_value_ids = array_map(function($av) { return $av['id']; }, $combo);
    $variantsMdl->setVariantAttributes($variant_id, $attr_value_ids);
}
```

### Example 2: POS Barcode Scan

```php
// When barcode is scanned at POS
$barcode = $_POST['barcode'];

// Find the variant
$variant = VariantsHelper::findByCode($barcode);

if ($variant) {
    // Get full info for sale
    $saleData = VariantsHelper::getVariantForSale($variant['id']);
    
    // Check stock
    $stock = VariantsHelper::getStockLevel($variant['id'], $location_id);
    
    if ($stock > 0) {
        // Add to cart with variant info
        $cartItem = [
            'sitemid' => $saleData['id'],
            'variant_id' => $saleData['variant_id'],
            'name' => $saleData['display_name'],
            'price' => $saleData['price'],
            'cost' => $saleData['cost'],
            // ... other fields
        ];
    }
}
```

### Example 3: Process Sale with Variant

```php
// When processing the sale
$itemsMdl = new SaleItemsModel();
$variant_id = $item->variant_id;
$product_id = $item->sitemid;

// Create sale item
$sale_item_id = $itemsMdl->create(
    $sale_id,
    $product_id,
    $item_ref,
    $qty,
    $name,
    $desc,
    $taxid,
    $tax,
    $cost,
    $unit,
    $price,
    $unit_original,
    $variant_id  // Variant ID is passed here
);

// Decrement stock for the specific variant
VariantsHelper::decrementStock($variant_id, $product_id, $location_id, $qty);
```

## UI Components

### Admin Variant Manager

Include in your product edit page:

```html
<div id="variants-section" class="variants-section">
    <h3>Product Variants</h3>
    
    <!-- Attribute selector for generation -->
    <div id="variant-generator"></div>
    
    <!-- List of existing variants -->
    <div id="variants-list"></div>
</div>

<link rel="stylesheet" href="/assets/css/variants.css">
<script src="/assets/js/variants.js"></script>
<script>
    // Initialize on product load
    VariantsManager.init(<?php echo $product_id; ?>);
</script>
```

### POS Variant Picker

Modal for selecting variant when product has multiple options:

```html
<div id="variant-picker-modal" class="variant-picker-modal">
    <div class="variant-picker-content">
        <div class="variant-picker-header">
            <button class="variant-picker-close">&times;</button>
            <h3>Select Variant</h3>
            <p class="text-muted">Choose an option for <span id="vp-product-name"></span></p>
        </div>
        <div id="variant-options"></div>
        <div class="variant-picker-actions">
            <button class="btn btn-default" onclick="closeVariantPicker()">Cancel</button>
        </div>
    </div>
</div>
```

## Best Practices

### 1. SKU Naming Convention

Use a consistent format for variant SKUs:
```
BASE-ATTRIBUTE1-ATTRIBUTE2
Example: TSHIRT-RED-L
```

### 2. Default Variants

- Every product must have at least one variant
- Mark the most common variant as default
- Default variant is used when no specific variant is selected

### 3. Stock Management

- Always update stock at the variant level
- Use the VariantsHelper methods for stock operations
- Stock reports should aggregate by variant and by product

### 4. Attribute Organization

- Use clear, consistent attribute names
- Order attributes logically (Color, then Size)
- Use sort_order to control display sequence

### 5. Performance Optimization

- Index frequently queried fields (sku, barcode)
- Use eager loading for variant attributes
- Cache attribute lists (they change infrequently)

### 6. Error Handling

- Validate SKU uniqueness before creating variants
- Prevent deletion of the only variant
- Handle variant_id gracefully when null (backward compatibility)

### 7. Migration

- Always backup before migration
- Test on development environment first
- Verify default variants created correctly
- Check stock assignments after migration

## Security Considerations

1. **SQL Injection**: All models use parameterized queries
2. **Access Control**: Variant management should require admin privileges
3. **Data Validation**: Validate all input using JsonValidate
4. **Cascade Deletes**: Be aware that deleting a product deletes all variants
5. **Stock Accuracy**: Implement transaction locks for stock updates

## Future Enhancements

Possible extensions to the variant system:

1. **Variant Images**: Associate images with specific variants
2. **Bulk Import**: CSV import for creating many variants
3. **Variant-Specific Pricing Rules**: Discounts per variant
4. **Composite Variants**: Bundled products as variants
5. **Variant Search**: Advanced POS search by attributes
6. **Matrix View**: Spreadsheet-like bulk editing

## Support & Contributing

For questions or contributions:
- GitHub: https://github.com/ochui/FreePOS
- Documentation: See VARIANTS_MIGRATION_GUIDE.md
- Issues: Report on GitHub Issues
