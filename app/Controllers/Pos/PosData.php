<?php

/**
 *
 * JsonData is used for retrieving database tables into JSON for use by the pos client.
 * The device,location and tax functions are no longer used much as Setup now provides these values alongside the config.
 *
 */

namespace App\Controllers\Pos;

use App\Controllers\Admin\AdminSettings;
use App\Database\AuthModel;
use App\Database\CategoriesModel;
use App\Database\CustomerModel;
use App\Database\DevicesModel;
use App\Database\LocationsModel;
use App\Database\SalesModel;
use App\Database\StockModel;
use App\Database\StoredItemsModel;
use App\Database\SuppliersModel;
use App\Database\TaxItemsModel;
use App\Database\TaxRulesModel;
use App\Database\ProductVariantsModel;
use App\Database\AttributesModel;
use App\Utility\VariantsHelper;


class PosData
{
    // these variables will determine which records to provide when requesting sales
    /**
     * @var int deviceId
     */
    var $devid;
    /**
     * @var int locationId
     */
    var $locid;

    /**
     * @var mixed JSON object
     */
    var $data;

    /**
     * Decodes any provided JSON string
     */
    public function __construct($jsondata = null)
    {
        if ($jsondata !== null) {
            if (is_string($jsondata)) {
                $this->data = json_decode($jsondata);
            } else {
                $this->data = $jsondata;
            }
        }
    }

    /**
     * @param array $result
     *
     * @return array of customer records
     */
    public function getCustomers($result)
    {
        $customersMdl = new CustomerModel();
        $customers    = $customersMdl->get();
        $contacts = $customersMdl->getContacts();
        if (is_array($customers)) {
            $cdata = [];
            foreach ($customers as $customer) {
                $customer['contacts'] = [];
                $cdata[$customer['id']] = $customer;
            }
            // add custoner contacts
            foreach ($contacts as $contact) {
                if (isset($cdata[$contact['customerid']])) {
                    $cdata[$contact['customerid']]['contacts'][$contact['id']] = $contact;
                }
            }
            $result['data'] = $cdata;
        } else {
            $result['error'] = $customersMdl->errorInfo;
        }

        return $result;
    }

    /**
     * @param array $result
     *
     * @return array of stored item records
     */
    public function getItems($result)
    {
        $storedItemsMdl = new StoredItemsModel();
        $storedItems    = $storedItemsMdl->get();
        if (is_array($storedItems)) {
            $items = [];
            foreach ($storedItems as $storedItem) {

                $items[$storedItem['id']] = $storedItem;
            }
            $result['data'] = $items;
        } else {
            $result['error'] = $storedItemsMdl->errorInfo;
        }

        return $result;
    }

    /**
     * @param array $result
     *
     * @return array of POS device records
     */
    public function getPosDevices($result)
    {
        $devMdl  = new DevicesModel();
        $devices = $devMdl->get();
        if (is_array($devices)) {
            $data = [];
            foreach ($devices as $device) {
                $data[$device['id']] =  $device;
            }
            $data[0] = ["id" => 0, "name" => "Admin dash", "locationname" => "Admin dash", "locationid" => 0];
            $result['data'] = $data;
        } else {
            $result['error'] = $devMdl->errorInfo;
        }

        return $result;
    }

    /**
     * @param array $result
     *
     * @return array of POS location records
     */
    public function getPosLocations($result)
    {
        $locMdl    = new LocationsModel();
        $locations = $locMdl->get();
        if (is_array($locations)) {
            $data = [];
            foreach ($locations as $location) {
                $data[$location['id']] = $location;
            }
            $data[0] = ["id" => 0, "name" => "Admin dash"];
            $result['data'] = $data;
        } else {
            $result['error'] = $locMdl->errorInfo;
        }

        return $result;
    }

    /**
     * @param $result
     * @return mixed an array of users without their password hash
     */
    public function getUsers($result)
    {
        $authMdl = new AuthModel();
        $users = $authMdl->get();
        $data = [];
        foreach ($users as $user) {
            unset($user['password']);
            $user['permissions'] = json_decode($user['permissions']);
            $data[$user['id']] = $user;
        }
        $result['data'] = $data;
        return $result;
    }

    /**
     * If stime & etime are not set, This function returns sales using the provided devices ID, using POS configuration values.
     *
     * @param $result
     * @return mixed
     */
    public function getSales($result)
    {
        if (!isset($this->data->stime) && !isset($this->data->etime)) {
            // time not set, retrieving POS records, get config.
            $Config = new AdminSettings();
            $config = $Config->getSettingsObject("pos");

            // set the sale range based on the config setting
            $etime = time() * 1000;
            $stime = strtotime("-1 " . (isset($config->salerange) ? $config->salerange : "week")) * 1000;

            // determine which devices transactions to include based on config
            if (isset($this->data->deviceid)) {
                switch ($config->saledevice) {
                    case "device":
                        break; // no need to do anything, id already set
                    case "all":
                        unset($this->data->deviceid); // unset the device id to get all sales
                        break;
                    case "location":
                        // get location device id array
                        $devMdl = new DevicesModel();
                        $this->data->deviceid = $devMdl->getLocationDeviceIds($this->data->deviceid);
                }
            }
        } else {
            $stime = $this->data->stime;
            $etime = $this->data->etime;
        }

        // Get all transactions within the specified timeframe/devices
        $salesMdl = new SalesModel();
        $dbSales  = $salesMdl->getRangeWithRefunds($stime, $etime, (isset($this->data->deviceid) ? $this->data->deviceid : null));

        if (is_array($dbSales)) {
            $sales = [];
            foreach ($dbSales as $sale) {
                $salejson = json_decode($sale['data']);
                $salejson->type = $sale['type'];
                $sales[$sale['ref']] = $salejson;
            }
            $result['data'] = $sales;
        } else if ($dbSales === false) {
            $result['error'] = $salesMdl->errorInfo;
        }

        return $result;
    }


    /**
     * Searches sales for the given reference.
     * @param $searchdata
     * @param $result
     * @return mixed Returns sales that match the specified ref.
     */
    public function searchSales($searchdata, $result)
    {
        $salesMdl = new SalesModel();
        $dbSales  = $salesMdl->get(0, 0, $searchdata->ref, null, null, null, null, true);
        if (is_array($dbSales)) {
            $sales = [];
            foreach ($dbSales as $sale) {
                $jsonObj             = json_decode($sale['data'], true);
                $sales[$sale['ref']] = $jsonObj;
            }
            $result['data'] = $sales;
        } else if ($dbSales === false) {
            $result['error'] = $salesMdl->errorInfo;
        }

        return $result;
    }

    /**
     * @param array $result
     *
     * @return array Returns an array of tax objects
     */
    public static function getTaxes($result = [])
    {
        $taxItemsMdl = new TaxItemsModel();
        $taxItemsArr    = $taxItemsMdl->get();

        if (is_array($taxItemsArr)) {
            $taxItems = [];
            foreach ($taxItemsArr as $taxItem) {
                $taxItems[$taxItem['id']] = $taxItem;
            }
            $result['data'] = [];
            $result['data']['items'] = $taxItems;

            $taxRulesMdl = new TaxRulesModel();
            $taxRulesArr   = $taxRulesMdl->get();
            if (is_array($taxRulesArr)) {
                $taxRules = [];
                foreach ($taxRulesArr as $taxRule) {
                    $ruleData = json_decode($taxRule['data']);
                    $ruleData->id = $taxRule['id'];
                    $taxRules[$taxRule['id']] = $ruleData;
                }

                $result['data']['rules'] = $taxRules;
            } else {
                $result['error'] = "Tax data could not be retrieved: " . $taxRulesMdl->errorInfo;
            }
        } else {
            $result['error'] = "Tax data could not be retrieved: " . $taxItemsMdl->errorInfo;
        }

        return $result;
    }



    /**
     * @param $result
     * @return mixed Returns an array of suppliers
     */
    public function getSuppliers($result)
    {
        $suppliersMdl = new SuppliersModel();
        $suppliers    = $suppliersMdl->get();
        if (is_array($suppliers)) {
            $supplierdata = [];
            foreach ($suppliers as $supplier) {
                $supplierdata[$supplier['id']] = $supplier;
            }
            $result['data'] = $supplierdata;
        } else {
            $result['error'] = $suppliersMdl->errorInfo;
        }

        return $result;
    }

    /**
     * @param $result
     * @return mixed Returns an array of categories
     */
    public function getCategories($result)
    {
        $catMdl = new CategoriesModel();
        $categories = $catMdl->get();
        if (is_array($categories)) {
            $catdata = [];
            foreach ($categories as $category) {
                $catdata[$category['id']] = $category;
            }
            $result['data'] = $catdata;
        } else {
            $result['error'] = $catMdl->errorInfo;
        }

        return $result;
    }

    /**
     * @param $result
     * @return mixed Returns an array of stock. Each row is a certain item & location ID.
     */
    public function getStock($result)
    {
        $stockMdl = new StockModel();
        $stocks    = $stockMdl->get();
        if (is_array($stocks)) {
            $stockdata = [];
            foreach ($stocks as $stock) {
                $stockdata[$stock['id']] = $stock;
            }
            $result['data'] = $stockdata;
        } else {
            $result['error'] = $stockMdl->errorInfo;
        }

        return $result;
    }

    /**
     * Get product variants data for POS
     * @param array $result
     * @return array of variant records
     */
    public function getVariants($result)
    {
        $variantsMdl = new ProductVariantsModel();
        $variants = $variantsMdl->get(null, null, null, null, true); // active_only = true
        
        if (is_array($variants)) {
            $variantData = [];
            foreach ($variants as $variant) {
                // Include attributes for each variant
                $variant['attributes'] = $variantsMdl->getVariantAttributes($variant['id']);
                $variantData[$variant['id']] = $variant;
            }
            $result['data'] = $variantData;
        } else {
            $result['error'] = $variantsMdl->errorInfo;
        }

        return $result;
    }

    /**
     * Get attributes data for POS
     * @param array $result
     * @return array of attribute records
     */
    public function getAttributes($result)
    {
        $attrMdl = new AttributesModel();
        $attributes = $attrMdl->getAttributesWithValues();
        
        if (is_array($attributes)) {
            $result['data'] = $attributes;
        } else {
            $result['error'] = $attrMdl->errorInfo;
        }

        return $result;
    }

    /**
     * Find a variant by barcode or SKU
     * Used for POS scanning
     * @param string $code Barcode or SKU
     * @param array $result
     * @return array
     */
    public function findVariantByCode($code, $result)
    {
        $variant = VariantsHelper::findByCode($code);
        
        if ($variant !== null) {
            // Get full variant info for sale
            $saleData = VariantsHelper::getVariantForSale($variant['id']);
            $result['data'] = $saleData;
        } else {
            $result['error'] = 'Item not found';
        }

        return $result;
    }

    /**
     * Get variants for a specific product (for variant picker)
     * @param int $product_id Product ID
     * @param int|null $location_id Optional location ID for stock info
     * @param array $result
     * @return array
     */
    public function getProductVariants($product_id, $location_id, $result)
    {
        $variants = VariantsHelper::getProductVariantsWithStock($product_id, $location_id);
        
        if (is_array($variants)) {
            $result['data'] = $variants;
        } else {
            $result['error'] = 'Failed to load variants';
        }

        return $result;
    }
}
