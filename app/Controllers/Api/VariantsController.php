<?php

/**
 * Variants API Controller
 * Handles product variant management endpoints
 */

namespace App\Controllers\Api;

use App\Auth;
use App\Database\ProductAttributesModel;
use App\Database\ProductAttributeValuesModel;
use App\Database\ProductVariantsModel;
use App\Database\VariantStockModel;
use App\Database\StoredItemsModel;
use App\Utility\Logger;

class VariantsController
{
    private $auth;
    private $result = ["errorCode" => "OK", "error" => "OK", "data" => ""];
    private $attributesModel;
    private $valuesModel;
    private $variantsModel;
    private $stockModel;
    private $itemsModel;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->attributesModel = new ProductAttributesModel();
        $this->valuesModel = new ProductAttributeValuesModel();
        $this->variantsModel = new ProductVariantsModel();
        $this->stockModel = new VariantStockModel();
        $this->itemsModel = new StoredItemsModel();
    }

    /**
     * Check if user is logged in and handle CSRF
     */
    private function checkAuthentication()
    {
        if (!$this->auth->isLoggedIn()) {
            $this->result['errorCode'] = "auth";
            $this->result['error'] = "Access Denied!";
            $this->returnResult();
        }
    }

    /**
     * Return JSON result and exit
     */
    private function returnResult()
    {
        header('Content-Type: application/json');
        echo json_encode($this->result);
        exit();
    }

    /**
     * Get all product attributes
     */
    public function getAttributes()
    {
        $this->checkAuthentication();

        $attributes = $this->attributesModel->get();
        if ($attributes === false) {
            $this->result['errorCode'] = "db";
            $this->result['error'] = "Database error";
        } else {
            $this->result['data'] = $attributes;
        }

        $this->returnResult();
    }

    /**
     * Create a new product attribute
     */
    public function createAttribute()
    {
        $this->checkAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['name']) || !isset($data['display_name'])) {
            $this->result['errorCode'] = "invalid";
            $this->result['error'] = "Name and display name are required";
            $this->returnResult();
        }

        $attributeData = [
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'sort_order' => $data['sort_order'] ?? 0
        ];

        $result = $this->attributesModel->create($attributeData);
        if ($result === false) {
            $this->result['errorCode'] = "db";
            $this->result['error'] = "Failed to create attribute";
        } else {
            $this->result['data'] = ['id' => $result];
        }

        $this->returnResult();
    }

    /**
     * Update a product attribute
     */
    public function updateAttribute($id)
    {
        $this->checkAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['display_name'])) {
            $this->result['errorCode'] = "invalid";
            $this->result['error'] = "Display name is required";
            $this->returnResult();
        }

        $attributeData = [
            'display_name' => $data['display_name'],
            'sort_order' => $data['sort_order'] ?? 0
        ];

        $result = $this->attributesModel->edit($id, $attributeData);
        if ($result === false) {
            $this->result['errorCode'] = "db";
            $this->result['error'] = "Failed to update attribute";
        } else {
            $this->result['data'] = ['affected_rows' => $result];
        }

        $this->returnResult();
    }

    /**
     * Delete a product attribute
     */
    public function deleteAttribute($id)
    {
        $this->checkAuthentication();

        $result = $this->attributesModel->remove($id);
        if ($result === false) {
            $this->result['errorCode'] = "db";
            $this->result['error'] = "Failed to delete attribute";
        } else {
            $this->result['data'] = ['affected_rows' => $result];
        }

        $this->returnResult();
    }

    /**
     * Get attribute values for an attribute
     */
    public function getAttributeValues($attributeId)
    {
        $this->checkAuthentication();

        $values = $this->valuesModel->get(null, $attributeId);
        if ($values === false) {
            $this->result['errorCode'] = "db";
            $this->result['error'] = "Database error";
        } else {
            $this->result['data'] = $values;
        }

        $this->returnResult();
    }

    /**
     * Create a new attribute value
     */
    public function createAttributeValue()
    {
        $this->checkAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['attribute_id']) || !isset($data['value']) || !isset($data['display_value'])) {
            $this->result['errorCode'] = "invalid";
            $this->result['error'] = "Attribute ID, value, and display value are required";
            $this->returnResult();
        }

        $valueData = [
            'attribute_id' => $data['attribute_id'],
            'value' => $data['value'],
            'display_value' => $data['display_value'],
            'sort_order' => $data['sort_order'] ?? 0
        ];

        $result = $this->valuesModel->create($valueData);
        if ($result === false) {
            $this->result['errorCode'] = "db";
            $this->result['error'] = "Failed to create attribute value";
        } else {
            $this->result['data'] = ['id' => $result];
        }

        $this->returnResult();
    }

    /**
     * Update an attribute value
     */
    public function updateAttributeValue($id)
    {
        $this->checkAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['display_value'])) {
            $this->result['errorCode'] = "invalid";
            $this->result['error'] = "Display value is required";
            $this->returnResult();
        }

        $valueData = [
            'display_value' => $data['display_value'],
            'sort_order' => $data['sort_order'] ?? 0
        ];

        $result = $this->valuesModel->edit($id, $valueData);
        if ($result === false) {
            $this->result['errorCode'] = "db";
            $this->result['error'] = "Failed to update attribute value";
        } else {
            $this->result['data'] = ['affected_rows' => $result];
        }

        $this->returnResult();
    }

    /**
     * Delete an attribute value
     */
    public function deleteAttributeValue($id)
    {
        $this->checkAuthentication();

        $result = $this->valuesModel->remove($id);
        if ($result === false) {
            $this->result['errorCode'] = "db";
            $this->result['error'] = "Failed to delete attribute value";
        } else {
            $this->result['data'] = ['affected_rows' => $result];
        }

        $this->returnResult();
    }

    /**
     * Get variants for a product
     */
    public function getProductVariants($productId)
    {
        $this->checkAuthentication();

        $variants = $this->variantsModel->get(null, $productId);
        if ($variants === false) {
            $this->result['errorCode'] = "db";
            $this->result['error'] = "Database error";
        } else {
            $this->result['data'] = $variants;
        }

        $this->returnResult();
    }

    /**
     * Create a new product variant
     */
    public function createVariant()
    {
        $this->checkAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['product_id']) || !isset($data['sku']) || !isset($data['name'])) {
            $this->result['errorCode'] = "invalid";
            $this->result['error'] = "Product ID, SKU, and name are required";
            $this->returnResult();
        }

        $variantData = [
            'product_id' => $data['product_id'],
            'sku' => $data['sku'],
            'barcode' => $data['barcode'] ?? null,
            'name' => $data['name'],
            'price' => $data['price'] ?? 0,
            'cost' => $data['cost'] ?? 0,
            'is_default' => $data['is_default'] ?? 0,
            'is_active' => $data['is_active'] ?? 1,
            'attributes' => $data['attributes'] ?? []
        ];

        $result = $this->variantsModel->create($variantData);
        if ($result === false) {
            $this->result['errorCode'] = "db";
            $this->result['error'] = "Failed to create variant";
        } else {
            $this->result['data'] = ['id' => $result];
        }

        $this->returnResult();
    }

    /**
     * Update a product variant
     */
    public function updateVariant($id)
    {
        $this->checkAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['sku']) || !isset($data['name'])) {
            $this->result['errorCode'] = "invalid";
            $this->result['error'] = "SKU and name are required";
            $this->returnResult();
        }

        $variantData = [
            'sku' => $data['sku'],
            'barcode' => $data['barcode'] ?? null,
            'name' => $data['name'],
            'price' => $data['price'] ?? 0,
            'cost' => $data['cost'] ?? 0,
            'is_default' => $data['is_default'] ?? 0,
            'is_active' => $data['is_active'] ?? 1,
            'attributes' => $data['attributes'] ?? []
        ];

        $result = $this->variantsModel->edit($id, $variantData);
        if ($result === false) {
            $this->result['errorCode'] = "db";
            $this->result['error'] = "Failed to update variant";
        } else {
            $this->result['data'] = ['affected_rows' => $result];
        }

        $this->returnResult();
    }

    /**
     * Delete a product variant
     */
    public function deleteVariant($id)
    {
        $this->checkAuthentication();

        $result = $this->variantsModel->remove($id);
        if ($result === false) {
            $this->result['errorCode'] = "db";
            $this->result['error'] = "Failed to delete variant";
        } else {
            $this->result['data'] = ['affected_rows' => $result];
        }

        $this->returnResult();
    }

    /**
     * Get variant stock levels
     */
    public function getVariantStock($variantId = null, $locationId = null)
    {
        $this->checkAuthentication();

        $stock = $this->stockModel->get($variantId, $locationId);
        if ($stock === false) {
            $this->result['errorCode'] = "db";
            $this->result['error'] = "Database error";
        } else {
            $this->result['data'] = $stock;
        }

        $this->returnResult();
    }

    /**
     * Update variant stock level
     */
    public function updateVariantStock()
    {
        $this->checkAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['variant_id']) || !isset($data['location_id']) || !isset($data['stock_level'])) {
            $this->result['errorCode'] = "invalid";
            $this->result['error'] = "Variant ID, location ID, and stock level are required";
            $this->returnResult();
        }

        $result = $this->stockModel->setStockLevel($data['variant_id'], $data['location_id'], $data['stock_level']);
        if ($result === false) {
            $this->result['errorCode'] = "db";
            $this->result['error'] = "Failed to update stock level";
        } else {
            $this->result['data'] = ['affected_rows' => $result];
        }

        $this->returnResult();
    }

    /**
     * Find variant by attributes
     */
    public function findVariantByAttributes()
    {
        $this->checkAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['product_id']) || !isset($data['attributes'])) {
            $this->result['errorCode'] = "invalid";
            $this->result['error'] = "Product ID and attributes are required";
            $this->returnResult();
        }

        $variant = $this->variantsModel->findByAttributes($data['product_id'], $data['attributes']);
        if ($variant === false) {
            $this->result['errorCode'] = "not_found";
            $this->result['error'] = "Variant not found";
        } else {
            $this->result['data'] = $variant;
        }

        $this->returnResult();
    }

    /**
     * Convert product to variant parent
     */
    public function makeVariantParent($productId)
    {
        $this->checkAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);
        $attributes = $data['attributes'] ?? [];

        $result = $this->itemsModel->makeVariantParent($productId, $attributes);
        if ($result === false) {
            $this->result['errorCode'] = "db";
            $this->result['error'] = "Failed to convert product to variant parent";
        } else {
            $this->result['data'] = ['affected_rows' => $result];
        }

        $this->returnResult();
    }
}