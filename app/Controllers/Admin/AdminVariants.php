<?php

/**
 *
 * AdminVariants is used to manage product variants and attributes
 *
 */

namespace App\Controllers\Admin;

use App\Database\ProductVariantsModel;
use App\Database\AttributesModel;
use App\Database\StoredItemsModel;
use App\Database\StockModel;
use App\Utility\JsonValidate;
use App\Utility\VariantsHelper;

class AdminVariants
{
    /**
     * @var stdClass provided params
     */
    private $data;

    /**
     * Init
     * @param null $data
     */
    public function __construct($data = null)
    {
        $this->data = $data;
    }

    // ========================================
    // VARIANTS MANAGEMENT
    // ========================================

    /**
     * Get variants for a product
     * @param array $result
     * @return array
     */
    public function getVariants($result)
    {
        // Validate input
        $jsonval = new JsonValidate($this->data, '{"product_id":1}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }

        $variantsMdl = new ProductVariantsModel();
        $variants = $variantsMdl->getVariantsWithAttributes($this->data->product_id);

        if (is_array($variants)) {
            // Add stock information for each variant
            $stockMdl = new StockModel();
            foreach ($variants as $key => $variant) {
                $stock = $stockMdl->get(null, null, false, $variant['id']);
                $variants[$key]['stock'] = is_array($stock) ? $stock : [];
            }
            $result['data'] = $variants;
        } else {
            $result['error'] = "Failed to retrieve variants";
        }

        return $result;
    }

    /**
     * Create a new variant
     * @param array $result
     * @return array
     */
    public function createVariant($result)
    {
        // Validate input
        $jsonval = new JsonValidate($this->data, '{"product_id":1, "sku":"~", "price":">=0"}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }

        $variantsMdl = new ProductVariantsModel();
        
        // Prepare variant data
        $variantData = [
            'product_id' => $this->data->product_id,
            'sku' => $this->data->sku,
            'barcode' => isset($this->data->barcode) ? $this->data->barcode : $this->data->sku,
            'name_suffix' => isset($this->data->name_suffix) ? $this->data->name_suffix : '',
            'price' => $this->data->price,
            'cost' => isset($this->data->cost) ? $this->data->cost : 0.00,
            'is_default' => 0, // New variants are never default
            'is_active' => isset($this->data->is_active) ? $this->data->is_active : 1,
            'sort_order' => isset($this->data->sort_order) ? $this->data->sort_order : 0,
            'data' => isset($this->data->data) ? $this->data->data : []
        ];

        $variant_id = $variantsMdl->create($variantData);

        if ($variant_id && $variant_id !== false) {
            // Link attribute values if provided
            if (isset($this->data->attribute_value_ids) && is_array($this->data->attribute_value_ids)) {
                $variantsMdl->setVariantAttributes($variant_id, $this->data->attribute_value_ids);
            }

            $result['data'] = ['id' => $variant_id];
        } else {
            $result['error'] = "Failed to create variant. The SKU may already exist.";
        }

        return $result;
    }

    /**
     * Update a variant
     * @param array $result
     * @return array
     */
    public function updateVariant($result)
    {
        // Validate input
        $jsonval = new JsonValidate($this->data, '{"id":1}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }

        $variantsMdl = new ProductVariantsModel();
        
        // Prepare update data
        $updateData = [];
        if (isset($this->data->sku)) $updateData['sku'] = $this->data->sku;
        if (isset($this->data->barcode)) $updateData['barcode'] = $this->data->barcode;
        if (isset($this->data->name_suffix)) $updateData['name_suffix'] = $this->data->name_suffix;
        if (isset($this->data->price)) $updateData['price'] = $this->data->price;
        if (isset($this->data->cost)) $updateData['cost'] = $this->data->cost;
        if (isset($this->data->is_active)) $updateData['is_active'] = $this->data->is_active;
        if (isset($this->data->sort_order)) $updateData['sort_order'] = $this->data->sort_order;
        if (isset($this->data->data)) $updateData['data'] = $this->data->data;

        $updated = $variantsMdl->edit($this->data->id, $updateData);

        if ($updated !== false) {
            // Update attribute values if provided
            if (isset($this->data->attribute_value_ids)) {
                $variantsMdl->setVariantAttributes($this->data->id, $this->data->attribute_value_ids);
            }

            $result['data'] = ['updated' => $updated];
        } else {
            $result['error'] = "Failed to update variant";
        }

        return $result;
    }

    /**
     * Delete a variant
     * @param array $result
     * @return array
     */
    public function deleteVariant($result)
    {
        // Validate input
        $jsonval = new JsonValidate($this->data, '{"id":1}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }

        $variantsMdl = new ProductVariantsModel();
        $deleted = $variantsMdl->remove($this->data->id);

        if ($deleted !== false && $deleted > 0) {
            $result['data'] = ['deleted' => $deleted];
        } else {
            $result['error'] = "Failed to delete variant. Cannot delete the only variant for a product.";
        }

        return $result;
    }

    /**
     * Generate variants from attribute combinations
     * @param array $result
     * @return array
     */
    public function generateVariants($result)
    {
        // Validate input
        $jsonval = new JsonValidate($this->data, '{"product_id":1, "attribute_ids":[]}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }

        // Get product info
        $itemsMdl = new StoredItemsModel();
        $products = $itemsMdl->get($this->data->product_id);
        if (!is_array($products) || count($products) == 0) {
            $result['error'] = "Product not found";
            return $result;
        }
        $product = $products[0];

        // Generate attribute combinations
        $attrMdl = new AttributesModel();
        $combinations = $attrMdl->generateAttributeCombinations($this->data->attribute_ids);

        if (empty($combinations)) {
            $result['error'] = "No attribute combinations to generate";
            return $result;
        }

        $variantsMdl = new ProductVariantsModel();
        $created_variants = [];

        foreach ($combinations as $combination) {
            // Generate SKU and name suffix
            $sku = VariantsHelper::generateSKU($product['code'], $combination);
            $name_suffix = VariantsHelper::generateNameSuffix($combination);

            // Check if variant with this SKU already exists
            $existing = $variantsMdl->get(null, null, $sku);
            if (is_array($existing) && count($existing) > 0) {
                continue; // Skip existing variants
            }

            // Create variant
            $variantData = [
                'product_id' => $this->data->product_id,
                'sku' => $sku,
                'barcode' => $sku,
                'name_suffix' => $name_suffix,
                'price' => $product['price'],
                'cost' => isset($product['cost']) ? $product['cost'] : 0.00,
                'is_default' => 0,
                'is_active' => 1,
                'sort_order' => 0
            ];

            $variant_id = $variantsMdl->create($variantData);

            if ($variant_id && $variant_id !== false) {
                // Link attribute values
                $attribute_value_ids = array_map(function($av) { return $av['id']; }, $combination);
                $variantsMdl->setVariantAttributes($variant_id, $attribute_value_ids);
                $created_variants[] = $variant_id;
            }
        }

        $result['data'] = [
            'created_count' => count($created_variants),
            'variant_ids' => $created_variants
        ];

        return $result;
    }

    // ========================================
    // ATTRIBUTES MANAGEMENT
    // ========================================

    /**
     * Get all attributes with values
     * @param array $result
     * @return array
     */
    public function getAttributes($result)
    {
        $attrMdl = new AttributesModel();
        $attributes = $attrMdl->getAttributesWithValues();

        if (is_array($attributes)) {
            $result['data'] = $attributes;
        } else {
            $result['error'] = "Failed to retrieve attributes";
        }

        return $result;
    }

    /**
     * Create a new attribute
     * @param array $result
     * @return array
     */
    public function createAttribute($result)
    {
        // Validate input
        $jsonval = new JsonValidate($this->data, '{"name":"~", "code":"~"}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }

        $attrMdl = new AttributesModel();
        
        $attrData = [
            'name' => $this->data->name,
            'code' => $this->data->code,
            'sort_order' => isset($this->data->sort_order) ? $this->data->sort_order : 0
        ];

        $attr_id = $attrMdl->createAttribute($attrData);

        if ($attr_id && $attr_id !== false) {
            $result['data'] = ['id' => $attr_id];
        } else {
            $result['error'] = "Failed to create attribute. The code may already exist.";
        }

        return $result;
    }

    /**
     * Create a new attribute value
     * @param array $result
     * @return array
     */
    public function createAttributeValue($result)
    {
        // Validate input
        $jsonval = new JsonValidate($this->data, '{"attribute_id":1, "value":"~", "code":"~"}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }

        $attrMdl = new AttributesModel();
        
        $valueData = [
            'attribute_id' => $this->data->attribute_id,
            'value' => $this->data->value,
            'code' => $this->data->code,
            'sort_order' => isset($this->data->sort_order) ? $this->data->sort_order : 0
        ];

        $value_id = $attrMdl->createAttributeValue($valueData);

        if ($value_id && $value_id !== false) {
            $result['data'] = ['id' => $value_id];
        } else {
            $result['error'] = "Failed to create attribute value. The value may already exist.";
        }

        return $result;
    }

    /**
     * Update stock for a variant
     * @param array $result
     * @return array
     */
    public function updateVariantStock($result)
    {
        // Validate input
        $jsonval = new JsonValidate($this->data, '{"variant_id":1, "location_id":1, "stock_level":">=0"}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }

        // Get variant to get product_id
        $variantsMdl = new ProductVariantsModel();
        $variant = $variantsMdl->getById($this->data->variant_id);
        
        if (!$variant) {
            $result['error'] = "Variant not found";
            return $result;
        }

        $stockMdl = new StockModel();
        $updated = $stockMdl->setStockLevel(
            $variant['product_id'], 
            $this->data->location_id, 
            $this->data->stock_level,
            $this->data->variant_id
        );

        if ($updated !== false) {
            $result['data'] = ['updated' => true];
        } else {
            $result['error'] = "Failed to update stock level";
        }

        return $result;
    }
}
