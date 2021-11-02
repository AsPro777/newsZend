<?php

return <<<S

$("div.panel.tpl").find("input").on("input", function(){
    $("span.btn-save-dropdown-row").show();
});

 $("div.panel.tpl input.form-control[name='bankbik']")
     .mask("000000000", {placeholder: "_________", selectOnFocus: true});
 $("div.panel.tpl input.form-control[name='bankks']")
     .mask("00000000000000000000", {placeholder: "____________________", selectOnFocus: true});
 $("div.panel.tpl input.form-control[name='bankrs']")
     .mask("00000000000000000000", {placeholder: "____________________", selectOnFocus: true});

var check = function()
{
    var v = "";
    
    v = $("div.panel.tpl input.form-control[name='bankname']").val();
    if(v.length<3)
        return alertMsg("Короткое наименование банка!") || false;

    v = $("div.panel.tpl input.form-control[name='bankbik']").val();
    if(v.length && (v.length<9))
        return alertMsg("БИК состоит из 9 цифр!") || false;

    v = $("div.panel.tpl input.form-control[name='bankks']").val();
    if(v.length && (v.length<20))
        return alertMsg("КС состоит из 20 цифр!") || false;

    v = $("div.panel.tpl input.form-control[name='bankrs']").val();
    if(v.length && (v.length<20))
        return alertMsg("РС состоит из 20 цифр!") || false;

    return true;
}

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
