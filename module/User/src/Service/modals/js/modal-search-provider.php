<?php
return <<<S

$("#modal-search div.input-group").css({"width":"100%", "margin-top": "5px"});

$("#modal-search input[name='date']").mask("00.00.0000", {placeholder: "дд.мм.гггг", selectOnFocus: false});

$("#modal-search").data("validate", function()
{        
    var params = [], c = $("#modal-search"), validated = true;
    c.data("params", params);

    var input = c.find("input[name=date]");
    var val = parseInt(input.val());
    if(!val) return alertMsg("Не введена дата!");
    params["date"] = val;

    input = c.find("input[name=from]");
    var val = input.val();
    if(!val) return alertMsg("Не введен пункт отправления!");
    params["from"] = val;

    input = c.find("input[name=to]");
    var val = input.val();
    if(!val) return alertMsg("Не введен пункт прибытия!");
    params["to"] = val;

    c.data("params", params);    
    return true;     
});

S;
