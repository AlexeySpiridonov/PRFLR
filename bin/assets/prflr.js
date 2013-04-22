function start(){ 
	$.ajaxSetup({cache: false}); // turn off ajax cache
	
	// Menu Item Handlers
	$('#tab_menu a').click(function(){
	    if (inProgress) {
	        return false;
	    }

	    $('.profiler_block :input').unbind();
	    $('.profiler_block').hide();
	    var selector = $(this).attr('href');
	    $(selector).show();

		var filter = $('.profiler_block:visible').find('input[name=filter]').val();
	    filter     = typeof(filter) != 'undefined' && filter.length > 0 ? filter : '*/*/*/*';

	    $(selector).find('input[name=filter]').val(filter);
	    $(selector+' :input').not('input[name="filter"]').change(function(e){
	        renderDataGrid(selector);
	    });
	    $(selector+' .refresh_button').click(function(){
	        renderDataGrid(selector);
	    });
	
	    $('#tab_menu a').removeClass('tabselected');
	    $(this).addClass('tabselected');
		
		renderDataGrid(selector, false);
	    
	    return false;
	});

	// URL anchor handlers
	var hash = window.location.hash.replace('#', '');
	if (hash.length > 0) {
		hash = hash.split("|");

		if (typeof(hash[1]) == 'undefined') {
			hash[1] = 'aggregate';
		}

		$('input[name=filter]').val(hash[0]);
	    $('#tab_menu a[href="#'+hash[1]+'"]').click();
	} else {
	    $('#tab_menu a[href="#aggregate"]').click();
	}

	$('.prflrItemHeader').click(function(){
		assignFilterChunkValue($(this).attr('item'), '*');
		renderDataGrid(getCurrentMenuSelector());
	});

	$('.resetFilter').click(function(){
		assignFilterChunkValue('*', '*');
		renderDataGrid(getCurrentMenuSelector());
	});
}

// Row items click handlers
function initProfilerItemsClickHandler()
{
	$(".prfrlItem").click(function(event){
		var item  = $(this).attr("item");
		var value = $(this).text();

		assignFilterChunkValue(item, value);

		var selector = getCurrentMenuSelector();

		event.stopPropagation();

		renderDataGrid(selector);
	});
}

function assignFilterChunkValue(chunk, value)
{
	var filter = $('input[name=filter]:visible');

	if (chunk == '*') {
		filter.val(value+'/'+value+'/'+value+'/'+value);
		return true;
	}

	var chunkToSlot = {
		"src":   0,
		"timer": 1,
		"info":  2,
		"thrd":  3
	};

	if (typeof(chunkToSlot[chunk]) == 'undefined') {
		return false;
	}

	var chunks = filter.val().split('/');

	chunks[chunkToSlot[chunk]] = value;

	filter.val(chunks.join('/'));
}

function getCurrentMenuSelector()
{
	return $(".tabselected").attr('href');
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

	var filter = $('input[name=filter]:visible').val();
	window.location.hash = "#"+filter+"|"+getCurrentMenuSelector().replace("#", '');

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
            if (typeof(item.Max) == 'undefined') return false;

            if (item.Max > maxMax) {
                maxMax = item.Max;
            }
        });

        var scale = lineBarLength / maxMax;
        $.each(data, function(i, item){
            var dd = [];
            if (typeof(item.Src) != 'undefined' && item.Src != '') {
                dd.push('<span class="f18 prfrlItem" item="src">'+item.Src+'</span>')
            }
            if (typeof(item.Timer) != 'undefined' && item.Timer != '') {
                dd.push('<span class="f25 prfrlItem" item="timer">'+item.Timer+'</span>')
            }
            if (typeof(item.Info) != 'undefined' && item.Info != '') {
                dd.push('<span class="f15 prfrlItem" item="info">'+item.Info+'</span>')
            }
            if (typeof(item.Thrd)  != 'undefined' && item.Thrd != '') {
                dd.push('<span class="f12 prfrlItem" item="thrd">'+item.Thrd+'</span>')
            }

			var min = item.Min;
            var avg = item.Total / item.Count;
            var max = item.Max;

            grid.append(''+
                '<tr class="b1 prflrRow">'+
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
                '        '+formatNumber(item.Total)+'<br/>'+
                '        '+item.Count+
                '    </td>')+
                '</tr>'+
                '');
        });
    }).complete(function(){
        grid.css('opacity', 1);
        button.css('color', 'black').html('Refresh');
        elem.find(':input').attr('disabled', null);

		initProfilerItemsClickHandler();

        inProgress = false;
    });
}

