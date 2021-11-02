<?php
return <<<S
$("a[name='sold-action']").on("click", function(){

    var tr = $(this).closest("tr");
    var id = tr.attr("name");
    var tag = $(this).attr("data-id");

    confirmMsg("Вы уверены?", function(){

        $.ajax({
                url: '/account/' + ajax_action,
                type: 'post',
                dataType: "json",
                data: {
                    id: id,
                    id_reis: '{$id_reis}',
                    action: 'sold-action-another',
                    tag: tag,
                    provider: '{$provider}'
                },
                success: function( data ) {
                    if(data.success==1)
                    {
                        successMsg("Операция выполнена!", function(){});
                        tr.remove();                        
                    }
                    else
                        alertMsg(data.msg?data.msg:"Нет данных по указанному билету!");
                }, // success
            });//$.ajax            
   });
    
   return false;
});
                    
$("a[name='print-action']").on("click", function(){
    
    var token = $(this).closest("tr").attr("data-token");                    
    var turl = $(this).closest("tr").attr("data-turl");                    
    
    if(turl)
    {
        var w = window.open(turl,"_blank", "menubar=no,location=no,toolbar=no,directories=no,status=no,width=850,resizable=yes,scrollbars=yes");
        if(!w) return alert("Необходимо отключить блокировку всплывающих окон для " + window.location.host + " в настройках браузера!");
        w.focus();
        return false;
    }                    
                    
    if(token)                    
        $.ajax({
                url: '/ticket/print',
                type: 'post',
                dataType: "json",
                data: {
                    token: token,
                },
                success: function( data ) {                                                
                    if(data.success==1)
                    {
                        var w = window.open("","_blank", "menubar=no,location=no,toolbar=no,directories=no,status=no,width=850,resizable=yes,scrollbars=yes");
                        if(!w) return alert("Необходимо отключить блокировку всплывающих окон для " + window.location.host + " в настройках браузера!");
                        w.document.open();
                        w.document.write(data.body);
                        w.document.close();
                        w.focus();
                        w.print();
                    }
                    else
                        alert('Билет не найден!');
                }, // success
            });//$.ajax
    
   return false;
});
S;
