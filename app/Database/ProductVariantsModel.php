<?php

/**
 * ProductVariantsModel extends the DbConfig PDO class to interact with the product_variants table
 */

namespace App\Database;

class ProductVariantsModel extends DbConfig
{
    /**
     * @var array available columns
     */
    protected $_columns = ['id', 'product_id', 'sku', 'barcode', 'name', 'price', 'cost', 'is_default', 'is_active', 'created_at', 'updated_at'];

    /**
     * Init DB
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param array $data
     * @return bool|string Returns false on an unexpected failure, returns -1 if a unique constraint in the database fails, or the new rows id if the insert is successful
     */
    public function create($data)
    {
        $sql = "INSERT INTO product_variants (`product_id`, `sku`, `barcode`, `name`, `price`, `cost`, `is_default`, `is_active`) VALUES (:product_id, :sku, :barcode, :name, :price, :cost, :is_default, :is_active);";
        $placeholders = [
            ":product_id" => $data['product_id'],
            ":sku" => $data['sku'],
            ":barcode" => $data['barcode'] ?? null,
            ":name" => $data['name'],
            ":price" => $data['price'],
            ":cost" => $data['cost'] ?? 0,
            ":is_default" => $data['is_default'] ?? 0,
            ":is_active" => $data['is_active'] ?? 1
        ];

        $variantId = $this->insert($sql, $placeholders);

        if ($variantId && isset($data['attributes']) && is_array($data['attributes'])) {
            $this->setVariantAttributes($variantId, $data['attributes']);
        }

        return $variantId;
    }

    /**
     * @param null $id
     * @param null $productId
     * @param null $sku
     * @param null $barcode
     * @return array|bool Returns false on an unexpected failure or an array of selected rows
     */
    public function get($id = null, $productId = null, $sku = null, $barcode = null)
    {
        $sql = 'SELECT v.*, p.name as product_name FROM product_variants v LEFT JOIN stored_items p ON v.product_id = p.id';
        $placeholders = [];

        if ($id !== null) {
            $sql .= ' WHERE v.id = :id';
            $placeholders[':id'] = $id;
        } elseif ($productId !== null) {
            $sql .= ' WHERE v.product_id = :product_id';
            $placeholders[':product_id'] = $productId;
        } elseif ($sku !== null) {
            $sql .= ' WHERE v.sku = :sku';
            $placeholders[':sku'] = $sku;
        } elseif ($barcode !== null) {
            $sql .= ' WHERE v.barcode = :barcode';
            $placeholders[':barcode'] = $barcode;
        }

        $sql .= ' AND v.is_active = 1 ORDER BY v.is_default DESC, v.name ASC';

        $variants = $this->select($sql, $placeholders);

        if ($variants && is_array($variants)) {
            foreach ($variants as $key => $variant) {
                $variants[$key]['attributes'] = $this->getVariantAttributes($variant['id']);
            }
        }

        return $variants;
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool|int Returns false on an unexpected failure or the number of rows affected by the update operation
     */
    public function edit($id, $data)
    {
        $sql = "UPDATE product_variants SET sku = :sku, barcode = :barcode, name = :name, price = :price, cost = :cost, is_default = :is_default, is_active = :is_active, updated_at = CURRENT_TIMESTAMP WHERE id = :id;";
        $placeholders = [
            ":id" => $id,
            ":sku" => $data['sku'],
            ":barcode" => $data['barcode'] ?? null,
            ":name" => $data['name'],
            ":price" => $data['price'],
            ":cost" => $data['cost'] ?? 0,
            ":is_default" => $data['is_default'] ?? 0,
            ":is_active" => $data['is_active'] ?? 1
        ];

        $result = $this->update($sql, $placeholders);

        if ($result && isset($data['attributes']) && is_array($data['attributes'])) {
            $this->setVariantAttributes($id, $data['attributes']);
        }

        return $result;
    }

    /**
     * @param integer|array $id
     * @return bool|int Returns false on an unexpected failure or the number of rows affected by the delete operation
     */
    public function remove($id)
    {
        $placeholders = [];
        $sql = "DELETE FROM product_variants WHERE";
        if (is_numeric($id)) {
            $sql .= " `id`=:id;";
            $placeholders[":id"] = $id;
        } else if (is_array($id)) {
            $id = array_map([$this->_db, 'quote'], $id);
            $sql .= " `id` IN (" . implode(', ', $id) . ");";
        } else {
            return false;
        }

        return $this->delete($sql, $placeholders);
    }

    /**
     * Set variant attributes
     * @param int $variantId
     * @param array $attributes Array of attribute_id => attribute_value_id
     * @return bool
     */
    public function setVariantAttributes($variantId, $attributes)
    {
        // First remove existing attributes
        $this->removeVariantAttributes($variantId);

        // Then add new ones
        foreach ($attributes as $attributeId => $valueId) {
            $sql = "INSERT INTO variant_attributes (`variant_id`, `attribute_id`, `attribute_value_id`) VALUES (:variant_id, :attribute_id, :value_id);";
            $placeholders = [
                ":variant_id" => $variantId,
                ":attribute_id" => $attributeId,
                ":value_id" => $valueId
            ];
            $this->insert($sql, $placeholders);
        }

        return true;
    }

    /**
     * Get variant attributes
     * @param int $variantId
     * @return array
     */
    public function getVariantAttributes($variantId)
    {
        $sql = "SELECT va.*, a.name as attribute_name, a.display_name as attribute_display_name, av.value, av.display_value FROM variant_attributes va LEFT JOIN product_attributes a ON va.attribute_id = a.id LEFT JOIN product_attribute_values av ON va.attribute_value_id = av.id WHERE va.variant_id = :variant_id ORDER BY a.sort_order ASC";
        $placeholders = [":variant_id" => $variantId];

        return $this->select($sql, $placeholders);
    }

    /**
     * Remove all attributes for a variant
     * @param int $variantId
     * @return bool|int
     */
    private function removeVariantAttributes($variantId)
    {
        $sql = "DELETE FROM variant_attributes WHERE variant_id = :variant_id;";
        $placeholders = [":variant_id" => $variantId];

        return $this->delete($sql, $placeholders);
    }

    /**
     * Find variant by attribute combination
     * @param int $productId
     * @param array $attributes Array of attribute_id => attribute_value_id
     * @return array|bool
     */
    public function findByAttributes($productId, $attributes)
    {
        if (empty($attributes)) {
            return false;
        }

        // Build query to find variant with exact attribute combination
        $whereConditions = [];
        $placeholders = [":product_id" => $productId];

        foreach ($attributes as $attrId => $valueId) {
            $whereConditions[] = "EXISTS (SELECT 1 FROM variant_attributes va WHERE va.variant_id = v.id AND va.attribute_id = :attr_{$attrId} AND va.attribute_value_id = :value_{$attrId})";
            $placeholders[":attr_{$attrId}"] = $attrId;
            $placeholders[":value_{$attrId}"] = $valueId;
        }

        $sql = "SELECT v.* FROM product_variants v WHERE v.product_id = :product_id AND (" . implode(' AND ', $whereConditions) . ") AND v.is_active = 1";

        $variants = $this->select($sql, $placeholders);

        if ($variants && count($variants) > 0) {
            $variant = $variants[0];
            $variant['attributes'] = $this->getVariantAttributes($variant['id']);
            return $variant;
        }

        return false;
    }

    /**
     * Get default variant for a product
     * @param int $productId
     * @return array|bool
     */
    public function getDefaultVariant($productId)
    {
        $sql = "SELECT * FROM product_variants WHERE product_id = :product_id AND is_default = 1 AND is_active = 1 LIMIT 1";
        $placeholders = [":product_id" => $productId];

        $variants = $this->select($sql, $placeholders);

        if ($variants && count($variants) > 0) {
            $variant = $variants[0];
            $variant['attributes'] = $this->getVariantAttributes($variant['id']);
            return $variant;
        }

        return false;
    }
}