<?php
return <<<S
$("a[name='reserved-action']").on("click", function(){

    var tr = $(this).closest("tr");
    var id = tr.attr("name");
    var tag = $(this).attr("data-id");
    var dt_drop = $(this).attr("data-drop");

    var message = "Вы уверены?";
    if( (tag==1) && (dt_drop != '') ) message = 'Данный билет забронирован по быстрой схеме и в нем необходимо произвести заполнение информации о пассажире!<br><br>Вы это уже сделали?';

    confirmMsg(message, function(){

        $.ajax({
                url: '/account/' + ajax_action,
                type: 'post',
                dataType: "json",
                data: {
                    id: id,
                    id_reis: '{$id_reis}',
                    action: 'reserved-action',
                    tag: tag
                },
                success: function( data ) {
                    if(data.success==1)
                    {
                        successMsg("Операция выполнена!", function(){
                            $('#calendar').fullCalendar('refetchEvents');
                        });
                        tr.remove();
                    }
                    else
                        alertMsg(data.msg?data.msg:"Нет данных по указанному билету!");
                }, // success
            });//$.ajax
   });

   return false;
});

$("a[name='reserved-pax-edit']").on("click", function(){

    var tr = $(this).closest("tr");
    var id = tr.attr("name");
    var w = modalTpl({
        id: 'modal-reserved-pax-edit',
        //class: 'modal-lg',
        title: "Изменить данные пассажира",
        "submit-text": "Сохранить",
        "close-text": "Отмена",
        "ajax-url": "/account/" + ajax_action,
        "ajax-data": {
            action: 'modal-reserved-pax-edit',
            id: id
        },
        "submit-function": function(){
            if($("#modal-reserved-pax-edit").data("validate")())
            {
                var pax_data = $.extend({}, $("#modal-reserved-pax-edit").data("params"), {id:id, action:'reserved-pax-edit'})
                $.ajax({
                    url: "/account/" + ajax_action,
                    type: 'post',
                    dataType: "json",
                    data: pax_data,
                    success: function( data ) {
                        if(data.success==1)
                        {
                            successMsg("Успешно!");
                            tr.find("td:eq(2)").text(pax_data.f + " " + pax_data.i + " " + pax_data.o);
                        }
                        else
                           alertMsg(data.msg?data.msg:"Изменение не произведено! Ошибка сохранения!");
                    } // success
                });//$.ajax

                return true;
            }
            else
                return false;
        }
    }).appendTo($("body"));
    w.modal("show");

    return false;
});

$("a[name='view-action']").on("click", function(){

    var tr = $(this).closest("tr");
    var id = tr.attr("name");
    showPreview(id);
    return false;
});


showPreview = function(id)
{
    if(!id) return;
    var action = 'ticket-data';

    $.ajax({
            url: '/account/'+ajax_action,
            type: 'post',
            dataType: "json",
            data: {
                id: id,
                action: action
            },
            success: function( data ) {
                if(data.success==1)
                {
                    var w = window.open(data.body, "_blank", "menubar=no,location=no,toolbar=no,directories=no,status=no,width=850,resizable=yes,scrollbars=yes");
//                    var w = window.open("","_blank", "menubar=no,location=no,toolbar=no,directories=no,status=no,width=850,resizable=yes,scrollbars=yes");
                    if(!w) return alertMsg("Необходимо отключить блокировку всплывающих окон для " + window.location.host + " в настройках браузера!");
//                    w.document.open();
//                    w.document.write(data.body);
//                    w.document.close();
                    w.focus();
                }
                else
                    alertMsg('Билет '+id+' не найден!');
            }, // success
        });//$.ajax
};

S;
