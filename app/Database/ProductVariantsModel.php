<?php

/**
 *
 * ProductVariantsModel extends the DbConfig PDO class to interact with the product_variants table
 *
 */

namespace App\Database;

class ProductVariantsModel extends DbConfig
{

    /**
     * @var array available columns
     */
    protected $_columns = ['id', 'product_id', 'sku', 'barcode', 'name_suffix', 'price', 'cost', 'is_default', 'is_active', 'sort_order', 'data', 'created_at', 'updated_at'];

    /**
     * Init DB
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create a new product variant
     * @param array $data Variant data
     * @return bool|string Returns false on failure, -1 on unique constraint violation, or new row id on success
     */
    public function create($data)
    {
        $sql = "INSERT INTO product_variants (`product_id`, `sku`, `barcode`, `name_suffix`, `price`, `cost`, `is_default`, `is_active`, `sort_order`, `data`) 
                VALUES (:product_id, :sku, :barcode, :name_suffix, :price, :cost, :is_default, :is_active, :sort_order, :data);";
        
        $placeholders = [
            ":product_id" => $data['product_id'],
            ":sku" => $data['sku'],
            ":barcode" => isset($data['barcode']) ? $data['barcode'] : null,
            ":name_suffix" => isset($data['name_suffix']) ? $data['name_suffix'] : '',
            ":price" => isset($data['price']) ? $data['price'] : 0.00,
            ":cost" => isset($data['cost']) ? $data['cost'] : 0.00,
            ":is_default" => isset($data['is_default']) ? $data['is_default'] : 0,
            ":is_active" => isset($data['is_active']) ? $data['is_active'] : 1,
            ":sort_order" => isset($data['sort_order']) ? $data['sort_order'] : 0,
            ":data" => isset($data['data']) ? (is_array($data['data']) ? json_encode($data['data']) : $data['data']) : '{}'
        ];

        return $this->insert($sql, $placeholders);
    }

    /**
     * Get product variants
     * @param int|null $id Variant ID
     * @param int|null $product_id Product ID
     * @param string|null $sku SKU code
     * @param string|null $barcode Barcode
     * @param bool $active_only Only return active variants
     * @return array|bool Returns false on failure or array of variants
     */
    public function get($id = null, $product_id = null, $sku = null, $barcode = null, $active_only = false)
    {
        $sql = 'SELECT pv.*, si.name AS product_name, si.code AS product_code 
                FROM product_variants pv 
                LEFT JOIN stored_items si ON pv.product_id = si.id';
        $placeholders = [];
        $conditions = [];

        if ($id !== null) {
            $conditions[] = 'pv.id = :id';
            $placeholders[':id'] = $id;
        }
        if ($product_id !== null) {
            $conditions[] = 'pv.product_id = :product_id';
            $placeholders[':product_id'] = $product_id;
        }
        if ($sku !== null) {
            $conditions[] = 'pv.sku = :sku';
            $placeholders[':sku'] = $sku;
        }
        if ($barcode !== null) {
            $conditions[] = 'pv.barcode = :barcode';
            $placeholders[':barcode'] = $barcode;
        }
        if ($active_only) {
            $conditions[] = 'pv.is_active = 1';
        }

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY pv.product_id, pv.is_default DESC, pv.sort_order, pv.id';

        $variants = $this->select($sql, $placeholders);
        if ($variants === false) {
            return false;
        }

        // Decode JSON data field
        foreach ($variants as $key => $variant) {
            $variants[$key]['data'] = json_decode($variant['data'], true);
        }

        return $variants;
    }

    /**
     * Get a single variant by ID
     * @param int $id Variant ID
     * @return array|bool|null Returns variant data or null if not found
     */
    public function getById($id)
    {
        $variants = $this->get($id);
        return is_array($variants) && count($variants) > 0 ? $variants[0] : null;
    }

    /**
     * Find variant by SKU or barcode
     * @param string $code SKU or barcode to search
     * @return array|bool|null Returns variant data or null if not found
     */
    public function findByCode($code)
    {
        // Try SKU first
        $variants = $this->get(null, null, $code);
        if (is_array($variants) && count($variants) > 0) {
            return $variants[0];
        }

        // Try barcode
        $variants = $this->get(null, null, null, $code);
        if (is_array($variants) && count($variants) > 0) {
            return $variants[0];
        }

        return null;
    }

    /**
     * Get default variant for a product
     * @param int $product_id Product ID
     * @return array|bool|null Returns default variant or null if not found
     */
    public function getDefaultVariant($product_id)
    {
        $sql = 'SELECT pv.*, si.name AS product_name, si.code AS product_code 
                FROM product_variants pv 
                LEFT JOIN stored_items si ON pv.product_id = si.id
                WHERE pv.product_id = :product_id AND pv.is_default = 1 
                LIMIT 1';
        
        $variants = $this->select($sql, [':product_id' => $product_id]);
        if (is_array($variants) && count($variants) > 0) {
            $variants[0]['data'] = json_decode($variants[0]['data'], true);
            return $variants[0];
        }

        return null;
    }

    /**
     * Update a product variant
     * @param int $id Variant ID
     * @param array $data Data to update
     * @return bool|int Returns false on failure or number of rows affected
     */
    public function edit($id, $data)
    {
        $updates = [];
        $placeholders = [':id' => $id];

        if (isset($data['sku'])) {
            $updates[] = 'sku = :sku';
            $placeholders[':sku'] = $data['sku'];
        }
        if (isset($data['barcode'])) {
            $updates[] = 'barcode = :barcode';
            $placeholders[':barcode'] = $data['barcode'];
        }
        if (isset($data['name_suffix'])) {
            $updates[] = 'name_suffix = :name_suffix';
            $placeholders[':name_suffix'] = $data['name_suffix'];
        }
        if (isset($data['price'])) {
            $updates[] = 'price = :price';
            $placeholders[':price'] = $data['price'];
        }
        if (isset($data['cost'])) {
            $updates[] = 'cost = :cost';
            $placeholders[':cost'] = $data['cost'];
        }
        if (isset($data['is_active'])) {
            $updates[] = 'is_active = :is_active';
            $placeholders[':is_active'] = $data['is_active'];
        }
        if (isset($data['sort_order'])) {
            $updates[] = 'sort_order = :sort_order';
            $placeholders[':sort_order'] = $data['sort_order'];
        }
        if (isset($data['data'])) {
            $updates[] = 'data = :data';
            $placeholders[':data'] = is_array($data['data']) ? json_encode($data['data']) : $data['data'];
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE product_variants SET " . implode(', ', $updates) . " WHERE id = :id;";
        return $this->update($sql, $placeholders);
    }

    /**
     * Delete a product variant
     * @param int $id Variant ID
     * @return bool|int Returns false on failure or number of rows affected
     */
    public function remove($id)
    {
        // Don't allow deleting the default variant if it's the only one
        $variant = $this->getById($id);
        if ($variant && $variant['is_default'] == 1) {
            $allVariants = $this->get(null, $variant['product_id']);
            if (count($allVariants) == 1) {
                return false; // Cannot delete the only variant
            }
        }

        $sql = "DELETE FROM product_variants WHERE id = :id;";
        return $this->delete($sql, [':id' => $id]);
    }

    /**
     * Get variants with their attribute values
     * @param int $product_id Product ID
     * @return array|bool Returns variants with attributes or false on failure
     */
    public function getVariantsWithAttributes($product_id)
    {
        $variants = $this->get(null, $product_id);
        if (!is_array($variants)) {
            return false;
        }

        foreach ($variants as $key => $variant) {
            $variants[$key]['attributes'] = $this->getVariantAttributes($variant['id']);
        }

        return $variants;
    }

    /**
     * Get attribute values for a specific variant
     * @param int $variant_id Variant ID
     * @return array Attribute values for the variant
     */
    public function getVariantAttributes($variant_id)
    {
        $sql = 'SELECT a.id AS attribute_id, a.name AS attribute_name, a.code AS attribute_code,
                       av.id AS value_id, av.value, av.code AS value_code
                FROM product_variant_attribute_values pvav
                INNER JOIN attribute_values av ON pvav.attribute_value_id = av.id
                INNER JOIN attributes a ON av.attribute_id = a.id
                WHERE pvav.variant_id = :variant_id
                ORDER BY a.sort_order, av.sort_order';
        
        $attributes = $this->select($sql, [':variant_id' => $variant_id]);
        return is_array($attributes) ? $attributes : [];
    }

    /**
     * Link attribute values to a variant
     * @param int $variant_id Variant ID
     * @param array $attribute_value_ids Array of attribute value IDs
     * @return bool Success status
     */
    public function setVariantAttributes($variant_id, $attribute_value_ids)
    {
        // First, remove existing attribute assignments
        $sql = "DELETE FROM product_variant_attribute_values WHERE variant_id = :variant_id";
        $this->delete($sql, [':variant_id' => $variant_id]);

        // Then add new assignments
        if (!empty($attribute_value_ids)) {
            $sql = "INSERT INTO product_variant_attribute_values (variant_id, attribute_value_id) VALUES ";
            $values = [];
            foreach ($attribute_value_ids as $av_id) {
                $values[] = "(" . intval($variant_id) . ", " . intval($av_id) . ")";
            }
            $sql .= implode(', ', $values);
            
            return $this->insert($sql, []) !== false;
        }

        return true;
    }

    /**
     * Get variant count for a product
     * @param int $product_id Product ID
     * @return int Number of variants
     */
    public function getVariantCount($product_id)
    {
        $sql = 'SELECT COUNT(*) AS count FROM product_variants WHERE product_id = :product_id';
        $result = $this->select($sql, [':product_id' => $product_id]);
        return (is_array($result) && count($result) > 0) ? intval($result[0]['count']) : 0;
    }
}
