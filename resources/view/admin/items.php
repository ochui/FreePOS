<div class="page-header">
    <h1 style="margin-right: 20px; display: inline-block;">
        Items
    </h1>
    <button onclick="$('#adddialog').dialog('open');" id="addbtn" class="btn btn-primary btn-sm pull-right"><i class="icon-pencil align-top bigger-125"></i>Add</button>
    <button class="btn btn-success btn-sm pull-right" style="margin-right: 10px;" onclick="exportItems();"><i class="icon-cloud-download align-top bigger-125"></i>Export CSV</button>
    <button class="btn btn-success btn-sm pull-right" style="margin-right: 10px;" onclick="openImportDialog();"><i class="icon-cloud-upload align-top bigger-125"></i>Import CSV</button>
</div><!-- /.page-header -->

<div class="row">
<div class="col-xs-12">
<!-- PAGE CONTENT BEGINS -->

<div class="row">
<div class="col-xs-12">

<div class="table-header">
    Manage your business products
</div>

<table id="itemstable" class="table table-striped table-bordered table-hover dt-responsive" style="width:100%;">
<thead>
<tr>
    <th data-priority="0" class="center">
        <label>
            <input type="checkbox" class="ace" />
            <span class="lbl"></span>
        </label>
    </th>
    <th data-priority="1">ID</th>
    <th data-priority="2">Name</th>
    <th data-priority="8">Description</th>
    <th data-priority="7">Tax</th>
    <th data-priority="6">Default Qty</th>
    <th data-priority="4">Price</th>
    <th data-priority="5">Stockcode</th>
    <th data-priority="9">Category</th>
    <th data-priority="10">Supplier</th>
    <th class="noexport" data-priority="2"></th>
</tr>
</thead>
<tbody>

</tbody>
</table>

</div>
</div>

</div><!-- PAGE CONTENT ENDS -->
</div><!-- /.col -->
<div id="editdialog" class="hide">
    <div class="tabbable" style="min-width: 360px; min-height: 310px;">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#itemdetails" data-toggle="tab">
                    Details
                </a>
            </li>
            <li class="">
                <a href="#itemoptions" data-toggle="tab">
                    Options
                </a>
            </li>
            <li class="">
                <a href="#itemvariants" data-toggle="tab">
                    Variants
                </a>
            </li>
        </ul>
        <div class="tab-content" style="min-height: 320px;">
            <div class="tab-pane active in" id="itemdetails">
                <table>
                    <tr>
                        <td style="text-align: right;"><label>Name:&nbsp;</label></td>
                        <td><input id="itemname" type="text"/>
                            <input id="itemid" type="hidden"/></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><label>Alternate Name:&nbsp;</label></td>
                        <td><input id="itemaltname" type="text"/><br/>
                            <small>Alternate language name</small>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><label>Description:&nbsp;</label></td>
                        <td><input id="itemdesc" type="text"/></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><label>Unit Cost:&nbsp;</label></td>
                        <td><input id="itemcost" type="text" value="0"/></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><label>Unit Price:&nbsp;</label></td>
                        <td><input id="itemprice" type="text" value="0"/></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><label>Tax:&nbsp;</label></td>
                        <td><select id="itemtax" class="taxselect">
                            </select></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><label>Default Qty:&nbsp;</label></td>
                        <td><input id="itemqty" type="text" value="1"/></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><label>Stockcode:&nbsp;</label></td>
                        <td><input id="itemcode" type="text"/></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><label>Category:&nbsp;</label></td>
                        <td><select id="itemcategory" class="catselect">
                            </select></td>
                    </tr>
                    <tr>
                        <td style="text-align: right;"><label>Supplier:&nbsp;</label></td>
                        <td><select id="itemsupplier" class="supselect">
                            </select></td>
                    </tr>
                </table>
            </div>
            <div class="tab-pane" id="itemoptions" style="min-height: 280px;">
                <form class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-4"><label>Item Type:</label></div>
                        <div class="col-sm-8">
                            <select id="itemtype">
                                <option value="general">General</option>
                                <option value="food">Food</option>
                                <option value="beverage">Beverage</option>
                            </select>
                            <br/><small>Used for kitchen terminal dispatch</small>
                        </div>
                    </div>
                    <div class="space-4"></div>
                    <div class="form-group">
                        <div class="col-sm-12"><label>Simple Modifiers:</label></div>
                        <table class="table table-stripped table-responsive" style="margin-bottom: 0; padding-left: 10px; margin-right: 10px;">
                            <thead class="table-header smaller">
                                <tr>
                                    <th><small>Qty</small></th>
                                    <th><small>Min Qty</small></th>
                                    <th><small>Max Qty</small></th>
                                    <th><small>Name</small></th>
                                    <th><small>Price</small></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="itemmodtable">

                            </tbody>
                        </table>
                        <button style="float: right; margin-right: 8px;" class="btn btn-primary btn-xs" onclick="addItemModifier();">Add</button>
                        <div class="col-sm-12"><label>Select Modifiers:</label></div>
                        <table class="table table-stripped table-responsive" style="margin-bottom: 0; padding-left: 10px; margin-right: 10px;">
                            <tbody id="itemselmodtable">

                            </tbody>
                        </table>
                        <button style="float: right; margin-right: 8px;" class="btn btn-primary btn-xs" onclick="addSelectItemModifier();">Add</button>
                    </div>
                </form>
            </div>
            <div class="tab-pane" id="itemvariants" style="min-height: 400px; max-height: 600px; overflow-y: auto;">
                <div class="tabbable tabs-left">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#variant-attributes" data-toggle="tab">
                                Attributes
                            </a>
                        </li>
                        <li>
                            <a href="#variant-values" data-toggle="tab">
                                Values
                            </a>
                        </li>
                        <li>
                            <a href="#variant-list" data-toggle="tab">
                                Variants
                            </a>
                        </li>
                        <li>
                            <a href="#variant-stock" data-toggle="tab">
                                Stock
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active in" id="variant-attributes">
                            <h4>Product Attributes</h4>
                            <button class="btn btn-primary btn-sm" onclick="openAddAttributeDialog();">Add Attribute</button>
                            <table class="table table-striped table-bordered" id="attributes-table" style="margin-top: 10px;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Display Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="tab-pane" id="variant-values">
                            <h4>Attribute Values</h4>
                            <div class="form-group">
                                <label>Attribute:</label>
                                <select id="attribute-select" class="form-control" onchange="loadAttributeValues();">
                                    <option value="">Select Attribute</option>
                                </select>
                            </div>
                            <button class="btn btn-primary btn-sm" onclick="openAddAttributeValueDialog();">Add Value</button>
                            <table class="table table-striped table-bordered" id="attribute-values-table" style="margin-top: 10px;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Value</th>
                                        <th>Display Value</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="tab-pane" id="variant-list">
                            <h4>Product Variants</h4>
                            <button class="btn btn-primary btn-sm" onclick="openAddVariantDialog();">Add Variant</button>
                            <table class="table table-striped table-bordered" id="variants-table" style="margin-top: 10px;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>SKU</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Cost</th>
                                        <th>Attributes</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="tab-pane" id="variant-stock">
                            <h4>Variant Stock</h4>
                            <div class="form-group">
                                <label>Location:</label>
                                <select id="stock-location-select" class="form-control" onchange="loadVariantStock();">
                                    <option value="">Select Location</option>
                                </select>
                            </div>
                            <table class="table table-striped table-bordered" id="variant-stock-table" style="margin-top: 10px;">
                                <thead>
                                    <tr>
                                        <th>Variant</th>
                                        <th>Current Stock</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="adddialog" class="hide">
    <table>
        <tr>
           <td style="text-align: right;"><label>Name:&nbsp;</label></td>
           <td><input id="newitemname" type="text"/><br/></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Alternate Name:&nbsp;</label></td>
            <td><input id="newitemaltname" type="text"/><br/>
                <small>Alternate language name</small>
            </td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Description:&nbsp;</label></td>
            <td><input id="newitemdesc" type="text"/></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Unit Cost:&nbsp;</label></td>
            <td><input id="newitemcost" type="text" value="0"/></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Unit Price:&nbsp;</label></td>
            <td><input id="newitemprice" type="text" value="0"/></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Tax:&nbsp;</label></td>
            <td><select id="newitemtax" class="taxselect">
            </select></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Default Qty:&nbsp;</label></td>
            <td><input id="newitemqty" type="text" value="1"/></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Stockcode:&nbsp;</label></td>
            <td><input id="newitemcode" type="text"/></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Category:&nbsp;</label></td>
            <td><select id="newitemcategory" class="catselect">
                </select></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Supplier:&nbsp;</label></td>
            <td><select id="newitemsupplier" class="supselect">
            </select></td>
        </tr>
    </table>
</div>

<!-- page specific plugin scripts -->
<link rel="stylesheet" href="../assets/js/csv-import/lib/jquery.ezdz.min.css"/>
<script type="text/javascript" src="../assets/js/csv-import/lib/jquery.ezdz.min.js"></script>
<script type="text/javascript" src="../assets/js/csv-import/lib/jquery-sortable-min.js"></script>
<script type="text/javascript" src="../assets/js/csv-import/lib/jquery.csv-0.71.min.js"></script>
<script type="text/javascript" src="../assets/js/csv-import/csv.import.tool.js"></script>

<!-- inline scripts related to this page -->
<script type="text/javascript">
    var stock = null;
    var suppliers = null;
    var categories = null;
    var datatable;
    $(function() {
        // get default data using parallel requests
        var itemsPromise = new Promise(function(resolve, reject) {
            POS.sendJsonDataAsync("items/get", JSON.stringify(""), function(data) {
                if (data === false) {
                    reject(new Error("Failed to fetch items"));
                } else {
                    resolve(data);
                }
            });
        });
        
        var suppliersPromise = new Promise(function(resolve, reject) {
            POS.sendJsonDataAsync("suppliers/get", JSON.stringify(""), function(data) {
                if (data === false) {
                    reject(new Error("Failed to fetch suppliers"));
                } else {
                    resolve(data);
                }
            });
        });
        
        var categoriesPromise = new Promise(function(resolve, reject) {
            POS.sendJsonDataAsync("categories/get", JSON.stringify(""), function(data) {
                if (data === false) {
                    reject(new Error("Failed to fetch categories"));
                } else {
                    resolve(data);
                }
            });
        });
        
        Promise.all([itemsPromise, suppliersPromise, categoriesPromise]).then(function(results) {
            stock = results[0];
            suppliers = results[1];
            categories = results[2];
        var itemarray = [];
        var tempitem;
        var taxrules = POS.getTaxTable().rules;
        for (var key in stock){
            tempitem = stock[key];
            if (taxrules.hasOwnProperty(tempitem.taxid)){
                tempitem.taxname = taxrules[tempitem.taxid].name;
            } else {
                tempitem.taxname = "Not Defined";
            }
            itemarray.push(tempitem);
        }
        datatable = $('#itemstable').dataTable({
            "bProcessing": true,
            "aaData": itemarray,
            "aaSorting": [[ 2, "asc" ]],
            "aLengthMenu": [ 10, 25, 50, 100, 200],
            "aoColumns": [
                { mData:null, sDefaultContent:'<div style="text-align: center"><label><input class="ace dt-select-cb" type="checkbox"><span class="lbl"></span></label><div>', bSortable: false },
                { "mData":"id" },
                { "mData":"name" },
                { "mData":"description" },
                { "mData":"taxname" },
                { "mData":"qty" },
                { "mData":function(data,type,val){return (data['price']==""?"":POS.util.currencyFormat(data["price"]));} },
                { "mData":"code" },
                { "mData":function(data,type,val){return (categories.hasOwnProperty(data.categoryid)?categories[data.categoryid].name:'None'); } },
                { "mData":function(data,type,val){return (suppliers.hasOwnProperty(data.supplierid)?suppliers[data.supplierid].name:'None'); } },
                { mData:null, sDefaultContent:'<div class="action-buttons"><a class="green" onclick="openEditDialog($(this).closest(\'tr\').find(\'td\').eq(1).text());"><i class="icon-pencil bigger-130"></i></a><a class="red" onclick="removeItem($(this).closest(\'tr\').find(\'td\').eq(1).text())"><i class="icon-trash bigger-130"></i></a></div>', "bSortable": false }
            ],
            "columns": [
                {},
                {type: "numeric"},
                {type: "string"},
                {type: "string"},
                {type: "string"},
                {type: "numeric"},
                {type: "currency"},
                {type: "string"},
                {type: "string"},
                {type: "string"},
                {}
            ],
            "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                // Add selected row count to footer
                var selected = this.api().rows('.selected').count();
                return sPre+(selected>0 ? '<br/>'+selected+' row(s) selected <span class="action-buttons"><a class="red" onclick="removeSelectedItems();"><i class="icon-trash bigger-130"></i></a></span>':'');
            }
        });

        // row selection checkboxes
        datatable.find("tbody").on('click', '.dt-select-cb', function(e){
            var row = $(this).parents().eq(3);
            if (row.hasClass('selected')) {
                row.removeClass('selected');
            } else {
                row.addClass('selected');
            }
            datatable.api().draw(false);
            e.stopPropagation();
        });

        $('table.dataTable th input:checkbox').on('change' , function(){
            var that = this;
            $(this).closest('table.dataTable').find('tr > td:first-child input:checkbox')
                .each(function(){
                    var row = $(this).parents().eq(3);
                    if ($(that).is(":checked")) {
                        row.addClass('selected');
                        $(this).prop('checked', true);
                    } else {
                        row.removeClass('selected');
                        $(this).prop('checked', false);
                    }
                });
            datatable.api().draw(false);
        });

        $('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});
        function tooltip_placement(context, source) {
            var $source = $(source);
            var $parent = $source.closest('table');
            var off1 = $parent.offset();
            var w1 = $parent.width();

            var off2 = $source.offset();
            var w2 = $source.width();

            if( parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2) ) return 'right';
            return 'left';
        }
        // dialogs
        $( "#adddialog" ).removeClass('hide').dialog({
                resizable: false,
                width: 'auto',
                modal: true,
                autoOpen: false,
                title: "Add Item",
                title_html: true,
                buttons: [
                    {
                        html: "<i class='icon-save bigger-110'></i>&nbsp; Save",
                        "class" : "btn btn-success btn-xs",
                        click: function() {
                            saveItem(true);
                        }
                    }
                    ,
                    {
                        html: "<i class='icon-remove bigger-110'></i>&nbsp; Cancel",
                        "class" : "btn btn-xs",
                        click: function() {
                            $( this ).dialog( "close" );
                        }
                    }
                ],
                create: function( event, ui ) {
                    // Set maxWidth
                    $(this).css("maxWidth", "460px");
                }
        });
        $( "#editdialog" ).removeClass('hide').dialog({
            resizable: false,
            width: 'auto',
            modal: true,
            autoOpen: false,
            title: "Edit Item",
            title_html: true,
            buttons: [
                {
                    html: "<i class='icon-save bigger-110'></i>&nbsp; Update",
                    "class" : "btn btn-success btn-xs",
                    click: function() {
                        saveItem(false);
                    }
                }
                ,
                {
                    html: "<i class='icon-remove bigger-110'></i>&nbsp; Cancel",
                    "class" : "btn btn-xs",
                    click: function() {
                        $( this ).dialog( "close" );
                    }
                }
            ],
            create: function( event, ui ) {
                // Set maxWidth
                $(this).css("maxWidth", "460px");
            }
        });
        // populate tax records in select boxes
        var taxsel = $(".taxselect");
        taxsel.html('');
        for (key in POS.getTaxTable().rules){
            taxsel.append('<option class="taxid-'+POS.getTaxTable().rules[key].id+'" value="'+POS.getTaxTable().rules[key].id+'">'+POS.getTaxTable().rules[key].name+'</option>');
        }
        // populate category & supplier records in select boxes
        var supsel = $(".supselect");
        supsel.html('');
        supsel.append('<option class="supid-0" value="0">None</option>');
        for (key in suppliers){
            supsel.append('<option class="supid-'+suppliers[key].id+'" value="'+suppliers[key].id+'">'+suppliers[key].name+'</option>');
        }

        var catsel = $(".catselect");
        catsel.html('');
        catsel.append('<option class="catid-0" value="0">None</option>');
        for (key in categories){
            catsel.append('<option class="catid-'+categories[key].id+'" value="'+categories[key].id+'">'+categories[key].name+'</option>');
        }

            // hide loader
            POS.util.hideLoader();
        }).catch(function(error) {
            console.error("Error loading data:", error);
            POS.notifications.error("Failed to load data: " + error.message, "Data Load Error", {delay: 0});
            POS.util.hideLoader();
        });
    });
    // updating records
    function openEditDialog(id){
        var item = stock[id];
        $("#itemid").val(item.id);
        $("#itemname").val(item.name);
        $("#itemaltname").val(item.alt_name);
        $("#itemdesc").val(item.description);
        $("#itemqty").val(item.qty);
        $("#itemtax").val(item.taxid);
        $("#itemcode").val(item.code);
        $("#itemcost").val(item.cost);
        $("#itemprice").val(item.price);
        $("#itemsupplier").val(item.supplierid);
        $("#itemcategory").val(item.categoryid);
        $("#itemtype").val(item.type);
        var modtable = $("#itemmodtable");
        var modselecttable = $("#itemselmodtable");
        modtable.html('');
        modselecttable.html('');
        if (item.hasOwnProperty('modifiers')){
            var mod;
            for (var i=0; i<item.modifiers.length; i++){
                mod = item.modifiers[i];
                if (mod.type=='select'){
                    var modopttable = '';
                    for (var o=0; o<mod.options.length; o++){
                        modopttable += '<tr><td><input onclick="handleSelectCheckbox(this);" type="checkbox" class="modoptdefault" '+(mod.options[o].default==true?'checked="checked"':'')+'/></td><td><input style="width: 130px" type="text" class="modoptname" value="'+mod.options[o].name+'"/></td><td><input type="text" style="width: 60px" class="modoptprice" value="'+mod.options[o].price+'"/></td><td style="text-align: right;"><button class="btn btn-danger btn-xs" onclick="$(this).parent().parent().remove();">X</button></td></tr>';
                    }
                    modselecttable.append('<tr class="selmoditem"><td colspan="4" style="padding-right: 0; padding-left: 0;"><div style="padding-left: 8px; padding-right: 8px;"><label>Name:</label>&nbsp;<input style="width: 130px" type="text" class="modname" value="'+mod.name+'"/><button class="btn btn-danger btn-xs pull-right" style="margin-left: 5px;" onclick="$(this).parents().eq(2).remove();">X</button></div><table class="table" style="margin-top: 5px;">'+modtableheader+'<tbody class="modoptions">'+modopttable+'</tbody></table></td></tr>');
                } else {
                    modtable.append('<tr><td><input type="text" style="width: 40px" class="modqty" value="'+mod.qty+'"/></td><td><input type="text" style="width: 40px" class="modminqty" value="'+mod.minqty+'"/></td><td><input type="text" style="width: 40px" class="modmaxqty" value="'+mod.maxqty+'"/></td><td><input style="width: 130px" type="text" class="modname" value="'+mod.name+'"/></td><td><input type="text" style="width: 60px" class="modprice" value="'+mod.price+'"/></td><td style="text-align: right;"><button class="btn btn-danger btn-xs" onclick="$(this).parent().parent().remove();">X</button></td></tr>');
                }
            }
        }
        $("#editdialog").dialog("open");
        
        // Load variant data for the Variants tab
        loadProductVariants(item.id);
    }
    function addItemModifier(){
        $("#itemmodtable").append('<tr><td><input onchange="var row = $(this).parent().parent(); if ($(this).val()>row.find(\'.modminqty\').val()) row.find(\'.modminqty\').val($(this).val())" type="text" style="width: 40px" class="modqty" value="0"/></td><td><input type="text" style="width: 40px" class="modminqty" value="0"/></td><td><input type="text" style="width: 40px" class="modmaxqty" value="0"/></td><td><input style="width: 130px" type="text" class="modname" value=""/></td><td><input type="text" style="width: 60px" class="modprice" value="0.00"/></td><td style="text-align: right;"><button class="btn btn-danger btn-xs" onclick="$(this).parent().parent().remove();">X</button></td></tr>');
    }
    function addSelectItemModifier(){
        var modseltable = $("#itemselmodtable");
        var modelem = $('<tr class="selmoditem"><td colspan="4" style="padding-right: 0; padding-left: 0;"><div style="padding-left: 8px; padding-right: 8px;"><label>Name:</label>&nbsp;<input style="width: 130px" type="text" class="modname" value=""/><button class="btn btn-danger btn-xs pull-right" style="margin-left: 5px;" onclick="$(this).parents().eq(2).remove();">X</button></div><table class="table" style="margin-top: 5px;">'+modtableheader+'<tbody class="modoptions">'+modselectoption+'</tbody></table></td></tr>');
        modelem.find('.modoptdefault').prop('checked', true);
        modseltable.append(modelem);
    }
    var modtableheader = '<thead class="table-header smaller"><tr><th><small>Default</small></th><th><small>Name</small></th><th><small>Price</small></th><th><button class="btn btn-primary btn-xs pull-right" onclick="addSelectModItem($(this).parents().eq(3).find(\'.modoptions\'));">Add Option</button></th></tr></thead>';
    var modselectoption = '<tr><td><input onclick="handleSelectCheckbox($(this));" type="checkbox" class="modoptdefault"/></td><td><input style="width: 130px" type="text" class="modoptname" value=""/></td><td><input type="text" style="width: 60px" class="modoptprice" value="0.00"/></td><td style="text-align: right;"><button class="btn btn-danger btn-xs" onclick="$(this).parents().eq(1).remove();">X</button></td></tr>';
    function addSelectModItem(elem){
        $(elem).append(modselectoption);
        if (elem.find('tr').length==1) $(elem).find('.modoptdefault').prop('checked', true);
    }
    function handleSelectCheckbox(elem){
        var table = $(elem).parent().parent().parent();
        table.find('.modoptdefault').prop('checked', false);
        $(elem).prop('checked', true);
    }
    function saveItem(isnewitem){
        // show loader
        POS.util.showLoader();
        var item = {};
        var result;
        var costval;
        if (isnewitem){
            // adding a new item
            item.code = $("#newitemcode").val();
            item.qty = $("#newitemqty").val();
            item.name = $("#newitemname").val();
            item.alt_name = $("#newitemaltname").val();
            item.description = $("#newitemdesc").val();
            item.taxid = $("#newitemtax").val();
            costval = $("#newitemcost").val();
            item.cost = (costval ? costval : 0);
            item.price = $("#newitemprice").val();
            item.supplierid = $("#newitemsupplier").val();
            item.categoryid = $("#newitemcategory").val();
            item.type = "general";
            item.modifiers = [];
            result = POS.sendJsonData("items/add", JSON.stringify(item));
            if (result!==false){
                stock[result.id] = result;
                reloadTable();
                $("#adddialog").dialog("close");
            }
        } else {
            // updating an item
            item.id = $("#itemid").val();
            item.code = $("#itemcode").val();
            item.qty = $("#itemqty").val();
            item.name = $("#itemname").val();
            item.alt_name = $("#itemaltname").val();
            item.description = $("#itemdesc").val();
            item.taxid = $("#itemtax").val();
            costval = $("#itemcost").val();
            item.cost = (costval ? costval : 0);
            item.price = $("#itemprice").val();
            item.supplierid = $("#itemsupplier").val();
            item.categoryid = $("#itemcategory").val();
            item.type = $("#itemtype").val();
            item.modifiers = [];
            $("#itemselmodtable .selmoditem").each(function(){
                var mod = {type:"select", options:[]};
                mod.name = $(this).find(".modname").val();
                $(this).find('.modoptions tr').each(function(){
                    var modoption = {};
                    modoption.default = $(this).find(".modoptdefault").is(':checked');
                    modoption.name = $(this).find(".modoptname").val();
                    modoption.price = $(this).find(".modoptprice").val();
                    mod.options.push(modoption);
                });
                item.modifiers.push(mod);
            });
            $("#itemmodtable tr").each(function(){
               var mod = {type:"simple"};
               mod.qty = $(this).find(".modqty").val();
               mod.minqty = $(this).find(".modminqty").val();
               mod.maxqty = $(this).find(".modmaxqty").val();
               mod.name = $(this).find(".modname").val();
               mod.price = $(this).find(".modprice").val();
               item.modifiers.push(mod);
            });
            result = POS.sendJsonData("items/edit", JSON.stringify(item));
            if (result!==false){
                stock[result.id] = result;
                reloadTable();
                $("#editdialog").dialog("close");
            }
        }
        // hide loader
        POS.util.hideLoader();
    }
    function removeItem(id){

        POS.util.confirm("Are you sure you want to delete this item?", function() {
            // show loader
            POS.util.showLoader();
            if (POS.sendJsonData("items/delete", '{"id":'+id+'}')){
                delete stock[id];
                reloadTable();
            }
            // hide loader
            POS.util.hideLoader();
        });
    }

    function removeSelectedItems(){
        var ids = datatable.api().rows('.selected').data().map(function(row){ return row.id });

        POS.util.confirm("Are you sure you want to delete "+ids.length+" selected items?", function() {
            // show loader
            POS.util.showLoader();
            if (POS.sendJsonData("items/delete", '{"id":"'+ids.join(",")+'"}')){
                for (var i=0; i<ids.length; i++){
                    delete stock[ids[i]];
                }
                reloadTable();
            }
            // hide loader
            POS.util.hideLoader();
        });
    }

    function reloadData(){
        stock = POS.getJsonData("items/get");
        reloadTable();
    }
    function reloadTable(){
        var itemarray = [];
        var tempitem;
        for (var key in stock){
            tempitem = stock[key];
            tempitem.taxname = POS.getTaxTable().rules[tempitem.taxid].name;
            itemarray.push(tempitem);
        }
        datatable.fnClearTable();
        if (itemarray.length > 0) {
            datatable.fnAddData(itemarray, false);
        }
        datatable.api().draw(false);
    }
    function exportItems(){

        var filename = "items-"+POS.util.getDateFromTimestamp(new Date());
        filename = filename.replace(" ", "");

        var data = {};
        var ids = datatable.api().rows('.selected').data().map(function(row){ return row.id }).join(',').split(',');

        if (ids && ids.length > 0 && ids[0]!='') {
            for (var i = 0; i < ids.length; i++) {
                var id = ids[i];
                if (stock.hasOwnProperty(id))
                    data[id] = stock[id];
            }
        } else {
            data = stock;
        }

        var csv = POS.data2CSV(
            ['ID', 'Stock Code', 'Name', 'Description', 'Default Qty', 'Unit Cost', 'Unit Price', 'Tax Rule Name', 'Category Name', 'Supplier Name'],
            ['id', 'code', 'name', 'description', 'qty', 'cost', 'price',
                {key:'taxid', func: function(value){ var taxtable = POS.getTaxTable().rules; return taxtable.hasOwnProperty(value) ? taxtable[value].name : 'Unknown'; }},
                {key:'categoryid', func: function(value){ return categories.hasOwnProperty(value) ? categories[value].name : 'Unknown'; }},
                {key:'supplierid', func: function(value){ return suppliers.hasOwnProperty(value) ? suppliers[value].name : 'Unknown'; }}
            ],
            data
        );

        POS.initSave(filename, csv);
    }

    var importdialog = null;
    function openImportDialog(){
        if (importdialog!=null) {
            importdialog.csvImport("destroy");
        }
        importdialog = $("body").csvImport({
            jsonFields: {
                'code': {title:'Stock Code', required: true},
                'name': {title:'Name', required: true},
                'description': {title:'Description', required: false, value: ""},
                'qty': {title:'Default Qty', required: false, value: 1},
                'cost': {title:'Unit Cost', required: false, value: 0.00},
                'price': {title:'Unit Price', required: false, value: ""},
                'tax_name': {title:'Tax Rule Name', required: false, value: ""},
                'supplier_name': {title:'Supplier Name', required: false, value: ""},
                'category_name': {title:'Category Name', required: false, value: ""}
            },
            csvHasHeader: true,
            importOptions: [
                {label: "Set unknown tax names to no tax", id:"skip_tax", checked:false},
                {label: "Create unknown suppliers", id:"add_suppliers", checked:true},
                {label: "Create unknown categories", id:"add_categories", checked:true}
            ],
            // callbacks
            onImport: function(jsondata, options){
                //console.log(options);
                importItems(jsondata, options);
            }
        });
    }

    function importItems(jsondata, options){
        showModalLoader("Importing Items");
        var total = jsondata.length;
        var percent_inc = total / 100;
        setModalLoaderStatus("Uploading data...");
        var data = {"options":options, "import_data": jsondata};
        var result = POS.sendJsonDataAsync('items/import/set', JSON.stringify(data), function(data){
            if (data!==false){
                POS.startEventSourceProcess(
                    '/api/items/import/start',
                    function(data){
                        if (data.hasOwnProperty('progress')) {
                            setModalLoaderSubStatus(data.progress +" of "+ total);
                            var progress = Math.round(percent_inc*data.progress);
                            setModalLoaderProgress(progress);
                        }

                        if (data.hasOwnProperty('status'))
                            setModalLoaderStatus(data.status);

                        if (data.hasOwnProperty('error')) {
                            if (data.error == "OK") {
                                showModalCloseButton('Item Import Complete!');
                            } else {
                                showModalCloseButton("Error Importing Items", data.error);
                            }
                            if (data.hasOwnProperty('data')){
                                // update table with imported items
                                for (var i in data.data) {
                                    if (data.data.hasOwnProperty(i))
                                        stock[i] = data.data[i];
                                }
                                reloadTable();
                            }
                        }
                    },
                    function(e){
                        showModalCloseButton("Event feed failed "+ e.message);
                    }
                );
            } else {
                showModalCloseButton("Item Import Failed!");
            }
        }, function(error){
            showModalCloseButton("Item Import Failed!", error);
        });
        if (!result)
            showModalCloseButton("Item Import Failed!");
    }

    var eventuiinit = false;
    function initModalLoader(title){
        $("#modalloader").removeClass('hide').dialog({
            resizable: true,
            width: 400,
            modal: true,
            autoOpen: false,
            title: title,
            title_html: true,
            closeOnEscape: false,
            open: function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }
        });
    }
    function showModalLoader(title){
        if (!eventuiinit){
            initModalLoader(title);
            eventuiinit = true;
        }
        $("#modalloader_status").text('Initializing...');
        $("#modalloader_substatus").text('');
        $("#modalloader_cbtn").hide();
        $("#modalloader_img").show();
        $("#modalloader_prog").show();
        var modalloader = $("#modalloader");
        modalloader.dialog('open');
    }
    function setModalLoaderProgress(progress){
        $("#modalloader_progbar").attr('width', progress+"%")
    }
    function showModalCloseButton(result, substatus){
        $("#modalloader_status").text(result);
        setModalLoaderSubStatus(substatus? substatus : '');
        $("#modalloader_img").hide();
        $("#modalloader_prog").hide();
        $("#modalloader_cbtn").show();
    }
    function setModalLoaderStatus(status){
        $("#modalloader_status").text(status);
    }
    function setModalLoaderSubStatus(status){
        $("#modalloader_substatus").text(status);
    }

    // Variant Management Functions
    var currentItemId = null;

    function loadProductVariants(itemId) {
        currentItemId = itemId;
        loadProductAttributes();
        loadProductVariantsList();
        loadLocationsForStock();
        loadAttributesForSelect();
    }

    function loadProductAttributes() {
        if (!currentItemId) return;
        
        POS.sendJsonDataAsync("attributes/get", JSON.stringify({product_id: currentItemId}), function(data) {
            if (data && data.data) {
                populateAttributesTable(data.data);
            }
        });
    }

    function populateAttributesTable(attributes) {
        var tbody = $("#attributes-table tbody");
        tbody.empty();
        
        for (var i = 0; i < attributes.length; i++) {
            var attr = attributes[i];
            var row = '<tr>' +
                '<td>' + attr.id + '</td>' +
                '<td>' + attr.name + '</td>' +
                '<td>' + (attr.display_name || attr.name) + '</td>' +
                '<td>' +
                    '<button class="btn btn-xs btn-danger" onclick="deleteAttribute(' + attr.id + ')">Delete</button>' +
                '</td>' +
                '</tr>';
            tbody.append(row);
        }
    }

    function openAddAttributeDialog() {
        $("#new-attribute-name").val("");
        $("#new-attribute-display-name").val("");
        
        $("#add-attribute-dialog").dialog({
            modal: true,
            title: "Add Product Attribute",
            width: 400,
            buttons: {
                "Add": function() {
                    addAttribute();
                    $(this).dialog("close");
                },
                "Cancel": function() {
                    $(this).dialog("close");
                }
            }
        });
    }

    function addAttribute() {
        var name = $("#new-attribute-name").val().trim();
        var displayName = $("#new-attribute-display-name").val().trim();
        
        if (!name) {
            POS.notifications.error("Attribute name is required");
            return;
        }
        
        POS.sendJsonDataAsync("attributes/add", JSON.stringify({
            product_id: currentItemId,
            name: name,
            display_name: displayName || name
        }), function(data) {
            if (data && data.errorCode === "OK") {
                loadProductAttributes();
                POS.notifications.success("Attribute added successfully");
            } else {
                POS.notifications.error("Failed to add attribute");
            }
        });
    }

    function deleteAttribute(attributeId) {
        if (confirm("Are you sure you want to delete this attribute?")) {
            POS.sendJsonDataAsync("attributes/delete", JSON.stringify({
                id: attributeId
            }), function(data) {
                if (data && data.errorCode === "OK") {
                    loadProductAttributes();
                    POS.notifications.success("Attribute deleted successfully");
                } else {
                    POS.notifications.error("Failed to delete attribute");
                }
            });
        }
    }

    function loadAttributeValues() {
        var attributeId = $("#attribute-select").val();
        if (!attributeId) {
            $("#attribute-values-table tbody").empty();
            return;
        }
        
        POS.sendJsonDataAsync("attribute-values/get", JSON.stringify({attribute_id: attributeId}), function(data) {
            if (data && data.data) {
                populateAttributeValuesTable(data.data);
            }
        });
    }

    function populateAttributeValuesTable(values) {
        var tbody = $("#attribute-values-table tbody");
        tbody.empty();
        
        for (var i = 0; i < values.length; i++) {
            var value = values[i];
            var row = '<tr>' +
                '<td>' + value.id + '</td>' +
                '<td>' + value.value + '</td>' +
                '<td>' + (value.display_value || value.value) + '</td>' +
                '<td>' +
                    '<button class="btn btn-xs btn-danger" onclick="deleteAttributeValue(' + value.id + ')">Delete</button>' +
                '</td>' +
                '</tr>';
            tbody.append(row);
        }
    }

    function openAddAttributeValueDialog() {
        var attributeId = $("#attribute-select").val();
        if (!attributeId) {
            POS.notifications.error("Please select an attribute first");
            return;
        }
        
        $("#new-value-attribute-id").val(attributeId);
        $("#new-attribute-value").val("");
        $("#new-attribute-display-value").val("");
        
        $("#add-attribute-value-dialog").dialog({
            modal: true,
            title: "Add Attribute Value",
            width: 400,
            buttons: {
                "Add": function() {
                    addAttributeValue();
                    $(this).dialog("close");
                },
                "Cancel": function() {
                    $(this).dialog("close");
                }
            }
        });
    }

    function addAttributeValue() {
        var attributeId = $("#new-value-attribute-id").val();
        var value = $("#new-attribute-value").val().trim();
        var displayValue = $("#new-attribute-display-value").val().trim();
        
        if (!value) {
            POS.notifications.error("Attribute value is required");
            return;
        }
        
        POS.sendJsonDataAsync("attribute-values/add", JSON.stringify({
            attribute_id: attributeId,
            value: value,
            display_value: displayValue || value
        }), function(data) {
            if (data && data.errorCode === "OK") {
                loadAttributeValues();
                POS.notifications.success("Attribute value added successfully");
            } else {
                POS.notifications.error("Failed to add attribute value");
            }
        });
    }

    function deleteAttributeValue(valueId) {
        if (confirm("Are you sure you want to delete this attribute value?")) {
            POS.sendJsonDataAsync("attribute-values/delete", JSON.stringify({
                id: valueId
            }), function(data) {
                if (data && data.errorCode === "OK") {
                    loadAttributeValues();
                    POS.notifications.success("Attribute value deleted successfully");
                } else {
                    POS.notifications.error("Failed to delete attribute value");
                }
            });
        }
    }

    function loadProductVariantsList() {
        if (!currentItemId) return;
        
        POS.sendJsonDataAsync("variants/get", JSON.stringify({product_id: currentItemId}), function(data) {
            if (data && data.data) {
                populateVariantsTable(data.data);
            }
        });
    }

    function populateVariantsTable(variants) {
        var tbody = $("#variants-table tbody");
        tbody.empty();
        
        for (var i = 0; i < variants.length; i++) {
            var variant = variants[i];
            var attributes = variant.attributes || [];
            var attrDisplay = attributes.map(function(attr) {
                return attr.display_value || attr.value;
            }).join(", ");
            
            var row = '<tr>' +
                '<td>' + variant.id + '</td>' +
                '<td>' + (variant.sku || '') + '</td>' +
                '<td>' + (variant.name || 'Variant ' + variant.id) + '</td>' +
                '<td>' + POS.util.currencyFormat(variant.price || 0) + '</td>' +
                '<td>' + POS.util.currencyFormat(variant.cost || 0) + '</td>' +
                '<td>' + attrDisplay + '</td>' +
                '<td>' +
                    '<button class="btn btn-xs btn-danger" onclick="deleteVariant(' + variant.id + ')">Delete</button>' +
                '</td>' +
                '</tr>';
            tbody.append(row);
        }
    }

    function openAddVariantDialog() {
        // Load attributes for selection
        POS.sendJsonDataAsync("admin/attributes", JSON.stringify({product_id: currentItemId}), function(data) {
            if (data && data.data) {
                buildVariantAttributesSelection(data.data);
                
                $("#new-variant-sku").val("");
                $("#new-variant-price").val("");
                $("#new-variant-cost").val("");
                
                $("#add-variant-dialog").dialog({
                    modal: true,
                    title: "Add Product Variant",
                    width: 600,
                    height: 500,
                    buttons: {
                        "Add": function() {
                            addVariant();
                            $(this).dialog("close");
                        },
                        "Cancel": function() {
                            $(this).dialog("close");
                        }
                    }
                });
            }
        });
    }

    function buildVariantAttributesSelection(attributes) {
        var container = $("#variant-attributes-selection");
        container.empty();
        
        if (attributes.length === 0) {
            container.html("<p>No attributes defined for this product.</p>");
            return;
        }
        
        for (var i = 0; i < attributes.length; i++) {
            var attr = attributes[i];
            var attrDiv = '<div class="form-group">' +
                '<label>' + attr.display_name + ':</label>' +
                '<select class="form-control variant-attribute-select" data-attribute-id="' + attr.id + '">' +
                '<option value="">Select ' + attr.display_name + '</option>' +
                '</select>' +
                '</div>';
            container.append(attrDiv);
            
            // Load values for this attribute
            loadAttributeValuesForSelection(attr.id);
        }
    }

    function loadAttributeValuesForSelection(attributeId) {
        POS.sendJsonDataAsync("attribute-values/get", JSON.stringify({attribute_id: attributeId}), function(data) {
            if (data && data.data) {
                var select = $('select[data-attribute-id="' + attributeId + '"]');
                select.empty();
                select.append('<option value="">Select value</option>');
                
                for (var i = 0; i < data.data.length; i++) {
                    var value = data.data[i];
                    select.append('<option value="' + value.id + '">' + (value.display_value || value.value) + '</option>');
                }
            }
        });
    }

    function addVariant() {
        var sku = $("#new-variant-sku").val().trim();
        var price = $("#new-variant-price").val().trim();
        var cost = $("#new-variant-cost").val().trim();
        
        // Collect selected attribute values
        var attributes = [];
        $(".variant-attribute-select").each(function() {
            var valueId = $(this).val();
            if (valueId) {
                attributes.push(parseInt(valueId));
            }
        });
        
        if (attributes.length === 0) {
            POS.notifications.error("Please select at least one attribute value");
            return;
        }
        
        var variantData = {
            product_id: currentItemId,
            sku: sku,
            attribute_value_ids: attributes
        };
        
        if (price) variantData.price = parseFloat(price);
        if (cost) variantData.cost = parseFloat(cost);
        
        POS.sendJsonDataAsync("variants/add", JSON.stringify(variantData), function(data) {
            if (data && data.errorCode === "OK") {
                loadProductVariantsList();
                POS.notifications.success("Variant added successfully");
            } else {
                POS.notifications.error("Failed to add variant: " + (data.error || "Unknown error"));
            }
        });
    }

    function deleteVariant(variantId) {
        if (confirm("Are you sure you want to delete this variant?")) {
            POS.sendJsonDataAsync("variants/delete", JSON.stringify({
                id: variantId
            }), function(data) {
                if (data && data.errorCode === "OK") {
                    loadProductVariantsList();
                    POS.notifications.success("Variant deleted successfully");
                } else {
                    POS.notifications.error("Failed to delete variant");
                }
            });
        }
    }

    function loadLocationsForStock() {
        POS.sendJsonDataAsync("locations/get", JSON.stringify(""), function(data) {
            if (data) {
                var select = $("#stock-location-select");
                select.empty();
                select.append('<option value="">Select Location</option>');
                
                for (var i = 0; i < data.length; i++) {
                    var location = data[i];
                    select.append('<option value="' + location.id + '">' + location.name + '</option>');
                }
            }
        });
    }

    function loadAttributesForSelect() {
        if (!currentItemId) return;
        
        POS.sendJsonDataAsync("attributes/get", JSON.stringify({product_id: currentItemId}), function(data) {
            if (data && data.data) {
                var select = $("#attribute-select");
                select.empty();
                select.append('<option value="">Select Attribute</option>');
                
                for (var i = 0; i < data.data.length; i++) {
                    var attr = data.data[i];
                    select.append('<option value="' + attr.id + '">' + attr.display_name + '</option>');
                }
            }
        });
    }

    function loadVariantStock() {
        var locationId = $("#stock-location-select").val();
        if (!locationId || !currentItemId) {
            $("#variant-stock-table tbody").empty();
            return;
        }
        
        POS.sendJsonDataAsync("variants/stock/get", JSON.stringify({
            product_id: currentItemId,
            location_id: locationId
        }), function(data) {
            if (data && data.data) {
                populateVariantStockTable(data.data);
            }
        });
    }

    function populateVariantStockTable(stockData) {
        var tbody = $("#variant-stock-table tbody");
        tbody.empty();
        
        for (var i = 0; i < stockData.length; i++) {
            var stock = stockData[i];
            var row = '<tr>' +
                '<td>' + (stock.variant_name || 'Variant ' + stock.variant_id) + '</td>' +
                '<td>' + (stock.stock_level || 0) + '</td>' +
                '<td>' +
                    '<button class="btn btn-xs btn-primary" onclick="editVariantStock(' + stock.variant_id + ', ' + stock.location_id + ', ' + (stock.stock_level || 0) + ', \'' + (stock.variant_name || '') + '\', \'' + (stock.location_name || '') + '\')">Edit</button>' +
                '</td>' +
                '</tr>';
            tbody.append(row);
        }
    }

    function editVariantStock(variantId, locationId, currentStock, variantName, locationName) {
        $("#edit-stock-variant-name").text(variantName || 'Variant ' + variantId);
        $("#edit-stock-location-name").text(locationName);
        $("#edit-stock-current").text(currentStock);
        $("#edit-stock-new").val(currentStock);
        
        $("#edit-variant-stock-dialog").data("variant-id", variantId);
        $("#edit-variant-stock-dialog").data("location-id", locationId);
        
        $("#edit-variant-stock-dialog").dialog({
            modal: true,
            title: "Edit Variant Stock",
            width: 400,
            buttons: {
                "Save": function() {
                    saveVariantStock();
                    $(this).dialog("close");
                },
                "Cancel": function() {
                    $(this).dialog("close");
                }
            }
        });
    }

    function saveVariantStock() {
        var variantId = $("#edit-variant-stock-dialog").data("variant-id");
        var locationId = $("#edit-variant-stock-dialog").data("location-id");
        var newStock = parseFloat($("#edit-stock-new").val()) || 0;
        
        POS.sendJsonDataAsync("variants/stock/set", JSON.stringify({
            variant_id: variantId,
            location_id: locationId,
            stock_level: newStock
        }), function(data) {
            if (data && data.errorCode === "OK") {
                loadVariantStock();
                POS.notifications.success("Stock updated successfully");
            } else {
                POS.notifications.error("Failed to update stock");
            }
        });
    }

</script>
<div id="modalloader" class="hide" style="width: 360px; height: 320px; text-align: center;">
    <img id="modalloader_img" style="width: 128px; height: auto;" src="../assets/images/cloud_loader.gif"/>
    <div id="modalloader_prog" class="progress progress-striped active">
        <div class="progress-bar" id="modalloader_progbar" style="width: 100%;"></div>
    </div>
    <h4 id="modalloader_status">Initializing...</h4>
    <h5 id="modalloader_substatus"></h5>
    <button id="modalloader_cbtn" class="btn btn-primary" style="display: none; margin-top:40px;" onclick="$('#modalloader').dialog('close');">Close</button>
</div>

<!-- Variant Management Dialogs -->
<div id="add-attribute-dialog" class="hide">
    <table>
        <tr>
            <td style="text-align: right;"><label>Name:</label></td>
            <td><input id="new-attribute-name" type="text" placeholder="e.g., Color, Size"/></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Display Name:</label></td>
            <td><input id="new-attribute-display-name" type="text" placeholder="e.g., Color, Size"/></td>
        </tr>
    </table>
</div>

<div id="add-attribute-value-dialog" class="hide">
    <table>
        <tr>
            <td style="text-align: right;"><label>Attribute:</label></td>
            <td>
                <select id="new-value-attribute-id" class="form-control">
                    <option value="">Select Attribute</option>
                </select>
            </td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Value:</label></td>
            <td><input id="new-attribute-value" type="text" placeholder="e.g., Red, Large"/></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Display Value:</label></td>
            <td><input id="new-attribute-display-value" type="text" placeholder="e.g., Red, Large"/></td>
        </tr>
    </table>
</div>

<div id="add-variant-dialog" class="hide">
    <div style="max-height: 400px; overflow-y: auto;">
        <table>
            <tr>
                <td style="text-align: right;"><label>SKU:</label></td>
                <td><input id="new-variant-sku" type="text" placeholder="Unique SKU code"/></td>
            </tr>
            <tr>
                <td style="text-align: right;"><label>Price:</label></td>
                <td><input id="new-variant-price" type="text" placeholder="Override price (optional)"/></td>
            </tr>
            <tr>
                <td style="text-align: right;"><label>Cost:</label></td>
                <td><input id="new-variant-cost" type="text" placeholder="Override cost (optional)"/></td>
            </tr>
        </table>
        <h4>Attributes</h4>
        <div id="variant-attributes-selection">
            <!-- Attribute selection will be populated here -->
        </div>
    </div>
</div>

<div id="edit-variant-stock-dialog" class="hide">
    <table>
        <tr>
            <td style="text-align: right;"><label>Variant:</label></td>
            <td><span id="edit-stock-variant-name"></span></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Location:</label></td>
            <td><span id="edit-stock-location-name"></span></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>Current Stock:</label></td>
            <td><span id="edit-stock-current"></span></td>
        </tr>
        <tr>
            <td style="text-align: right;"><label>New Stock:</label></td>
            <td><input id="edit-stock-new" type="number" min="0" step="0.01"/></td>
        </tr>
    </table>
</div>

<style type="text/css">
    #itemstable_processing {
        display: none;
    }

    body.dragging, body.dragging * {
        cursor: move !important;
    }

    .dragged {
        position: absolute;
        opacity: 0.8;
        z-index: 2000;
    }

    #dest_table li.excluded, #source_table li.excluded {
        opacity: 0.8;
        background-color: #f5f5f5;
    }

    .placeholder {
        position: relative;
        height: 40px;
    }

    .placeholder:before {
        position: absolute;
        /** Define arrowhead **/
    }
</style>