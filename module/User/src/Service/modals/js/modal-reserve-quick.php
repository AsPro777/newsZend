<?php
return <<<S
/* инициализация */
var modal = $("div.ticket-sell-content");
var footer = modal.closest("div.modal-content").find("div.modal-footer");
var placesReloadTimerId = 0;
modal.find("div.input-group").css({"width":"100%","margin-top":"2px"});

//modal.find( "input.cls-phone" ).mask("0-000-000-00-00", {placeholder: "7-910-123-45-67", selectOnFocus: false});
modal.find( "input.cls-phone" ).phoneMask();
modal.find( "input.cls-dt" ).mask("00.00.0000", {placeholder: "дд.мм.гггг", selectOnFocus: false});
modal.find( "input.cls-tm" ).mask("00:00", {placeholder: "чч.мм", selectOnFocus: false});
modal.find( "input.cls-place" ).mask("00", {placeholder: "0", selectOnFocus: false});

/* buttons on footer */

var submitter = footer.find("button.btn-submit")
    .text("Забронировать")
    .on("click", function(){
        submitTicket();
    });

var eventList = $._data(submitter[0], 'events');
var clicks = eventList.click;
var first = clicks.shift();
clicks.push(first);

/* загрузка первичных ИД */
$.ajax({
        url: '/account/sells',
        type: 'post',
        dataType: "json",
        data: {
            'id_reis': '{$id_reis}',
            'action': 'ticket-window-load'
        },
        success: function( data ) {
            if(data.success==1)
            {
                if(data.to_points)
                    modal.find("select[name='to-point']").empty().append($(data.to_points));
                if(data.tarifs)
                    modal.find("select[name='tarif']").empty().append($(data.tarifs));
                if(data.ticket_comment)
                    modal.find("textarea[name='comment']").text(data.ticket_comment);

                modal.find("div.raw-places").empty().html(data.bus?mkRawPlaces(data.bus.size):"Не назначен автобус!");
                modal.find("div.schema-places").empty().html(data.bus?mkSchemaPlaces(data.bus.config):"Не назначен автобус!");
                initSchemaPlaces();

                if(data.from_points)
                    setTimeout(function(){
                        modal.find("select[name='from-point']").empty().append($(data.from_points)).trigger("change");
                    },100);
            }
            if(data.msg)
                alertMsg(data.msg);
        }, /* success */
    });/* $.ajax */


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

modal.find("select[name='from-point']").on("change", function(){

    modal.find("select.cls-to-point").empty();
    initRawPlaces();
    initSchemaPlaces();
    stopPlaceReloadTimer();
    $.ajax({
//        url: '/account/' + ajax_action,   // by Mike when sell reserved place
            url: '/account/sells',
            type: 'post',
            dataType: "json",
            data: $.extend(placeReisData(), {
                    "action": "ticket-to-points-load"
                }),

            success: function( data ) {
                if(data.success==1)
                {
                    if(data.to_points)
                        modal.find("select[name='to-point']").append($(data.to_points));
                        modal.find("select[name='to-point'] option:last").attr("selected","selected");
                        reloadPlaces(placeReisData());
                }
                else
                alertMsg(data.msg?data.msg:"Нет пунктов прибытия для данного пункта отправления!");
                calculateCosts();
            } // success
        });//$.ajax
});

modal.find("select[name='to-point']").on("change", function(){

    initRawPlaces();
    initSchemaPlaces();
    reloadPlaces(placeReisData());
    startPlaceReloadTimer();
    calculateCosts();
});

modal.find("select[name='tarif'], select[name='cargo']").on("change", function(){
    calculateCosts();
});


/* Система поиска пассажира по телефону или документу */

modal.find("input.cls-search-by").autocomplete({
    source: function( request, response ) {
      $.ajax({
              url: '/account/sells',
              type: 'post',
              dataType: "json",
              data: {
                action: 'search-by',
                query: request.term,
              },
              success: function( data ) {
                response( data );
              },
          });//$.ajax
    },
    minLength: 4,
    select: function( event, ui ) {
          {
                var pax = JSON.parse(ui.item.pax);
                modal.find("input.cls-phone").val(pax.phone);
                modal.find("select.cls-sex").val(pax.sex);
                modal.find("input.cls-f").val(pax.f);
                modal.find("input.cls-i").val(pax.i);
                modal.find("input.cls-o").val(pax.o);
                modal.find("input.cls-dr").val(pax.dr);
                modal.find("select.cls-grazhd").val(pax.grazhd).trigger("change");
                modal.find("select.cls-doc-type").val(pax.doc_type).trigger("change");
                modal.find("input.cls-doc-num").val(pax.doc_num);
              return false;
          }
      }
  })
  .autocomplete( "instance" )._renderItem = function( ul, item ) {
      $(ul).css({"max-height":$(window).height()+"px", "padding":0});
      var li = $( "<li>" )
              .css({"padding":"2px"})
              .on("click", function(){
                  $("input.user-phone-control").val('');
              });
      var div = $( "<div />" )
              .html(item.label)
              .addClass( (item.status==1) ? "bg-teal-300" : "bg-orange-300"  )
              .css({"padding":"7px","border-radius":"4px"});

      return li.append( div ).appendTo( ul );
  };

/* functions */

var mkRawPlaces = function(total)
{
    result = $("<div></div>");
    for(var i=1; i<=total; i++)
    {
        place = $("<div></div>")
                .attr("name", i)
                .text(i)
                .addClass("raw-place blocked");
        result.append(place);
    }
    return $(result.html());
};

var initRawPlaces = function()
{
    modal.find("div.raw-places div.raw-place")
        .prop("class", "raw-place blocked")
        .unbind("click");
};

var updateRawPlaces = function(data)
{
    var places = data.places;
    var tickets = data.tickets;
    if(!places) return;

    var container = modal.find("div.raw-places");
    $.each(places, function(key, val){
        var p = $(container).find("div.raw-place[name='"+key+"']");
        p.prop("class", "raw-place")
         .attr("id", tickets[key]?tickets[key]:"")
         .unbind("click")
         .removeClass("vacant reserved blocked my-blocked");

        switch(val)
        {
            case -1:
                    p.addClass("my-blocked")
                    .on("click", function(){
                        $(this).removeClass("my-blocked");
                        putReservPlace(p.attr("name"));
                    });
                    break;
            case "": p.addClass("vacant")
                    .on("click", function(){
                        $(this).removeClass("vacant");
                        modal.find("input.cls-place").val(p.attr("name"));
                        putReservPlace(p.attr("name"));
                    });
                    break;
            case 0: p.addClass("reserved")
                    .on("click", function(){
                        //self._showPrint(p.attr("id"), false);
                    });
                    break;
            case 1: p.addClass("sold")
                    .on("click", function(){
                        //self._showPrint(p.attr("id"), false);
                    });
                    break;
            default: p.addClass("blocked"); break;
        }
    });
};

var mkSchemaPlaces = function(config)
{
    var busView = $("<div id='busview'></div>");
    busView.mybusview();
    busView.mybusview("setJson", JSON.stringify(config));
    return busView;
};

var initSchemaPlaces = function()
{
    $($("div.schema-places div.bus_place.place_active")[0]).removeClass("place_active"); /* водитель */

    $.each($("div.schema-places div.bus_place.clickable.place_active"), function(i, place){
        $(place)
        .attr("data-name", $(place).text())
        .addClass("raw-place blocked")
        .unbind("click");
    });
};

var updateSchemaPlaces = function(data)
{
    var places = data.places;
    var tickets = data.tickets;
    if(!places) return;

    var container = modal.find("div.schema-places");
    $.each(places, function(key, val){
        var p = $(container).find("div.raw-place[data-name='"+key+"']");
        p.attr("id", tickets[key]?tickets[key]:"")
         .unbind("click")
         .removeClass("vacant reserved blocked my-blocked sold");
        switch(val)
        {
            case -1:
                    p.addClass("my-blocked")
                    .on("click", function(){
                        $(this).removeClass("my-blocked");
                        putReservPlace(p.attr("data-name"));
                    });
                    break;
            case "": p.addClass("vacant")
                    .on("click", function(){
                        $(this).removeClass("vacant");
                        modal.find("input.cls-place").val(p.attr("data-name"));
                        putReservPlace(p.attr("data-name"));
                    });
                    break;
            case 0: p.addClass("reserved")
                    .on("click", function(){
                        //self._showPrint(p.attr("id"), false);
                    });
                    break;
            case 1: p.addClass("sold")
                    .on("click", function(){
                        //self._showPrint(p.attr("id"), false);
                    });
                    break;
            default: p.addClass("blocked"); break;
        }
    });
};

var calculateCosts = function()
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

    setTimeout(function(){
        $.ajax({
//              url: '/account/' + ajax_action,   // by Mike when sell reserved place
                url: '/account/sells',
                type: 'post',
                dataType: "json",
                data: $.extend(placeReisData(), {
                    "cargo_num":modal.find("select.cls-cargo").val(),
                    "action": 'ticket-calculate-costs'
                }),
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
    }, 600);
};

var placeReisData = function()
{
    var data = {
                'id_reis': '{$id_reis}',
                'id_from_point': modal.find("select[name='from-point']").val(),
                'id_to_point': modal.find("select[name='to-point']").val(),
                'id_tarif': modal.find("select[name='tarif']").val(),
                'size': modal.find("div.raw-places div.raw-place").length,
            };
    return data;
};

var reloadPlaces = function(data)
{
    data.action = "ticket-timer-to-points-load";
    $.ajax({
//              url: '/account/' + ajax_action,   // by Mike when sell reserved place
            url: '/account/sells',
            type: 'post',
            dataType: "json",
            data: data,
            success: function( data ) {
                if(data.success==1)
                {
                    if(data.places) {
                        updateRawPlaces(data);
                        updateSchemaPlaces(data);
                    }
                    else {
                        initRawPlaces();
                        initSchemaPlaces();
                    }
                }
                else {
                    initRawPlaces();
                    initSchemaPlaces();
                }
            }, // success
        });//$.ajax
};

var stopPlaceReloadTimer = function()
{
    clearInterval(placesReloadTimerId);
};

var startPlaceReloadTimer = function()
{
    stopPlaceReloadTimer();
    placesReloadTimerId = setInterval(function(){
        $.ajax({
                global: false,
//              url: '/account/' + ajax_action,   // by Mike when sell reserved place
                url: '/account/sells',
                type: 'post',
                dataType: "json",
                data: $.extend(placeReisData(), {
                            "action": "ticket-timer-to-points-load"
                    }),
                complete: function( a, b ) {
                },
                success: function( data ) {
                    if(data.success==1)
                    {
                        if(data.places) {
                            updateRawPlaces(data);
                            updateSchemaPlaces(data);
                        }
                        else {
                            initRawPlaces();
                            initSchemaPlaces();
                        }
                    }
                    else {
                        initRawPlaces();
                        initSchemaPlaces();
                    }
                }, // success
            });//$.ajax
    }, 15000);
};

var putReservPlace = function(id_place)
{
    var data = $.extend({}, placeReisData(), {"id_reserv":id_place});
    reloadPlaces(data);
};

var checkSubmittedValues = function()
{
    var lastPlace = parseInt(modal.find("div.raw-places div.raw-place").last().text());
    var place = modal.find("input.cls-place");
    var f = modal.find("input.cls-f");
    var i = modal.find("input.cls-i");
    var o = modal.find("input.cls-o");
    var phone = modal.find("input.cls-phone");
    var dt = modal.find("input.cls-dt");
    var tm = modal.find("input.cls-tm");

    if(!place.val()) return "Необходимо заполнить: Номер места!";
    if(parseInt(place.val())<1) return "Номер места должен быть больше 0!";
    if(parseInt(place.val())>lastPlace) return "Номер места должен быть меньше "+lastPlace+"!";

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

    if(!phone.val()) return "Необходимо заполнить: Телефон!";

    if(!isValidMasker("dt") || isInvalidDate(dt.val())) return "Необходимо правильно заполнить: Дата выкупа!";
    if(!isValidMasker("tm") || isInvalidTime(tm.val())) return "Необходимо правильно заполнить: Время выкупа!";

    if(isInvalidDateTime(dt.val(), tm.val())) return "Необходимо правильно заполнить: Дата и время выкупа! <br>Они должны быть в будущем, но не позднее $date_start!";

    return "";
};

var isValidMasker = function(name){
    var item = $("[name='"+name+"']");

    var value = item.val();
    var mask = $.trim(item.attr("placeholder"));
    var hasMask = mask.split("_").join("") != "";

    if(value=="") return false;
    if( !hasMask ) return true;
    if(value.length!=mask.length) return false;

    return true;
}

var isInvalidDateTime = function(date, time)
{
  var _now = new Date();
  var _check = new Date(date.split('.').reverse().join('-')+' '+time);
  var _max = new Date('$date_start_eng');
  if(_check<_now) return true;
  if(_check>_max) return true;
  return false;
}

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

function isInvalidTime(time)
{
    time = time.split(':');
    var h = parseInt(time[0]), m = parseInt(time[1]);
    return !( (h>=0) && (h<24) && (m>=0) && (m<60) );
}

var submitTicket = function(){

    var checked = checkSubmittedValues();
    if(!!checked) {
          alertMsg(checked);
          return false;
    }

    var ajaxSubmit = function(request_data)
    {
        $.ajax({
                url: '/account/sells',
                type: 'post',
                dataType: "json",
                data: request_data,
                success: function( data ) {
                    if(data.success)
                    {
                        successMsg("Бронь оформлена! \\r\\n\\r\\nНомер билета для выкупа: "+data.id_ticket);
                        modal.closest("div.modal").modal("hide");
                    }
                    else
                    {
                        alertMsg(data.msg?data.msg:"Билет не оформлен! Ошибка данных!");
                        footer.find("button.btn-submit").prop("disabled", null);
                    }
                }, // success
            });//$.ajax
    }

    var request_data = {
                'action': 'ticket-reserve-quick',
                'id_reis': '{$id_reis}',
                'id_from_point': modal.find("select[name='from-point']").val(),
                'id_to_point': modal.find("select[name='to-point']").val(),
                'id_tarif': modal.find("select[name='tarif']").val(),
                'order_type': 0,
                'cargo_num': modal.find("select[name='cargo']").val(),
                'place': modal.find("input[name='place']").val(),
                'dt_drop': modal.find("input.cls-dt").val(),
                'tm_drop': modal.find("input.cls-tm").val(),

                'f': modal.find("input[name='f']").val(),
                'i': modal.find("input[name='i']").val(),
                'o': modal.find("input[name='o']").val(),
                'phone': modal.find("input[name='phone']").val(),
                'comment': modal.find("textarea[name='comment']").val(),
            };
    $.ajax({
            url: '/account/sells',
            type: 'post',
            dataType: "json",
            data: $.extend({}, request_data, {'action':'check-double'}),
            success: function( data ) {
                if(data.success)
                    ajaxSubmit(request_data);
                else
                {
                    confirmMsg((data.msg?data.msg:"Ошибка проверки дублирования данных о пассажире!") + "<p>Продолжить?",
                    /*yes*/
                    function(){
                        ajaxSubmit(request_data);
                    },
                    /*no*/
                    function(){
                        footer.find("button.btn-submit").prop("disabled", null);
                        return false;
                    });
                }
            }, // success
        });//$.ajax

    footer.find("button.btn-submit").prop("disabled", "disabled");
    return false;
};

S;
