<?php

/**
 * ProductAttributesModel extends the DbConfig PDO class to interact with the product_attributes table
 */

namespace App\Database;

class ProductAttributesModel extends DbConfig
{
    /**
     * @var array available columns
     */
    protected $_columns = ['id', 'name', 'display_name', 'sort_order', 'created_at', 'updated_at'];

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
        $sql = "INSERT INTO product_attributes (`name`, `display_name`, `sort_order`) VALUES (:name, :display_name, :sort_order);";
        $placeholders = [
            ":name" => $data['name'],
            ":display_name" => $data['display_name'],
            ":sort_order" => $data['sort_order'] ?? 0
        ];

        return $this->insert($sql, $placeholders);
    }

    /**
     * @param null $id
     * @return array|bool Returns false on an unexpected failure or an array of selected rows
     */
    public function get($id = null)
    {
        $sql = 'SELECT * FROM product_attributes';
        $placeholders = [];

        if ($id !== null) {
            $sql .= ' WHERE id = :id';
            $placeholders[':id'] = $id;
        }

        $sql .= ' ORDER BY sort_order ASC, display_name ASC';

        return $this->select($sql, $placeholders);
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool|int Returns false on an unexpected failure or the number of rows affected by the update operation
     */
    public function edit($id, $data)
    {
        $sql = "UPDATE product_attributes SET display_name = :display_name, sort_order = :sort_order, updated_at = CURRENT_TIMESTAMP WHERE id = :id;";
        $placeholders = [
            ":id" => $id,
            ":display_name" => $data['display_name'],
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
        $sql = "DELETE FROM product_attributes WHERE";
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
}