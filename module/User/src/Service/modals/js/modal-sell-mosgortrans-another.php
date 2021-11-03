<?php
return <<<S
/* инициализация */
var id_reis = '{$id_reis}';

var modal = $("div.ticket-sell-content");
var footer = modal.closest("div.modal-content").find("div.modal-footer");
var placesReloadTimerId = 0;
modal.find("div.input-group").css({"width":"100%","margin-top":"2px"});

modal.find("select.cls-grazhd").typeheadOnSelect({});
modal.find("select.cls-doc-type").typeheadOnSelect({onSelectFn:function(num){
        var option = modal.find("select.cls-doc-type option:selected").first().attr("data-base"); 
        //if(!option) return;
        option = JSON.parse(option); 
        $("#doc-num-example").html("<b>Подсказка: </b>"+(option.example?option.example:"Формат \"серия-номер\" смотри в документе."));
        if(option.mask && (option.mask!=""))
            modal.find("input.cls-doc-num").first().mask(option.mask, {placeholder: option.mask});
        else
            modal.find("input.cls-doc-num").first().mask('_______________', {placeholder: " "});
}});  

/* buttons on footer */

var submitter = footer.find("button.btn-submit")
    .attr("data-type", 1)
    .text("Продать")
    .on("click", function(){    
        modal.find("select[name='order-type']").val($(this).attr("data-type"));
        submitTicket();
    });
  
/* обработчики */   
            
modal.find("button.btn-phone").on("click", function(){
            
    var ph = modal.find("input.cls-phone").val();
    if(!ph) return alertMsg("Введите номер!");
            
    $.ajax({
            url: '/account/sells',
            type: 'post',
            dataType: "json",
            data: {
                phone: ph,
                action: 'pax-data-by-phone'
            },
            success: function( data ) {
                if(data.success==1)
                {
                    var pax = JSON.parse(data.pax);
                    modal.find("select.cls-sex").val(pax.sex);
                    modal.find("input.cls-f").val(pax.f);
                    modal.find("input.cls-i").val(pax.i);
                    modal.find("input.cls-o").val(pax.o);
                    modal.find("input.cls-dr").val(pax.dr);
                    modal.find("select.cls-grazhd").val(pax.grazhd).trigger("change");
                    modal.find("select.cls-doc-type").val(pax.doc_type).trigger("change");
                    modal.find("input.cls-doc-num").val(pax.doc_num);
                }
                else
                alertMsg(data.msg?data.msg:"Нет данных по указанному номеру!");
            }, // success
        });//$.ajax
});
            
modal.find("select[name='to-point']").on("change", function(){
 
    reset_calculate();
    $.ajax({
            url: '/account/' + ajax_action,
            type: 'post',
            dataType: "json",
            data: {
                "action": "ticket-by-points-load-provider",
                'id_reis': id_reis,
//                'id_from_point': modal.find("select[name='from-point']").val(),
                'id_to_point': modal.find("select[name='to-point']").val(),
                'provider': '{$provider}',
                },

            success: function( data ) {
                if(data.success==1)
                {
                    id_reis = data.id_reis;
                
                    if(data.cargo)
                        modal.find("select[name='cargo']").empty().append($(data.cargo));
                    if(data.places)
                        modal.find("select[name='place']").empty().append($(data.places));
                    if(data.tarifs)
                        modal.find("select[name='tarif']").empty().append($(data.tarifs));
                    modal.find("select[name='cargo']").prop("disabled", data.cargo_cost?null:"disabled").val(0);
                    
                    calculate();
                }
                else
                alertMsg(data.msg?data.msg:"Нет пунктов прибытия для данного пункта отправления!");
            }, // success
        });//$.ajax
});

modal.find("select[name='tarif']").on("change", function(){
   calculate();
});
                
modal.find("select[name='cargo']").on("change", function(){
   calculate();
});
    
                
/* Калькуляция */   
             
var calculate = function()
{                
    var ticket_cost_info = modal.find(".cls-ticket-cost-info");
    var cargo_cost_info = modal.find(".cls-cargo-cost-info");
    var comission_cost_info = modal.find(".cls-comission-cost-info");
    var comission_in_cost_info = modal.find(".cls-comission-in-cost-info");
    var total_cost_info = modal.find(".cls-total-cost-info");

    ticket_cost_info.val("").text("").closest("div.row").hide();
    cargo_cost_info.val("").text("").closest("div.row").hide();
    comission_cost_info.val("").text("").closest("div.row").hide();
    comission_in_cost_info.val("").text("").closest("div.row").hide();
    total_cost_info.val("").text("").closest("div.row").hide();                

    var container = ticket_cost_info.closest("div.panel-body");
    container.append($('<div />').attr('id', 'cost-loading-indicator').html('<i class="false fa fa-spinner fa-pulse fa-fw" aria-hidden="true"></i>').show());

    $.ajax({
            url: '/account/' + ajax_action,
            type: 'post',
            dataType: "json",
            data: {
                "action": 'ticket-calculate-costs-provider',
                'id_reis': id_reis,
                'id_from': modal.find("select[name='from-point'] option:selected").val(),
                'id_to': modal.find("select[name='to-point'] option:selected").val(),                
                'provider': '{$provider}',
                "cargo": modal.find("select[name='cargo'] option:selected").val(),
                "id_tarif": modal.find("select[name='tarif'] option:selected").val(),
            },
            success: function( data ) {
                if(data.success==1)
                {
                    ticket_cost_info.val(data.ticket_cost_info).text(data.ticket_cost_info).closest("div.row").css({"display":((parseInt(data.ticket_cost_info)>0)?"block":"none")});
                    cargo_cost_info.val(data.cargo_cost_info).text(data.cargo_cost_info).closest("div.row").css({"display":((parseInt(data.cargo_cost_info)>0)?"block":"none")});
                    comission_cost_info.val(data.comission_cost_info).text(data.comission_cost_info).closest("div.row").css({"display":((parseInt(data.comission_cost_info)>0)?"block":"none")});
                    comission_in_cost_info.val(data.comission_in_cost_info).text(data.comission_in_cost_info).closest("div.row").css({"display":((parseInt(data.comission_in_cost_info)>0)?"block":"none")});
                    total_cost_info.val(data.total_cost_info).text(data.total_cost_info).closest("div.row").css({"display":((parseInt(data.total_cost_info)>0)?"block":"none")});
                }
                $("div#cost-loading-indicator").remove();
            }, // success
        });//$.ajax
};
                  
var reset_calculate = function()
{    
    var ticket_cost_info = modal.find(".cls-ticket-cost-info");
    var cargo_cost_info = modal.find(".cls-cargo-cost-info");
    var comission_cost_info = modal.find(".cls-comission-cost-info");
    var comission_in_cost_info = modal.find(".cls-comission-in-cost-info");
    var total_cost_info = modal.find(".cls-total-cost-info");

    ticket_cost_info.val("").text("");
    cargo_cost_info.val("").text("");
    comission_cost_info.val("").text("");
    comission_in_cost_info.val("").text("");
    total_cost_info.val("").text("");
};
                
/* загрузка окна */   
                
reset_calculate();                
                
/* загрузка первичных ИД */

$.ajax({            
        url: '/account/' + ajax_action,
        type: 'post',
        dataType: "json",
        data: {
            'id_reis': id_reis,
            'provider': '{$provider}',
            'action': 'ticket-window-load-provider'
        },
        success: function( data ) {            
            if(data.success==1)
            {
                if(data.from_points)
                    modal.find("select[name='from-point']").empty().append($(data.from_points));
                if(data.to_points)
                    modal.find("select[name='to-point']").empty().append($(data.to_points));
                if(data.tarifs)
                    modal.find("select[name='tarif']").empty().append($(data.tarifs));
                if(data.places)
                    modal.find("select[name='place']").empty().append($(data.places));
                if(data.grazhd)
                    modal.find("select[name='grazhd']").empty().append($(data.grazhd)).trigger("onload");            
                if(data.doc_type)
                    modal.find("select[name='doc-type']").empty().append($(data.doc_type)).trigger("onload");
                if(data.ticket_comment)    
                    modal.find("textarea[name='comment']").text(data.ticket_comment);                                    
                    
                if(data.cargo)
                    modal.find("select[name='cargo']").empty().append($(data.cargo));
                modal.find("select[name='cargo']").prop("disabled", data.cargo_cost?null:"disabled").val(0);
            
                setTimeout(function(){calculate();}, 100);            
            }
            if(data.msg)
                alertMsg(data.msg);
        }, /* success */
        beforeSend: function(){
            setTimeout(function(){
                $( "#load-indicator" ).show();
            }, 100);            
        },    
        complete: function(){
            $( "#load-indicator" ).hide();
        }    
    });/* $.ajax */
            
 /* Submitter */            
             

var checkSubmittedValues = function()
{    
    var place = modal.find("select.cls-place");
    var f = modal.find("input.cls-f");
    var i = modal.find("input.cls-i");
    var o = modal.find("input.cls-o");
    var dr = modal.find("input.cls-dr");
    var doc_num = modal.find("input.cls-doc-num");
    var phone = modal.find("input.cls-phone");
    var cost = modal.find("input[name='total-cost-info']");

    if(!place.val()) return "Необходимо заполнить: Номер места!";
    if(parseInt(place.val())<1) return "Номер места должен быть больше 0!";

    if(!f.val()) return "Необходимо заполнить: Фамилия!";                
    if( /[a-zA-Z]+/.test(f.val()) && /[а-яА-ЯЁё]+/.test(f.val())) return "В Фамилии не должны использоваться одновременно символы разных алфавитов!";
    if( /[^a-zA-Zа-яА-ЯЁё `-]+/.test(f.val()) ) return "В Фамилии должны использоваться только буквы, пробелы, тире и апостроф (`)!";
                
    if(!i.val()) return "Необходимо заполнить: Имя!";
    if( /[a-zA-Z]+/.test(i.val()) && /[а-яА-ЯЁё]+/.test(i.val())) return "В имени не должны использоваться одновременно символы разных алфавитов!";
    if( /[^a-zA-Zа-яА-ЯЁё -]+/.test(i.val()) ) return "В имени должны использоваться только буквы, пробелы и тире!";

   //if(!o.val()) return "Необходимо заполнить: Отчество!";
    if( /[a-zA-Z]+/.test(o.val()) && /[а-яА-ЯЁё]+/.test(o.val())) return "В отчестве не должны использоваться одновременно символы разных алфавитов!";
    if( /[^a-zA-Zа-яА-ЯЁё -]+/.test(o.val()) ) return "В отчестве должны использоваться только буквы, пробелы и тире!";
                
    var fio = f.val() + i.val() + o.val();
    if( /[a-zA-Z]+/.test(fio) && /[а-яА-ЯЁё]+/.test(fio)) return "В ФИО не должны использоваться одновременно символы разных алфавитов!";
            
    if(!dr.val()) return "Необходимо заполнить: Дата рождения!";
    if(!doc_num.val()) return "Необходимо заполнить: Серия и номер!";

    if(!phone.val()) return "Необходимо заполнить: Телефон!";
            
    if(!cost.val()) return "Не указана цена!";
            
    return "";
};

var submitTicket = function(){
    var checked = checkSubmittedValues();
    if(!!checked) {
          alertMsg(checked);
          return false;
    }

    var data = {
                'action': 'ticket-create-provider',
                'id_reis': id_reis,
                'provider': '{$provider}',
                'id_from_point': modal.find("select[name='from-point']").val(),
                'id_to_point': modal.find("select[name='to-point']").val(),
                'id_tarif': modal.find("select[name='tarif']").val(),
                'cargo_num': modal.find("select[name='cargo']").val(),
                'place': modal.find("select[name='place']").val(),

                'f': modal.find("input[name='f']").val(),
                'i': modal.find("input[name='i']").val(),
                'o': modal.find("input[name='o']").val(),
                'sex': modal.find("select[name='sex']").val(),
                'dr': modal.find("input[name='dr']").val(),
                'grazhd': modal.find("select[name='grazhd']").val(),
                'doc_type': modal.find("select[name='doc-type']").val(),
                'doc_num': modal.find("input[name='doc-num']").val(),
                'phone': modal.find("input[name='phone']").val(),
                
                'comment': modal.find("textarea[name='comment']").val(),
                'total': modal.find("input[name='total-cost-info']").val(),
            };

    $.ajax({
            url: '/account/' + ajax_action,
            type: 'post',
            dataType: "json",
            data: data,
            success: function( data ) {                
                switch(parseInt(data.success))
                {
                    case 1: order_type = modal.find("select[name='order-type']").val();
                            switch(parseInt(order_type)) {
                                case 0: successMsg("Бронь оформлена! \\r\\n\\r\\nНомер билета для выкупа: "+data.id_ticket); 
                                case 1: modal.closest("div.modal").modal("hide");
                                        if($autoprint) showPrint(data);
                                        else {                                            
                                            successMsg("Успешно!");                                            
                                        }
                                        return;
                            }
                            modal.closest("div.modal").modal("hide");
                            break;
                    case 0: alertMsg(data.msg?data.msg:"Билет не оформлен! Ошибка данных!");                                       
                            break;
                    default: alertMsg("Билет не оформлен! Ошибка запроса!");
                }
                footer.find("button.btn-submit").prop("disabled", null);                        
            }, // success
        });//$.ajax

    footer.find("button.btn-submit").prop("disabled", "disabled");
    return false;
};

showPrint = function(data)
{    
console.log(data);                        
    if(!data) return;
    if(!data.turl) return;
    var w = window.open(data.turl,"_blank", "menubar=no,location=no,toolbar=no,directories=no,status=no,width=850,resizable=yes,scrollbars=yes");
    if(!w) return alert("Необходимо отключить блокировку всплывающих окон для " + window.location.host + " в настройках браузера!");
    w.focus();                        
};
   
S;