<?php

/**
 * ProductAttributeValuesModel extends the DbConfig PDO class to interact with the product_attribute_values table
 */

namespace App\Database;

class ProductAttributeValuesModel extends DbConfig
{
    /**
     * @var array available columns
     */
    protected $_columns = ['id', 'attribute_id', 'value', 'display_value', 'sort_order', 'created_at', 'updated_at'];

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
        $sql = "INSERT INTO product_attribute_values (`attribute_id`, `value`, `display_value`, `sort_order`) VALUES (:attribute_id, :value, :display_value, :sort_order);";
        $placeholders = [
            ":attribute_id" => $data['attribute_id'],
            ":value" => $data['value'],
            ":display_value" => $data['display_value'],
            ":sort_order" => $data['sort_order'] ?? 0
        ];

        return $this->insert($sql, $placeholders);
    }

    /**
     * @param null $id
     * @param null $attributeId
     * @return array|bool Returns false on an unexpected failure or an array of selected rows
     */
    public function get($id = null, $attributeId = null)
    {
        $sql = 'SELECT v.*, a.display_name as attribute_name FROM product_attribute_values v LEFT JOIN product_attributes a ON v.attribute_id = a.id';
        $placeholders = [];

        if ($id !== null) {
            $sql .= ' WHERE v.id = :id';
            $placeholders[':id'] = $id;
        } elseif ($attributeId !== null) {
            $sql .= ' WHERE v.attribute_id = :attribute_id';
            $placeholders[':attribute_id'] = $attributeId;
        }

        $sql .= ' ORDER BY v.sort_order ASC, v.display_value ASC';

        return $this->select($sql, $placeholders);
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool|int Returns false on an unexpected failure or the number of rows affected by the update operation
     */
    public function edit($id, $data)
    {
        $sql = "UPDATE product_attribute_values SET display_value = :display_value, sort_order = :sort_order, updated_at = CURRENT_TIMESTAMP WHERE id = :id;";
        $placeholders = [
            ":id" => $id,
            ":display_value" => $data['display_value'],
            ":sort_order" => $data['sort_order'] ?? 0
        ];

        return $this->update($sql, $placeholders);
    }

    /**
     * @param integer|array $id
     * @return bool|int Returns false on an unexpected failure or the number of rows affected by the delete operation
     */
    public function remove($id)
    {
        $placeholders = [];
        $sql = "DELETE FROM product_attribute_values WHERE";
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
     * Get all values for multiple attributes
     * @param array $attributeIds
     * @return array|bool
     */
    public function getByAttributes($attributeIds)
    {
        if (empty($attributeIds)) {
            return [];
        }

        $placeholders = [];
        $inClause = str_repeat('?,', count($attributeIds) - 1) . '?';
        $placeholders = $attributeIds;

        $sql = "SELECT v.*, a.display_name as attribute_name FROM product_attribute_values v LEFT JOIN product_attributes a ON v.attribute_id = a.id WHERE v.attribute_id IN ($inClause) ORDER BY v.attribute_id ASC, v.sort_order ASC, v.display_value ASC";

        return $this->select($sql, $placeholders);
    }
}