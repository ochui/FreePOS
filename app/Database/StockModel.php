<?php

/**
 *
 * StockModel extends the DbConfig PDO class to interact with the config DB table
 *
 */

namespace App\Database;

class StockModel extends DbConfig
{

    /**
     * @var array
     */
    protected $_columns = ['id', 'storeditemid', 'variant_id', 'locationid', 'stocklevel', 'dt'];

    /**
     * Init the DB
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @param $storeditemid
     * @param $locationid
     * @param $stocklevel
     * @param int|null $variant_id Optional variant ID
     * @return bool|string Returns false on an unexpected failure, returns -1 if a unique constraint in the database fails, or the new rows id if the insert is successful
     */
    public function create($storeditemid, $locationid, $stocklevel, $variant_id = null)
    {
        $sql          = "INSERT INTO stock_levels (`storeditemid`, `variant_id`, `locationid`, `stocklevel`, `dt`) VALUES (:storeditemid, :variant_id, :locationid, :stocklevel, now());";
        $placeholders = [":storeditemid" => $storeditemid, ":variant_id" => $variant_id, ":locationid" => $locationid, ":stocklevel" => $stocklevel];

        return $this->insert($sql, $placeholders);
    }

    /**
     * @param $storeditemid
     * @param $locationid
     * @param $stocklevel
     * @param int|null $variant_id Optional variant ID
     * @return bool|int|string Returns false on failure, number of rows affected or a newly inserted id.
     */
    public function setStockLevel($storeditemid, $locationid, $stocklevel, $variant_id = null)
    {

        $sql = "UPDATE stock_levels SET `stocklevel`=:stocklevel WHERE `storeditemid`=:storeditemid AND `locationid`=:locationid";
        $placeholders = [":storeditemid" => $storeditemid, ":locationid" => $locationid, ":stocklevel" => $stocklevel];
        
        // Add variant condition if provided
        if ($variant_id !== null) {
            $sql .= " AND `variant_id`=:variant_id";
            $placeholders[':variant_id'] = $variant_id;
        } else {
            $sql .= " AND `variant_id` IS NULL";
        }
        
        $result = $this->update($sql, $placeholders);
        if ($result > 0) // if row has been updated, return
            return $result;

        if ($result === false) // if error occured return
            return false;

        // Otherwise add a new stock record, none exists
        return $this->create($storeditemid, $locationid, $stocklevel, $variant_id);
    }

    /**
     * @param $storeditemid
     * @param $locationid
     * @param $amount
     * @param bool $decrement
     * @param int|null $variant_id Optional variant ID
     * @return bool|int|string Returns false on failure, number of rows affected or a newly inserted id.
     */
    public function incrementStockLevel($storeditemid, $locationid, $amount, $decrement = false, $variant_id = null)
    {
        $sql = "UPDATE stock_levels SET `stocklevel`= (`stocklevel` " . ($decrement == true ? '-' : '+') . " :stocklevel) WHERE `storeditemid`=:storeditemid AND `locationid`=:locationid";
        $placeholders = [":storeditemid" => $storeditemid, ":locationid" => $locationid, ":stocklevel" => $amount];
        
        // Add variant condition if provided
        if ($variant_id !== null) {
            $sql .= " AND `variant_id`=:variant_id";
            $placeholders[':variant_id'] = $variant_id;
        } else {
            $sql .= " AND `variant_id` IS NULL";
        }

        $result = $this->update($sql, $placeholders);
        if ($result > 0) return $result;

        if ($result === false) return false;

        if ($decrement === false) { // if adding stock and no record exists, create it
            return $this->create($storeditemid, $locationid, $amount, $variant_id);
        }

        return true;
    }

    /**
     * Returns an array of stock records, optionally including special reporting values
     * @param null $storeditemid
     * @param null $locationid
     * @param bool $report
     * @param null $variant_id Optional variant ID filter
     * @return array|bool Returns false on failure, or an array of stock records
     */
    public function get($storeditemid = null, $locationid = null, $report = false, $variant_id = null)
    {

        $sql = 'SELECT s.*, i.name AS name, COALESCE(p.name, "Misc") AS supplier' . ($report ? ', l.name AS location, i.price*s.stocklevel as stockvalue' : '') . ', pv.sku AS variant_sku, pv.name_suffix AS variant_name FROM stock_levels as s LEFT JOIN stored_items as i ON s.storeditemid=i.id LEFT JOIN stored_suppliers as p ON i.supplierid=p.id LEFT JOIN product_variants as pv ON s.variant_id=pv.id' . ($report ? ' LEFT JOIN locations as l ON s.locationid=l.id' : '');
        $placeholders = [];
        if ($storeditemid !== null) {
            if (empty($placeholders)) {
                $sql .= ' WHERE';
            }
            $sql .= ' s.storeditemid = :storeditemid';
            $placeholders[':storeditemid'] = $storeditemid;
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
        if ($variant_id !== null) {
            if (empty($placeholders)) {
                $sql .= ' WHERE';
            } else {
                $sql .= ' AND';
            }
            $sql .= ' s.variant_id = :variant_id';
            $placeholders[':variant_id'] = $variant_id;
        }

        return $this->select($sql, $placeholders);
    }

    /**
     * Remove stock record by item id.
     * @param $itemid
     * @return bool|int Returns false on failure, or number of records deleted
     */
    public function removeByItemId($itemid)
    {
        if ($itemid === null) {
            return false;
        }
        $sql          = "DELETE FROM stock_levels WHERE itemid=:itemid;";
        $placeholders = [":itemid" => $itemid];

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
        $sql          = "DELETE FROM stock_levels WHERE locationid=:locationid;";
        $placeholders = [":locationid" => $locationid];

        return $this->delete($sql, $placeholders);
    }
}
