(function($) {

    $.widget("ui.ticketEdit", {

        options: {
            config: null,
        },
        placesReloadTimerId: 0,
        id_reis: false,
        tmpHandlerFromPointsLoadedCallback: false,
        
        _create: function()
        {
            var self = this, el = self.element, o = self.options;
            el.append(self._mkTicketsModalBody());
            self._setCallBacksTicketModalBody();
        },

        _setCallBacksTicketModalBody: function(config)
        {
            var self = this, body = self.tbody, content = $("#page-content");

            content.find("input[name='date']").on("change", function(){
                $("#panel-2").hide();
                content.find("select[name='id-reis'], select[name='marsh'], select[name='time'], select[name='from-point'], select[name='to-point'], select[name='tarif'], input[name='place']").empty().val("");
                self._stopPlaceReloadTimer();
                self._getReisesList();
            });
            
            content.find("select[name='marsh']").on("change", function(){
                $("#panel-2").hide();
                content.find("select[name='id-reis'], select[name='time'], select[name='from-point'], select[name='to-point'], select[name='tarif'], input[name='place']").empty().val("");
                self._stopPlaceReloadTimer();
                self._getTimesList();
            });

            content.find("select[name='time']").on("change", function(){
                $("#panel-2").hide();
                content.find("select[name='id-reis'], select[name='from-point'], select[name='to-point'], select[name='tarif'], input[name='place']").empty().val("");
                self._stopPlaceReloadTimer();
                self._getIdReisList();
            });

            content.find("select[name='id-reis']").on("change", function(){
                $("#panel-2").hide();
                content.find("select[name='from-point'], select[name='to-point'], select[name='tarif'], input[name='place']").empty().val("");
                self._stopPlaceReloadTimer();
                self._getFromPointsList();
            });

            content.find("span.btn-phone").on("click", function(){
                var ph = content.find("input.cls-phone").val();

                if(!ph) return alertMsg("Введите номер!");
                $.ajax({
                        url: '/account/reises/',
                        type: 'post',
                        dataType: "json",
                        data: {
                            phone: content.find("input.cls-phone").val(),
                            action: 'pax-data-by-phone'
                        },
                        success: function( data ) 
                        {
                            if(data.success==1)
                            {
                                var pax = JSON.parse(data.pax);
                                content.find("select.cls-sex").val(pax.sex);
                                content.find("input.cls-f").val(pax.f);
                                content.find("input.cls-i").val(pax.i);
                                content.find("input.cls-o").val(pax.o);
                                content.find("input.cls-dr").val(pax.dr);
                                content.find("select.cls-grazhd").val(pax.grazhd).trigger("change");
                                content.find("select.cls-doc-type").val(pax.doc_type).trigger("change");
                                content.find("input.cls-doc-num").val(pax.doc_num);
                            }
                            else
                                alertMsg(data.msg?data.msg:"Нет данных по указанному номеру!");
                        }, // success
                    });//$.ajax
            });

            content.find("select[name='from-point']").on("change", function(){

                content.find("select.remotable.cls-to-point").empty();
                self._initRawPlaces();
                self._initSchemaPlaces();
                self._stopPlaceReloadTimer();
                self._calculateCosts();
                $.ajax({
                        url: '/account/reises/',
                        type: 'post',
                        dataType: "json",
                        data: $.extend(self._placeReisData(), {
                                    "action": "ticket-to-points-load"
                            }),

                        success: function( data ) {
                            if(data.success==1)
                            {
                                if(data.to_points)
                                    content.find("select[name='to-point']").append($(data.to_points));
                                self._reloadPlaces(self._placeReisData());
                                if(self.tmpHandlerFromPointsLoadedCallback) 
                                    self.tmpHandlerFromPointsLoadedCallback();
                            }
                            else
                            alertMsg(data.msg?data.msg:"Нет пунктов прибытия для данного пункта отправления!");
                        }, // success
                    });//$.ajax
            });

            content.find("select[name='to-point']").on("change", function(){
                $("#panel-2").show();
                self._initRawPlaces();
                self._initSchemaPlaces();
                self._reloadPlaces(self._placeReisData());
                self._startPlaceReloadTimer();
                self._calculateCosts();
            });

            content.find("select[name='tarif'], select[name='cargo']").on("change", function(){
                self._calculateCosts();
            });

            content.find("#submitter").on("click", function(){
                self._submitTicket();
            });
        },

        _mkTicketsModalBody: function()
        {
            var divRow = $('<div class="row"></div>');
            var divCol_1 = $('<div class="col-xs-6" id="panel-1"></div>');
            var divCol_2 = $('<div class="col-xs-6" id="panel-2"></div>');

            var panel_0 = $('<div class="panel"></div>');
            var panelHead_0 = $('<div class="panel-heading">Дата, время, рейс</div>');
            var panelBody_0 = $('<div class="panel-body"></div>');
            panel_0.append(panelHead_0).append(panelBody_0);

            var date = $('<div><span class="my-label">Дата:</span> <input class="form-control cls-date" value="" name="date"></div>');
            var marsh = $('<div><span class="my-label">Маршрут:</span> <select class="form-control cls-marsh remotable" name="marsh"></div>');
            var time = $('<div><span class="my-label">Время:</span> <select class="form-control cls-time remotable" name="time"></div>');
            var id_reis = $('<div><span class="my-label">№ рейса:</span> <select class="form-control cls-id-reis remotable" name="id-reis"></div>');
                        
            date.find( "input.cls-date" ).mask("00.00.0000", {placeholder: "дд.мм.гггг", selectOnFocus: false});
            panelBody_0.append(date).append(marsh).append(time).append(id_reis);
            
            var panel = $('<div class="panel"></div>');
            var panelHead = $('<div class="panel-heading">Пассажир</div>');
            var panelBody = $('<div class="panel-body"></div>');
            panel.append(panelHead).append(panelBody);

            var phone = $('<div><span class="my-label">Телефон</span> <input class="form-control cls-phone" value="" maxlength="10" name="phone"><div class="input-group-btn"><button type=button class="btn bg-teal btn-phone"><i class="icon-search4"></i></button></div></div>');
            phone.find( "input.cls-phone" ).mask("0-000-000-00-00", {placeholder: "7-910-123-45-67", selectOnFocus: false});

            panelBody.append(phone);
            var f = $('<div><span class="my-label">Фамилия</span> <input class="cls-f" value="" name="f" maxlength="100"></div>');
            panelBody.append(f);
            var i = $('<div><span class="my-label">Имя</span> <input class="cls-i" value="" name="i" maxlength="100"></div>');
            panelBody.append(i);
            var o = $('<div><span class="my-label">Отчество</span> <input class="cls-o" value="" name="o" maxlength="100"></div>');
            panelBody.append(o);
            var sex = $('<div><span class="my-label">Пол</span> <select class="cls-sex" value="" name="sex"><option value=0>Женский</option><option value=1>Мужской</option></select></div>');
            panelBody.append(sex);
            var dr = $('<div><span class="my-label">Дата рождения</span> <input class="cls-dr" value="" name="dr"></div>');
            dr.find( "input.cls-dr" ).mask("00.00.0000", {placeholder: "дд.мм.гггг", selectOnFocus: false});
            panelBody.append(dr);
            var grazhd = $('<div><span class="my-label">Гражданство</span> <select class="cls-grazhd remotable" value="" name="grazhd"></select></div>');
            panelBody.append(grazhd);
            var doc_type = $('<div><span class="my-label">Удостоверяющий документ</span> <select class="cls-doc-type remotable" value="" name="doc-type"></select></div>');
            panelBody.append(doc_type);
            var doc_num = $('<div><span class="my-label">Серия и номер</span> <input class="cls-doc-num" value="" maxlength="25" name="doc-num"> <div id=doc-num-example class="alert alert-info"></div></div>');
            panelBody.append(doc_num);
            var comment = $('<div><span class="my-label">Комментарий к билету</span> <textarea class="cls-comment form-control my-control" name="comment" placeholder="При необходимости введите сюда комментарии к билету" style="min-height: 65px;" /></div>');
            panelBody.append(comment);

            var panel_1 = $('<div class="panel"></div>');
            var panelHead_1 = $('<div class="panel-heading">Билет</div>');
            var panelBody_1 = $('<div class="panel-body"></div>');
            panel_1.append(panelHead_1).append(panelBody_1);

            var from_point = $('<div><span class="my-label">Пункт отправления</span> <select class="cls-from-point remotable" value="" name="from-point"></select></div>');
            panelBody_1.append(from_point);
            var to_point = $('<div><span class="my-label">Пункт прибытия</span> <select class="cls-to-point remotable" name="to-point"></select></div>');
            panelBody_1.append(to_point);
            var tarif = $('<div><span class="my-label">Тариф</span> <select class="cls-tarif remotable" value="" name="tarif"></select></div>');
            panelBody_1.append(tarif);
            var cargo = $('<div><span class="my-label">Мест багажа</span> <select class="cls-cargo" value="" maxlength="1" name="cargo"></select></div>');
            for(var i=0; i<11; i++)
                cargo.find("select").append($("<option value="+i+">"+i+"</option>"));
            panelBody_1.append(cargo);
            var place = $('<div><span class="my-label">Номер места</span> <input class="cls-place" value="" maxlength="2" name="place"></div>');
            place.find( "input.cls-place" ).mask("00", {placeholder: "0", selectOnFocus: true});
            panelBody_1.append(place);
$.each({panelBody, panelBody_0, panelBody_1}, function(i, panel){
    $.each(panel.find("div"), function(i, div){
        div = $(div);
        div.addClass("form-group");
        var label = div.find("span").first().removeClass().addClass("control-label col-xs-4");    
        var others = label.siblings();
        others.detach();
        label.detach();
        var d1 = $("<div />").addClass("col-xs-8");
        var d2 = $("<div />").addClass("input-group").css("width", "100%");
        label.appendTo(div);
        others.appendTo(d2);
        div.append(d1.append(d2));
        div.find(".my-control").removeClass("my-control");
    });
});
            divCol_1.append(panel_0).append(panel_1).append(panel);

            var panel_2 = $('<div class="panel"></div>');
            var panelHead_2 = $('<div class="panel-heading">Места</div>');
            var panelBody_2 = $('<div class="panel-body"><div class="col-xs-4 raw-places">raw-places</div><div class="col-xs-8 schema-places">schema-places</div></div>');

            panel_2.append(panelHead_2).append(panelBody_2);

            var panel_3 = $('<div class="panel border-teal"></div>');
            var panelHead_3 = $('<div class="panel-heading  bg-teal">Перерасчет стоимости</div>');
            var panelBody_3 = $('<div class="panel-body"></div>');

            var header_cost = $('<div class=row><div class=col-xs-3></div><div class=col-xs-3>Новая</div><div class=col-xs-3>Прежняя</div><div class=col-xs-3>Разница</div></div>');
            panelBody_3.append(header_cost);            
            var ticket_cost = $('<div class=row><div class=col-xs-3><span class="my-label">Билет:</span></div><div class=col-xs-3><input class="cls-ticket-cost-info" value="" readonly="true" name="ticket-cost-info"></div><div class=col-xs-3><input class="cls-ticket-cost-info-old" value="" readonly="true" name="ticket-cost-info-old"></div><div class=col-xs-3><input class="cls-ticket-cost-info-calculated" value="0" readonly="true" name="ticket-cost-info-calculated"></div></div>');
            panelBody_3.append(ticket_cost);            
            var cargo_cost = $('<div class=row><div class=col-xs-3><span class="my-label">Багаж:</span></div><div class=col-xs-3><input class="cls-cargo-cost-info" value="" readonly="true" name="cargo-cost-info"></div><div class=col-xs-3><input class="cls-cargo-cost-info-old" value="" readonly="true" name="cargo-cost-info-old"></div><div class=col-xs-3><input class="cls-cargo-cost-info-calculated" value="0" readonly="true" name="cargo-cost-info-calculated"></div></div>');
            panelBody_3.append(cargo_cost);
            var comission_cost = $('<div class=row><div class=col-xs-3><span class="my-label">Комиссия:</span></div><div class=col-xs-3><input class="cls-comission-cost-info" value="" readonly="true" name="comission-cost-info"></div><div class=col-xs-3><input class="cls-comission-cost-info-old" value="" readonly="true" name="comission-cost-info-old"></div><div class=col-xs-3><input class="cls-comission-cost-info-calculated" value="0" readonly="true" name="comission-cost-info-calculated"></div></div>');
            panelBody_3.append(comission_cost);
            var total_cost = $('<div class=row><div class=col-xs-3><span class="my-label">Всего:</span></div><div class=col-xs-3><input class="cls-total-cost-info" value="" readonly="true" name="total-cost-info"></div><div class=col-xs-3><input class="cls-total-cost-info-old" value="" readonly="true" name="total-cost-info-old"></div><div class=col-xs-3><input class="cls-total-cost-info-calculated" value="0" readonly="true" name="total-cost-info-calculated"></div></div>');
            panelBody_3.append(total_cost);
            var result_cost = $('<div class=row><div class=col-xs-3><span class="my-label">Итого:</span></div><div class=col-xs-6><input class="cls-result-label" value="" readonly="true" name="result-label"></div><div class=col-xs-3><input class="cls-result-calculated" value="0" readonly="true" name="result-calculated"></div></div>');
            panelBody_3.append(result_cost);

            panel_3.append(panelHead_3).append(panelBody_3);

            divCol_2.append(panel_2).append(panel_3);

            divRow.append(divCol_1);
            divCol_1.find("input, select").addClass("form-control my-control");
            divRow.append(divCol_2);
            divCol_2.find("input").addClass("form-control");
            divCol_2.find("div.row > div").css("padding", "2px 5px");

            grazhd.find("select").typeheadOnSelect({});
            doc_type.find("select").typeheadOnSelect({onSelectFn:function(){
                    var option = JSON.parse(doc_type.find("select option:selected").first().attr("data-base"));
                    $("#doc-num-example").html("<b>Подсказка: </b>"+(option.example?option.example:"Формат \"серия-номер\" смотри в документе."));
                    if(option.mask && (option.mask!=""))
                        doc_num.find("input.cls-doc-num").first().mask(option.mask, {placeholder: option.mask});
                    else
                        doc_num.find("input.cls-doc-num").first().mask('_______________', {placeholder: " "});
            }});

            var panel_4 = $('<div class="row">'
                            + '<div class="col-xs-12">'
                            + '<input id="submitter" class="btn btn-primary" value="Сохранить" type="button">'
                            + '</div>'
                            + '</div>');
            divCol_2.append(panel_4);                        
            
            return divRow;
        },

        _mkSchemaPlaces: function(config)
        {
            var self = this;
            var busView = $("<div id='busview'></div>");
            busView.mybusview();
            busView.mybusview("setJson", JSON.stringify(config));
            return busView;
        },

        _initSchemaPlaces: function()
        {
            $($("#page-content div.schema-places div.bus_place.place_active")[0]).removeClass("place_active");

            $.each($("#page-content div.schema-places div.bus_place.clickable.place_active"), function(i, place){
                $(place)
                .attr("data-name", $(place).text())
                .addClass("raw-place blocked")
                //.removeClass("place_active")
                .unbind("click");
            });
        },

        _updateSchemaPlaces: function(data)
        {
            var self = this;
            var places = data.places;
            var tickets = data.tickets;
            if(!places)
                return;
            var container = $("#page-content div.schema-places");
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
                                self._putReservPlace(p.attr("data-name"));
                            });
                            break;
                    case "":
                            
                            p.addClass("vacant")
                            .on("click", function(){
                                $(this).removeClass("vacant");
                                $("#page-content input.cls-place").val(p.attr("data-name"));
                                self._putReservPlace(p.attr("data-name"));
                            });
                            break;
                    case 0: p.addClass("reserved")
                            .on("click", function(){                                
                            });
                            break;
                    case 1: p.addClass("sold")
                            .on("click", function(){                                
                            });
                            break;
                    default: p.addClass("blocked"); break;
                }
            });
        },

        _mkRawPlaces: function(total)
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
        },

        _updateRawPlaces: function(data)
        {
            var self = this;
            var places = data.places;
            var tickets = data.tickets;
            if(!places)
                return;
            var container = $("#page-content div.raw-places");
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
                                self._putReservPlace(p.attr("name"));
                            });
                            break;
                    case "": p.addClass("vacant")
                            .on("click", function(){
                                $(this).removeClass("vacant");
                                $("#page-content input.cls-place").val(p.attr("name"));
                                self._putReservPlace(p.attr("name"));
                            });
                            break;
                    case 0: p.addClass("reserved")
                            .on("click", function(){                                
                            });
                            break;
                    case 1: p.addClass("sold")
                            .on("click", function(){                                
                            });
                            break;
                    default: p.addClass("blocked"); break;
                }
            });
        },

        _initRawPlaces: function()
        {
            var self = this;
            $("#page-content div.raw-places div.raw-place")            
                .prop("class", "raw-place blocked")
                .unbind("click");
        },

        _putReservPlace: function(id_place)
        {
            var data = $.extend({}, this._placeReisData(), {"id_reserv":id_place});
            this._reloadPlaces(data);
        },

        _placeReisData: function()
        {
            var self = this;
            var modal_body = $("#page-content");

            var data = {
                        'id_reis': self.id_reis,
                        'id_from_point': $(modal_body.find("select[name='from-point']")).val(),
                        'id_to_point': $(modal_body.find("select[name='to-point']")).val(),
                        'id_tarif': $(modal_body.find("select[name='tarif']")).val(),
                        'size': modal_body.find("div.raw-places div.raw-place").length,
                    };
            return data;
        },

        _reloadPlaces: function(data)
        {
            var self = this;
            data.action = "ticket-timer-to-points-load";
            $.ajax({                    
                    //url: '/account/reises/',
                    url: '/account/sells/',
                    type: 'post',
                    dataType: "json",
                    data: data,
                    success: function( data ) {
                        if(data.success==1)
                        {
                            if(data.places) {
                                self._updateRawPlaces(data);
                                self._updateSchemaPlaces(data);
                            }
                            else {
                                self._initRawPlaces();
                                self._initSchemaPlaces();
                            }
                        }
                        else {
                            self._initRawPlaces();
                            self._initSchemaPlaces();
                        }
                    }, // success
                });//$.ajax
        },

        _startPlaceReloadTimer: function()
        {
            var self = this;
            self._stopPlaceReloadTimer();
            self.placesReloadTimerId = setInterval(function(){
                $.ajax({
                        global: false,
                        url: '/account/reises/',
                        type: 'post',
                        dataType: "json",
                        data: $.extend(self._placeReisData(), {
                                    "action": "ticket-timer-to-points-load"
                            }),
                        success: function( data ) {
                            if(data.success==1)
                        {
                            if(data.places) {
                                self._updateRawPlaces(data);
                                self._updateSchemaPlaces(data);
                            }
                            else {
                                self._initRawPlaces();
                                self._initSchemaPlaces();
                            }
                        }
                        else {
                            self._initRawPlaces();
                            self._initSchemaPlaces();
                        }
                        }, // success
                    });//$.ajax
            }, 15000);
        },

        _stopPlaceReloadTimer: function()
        {
            clearInterval(this.placesReloadTimerId);
        },

        _prsInt: function(val)
        {
            val = parseInt(val);
            if(isNaN(val)) val = 0;
            return val;
        },
        
        _calculateCosts: function()
        {
            var self = this;
            if(self.tmpHandlerFromPointsLoadedCallback) return;

            var modal_body = $("#page-content");
            
            var ticket_cost_info = modal_body.find("input.cls-ticket-cost-info");
            var cargo_cost_info = modal_body.find("input.cls-cargo-cost-info");
            var comission_cost_info = modal_body.find("input.cls-comission-cost-info");
            var total_cost_info = modal_body.find("input.cls-total-cost-info");

            var ticket_cost_info_old = modal_body.find("input.cls-ticket-cost-info-old");
            var cargo_cost_info_old = modal_body.find("input.cls-cargo-cost-info-old");
            var comission_cost_info_old = modal_body.find("input.cls-comission-cost-info-old");
            var total_cost_info_old = modal_body.find("input.cls-total-cost-info-old");

            var ticket_cost_info_calculated = modal_body.find("input.cls-ticket-cost-info-calculated");
            var cargo_cost_info_calculated = modal_body.find("input.cls-cargo-cost-info-calculated");
            var comission_cost_info_calculated = modal_body.find("input.cls-comission-cost-info-calculated");
            var total_cost_info_calculated = modal_body.find("input.cls-total-cost-info-calculated");
            var result_calculated = modal_body.find("input.cls-result-calculated");
            var result_label = modal_body.find("input.cls-result-label");
            
            ticket_cost_info.val("");
            cargo_cost_info.val("");
            comission_cost_info.val("");
            total_cost_info.val("");
            
            $.ajax({
                    url: '/account/reises',
                    type: 'post',
                    dataType: "json",
                    data: $.extend(self._placeReisData(), {
                        "cargo_num":modal_body.find("select.cls-cargo").val(),
                        "action": 'ticket-calculate-costs'
                    }),
                    success: function( data ) {
                        if(data.success==1)
                        {
                            var cs = data.comission_stack;
                            
                            ticket_cost_info.val(data.ticket_cost_info);
                            cargo_cost_info.val(data.cargo_cost_info);
                            
                            ticket_cost_info_calculated.val(self._prsInt(ticket_cost_info.val()) - self._prsInt(ticket_cost_info_old.val()) + ' руб.');
                            cargo_cost_info_calculated.val(self._prsInt(cargo_cost_info.val()) - self._prsInt(cargo_cost_info_old.val()) + ' руб.');

                            comission_cost_info.val( self._prsInt(data.comission_cost_info) + ' руб.');
                            comission_cost_info_calculated.val(self._prsInt(comission_cost_info.val()) - self._prsInt(comission_cost_info_old.val()) + ' руб.');

                            total_cost_info.val((self._prsInt(ticket_cost_info.val()) + self._prsInt(cargo_cost_info.val()) + self._prsInt(comission_cost_info.val()))  + ' руб.' ) ;
                            
                            var total = self._prsInt(ticket_cost_info_calculated.val()) + self._prsInt(cargo_cost_info_calculated.val());
                            if(parseInt(comission_cost_info_calculated.val())>0) total += parseInt(comission_cost_info_calculated.val());
                    
                            total_cost_info_calculated.val(total + ' руб.');                            
                            
                            result_calculated.val(self._prsInt(total_cost_info_calculated.val()) + ' руб.');
                            if(self._prsInt(result_calculated.val())>0)
                                result_label.val("Доплата:");
                            else if(self._prsInt(result_calculated.val())<0)
                                result_label.val("Возврат:");
                            else
                                result_label.val("Цена совпадает");

                        }
                    }, // success
                });//$.ajax
        },

        _isValidMasker: function(item)
        {
            var value = item.val();
            var mask = $.trim(item.attr("placeholder"));
            var hasMask = mask.split("_").join("") != "";

            if(value=="") return false; 
            if( !hasMask ) return true;                
            if(value.length!=mask.length) return false;

            return true;
        },

        _checkSubmittedValues: function()
        {
            var self = this;
            var modal_body = $("#page-content");
            var lastPlace = parseInt(modal_body.find("div.raw-places div.raw-place").last().text());
            var place = modal_body.find("input.cls-place");
            var f = modal_body.find("input.cls-f");
            var i = modal_body.find("input.cls-i");
            var o = modal_body.find("input.cls-o");
            var dr = modal_body.find("input.cls-dr");
            var doc_num = modal_body.find("input.cls-doc-num");
            var phone = modal_body.find("input.cls-phone");

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
            
//            if(!dr.val()) return "Необходимо заполнить: Дата рождения!";
//            if(!doc_num.val()) return "Необходимо заполнить: Серия и номер!";
//            if(!phone.val()) return "Необходимо заполнить: Телефон!";

            if(!self._isValidMasker(modal_body.find("input.cls-dr"))) return "Необходимо заполнить: Дата рождения!";
            if(!self._isValidMasker(modal_body.find("input.cls-doc-num"))) return "Необходимо заполнить: Серия и номер!";
            if(!self._isValidMasker(modal_body.find("input.cls-phone"))) return "Необходимо заполнить: Телефон!";

            return "";
        },
        
        _getSubmitData: function()
        {
            var modal_body = $("#page-content"), self = this;
            var data = {
                        'id_reis': self.id_reis,
                        'date': $(modal_body.find("input[name='date']")).val(),
                        'time': $(modal_body.find("select[name='time']")).val(),
                        'reis': $(modal_body.find("select[name='marsh']")).val(),
                        
                        'id_from_point': $(modal_body.find("select[name='from-point']")).val(),
                        'id_to_point': $(modal_body.find("select[name='to-point']")).val(),
                        'id_tarif': $(modal_body.find("select[name='tarif']")).val(),
                        'cargo_num': $(modal_body.find("select[name='cargo']")).val(),
                        'place': $(modal_body.find("input[name='place']")).val(),

                        'f': $(modal_body.find("input[name='f']")).val(),
                        'i': $(modal_body.find("input[name='i']")).val(),
                        'o': $(modal_body.find("input[name='o']")).val(),
                        'sex': $(modal_body.find("select[name='sex']")).val(),
                        'dr': $(modal_body.find("input[name='dr']")).val(),
                        'grazhd': $(modal_body.find("select[name='grazhd']")).val(),
                        'doc_type': $(modal_body.find("select[name='doc-type']")).val(),
                        'doc_num': $(modal_body.find("input[name='doc-num']")).val(),
                        'phone': $(modal_body.find("input[name='phone']")).val(),
                        
                        'comment': $(modal_body.find("textarea[name='comment']")).val(),
                    };
            return data;
        },
        
        _submitTicket: function()
        {            
            var self = this, but = $("#submitter");
            var checked = self._checkSubmittedValues();
            if(!!checked) 
            {
                alertMsg(checked);
                return false;
            }

            $(but).prop("disabled", "disabled");                
            
            var data = self._getSubmitData();
            data.action = 'ticket-change';

            $.ajax({
                    url: window.location.href,
                    type: 'post',
                    dataType: "json",
                    data: data,
                    success: function( response ) {
                    if( (parseInt(response.success)==1) && (response.id_ticket>0) )
                    {
                        successMsg( "Печать билета...\r\n\r\nУбедитесь в готовности принтера и нажмите кнопку.\r\n\r\nВ случае ошибки при печати не закрывайте окно с билетом и для повторной попытки нажмите комбинацию клавиш 'Ctrl+P'.",
                                function(){
                                    self._showPrint(response.turl);
                                    setTimeout(function(){
                                        window.location.href = "/account/ticket-edit";
                                    }, 2000);
                                });
                    }
                    else
                    {
                        alertMsg("Билет не оформлен! " + (response.msg?("\r\n\r\n"+response.msg):"") );
                        $(but).prop("disabled", null);
                    }
                }, // success
            });//$.ajax
        },

        _showPrint: function(turl)
        {
            if(!turl)
            {
                alertMsg("Указан некорректный номер билета! Печать отменена.");
                return;
            }
            var w = window.open(turl,"_blank", "menubar=no,location=no,toolbar=no,directories=no,status=no,width=850,resizable=yes,scrollbars=yes");
            if(!w) return alertMsg("Необходимо отключить блокировку всплывающих окон для " + window.location.host + " в настройках браузера!");
            w.focus();            
        },

        loadReis: function(id_reis, ticket)
        {
//            console.log(ticket);
            var self = this;
            self.id_reis = id_reis;
            self.ticket = ticket;
            var content = $("#page-content");
            
            if(ticket.options && ticket.options.marsh)
            {
                var sel = content.find("select[name='marsh']");
                $.each(ticket.options.marsh, function(i, val){
                    $(sel).append($("<option value="+i+">"+val+"</option>"));
                });
            }
            
            if(ticket.options && ticket.options.times)
            {
                var sel = content.find("select[name='time']");
                $.each(ticket.options.times, function(i, val){
                    val = val.split(":");
                    val = ''+val[0]+':'+val[1];
                    $(sel).append($("<option value="+val+">"+val+"</option>"));
                });
            }
            
            if(ticket.options && ticket.options.id_reis)
            {
                var sel = content.find("select[name='id-reis']");
                $.each(ticket.options.id_reis, function(i, val){
                    $(sel).append($("<option value="+i+">"+val+"</option>"));
                });
            }
            
            if(ticket.date_start)
            {
                var date_start = ticket.date_start.split(" ");
                content.find("input[name='date']").val(date_start[0]);
                content.find("select[name='time']").val(date_start[1]);
            }
            
            $.ajax({
                    url: '/account/reises/',
                    type: 'post',
                    dataType: "json",
                    data: {
                        'id_reis': id_reis,
                        'action': 'ticket-window-load'
                    },
                    success: function( data ) {

                        if(data.success==1)
                        {
                            if(data.from_points)
                                content.find("select[name='from-point']").append($(data.from_points));
                            if(data.to_points)
                                content.find("select[name='to-point']").append($(data.to_points));
                            if(data.tarifs)
                                content.find("select[name='tarif']").append($(data.tarifs));
                            if(data.doc_type)
                                content.find("select[name='doc-type']").append($(data.doc_type)).trigger("onload");
                            if(data.grazhd)
                                content.find("select[name='grazhd']").append($(data.grazhd)).trigger("onload");

                            content.find("div.raw-places").html(data.bus?self._mkRawPlaces(data.bus.size):"Не назначен автобус!");
                            content.find("div.schema-places").html(data.bus?self._mkSchemaPlaces(data.bus.config):"Не назначен автобус!");
                            self._initSchemaPlaces();
                            
                            self._loadTicketStart();
                        }
                        if(data.msg)
                            alertMsg(data.msg);
                    }, // success
                });//$.ajax    
        },

        _tmpHandlerFromPointsLoadedCallback: function()
        {
            var self = this;
            self.tmpHandlerFromPointsLoadedCallback = false;
            var content = $("#page-content");            
            content.find("select[name='to-point']").val(self.ticket.pax.to);
            content.find("select[name='tarif']").val(self.ticket.pax.tarif);
            content.find("select[name='cargo']").val(self.ticket.pax.cargo_num);
            
            content.find("input[name='place']").val(self.ticket.place);
            content.find("input[name='phone']").val(self.ticket.pax.phone.replace("(","-").replace(")","-").replace(" ",""));
            content.find("input[name='f']").val(self.ticket.pax.f);
            content.find("input[name='i']").val(self.ticket.pax.i);
            content.find("input[name='o']").val(self.ticket.pax.o);
            content.find("select[name='sex']").val(self.ticket.pax.sex);
            content.find("input[name='dr']").val(self.ticket.pax.dr);
            content.find("select[name='grazhd']").val(self.ticket.pax.grazhd).siblings(":text").trigger("blur");
            content.find("select[name='doc-type']").val(self.ticket.pax.doc_type).siblings(":text").trigger("blur");
            content.find("input[name='doc-num']").val(self.ticket.pax.doc_num);
            
            content.find("textarea[name='comment']").val(self.ticket.pax.comment);
            
            content.find("input.cls-ticket-cost-info, input.cls-ticket-cost-info-old").val(''+self.ticket.cost+' руб.');
            content.find("input.cls-cargo-cost-info, input.cls-cargo-cost-info-old").val(''+self.ticket.pax.cargo_cost+' руб.');
            content.find("input.cls-comission-cost-info, input.cls-comission-cost-info-old").val(''+(self.ticket.pax.comission_stack.over.agent_percent_sum+self.ticket.pax.comission_stack.over.gobus_percent_sum) +' руб.');
            content.find("input.cls-total-cost-info, input.cls-total-cost-info-old").val(''+(self.ticket.pax.cargo_cost+self.ticket.cost+self.ticket.pax.comission_stack.over.agent_percent_sum+self.ticket.pax.comission_stack.over.gobus_percent_sum)+' руб.');

            content.find("select[name='to-point']").trigger("change");
//            setTimeout(function(){
//                if(content.find("input.cls-cargo-cost-info").val()=="")
//                    self._calculateCosts();
//            }, 500);
        },

        _loadTicketStart: function()
        {
            var self = this;
            var content = $("#page-content");            
            self.tmpHandlerFromPointsLoadedCallback = self._tmpHandlerFromPointsLoadedCallback;            
            content.find("select[name='from-point']").val(self.ticket.pax.from).trigger("change");
        },
        
        _getReisesList: function()
        {
            var content = $("#page-content");
            $.ajax({
                    url: window.location.href,
                    type: 'post',
                    dataType: "json",
                    data: {
                        date: content.find("input[name='date']").val(),
                        action: 'get-reises-list'
                    },
                    success: function( data ) {
                        if( (data.success==1) && data.items )
                        {
                            var sel = content.find("select[name='marsh']");
                            $(sel).append($("<option value='0-0' selected>Выберите</option>"));
                            $.each(data.items, function(i, val){
                                $(sel).append($("<option />").val(i).text(val));
                            });
                        }
                    }, // success
                });//$.ajax            
        },
        
        _getTimesList: function()
        {
            var content = $("#page-content");
            $.ajax({
                    url: window.location.href,
                    type: 'post',
                    dataType: "json",
                    data: {
                        date: content.find("input[name='date']").val(),
                        marsh: content.find("select[name='marsh']").val(),
                        action: 'get-times-list'
                    },
                    success: function( data ) {
                        if( (data.success==1) && data.items )
                        {
                            var sel = content.find("select[name='time']");
                            $(sel).append($("<option value='' selected>Выберите</option>"));
                            $.each(data.items, function(i, val){
                                val = val.split(":");
                                val = ''+val[0]+':'+val[1];
                                $(sel).append($("<option />").val(val).text(val));
                            });
                        }
                    }, // success
                });//$.ajax            
        },
        
        _getIdReisList: function()
        {            
            var content = $("#page-content"), self = this;
            $.ajax({
                    url: window.location.href,
                    type: 'post',
                    dataType: "json",
                    data: {
                        date: content.find("input[name='date']").val(),
                        marsh: content.find("select[name='marsh']").val(),
                        time: content.find("select[name='time']").val(),
                        action: 'get-id-reis-list'
                    },
                    success: function( data ) {
                        if( (data.success==1) && data.items )
                        {
                            var sel = content.find("select[name='id-reis']");
                            $(sel).append($("<option value='' selected>Выберите</option>"));
                            $.each(data.items, function(i, val){
                                $(sel).append($("<option />").val(i).text(val));
                            });
                        }
                    }, // success
                });//$.ajax            
        },
        
        _getFromPointsList: function()
        {            
            var content = $("#page-content"), self = this;
            $.ajax({
                    url: window.location.href,
                    type: 'post',
                    dataType: "json",
                    data: {
                        date: content.find("input[name='date']").val(),
                        id_reis: content.find("select[name='id-reis']").val(),
                        time: content.find("select[name='time']").val(),
                        action: 'get-from-points-list'
                    },
                    success: function( data ) {
                        if( (data.success==1) && data.id_reis )
                            self.id_reis = data.id_reis;
                        if( (data.success==1) && data.points )
                        {
                            var sel = content.find("select[name='from-point']");
                            $(sel).append($("<option value='' selected>Выберите</option>"));
                            $.each(data.points, function(i, val){
                                $(sel).append($("<option />").val(i).text(val));
                            });
                        }
                        if( (data.success==1) && data.tarifs )
                        {
                            var sel = content.find("select[name='tarif']");
                            $(sel).append($("<option value='Полный:100' selected>Полный, 100%</option>"));
                            $.each(data.tarifs, function(i, val){
                                $(sel).append($("<option />").val(i).text(val));
                            });                            
                        }
                        
                        if( (data.success==1) && data.bus )
                        {
                            content.find("div.raw-places").html(data.bus?self._mkRawPlaces(data.bus.size):"Не назначен автобус!");
                            content.find("div.schema-places").html(data.bus?self._mkSchemaPlaces(data.bus.config):"Не назначен автобус!");
                            self._initSchemaPlaces();                            
                        }
                    }, // success
                });//$.ajax            
        }
        
 });

})(jQuery);
