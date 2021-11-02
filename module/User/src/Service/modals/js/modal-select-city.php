<?php
return <<<S
    
$( "#modal-select-city input[name='city']" ).cityselector({
    valueInputPathCity: "#modal-select-city input[name='id_city']", 
    valueInputPathCountry: "#modal-select-city input[name='id_country']",
});    

$("#modal-select-city").data("validate", function()
{        
    var params = {}, c = $("#modal-select-city");
    c.data("params", params);    

    if( parseInt( "0" + c.find("input[name='id_city']").val() ) == 0 )
        return alertMsg("Необходимо выбрать город из списка!");            

   params.id_city = parseInt( c.find("input[name='id_city']").val() );
   params.id_country = parseInt( c.find("input[name='id_country']").val() );    

   c.data("params", params);    
   return true; 
});

$("#modal-select-city input[name='city']").focus();

S;
