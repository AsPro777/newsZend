<?php
return <<<S

$("#modal-reis-payed input[name=payment]").mask("000000", {placeholder: "0", selectOnFocus: false});

$("#modal-reis-payed").data("validate", function()
{        
    var params = [], c = $("#modal-reis-payed"), validated = true;
    c.data("params", params);

    var input = c.find("input[name=payment]");
    var val = parseInt(input.val());
    var max = parseInt( input.attr('data-max-cost') );
    var payed = parseInt( input.attr('data-payed') );

    if(!val) 
        return alertMsg("Не введена сумма!");

    if( (val+payed) > max ) 
        return alertMsg("Cумма платежей превышает сумму по договору! Максимальное возможное значение: " + (max-payed) + " руб.!" );

    params["sum"] = val;
    c.data("params", params);    
    return true;     
});

S;
