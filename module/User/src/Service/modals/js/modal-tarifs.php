<?php
return <<<S

   $("#modal-reis-tarifs").find("input[name='cargo']").mask("00", {placeholder: "10%", selectOnFocus: false}).val({$cargo});
   
   var tarifs = '{$tarifs}';   
   $("#modal-reis-tarifs").find("input[name='tarifs']").sortableList().sortableList("initFromJson", JSON.parse(tarifs));
   
   $("#modal-reis-tarifs").data("validate", function()
    {        
        var params = {}, c = $("#modal-reis-tarifs");
        c.data("params", params);    

        params.cargo = parseInt( "0"+c.find("input[name='cargo']").val() );
        params.tarifs = c.find("input[name='tarifs']").sortableList("getConfig");

        c.data("params", params);    
        return true; 
    });
   

S;
