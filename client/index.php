<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <script type="text/javascript" src="jquery-1.7.2.min.js"></script> 
        <style type="text/css">

            .b1 td {border-bottom:1px solid #808080;}

            .profiler_block {display: none;padding-bottom:50px}
            .refresh_button {margin-left: 10px;}

            #tab_menu  a {text-decoration: none; color: #000000;font-size:35px; padding-top:35px;}
            
            .tabselected {text-shadow: 1px 2px 6px #000000;color:#ffffff!important;}

            .r1 {width:750px; padding-left:5px;}
            .r2 {width:270px}
            .r3 {width:50px;padding-left:15px;}
            .r4 {width:60px; text-align: right; padding-right:5px}

            .f10 {font-size:10px;}
            .f12 {font-size:12px;}
            .f15 {font-size:15px;}
            .f18 {font-size:18px;}
            .f20 {font-size:20px;}
            .f25 {font-size:25px;}
            .f30 {font-size:30px;}
            .f35 {font-size:35px;}
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
                    
                    //TODO   add  .tabselected  to clicked tab
                    $('#tab_menu a').removeClass('tabselected');
                    $(this).addClass('tabselected');
                    
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
                    <img src="prflr.gif" width="250" style="padding:0 50px 0 5px;"/>
                </td>
                <td>
                    <div id="tab_menu">
                        <a href="#last" class="tabselected">Raw Timers</a>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                        <a href="#agg">Statistic</a>
                        <!-- | <a href="#slow">SlowTop</a> | <a href="#groups">Groups</a> | <a href="#time">TimeGraph</a>-->
                        <!--    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <a href="#settings">Settings</a>
                        -->
                    </div>
                </td>
            </table>
            <div style="padding:15px;">

                <!-- RAW TIMERS -->
                <div id="last" class="profiler_block">
                    <!--<h1>Raw Timers</h1>-->

                    <form action="" method="GET" onsubmit="$(this).find('.refresh_button').click();return false">
                        <input type="hidden" name="r" value="stat_last" />
                        <table border="0" cellpadding="0" cellspacing="0"> 
                            <tr>
                                <td class='r0 f15'></td>
                                <td class="r1 f30">Group / Timer / Info / Thread</td>
                                <td class="r2 f30"></td>
                                <td class="r3"></td>
                                <td class="r4 f18">
                                    Time<br/>&nbsp;
                                    
                                </td>
                            </tr>
                            <tr>
                                <td class='r0'>#>&nbsp;</td>
                                <td>
                                    <input name="filter" style="width:700px; height:35px; font-size:14px;" value="*/*/*/*" />
                                </td>
                                <td align="left"></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class='r0'></td>
                                <td></td>
                                <td><button class="refresh_button">Refresh</button></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class='b0'></td>
                                <td colspan="4">
                                    <table class="m profiler_grid" border="0" cellpadding="0" cellspacing="0"></table>
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
                        <table border="0" cellpadding="0" cellspacing="0"> 
                            <tr>
                                <td class='r0 f15'></td>
                                <td class="r1 f30">Group / Timer / Info / Thread</td>
                                <td class="r2 f30">Statistic</td>
                                <td class="r3"></td>
                                <td class="r4 f18">
                                    Total<br/>
                                    Count
                                </td>
                            </tr>
                            <tr>
                                <td class='r0'>#>&nbsp;</td>
                                <td>
                                    <input name="filter" style="width:700px; height:35px; font-size:14px;" value="*/*/*/*" />
                                </td>
                                <td align="left">
                                    Sort By: 
                                    <select style="width:160px;" name="sortby">
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
                                <td class='r0'></td>
                                <td align="right">
                                    <div style="padding-right:75px;">
                                        Group By: 
                                        <select style="width:200px;" name="groupby">
                                            <option value="group,timer">Group + Timer</option>
                                            <option value="group">Group</option>
                                            <option value="timer">Timer</option>
                                        </select>
                                    </div>
                                </td>
                                <td><button class="refresh_button">Refresh</button></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class='b0'></td>
                                <td colspan="4">
                                    <table class="m profiler_grid" border="0" cellpadding="0" cellspacing="0"></table>
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
                    dd.push('<span class="f18">'+item.group+'</span>')
                }
                if (typeof(item.timer)  != 'undefined') {
                    dd.push('<span class="f20">'+item.timer+'</span>')
                }
                if (typeof(item.info)   != 'undefined') {
                    dd.push('<span class="f12">'+item.info+'</span>')
                }
                if (typeof(item.thread) != 'undefined') {
                    dd.push('<span class="f10">'+item.thread+'</span>')
                }
                var min = item.time.min;
                var avg = item.time.total / item.count;
                var max = item.time.max;
                
                grid.append(''+
                    '<tr class="b1">'+
                    '    <td class="r1">' + dd.join(' / ')+'</td>'+
                    (typeof(item.time.current) != 'undefined' ?
                    '    <td class="r2"></td><td class="r3"><div style="height:18;width:10px"&nbsp;</div></td><td align="right" class="r4">'+item.time.current+'ms</td>' 
                :
                    '    <td class="r2">'+
                    '        <div style="background-color: #0000ff;width:'+(min > 0 ? min*scale : 1)+'px;height:6px"/>'+
                    '        <div style="background-color: #00ff00;width:'+(avg > 0 ? avg*scale : 1)+'px;height:6px"/>'+
                    '        <div style="background-color: #ff0000;width:'+(max > 0 ? max*scale : 1)+'px;height:6px"/>'+
                    '    </td>'+
                    '    <td class="r3 f12">'+min+'ms<br>'+Math.round(avg)+'ms<br>'+max+'ms</td>'+
                    '    <td align="right" class="r4 f15">'+
                    '        '+item.time.total+'&nbsp;ms<br/>'+
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
