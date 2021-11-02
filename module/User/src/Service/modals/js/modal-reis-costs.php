<?php
return <<<S

   var modal = $("#modal-reis-costs");
   var costs = {$costs};
   var costs_ext = {$costs_ext};

   var get_costs = function(wa_id)
   {
        if(costs_ext[wa_id]) return costs_ext[wa_id];
        return costs;
   }

   var view_table = function(wa_id)
   {
        modal.find("td.data-cell").text("X");
        $.each(get_costs(wa_id), function(key, val){
            modal.find("td.data-cell[name='"+key+"']").text(val);
        });
   }

   var edit_table = function(wa_id)
   {
        modal.find(":hidden.wa_id").val(wa_id);
        modal.find("input.cost-input").val(0);

        if(wa_id>0)
            modal.find("input.reset").show();
        else
            modal.find("input.reset").hide();

        $.each(get_costs(wa_id), function(key, val){
            modal.find("input.cost-input[name='"+key+"']").val(val);
        });
   }

   var validate = function()
   {
            var sum = 0;
            var costs_new = {};
            $.each(modal.find("input.cost-input"), function(i, obj){
                var val = parseInt($(obj).val());
                val = isNaN(val) ? 0 : val;
                sum += val;
                if(val>0) costs_new[$(obj).attr("name")] = val.toString();
            });
            if(!sum)
            {
                alertMsg("Необходимо указать цену!");
                return false;
            }

            var cost_ext_exists = costs_ext!={};
            costs_ext["0"] = costs;

            // проверить, что во всех вариантах используются одни и те же остановки (важно для поиска)
            // 1. Относительно новых цен
            var result = true;
            var bad_key = false;
            $.each(costs_new, function(key, value){
                $.each(costs_ext, function(id, obj){
                    result = (obj[key] != undefined);
                    if(!result)
                    {
                        bad_key = key;
                        return false;
                    }
                });
                if(!result) return false;
            });

            // 2. Относительно базовых цен
            if(result)
            {
                var result = true;
                $.each(costs_ext["0"], function(key, value){
                    result = (costs_new[key] != undefined);
                    if(!result)
                    {
                        bad_key = key;
                        return false;
                    }
                });
            }

            // 2. Вся имеющаяся матрица цен
            if(result)
            {
                var result = true;
                $.each(costs_ext, function(wa_id, obj1)
                {
                    $.each(obj1, function(key, value)
                    {
                        $.each(costs_ext, function(dummy, obj2)
                        {
                            result = (obj2[key] != undefined);
                            if(!result)
                            {
                                bad_key = key;
                                return false;
                            }
                        });
                        if(!result) return false;
                    });
                    if(!result) return false;
                });
            }

            if(!result)
            {
                var wa_id = $("#edit-costs form input[name=wa_id]").val();
                var ids = bad_key.split('-');
                var point1 = $("#citys-from a.from[name="+ids[0]+"]").text();
                var point2 = $("#citys-to input[name="+bad_key+"]").closest("tr").find("td").first().text();
                if(wa_id>0)
                {
                    alertMsg("Цены должны быть установлены на одних и тех же остановках во всех вариантах!<br><br>См. остановки:<br> От " + point1 + "<br>До " + point2 + "<br>");
                    return false;
                }
                else
                {
                    if(cost_ext_exists)
                        alertMsg('После сохранения в ценах для агентов останутся те же остановки, что и в версии цен "Исходные"!');
                    return true;
                }
            }

            return true;
   }

   var setCallbacks = function()
   {
        $("#li-view-costs li a.dropdownmenu").unbind("click").on("click", function(){
            $("#li-view-costs li").removeClass("active");
            $("#span-view-costs-title").text( $(this).text() ? ("- "+$(this).text()) : "" )
            view_table($(this).attr("data-wa"));
        });

        $("#li-edit-costs > ul > li > a.dropdownmenu, #li-edit-costs > a.notdropdownmenu").unbind("click").on("click", function(){
            $("#li-edit-costs li").removeClass("active");

            if($(this).hasClass("dropdownmenu"))
                $("#span-edit-costs-title").text( $(this).text() ? ("- "+$(this).text()) : "" );
            else
                $("#span-edit-costs-title").text( "" );

            edit_table($(this).attr("data-wa"));

            $("td#citys-from a.list-group-item.from").on("click", function(){
                $("a.list-group-item.from").removeClass("active");
                $(this).addClass("active");

                var fromPoint = "";

                if($(this).hasClass("from-point"))
                    fromPoint = $("span[name='from-city-name']").text() + " - " + $(this).text();
                else if($(this).hasClass("trace-point"))
                    fromPoint = $(this).text();
                else if($(this).hasClass("to-point"))
                    fromPoint = $("span[name='to-city-name']").text() + " - " + $(this).text();

                $("span[name='city-from']").text(fromPoint);

                $("td#citys-to table tbody tr").addClass("hidden");
                var selector = "td#citys-to table tbody tr:has(input[name^='"+$(this).attr("name")+"-'])";
                $(selector).removeClass("hidden");

                return false;
            });

            $("td#citys-from a.list-group-item.from").first().trigger("click"); // select variant first
        });

        $("input.cost-input").mask("00000", {placeholder: "0", selectOnFocus: false});

        //$("#li-edit-costs > ul > li > a.dropdownmenu, #li-edit-costs > a.notdropdownmenu").first().trigger("click");


   } // setCallbacks

   setCallbacks();






    modal.find("a.list-group-item.from").on("click", function(){
        modal.find("a.list-group-item.from").removeClass("active");
        $(this).addClass("active");

        var fromPoint = "";

        if($(this).hasClass("from-point"))
            fromPoint = modal.find("span[name='from-city-name']").text() + " - " + $(this).text();
        else if($(this).hasClass("trace-point"))
            fromPoint = $(this).text();
        else if($(this).hasClass("to-point"))
            fromPoint = modal.find("span[name='to-city-name']").text() + " - " + $(this).text();

        modal.find("span[name='city-from']").text(fromPoint).css({"font-weight":"bold"});

        modal.find("td#citys-to table tbody tr").addClass("hidden");
        var selector = "td#citys-to table tbody tr:has(input[name^='"+$(this).attr("name")+"-'])";
        modal.find(selector).removeClass("hidden");

        return false;
    });

    modal.find("a.list-group-item.from, th, td").css({"white-space": "nowrap"});
    modal.find("#citys-from").css({"width": "30%"});

    //modal.find("a.list-group-item.from").first().trigger("click");

    modal.find("input.cost-input").mask("00000", {placeholder: "0", selectOnFocus: false});

    modal.find("div.row-submit :button").on("click", function(){

        if(!validate()) return false;

        var costs = {};
        var id = modal.find(":hidden.id").val();
        var wa_id = modal.find(":hidden.wa_id").val();
        var is_copy = $(this).hasClass("copy");
        var is_reset = $(this).hasClass("reset");

        $.each(modal.find(":input.cost-input"), function(i, input){
            costs[$(input).attr("name")] = $(input).val();
        });

        confirmMsg("Вы уверены?", function()
        {
            $.ajax({
                url: "/account/" + ajax_action,
                type: 'post',
                dataType: "json",
                data: {
                    action: is_reset ? 'costs-reset' : 'costs-save',
                    id: id,
                    wa_id: wa_id,
                    costs: costs
                },
                success: function( data ) {
                    if(data.success==1)
                    {
                        if(is_copy)
                        {
                            modal.modal("hide");

                            setTimeout( function() {
                                var w = modalTpl({
                                    id: 'modal-reis-costs-copy',
                                    class: 'modal-lg',
                                    title: "Выберите рейсы для копирования данных из рейса №" + id,
                                    "submit-text": "Копировать",
                                    "close-text": "Не копировать",
                                    "ajax-url": "/account/" + ajax_action,
                                    "ajax-data": {
                                        action: 'modal-reis-costs-copy',
                                        id: id
                                    },
                                    "submit-function": function(){
                                        if($("#modal-reis-costs-copy").data("validate")())
                                        {
                                            $.ajax({
                                                url: "/account/" + ajax_action,
                                                type: 'post',
                                                dataType: "json",
                                                data: {
                                                    action: 'modal-reis-costs-copy-submit',
                                                    id: id,
                                                    reises: $("#modal-reis-costs-copy").data("params")
                                                },
                                                success: function( data ) {
                                                    if(data.success==1)
                                                    {
                                                        $('#calendar').fullCalendar( 'refetchEvents' );
                                                        successMsg("Успешно!");
                                                    }
                                                    else
                                                       alertMsg(data.msg?data.msg:"Копирование не произведено! Ошибка сохранения!");
                                                } // success
                                            });//$.ajax

                                            return true;
                                        }
                                        else
                                            return false;
                                    }
                                }).appendTo($("body"));
                                w.modal("show");
                            }, 100);
                        }
                        else
                        {
                            $('#calendar').fullCalendar( 'refetchEvents' );
                            successMsg("Успешно!", function(){
                                modal.modal("hide");
                            });
                        }
                    }
                    else
                       alertMsg(data.msg?data.msg:"Конфигурация не обновлена! Ошибка сохранения!");
                } // success
            });//$.ajax
        });  // confirm
  
    });

S;
