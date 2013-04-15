    function start(){ 
                $.ajaxSetup({cache: false}); // turn off ajax cache

                $('#tab_menu a').click(function(){
                    if (inProgress) {
                        return false;
                    }

                    var filter = $('.profiler_block:visible').find('input[name=filter]').val();
                    filter     = typeof(filter) != 'undefined' && filter.length > 0 ? filter : '*/*/*/*';

                    $('.profiler_block :input').unbind();
                    $('.profiler_block').hide();
                    var selector = $(this).attr('href');
                    $(selector).show();
                    $(selector).find('input[name=filter]').val(filter);
                    $(selector+' :input').not('input[name="filter"]').change(function(e){
                        renderDataGrid(selector);
                    });
                    $(selector+' .refresh_button').click(function(){
                        renderDataGrid(selector);
                    });
                    //renderDataGrid(selector, true);
                    renderDataGrid(selector, false);

                    // @TODO   add  .tabselected  to clicked tab
                    $('#tab_menu a').removeClass('tabselected');
                    $(this).addClass('tabselected');
                    
                    return false;
                });

                var anchor = window.location.hash;
                if (anchor.length > 0) {
                    $('#tab_menu a[href="'+anchor+'"]').click();
                } else {
                    $('#tab_menu a[href="#aggregate"]').click();
                }
    }

    function round(value)
    {
	return Math.round(value*100)/100;
    }

    function formatNumber(number)
    {
        var label = 'ms';
        if (number > 1000) {
            label = 'sec';
            number = number/1000;
        } else if (number > 10) {
            number = Math.floor(number);
        }
        return round(number) + label;
    }

    function renderDataGrid(selector, checkEmpty)
    {
        var elem = $(selector);
        var grid = elem.find('table.profiler_grid');
        var button = elem.find('.refresh_button');
        var query  = "/" + elem.attr('id') + "/?" + elem.find(':input').serialize();

        if (grid.length == 0) {
            return false;
        }

        if (typeof(checkEmpty) == 'undefined') {
            checkEmpty = false;
        }
        if (checkEmpty && grid.html().length > 0) {
            return false;
        }

        inProgress = true; // set to true (C.O.)  (^_^)
        elem.find(':input').attr('disabled', 'disabled');

        grid.css('opacity', 0.3);
        button.css('color', 'grey').html('Loading...');
        $.getJSON(query, function(data){
            grid.empty().append('<tr class="b1"><td colspan="5">&nbsp;</td></tr>');

            if (data == null) return false;
            // first calculate line bars scale
            // we should get the biggest max value and divide lineBarLength on this value
            var maxMax = 0.000001;
            $.each(data, function(i, item){
                if (typeof(item.Time) == 'undefined')     return false;
                if (typeof(item.Time.max) == 'undefined') return false;

                if (item.Time.max > maxMax) {
                    maxMax = item.Time.max;
                }
            });

            var scale = lineBarLength / maxMax;
            $.each(data, function(i, item){
                //FIXME  проблема с обработкой массива данных, проверить формат ответа от сервера
                if (typeof(item.Time) == 'undefined') return false;

                var dd = [];
                if (typeof(item.Src)  != 'undefined') {
                    dd.push('<span class="f18">'+item.Src+'</span>')
                }
                if (typeof(item.Timer)  != 'undefined') {
                    dd.push('<span class="f25">'+item.Timer+'</span>')
                }
                if (typeof(item.Info)   != 'undefined') {
                    dd.push('<span class="f15">'+item.Info+'</span>')
                }
                if (typeof(item.Thrd) != 'undefined') {
                    dd.push('<span class="f12">'+item.Thrd+'</span>')
                }
                var min = item.Time.min;
                var avg = item.Time.total / item.count;
                var max = item.Time.max;
                


                grid.append(''+
                    '<tr class="b1">'+
                    '    <td class="r1">' + dd.join(' / ')+'</td>'+
                (typeof(item.Time) != 'undefined' ?
                    '    <td class="r2"></td><td class="r3 f12">&nbsp;<br>&nbsp;<br>&nbsp;</td><td align="right" class="r4 f15">'+formatNumber(item.Time)+'</td>' 
                :
                    '    <td class="r2">'+
                    '        <div class="bln" style="width:'+(min > 0 ? round(min*scale) : 1)+'px;"/>'+
                    '        <div class="gln" style="width:'+(avg > 0 ? round(avg*scale) : 1)+'px;"/>'+
                    '        <div class="rln" style="width:'+(max > 0 ? round(max*scale) : 1)+'px;"/>'+
                    '    </td>'+
                    '    <td class="r3 f12">'+formatNumber(min)+'<br>'+formatNumber(avg)+'<br>'+formatNumber(max)+'</td>'+
                    '    <td align="right" class="r4 f15">'+
                    '        '+formatNumber(item.Time.total)+'<br/>'+
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

