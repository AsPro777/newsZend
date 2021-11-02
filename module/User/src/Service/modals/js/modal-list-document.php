<?php
return <<<S

var current_email = '{$user_email}';

$("span.btn-remove")
        .unbind("click")
    .on("click",function(){
            var self = this;
            var id = $(self).attr("name");

            if(!confirm("Удалить документ?")) return false;

            $.ajax({
                url: '/account/docs',
                type: 'post',
                dataType: "json",
                data: {
                    action: 'remove',
                    id: id                        
                },
                success: function( data ) {
                    if(data.success) {
                       var label = $('<span class="label label-success">Документ удален!</span>');
                       $("#info-"+id).append(label);          
                       var tr = $("#info-"+id).parent();
                       setTimeout(function(){tr.remove();}, 2000);                           
                    } else {                            
                        if(data.msg || data.err) alertMsg(data.msg + "\\r\\n\\r\\n" + (data.err?data.err:""));
                    }
                } // success
            });//$.ajax                                        
}); 

$("input.send-to-email-ok")
        .unbind("click")
    .on("click",function(){
            var self = this;
            var input = $(this).parent().find("input.send-to-email");
            var id = input.attr("name");
            var email = input.val();

            if($.trim(email)=="")
            {
                 alertMsg("Не введен адрес электронной почты!");
                 return false;
            }

            $.ajax({
                url: '/account/docs',
                type: 'post',
                dataType: "json",
                data: {
                    action: 'send',
                    id: id,
                    email: email
                },
                success: function( data ) {
                    if(data.success) {
                       var label = $('<br><br><span class="label label-success">Отправлено!</span>');
                       $(self).after(label);

                       setTimeout(function(){label.remove();}, 2000);
                       setTimeout(function(){\$("div.btn-group").removeClass("open");}, 2500);

                    } else {                            
                        if(data.msg || data.err) alertMsg(data.msg + "\\r\\n\\r\\n" + (data.err?data.err:""));
                    }
                } // success
            });//$.ajax                                


}); 

$("button.dropdown-toggle").on("click", function(){        
   var self = this; 
   setTimeout(function(){
       $(self).parent().find("input.send-to-email").focus(); 
   }, 100);        
});

$("input.send-to-email").val(current_email); 

S;
