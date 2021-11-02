<?php
return <<<S

$("#modal-reis-costs-copy").data("validate", function() {        

    var params = [], c = $("#modal-reis-costs-copy"), validated = true;
    c.data("params", params);

    $.each(c.find(":checkbox:checked"), function(i, input){        
        params.push( $(input).attr("name") );
    });

    if( params.length <= 0 )
    {
        return alertMsg("Не выбрано ни одного рейса!");
    }

    c.data("params", params);    
    return true;     
});

S;
