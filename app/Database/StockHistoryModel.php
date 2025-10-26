<?php

/**
 *
 * StockHistoryModel extends the DbConfig PDO class to interact with the config DB table
 *
 */

namespace App\Database;

class StockHistoryModel extends DbConfig
{

    /**
     * @var array of available columns
     */
    protected $_columns = ['id', 'storeditemid', 'variant_id', 'locationid', 'auxid', 'auxdirection', 'type', 'amount', 'dt'];

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
     * @param $type
     * @param $amount
     * @param $auxid
     * @param int $direction
     * @param int|null $variant_id Optional variant ID
     * @return bool|string Returns false on an unexpected failure, returns -1 if a unique constraint in the database fails, or the new rows id if the insert is successful
     */
    public function create($storeditemid, $locationid, $type, $amount, $auxid = -1, $direction = 0, $variant_id = null)
    {
        $sql = "INSERT INTO stock_history (storeditemid, variant_id, locationid, auxid, auxdir, type, amount, dt) VALUES (:storeditemid, :variant_id, :locationid, :auxid, :auxdir, :type, :amount, '" . date("Y-m-d H:i:s") . "');";
        $placeholders = [":storeditemid" => $storeditemid, ":variant_id" => $variant_id, ":locationid" => $locationid, ":auxid" => $auxid, ":auxdir" => $direction, ":type" => $type, ":amount" => $amount];

        return $this->insert($sql, $placeholders);
    }

    /**
     * @param bool $storeditemid
     * @param bool $locationid
     * @param bool $variant_id Optional variant ID filter
     * @return array|bool Returns an array of results on success, false on failure
     */
    public function get($storeditemid = false, $locationid = false, $variant_id = false)
    {
        $sql = "SELECT h.*, i.name as name, COALESCE(l.name, 'Warehouse') as location, pv.sku AS variant_sku, pv.name_suffix AS variant_name FROM stock_history as h LEFT JOIN stored_items as i ON h.storeditemid=i.id LEFT JOIN locations as l ON h.locationid=l.id LEFT JOIN product_variants as pv ON h.variant_id=pv.id";
        $placeholders = [];
        if ($storeditemid !== false) {
            if (empty($placeholders)) {
                $sql .= ' WHERE';
            }
            $sql .= ' h.storeditemid = :storeditemid';
            $placeholders[':storeditemid'] = $storeditemid;
        }
        if ($locationid !== false) {
            if (empty($placeholders)) {
                $sql .= ' WHERE';
            } else {
                $sql .= ' AND';
            }
            $sql .= ' h.locationid = :locationid';
            $placeholders[':locationid'] = $locationid;
        }
        if ($variant_id !== false) {
            if (empty($placeholders)) {
                $sql .= ' WHERE';
            } else {
                $sql .= ' AND';
            }
            $sql .= ' h.variant_id = :variant_id';
            $placeholders[':variant_id'] = $variant_id;
        }

        return $this->select($sql, $placeholders);
    }

    /**
     * Remove stock history using item ID
     * @param $itemid
     * @return bool|int Returns false on failure or the number of rows affected
     */
    public function removeByItemId($itemid)
    {
        if ($itemid === null) {
            return false;
        }
        $sql = "DELETE FROM stock_history WHERE itemid=:itemid;";
        $placeholders = [":itemid" => $itemid];

        return $this->delete($sql, $placeholders);
    }

    /**
     * Remove stock history using location ID
     * @param $locationid
     * @return bool|int Returns false on failure or the number of rows affected
     */
    public function removeByLocationId($locationid)
    {
        if ($locationid === null) {
            return false;
        }
        $sql          = "DELETE FROM stock_history WHERE locationid=:locationid;";
        $placeholders = [":locationid" => $locationid];

        return $this->delete($sql, $placeholders);
    }
}
