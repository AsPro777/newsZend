<?php

return <<<S

var isValidDate = function (date)
{
    date = date.split('.');
    if(date.length<3) return false;

    var d = date[0], m = date[1], y = date[2];
    var dt = new Date();
    dt.setUTCFullYear(y, m-1, d);

    var result = ((y == dt.getUTCFullYear()) && ((m-1) == dt.getUTCMonth()) && (d == dt.getUTCDate()));
    if(!result) 
        return alertMsg("Некорректная дата: "+date+"!") && false;

    var nextMonth = new Date();
    nextMonth.setUTCFullYear(nextMonth.getFullYear(), nextMonth.getMonth()+1, nextMonth.getDate());

    if(nextMonth > dt)
    {
        alertMsg("Дата истечения должна быть в будущем, не менее месяца от сегодняшней даты!\\r\\n\\r\\nИзмените дату: "+date+"!");
        return false;
    }
    return result;
}

var check = function()
{      
    var lcensydtend = $("div.panel.tpl input.form-control[name='lcensydtend']").val();
    var lcensynum = $("div.panel.tpl input.form-control[name='lcensynum']").val();
    var lcensypermanently = $("div.panel.tpl input[name='lcensypermanently']").prop("checked");

    if(lcensynum.length) {
        if(!lcensypermanently && !isValidDate(lcensydtend) ) 
        {
            alertMsg("Номер лицензии и дата истечения должны быть указаны!");
            return false;
        }
    }

    var internationaldtend = $("div.panel.tpl input.form-control[name='internationaldtend']").val();
    var internationalnum = $("div.panel.tpl input.form-control[name='internationalnum']").val();

    if( internationalnum.length || internationaldtend.length ) 
      if( (!isValidDate(internationaldtend) || !internationalnum.length) )
        {            
            alertMsg("Номер удостоверения допуска к международным перевозкам и дата истечения должны быть указаны!");
            return false;
        }

    var ugadndtend = $("div.panel.tpl input.form-control[name='ugadndtend']").val();
    var ugadnnum = $("div.panel.tpl input.form-control[name='ugadnnum']").val();
            
    if( ugadnnum.length || ugadndtend.length)
      if( !isValidDate(ugadndtend) || !ugadnnum.length ) 
        {
            alertMsg("Номер уведомления УГАДН и дата истечения должны быть указаны!");
            return false;
        }
    
    return true;
}

$("div.panel.tpl").find(":text").on("change", function(){
    $("span.btn-save-dropdown-row").show();
});

$("div.panel.tpl").find(":checkbox").on("change", function(){
    $("span.btn-save-dropdown-row").show();
    var div = $("div.row.view-if-lcensypermanently");
    div.toggleClass("hidden");
    if(div.hasClass("hidden")) div.find(":text").val("");
});

 $("div.panel.tpl input.form-control[name$='dtend']")
     .mask("00.00.0000", {placeholder: "дд.мм.гггг", selectOnFocus: false});

$("span.btn-save-dropdown-row")
    .unbind("click")
    .on("click", function() {
        if(!check()) return;
        var trSetup = $(this).parent().parent().first();
        var trUser = trSetup.prev("tr");
        var data = {
            id: $(trUser).attr("name"),
            action: "set-profile",
            block: $("div.panel.tpl").attr("data-node"),            
        };        
        $.each($("div.panel.tpl").find(":text"), function(i, obj){            
                data[$(obj).attr("name")] = $(obj).val();
        });
        $.each($("div.panel.tpl").find(":checkbox"), function(i, obj){            
                data[$(obj).attr("name")] = $(obj).prop("checked");
        });

        $.ajax({
                url: '/account/'+ajax_action,
                type: 'post',
                dataType: "json",
                data: data,
                success: function( data ) {
                    if(data.success) {                            
                        toggleDropdownRow(trSetup, "", false);
                        if(data.msg) trUser.find(".result").html('<span class="label label-success">Выполнено!</span>');
                    } else {                            
                        if(data.msg || data.err) alertMsg(data.msg + "\\r\\n\\r\\n" + (data.err?data.err:""));
                    }
                } 
            });
});

S;
