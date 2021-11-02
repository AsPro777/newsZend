<?php
$node = "modal-reis-time";
return <<<S

   $("#$node")
       .find("input[name='date_start']")
       .mask("00.00.0000 00:00", {placeholder: "дд.мм.гггг чч:мм", selectOnFocus: false})
       .val('$date_start')
    ;
     
   $("#$node").data("validate", function()
    {        
        var params = {}, c = $("#$node");
        c.data("params", params);    
        params.date_start = c.find("input[name='date_start']").val();
        c.data("params", params);    
        return true; 
    });   

S;
