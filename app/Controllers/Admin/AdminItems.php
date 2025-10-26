<?php

/**
 *
 * AdminItems is used to modify administrative items including stored items, suppliers, customers and users.
 *
 */


namespace App\Controllers\Admin;

use App\Communication\SocketIO;
use App\Database\AuthModel;
use App\Database\CategoriesModel;
use App\Database\StoredItemsModel;
use App\Database\SuppliersModel;
use App\Database\TaxItemsModel;
use App\Database\TaxRulesModel;
use App\Database\ProductAttributesModel;
use App\Database\ProductAttributeValuesModel;
use App\Database\ProductVariantsModel;
use App\Database\VariantStockModel;
use App\Models\StoredItem;
use App\Controllers\Pos\PosData;
use App\Utility\EventStream;
use App\Utility\JsonValidate;
use App\Utility\Logger;

class AdminItems
{
    private $data;

    /**
     * Set any provided data
     * @param $data
     */
    function __construct($data)
    {
        // parse the data and put it into an object
        if ($data !== false) {
            $this->data = $data;
        } else {
            $this->data = new \stdClass();
        }
    }
    // STORED ITEMS
    /**
     * Add a stored item into the system
     * @param $result
     * @return mixed
     */
    public function addStoredItem($result)
    {
        // validate input
        $jsonval = new JsonValidate($this->data, '{"code":"","qty":1, "name":"", "taxid":1, "cost":-1, "price":-1,"type":""}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        // create model and check for duplicate stockcode
        $itemMdl = new StoredItemsModel();
        $this->data->code = strtoupper($this->data->code); // make sure stockcode is upper case
        if (sizeof($itemMdl->get(null, $this->data->code)) > 0) {
            $result['error'] = "An item with that stockcode already exists";
            return $result;
        }
        // create the new item
        $qresult = $itemMdl->create($this->data);
        if ($qresult === false) {
            $result['error'] = "Could not add the item: " . $itemMdl->errorInfo;
        } else {
            $this->data->id = $qresult;
            $result['data'] = $this->data;
            // broadcast the item
            $socket = new SocketIO();
            $socket->sendItemUpdate($this->data);

            // log data
            Logger::write("Item added with id:" . $this->data->id, "ITEM", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Update a stored item
     * @param $result
     * @return mixed
     */
    public function updateStoredItem($result)
    {
        // validate input
        $jsonval = new JsonValidate($this->data, '{"id":1, "code":"", "qty":1, "name":"", "taxid":1, "cost":-1, "price":-1}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        // create model and check for duplicate stockcode
        $itemMdl = new StoredItemsModel();
        $this->data->code = strtoupper($this->data->code); // make sure stockcode is upper case
        $dupitems = $itemMdl->get(null, $this->data->code);
        if (sizeof($dupitems) > 0) {
            $dupitem = $dupitems[0];
            if ($dupitem['id'] != $this->data->id) {
                $result['error'] = "An item with that stockcode already exists";
                return $result;
            }
        }
        // update the item
        $qresult = $itemMdl->edit($this->data->id, $this->data);
        if ($qresult === false) {
            $result['error'] = "Could not edit the item: " . $itemMdl->errorInfo;
        } else {
            $result['data'] = $this->data;
            // broadcast the item
            $socket = new SocketIO();
            $socket->sendItemUpdate($this->data);

            // log data
            Logger::write("Item updated with id:" . $this->data->id, "ITEM", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Delete a stored item
     * @param $result
     * @return mixed
     */
    public function deleteStoredItem($result)
    {
        // validate input
        if (!is_numeric($this->data->id)) {
            if (isset($this->data->id)) {
                $ids = explode(",", $this->data->id);
                foreach ($ids as $id) {
                    if (!is_numeric($id)) {
                        $result['error'] = "A valid comma separated list of ids must be supplied";
                        return $result;
                    }
                }
            } else {
                $result['error'] = "A valid id, or comma separated list of ids must be supplied";
                return $result;
            }
        }
        // remove the item
        $itemMdl = new StoredItemsModel();
        $qresult = $itemMdl->remove(isset($ids) ? $ids : $this->data->id);
        if ($qresult === false) {
            $result['error'] = "Could not delete the item: " . $itemMdl->errorInfo;
        } else {
            $result['data'] = true;
            // broadcast the item; supplying the id only indicates deletion
            $socket = new SocketIO();
            $socket->sendItemUpdate($this->data->id);

            // log data
            Logger::write("Item(s) deleted with id:" . $this->data->id, "ITEM");
        }
        return $result;
    }

    /**
     * Import items
     * @param $result
     * @return mixed
     */
    public function importItemsSet($result)
    {
        $_SESSION['import_data'] = $this->data->import_data;
        $_SESSION['import_options'] = $this->data->options;
        return $result;
    }

    private function getIdForName($arr, $value)
    {
        foreach ($arr as $key => $item) {
            if ($item['name'] === $value)
                return $item['id'];
        }
        return false;
    }

    /**
     * Import items
     * @param $result
     * @return mixed
     */
    public function importItemsStart($result)
    {
        if (!isset($_SESSION['import_data']) || !is_array($_SESSION['import_data'])) {
            $result['error'] = "Import data was not received.";
            EventStream::sendStreamData($result);
            return $result;
        }
        $options = $_SESSION['import_options'];
        $items = $_SESSION['import_data'];

        EventStream::iniStream();
        $itemMdl = new StoredItemsModel();
        $catMdl = new CategoriesModel();
        $supMdl = new SuppliersModel();
        $taxMdl = new TaxRulesModel();

        $categories = $catMdl->get();
        $suppliers = $supMdl->get();
        $taxRules = $taxMdl->get();
        foreach ($taxRules as $key => $rule) {
            $data = json_decode($rule['data'], true);
            $data['id'] = $rule['id'];
            $taxRules[$rule['id']] = $data;
        }

        if ($categories === false || $suppliers === false || $taxRules === false) {
            $result['error'] = "Could not load categories, suppliers or tax rules: " . $catMdl->errorInfo . " " . $supMdl->errorInfo . " " . $taxMdl->errorInfo;
            EventStream::sendStreamData($result);
            return $result;
        }

        EventStream::sendStreamData(['status' => "Validating Items..."]);
        $validator = new JsonValidate(null, '{"code":"", "qty":1, "name":"", "price":-1, "tax_name":"", "category_name":"", "supplier_name":""}');
        $count = 1;
        foreach ($items as $key => $item) {
            EventStream::sendStreamData(['status' => "Validating Items...", 'progress' => $count]);

            $validator->validate($item);

            $item->code = strtoupper($item->code); // make sure stockcode is upper case
            $dupitems = $itemMdl->get(null, $item->code);
            if (sizeof($dupitems) > 0) {
                $dupitem = $dupitems[0];
                if ($dupitem['id'] != $item->id) {
                    $result['error'] = "An item with the stockcode " . $item->code . " already exists on line " . $count;
                    EventStream::sendStreamData($result);
                    return $result;
                }
            }

            // remove currency symbol from price & cost
            $item->price = preg_replace("/([^0-9\\.])/i", "", $item->price);
            $item->cost = preg_replace("/([^0-9\\.])/i", "", $item->cost);

            // Match tax id with name
            if (!$item->tax_name) {
                $id = 1;
            } else {
                $id = $this->getIdForName($taxRules, $item->tax_name);
            }
            if ($id === false) {
                $result['error'] = "Could not find tax rule id for name " . $item->tax_name . " on line " . $count . " of the CSV";
                EventStream::sendStreamData($result);
                return $result;
            }
            $item->taxid = $id;
            unset($item->tax_name);

            // Match category
            if (!$item->category_name || $item->category_name == "None" || $item->category_name == "Misc") {
                $id = 0;
            } else {
                $id = $this->getIdForName($categories, $item->category_name);
            }
            if ($id === false) {
                if ((isset($options->add_categories) && $options->add_categories === true)) {
                    EventStream::sendStreamData(['status' => "Adding category..."]);
                    $id = $catMdl->create($item->category_name);
                    if (!is_numeric($id)) {
                        $result['error'] = "Could not add new category " . $item->category_name . " on line " . $count . " of the CSV: " . $catMdl->errorInfo;
                        EventStream::sendStreamData($result);
                        return $result;
                    }
                    $categories[] = ['' => $id, 'name' => $item->category_name];
                } else {
                    $result['error'] = "Could not find category id for name " . $item->category_name . " on line " . $count . " of the CSV";
                    EventStream::sendStreamData($result);
                    return $result;
                }
            }
            $item->categoryid = $id;
            unset($item->category_name);

            // Match supplier
            if (!$item->supplier_name || $item->supplier_name == "None" || $item->supplier_name == "Misc") {
                $id = 0;
            } else {
                $id = $this->getIdForName($suppliers, $item->supplier_name);
            }
            if ($id === false) {
                if ((isset($options->add_suppliers) && $options->add_suppliers === true)) {
                    EventStream::sendStreamData(['status' => "Adding supplier..."]);
                    $id = $supMdl->create($item->supplier_name);
                    if (!is_numeric($id)) {
                        $result['error'] = "Could not add new supplier " . $item->supplier_name . " on line " . $count . " of the CSV: " . $catMdl->errorInfo;
                        EventStream::sendStreamData($result);
                        return $result;
                    }
                    $suppliers[] = ['' => $id, 'name' => $item->supplier_name];
                } else {
                    $result['error'] = "Could not find supplier id for name " . $item->supplier_name . " on line " . $count . " of the CSV";
                    EventStream::sendStreamData($result);
                    return $result;
                }
            }
            $item->supplierid = $id;
            unset($item->supplier_name);

            $items[$key] = $item;

            $count++;
        }

        EventStream::sendStreamData(['status' => "Importing Items..."]);
        $result['data'] = [];
        $count = 1;
        foreach ($items as $item) {
            EventStream::sendStreamData(['progress' => $count]);

            $itemObj = new StoredItem($item);
            $id = $itemMdl->create($itemObj);

            if ($id === false) {
                $result['error'] = "Failed to add the item on line " . $count . " of the CSV: " . $itemMdl->errorInfo;
                EventStream::sendStreamData($result);
                return $result;
            }
            $itemObj->id = $id;
            $result['data'][$id] = $itemObj;

            $count++;
        }

        unset($_SESSION['import_data']);
        unset($_SESSION['import_options']);

        EventStream::sendStreamData($result);
        return $result;
    }

    // ITEM CATEGORIES
    /**
     * Add a new category
     * @param $result
     * @return mixed
     */
    public function addCategory($result)
    {
        $jsonval = new JsonValidate($this->data, '{"name":""}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        $catMdl = new CategoriesModel();
        $qresult = $catMdl->create($this->data->name);
        if ($qresult === false) {
            $result['error'] = "Could not add the category: " . $catMdl->errorInfo;
        } else {
            $result['data'] = $this->getCategoryRecord($qresult);
            // broadcast update
            $socket = new SocketIO();
            $socket->sendConfigUpdate($result['data'], 'item_categories');
            // log data
            Logger::write("Category added with id:" . $this->data->id, "CATEGORY", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Update a category
     * @param $result
     * @return mixed
     */
    public function updateCategory($result)
    {
        $jsonval = new JsonValidate($this->data, '{"id":1, "name":""}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        $catMdl = new CategoriesModel();
        $qresult = $catMdl->edit($this->data->id, $this->data->name);
        if ($qresult === false) {
            $result['error'] = "Could not edit the category: " . $catMdl->errorInfo;
        } else {
            $result['data'] = $this->getCategoryRecord($this->data->id);
            // broadcast update
            $socket = new SocketIO();
            $socket->sendConfigUpdate($result['data'], 'item_categories');
            // log data
            Logger::write("Category updated with id:" . $this->data->id, "CATEGORY", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Returns category array by ID
     * @param $id
     * @return mixed
     */
    private function getCategoryRecord($id)
    {
        $supMdl = new CategoriesModel();
        $result = $supMdl->get($id)[0];
        return $result;
    }

    /**
     * Delete category
     * @param $result
     * @return mixed
     */
    public function deleteCategory($result)
    {
        // validate input
        if (!is_numeric($this->data->id)) {
            if (isset($this->data->id)) {
                $ids = explode(",", $this->data->id);
                foreach ($ids as $id) {
                    if (!is_numeric($id)) {
                        $result['error'] = "A valid comma separated list of ids must be supplied";
                        return $result;
                    }
                }
            } else {
                $result['error'] = "A valid id, or comma separated list of ids must be supplied";
                return $result;
            }
        }
        $catMdl = new CategoriesModel();
        $qresult = $catMdl->remove(isset($ids) ? $ids : $this->data->id);
        if ($qresult === false) {
            $result['error'] = "Could not delete the category: " . $catMdl->errorInfo;
        } else {
            $result['data'] = true;
            // broadcast update
            $socket = new SocketIO();
            $socket->sendConfigUpdate($this->data->id, 'item_categories');
            // log data
            Logger::write("Category(s) deleted with id:" . $this->data->id, "CATEGORY");
        }
        return $result;
    }
    // SUPPLIERS
    /**
     * Add a new supplier
     * @param $result
     * @return mixed
     */
    public function addSupplier($result)
    {
        $jsonval = new JsonValidate($this->data, '{"name":""}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        $supMdl = new SuppliersModel();
        $qresult = $supMdl->create($this->data->name);
        if ($qresult === false) {
            $result['error'] = "Could not add the supplier: " . $supMdl->errorInfo;
        } else {
            $result['data'] = $this->getSupplierRecord($qresult);
            // log data
            Logger::write("Supplier added with id:" . $this->data->id, "SUPPLIER", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Update a supplier
     * @param $result
     * @return mixed
     */
    public function updateSupplier($result)
    {
        $jsonval = new JsonValidate($this->data, '{"id":1, "name":""}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        $supMdl = new SuppliersModel();
        $qresult = $supMdl->edit($this->data->id, $this->data->name);
        if ($qresult === false) {
            $result['error'] = "Could not edit the supplier: " . $supMdl->errorInfo;
        } else {
            $result['data'] = $this->getSupplierRecord($this->data->id);

            // log data
            Logger::write("Suppliers updated with id:" . $this->data->id, "SUPPLIER", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Returns supplier array by ID
     * @param $id
     * @return mixed
     */
    private function getSupplierRecord($id)
    {
        $supMdl = new SuppliersModel();
        $result = $supMdl->get($id)[0];
        return $result;
    }

    /**
     * Delete supplier
     * @param $result
     * @return mixed
     */
    public function deleteSupplier($result)
    {
        // validate input
        if (!is_numeric($this->data->id)) {
            if (isset($this->data->id)) {
                $ids = explode(",", $this->data->id);
                foreach ($ids as $id) {
                    if (!is_numeric($id)) {
                        $result['error'] = "A valid comma separated list of ids must be supplied";
                        return $result;
                    }
                }
            } else {
                $result['error'] = "A valid id, or comma separated list of ids must be supplied";
                return $result;
            }
        }
        $supMdl = new SuppliersModel();
        $qresult = $supMdl->remove(isset($ids) ? $ids : $this->data->id);
        if ($qresult === false) {
            $result['error'] = "Could not delete the supplier: " . $supMdl->errorInfo;
        } else {
            $result['data'] = true;

            // log data
            Logger::write("Supplier(s) deleted with id:" . $this->data->id, "SUPPLIER");
        }
        return $result;
    }
    // USERS
    private $defaultPermissions = [
        "sections" => ['access' => "no", 'dashboard' => "none", 'reports' => 0, 'graph' => 0, 'realtime' => 0, 'sales' => 0, 'items' => 0, 'stock' => 0, 'categories' => 0, 'suppliers' => 0, 'customers' => 0],
        "apicalls" => []
    ];
    /**
     * Maps permissions with their corresponding section name and API actions
     * @var array
     */
    private $permissionMap = [
        "readapicalls" => [
            "dashboard" => ['stats/general', 'stats/takings', 'stats/itemselling', 'stats/locations', 'stats/devices', 'graph/general'],
            "reports" => ['stats/general', 'stats/takings', 'stats/itemselling', 'stats/categoryselling', 'stats/supplyselling', 'stats/stock', 'stats/devices', 'stats/locations', 'stats/users', 'stats/tax'],
            "graph" => ['graph/general', 'graph/takings', 'graph/devices', 'graph/locations'],
            "realtime" => ['stats/general', 'graph/general'],
            "sales" => [],
            "invoices" => ['invoices/get'],
            "items" => ['suppliers/get', 'categories/get'],
            "stock" => ['stock/get', 'stock/history'],
            "categories" => ['categories/get'],
            "suppliers" => ['suppliers/get'],
            "customers" => [],
        ],
        "editapicalls" => [
            "dashboard" => [],
            "reports" => [],
            "graph" => [],
            "realtime" => [],
            "sales" => ['sales/delete', 'sales/deletevoid', 'sales/adminvoid'],
            "invoices" => [
                'invoices/add',
                'invoices/edit',
                'invoices/delete',
                'invoices/items/add',
                'invoices/items/edit',
                'invoices/items/delete',
                'invoices/payments/add',
                'invoices/payments/edit',
                'invoices/payments/delete',
                'invoices/generate',
                'invoices/email'
            ],
            "items" => ['items/add', 'items/edit', 'items/delete'],
            "stock" => ['stock/add', 'stock/set', 'stock/transfer'],
            "categories" => ['categories/add', 'categories/edit', 'categories/delete'],
            "suppliers" => ['suppliers/add', 'suppliers/edit', 'suppliers/delete'],
            "customers" => ['customers/add', 'customers/edit', 'customers/delete', 'customers/contacts/add', 'customers/contacts/edit', 'customers/contacts/delete', 'customers/setaccess', 'customers/setpassword', 'customers/sendreset'],
        ]
    ];

    /**
     * Add user
     * @param $result
     * @return mixed
     */
    public function addUser($result)
    {
        $jsonval = new JsonValidate($this->data, '{"username":"", "pass":"", "admin":1}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        // check for duplicate username
        $authMdl = new AuthModel();
        if (sizeof($authMdl->get(0, 0, null, $this->data->username)) > 0) {
            $result['error'] = "The username specified is already taken";
            return $result;
        }
        // insert entry if the user is admin, preset all permissions
        $qresult = $authMdl->create($this->data->username, $this->data->pass, $this->data->admin, json_encode($this->defaultPermissions));
        if ($qresult === false) {
            $result['error'] = "Could not add the user";
        } else {
            $result['data'] = true;

            // log data
            unset($this->data->pass);
            Logger::write("User added with id:" . $this->data->id, "USER", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Update user
     * @param $result
     * @return mixed
     */
    public function updateUser($result)
    {
        // prevent updating of master admin username
        if ($this->data->id == 1 && !isset($this->data->pass)) {
            $result['error'] = "Only the master admin password may be updated.";
            return $result;
        }
        // validate input
        $jsonval = new JsonValidate($this->data, '{"id":1, "username":"", "admin":1}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        $authMdl = new AuthModel();
        if ($this->data->id == 1) {
            // Only rhe admin users password can be updated
            $qresult = $authMdl->edit($this->data->id, $this->data->username, $this->data->pass);
            unset($this->data->permissions);
            unset($this->data->admin);
        } else {

            $dupitems = $authMdl->get(0, 0, null, $this->data->username);
            if (sizeof($dupitems) > 0) {
                $dupitem = $dupitems[0];
                if ($dupitem['id'] != $this->data->id) {
                    $result['error'] = "The username specified is already taken";
                    return $result;
                }
            }
            // generate permissions object
            $permObj = [
                "sections" => $this->data->permissions,
                "apicalls" => []
            ];
            foreach ($this->data->permissions as $key => $value) {
                switch ($key) {
                    case "access";
                        if ($value != "no") {
                            $permObj['apicalls'][] = "adminconfig/get";
                        }
                        break;
                    case "dashboard";
                        if ($value == "both" || $value == "standard") {
                            $permObj['apicalls'] = array_merge($permObj['apicalls'], $this->permissionMap['readapicalls']['dashboard']);
                        }
                        if ($value == "both" || $value == "realtime") {
                            $permObj['apicalls'] = array_merge($permObj['apicalls'], $this->permissionMap['readapicalls']['realtime']);
                        }
                        break;
                    default:
                        switch ($value) {
                            case 2:
                                // add write api calls
                                if (isset($this->permissionMap['editapicalls'][$key])) {
                                    $permObj['apicalls'] = array_merge($permObj['apicalls'], $this->permissionMap['editapicalls'][$key]);
                                }
                            case 1:
                                // add read api calls
                                if (isset($this->permissionMap['readapicalls'][$key])) {
                                    $permObj['apicalls'] = array_merge($permObj['apicalls'], $this->permissionMap['readapicalls'][$key]);
                                }
                                break;
                        }
                }
            }
            if ($this->data->pass == "") {
                $qresult = $authMdl->edit($this->data->id, $this->data->username, null, $this->data->admin, json_encode($permObj));
            } else {
                $qresult = $authMdl->edit($this->data->id, $this->data->username, $this->data->pass, $this->data->admin, json_encode($permObj));
            }
        }
        if ($qresult === false) {
            $result['error'] = "Could not update the user";
        } else {
            $result['data'] = true;

            // log data
            unset($this->data->pass);
            Logger::write("User updated with id:" . $this->data->id, "USER", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Delete user
     * @param $result
     * @return mixed
     */
    public function deleteUser($result)
    {
        // validate input
        if (!is_numeric($this->data->id)) {
            $result['error'] = "A valid id must be supplied";
            return $result;
        }
        $authMdl = new AuthModel();
        $qresult = $authMdl->remove($this->data->id);
        if ($qresult === false) {
            $result['error'] = "Could not delete the user";
        } else {
            $result['data'] = true;

            // log data
            Logger::write("User deleted with id:" . $this->data->id, "USER");
        }
        return $result;
    }

    /**
     * Set user disabled
     * @param $result
     * @return mixed
     */
    public function setUserDisabled($result)
    {
        // validate input
        if (!is_numeric($this->data->id)) {
            $result['error'] = "A valid id must be supplied";
            return $result;
        }
        // prevent updating of master admin username
        if ($this->data->id == 1 && !isset($this->data->pass)) {
            $result['error'] = "The master admin user cannot be disabled";
            return $result;
        }
        $userMdl = new AuthModel();
        if ($userMdl->setDisabled($this->data->id, boolval($this->data->disable)) === false) {
            $result['error'] = "Could not enable/disable the user";
        }

        // log data
        Logger::write("User " . ($this->data->disable == true ? "disabled" : "enabled") . " with id:" . $this->data->id, "USER");

        return $result;
    }
    // Tax items
    /**
     * Add a new tax rule
     * @param $result
     * @return mixed
     */
    public function addTaxRule($result)
    {
        $jsonval = new JsonValidate($this->data, '{"name":"", "inclusive":true, "base":"", "locations":""}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        $taxRuleMdl = new TaxRulesModel();
        $qresult = $taxRuleMdl->create($this->data);
        if ($qresult === false) {
            $result['error'] = "Could not add the tax rule: " . $taxRuleMdl->errorInfo;
        } else {
            $this->data->id = $qresult;
            $result['data'] = $this->data;
            $this->broadcastTaxUpdate();
            // log data
            Logger::write("Tax rule added with id:" . $this->data->id, "TAX", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Update a tax rule
     * @param $result
     * @return mixed
     */
    public function updateTaxRule($result)
    {
        $jsonval = new JsonValidate($this->data, '{"id":1, "name":"", "inclusive":true, "base":"", "locations":""}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        if ($this->data->id == 1) {
            $result['error'] = "The No Tax rule cannot be edited";
            return $result;
        }
        $taxRuleMdl = new TaxRulesModel();
        $qresult = $taxRuleMdl->edit($this->data->id, $this->data);
        if ($qresult === false) {
            $result['error'] = "Could not edit the tax rule: " . $taxRuleMdl->errorInfo;
        } else {
            $result['data'] = $this->data;
            $this->broadcastTaxUpdate();
            // log data
            Logger::write("Tax rule updated with id:" . $this->data->id, "TAX", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Delete a tax rule
     * @param $result
     * @return mixed
     */
    public function deleteTaxRule($result)
    {
        // validate input
        if (!is_numeric($this->data->id)) {
            $result['error'] = "A valid id must be supplied";
            return $result;
        }
        if ($this->data->id == 1) {
            $result['error'] = "The No Tax rule cannot be deleted";
            return $result;
        }
        $taxRuleMdl = new TaxRulesModel();
        $qresult = $taxRuleMdl->remove($this->data->id);
        if ($qresult === false) {
            $result['error'] = "Could not delete the tax rule: " . $taxRuleMdl->errorInfo;
        } else {
            $result['data'] = true;
            $this->broadcastTaxUpdate();
            // log data
            Logger::write("Tax rule deleted with id:" . $this->data->id, "TAX");
        }
        return $result;
    }

    /**
     * @param $value
     * @return float
     */
    public static function calculateTaxMultiplier($value)
    {
        return ($value / 100);
    }
    /**
     * Add a new tax rule
     * @param $result
     * @return mixed
     */
    public function addTaxItem($result)
    {
        $jsonval = new JsonValidate($this->data, '{"name":"", "type":"", "value":1}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        $this->data->multiplier = AdminItems::calculateTaxMultiplier($this->data->value);
        $taxItemMdl = new TaxItemsModel();
        $qresult = $taxItemMdl->create($this->data->name, $this->data->altname, $this->data->type, $this->data->value, $this->data->multiplier);
        if ($qresult === false) {
            $result['error'] = "Could not add the tax item: " . $taxItemMdl->errorInfo;
        } else {
            $this->data->id = $qresult;
            $result['data'] = $this->data;
            $this->broadcastTaxUpdate();
            // log data
            Logger::write("Tax item added with id:" . $this->data->id, "TAX", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Update a tax rule
     * @param $result
     * @return mixed
     */
    public function updateTaxItem($result)
    {
        $jsonval = new JsonValidate($this->data, '{"name":"", "type":"", "value":1}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        $this->data->multiplier = AdminItems::calculateTaxMultiplier($this->data->value);
        $taxItemMdl = new TaxItemsModel();
        $qresult = $taxItemMdl->edit($this->data->id, $this->data->name, $this->data->altname, $this->data->type, $this->data->value, $this->data->multiplier);
        if ($qresult === false) {
            $result['error'] = "Could not edit the tax item: " . $taxItemMdl->errorInfo;
        } else {
            $result['data'] = $this->data;
            $this->broadcastTaxUpdate();
            // log data
            Logger::write("Tax item updated with id:" . $this->data->id, "TAX", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Delete a tax rule
     * @param $result
     * @return mixed
     */
    public function deleteTaxItem($result)
    {
        // validate input
        if (!is_numeric($this->data->id)) {
            $result['error'] = "A valid id must be supplied";
            return $result;
        }
        $taxItemMdl = new TaxItemsModel();
        $qresult = $taxItemMdl->remove($this->data->id);
        if ($qresult === false) {
            $result['error'] = "Could not delete the tax item: " . $taxItemMdl->errorInfo;
        } else {
            $result['data'] = true;
            $this->broadcastTaxUpdate();
            // log data
            Logger::write("Tax item deleted with id:" . $this->data->id, "TAX");
        }
        return $result;
    }

    private function broadcastTaxUpdate()
    {
        $taxconfig = PosData::getTaxes();
        if (!isset($taxconfig['error'])) {
            $socket = new SocketIO();
            $socket->sendConfigUpdate($taxconfig['data'], "tax");
        }
    }

    // PRODUCT ATTRIBUTES
    /**
     * Add a product attribute
     * @param $result
     * @return mixed
     */
    public function addProductAttribute($result)
    {
        // validate input
        $jsonval = new JsonValidate($this->data, '{"name":"", "display_name":""}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        // create model and check for duplicate name
        $attrMdl = new ProductAttributesModel();
        $existing = $attrMdl->get();
        foreach ($existing as $attr) {
            if (strtolower($attr['name']) === strtolower($this->data->name)) {
                $result['error'] = "An attribute with that name already exists";
                return $result;
            }
        }
        // create the new attribute
        $qresult = $attrMdl->create($this->data);
        if ($qresult === false) {
            $result['error'] = "Could not add the attribute: " . $attrMdl->errorInfo;
        } else {
            $this->data->id = $qresult;
            $result['data'] = $this->data;
            // log data
            Logger::write("Product attribute added with id:" . $this->data->id, "ATTRIBUTE", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Update a product attribute
     * @param $result
     * @return mixed
     */
    public function updateProductAttribute($result)
    {
        // validate input
        $jsonval = new JsonValidate($this->data, '{"id":1, "name":"", "display_name":""}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        // create model and check for duplicate name
        $attrMdl = new ProductAttributesModel();
        $existing = $attrMdl->get();
        foreach ($existing as $attr) {
            if (strtolower($attr['name']) === strtolower($this->data->name) && $attr['id'] != $this->data->id) {
                $result['error'] = "An attribute with that name already exists";
                return $result;
            }
        }
        // update the attribute
        $qresult = $attrMdl->edit($this->data->id, $this->data);
        if ($qresult === false) {
            $result['error'] = "Could not edit the attribute: " . $attrMdl->errorInfo;
        } else {
            $result['data'] = $this->data;
            // log data
            Logger::write("Product attribute updated with id:" . $this->data->id, "ATTRIBUTE", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Delete a product attribute
     * @param $result
     * @return mixed
     */
    public function deleteProductAttribute($result)
    {
        // validate input
        if (!is_numeric($this->data->id)) {
            $result['error'] = "A valid id must be supplied";
            return $result;
        }
        $attrMdl = new ProductAttributesModel();
        $qresult = $attrMdl->remove($this->data->id);
        if ($qresult === false) {
            $result['error'] = "Could not delete the attribute: " . $attrMdl->errorInfo;
        } else {
            $result['data'] = true;
            // log data
            Logger::write("Product attribute deleted with id:" . $this->data->id, "ATTRIBUTE");
        }
        return $result;
    }

    /**
     * Get product attributes
     * @param $result
     * @return mixed
     */
    public function getProductAttributes($result)
    {
        $attrMdl = new ProductAttributesModel();
        $attributes = $attrMdl->get();
        if ($attributes === false) {
            $result['error'] = "Could not get attributes: " . $attrMdl->errorInfo;
        } else {
            $result['data'] = $attributes;
        }
        return $result;
    }

    // PRODUCT ATTRIBUTE VALUES
    /**
     * Add a product attribute value
     * @param $result
     * @return mixed
     */
    public function addProductAttributeValue($result)
    {
        // validate input
        $jsonval = new JsonValidate($this->data, '{"attribute_id":1, "value":""}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        // create model and check for duplicate value for this attribute
        $valMdl = new ProductAttributeValuesModel();
        $existing = $valMdl->get(null, $this->data->attribute_id);
        foreach ($existing as $val) {
            if (strtolower($val['value']) === strtolower($this->data->value)) {
                $result['error'] = "An attribute value with that value already exists for this attribute";
                return $result;
            }
        }
        // create the new attribute value
        $qresult = $valMdl->create($this->data);
        if ($qresult === false) {
            $result['error'] = "Could not add the attribute value: " . $valMdl->errorInfo;
        } else {
            $this->data->id = $qresult;
            $result['data'] = $this->data;
            // log data
            Logger::write("Product attribute value added with id:" . $this->data->id, "ATTRIBUTE_VALUE", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Update a product attribute value
     * @param $result
     * @return mixed
     */
    public function updateProductAttributeValue($result)
    {
        // validate input
        $jsonval = new JsonValidate($this->data, '{"id":1, "attribute_id":1, "value":""}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        // create model and check for duplicate value for this attribute
        $valMdl = new ProductAttributeValuesModel();
        $existing = $valMdl->get(null, $this->data->attribute_id);
        foreach ($existing as $val) {
            if (strtolower($val['value']) === strtolower($this->data->value) && $val['id'] != $this->data->id) {
                $result['error'] = "An attribute value with that value already exists for this attribute";
                return $result;
            }
        }
        // update the attribute value
        $qresult = $valMdl->edit($this->data->id, $this->data);
        if ($qresult === false) {
            $result['error'] = "Could not edit the attribute value: " . $valMdl->errorInfo;
        } else {
            $result['data'] = $this->data;
            // log data
            Logger::write("Product attribute value updated with id:" . $this->data->id, "ATTRIBUTE_VALUE", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Delete a product attribute value
     * @param $result
     * @return mixed
     */
    public function deleteProductAttributeValue($result)
    {
        // validate input
        if (!is_numeric($this->data->id)) {
            $result['error'] = "A valid id must be supplied";
            return $result;
        }
        $valMdl = new ProductAttributeValuesModel();
        $qresult = $valMdl->remove($this->data->id);
        if ($qresult === false) {
            $result['error'] = "Could not delete the attribute value: " . $valMdl->errorInfo;
        } else {
            $result['data'] = true;
            // log data
            Logger::write("Product attribute value deleted with id:" . $this->data->id, "ATTRIBUTE_VALUE");
        }
        return $result;
    }

    /**
     * Get product attribute values for an attribute
     * @param $result
     * @return mixed
     */
    public function getProductAttributeValues($result)
    {
        // validate input
        if (!isset($this->data->attribute_id) || !is_numeric($this->data->attribute_id)) {
            $result['error'] = "A valid attribute_id must be supplied";
            return $result;
        }
        $valMdl = new ProductAttributeValuesModel();
        $values = $valMdl->get(null, $this->data->attribute_id);
        if ($values === false) {
            $result['error'] = "Could not get attribute values: " . $valMdl->errorInfo;
        } else {
            $result['data'] = $values;
        }
        return $result;
    }

    // PRODUCT VARIANTS
    /**
     * Add a product variant
     * @param $result
     * @return mixed
     */
    public function addProductVariant($result)
    {
        // validate input
        $jsonval = new JsonValidate($this->data, '{"product_id":1, "sku":"", "barcode":"", "price":-1, "cost":-1, "attributes":{}}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        // create model and check for duplicate SKU
        $varMdl = new ProductVariantsModel();
        $this->data->sku = strtoupper($this->data->sku); // make sure SKU is upper case
        $existing = $varMdl->get(null, null, $this->data->sku);
        if (sizeof($existing) > 0) {
            $result['error'] = "A variant with that SKU already exists";
            return $result;
        }
        // create the new variant
        $qresult = $varMdl->create($this->data);
        if ($qresult === false) {
            $result['error'] = "Could not add the variant: " . $varMdl->errorInfo;
        } else {
            $this->data->id = $qresult;
            $result['data'] = $this->data;
            // broadcast the item update
            $socket = new SocketIO();
            $socket->sendItemUpdate($this->data->product_id);

            // log data
            Logger::write("Product variant added with id:" . $this->data->id, "VARIANT", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Update a product variant
     * @param $result
     * @return mixed
     */
    public function updateProductVariant($result)
    {
        // validate input
        $jsonval = new JsonValidate($this->data, '{"id":1, "product_id":1, "sku":"", "barcode":"", "price":-1, "cost":-1, "attributes":{}}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        // create model and check for duplicate SKU
        $varMdl = new ProductVariantsModel();
        $this->data->sku = strtoupper($this->data->sku); // make sure SKU is upper case
        $existing = $varMdl->get(null, null, $this->data->sku);
        foreach ($existing as $var) {
            if ($var['id'] != $this->data->id) {
                $result['error'] = "A variant with that SKU already exists";
                return $result;
            }
        }
        // update the variant
        $qresult = $varMdl->edit($this->data->id, $this->data);
        if ($qresult === false) {
            $result['error'] = "Could not edit the variant: " . $varMdl->errorInfo;
        } else {
            $result['data'] = $this->data;
            // broadcast the item update
            $socket = new SocketIO();
            $socket->sendItemUpdate($this->data->product_id);

            // log data
            Logger::write("Product variant updated with id:" . $this->data->id, "VARIANT", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Delete a product variant
     * @param $result
     * @return mixed
     */
    public function deleteProductVariant($result)
    {
        // validate input
        if (!is_numeric($this->data->id)) {
            $result['error'] = "A valid id must be supplied";
            return $result;
        }
        $varMdl = new ProductVariantsModel();
        // get variant info for logging and broadcasting
        $variants = $varMdl->get($this->data->id);
        $variant = $variants ? $variants[0] : null;
        $qresult = $varMdl->remove($this->data->id);
        if ($qresult === false) {
            $result['error'] = "Could not delete the variant: " . $varMdl->errorInfo;
        } else {
            $result['data'] = true;
            // broadcast the item update
            if ($variant) {
                $socket = new SocketIO();
                $socket->sendItemUpdate($variant['product_id']);
            }
            // log data
            Logger::write("Product variant deleted with id:" . $this->data->id, "VARIANT");
        }
        return $result;
    }

    /**
     * Get product variants for a product
     * @param $result
     * @return mixed
     */
    public function getProductVariants($result)
    {
        // validate input
        if (!isset($this->data->product_id) || !is_numeric($this->data->product_id)) {
            $result['error'] = "A valid product_id must be supplied";
            return $result;
        }
        $varMdl = new ProductVariantsModel();
        $variants = $varMdl->get(null, $this->data->product_id);
        if ($variants === false) {
            $result['error'] = "Could not get variants: " . $varMdl->errorInfo;
        } else {
            $result['data'] = $variants;
        }
        return $result;
    }

    // VARIANT STOCK MANAGEMENT
    /**
     * Get variant stock levels
     * @param $result
     * @return mixed
     */
    public function getVariantStock($result)
    {
        // validate input
        if (!isset($this->data->variant_id) || !is_numeric($this->data->variant_id)) {
            $result['error'] = "A valid variant_id must be supplied";
            return $result;
        }
        $stockMdl = new VariantStockModel();
        $stock = $stockMdl->get($this->data->variant_id);
        if ($stock === false) {
            $result['error'] = "Could not get variant stock: " . $stockMdl->errorInfo;
        } else {
            $result['data'] = $stock;
        }
        return $result;
    }

    /**
     * Update variant stock level
     * @param $result
     * @return mixed
     */
    public function updateVariantStock($result)
    {
        // validate input
        $jsonval = new JsonValidate($this->data, '{"variant_id":1, "location_id":1, "quantity":0}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        $stockMdl = new VariantStockModel();
        $qresult = $stockMdl->setStockLevel($this->data->variant_id, $this->data->location_id, $this->data->quantity);
        if ($qresult === false) {
            $result['error'] = "Could not update variant stock: " . $stockMdl->errorInfo;
        } else {
            $result['data'] = true;
            // broadcast the item update
            $variantMdl = new ProductVariantsModel();
            $variants = $variantMdl->get($this->data->variant_id);
            $variant = $variants ? $variants[0] : null;
            if ($variant) {
                $socket = new SocketIO();
                $socket->sendItemUpdate($variant['product_id']);
            }
            // log data
            Logger::write("Variant stock updated for variant_id:" . $this->data->variant_id . " location_id:" . $this->data->location_id, "STOCK", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Transfer variant stock between locations
     * @param $result
     * @return mixed
     */
    public function transferVariantStock($result)
    {
        // validate input
        $jsonval = new JsonValidate($this->data, '{"variant_id":1, "from_location_id":1, "to_location_id":1, "quantity":1}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        $stockMdl = new VariantStockModel();
        // First decrement stock from source location
        $decrementResult = $stockMdl->incrementStockLevel($this->data->variant_id, $this->data->from_location_id, $this->data->quantity, true);
        if ($decrementResult === false) {
            $result['error'] = "Could not transfer variant stock: insufficient stock at source location";
            return $result;
        }
        // Then increment stock at destination location
        $incrementResult = $stockMdl->incrementStockLevel($this->data->variant_id, $this->data->to_location_id, $this->data->quantity, false);
        if ($incrementResult === false) {
            $result['error'] = "Could not transfer variant stock: failed to add stock to destination location";
            return $result;
        }
        $result['data'] = true;
        // broadcast the item update
        $variantMdl = new ProductVariantsModel();
        $variants = $variantMdl->get($this->data->variant_id);
        $variant = $variants ? $variants[0] : null;
        if ($variant) {
            $socket = new SocketIO();
            $socket->sendItemUpdate($variant['product_id']);
        }
        // log data
        Logger::write("Variant stock transferred for variant_id:" . $this->data->variant_id, "STOCK", json_encode($this->data));
        return $result;
    }
}
