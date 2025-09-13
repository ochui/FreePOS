<div class="page-header">
    <h1>
        Utilities
        <small>
            <i class="icon-double-angle-right"></i>
            Manage Application Data
        </small>
    </h1>
</div><!-- /.page-header -->

<div class="col-sm-12">

    <div class="col-sm-5">
        <div class="widget-box transparent">
            <div class="widget-header widget-header-flat">
                <h4 class="lighter">
                    <i class="icon-edit blue"></i>
                    Logs
                </h4>
            </div>

            <div class="widget-body">
                <div class="widget-main " style="text-align: center;">
                    <select id="loglist" size="10" style="width: 300px;">
                        <option>Loading...</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-7">
        <div class="widget-box transparent">
            <div class="widget-header widget-header-flat">
                <h4 class="lighter">
                    <i class="icon-edit blue"></i>
                    Templates
                </h4>
            </div>
            <div class="widget-body" style="height: 200px;">
                <div class="space-8"></div>
                <button class="btn btn-primary" onclick="changehash('!templates');">Template Editor</button>
                <button class="btn btn-primary" onclick="restoreTemplates();">Restore Default Templates</button>
            </div>
        </div>

    </div>
</div>
<div id="logdialog" style="display:none; padding:10px; background-color: white;" title="Log Contents">
    <div id="logcontents" style="font-family: monospace; white-space: pre;"></div>
</div>
<script type="text/javascript">
    function populateLogs() {
        var logs = POS.getJsonData("logs/list");
        if (logs !== false) {
            $("#loglist").html('');
            for (var i in logs) {
                $("#loglist").append('<option onclick="viewLog($(this).val())" value="' + logs[i] + '">' + logs[i].split('.')[0] + '</option>');
            }
        }
    }

    function viewLog(filename) {
        var log = POS.sendJsonData("logs/read", JSON.stringify({
            filename: filename
        }));
        if (log != false) {
            log = log.replace(/\n/g, "<br/>");
            $("#logcontents").html(log);
            $("#logdialog").dialog('open');
        }
    }

    function restoreTemplates() {
        POS.util.confirm("Are you sure you want to restore the default template files?\nThis will DESTROY all changes you have made to the default templates.", function() {
            POS.getJsonData('templates/restore');
        });
    }


    $(function() {
        $("#logdialog").dialog({
            height: 420,
            width: 'auto',
            maxWidth: 650,
            modal: true,
            closeOnEscape: false,
            autoOpen: false,
            open: function(event, ui) {},
            close: function(event, ui) {},
            create: function(event, ui) {
                // Set maxWidth
                $(this).css("maxWidth", "650px");
            }
        });


        populateLogs();
        // hide loader
        POS.util.hideLoader();
    });
</script>