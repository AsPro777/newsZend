<?php
return <<<S

var check = function()
{      
    var access = $("div.panel.tpl :checkbox[name='access']").prop("checked");
    if(!access) return true;

    var login = $("div.panel.tpl input.form-control[name='walogin']").val();
    var passwd = $("div.panel.tpl input.form-control[name='wapassword']").val();
console.log(login, passwd);
    if( (login.length < 8) || (login.length > 15) )
    {
        alertMsg("Неподходящий логин! Ожидается от 8 до 15 символов!");
        return false;
    }
        
    if( (passwd.length < 8) || (passwd.length > 15) )
    {
        alertMsg("Неподходящий пароль! Ожидается от 8 до 15 символов!");
        return false;
    }
    
    return true;
}

$("div.panel.tpl").find(":text, :checkbox").on("change", function(){
    $("span.btn-save-dropdown-row").show();
});

$("div.panel.tpl").find(":checkbox[name=access]").on("change", function(){
    $("span.btn-save-dropdown-row").show();
    var div = $("div.row.view-if-access");
    div.toggleClass("hidden");    
});

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

$("div.panel.tpl").find(":button.password-generator").on("click", function(){
        var passwd = '';
        var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        for (i=1;i<10;i++) {
          var c = Math.floor(Math.random()*chars.length + 1);
          passwd += chars.charAt(c);
        }
        $("div.panel.tpl").find(":text[name='wapassword']").val(passwd);
        $("span.btn-save-dropdown-row").show();
});

var login_input = $("div.panel.tpl").find(":text[name='walogin']");
if($.trim(login_input.val())=='') 
{
    login_input.val('webaccess_'+$("div.panel.tpl").closest('tr.dropdown-row').prev().attr('name'));
    $("span.btn-save-dropdown-row").show();
}

S;
