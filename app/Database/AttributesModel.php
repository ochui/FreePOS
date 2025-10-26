<?php

/**
 *
 * AttributesModel extends the DbConfig PDO class to interact with attributes and attribute_values tables
 *
 */

namespace App\Database;

class AttributesModel extends DbConfig
{

    /**
     * Init DB
     */
    public function __construct()
    {
        parent::__construct();
    }

    // ========================================
    // ATTRIBUTES METHODS
    // ========================================

    /**
     * Create a new attribute
     * @param array $data Attribute data (name, code, sort_order)
     * @return bool|string Returns false on failure, -1 on unique constraint violation, or new row id on success
     */
    public function createAttribute($data)
    {
        $sql = "INSERT INTO attributes (`name`, `code`, `sort_order`) 
                VALUES (:name, :code, :sort_order);";
        
        $placeholders = [
            ":name" => $data['name'],
            ":code" => $data['code'],
            ":sort_order" => isset($data['sort_order']) ? $data['sort_order'] : 0
        ];

        return $this->insert($sql, $placeholders);
    }

    /**
     * Get attributes
     * @param int|null $id Attribute ID
     * @param string|null $code Attribute code
     * @return array|bool Returns false on failure or array of attributes
     */
    public function getAttributes($id = null, $code = null)
    {
        $sql = 'SELECT * FROM attributes';
        $placeholders = [];
        $conditions = [];

        if ($id !== null) {
            $conditions[] = 'id = :id';
            $placeholders[':id'] = $id;
        }
        if ($code !== null) {
            $conditions[] = 'code = :code';
            $placeholders[':code'] = $code;
        }

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY sort_order, name';

        return $this->select($sql, $placeholders);
    }

    /**
     * Get a single attribute by ID
     * @param int $id Attribute ID
     * @return array|bool|null Returns attribute data or null if not found
     */
    public function getAttributeById($id)
    {
        $attributes = $this->getAttributes($id);
        return is_array($attributes) && count($attributes) > 0 ? $attributes[0] : null;
    }

    /**
     * Update an attribute
     * @param int $id Attribute ID
     * @param array $data Data to update
     * @return bool|int Returns false on failure or number of rows affected
     */
    public function editAttribute($id, $data)
    {
        $updates = [];
        $placeholders = [':id' => $id];

        if (isset($data['name'])) {
            $updates[] = 'name = :name';
            $placeholders[':name'] = $data['name'];
        }
        if (isset($data['code'])) {
            $updates[] = 'code = :code';
            $placeholders[':code'] = $data['code'];
        }
        if (isset($data['sort_order'])) {
            $updates[] = 'sort_order = :sort_order';
            $placeholders[':sort_order'] = $data['sort_order'];
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE attributes SET " . implode(', ', $updates) . " WHERE id = :id;";
        return $this->update($sql, $placeholders);
    }

    /**
     * Delete an attribute
     * @param int $id Attribute ID
     * @return bool|int Returns false on failure or number of rows affected
     */
    public function removeAttribute($id)
    {
        $sql = "DELETE FROM attributes WHERE id = :id;";
        return $this->delete($sql, [':id' => $id]);
    }

    // ========================================
    // ATTRIBUTE VALUES METHODS
    // ========================================

    /**
     * Create a new attribute value
     * @param array $data Attribute value data (attribute_id, value, code, sort_order)
     * @return bool|string Returns false on failure, -1 on unique constraint violation, or new row id on success
     */
    public function createAttributeValue($data)
    {
        $sql = "INSERT INTO attribute_values (`attribute_id`, `value`, `code`, `sort_order`) 
                VALUES (:attribute_id, :value, :code, :sort_order);";
        
        $placeholders = [
            ":attribute_id" => $data['attribute_id'],
            ":value" => $data['value'],
            ":code" => $data['code'],
            ":sort_order" => isset($data['sort_order']) ? $data['sort_order'] : 0
        ];

        return $this->insert($sql, $placeholders);
    }

    /**
     * Get attribute values
     * @param int|null $id Attribute value ID
     * @param int|null $attribute_id Attribute ID (to get all values for an attribute)
     * @return array|bool Returns false on failure or array of attribute values
     */
    public function getAttributeValues($id = null, $attribute_id = null)
    {
        $sql = 'SELECT av.*, a.name AS attribute_name, a.code AS attribute_code 
                FROM attribute_values av
                LEFT JOIN attributes a ON av.attribute_id = a.id';
        $placeholders = [];
        $conditions = [];

        if ($id !== null) {
            $conditions[] = 'av.id = :id';
            $placeholders[':id'] = $id;
        }
        if ($attribute_id !== null) {
            $conditions[] = 'av.attribute_id = :attribute_id';
            $placeholders[':attribute_id'] = $attribute_id;
        }

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY av.attribute_id, av.sort_order, av.value';

        return $this->select($sql, $placeholders);
    }

    /**
     * Get a single attribute value by ID
     * @param int $id Attribute value ID
     * @return array|bool|null Returns attribute value data or null if not found
     */
    public function getAttributeValueById($id)
    {
        $values = $this->getAttributeValues($id);
        return is_array($values) && count($values) > 0 ? $values[0] : null;
    }

    /**
     * Update an attribute value
     * @param int $id Attribute value ID
     * @param array $data Data to update
     * @return bool|int Returns false on failure or number of rows affected
     */
    public function editAttributeValue($id, $data)
    {
        $updates = [];
        $placeholders = [':id' => $id];

        if (isset($data['value'])) {
            $updates[] = 'value = :value';
            $placeholders[':value'] = $data['value'];
        }
        if (isset($data['code'])) {
            $updates[] = 'code = :code';
            $placeholders[':code'] = $data['code'];
        }
        if (isset($data['sort_order'])) {
            $updates[] = 'sort_order = :sort_order';
            $placeholders[':sort_order'] = $data['sort_order'];
        }

        if (empty($updates)) {
            return false;
        }

        $sql = "UPDATE attribute_values SET " . implode(', ', $updates) . " WHERE id = :id;";
        return $this->update($sql, $placeholders);
    }

    /**
     * Delete an attribute value
     * @param int $id Attribute value ID
     * @return bool|int Returns false on failure or number of rows affected
     */
    public function removeAttributeValue($id)
    {
        $sql = "DELETE FROM attribute_values WHERE id = :id;";
        return $this->delete($sql, [':id' => $id]);
    }

    // ========================================
    // COMBINED METHODS
    // ========================================

    /**
     * Get all attributes with their values
     * @return array Array of attributes with their values
     */
    public function getAttributesWithValues()
    {
        $attributes = $this->getAttributes();
        if (!is_array($attributes)) {
            return [];
        }

        foreach ($attributes as $key => $attribute) {
            $attributes[$key]['values'] = $this->getAttributeValues(null, $attribute['id']);
        }

        return $attributes;
    }

    /**
     * Bulk create attribute with values
     * @param string $attribute_name Attribute name
     * @param string $attribute_code Attribute code
     * @param array $values Array of value strings
     * @return bool|int Returns attribute ID on success, false on failure
     */
    public function createAttributeWithValues($attribute_name, $attribute_code, $values)
    {
        // Create the attribute
        $attribute_id = $this->createAttribute([
            'name' => $attribute_name,
            'code' => $attribute_code,
            'sort_order' => 0
        ]);

        if (!$attribute_id || $attribute_id === false) {
            return false;
        }

        // Create values
        $sort_order = 0;
        foreach ($values as $value) {
            $this->createAttributeValue([
                'attribute_id' => $attribute_id,
                'value' => $value,
                'code' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $value)),
                'sort_order' => $sort_order++
            ]);
        }

        return $attribute_id;
    }

    /**
     * Get attribute combinations for variant generation
     * @param array $attribute_ids Array of attribute IDs
     * @return array Array of attribute value combinations
     */
    public function generateAttributeCombinations($attribute_ids)
    {
        if (empty($attribute_ids)) {
            return [];
        }

        // Get values for each attribute
        $attributeValueSets = [];
        foreach ($attribute_ids as $attr_id) {
            $values = $this->getAttributeValues(null, $attr_id);
            if (is_array($values) && !empty($values)) {
                $attributeValueSets[] = $values;
            }
        }

        if (empty($attributeValueSets)) {
            return [];
        }

        // Generate all combinations (cartesian product)
        $combinations = [[]];
        foreach ($attributeValueSets as $valueSet) {
            $newCombinations = [];
            foreach ($combinations as $combination) {
                foreach ($valueSet as $value) {
                    $newCombinations[] = array_merge($combination, [$value]);
                }
            }
            $combinations = $newCombinations;
        }

        return $combinations;
    }
}
