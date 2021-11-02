<?php
return <<<S
 $("#modal-add-pax").find("input[name='dr']").mask("00.00.0000", {placeholder: "дд.мм.гггг", selectOnFocus: false});
$("#modal-add-pax").find("select[name='grazhd']").typeheadOnSelect({remote:true, url:'/ajax/get-country'});

$("#modal-add-pax").find("select[name='passport_type']").typeheadOnSelect({    
    remote:true,
    url:'/ajax/get-doc-type',        
    onSelectFn:function(){            
          var row = $("#modal-add-pax").find("select[name='passport_type'] option:selected");
          var passport = $("#modal-add-pax").find("input[name='passport']");
          if(row.length && row.attr("data-base")) {
                var option = JSON.parse(row.attr("data-base"));
                if(option.mask && (option.mask!=""))
                    passport.mask(option.mask, {placeholder: option.mask});
                else
                    passport.mask('_______________', {placeholder: " "});
                $("#doc-num-example").html("<b>Подсказка: </b>"+(option.example?option.example:"Формат \"серия-номер\" смотри в документе.")).addClass("alert alert-info");
            } 
            }
});
$("#modal-add-pax").find("input[name='__passport_type-typeheadOnSelect']").attr("name", "passport_type_txt");    
$("#modal-add-pax").find("input[name='__grazhd-typeheadOnSelect']").attr("name", "grazhd_txt");    

S;
