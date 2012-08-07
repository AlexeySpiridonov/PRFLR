<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <script type="text/javascript" src="jquery-1.7.2.min.js"></script> 
        <style type="text/css">
            table {font-size:14px;}
            table.m td {margin-top:4px;padding-right:16px;}
            table.profiler_grid {width: 100.5% !important}
            tr.f td {font-size:30px;}
            table tr td.ff {font-size:15px;}
            table tr td.dd {font-size:18px;padding-top:10px; padding-bottom: 10px}
            table tr td.ll {padding-right: 0px !important}
            .timer {font-size:18px;}
            .group {font-size:15px;}
            .info {font-size:12px;}
            .thread {font-size:10px;}
            .b0 {border:none!important;padding-right:0px!important;}
            .b1 td {border-bottom:1px solid #808080;}
            table.b td {margin-top:0px;border:0px solid #808080;font-size:10px;}
            
            .profiler_block {display: none;padding-bottom:50px}
            .refresh_button {margin-left: 10px;}
            
            #revert_gif {position: relative}
            
            .r1 {}
            .r2 {width:270px}
            .r3 {width:20px}
            .r4 {width:30px}
            
            .f10 {font-size:10px;}
            .f12 {font-size:12px;}
            .f15 {font-size:15px;}
            .f18 {font-size:18px;}
            .f20 {font-size:20px;}
            .f25 {font-size:25px;}
            .f30 {font-size:30px;}
        </style>
        <script type="text/javascript">
            var inProgress    = false;
            var lineBarLength = 265;

            $(document).ready(function(){
                $.ajaxSetup({cache: false}); // turn off ajax cache

                $('#tab_menu a').click(function(){
                    if (inProgress) {
                        return false;
                    }
                    $('.profiler_block :input').unbind();
                    $('.profiler_block').hide();
                    var selector = $(this).attr('href');
                    $(selector).show();
                    $(selector+' :input').not('input[name="filter"]').change(function(e){
                        renderDataGrid(selector);
                    });
                    $(selector+' .refresh_button').click(function(){
                        renderDataGrid(selector);
                    });
                    renderDataGrid(selector, true);
                    // move revert gif
                    var offset = 0;
                    $(this).prevAll().each(function(i, item){
                        offset += $(item).width() + 17;
                    });
                    $('#revert_gif').css('left', offset + $(this).width()/3.7);
                    return false;
                });

                var anchor = window.location.hash;
                if (anchor.length > 0) {
                    $('#tab_menu a[href="'+anchor+'"]').click();
                } else {
                    $('#tab_menu a[href="#last"]').click();
                }
            });
        </script>
    </head>
    <body>
        <div>
            <table>
                <td width="250">
                    <img src="prflr.gif" width="250" />
                </td>
                <td>
                    <br></br>
                    <h1 id="tab_menu">
                        <a href="#last">Raw Timers</a> | <a href="#agg">Statistic</a><!-- | <a href="#slow">SlowTop</a> | <a href="#groups">Groups</a> | <a href="#time">TimeGraph</a>--> | <a href="#settings">Settings</a>
                    </h1>
                    <img id="revert_gif" src="revert.gif" border=0 height="40"/>
                </td>
            </table>
            <div style="padding:15px;">

                <!-- RAW TIMERS -->
                <div id="last" class="profiler_block">
                    <!--<h1>Raw Timers</h1>-->
                    
                    <form action="" method="GET" onsubmit="$(this).find('.refresh_button').click();return false">
                    <table class="m" border="0" cellpadding="0" cellspacing="0" width="100%"> 
                        <tr class="f bo">
                            <td class='b0'></td>
                            <td>Group / Timer / Info / Thread</td>
                            <td></td>
                            <td><!--Statistic--></td>
                            <td class="ff" align="right">
                                Total<br />
                                Count
                            </td>
                        </tr>
                        <tr>
                            <td class='b0'>#><input type="hidden" name="r" value="stat_last" /></td>
                            <td colspan="10">
                                <input name="filter" style="width:100%; height:35px; font-size:14px;" value="*/*/*/*" />
                            </td>
                        </tr>
                        <tr>
                            <td class='b0'>&nbsp;</td>
                            <td colspan="10" align="right" style="padding-right: 8px !important;">
                                <div style="float:left">
                                     <!--Sort By: 
                                    <select style="width:200px;" name="sortby">
                                        <option value="total">Total Time</option>
                                        <option value="count">Count</option>
                                        <option value="dispersion">Dispersion</option>
                                    </select>
			            -->
                                </div>
                                <div style="float:right">
                                    <button class="refresh_button">Refresh</button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class='b0'></td>
                            <td colspan="20">
                                <table class="m profiler_grid" border="0" cellpadding="0" cellspacing="0" width="100%"></table>
                            </td>
                        </tr>
                    </table>
                    </form>
                </div> <!-- END OF last -->

                <!-- STATISTIC -->
                <div id="agg" class="profiler_block">

                    <!--<h1>Statistic</h1>-->

                    <form action="" method="GET" onsubmit="$(this).find('.refresh_button').click();return false">
                        <input type="hidden" name="r" value="stat_aggregate" />
                    <table class="m" border="0" cellpadding="0" cellspacing="0" width="100%"> 
                        <tr class="f bo">
                            <td class='b0 r0'></td>
                            <td class="f30">Group / Timer / Info / Thread</td>
                            <td class="ff r1 f30">Statistic</td>
                            <td class="r3"></td>
                            <td class="r4 f15">
				Total<br/>
                                Count
                            </td>
                        </tr>
                        <tr>
                            <td class='b0'>#></td>
                            <td width="90%">
                                <input name="filter" style="width:100%; height:35px; font-size:14px;" value="*/*/*/*" />
                            </td>
                            <td align="left">
                                Sort By: 
                                <select style="width:200px;" name="sortby">
                                    <option value="max">Max Time (red)</option>
                                    <option value="average">Avg Time (green)</option>
                                    <option value="min">Min Time (blue)</option>
                                    <option value="total">Total Time</option>
                                    <option value="count">Count</option>
                                    <option value="dispersion">Dispersion</option>
                                </select>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class='b0'></td>
                            <td align="right">
                                <div style="float:left">
                                    Group By: 
                                    <select style="width:200px;" name="groupby">
                                        <option value="group,timer">Group + Timer</option>
                                        <option value="group">Group</option>
                                        <option value="timer">Timer</option>
                                    </select>
                                </div>
                                <div style="float:right">
                                    <button class="refresh_button">Refresh</button>
                                </div>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class='b0'></td>
                            <td colspan="4">
                                <table class="m profiler_grid" border="0" cellpadding="0" cellspacing="0" width="100%"></table>
                            </td>
                        </tr>
                    </table>
                    </form>
                </div> <!-- END OF agg -->

                <!-- SETTINGS -->
                <div id="settings" class="profiler_block">
                    <h1>Settings</h1>
                    in near future :)
                </div> <!-- END OF settings -->

            </div>

        </div>
    </body>
</html>

<script type="text/javascript">
    function renderDataGrid(selector, checkEmpty)
    {
        var elem = $(selector);
        var grid = elem.find('table.profiler_grid');
        var button = elem.find('.refresh_button');
        var query  = elem.find(':input').serialize();
        
        if (grid.length == 0) {
            return false;
        }

        if (typeof(checkEmpty) == 'undefined') {
            checkEmpty = false;
        }
        if (checkEmpty && grid.html().length > 0) {
            return false;
        }

        inProgress = true; // set to true (C.O.)
        elem.find(':input').attr('disabled', 'disabled');

        grid.css('opacity', 0.3);
        button.css('color', 'grey').html('Loading...');
        $.getJSON('./api.php?'+query, function(data){
            grid.empty().append('<tr class="b1"><td colspan="5">&nbsp;</td></tr>');

            if (data == null) return false;

            // first calculate line bars scale
            // we should get the biggest max value and divide lineBarLength on this value
            var maxMax = 0.1;
            $.each(data, function(i, item){
                if (typeof(item.time) == 'undefined')     return false;
                if (typeof(item.time.max) == 'undefined') return false;

                if (item.time.max > maxMax) {
                    maxMax = item.time.max;
                }
            });

            var scale = lineBarLength / maxMax;
            $.each(data, function(i, item){
                if (typeof(item.time) == 'undefined') return false;

                var dd = [];
                if (typeof(item.group)  != 'undefined') {
                    dd.push('<span class="group">'+item.group+'</span>')
                }
                if (typeof(item.timer)  != 'undefined') {
                    dd.push('<span class="timer">'+item.timer+'</span>')
                }
                if (typeof(item.info)   != 'undefined') {
                    dd.push('<span class="info">'+item.info+'</span>')
                }
                if (typeof(item.thread) != 'undefined') {
                    dd.push('<span class="thread">'+item.thread+'</span>')
                }
                var min = item.time.min;
                var avg = item.time.total / item.count;
                var max = item.time.max;
                
                grid.append(''+
                    '<tr class="b1">'+
                    '    <td class="dd r1">' + dd.join(' / ')+'</td>'+
                    (typeof(item.time.current) != 'undefined' ?
                    '    <td align="right" class="ll r4">'+item.time.current+'ms</td>' 
                    :
                    '    <td width="'+(lineBarLength+55)+'" class="r2">'+
                    '        <div style="background-color: #0000ff;width:'+(min > 0 ? min*scale : 1)+'px;height:6px"/>'+
                    '        <div style="background-color: #00ff00;width:'+(avg > 0 ? avg*scale : 1)+'px;height:6px"/>'+
                    '        <div style="background-color: #ff0000;width:'+(max > 0 ? max*scale : 1)+'px;height:6px"/>'+
                    '    </td>'+
                    '    <td class="r3">'+min+'ms<br>'+Math.round(avg)+'ms<br>'+max+'ms</td>'+
                    '    <td align="right" class="ll r4">'+
                    '        '+item.time.total+'ms<br/>'+
                    '        '+item.count+
                    '    </td>'+
                    '</tr>')+
                '');
            });
        }).complete(function(){
            grid.css('opacity', 1);
            button.css('color', 'black').html('Refresh');
            elem.find(':input').attr('disabled', null);

            inProgress = false;
        });
    }
</script>
