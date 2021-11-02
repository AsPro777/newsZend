<?php
return <<<S

$("#modal-select-bus").data("validate", function()
{        
    var params = {}, c = $("#modal-select-bus");
    c.data("params", params);    

    if( parseInt( "0"+c.find("#bus").val() ) == 0 )
        return alertMsg("Необходимо выбрать автобус!");            

    params.id_bus = parseInt( "0"+c.find("#bus").val() );

    c.data("params", params);    
    return true; 
});

S;
