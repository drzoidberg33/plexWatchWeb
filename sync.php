<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>plexWatch</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="css/plexwatch.css" rel="stylesheet">
    <link href="css/plexwatch-tables.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet" >
    <style type="text/css">
        body {
            padding-top: 60px;
            padding-bottom: 40px;
        }
        .sidebar-nav {
            padding: 9px 0;
        }
    </style>

    <!-- touch icons -->
    <link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/icon_iphone.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/icon_ipad.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/icon_iphone@2x.png">
    <link rel="apple-touch-icon" sizes="144x144" href="images/icon_ipad@2x.png">
</head>

<body>

<div class="container">

    <div class="navbar navbar-fixed-top">
        <div class="navbar-inner">
            <a href="index.php"><div class="logo hidden-phone"></div></a>
            <ul class="nav">
                <li><a href="index.php"><i class="icon-2x icon-home icon-white" data-toggle="tooltip" data-placement="bottom" title="Home" id="home"></i></a></li>
                <li><a href="history.php"><i class="icon-2x icon-calendar icon-white" data-toggle="tooltip" data-placement="bottom" title="History" id="history"></i></a></li>
                <li><a href="stats.php"><i class="icon-2x icon-tasks icon-white" data-toggle="tooltip" data-placement="bottom" title="Stats" id="stats"></i></a></li>
                <li><a href="users.php"><i class="icon-2x icon-group icon-white" data-toggle="tooltip" data-placement="bottom" title="Users" id="users"></i></a></li>
                <li><a href="charts.php"><i class="icon-2x icon-bar-chart icon-white" data-toggle="tooltip" data-placement="bottom" title="Charts" id="charts"></i></a></li>
                <li class="active"><a href="sync.php"><i class="icon-2x icon-refresh icon-white" data-toggle="tooltip" data-placement="bottom" title="Sync Stats" id="sync"></i></a></li>
                <li><a href="settings.php"><i class="icon-2x icon-wrench icon-white" data-toggle="tooltip" data-placement="bottom" title="Settings" id="settings"></i></a></li>
            </ul>
        </div>
    </div>
</div>


<div class="clear"></div>

<div class="container-fluid">
    <div class="row-fluid">
        <div class="span12">
            <div class='wellheader'>
                <div class="dashboard-wellheader-no-chevron">
                    <h2><i class="icon-large icon-refresh icon-white"></i> Sync Stats</h2>
                </div>
            </div>
        </div>
    </div>
</div>


<div class='container-fluid'>
    <div class='row-fluid'>
        <div class='span12'>
            <div class="alert alert-warning" id="error-msg" style="display: none;">Failed to access Plex Media Server. Please check your settings.</div>
            <div class="spinner" id="sync-spinner" style="display: none;"></div>
            <div class='wellbg' id="sync-table-div" style="display: none;">
                <table id="sync-table" class='display' width='100%'>
                    <thead>
                    <tr>
                        <th align='left'><i class='icon-sort icon-white'></i> State</th>
                        <th align='left'><i class='icon-sort icon-white'></i> Server</th>
                        <th align='left'><i class='icon-sort icon-white'></i> User Name</th>
                        <th align='left'><i class='icon-sort icon-white'></i> Title</th>
                        <th align='left'><i class='icon-sort icon-white'></i> Type</th>
                        <th align='left'><i class='icon-sort icon-white'></i> Device</th>
                        <th align='left'><i class='icon-sort icon-white'></i> Platform</th>
                        <th align='left'><i class='icon-sort icon-white'></i> Total Size</th>
                        <th align='left'><i class='icon-sort icon-white'></i> Total Items</th>
                        <th align='left'><i class='icon-sort icon-white'></i> Downloaded</th>
                        <th align='left'><i class='icon-sort icon-white'></i> Items Downloaded</th>
                    </tr>
                    </thead>
                </table>

            </div>
        </div>
    </div>
</div>


<footer>

</footer>

<!-- javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="js/jquery-2.0.3.js"></script>
<script src="js/bootstrap.js"></script>
<script src="js/jquery.dataTables.js"></script>
<script src="js/jquery.dataTables.plugin.bootstrap_pagination.js"></script>
<script src="js/spin.min.js"></script>

<script>
    var syncTableOptions = {
        "bDestroy": true,
        "bPaginate": true,
        "bLengthChange": true,
        "bFilter": true,
        "bSort": true,
        "bInfo": true,
        "bAutoWidth": true,
        "aaSorting": [[ 0, "desc" ], [ 10, "asc"]],
        "bStateSave": false,
        "bSortClasses": true,
        "sPaginationType": "bootstrap",
        "bProcessing": true,
        "aoColumns": [
            { "mData": "item_state" },
            { "mData": "server_name" },
            { "mData": "user_name" },
            { "mData": "item_title" },
            { "mData": "item_type" },
            { "mData": "device_name" },
            { "mData": "device_platform" },
            { "mData": "item_total_size" },
            { "mData": "item_count" },
            { "mData": "item_downloaded_count" },
            { "mData": "item_downloaded_percent" }
        ],
        "aoColumnDefs": [
            {
                "aTargets": [0],
                "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    if (sData === 'complete') {
                        $(nTd).html('Complete');
                    } else if (sData === 'pending') {
                        $(nTd).addClass('currentlyWatching');
                        $(nTd).html('Pending...');
                    } else {
                        $(nTd).html(sData);
                    }
                }
            },
            {
                "aTargets": [4],
                "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    if (sData === 'track') {
                        $(nTd).html('Music');
                    } else if (sData === 'movie') {
                        $(nTd).html('Movie');
                    } else if (sData === 'episode') {
                        $(nTd).html('TV Show');
                    } else if (sData === 'photo') {
                            $(nTd).html('Photo');
                    } else {
                        $(nTd).html(sData);
                    }
                }
            },
            {
                "aTargets": [7],
                "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    if(sData !== '') {
                        $(nTd).html(Math.round((sData/1024)/1024)+"MB");
                    } else {
                        $(nTd).html('');
                    }
                }
            },
            {
                "aTargets": [9],
                "bVisible": false,
                "bSearchable": false
            },
            {
                "aTargets": [10],
                "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    if(sData !== '') {
                        $(nTd).html('<span class="badge badge-warning">' + sData + '%</span>');
                    }
                }
            }
        ]
    };

    $(document).ready(function () {
        document.getElementById("sync-table-div").style.display = "none";
        document.getElementById("sync-spinner").style.display = "block";
        syncTable = $('#sync-table').DataTable(syncTableOptions);
        $.ajax({
            url: "includes/synced_items.php",
            //data: {limit: 500},
            //type:'get',
            dataType: "json",
            success: function(data){
                if (data.status === 'success') {
                    document.getElementById("sync-table-div").style.display = "block";
                    document.getElementById("sync-spinner").style.display = "none";
                    syncTableOptions.aaData = data.data;
                    syncTable = $('#sync-table').DataTable(syncTableOptions);
                } else {
                    document.getElementById("sync-spinner").style.display = "none";
                    document.getElementById("error-msg").style.display = "block";
                }
            }
        });
    });

</script>

<script>
    var opts = {
        lines: 8, // The number of lines to draw
        length: 8, // The length of each line
        width: 4, // The line thickness
        radius: 5, // The radius of the inner circle
        corners: 1, // Corner roundness (0..1)
        rotate: 0, // The rotation offset
        direction: 1, // 1: clockwise, -1: counterclockwise
        color: '#fff', // #rgb or #rrggbb or array of colors
        speed: 1, // Rounds per second
        trail: 60, // Afterglow percentage
        shadow: false, // Whether to render a shadow
        hwaccel: false, // Whether to use hardware acceleration
        className: 'spinner', // The CSS class to assign to the spinner
        zIndex: 2e9, // The z-index (defaults to 2000000000)
        top: '50%', // Top position relative to parent
        left: '50%' // Left position relative to parent
    };
    var target = document.getElementById('sync-spinner');
    var spinner = new Spinner(opts).spin(target);
</script>

<script>
    $(document).ready(function() {
        $('#home').tooltip();
    });
    $(document).ready(function() {
        $('#history').tooltip();
    });
    $(document).ready(function() {
        $('#users').tooltip();
    });
    $(document).ready(function() {
        $('#charts').tooltip();
    });
    $(document).ready(function() {
        $('#settings').tooltip();
    });
    $(document).ready(function() {
        $('#stats').tooltip();
    });
    $(document).ready(function() {
        $('#sync').tooltip();
    });
</script>

</body>
</html>
