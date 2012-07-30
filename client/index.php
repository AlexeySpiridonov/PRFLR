<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <script type="text/javascript" src="jquery-1.7.2.min.js"></script> 
        <style type="text/css">
            table {font-size:14px;}
            table.m td {margin-top:4px;padding-right:16px;}
            tr.f td {font-size:30px;}
            table tr td.ff {font-size:15px;}
            table tr td.dd {font-size:18px;}
            .timer {font-size:18px;}
            .group {font-size:15px;}
            .info {font-size:12px;}
            .thread {font-size:10px;}
            .b0 {border:none!important;padding-right:0px!important;}
            .b1 td {border-bottom:1px solid #808080;}
            table.b td {margin-top:0px;border:0px solid #808080;font-size:10px;}
            
            .profiler_block {display: none;padding-bottom:50px}
            .refresh_button {margin-right: 28px}
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
                    return false;
                });

                var anchor = window.location.hash;
                if (anchor.length > 0) {
                    $('#tab_menu a[href="'+anchor+'"]').click();
                } else {
                    $('#tab_menu a[href="#agg"]').click();
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
                    <h1 id="tab_menu">
                        <a href="#raw">Raw Timers</a> | <a href="#agg">Statistic</a> | <a href="#slow">SlowTop</a> | <a href="#groups">Groups</a> | <a href="#time">TimeGraph</a> | <a href="#settings">Settings</a>
                    </h1>
                    <img src="revert.gif" border=0/>
                </td>
            </table>
            <div style="padding:15px;">

                <!-- RAW TIMERS -->
                <div id="raw" class="profiler_block">
                    <h1>Raw Timers</h1>
                    
                    <table class="m" border="0" cellpadding="0" cellspacing="0" width="100%"> 
                        <tr class="f bo">
                            <td class='b0'></td>
                            <td>Group / Timer / Info / Thread</td>
                            <td></td>
                            <td>Statistic</td>
                            <td class="ff" align="right">
                                Total<br />
                                Count
                            </td>
                        </tr>
                        <tr class="b0">
                            <td class='b0'>#></td>
                            <td>
                                <input name="filter" style="width:95%; height:35px; font-size:14px;" value="*/*/*/*" />
                                [?]
                            </td>
                            <td><input type="hidden" name="r" value="stat_raw" /></td>
                            <td colspan="2" align="left">
                                Sort By: 
                                <select style="width:200px;" name="sortby">
                                    <option value="max">Max Time (red)</option>
                                    <option value="avg">Avg Time (green)</option>
                                    <option value="max">Min Time (blue)</option>
                                    <option value="total">Total Time</option>
                                    <option value="count">Count</option>
                                    <option value="dispersion">Dispersion</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div style="float:right">
                                    <button class="refresh_button">Refresh</button>
                                </div>
                            </td>
                        </tr>
                        <tr class="b1"><td class='b0'></td><td colspan="5">&nbsp;</td></tr>
                        <tr>
                            <td class='b0'></td>
                            <td colspan="20">
                                <table class="m profiler_grid" border="0" cellpadding="0" cellspacing="0" width="100%"></table>
                            </td>
                        </tr>
                    </table>
                </div> <!-- END OF raw -->

                <!-- STATISTIC -->
                <div id="agg" class="profiler_block">

                    <h1>Statistic</h1>

                    <table class="m" border="0" cellpadding="0" cellspacing="0" width="100%"> 
                        <tr class="f bo">
                            <td class='b0'></td>
                            <td>Group / Timer / Info / Thread</td>
                            <td></td>
                            <td>Statistic</td>
                            <td class="ff" align="right">
                                Total<br/>
                                Count
                            </td>
                        </tr>
                        <tr class="b0">
                            <td class='b0'>#></td>
                            <td>
                                <input name="filter" style="width:95%; height:35px; font-size:14px;" value="*/pimer/*/*" />
                                [?]
                            </td>
                            <td><input type="hidden" name="r" value="stat_aggregate" /></td>
                            <td colspan="2" align="left">
                                Sort By: 
                                <select style="width:200px;" name="sortby">
                                    <option value="max">Max Time (red)</option>
                                    <option value="avg">Avg Time (green)</option>
                                    <option value="max">Min Time (blue)</option>
                                    <option value="total">Total Time</option>
                                    <option value="count">Count</option>
                                    <option value="dispersion">Dispersion</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <div style="float:left">
                                    Group By: 
                                    <select style="width:200px;" name="groupby">
                                        <option value="timer">Timer</option>
                                        <option value="group,timer">Group + Timer</option>
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
                        <tr class="b1"><td class='b0'></td><td colspan="5">&nbsp;</td></tr>
                        <tr>
                            <td class='b0'></td>
                            <td colspan="20">
                                <table class="m profiler_grid" border="0" cellpadding="0" cellspacing="0" width="100%"></table>
                            </td>
                        </tr>
                    </table>
                </div> <!-- END OF agg -->

                <!-- SLOW TOP -->
                <div id="slow" class="profiler_block">
                    <h1>Slow Top</h1>
                    <table class="m" border="0" cellpadding="0" cellspacing="0" width="100%"> 
                        <tr class="f bo">
                            <td class='b0'></td>
                            <td>Group / Timer / Info / Thread</td>
                            <td></td>
                            <td>Statistic</td>
                            <td class="ff" align="right">
                                Total<br />
                                Count
                            </td>
                        </tr>
                        <tr class="b0">
                            <td class='b0'>#></td>
                            <td>
                                <input name="filter" style="width:95%; height:35px; font-size:14px;" value="group.1/pimer/*/*" />
                                [?]
                            </td>
                            <td><input type="hidden" name="r" value="stat_slow" /></td>
                            <td colspan="2" align="left">
                                Sort By: 
                                <select style="width:200px;" name="sortby">
                                    <option value="max">Max Time (red)</option>
                                    <option value="avg">Avg Time (green)</option>
                                    <option value="max">Min Time (blue)</option>
                                    <option value="total">Total Time</option>
                                    <option value="count">Count</option>
                                    <option value="dispersion">Dispersion</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div style="float:right">
                                    <button class="refresh_button">Refresh</button>
                                </div>
                            </td>
                        </tr>
                        <tr class="b1"><td class='b0'></td><td colspan="5">&nbsp;</td></tr>
                        <tr>
                            <td class='b0'></td>
                            <td colspan="20">
                                <table class="m profiler_grid" border="0" cellpadding="0" cellspacing="0" width="100%"></table>
                            </td>
                        </tr>
                    </table>
                </div> <!-- END OF slow -->

                <!-- GROUPS -->
                <div id="groups" class="profiler_block">
                    <h1>Groups</h1>
                    <table class="m profiler_table" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr class="f bo">
                            <td class='b0'></td>
                            <td>Group / Timer / Info / Thread</td>
                            <td></td>
                            <td>Statistic</td>
                            <td class="ff" align="right">
                                Total<br />
                                Count
                            </td>
                        </tr>
                        <tr class="b0">
                            <td class='b0'>#></td>
                            <td>
                                <input name="filter" style="width:95%; height:35px; font-size:14px;" value="group.1/pimer/*/*" />
                                [?]
                            </td>
                            <td><input type="hidden" name="r" value="stat_groups" /></td>
                            <td colspan="2" align="left">
                                Sort By: 
                                <select style="width:200px;" name="sortby">
                                    <option value="max">Max Time (red)</option>
                                    <option value="avg">Avg Time (green)</option>
                                    <option value="max">Min Time (blue)</option>
                                    <option value="total">Total Time</option>
                                    <option value="count">Count</option>
                                    <option value="dispersion">Dispersion</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div style="float:right">
                                    <button class="refresh_button">Refresh</button>
                                </div>
                            </td>
                        </tr>
                        <tr class="b1"><td class='b0'></td><td colspan="5">&nbsp;</td></tr>
                        <tr>
                            <td class='b0'></td>
                            <td colspan="20">
                                <table class="m profiler_grid" border="0" cellpadding="0" cellspacing="0" width="100%"></table>
                            </td>
                        </tr>
                    </table>
                </div> <!-- END OF groups -->

                <!-- TIME GRAPH -->
                <div id="time" class="profiler_block">
                    <h1>Time Graph</h1>
                    <table class="m" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr class="f bo">
                            <td class='b0'></td>
                            <td>Group / Timer / Info / Thread</td>
                            <td></td>
                            <td>Statistic</td>
                            <td class="ff" align="right">
                                Total<br />
                                Count
                            </td>
                        </tr>
                        <tr class="b0">
                            <td class='b0'>#></td>
                            <td>
                                <input name="filter" style="width:95%; height:35px; font-size:14px;" value="" />
                                [?]
                            </td>
                            <td><input type="hidden" name="r" value="stat_graph" /></td>
                            <td colspan="2" align="left">
                                Sort By: 
                                <select style="width:200px;" name="sortby">
                                    <option value="max">Max Time (red)</option>
                                    <option value="avg">Avg Time (green)</option>
                                    <option value="max">Min Time (blue)</option>
                                    <option value="total">Total Time</option>
                                    <option value="count">Count</option>
                                    <option value="dispersion">Dispersion</option>
                                </select>
                            </td>
                        </tr>
                        <tr class="b1"><td class='b0'></td><td colspan="5">&nbsp;</td></tr>
                        <tr>
                            <td class='b0'></td>
                            <td colspan="20">
                                <table class="m profiler_grid" border="0" cellpadding="0" cellspacing="0" width="100%"></table>
                            </td>
                        </tr>
                    </table>
                </div> <!-- END OF time -->

                <div id="settings" class="profiler_block">
                    <h1>Settings</h1>
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
            grid.empty();

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
                    '    <td class="dd">'+
                    dd.join(' / ')+
                    '    </td>'+
                    '    <td></td>'+
                    '    <td width="'+(lineBarLength+55)+'">'+
                    '        <table width="100%" class="b" cellpadding="0" cellspacing="0">'+
                    '            <tr>'+
                    '                <td><div style="background-color: #0000ff;width:'+(min > 0 ? min*scale : 1)+'px;height:8px"></div></td>'+
                    '                <td>'+min+'ms</td>'+
                    '            </tr>'+
                    '            <tr>' +
                    '                <td><div style="background-color: #00ff00;width:'+(avg > 0 ? avg*scale : 1)+'px;height:8px"></div></td>'+
                    '                <td>'+Math.round(avg)+'ms</td>'+
                    '            </tr>'+
                    '            <tr>' +
                    '                <td width="'+lineBarLength+'"><div style="background-color: #ff0000;width:'+(max > 0 ? max*scale : 1)+'px;height:8px"></div></td>'+
                    '                <td>'+max+'ms</td>'+
                    '            </tr>'+
                    '        </table>'+
                    '    </td>'+
                    '    <td align="right" width="65">'+
                    '        '+item.time.total+'ms<br/>'+
                    '        '+item.count+
                    '    </td>'+
                    '</tr>'+
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