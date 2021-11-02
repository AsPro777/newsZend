<?php
return <<<S

$("#modal-search div.input-group").css({"width":"100%", "margin-top": "5px"});

var search_date = getCookie("search_date");
if(search_date)
    $("#modal-search input[name='dt-start']").val(search_date);

var search_path = ''+getCookie("search_path");
if(search_path)
    search_path = search_path.split("-");
if(search_path.length>0)
    $("#modal-search select[name='search-from']").attr('data-init-id', search_path[0]);    
//$("#modal-search select[name='search-from']").attr('data-init-val', getCookie("search_path_from_val"));

if(search_path.length>1)
    $("#modal-search select[name='search-to']").attr('data-init-id', search_path[1]);
//$("#modal-search select[name='search-to']").attr('data-init-val', getCookie("search_path_to_val"));

$("#modal-search input[name='dt-start']").mask("00.00.0000", {placeholder: "дд.мм.гггг", selectOnFocus: false});
$("#modal-search select[name='search-from']").typeheadOnSelect({remote:true, url:'/ajax/citys-list'});        
$("#modal-search select[name='search-to']").typeheadOnSelect({remote:true, url:'/ajax/citys-list'});        

$("#modal-search").data("validate", function()
{        
    var params = [], c = $("#modal-search"), validated = true;
    c.data("params", params);

    var input = c.find("input[name=dt-start]");
    var val = parseInt(input.val());
    if(!val) return alertMsg("Не введена дата!");
    params["dt_start"] = val;

    var input = c.find("select[name=search-from] option:selected");
    var val = parseInt(input.val());
    if(!val) return alertMsg("Не введен пункт отправления!");
    params["search-from"] = val;

    var input = c.find("select[name=search-to] option:selected");
    var val = parseInt(input.val());
    if(!val) return alertMsg("Не введен пункт прибытия!");
    params["search-to"] = val;

    c.data("params", params);    

    return true;     
});

S;
