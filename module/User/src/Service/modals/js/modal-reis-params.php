<?php
return <<<S
$("#modal-reis-params div.row").css({"padding-top":"5px"});
$("#modal-reis-params input.form-control[name='cost']")
    .mask("00000000", {placeholder: "________", selectOnFocus: false})

$("#modal-reis-params").data("validate", function()
{        
    var params = {}, c = $("#modal-reis-params");
    c.data("params", params);    

    if( parseInt( "0"+c.find("input[name='cost']").val() ) == 0 )
        return alertMsg("Необходимо указать сумму!");            

    params.cost = parseInt( "0"+c.find("input[name='cost']").val() );
    params.obratno = c.find(":checkbox[name='obratno']").prop('checked');
    params.razvoz = c.find(":checkbox[name='razvoz']").prop('checked');
    params.excursiya = c.find(":checkbox[name='excursiya']").prop('checked');

    c.data("params", params);    
    return true; 
});

S;
