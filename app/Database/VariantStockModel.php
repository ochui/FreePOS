<?php

/**
 * VariantStockModel extends the DbConfig PDO class to interact with the variant_stock_levels table
 */

namespace App\Database;

class VariantStockModel extends DbConfig
{
    /**
     * @var array
     */
    protected $_columns = ['id', 'variant_id', 'locationid', 'stocklevel', 'dt'];

    /**
     * Init the DB
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $variantId
     * @param $locationid
     * @param $stocklevel
     * @return bool|string Returns false on an unexpected failure, returns -1 if a unique constraint in the database fails, or the new rows id if the insert is successful
     */
    public function create($variantId, $locationid, $stocklevel)
    {
        $sql = "INSERT INTO variant_stock_levels (`variant_id`, `locationid`, `stocklevel`, `dt`) VALUES (:variant_id, :locationid, :stocklevel, now());";
        $placeholders = [":variant_id" => $variantId, ":locationid" => $locationid, ":stocklevel" => $stocklevel];

        return $this->insert($sql, $placeholders);
    }

    /**
     * @param $variantId
     * @param $locationid
     * @param $stocklevel
     * @return bool|int|string Returns false on failure, number of rows affected or a newly inserted id.
     */
    public function setStockLevel($variantId, $locationid, $stocklevel)
    {
        $sql = "UPDATE variant_stock_levels SET `stocklevel`=:stocklevel WHERE `variant_id`=:variant_id AND `locationid`=:locationid";
        $placeholders = [":variant_id" => $variantId, ":locationid" => $locationid, ":stocklevel" => $stocklevel];
        $result = $this->update($sql, $placeholders);
        if ($result > 0) // if row has been updated, return
            return $result;

        if ($result === false) // if error occured return
            return false;

        // Otherwise add a new stock record, none exists
        return $this->create($variantId, $locationid, $stocklevel);
    }

    /**
     * @param $variantId
     * @param $locationid
     * @param $amount
     * @param bool $decrement
     * @return bool|int|string Returns false on failure, number of rows affected or a newly inserted id.
     */
    public function incrementStockLevel($variantId, $locationid, $amount, $decrement = false)
    {
        $sql = "UPDATE variant_stock_levels SET `stocklevel`= (`stocklevel` " . ($decrement == true ? '-' : '+') . " :stocklevel) WHERE `variant_id`=:variant_id AND `locationid`=:locationid";
        $placeholders = [":variant_id" => $variantId, ":locationid" => $locationid, ":stocklevel" => $amount];

        $result = $this->update($sql, $placeholders);
        if ($result > 0) return $result;

        if ($result === false) return false;

        if ($decrement === false) { // if adding stock and no record exists, create it
            return $this->create($variantId, $locationid, $amount);
        }

        return true;
    }

    /**
     * Returns an array of stock records, optionally including special reporting values
     * @param null $variantId
     * @param null $locationid
     * @param bool $report
     * @return array|bool Returns false on failure, or an array of stock records
     */
    public function get($variantId = null, $locationid = null, $report = false)
    {
        $sql = 'SELECT s.*, v.name AS variant_name, v.sku, p.name AS product_name, COALESCE(sup.name, "Misc") AS supplier' . ($report ? ', l.name AS location, v.price*s.stocklevel as stockvalue' : '') . ' FROM variant_stock_levels as s LEFT JOIN product_variants as v ON s.variant_id=v.id LEFT JOIN stored_items as p ON v.product_id=p.id LEFT JOIN stored_suppliers as sup ON p.supplierid=sup.id' . ($report ? ' LEFT JOIN locations as l ON s.locationid=l.id' : '');
        $placeholders = [];
        if ($variantId !== null) {
            if (empty($placeholders)) {
                $sql .= ' WHERE';
            }
            $sql .= ' s.variant_id = :variant_id';
            $placeholders[':variant_id'] = $variantId;
        }
        if ($locationid !== null) {
            if (empty($placeholders)) {
                $sql .= ' WHERE';
            } else {
                $sql .= ' AND';
            }
            $sql .= ' s.locationid = :locationid';
            $placeholders[':locationid'] = $locationid;
        }

        return $this->select($sql, $placeholders);
    }

    /**
     * Get total stock for a product across all variants and locations
     * @param int $productId
     * @param null $locationid
     * @return int
     */
    public function getProductTotalStock($productId, $locationid = null)
    {
        $sql = 'SELECT SUM(s.stocklevel) as total_stock FROM variant_stock_levels s LEFT JOIN product_variants v ON s.variant_id = v.id WHERE v.product_id = :product_id AND v.is_active = 1';
        $placeholders = [':product_id' => $productId];

        if ($locationid !== null) {
            $sql .= ' AND s.locationid = :locationid';
            $placeholders[':locationid'] = $locationid;
        }

        $result = $this->select($sql, $placeholders);

        return $result && isset($result[0]['total_stock']) ? (int)$result[0]['total_stock'] : 0;
    }

    /**
     * Remove stock record by variant id.
     * @param $variantId
     * @return bool|int Returns false on failure, or number of records deleted
     */
    public function removeByVariantId($variantId)
    {
        if ($variantId === null) {
            return false;
        }
        $sql = "DELETE FROM variant_stock_levels WHERE variant_id=:variant_id;";
        $placeholders = [":variant_id" => $variantId];

        return $this->delete($sql, $placeholders);
    }

    /**
     * Remove stock record by location id.
     * @param $locationid
     * @return bool|int Returns false on failure, or number of records deleted
     */
    public function removeByLocationId($locationid)
    {
        if ($locationid === null) {
            return false;
        }
        $sql = "DELETE FROM variant_stock_levels WHERE locationid=:locationid;";
        $placeholders = [":locationid" => $locationid];

        return $this->delete($sql, $placeholders);
    }
}