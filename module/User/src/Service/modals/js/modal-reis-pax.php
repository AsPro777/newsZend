<?php
return <<<S

    var sex = [
        {value: 1, text: "М"},
        {value: 0, text: "Ж"}
    ];

    var from = $from_array;
    var to   = $to_array;

    var from_options = $("<div />");
    $.each(from, function(i, opt){
        from_options.append($("<option />").val(opt.value).text(opt.text)
        );
    });
    from_options = from_options.html();

    var to_options = $("<div />");
    $.each(to, function(i, opt){
        to_options.append($("<option />").val(opt.value).text(opt.text)
        );
    });
    to_options = to_options.html();

    $("a.btn-pax-tpl").on("click", function(){
        $.ajax({
                url: '/account/reises',
                type: 'post',
                dataType: "json",
                data: {
                    action: 'pax-list-tpl'
                },
                success: function( data ) {
                    window.open(data.url);
                }, // success
            });//$.ajax
    });

    $("#all_checkboxes").unbind("click").on("click", function(){
        if($("#paxes-body :checkbox:checked").length>0)
            $("#paxes-body :checkbox:checked").prop("checked", false);
        else
            $("#paxes-body :checkbox").prop("checked", true);
        return false;
    });

    $("div.mass ul.dropdown-menu li a").on("click", function(){
        var dnt = $(this).closest("div.mass").find("span[name='data-node-title']");
        var short = $(this).text().substring(0, 25);
        //if(short.length < $(this).text().length) short += "...";
        dnt.text(short)
           .attr("title", $(this).text())
           .attr("data-id", $(this).attr("data-id"));
        if($(this).attr("data-mask"))
            dnt.attr("data-mask", $(this).attr("data-mask"));
    });

    $("div#mass_selector ul.dropdown-menu li a").on("click", function(){
        var node = $(this).attr("data-node");
        var dnt = $(this).closest("div#mass_selector").find("span[name='data-node-title']");
        dnt.text($(this).text()).attr("data-node", node);
        $("div.mass").removeClass("hidden").hide();
        $("div#"+node).show();
    });

    $("div#mass_OK :button").on("click", function(){
        var mass_places = $("div#mass_places :text").val().split(",");
        var ids = [];
        $.each(mass_places, function(i, val){
            val = val.split("-");
            if(parseInt(val[0])) ids.push(parseInt(val[0]));
            if(parseInt(val[1]))
                for(var i=parseInt(val[0])+1; i<=parseInt(val[1]); i++)
                    ids.push(i);
        });

        if(ids.length>0)
        {
            $("#paxes-body :checkbox").prop("checked", false);
            var selector = [];
            $.each(ids, function(i, id){
                selector.push(":checkbox[name='ch_"+id+"']");
            });
            $("#paxes-body").find(selector.join(",")).prop("checked", true);
        }

        $.each($("#paxes-body :checkbox:checked"), function(i, chbox){
            var tr = $(chbox).closest("tr");
            var node = $("div#mass_selector span[name='data-node-title']").attr("data-node");
            if(!node) return;

            var src = $("#modal-reis-pax div#"+node+" span[name='data-node-title']");
            var data_id = src.attr("data-id");
            var data_txt = $.trim(src.attr("title"));
            var data_mask = src.attr("data-mask");

            switch( node )
            {
                case "mass_grazhd":
                                    var span = tr.find("span[name='grazhd']");
                                    var td = span.closest("td");
                                    span.attr("data-base-val", data_id)
                                        .attr("data-base-val-txt", data_txt)
                                        .text(data_txt);
                                    td.find("span.editable-empty-text-label").hide();
                                    td.find("span.editable-value").show();
                                    break;
                case "mass_docs":
                                    var span = tr.find("span[name='doc_type']");
                                    var td = span.closest("td");
                                    span.attr("data-base-val", data_id)
                                        .attr("data-base-val-txt", data_txt)
                                        .text(data_txt);
                                    td.find("span.editable-empty-text-label").hide();
                                    td.find("span.editable-value").show();

                                    var span = tr.find("span[name='doc_num']");
                                    var td = span.closest("td");
                                    span.attr("data-mask", data_mask)
                                        .attr("placeholder", data_mask);
                                    if(span.hasClass("editable-empty"))
                                    {
                                        span.hide();
                                        span.prev("span.editable-empty-text-label").show();
                                    }
                                    break;
                case "mass_from":
                                    var span = tr.find("span[name='from']");
                                    var td = span.closest("td");
                                    span.attr("data-base-val", data_id)
                                        .attr("data-base-val-txt", data_txt)
                                        .text(data_txt);
                                    td.find("span.editable-empty-text-label").hide();
                                    td.find("span.editable-value").show();
                                    break;
                case "mass_to":
                                    var span = tr.find("span[name='to']");
                                    var td = span.closest("td");
                                    span.attr("data-base-val", data_id)
                                        .attr("data-base-val-txt", data_txt)
                                        .text(data_txt);
                                    td.find("span.editable-empty-text-label").hide();
                                    td.find("span.editable-value").show();
                                    break;
                default: return;
            }
        });

    });

    $("a.btn-pax-add").on("click", function(){
        var body = $("#paxes-body");
        var last = body.find("tr:last");
        var template = $({$template});
        var place_num = parseInt(last.attr("name"))+1;

        template.addClass("not-ticket-row").attr("name", place_num);
        template.find("td:first").text(place_num + " - б/м");
        template.find(":checkbox").attr("name", "ch_"+place_num);

        $.each(template.find('span'), function(j, span){
            $(span).attr('data-n', j+1);
        });

        body.append(template);
        $.each(template.find('span'), _init_my_editable);
    });

    $("a.btn-pax-import").on("click", function(){
        var w = modalTpl({
            id: 'modal-pax-list-import',
            title: "Импорт списка пассажиров",
            body: "Загрузка...",
            "submit-text": "Принять список",
            "close-text": "Отмена",
            "ajax-url": "/account/zakaz",
            "ajax-data": {
                action: 'modal-pax-list-import',
            },
        }).appendTo($("body"));
        w.modal("show")
        .on('hidden.bs.modal', function () {
            $('#modal-reis-pax').css({'overflow-y':'auto'});
          });
    });

    $("a.btn-pax-export").on("click", function(){
        if($("#modal-reis-pax").data("prepare_export")())
        {
            $.ajax({
                url: "/account/" + ajax_action,
                type: 'post',
                dataType: "json",
                data: {
                    action: 'upload-pax-list',
                    params: $("#modal-reis-pax").data("prepared_export")
                },
                success: function( data ) {
                        if(data.success==1) {
                             window.open(data.url);
                        }
                        else
                           alertMsg("Ошибка интерпретатора!");
                } // success
            });//$.ajax
        }
    });

//    $("a.btn-pax-import-export").on("click", function(){
//        var w = modalTpl({
//            id: 'modal-pax-import-export',
//            title: "Импорт / Экспорт списка пассажиров",
//            body: "Загрузка...",
//            "submit-text": "Принять список",
//            "close-text": "Отмена",
//            "ajax-url": "/account/" + ajax_action,
//            "ajax-data": {
//                action: 'modal-pax-import-export',
//            },
//            "submit-function": function(){
//                return importData($("#textarea-pax-import-export").val());
//            }
//        }).appendTo($("body"));
//        w.modal("show")
//        .on('hidden.bs.modal', function () {
//            $('#modal-reis-pax').css({'overflow-y':'auto'});
//          });
//    });

$.each($('#paxes-body tr.not-ticket-row'), function(i, obj){
     $.each($(obj).find('span'), function(j, span){
         $(span).attr('data-n', j+1);
     });
});

var _init_my_editable = function(i, obj)
{
    var opt = {};
    opt.emptytext = $(obj).attr('title');
    opt.backup = function(){return $(obj).text()};
    opt.onshow = function(){ control.trigger("focus"); };

    switch($(obj).attr("data-type"))
    {
        case 'radiogroup':
            var control = $('<span />').myRadiogroup({
                data:eval($(obj).attr('data-source-array')),
                checked: $(obj).attr('data-base-val')
            });
            opt.input = control;
            opt.backup = function(){
                    return $(obj).attr('data-base-val')
                };
            opt.init = function(){

                };
            opt.onshow = function(){
                    control.find("input.styled").trigger("focus");
                };
            opt.submit = function(input){
                var res = input.myRadiogroup("get");
                $(obj).text(res.txt);
                $(obj).text(res.txt)
                    .attr('data-base-val', res.id)
                    .attr('data-base-val-txt', res.txt);
            };
            break;

        case 'select':
            var div = $('<div class="input-group" />');
            var control = $('<select />').attr('data-init-id', $(obj).attr('data-base-val')).attr('data-init-val', $(obj).attr('data-base-val-txt'));
            opt.input = div.append(control);
            opt.init = function(){
                switch( $(obj).attr('data-source-array') )
                {
                    case 'from': control.html(from_options);
                                 break;
                    case 'to':   control.html(to_options);
                                 break;
                }
                control.typeheadOnSelect({remote:false});
                div.find('input.form-control').addClass('input-sm');
                div.find('button.btn').addClass('btn-sm');
            };
            opt.onshow = function(){
                control.closest("div").find("input").trigger("focus");
            };
            opt.submit = function(input){
                if(control.find(':selected').length<1) return;

                $(obj).text(control.find(':selected').text())
                    .attr('data-base-val', control.val())
                    .attr('data-base-val-txt', control.find(':selected').text());
            };
            break;

        case 'typehead':
            var div = $('<div class="input-group" />');
            var control = $('<select />').attr('data-init-id', $(obj).attr('data-base-val')).attr('data-init-val', $(obj).attr('data-base-val-txt'));
            opt.input = div.append(control);
            opt.init = function(){
                control.typeheadOnSelect(
                    {
                        remote:true,
                        url:$(obj).attr('data-source-url'),
                        onSelectFn: function(index){
                            if(index==3)
                                control.closest("form").find("button.editable-submit").focus();
                        }
                    }
                );
                div.find('input.form-control').addClass('input-sm');
                div.find('button.btn').addClass('btn-sm');
            };
            opt.onshow = function(){
                    control.closest("div").find("input").trigger("focus");
                };
            opt.submit = function(input){
                if(control.find(':selected').length<1) return;

                $(obj).text(control.find(':selected').text())
                    .attr('data-base-val', control.val())
                    .attr('data-base-val-txt', control.find(':selected').text());

                var data = JSON.parse(control.find(':selected').attr('data-base'));
                $(obj).closest("tr").find('span[name=doc_num]')
                    .attr('data-mask', data.mask)
                    .attr('placeholder', data.mask)
                    .attr('data-base-val', '')
                    .text('')
                    .myEditable("clear");
            };
            break;

        case 'mask': // masked text
            var control = $('<input class="form-control input-sm">');
            opt.input = control;
            opt.init = function(){
                control.mask( $(obj).attr("data-mask"), {placeholder: $(obj).attr("placeholder")} );
                control.val($(obj).attr('data-base-val'));
            };
            opt.submit = function(input){
                $(obj).text(input.val()).attr('data-base-val', input.val());
            };
            break;

        default:    // text
            var control = $('<input class="form-control input-sm">');
            opt.input = control;
            opt.init = function(){
                control.val($(obj).attr('data-base-val')).attr("placeholder", $(obj).attr("title"));
            };
            opt.submit = function(input){
                $(obj).text(input.val()).attr('data-base-val', input.val());
            };
    }

    opt.shownext = function(span)
    {
        var n = 1 + parseInt($(span).attr('data-n'));
        var next = $(span).closest('tr').find('span[data-n='+n+']');

        if(next.length==0)
            next = $(span).closest('tr').next().find('span[data-n=1]');

        $(next).myEditable('show');
    }
    $(obj).myEditable(opt);
};

$.each($('#paxes-body tr.not-ticket-row span'), _init_my_editable);

var isInvalidDate = function(date)
{
    var saved = date;
    date = date.split('.');
    if(date.length<3) return true;

    var d = date[0], m = date[1], y = date[2];
    var dt = new Date();
    if( (y<1900) || (y>dt.getUTCFullYear()) ) return true;

    dt.setUTCFullYear(y, m-1, d);

    var result = ((y == dt.getUTCFullYear()) && ((m-1) == dt.getUTCMonth()) && (d == dt.getUTCDate()));
    return !result;
}

var isInvalidMasker = function(item){

    var value = item.doc_num;
    var mask = item.doc_num_mask;
    var hasMask = mask.split("_").join("") != "";

    if(value=="") return true;
    if( !hasMask ) return false;
    if(value.length!=mask.length) return true;

    return false;
}

var validate_row = function(row)
{
    var test = row.doc_num + row.doc_type_txt + row.dr + row.f + row.i + row.o + row.grazhd_txt + row.sex_txt;
    if(test=='') return true;

    var result = true;
    if(row.f == '') return "f";
    if( /[a-zA-Z]+/.test(row.f) && /[а-яА-ЯЁё]+/.test(row.f)) return "f"; //В ФИО не должны использоваться одновременно символы разных алфавитов!";
    if( /[^a-zA-Zа-яА-ЯЁё -]+/.test(row.f) ) return "f";    //В имени должны использоваться только буквы, пробелы и тире!";

    if(row.i == '') return "i";
    if( /[a-zA-Z]+/.test(row.i) && /[а-яА-ЯЁё]+/.test(row.i)) return "i";
    if( /[^a-zA-Zа-яА-ЯЁё -]+/.test(row.i) ) return "i";

    //if(row.o == '') return "o";
    if( /[a-zA-Z]+/.test(row.o) && /[а-яА-ЯЁё]+/.test(row.o)) return "o";
    if( /[^a-zA-Zа-яА-ЯЁё -]+/.test(row.o) ) return "o";

    if(isInvalidDate(row.dr)) return "dr";
    if(row.sex_txt == '') return "sex";
    if(row.grazhd_txt == '') return "grazhd";
    if(row.doc_type_txt == '') return "doc_type";
    if(isInvalidMasker(row)) return "doc_num";

    if(row.from == '') return "from";
    if(row.to == '') return "to";

    var from_idx = row.from.split(":");
    from_idx = from_idx[5] ? from_idx[5] : "";
    if(from_idx == '') return "from";

    var to_idx = row.to.split(":");
    to_idx = to_idx[5] ? to_idx[5] : "";
    if(to_idx == '') return "to";

    if(to_idx <= from_idx) return "to";

    return true;
}

$("#modal-reis-pax").data("validate", function()
{
    var params = [], c = $("#modal-reis-pax"), validated = true;
    c.data("params", params);

    $.each($('#paxes-body tr.not-ticket-row'), function(i, obj)
    {
        var row = {};
        $.each($(obj).find('span.editable-value'), function(j, span){

            var name = $(span).attr('name');

            row[name] = $(span).attr('data-base-val');

            if($(span).attr('data-base-val-txt') != undefined)
                row[$(span).attr('name')+'_txt'] = $(span).attr('data-base-val-txt');

            if($(span).attr('data-mask') != undefined)
                row[$(span).attr('name')+'_mask'] = $(span).attr('data-mask');
        });

        validated = validate_row(row); // true or field name
        if(validated !== true)
        {
            validated = {
                place: $(obj).closest("tr").attr("name"),
                fld: $(obj).find("span[name='"+validated+"']").attr('title')
            };
            return false;
        }
        if( (row.f != '') && (row.doc_num != '') )
            params[$(obj).closest("tr").attr("name")] = row;
    });
    if( validated !== true )
    {
        return alertMsg("Не заполнено или неправильно заполнено поле «"+validated.fld+"» (место №"+validated.place+")");
    }

    c.data("params", params);
    return true;
});

$("#modal-reis-pax").data("prepare_export", function()
{
    var params = [], c = $("#modal-reis-pax"), validated = true;
    c.data("prepared_export", params);

    $.each($('#paxes-body tr'), function(i, obj)
    {
        var row = {};
        $.each($(obj).find('span'), function(j, span){

            var name = $(span).attr('name');
            if(name==undefined) return;

            row[name] = $(span).attr('data-base-val');

            if($(span).attr('data-base-val-txt') != undefined)
                row[$(span).attr('name')+'_txt'] = $(span).attr('data-base-val-txt');

            if($(span).attr('data-mask') != undefined)
                row[$(span).attr('name')+'_mask'] = $(span).attr('data-mask');
        });

        if( (row.f != '') && (row.doc_num != '') )
            params[$(obj).closest("tr").attr("name")] = row;
    });

    c.data("prepared_export", params);
    return true;
});


var importData = function(data)
{
    data = data.split('\\n');
    $.each(data, function(i, d){
        if(d == undefined)
        {
            delete(data[i]);
            return;
        }

        if(d.indexOf("##")===0) return;
        if(d.indexOf("#") ===0)
        {
            delete(data[i]);
            return;
        }

    });

    var result = [];
    data = data.join('\\n').split('##');
    $.each(data, function(place, rec){

        if( rec == undefined ) return;
        rec = rec.replace(/^\s+/,"");
        if( rec == "" ) return;

        var d = rec.split('\\n');
        if(d.length<8)
        {
            delete(data[place]);
            return;
        }
        d.length = 8;
        result.push(d);
    });

    setTimeout(function(){
        var id = $("#paxes-body").attr("data-id");
        $("#modal-reis-pax").modal("hide");

        var w = modalTpl({
            id: 'modal-reis-pax',
            class: 'modal-full',
            title: "Список пассажиров заказного рейса",
            body: "Загрузка...",
            "ajax-url": "/account/" + ajax_action,
            "ajax-data": {
                action: 'modal-reis-pax-import-export',
                id: id,
                data: result
            },
            "submit-function": function(){
                if($("#modal-reis-pax").data("validate")())
                {
                    $.ajax({
                        url: "/account/" + ajax_action,
                        type: 'post',
                        dataType: "json",
                        data: {
                            action: 'pax-save',
                            id: id,
                            params: $("#modal-reis-pax").data("params")
                        },
                        success: function( data ) {
                                if(data.success==1) {
                                    successMsg("Успешно!", function(){
                                        $("#modal-reis-pax").modal("hide");
                                        $('#calendar').fullCalendar( 'refetchEvents' );
                                    });
                                 }
                                else
                                   alertMsg("Конфигурация не обновлена! Ошибка сохранения!");
                        } // success
                    });//$.ajax
                }
                return false;
            }
        }).appendTo($("body"));
        w.modal("show");
    }, 150);

    return true;
}


 // Импорт
var _import = function(html_rows)
{
    var old_count_rows = $("#paxes-body").find("tr").length;
    $("#paxes-body").empty();
    $("#paxes-body").html(html_rows);

    $.each($("#paxes-body tr"), function(i, row){
        $.each($(row).find('span'), function(j, span){
            $(span).attr('data-n', j+1);
        });
        $.each($(row).find('span'), _init_my_editable);
    });

    var new_count_rows = $("#paxes-body").find("tr").length;
    var delta = old_count_rows-new_count_rows;
    if(delta>0)
        for(var i=0; i<delta; i++)
            $("a.btn-pax-add").trigger("click");
}

$("#paxes-body").data("import", _import);

S;
