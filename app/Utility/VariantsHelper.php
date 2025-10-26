<?php

/**
 *
 * VariantsHelper provides utility methods for working with product variants
 *
 */

namespace App\Utility;

use App\Database\ProductVariantsModel;
use App\Database\StoredItemsModel;
use App\Database\StockModel;

class VariantsHelper
{
    /**
     * Find a sellable item by barcode or SKU
     * Returns variant if found, otherwise tries to find product and return its default variant
     * 
     * @param string $code Barcode or SKU
     * @return array|null Variant data or null if not found
     */
    public static function findByCode($code)
    {
        $variantsMdl = new ProductVariantsModel();
        
        // Try to find variant by SKU or barcode
        $variant = $variantsMdl->findByCode($code);
        if ($variant !== null) {
            return $variant;
        }
        
        // Try to find product by code and return its default variant
        $itemsMdl = new StoredItemsModel();
        $items = $itemsMdl->get(null, $code);
        if (is_array($items) && count($items) > 0) {
            $product = $items[0];
            $defaultVariant = $variantsMdl->getDefaultVariant($product['id']);
            if ($defaultVariant !== null) {
                return $defaultVariant;
            }
        }
        
        return null;
    }
    
    /**
     * Get full product and variant information for POS display
     * 
     * @param int $variant_id Variant ID
     * @return array|null Complete product/variant info or null
     */
    public static function getVariantForSale($variant_id)
    {
        $variantsMdl = new ProductVariantsModel();
        $variant = $variantsMdl->getById($variant_id);
        
        if ($variant === null) {
            return null;
        }
        
        // Get product details
        $itemsMdl = new StoredItemsModel();
        $products = $itemsMdl->get($variant['product_id']);
        if (!is_array($products) || count($products) == 0) {
            return null;
        }
        
        $product = $products[0];
        
        // Merge product and variant data
        return array_merge($product, [
            'variant_id' => $variant['id'],
            'variant_sku' => $variant['sku'],
            'variant_barcode' => $variant['barcode'],
            'variant_name_suffix' => $variant['name_suffix'],
            'variant_price' => $variant['price'],
            'variant_cost' => $variant['cost'],
            'variant_is_default' => $variant['is_default'],
            'variant_data' => $variant['data'],
            // Override price with variant price
            'price' => $variant['price'],
            'cost' => $variant['cost'],
            // Create display name
            'display_name' => $variant['name_suffix'] ? 
                $product['name'] . ' - ' . $variant['name_suffix'] : 
                $product['name']
        ]);
    }
    
    /**
     * Get stock level for a specific variant at a location
     * 
     * @param int $variant_id Variant ID
     * @param int $location_id Location ID
     * @return int Stock level
     */
    public static function getStockLevel($variant_id, $location_id)
    {
        $stockMdl = new StockModel();
        $stock = $stockMdl->get(null, $location_id, false, $variant_id);
        
        if (is_array($stock) && count($stock) > 0) {
            return intval($stock[0]['stocklevel']);
        }
        
        return 0;
    }
    
    /**
     * Get all variants for a product with stock information
     * 
     * @param int $product_id Product ID
     * @param int|null $location_id Optional location ID for stock info
     * @return array Array of variants with stock
     */
    public static function getProductVariantsWithStock($product_id, $location_id = null)
    {
        $variantsMdl = new ProductVariantsModel();
        $variants = $variantsMdl->getVariantsWithAttributes($product_id);
        
        if (!is_array($variants)) {
            return [];
        }
        
        $stockMdl = new StockModel();
        
        foreach ($variants as $key => $variant) {
            if ($location_id !== null) {
                $stock = $stockMdl->get(null, $location_id, false, $variant['id']);
                $variants[$key]['stock_level'] = (is_array($stock) && count($stock) > 0) ? 
                    intval($stock[0]['stocklevel']) : 0;
            } else {
                // Get total stock across all locations
                $stock = $stockMdl->get(null, null, false, $variant['id']);
                $total_stock = 0;
                if (is_array($stock)) {
                    foreach ($stock as $s) {
                        $total_stock += intval($s['stocklevel']);
                    }
                }
                $variants[$key]['stock_level'] = $total_stock;
            }
            
            // Create formatted attribute string for display
            $attr_strings = [];
            if (isset($variant['attributes']) && is_array($variant['attributes'])) {
                foreach ($variant['attributes'] as $attr) {
                    $attr_strings[] = $attr['value'];
                }
            }
            $variants[$key]['attributes_display'] = implode(' / ', $attr_strings);
        }
        
        return $variants;
    }
    
    /**
     * Decrement stock for a variant sale
     * 
     * @param int $variant_id Variant ID
     * @param int $product_id Product ID (for backward compatibility)
     * @param int $location_id Location ID
     * @param int $quantity Quantity to decrement
     * @return bool Success status
     */
    public static function decrementStock($variant_id, $product_id, $location_id, $quantity)
    {
        $stockMdl = new StockModel();
        $result = $stockMdl->incrementStockLevel($product_id, $location_id, $quantity, true, $variant_id);
        return $result !== false;
    }
    
    /**
     * Increment stock for a variant (purchase, return, etc.)
     * 
     * @param int $variant_id Variant ID
     * @param int $product_id Product ID (for backward compatibility)
     * @param int $location_id Location ID
     * @param int $quantity Quantity to increment
     * @return bool Success status
     */
    public static function incrementStock($variant_id, $product_id, $location_id, $quantity)
    {
        $stockMdl = new StockModel();
        $result = $stockMdl->incrementStockLevel($product_id, $location_id, $quantity, false, $variant_id);
        return $result !== false;
    }
    
    /**
     * Generate SKU for a new variant
     * 
     * @param string $product_code Base product code
     * @param array $attribute_values Array of attribute value codes
     * @return string Generated SKU
     */
    public static function generateSKU($product_code, $attribute_values = [])
    {
        if (empty($attribute_values)) {
            return $product_code;
        }
        
        $suffix = [];
        foreach ($attribute_values as $av) {
            if (isset($av['code'])) {
                $suffix[] = strtoupper($av['code']);
            } elseif (isset($av['value'])) {
                $suffix[] = strtoupper(substr($av['value'], 0, 3));
            }
        }
        
        return $product_code . '-' . implode('-', $suffix);
    }
    
    /**
     * Generate name suffix for a variant from attributes
     * 
     * @param array $attribute_values Array of attribute values
     * @return string Name suffix (e.g., "Red / Large")
     */
    public static function generateNameSuffix($attribute_values)
    {
        $parts = [];
        foreach ($attribute_values as $av) {
            if (isset($av['value'])) {
                $parts[] = $av['value'];
            }
        }
        
        return implode(' / ', $parts);
    }
    
    /**
     * Check if a product has multiple variants
     * 
     * @param int $product_id Product ID
     * @return bool True if product has more than one variant
     */
    public static function hasMultipleVariants($product_id)
    {
        $variantsMdl = new ProductVariantsModel();
        $count = $variantsMdl->getVariantCount($product_id);
        return $count > 1;
    }
    
    /**
     * Resolve stored item ID to variant ID for sale
     * If variant_id is provided, use it. Otherwise get default variant for the product.
     * 
     * @param int $stored_item_id Product ID
     * @param int|null $variant_id Optional variant ID
     * @return int|null Variant ID to use
     */
    public static function resolveVariantId($stored_item_id, $variant_id = null)
    {
        if ($variant_id !== null && $variant_id > 0) {
            return $variant_id;
        }
        
        $variantsMdl = new ProductVariantsModel();
        $defaultVariant = $variantsMdl->getDefaultVariant($stored_item_id);
        
        return $defaultVariant ? $defaultVariant['id'] : null;
    }
}
