<?php
return <<<S
        
    var from_points = '{$from_points_js}';   
    var trace_points = '{$trace_points_js}';
    var to_points = '{$to_points_js}';

    var modal_id = '#modal-reis-points';
    var modal = $(modal_id);    
        
    modal.find("input[name='from-points']")
    .sortableEndpoints({
            showCity: true,
            showSelector: false
    })
    .sortableEndpoints("initFromJson", JSON.parse(from_points));

    modal.find("input[name='trace-points']")
    .sortableEndpoints({
            showCity: true,
            onlyOneCity: false
    })
    .sortableEndpoints("initFromJson", JSON.parse(trace_points));

    modal.find("input[name='to-points']")
    .sortableEndpoints({
        showCity: true,
        showSelector: false
    })
    .sortableEndpoints("initFromJson", JSON.parse(to_points));        
        
   
   modal.data("validate", function()
    {        
        var params = [];
        modal.data("params", params);    
   
        var ok = true;
        $.each(modal.find("input.cls-data"), function(i, obj){
            var data = $(obj).val().split(":");
            if(isNaN(data[2]) || (parseInt(data[2])<=0) )
            {
                alertMsg("Необходимо выбрать остановку в " + data[1] + " или удалить остановку!");
                return ok = false;
            }
        });
        if(!ok) return false;
        
        ok = true;
        $.each(modal.find("input.cls-time"), function(i, obj){
            if(i==0) return;
            if(isNaN(parseInt($(obj).val())) || (parseInt($(obj).val())<=0) )
            {
                alertMsg("Необходимо указать время в пути для всех пунктов!", function(){
                    $(obj).focus();                
                });                
                return ok = false;
            }
        });
        if(!ok) return false;
        
        params['from-points'] = modal.find("input[name='from-points']").sortableEndpoints("getConfig");
        params['trace-points'] = modal.find("input[name='trace-points']").sortableEndpoints("getConfig");
        params['to-points'] = modal.find("input[name='to-points']").sortableEndpoints("getConfig");

        modal.data("params", params);    
        return true; 
    });
   

S;
