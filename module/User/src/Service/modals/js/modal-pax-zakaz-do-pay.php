<?php
$node = "modal-pax-zakaz-do-pay";
return <<<S

$("#$node input[name=payment]")
    .mask("00000", {placeholder: "0", selectOnFocus: false})
    .focus();

$("#$node").data("validate", function()
{
    var params = [], c = $("#$node"), validated = true;
    c.data("params", params);

    var input = c.find("input[name=payment]");
    var val = parseInt(input.val());

    if(!val)
        return alertMsg("Не введена сумма!");

    params["sum"] = val;
    c.data("params", params);
    return true;
});

S;
