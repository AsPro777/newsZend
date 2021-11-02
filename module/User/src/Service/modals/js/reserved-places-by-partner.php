<?php

return <<<S
    
    var setCallBacks = function(){

        $("#modal-table ul > li.selling")
            .unbind("click")
            .on("click", function(){
                var self = this;
                var timeValue = 15; // минут
                var id_reis = $(this).closest("table").attr("name");
                var id = $(this).closest("tr").attr("name");

                $.ajax({
                        url: '/account/reises',
                        type: 'post',
                        dataType: "json",
                        data:{
                            id_reis: id_reis,
                            place: id,
                            value: timeValue,
                            action: "set-reserved-place"
                        },
                        success: function( data ) {
                            if(data.success)
                            {
                                successMsg("Место " +id+ " недоступно для продаж другим кассирам на "+timeValue+" минут и удалено из списка партнерской брони!", function(){
                                    
                                    $(self).closest("tr").find("td.status").text("Резерв для оформления билета на " + timeValue + " минут.");
                                    $(self).closest("tr").find("td.blocking, td.dispatcher").text("");
                                    
                                    var w = modalTpl({
                                        id: 'modal-sell',
                                        title: "Оформление билета. {$reis_info}",
                                        class: 'modal-lg',
                                        "ajax-url": "/account/sells",
                                        "ajax-data": {
                                            action: 'modal-sell',
                                            id: id_reis,
                                            provider: ''
                                        },
                                        "submit-function": function(){                                            
                                            return false;
                                        },
                                        "ajax-callback": function(data){
                                            $("#modal-sell input[name=place]").val(id);
                                        },
                                    }).appendTo($("body"));
                                    w.modal("show");
                                        
                                    w.on('hidden.bs.modal', function() {        
                                        console.log('hidden.bs.modal');
                                        $(self).closest("tr").find("td.status").text("Разблокировано. Резерв на " + timeValue + " минут. Оформление билета завершено.");
                                        //$('#calendar').fullCalendar('refetchEvents');                                        
                                    });    
                                        
                                });
                            }
                            else    
                                alertMsg(data.msg?data.msg:"Ошибка данных!");
                        }, // success
                    });//$.ajax                
            });
    }

    $("#modal-table ul > li.blocking").on("click", function(){
                            
        var id = $(this).closest("tr").attr("name");
        var id_reis = $(this).closest("table").attr("name");
        var action = $(this).hasClass("plus") ? "block" : "unblock";
        $.ajax({
                url: '/account/'+ajax_action,
                type: 'post',
                dataType: "json",
                data:{
                    id_reis: id_reis,
                    place: id,
                    action: "set-partner-reserved-" + action
                },
                success: function( data ) {
                    if(data.success)
                    {
                        $("#modal-table tr[name='"+id+"'] ul > li.blocking")
                            .removeClass("plus") 
                            .removeClass("minus") 
                            .addClass( (action=="block") ? "minus" : "plus")
                            .html( (action=="block") ? "<a><i class='icon-minus3 position-left'></i> Разблокировать</a>" : "<a><i class='icon-plus3 position-left'></i> Заблокировать</a>");

                        $("#modal-table tr[name='"+id+"'] td.status")
                            .html( (action=="block") ? "Блокировка партнером" : "");
                        
                        if({$is_dispatcher})    
                        {
                            $("#modal-table tr[name='"+id+"'] td.dispatcher")                            
                            .html( (action=="block") ? "<ul class='icons-list'><li class='text-info plus selling'><a><i class='icon-ticket position-left'></i> Оформить билет</a></li></ul>" : "");
                            setCallBacks();
                        }
                    }
                    else    
                        alertMsg(data.msg?data.msg:"Конфигурация не обновлена! Ошибка сохранения!");
                }, // success
            });//$.ajax

    });

                        
setCallBacks();                        
S;
