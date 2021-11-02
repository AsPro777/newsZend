<?php
return <<<S
var m_id = "#modal-reserved-pax-edit";
$(m_id).find("input[name='dr']").mask("00.00.0000", {placeholder: "дд.мм.гггг", selectOnFocus: false});
$(m_id).find("input[name='passport']").mask("{$passport_type_mask}", {placeholder: "{$passport_type_mask}", selectOnFocus: false}).val("{$doc_num}");
$(m_id).find("select[name='grazhd']").typeheadOnSelect({remote:true, url:'/ajax/get-country'});

$(m_id).find("select[name='passport_type']").typeheadOnSelect({    
    remote:true,
    url:'/ajax/get-doc-type',        
    onSelectFn:function(){            
          var row = $(m_id).find("select[name='passport_type'] option:selected");
          var passport = $(m_id).find("input[name='passport']");
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
$(m_id).find("input[name='__passport_type-typeheadOnSelect']").attr("name", "passport_type_txt");    
$(m_id).find("input[name='__grazhd-typeheadOnSelect']").attr("name", "grazhd_txt");    


$(m_id).data("validate", function()
{        
    var params = [], c = $(m_id), validated = true;
    c.data("params", params);

    params["f"] = $.trim($(c).find("input[name='f']").val());
    validated = validated && (params["f"] != "");
    if(!validated)
        return alertMsg("Не заполнено или неправильно заполнено поле «Фамилия»!");        

    params["i"] = $.trim($(c).find("input[name='i']").val());
    validated = validated && (params["i"] != "");
    if(!validated)
        return alertMsg("Не заполнено или неправильно заполнено поле «Имя»!");

    params["o"] = $.trim($(c).find("input[name='o']").val());
    validated = validated && (params["o"] != "");
    if(!validated)
        return alertMsg("Не заполнено или неправильно заполнено поле «Отчество»!");

    params["dr"] = $.trim($(c).find("input[name='dr']").val());
    validated = validated && !isInvalidDate(params["dr"]);
    if(!validated)
        return alertMsg("Не заполнено или неправильно заполнено поле «Дата рождения»!");

    params["grazhd"] = $.trim($(c).find("select[name='grazhd'] option:selected").val());
    validated = validated && (params["grazhd"] > 0);
    if(!validated)
        return alertMsg("Не заполнено или неправильно заполнено поле «Гражданство»!");

    params["passport_type"] = $.trim($(c).find("select[name='passport_type'] option:selected").val());
    validated = validated && (params["passport_type"] != "");
    if(!validated)
        return alertMsg("Не заполнено или неправильно заполнено поле «Удостоверение личности»!");

    params["passport"] = $(c).find("input[name='passport']").val();    
    validated = validated && isValidMasker("passport");
    if(!validated)
        return alertMsg("Не заполнено или неправильно заполнено поле «Серия и номер»!");
        
    params["sex"] = $.trim($(c).find("select[name='sex'] option:selected").val());    

    c.data("params", params);    
    return true;     
});

var isInvalidDate = function(date)
{
    var saved = date;
    date = date.split('.');
    if(date.length<3) return true;

    var d = date[0], m = date[1], y = date[2];
    var dt = new Date();
    if( (y<1900) || (y>dt.getUTCFullYear()) ) return true;

    dt.setUTCFullYear(y, m-1, d);

    var result = ((y == dt.getUTCFullYear()) && ((m-1) == dt.getUTCMonth()) && (d == dt.getUTCDate()));
    return !result;
}

var isValidMasker = function(name)
{    
    var result = true;
    var item = $("[name='"+name+"']");        

    var value = item.val();
    var mask = $.trim(item.attr("data-mask"));
    var hasMask = mask.split("_").join("") != "";

    if(value=="") return false; 
    if( !hasMask ) return true;                
    if(value.length!=mask.length) return false;

    return result;    
}

S;
