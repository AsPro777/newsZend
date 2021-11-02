(function($) {

    $.widget("ui.reises", {

        options: {
            config: null,
            granted: null,
        },
        baseName: '',
        arrName: '',
        table: '',
        th: '',
        tbody: '',
        busModal: null,
        cogModal: null,
        tarifsModal: null,
        selectPointModal: null,
        selectData: null,
        busAbsent: 'автобус н/д',
        driverAbsent: 'водитель н/д',
        caller: null,
        placesReloadTimerId: 0,

        _days: {
                    0 : 'Вск',
                    1 : 'Пнд',
                    2 : 'Втр',
                    3 : 'Срд',
                    4 : 'Чтв',
                    5 : 'Птн',
                    6 : 'Сбт',
        },

        _busModalConfig: {
            id : 'reisBusModal',
            title : 'Выбор автобуса',
            btnCloseTitle : 'Отмена',
            btnOkTitle : 'Готово'
        },

        _cogModalConfig: {
            id : 'reisCogModal',
            title : 'Настройки рейса',
            btnCloseTitle : 'Отмена',
            btnOkTitle : 'Сохранить'
        },

        _tarifsModalConfig: {
            id : 'reisTarifsModal',
            title : 'Настройки тарифов',
            btnCloseTitle : 'Отмена',
            btnOkTitle : 'Сохранить'
        },

        _ticketsModalConfig: {
            id : 'reisTicketsModal',
            title : 'Продажа билетов',
            btnCloseTitle : 'Отмена',
            btnOkTitle : 'Продать билет',
            btnReservTitle : 'Забронировать билет',
        },

        _ticketsMapModalConfig: {
            id : 'ticketsMapModal',
            title : 'Билеты на рейс',
            btnCloseTitle : 'Отмена',
            btnOkTitle : 'Закрыть'
        },

        _selectPointModalConfig: {
            id : 'selectPointModal',
            title : 'Выбор остановочного пункта',
            btnCloseTitle : 'Отмена',
            btnOkTitle : 'Готово'
        },
        
        _ticketQuestionModalConfig: {
            id : 'ticketQuestionModal',
            title : 'Идентификация билета и пассажира',
            btnCloseTitle : 'Отмена',
            btnOkTitle : 'Готово'
        },

        _create: function()
        {
            var self = this, el = self.element, o = self.options;

            self.baseName = $(el).attr("id");
            self.arrName = $(el).attr("id")+"-arr";

            self.table = $("<table class=table-schedule></table>").attr("name", self.baseName+"-table");
            self.th = $("<tr class=table-schedule-head-tr></tr>");
            self.tbody = $("<tbody></tbody>");

            self._busModalConfig.id = self.baseName+"BusModal";
            self.busModal = self._mkBusModal(self._busModalConfig);

            self._cogModalConfig.id = self.baseName+"CogModal";
            self.cogModal = self._mkCogModal(self._cogModalConfig);

            self._tarifsModalConfig.id = self.baseName+"TarifsModal";
            self.tarifsModal = self._mkTarifsModal(self._tarifsModalConfig);

            self._ticketsModalConfig.id = self.baseName+"TicketsModal";
            self.ticketsModal = self._mkTicketsModal(self._ticketsModalConfig);

            self._ticketsMapModalConfig.id = self.baseName+"TicketsMapModal";
            self.ticketsMapModal = self._mkTicketsMapModal(self._ticketsMapModalConfig);

            self._selectPointModalConfig.id = self.baseName+"SelectPointModal";
            self.selectPointModal = self._mkSelectPointModal(self._selectPointModalConfig);

            self.table.append(self.th).append(self.tbody);
            el.append(self.table);
            el.append(self.busModal);
            el.append(self.cogModal);
            el.append(self.tarifsModal);
            el.append(self.ticketsModal);
            el.append(self.selectPointModal);

            self._mkHeadDaysOfWeek();
            self._initFromJson();
            self.tbody.find("td.hiden-while-initialisation").empty().removeClass("hiden-while-initialisation");
            self.tbody.remove("tr[text='']");
        },

        _mkHeadDaysOfWeek: function()
        {
            var self = this, el = self.element, o = self.options;
            // "start_date":{"date":"2017-06-28 12:42:23.000000","timezone_type":3,"timezone":"Europe\/Berlin"}
            var startDay = o.config.start_date.date.split(" ");
            var startDate = startDay = startDay[0].split("-");
            startDay = new Date(startDay[0], startDay[1]-1, startDay[2]);
            startDay = startDay.getDay();
            for(i=0;i<o.config.show_days;i++)
            {
                var dayIndex = startDay + i;
                if(dayIndex>=7) dayIndex-=7;

                var d = new Date(startDate[0], startDate[1]-1, startDate[2]*1+i);
                var dt = d.toLocaleFormat("%d.%m.%Y");

                var day = $("<th class='table-schedule-th'></th>")
                        .attr('id', self.baseName + '-days-head-'+i)
                        .html(
                            ' <span>'+self._days[dayIndex]+', '+ dt + '</span>'
                            );
                self.th.append(day);
            }
        },

        _resort: function()
        {
            var self = this;
            var rows = self.tbody.children("tr");
            rows.sort(function(a,b)
            {
                var an = a.getAttribute('name'),
                    bn = b.getAttribute('name');

                if(an > bn) {
                        return 1;
                }
                if(an < bn) {
                        return -1;
                }
                return 0;
            });
            rows.detach().appendTo(self.tbody);
        },

        _initFromJson: function()
        {
            var self = this, el = self.element, o = self.options;
            var config = o.config.reises;
            var columns = o.config.show_days;
            var startDate = (o.config.start_date.date.split(" "))[0];

            if(!config) return;

            self.tbody.empty();

            $.each(config, function(iReis,reisObj){
                    var start_date = reisObj.date_start.date.split(" ");
                    var dt = start_date[0];

                    var tm = start_date[1].split(":");
                    tm = tm[0]+":"+tm[1];

                    self.addRow(tm, startDate, columns);
                    self._configCell({
                        date: dt,
                        time: tm,
                        data: reisObj
                    });
            });
            
            self._removeCellNotAccessableControls();
        },

        addRow: function(time, startDate, columns) // append row
        {
            var self = this, body = self.tbody, o = self.options;

            if( body.find("tr[name='"+time+"']").length>0 ) return;

            var tr = $("<tr></tr>")
                    .attr("name", time);

            startDate = startDate.split("-");

            for(var i=0; i<columns; i++)
            {
                var d = new Date(startDate[0], startDate[1]-1, startDate[2]*1+i);
                var dt = d.toLocaleFormat("%Y-%m-%d");

                var td = $("<td />").attr("name", dt).addClass("hiden-while-initialisation");

                var carrier = $('<span class="carrier active"  title="Перевозчик"></span>');
                var marshname = $('<span class="marshname active"  title="Заказной рейс"></span>');
                var busname = $('<span class="busname active"  title="Автобус на рейс">'+self.busAbsent+'</span>');
                var tm = $('<span class="time active" title="Время отправления">'+time+'</span>');
                var reis = $('<span class="reis active" title="№ рейса"></span>');
                                
                var sold = $('<button type="button" class="btn bg-info sold" title="Оформленные билеты и операции с ними"><i class="icon icon-list-numbered"></i></button>')
                        .on("click", function(){
                            self.caller = $(this).closest("div.reis-container");
                            self.ticketsMapModal.modal("show");
                        });

                var closer = $('<i class="fa cancel fa-times fa-1x" title="Отмена рейса (произойдет только при отутствии активных билетов!)"></i>')
                        .on("click", function(){
                            var caller = $(this).parent();
                            confirmMsg("Вы хотите отменить рейс! Вы уверены?", function()
                            {
                                var json = JSON.parse(caller.find("input:hidden").val());
                                var id = json.data.id;
                                $.ajax({
                                        url: '/account/reises/'+id,
                                        type: 'post',
                                        dataType: "json",
                                        data: {
                                            id: id,
                                            action: 'reis-remove'
                                        },
                                        success: function( data ) {
                                            if(data.success == 1)
                                            {                                                
                                                var tr = caller.closest("tr");
                                                var table = caller.closest("table");
                                                caller.remove();
                                                if(tr.text()=="") tr.remove();
                                                if(!table.find("tr").length) table.remove();
                                            }
                                            else
                                            {
                                                if(data.msg) alertMsg(data.msg);
                                                else alertMsg("Сервер не допустил удаление! Причина неизвестна!");
                                            }
                                        }, // success
                                    });//$.ajax

                            });
                        });
                var cog = $('<i class="fa points fa-road fa-1x" data-toggle="modal"  title="Настройка остановок"></i>')
                        .on("click", function(){
                            self.caller = $(this).closest("div.reis-container");
                            self.cogModal.modal("show");
                        });
                        
                var partner_bron = $('<i class="fa points fa-h-square fa-1x" data-toggle="modal"  title="Партнерская бронь"></i>')
                        .on("click", function(){
                            self.caller = $(this).closest("div.reis-container");                           
                            var w = modalTpl({
                                title: "Бронь для партнеров",
                                body: "Загрузка...",
                                "ajax-url": "/account/" + ajax_action,
                                "ajax-data": {
                                    action: 'modal-reis-reserved-by-partner',
                                    id: self.caller.attr("name")
                                },
                            }).appendTo($("body"));
                            w.modal("show");
                            
                        });

                var tarifs = $('<i class="fa tarifs fa-ruble fa-1x" data-toggle="modal" title="Настройка тарифов"></i>')
                        .on("click", function(){
                            self.caller = $(this).closest("div.reis-container");
                            self.tarifsModal.modal("show");
                        });

                var bus = $('<i class="fa bus fa-bus fa-1x" title="Выбор автобуса"></i>')
                        .on("click", function(){
                            self.caller = $(this).closest("div.reis-container");
                            self.busModal.modal("show");
                        });

                var rtype = $('<i class="fa rtype fa-check-square-o fa-1x" title="Заказной рейс"></i>');

                var driver = $('<i class="fa driver fa-user fa-1x" title="Выбор экипажа"></i>')
                        .on("click", function(){
                            self.caller = $(this).closest("div.reis-container");
                            var w = modalTpl({
                                id: 'modal-reis-equipage',
                                title: "Экипаж",
                                body: "Загрузка...",
                                "ajax-url": "/account/" + ajax_action,
                                "ajax-data": {
                                    action: 'modal-reis-equipage',
                                    id: self.caller.attr("name")
                                },
                                "submit-function": function(){                                    
                                    if($("#modal-reis-equipage").data("validate")())
                                    {
                                        var cfg = self.caller.find("input:hidden");
                                        var cfgVals = JSON.parse(cfg.val());
                                        
                                        var drivers = $("#modal-reis-equipage").data("drivers");
                                        var personal = $("#modal-reis-equipage").data("personal");                                        
  
                                        var drvs = []; 
                                        $.each(drivers, function(id, obj){                                                                                        
                                            drvs.push(obj);                                            
                                        });
                                        cfgVals.data.drivers = drvs;
                                        
                                        var prss = []; 
                                        $.each(personal, function(id, obj){                                            
                                            prss.push(obj);                                            
                                        });
                                        cfgVals.data.personal = prss;                                                                                
                                        
                                        cfg.val(JSON.stringify(cfgVals));
                                        
                                        $.ajax({
                                            url: '/account/reises/',
                                            type: 'post',
                                            dataType: "json",
                                            data: {
                                                action: 'reis-save',
                                                data: cfg.val()
                                            },
                                            success: function( data ) {
                                                if(data.success==1)
                                                {
                                                    self.caller.removeClass("changed");
                                                    if(drvs.length>0)
                                                        self.caller.find("i.fa-user").addClass("active");
                                                    else
                                                        self.caller.find("i.fa-user").removeClass("active");

                                                    self._updateWarning(self.caller);
                                                }
                                                else
                                                    alertMsg("Конфигурация не обновлена! Ошибка сохранения!");
                                            } // success
                                        });//$.ajax                                        
                                        
                                        return true;
                                    }
                                    else
                                        return false;
                                }                                
                            }).appendTo($("body"));
                            w.find(".modal-dialog").addClass("modal-lg");
                            w.modal("show");
                        });

                var tiсkets = $('<button type="button" class="btn bg-info btn-plus" title="Оформление билетов"><i class="icon icon-plus3"></i></button>')
                        .on("click", function(){
                            self.caller = $(this).closest("div.reis-container");
                            self.ticketsModal.modal("show");
                        });
                        
                var reserved = $('<button type="button" class="btn bg-info btn-reserved" title="Брони"><i class="icon icon-certificate"></i></button>')
                        .on("click", function(){
                            self.caller = $(this).closest("div.reis-container");                           
                            var w = modalTpl({
                                title: "Список забронированных билетов",
                                body: "Загрузка...",
                                "ajax-url": "/account/" + ajax_action,
                                "ajax-data": {
                                    action: 'modal-reis-reserved-list',
                                    id: self.caller.attr("name")
                                },
                            }).appendTo($("body"));
                            w.modal("show");
                            
                        });

                costs = $('<i class="fa costs fa-sitemap fa-1x" title="Настройка цен"></i>')
                        .on("click", function(){
                            var val = JSON.parse($(this).closest("div.reis-container").find("input:hidden").val());
                            var location = window.location.protocol + "//" + window.location.host + "/account/reis-costs/"+val.data.id;
                            window.location.href = location;
                        });


                var hidden = $("<input type='hidden'>")
                        .attr("name", 'config[days][' + i + '][times][' + time+']')
                        .val("");
                
                var icons_container = $("<div />").addClass("btc icons-container"); // btc - buttons container
                icons_container
                  .append(o.granted.controls?partner_bron:false)
                  .append(o.granted.controls?costs:false)
                  .append(o.granted.controls?tarifs:false)
                  .append(o.granted.controls?cog:false)               
//                  .append(o.granted.controls?bus:false)
                  .append(o.granted.controls?driver:false)
                        ;
                var buttons_container = $("<div />").addClass("btc buttons-container");
                buttons_container
                  .append((o.granted.controls || o.granted.sell)?tiсkets:false)
                  .append((o.granted.controls || o.granted.sell)?reserved:false)
                  .append((o.granted.controls && o.granted.edit)?sold:false)
                        ;                
                
                var container = $("<div name=container />").addClass("reis-container");
                container
                  .append(icons_container)
                  .append(o.granted.remove?closer:false)
                  .append(reis)
                  .append(tm)
                  .append(carrier)
          
                  .append(o.granted.controls?rtype:false)
                  .append(marshname)

                  .append(o.granted.controls?bus:false)
                  .append(busname)
          
                  .append(hidden)
                  .append(buttons_container)
          ;

                td.append(container);
                tr.append(td);
            }
            body.append(tr);
            self._resort();
        },

        _removeCellNotAccessableControls: function()
        {
            var self = this;            
            $.each(self.tbody.find("div.reis-container"), function(i, container)
            {
                container = $(container);
                var config = container.find("input:hidden").val();
                if(config=="") return;
                
                config = JSON.parse(config);
                if(!config.data) return;
                
                var data = config.data;
                if(data.inet && !data.eq_owner && !data.eq_agent)
                {
                    container.find("i.fa:not(.sell)").remove();
                    container.find("button.sold").remove();
                }
                if(!data.chartered)
                    container.find("i.fa.rtype").remove();                    
            });            
        },
        
        _configCell: function(reisObjconfig)
        {
            var self = this, body = self.tbody;
            config = $.extend({}, reisObjconfig.data);
            var cell = body.find("tr[name='"+reisObjconfig.time+"'] td[name='"+reisObjconfig.date+"']");

            var container = $(cell.find("div").not(".btc").last());
            if(container.attr("name") != "container")
            {
                container = container.clone(true);    
                container.attr("name", config.id);
                cell.append(container);                
            }
            container.attr("name", config.id);
            
            cell.removeClass("hiden-while-initialisation");
            container.removeClass("warning");
            container.find("span.marshname").html(config.chartered?"Заказной":(config.from + "-" + config.to));
            container.find("span.reis").html("№" + config.id);
            container.find("button.sold").removeClass("hidden").addClass(config.tickets_count?"":"hidden"); // text(config.tickets_count?config.tickets_count:"")
            container.find("span.carrier").text(config.carrier?config.carrier:"");
            if(config.bus)
            {
                container.find("span.busname").html(config.bus);
                container.find("i.fa-bus").addClass("active");
            }
            else
            {
               container.find("span.busname").html(self.busAbcent);
               container.find("i.fa-bus").removeClass("active");
               if(config.eq_owner) container.addClass("warning");
            }
            
            if(!config.has_costs)
            {
                container.find("i.fa-sitemap").addClass("warning");
                if(config.eq_owner) container.addClass("warning");
            }
            else 
                container.find("i.fa-sitemap").addClass("active");
            
            if(config.drivers.length)
                container.find("i.fa-user").addClass("active");
            else
            {
               container.find("i.fa-user").removeClass("active");
               if(config.eq_owner) container.addClass("warning");
            }
            container.find("button.fa-dedent").addClass("active");
            container.find("input:hidden").val(JSON.stringify(reisObjconfig));
        },

        _mkModalWindow: function(config)
        {
            var self = this, body = self.tbody;

            var id = config.id;
            var title = config.title;
            var btnCloseTitle = config.btnCloseTitle;
            var btnOkTitle = config.btnOkTitle;

            var modal = $('<div class="modal fade" id="'+id+'" tabindex="-1" role="dialog" aria-labelledby="'+id+'ModalLabel" aria-hidden="true"></div>')
            var dialog = $('<div class="modal-dialog"></div>');
            var content = $('<div class="modal-content"></div>');
            var header = $('<div class="modal-header"></div>');
            var closer = $('<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>');
            var title = $('<h4 class="modal-title" id="'+id+'ModalLabel">'+title+'</h4>');
            var body = $('<div class="modal-body"></div>');
            var footer = $('<div class="modal-footer"></div>');
            var btnClose = $('<button type="button" class="btn btn-default" data-dismiss="modal">'+btnCloseTitle+'</button>');
            var btnOK = $('<button type="button" class="btn btn-primary" data-dismiss="modal">'+btnOkTitle+'</button>');

            modal.append(dialog.append(content.append(header.append(closer).append(title)).append(body).append(footer.append(btnClose).append(btnOK))));
            modal.btnOK = btnOK;
            modal.modalDialog = dialog;
            modal.body = body;
            modal.title = title;

            return modal;
        },

        _mkBusModal: function(config)
        {
            var self = this, body = self.tbody;

            var modal = self._mkModalWindow(config);

            modal.body.html(self._mkBusModalBody());

            modal.on('show.bs.modal', function (e) {
                var select = modal.body.find("select").hide();
//                console.log("Запрос списка по аяксу");
                $.ajax({
                        url: '/ajax/cb-bus',
                        type: 'post',
                        dataType: "json",
                        data:{
                            id_reis: $(self.caller).attr("name")
                        },
                        success: function( data ) {
                            self.selectData = $.extend({}, data);
                            var caller = $(self.caller);
                            var json = JSON.parse(caller.find("input:hidden").val());
                            selectedIndex = json.data.id_bus?json.data.id_bus:0;

                            select.empty();
                            select.append($("<option value=0 title='"+self.busAbsent+"'>Не выбран</option>"));
                            $.each(data, function(i,v){
                                var opt = $("<option></option>");
                                if(v.id == selectedIndex)
                                    opt.attr("selected", "selected");
                                opt.val(v.id)
                                   .html(v.value)
                                   .attr("title", v.label);
                                select.append(opt);
                            })
                            select.show(500);
                        }, // success
                    });//$.ajax
            });

            modal.btnOK.on("click", function(){
                var id = modal.body.find("select option:selected").val();
                var title = modal.body.find("select option:selected").attr("title");
                var caller = $(self.caller);

                var cfg = JSON.parse(caller.find("input:hidden").val());
                cfg.data.id_bus = id;
                cfg.data.bus = title;
                caller.find("input:hidden").val(JSON.stringify(cfg));
                caller.addClass("changed");

                caller.find("span.busname").html(title);

                $.ajax({
                        url: '/account/reises/',
                        type: 'post',
                        dataType: "json",
                        data: {
                            action: 'reis-save',
                            data: caller.find("input:hidden").val()
                        },
                        success: function( data ) {
                            if(data.success==1)
                            {
                                caller.removeClass("changed");
                                if(id>0)
                                    caller.find("i.fa-bus").addClass("active");
                                else
                                    caller.find("i.fa-bus").removeClass("active");

                                self._updateWarning(caller);
                            }
                            else
                                alertMsg("Конфигурация не обновлена! Ошибка сохранения!");
                        } // success
                    });//$.ajax
            });

            return modal;
        },

        _mkCogModal: function(config)
        {
            var self = this, body = self.tbody;

            var modal = self._mkModalWindow(config);
            modal.modalDialog.addClass("modal-full");
            modal.body.html(self._mkCogModalBody());

            modal.on('show.bs.modal', function (e) {
                var caller = $(self.caller);
                var hidden = caller.find("input:hidden").first();
                var cfg = JSON.parse(hidden.val());

                modal.body.find("input[name='from-points']").sortableEndpoints("initFromJson", cfg.data.from_points);
                modal.body.find("input[name='trace-points']").sortableEndpoints("initFromJson", cfg.data.trace_points);
                modal.body.find("input[name='to-points']").sortableEndpoints("initFromJson", cfg.data.to_points);
            });

            modal.btnOK.on("click", function(){
                var marshr = "";
                var caller = $(self.caller);
                var hidden = caller.find("input:hidden").first();
                var cfg = JSON.parse(hidden.val());

                var from = modal.body.find("input[name='from-points']").sortableEndpoints("getLiSetup").split(":")[1];
                marshr = from + "-";
                from = modal.body.find("input[name='from-points']").sortableEndpoints("getConfig");

                var to = modal.body.find("input[name='to-points']").sortableEndpoints("getLiSetup").split(":")[1];
                marshr += to;
                var to = modal.body.find("input[name='to-points']").sortableEndpoints("getConfig");

                var trace = modal.body.find("input[name='trace-points']").sortableEndpoints("getConfig");

                var path = from.concat(trace).concat(to);
                var timesOk = true;
                $.each(path, function(i, obj){
                   if(!i) return; 
                   if( isNaN(parseInt(obj.time)) || (obj.time < 1) ) return ( timesOk=false ); 
                });    
                
                if(!timesOk) {
                    alertMsg("Необходимо указать время в пути для всех пунктов!");
                    return false;
                }
                $.ajax({
                        url: '/account/reises/',
                        type: 'post',
                        dataType: "json",
                        data: {
                            'action': 'reis-config-save',
                            'id': cfg.data.id,
                            'from-points': from,
                            'trace-points': trace,
                            'to-points': to
                        },
                        success: function( data ) {
                            if(data.success==1)
                            {
                                caller.removeClass("changed");
                                caller.find("span.marshname").text(cfg.data.chartered?"Заказной":marshr);
                                cfg.data.from_points = from;
                                cfg.data.to_points = to;
                                cfg.data.trace_points = trace;
                                caller.find("input:hidden").val(JSON.stringify(cfg));
                                //window.location.reload(true);
                            }
                            else
                                alertMsg("Конфигурация не обновлена! Ошибка сохранения!");
                        }, // success
                    });//$.ajax

                modal.modal("hide");
            });

            return modal;
        },

        _mkTarifsModal: function(config)
        {
            var self = this, body = self.tbody;

            var modal = self._mkModalWindow(config);
            //modal.modalDialog.addClass("modal-sm");
            modal.body.html(self._mkTarifsModalBody());

            modal.on('show.bs.modal', function (e) {
                var caller = $(self.caller);
                var hidden = caller.find("input:hidden").first();
                var cfg = JSON.parse(hidden.val());

                modal.body.find("input[name='cargo']").val(cfg.data.cargo);
                modal.body.find("input[name='tarifs']").sortableList("initFromJson", cfg.data.tarifs);
            });

            modal.btnOK.on("click", function(){
                var caller = $(self.caller);
                var hidden = caller.find("input:hidden").first();
                var cfg = JSON.parse(hidden.val());
                var tarifs = modal.body.find("input[name='tarifs']").sortableList("getConfig");
                var cargo = modal.body.find("input[name='cargo']").val();

                $.ajax({
                        url: '/account/reises/',
                        type: 'post',
                        dataType: "json",
                        data: {
                            'action': 'reis-tarifs-save',
                            'id': cfg.data.id,
                            'cargo': cargo,
                            'tarifs': tarifs
                        },
                        success: function( data ) {
                            if(data.success==1)
                            {
                                caller.removeClass("changed");
                                cfg.data.cargo = cargo;
                                cfg.data.tarifs = tarifs;
                                caller.find("input:hidden").val(JSON.stringify(cfg));
                                //window.location.reload(true);
                            }
                            else
                                alertMsg("Конфигурация не обновлена! Ошибка сохранения!");
                        }, // success
                    });//$.ajax

                modal.modal("hide");
            });

            return modal;
        },

        _mkSelectPointModal: function(config)
        {
            var self = this, body = self.tbody;

            var modal = self._mkModalWindow(config);
            modal.body.html(self._mkSelectPointModalBody());

            modal.on('show.bs.modal', function (e) {
                    var container = $($(e).prop("relatedTarget")); // li передан искусственно аргументом show
                    $("#configSelectPointModal").data("li", container);

                    var d = container.find("input:hidden").first().val().split(':');
                    $(this).find(".modal-title").html('Выбор остановочного пункта в <b>' + d[1] + '</b>');
                    $(this).find("#id-city").val(d[0]);
                    $(this).find("#id-country").val(d[4]);
                    $(this).find("#add-point").val('');
                    $(this).find("#related-control").val(container.attr("id"));
                    $.ajax({
                            url: '/ajax/end-points-list',
                            type: 'post',
                            dataType: "json",
                            data: {
                              id_city: d[0],
                              id_country: d[4]
                            },
                            success: function( data ) {
                                var select = $("#configSelectPointModal").find("select");
                                select.empty();
                                select.append( $('<option value="0">Укажите остановку</option>') );
                                $.each(data, function(i,v){
                                      select.append( $('<option value="'+v.id+'">'+v.point+'</option>'));
                                });
                                select.val(parseInt(d[2])?d[2]:0);
                            }, // success
                        });//$.ajax
            });

            modal.btnOK.on("click", function(){
                    var li = $("#configSelectPointModal").data("li"); // li element
                    var topDiv = li.parent().parent();
//                    console.log(topDiv);
                    var relatedInput = topDiv.find("input.ui-autocomplete-input").first();
                    var ar = relatedInput.sortableEndpoints("getLiSetup", li).split(":");
                    if($("#configSelectPointModal").find("div.tab-pane.active").prop("id") == "add")
                    {
                        $.ajax({
                                url: '/account/util/',
                                type: 'post',
                                dataType: "json",
                                data: {
                                  id_city: $("#configSelectPointModal").find("#id-city").val(),
                                  id_country: $("#configSelectPointModal").find("#id-country").val(),
                                  name: $("#configSelectPointModal").find("#add-point").val(),
                                  action: 'add-point'
                                },
                                success: function( data ) {
                                    swal({
                                        title: "Успешно!",
                                        text: "Заявка на добавление остановки отправлена на модерацию! В ближайшее время она будет проверена и появится в списке остановок. ",
                                        confirmButtonColor: "#66BB6A",
                                        type: "success"
                                    });                    
                                }, // success
                            });//$.ajax
                    }
                    else
                    {
                        if(parseInt('0'+$("#configSelectPointModal").find("select").val()) == 0)
                        {
                            alertMsg("Необходимо выбрать остановку!");
                            return false;
                        };
                        var select = $("#configSelectPointModal").find("select");
                        var newval = ar[0] + ":" + ar[1] + ":" + select.val() + ":" + select.find(":selected").html() + ":" + ar[4];
                        relatedInput.sortableEndpoints("setLiSetup", li.attr("id"), newval);
                        $("#configSelectPointModal").modal("hide");
                    }
            });

            return modal;
        },

        _mkTicketsModal: function(config)
        {
            var self = this, body = self.tbody;
            var modal = self._mkModalWindow(config);
            modal.modalDialog.addClass("modal-full");
            modal.body.html(self._mkTicketsModalBody());

            modal.body.find("button.btn-phone").on("click", function(){
                var ph = modal.body.find("input.cls-phone").val();

                if(!ph) return alertMsg("Введите номер!");
                $.ajax({
                        url: '/account/reises/',
                        type: 'post',
                        dataType: "json",
                        data: {
                            phone: modal.body.find("input.cls-phone").val(),
                            action: 'pax-data-by-phone'
                        },
                        success: function( data ) {
                            if(data.success==1)
                            {
                                var pax = JSON.parse(data.pax);
//                                console.log(pax);
                                modal.body.find("select.cls-sex").val(pax.sex);
                                modal.body.find("input.cls-f").val(pax.f);
                                modal.body.find("input.cls-i").val(pax.i);
                                modal.body.find("input.cls-o").val(pax.o);
                                modal.body.find("input.cls-dr").val(pax.dr);
                                modal.body.find("select.cls-grazhd").val(pax.grazhd).trigger("change");
                                modal.body.find("select.cls-doc-type").val(pax.doc_type).trigger("change");
                                modal.body.find("input.cls-doc-num").val(pax.doc_num);
                            }
                            else
                            alertMsg(data.msg?data.msg:"Нет данных по указанному номеру!");
                        }, // success
                    });//$.ajax
            });

            modal.body.find("select[name='from-point']").on("change", function(){

                modal.body.find("select.remotable.cls-to-point").empty();
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
                                    modal.body.find("select[name='to-point']").append($(data.to_points));
                                    self._reloadPlaces(self._placeReisData());
                            }
                            else
                            alertMsg(data.msg?data.msg:"Нет пунктов прибытия для данного пункта отправления!");
                        }, // success
                    });//$.ajax
            });

            modal.body.find("select[name='to-point']").on("change", function(){

                self._initRawPlaces();
                self._initSchemaPlaces();
                self._reloadPlaces(self._placeReisData());
                self._startPlaceReloadTimer();
                self._calculateCosts();
            });

            modal.body.find("select[name='tarif'], select[name='cargo']").on("change", function(){

                self._calculateCosts();
            });

            modal.on('hide.bs.modal', function (e) {
                $("button.refresh").trigger("click");
                self._stopPlaceReloadTimer();
            });

            modal.on('show.bs.modal', function (e) {
                var caller = $(self.caller);
                var hidden = caller.find("input:hidden").first();
                var cfg = JSON.parse(hidden.val());

                modal.body.find("select.remotable").empty();
                modal.body.find("input").val('');
                modal.body.find("div.raw-places").empty();
                modal.body.find("div.schema-places").empty();
                self._calculateCosts();

                $.ajax({
                        url: '/account/reises/',
                        type: 'post',
                        dataType: "json",
                        data: {
                            'id_reis': cfg.data.id,
                            'action': 'ticket-window-load'
                        },
                        success: function( data ) {
                            if(data.success==1)
                            {
                                modal.title.text(data.title);
                                if(data.from_points)
                                    modal.body.find("select[name='from-point']").append($(data.from_points));
                                if(data.to_points)
                                    modal.body.find("select[name='to-point']").append($(data.to_points));
                                if(data.tarifs)
                                    modal.body.find("select[name='tarif']").append($(data.tarifs));
                                if(data.doc_type)
                                    modal.body.find("select[name='doc-type']").append($(data.doc_type)).trigger("onload");
                                if(data.grazhd)
                                    modal.body.find("select[name='grazhd']").append($(data.grazhd)).trigger("onload");

                                modal.body.find("div.raw-places").html(data.bus?self._mkRawPlaces(data.bus.size):"Не назначен автобус!");
                                modal.body.find("div.schema-places").html(data.bus?self._mkSchemaPlaces(data.bus.config):"Не назначен автобус!");
                                self._initSchemaPlaces();
                            }
                            if(data.msg)
                                alertMsg(data.msg);
                        }, // success
                    });//$.ajax
            });


            var _submitTicket = function(but){

                  var checked = self._checkSubmittedValues();
                  if(!!checked) {
                      alertMsg(checked);
                      return false;
                  }

                var caller = $(self.caller);
                var hidden = caller.find("input:hidden").first();
                var cfg = JSON.parse(hidden.val());
                var modal_body = $("#" + self._ticketsModalConfig.id + " div.modal-body").first();

                var data = {
                            'action': 'ticket-create',
                            'id_reis': cfg.data.id,
                            'id_from_point': $(modal_body.find("select[name='from-point']")).val(),
                            'id_to_point': $(modal_body.find("select[name='to-point']")).val(),
                            'id_tarif': $(modal_body.find("select[name='tarif']")).val(),
                            'order_type': $(modal_body.find("select[name='order-type']")).val(),
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
                        };

                $.ajax({
                        url: '/account/sells/',
                        type: 'post',
                        dataType: "json",
                        data: data,
                        success: function( data ) {
                            switch(parseInt(data.success))
                            {
                                case 1: order_type = $(modal_body.find("select[name='order-type']")).val();
                                        switch(parseInt(order_type)) {
                                            case 0: successMsg("Бронь оформлена! \r\n\r\nНомер билета для выкупа: "+data.id_ticket); 
                                            case 1: successMsg( "Печать билета...\r\n\r\nУбедитесь в готовности принтера и нажмите кнопку.\r\n\r\nВ случае ошибки при печати не закрывайте окно с билетом и для повторной попытки нажмите комбинацию клавиш 'Ctrl+P'.", 
                                                        function(){
                                                            self._showPrint(data.id_ticket, false, true);
                                                            setTimeout(function(){
                                                                modal.modal("hide");
                                                                window.location.reload();
                                                            }, 1000);
                                                        });
                                                    return;
                                        }
                                        modal.modal("hide");
                                        break;
                                case 0: alertMsg(data.msg?data.msg:"Билет не оформлен! Ошибка данных!");                                       
                                        break;
                                default: alertMsg("Билет не оформлен! Ошибка запроса!");
                            }
                            $(but).prop("disabled", null);
                        }, // success
                    });//$.ajax

                $(but).prop("disabled", "disabled");
                return false;
            }

            var reservBtn = $('<button type="button" class="btn btn-primary" data-dismiss="modal">'+this._ticketsModalConfig.btnReservTitle+'</button>');
            modal.btnOK.before(reservBtn);

            modal.btnOK.on("click", function() {
                var modal_body = $("#" + self._ticketsModalConfig.id + " div.modal-body").first();
                $(modal_body.find("select[name='order-type']")).val(1)
                return _submitTicket(modal.btnOK);
            });

            reservBtn.on("click", function() {
                var modal_body = $("#" + self._ticketsModalConfig.id + " div.modal-body").first();
                $(modal_body.find("select[name='order-type']")).val(0)
                return _submitTicket(reservBtn);
            });

            return modal;
        },

        _mkTicketsMapModal: function(config)
        {
            var self = this, body = self.tbody;
            var modal = self._mkModalWindow(config);
            modal.modalDialog.addClass("modal-full");
            modal.body.html(self._mkTicketsMapModalBody());

            modal.on('show.bs.modal', function (e) {
                var caller = $(self.caller);
                var hidden = caller.find("input:hidden").first();
                var cfg = JSON.parse(hidden.val());

                $.ajax({
                        url: '/account/reises/',
                        type: 'post',
                        dataType: "json",
                        data: {
                            'id_reis': cfg.data.id,
                            'action': 'tickets-map-window-load'
                        },
                        success: function( data ) {
                            if(data.success==1)
                            {
                                modal.title.html(data.title);
                                modal.title.append( $("<button type=button class='btn-xs btn-info' name='pax-list-print' title='Распечатать список пассажиров'>&nbsp;Список пассажиров&nbsp;<span class='glyphicon glyphicon-print'></span></button>")
                                        .css("margin-left", "50px")
                                        .on("click", function(){
                                            self._showPrint(cfg.data.id, 'pax-list-data', true);
                                        })
                                );
                        
                                modal.title.append( $("<button type=button class='btn-xs btn-info' name='pax-list-print' title='Распечатать список пассажиров б/к'>&nbsp;Список пассажиров б/к&nbsp;<span class='glyphicon glyphicon-print'></span></button>")
                                        .css("margin-left", "50px")
                                        .on("click", function(){
                                            self._showPrint(cfg.data.id, 'pax-list-data', true);
                                        })
                                );
                        
                                var tbody = modal.body.find("tbody[name='tickets-list']");
                                tbody.empty();
                                $.each(data.tickets, function(i, t){
                                    var p = t.pax;
                                    var tr = $("<tr></tr>");

                                    var status = "";
                                    switch(''+t.status) {
                                        case -1: status = "отмн."; break;
                                        case "": status = "своб."; break;
                                        case "0": status = "брон."; break;
                                        case "1": status = "прод."; break;
                                        case "2": status = "возвр."; break;
                                        case "3": status = "замена"; break;
                                        case "9": status = "отказ"; break;
                                        case "15": status = "отм.рейса"; break;
                                        default: status = "Неизв.! (Код:"+t.status+")";
                                    }
                                    var td_first = $("<td class='ticket-status-"+t.status+"'>"+status+"</td>");
                                    tr.append(td_first);

                                    tr.append($("<td>"+t.id+"</td>"));
                                    tr.append($("<td class=ticket-place>"+t.place+"</td>"));

                                    var from = p.from.split(":");
                                    var to = p.to.split(":");
                                    tr.append($("<td>"+from[1]+", "+from[3]+"<br>"+to[1]+", "+to[3]+"</td>"));


                                    tr.append($("<td>"+p.f+" "+p.i+" "+p.o+"<br>"+p.phone+"</td>"));

                                    var tarif = p.tarif.split(":");
                                    var tarif_str = "Тариф: "+tarif[0]+" / "+t.cost+" руб.";

                                    var cargo_str = "";
                                    if(p.cargo_num>0){
                                        cargo_str = "<br>Багаж: " + p.cargo_num + " / " + p.cargo_cost + " руб.";
                                    }

                                    var cost_str = tarif_str + cargo_str + "<br>Всего: " + parseInt(t.cost+p.cargo_cost) +" руб.";
                                    tr.append($("<td>"+cost_str+"</td>"));                                                                  

                                    var buttons_str = '';
                                    
                                    if( (parseInt(t.status) == 1) || (parseInt(t.status) == 0) )
                                        buttons_str = '<button type="button" class="btn-xs btn-info" name="ticket-print" data-id="'+t.id+'" title="Распечатать"><span class="glyphicon glyphicon-print"></span></button>&nbsp;';
                                    
                                    if(parseInt(t.status) == 1)
                                        buttons_str += '<button type="button" class="btn-xs btn-info" name="ticket-edit" data-id="'+t.id+'" title="Редактировать или заменить"><span class="glyphicon glyphicon-edit"></span></button>&nbsp;';
                                    
                                    if(!t.ownered) buttons_str = "";
                                    tr.append($("<td>"+ buttons_str+"</td>"));                                                              

                                    if(t.ownered && ( (t.status==0) || (t.status==1) ) )
                                    {
                                         var bg = $('<div class="btn-group"></div>');
                                         var b = $('<button type="button" class="btn btn-xs dropdown-toggle" data-toggle="dropdown"> '+status+' <span class="caret"></span></button>');

                                         var ul = $('<ul class="dropdown-menu" role="menu"></ul>')
                                                 .attr("name", t.id);
                                                 //.css({"position":"inherit", "min-width":"100px", "padding":0, "font-size":"12px"});
                                         td_first.html(bg.append(b).append(ul));

                                         ul.append($('<li class="sell"><a href="#">Продать</a></li>'));                                      
                                         ul.append($('<li class="sell" name="0"><a href="#">Отказ от брони</a></li>'));                         
                                         $.each(data.tarifs, function(i, cancel_tarif){
                                            var cost = parseInt(t.cost+p.cargo_cost);
                                            var comission = Math.trunc(cost / 100 * cancel_tarif[2]);                                            
                                            var comission_percent = (cancel_tarif[2]*12345) + t.randomized + Math.floor(Math.random() * (100000 - 10000 + 1)) + 10000 + cancel_tarif[2]*2468;
                                            ul.append($('<li class="cancel" data-comission="'+comission_percent+'" name="'+cancel_tarif[0]+'"><a href="#">'+cancel_tarif[1]+' (-' + cancel_tarif[2] + '%, ' + comission + 'руб.)'+'</a></li>'));
                                         });

                                        ul.find("li").hide();
                                        //ul.find("li a").css({"padding":"3px 10px"});

                                        if(parseInt(t.status)==0)
                                        {
                                            ul.find("li.sell").show();
                                            //b.addClass("btn-warning");
                                        }
                                        else if(parseInt(t.status)==1)
                                        {
                                            ul.find("li.cancel").show();
                                            //b.addClass("btn-danger");
                                        }
                                    }
                                    tbody.append(tr);
                                });
                                tbody.find("li.sell, li.cancel").on("click", function(){
                                    var self = this;
                                    confirmMsg("Вы уверены?", function() 
                                    {
                                        var ticket_id = $(self).parent().attr("name"); // ul
                                        var tarif_id = $(self).attr("name"); // li
                                        var comission = $(self).attr("data-comission"); // li
                                        var node = "";
                                        if($(self).hasClass("sell")) node = "sell";
                                        else if($(self).hasClass("cancel")) node = "cancel";
                                        if(node == "") return;

                                        var button = $(this);
                                        $.ajax({
                                                url: '/account/reises/',
                                                type: 'post',
                                                dataType: "json",
                                                data: {
                                                 id_ticket: ticket_id,
                                                 id_tarif: tarif_id,
                                                 comission: comission,
                                                 action: 'ticket-change-to-'+node
                                                },
                                                success: function( data ) {
                                                    if(data.success==1)
                                                    {
                                                        modal.trigger('show.bs.modal');
                                                    }
                                                }, // success
                                         });//$.ajax
                                     });
                                });
                                tbody.find("button[name='ticket-print']").on("click", function(){                                           
                                    self._showPrint($(this).attr("data-id"), false, true);
                                });
                                tbody.find("button[name='ticket-edit']").on("click", function(){
                                    self._showEditQuestion($(this).attr("data-id"));
                                    return false;
                                });
                            }
                        }, // success
                    });//$.ajax
            });

            modal.on('hide.bs.modal', function (e) {
              $("button.refresh").trigger("click");
              window.location.reload();
            });

            modal.btnOK.on("click", function(){
                modal.modal("hide");
                return false;
            });

            return modal;
        },

        _mkBusModalBody: function()
        {
            var divRow = $('<div class="row"></div>');
            var divCol = $('<div class="col-md-12"></div>');
            var select = $('<select name="bus" class="form-control"></select>');
            divCol.append(select);
            divRow.append(divCol);
            return divRow;
        },

        _mkCogModalBody: function()
        {
            var self = this, o = self.options;
            var result = $("<div class=row></div>");
            result.append(this._mkSortableField('from-points', 'Отправление', 'Введите наименование', 'col-md-4'));
            result.append(this._mkSortableField('trace-points', 'Промежуточные пункты', 'Введите наименование', 'col-md-4'));
            result.append(this._mkSortableField('to-points', 'Прибытие', 'Введите наименование', 'col-md-4'));

            result.find("input[name='from-points']").sortableEndpoints({
                showCity: true,
                modal: "configSelectPointModal"
            });
            result.find("input[name='trace-points']").sortableEndpoints({
                modal: "configSelectPointModal",
                showCity: true,
                onlyOneCity: false
            });
            result.find("input[name='to-points']").sortableEndpoints({
                showCity: true,
                modal: "configSelectPointModal"
            });

            return result;
        },

        _mkTarifsModalBody: function()
        {
            var self = this, o = self.options;
            var result = $("<div class=row></div>");
            result.append(this._mkSortableField('cargo', 'Багаж, %', '25%', 'col-md-12'));
            result.append(this._mkSortableField('tarifs', 'Тарифы', 'Введите наименование', 'col-md-12'));

            result.find("input[name='tarifs']").sortableList({
//                config: <?=$edReis->getTarifsJson()?>
            });

            return result;
        },

        _mkSelectPointModalBody: function()
        {
            var self = this, o = self.options;

            var ul = $('<ul class="nav nav-tabs bg-teal" id="modalTab"></ul>');
            var li1 = $('<li class="active"><a href="#select" data-toggle="tab">Выбрать из списка</a></li>');
            var li2 = $('<li><a href="#add" data-toggle="tab" tech-tag="2">Добавить новую</a></li>');
            ul.append(li1).append(li2);

            var div = $("<div class=col-md-12></div>");
            var label = $('<br><label for="select-point">Конечная остановка</label><br>');
            var tabContent = $('<div class="tab-content"></div>');
            var tabPaneActive = $('<div class="tab-pane active" id="select"></div>');
            var tabPane = $('<div class="tab-pane" id="add"></div>');
            var select = $('<select name="select-point" class="form-control"><option value="">Укажите остановку</option></select>');
            var input = $('<input id="add-point" name="add-point" class="form-control" placeholder="Укажите остановку"></input>');

            var hiddens = $("<input type=hidden id=id-city> <input type=hidden id=id-country> <input type=hidden id=related-control>");

            div.append(ul).append(label).append(tabContent.append(tabPaneActive.append(select)).append(tabPane.append(input))).append(hiddens);

            return $(div.html());
        },

        _mkTicketsModalBody: function()
        {
            var divRow = $('<div class="row"></div>');
            var divCol_1 = $('<div class="col-md-6"></div>');
            var divCol_2 = $('<div class="col-md-6"></div>');

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
            var order_type = $('<div><span class="my-label">Вид оформления</span> <select class="cls-order-type" maxlength="1" name="order-type"></select></div>');
                order_type.find("select").append($("<option value=0>Бронь</option>"));
                order_type.find("select").append($("<option value=1>Продажа</option>"));
                order_type.hide();
            panelBody_1.append(order_type);

            divCol_1.append(panel_1).append(panel);

            var panel_2 = $('<div class="panel"></div>');
            var panelHead_2 = $('<div class="panel-heading">Места</div>');
            var panelBody_2 = $('<div class="panel-body"><div class="col-md-4 raw-places">raw-places</div><div class="col-md-8 schema-places">schema-places</div></div>');
            
            panel_2.append(panelHead_2).append(panelBody_2);

            var panel_3 = $('<div class="panel"></div>');
            var panelHead_3 = $('<div class="panel-heading">Стоимость</div>');
            var panelBody_3 = $('<div class="panel-body"></div>');

            var ticket_cost = $('<div><span class="my-label">Стоимость билета</span> <input class="cls-ticket-cost-info" value="" readonly="true" name="ticket-cost-info"></div>');
            panelBody_3.append(ticket_cost);
            var cargo_cost = $('<div><span class="my-label">Стоимость провоза багажа</span> <input class="cls-cargo-cost-info" value="" readonly="true" name="cargo-cost-info"></div>');
            panelBody_3.append(cargo_cost);
            var comission_cost = $('<div><span class="my-label">Комиссия</span> <input class="cls-comission-cost-info" value="" readonly="true" name="comission-cost-info"></div>');
            panelBody_3.append(comission_cost);
            var total_cost = $('<div><span class="my-label">Стоимость общая</span> <input class="cls-total-cost-info" value="" readonly="true" name="total-cost-info"></div>');
            panelBody_3.append(total_cost);

            panel_3.append(panelHead_3).append(panelBody_3);

            divCol_2.append(panel_2).append(panel_3);

            divRow.append(divCol_1);
            divRow.append(divCol_2);

$.each({panelBody, panelBody_1, panelBody_3}, function(i, panel){
    $.each(panel.find("div"), function(i, div){
        div = $(div);
        div.addClass("form-group");
        var label = div.find("span").first().removeClass().addClass("control-label col-lg-4");    
        var others = label.siblings();
        others.detach();
        label.detach();
        var d1 = $("<div />").addClass("col-lg-8");
        var d2 = $("<div />").addClass("input-group").css("width", "100%");
        label.appendTo(div);
        others.appendTo(d2);
        div.append(d1.append(d2));
        div.find(".my-control").removeClass("my-control");
    });
});

            grazhd.find("select").typeheadOnSelect({});
            doc_type.find("select").typeheadOnSelect({onSelectFn:function(){
                    var option = JSON.parse(doc_type.find("select option:selected").first().attr("data-base"));
                    $("#doc-num-example").html("<b>Подсказка: </b>"+(option.example?option.example:"Формат \"серия-номер\" смотри в документе."));
                    if(option.mask && (option.mask!=""))
                        doc_num.find("input.cls-doc-num").first().mask(option.mask, {placeholder: option.mask});
                    else
                        doc_num.find("input.cls-doc-num").first().mask('_______________', {placeholder: " "});
            }});

            divRow.find("input, select").addClass("form-control my-control");
            return divRow;
        },

        _mkTicketsMapModalBody: function()
        {
            var self = this, o = self.options;
            var thead = $("<tr></tr>");
            thead.append($("<th>Статус</th>"));
            thead.append($("<th>Билет</th>"));
            thead.append($("<th>Место</th>"));
            thead.append($("<th>Поездка</th>"));
            thead.append($("<th>Пассажир</th>"));
            thead.append($("<th>Стоимость</th>"));
            thead.append($("<th>Действия</th>"));
            var table = $("<table class='table'></table>");
            var tbody = $("<tbody name='tickets-list'></tbody>");
            var result = $('<div class="panel"></div>').append(table.append(thead).append(tbody));

            return result;
        },

        _mkSortableField: function(name, label, placeholder, _class)
        {
            var field = $('<div class="'+_class+'" id="field-'+name+'"></div>');
            var label = $('<label for="'+name+'">'+label+'</label><br>');
            var input = $('<input type="text" name="'+name+'" class="form-control" placeholder="'+placeholder+'" value="">');

            return field.append(label).append(input);
        },

        _updateWarning: function(caller)
        {
           var self = this;
           var json = JSON.parse(caller.find("input:hidden").val());
           if( (json.data.id_bus>0) && json.data.drivers.length )
               caller.removeClass("warning");
           else
               caller.addClass("warning");
        },

        _mkSchemaPlaces: function(config)
        {
            var self = this;
//            console.log("_mkSchemaPlaces");
            var busView = $("<div id='busview'></div>");
            busView.mybusview();
            busView.mybusview("setJson", JSON.stringify(config));
            return busView;
        },

        _initSchemaPlaces: function()
        {
//            console.log("_initSchemaPlaces");
            $($("#"+this._ticketsModalConfig.id+" div.schema-places div.bus_place.place_active")[0]).removeClass("place_active");

            $.each($("#"+this._ticketsModalConfig.id+" div.schema-places div.bus_place.clickable.place_active"), function(i, place){
                $(place)
                .attr("data-name", $(place).text())
                .addClass("raw-place blocked")
                //.removeClass("place_active")
                .unbind("click");
            });
        },

        _updateSchemaPlaces: function(data)
        {
//            console.log("_updateSchemaPlaces");
            var self = this;
            var places = data.places;
            var tickets = data.tickets;
            if(!places)
                return;
            var container = $("#"+self._ticketsModalConfig.id+" div.schema-places");
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
                    case "": p.addClass("vacant")
                            .on("click", function(){
                                $(this).removeClass("vacant");
                                $("#"+self._ticketsModalConfig.id+" input.cls-place").val(p.attr("data-name"));
                                self._putReservPlace(p.attr("data-name"));
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
        },

        _mkRawPlaces: function(total)
        {
//            console.log("_mkRawPlaces");
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
//            console.log("_updateRawPlaces");
            var self = this;
            var places = data.places;
            var tickets = data.tickets;
            if(!places)
                return;
            var container = $("#"+self._ticketsModalConfig.id+" div.raw-places");
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
                                $("#"+self._ticketsModalConfig.id+" input.cls-place").val(p.attr("name"));
                                self._putReservPlace(p.attr("name"));
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
        },

        _initRawPlaces: function()
        {
//            console.log("_initRawPlaces");
            var self = this;
            $("#"+self._ticketsModalConfig.id+" div.raw-places div.raw-place")
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
            var caller = $(self.caller);
            var hidden = caller.find("input:hidden").first();
            var cfg = JSON.parse(hidden.val());
            var modal_body = $("#" + self._ticketsModalConfig.id + " div.modal-body").first();

            var data = {
                        'id_reis': cfg.data.id,
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
//            return;
            var self = this;
            self._stopPlaceReloadTimer();
            //reisremove('_startPlaceReloadTimer');
            self.placesReloadTimerId = setInterval(function(){
                //console.log('placesReloadTimerId');console.log(self._placeReisData());
                $.ajax({
                        global: false,
//              url: '/account/' + ajax_action,   // by Mike when sell reserved place
                        url: '/account/sells',
                        type: 'post',
                        dataType: "json",
                        data: $.extend(self._placeReisData(), {
                                    "action": "ticket-timer-to-points-load"
                            }),
                        complete: function( a, b ) {
//                            console.log("complete", a, b);
                        },
//                        error: function( a, b ) {
//                            console.log("error", a, b);
//                        },
                        
                        success: function( data ) {
//                        console.log("success");    
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
            //console.log('_stopPlaceReloadTimer');
            clearInterval(this.placesReloadTimerId);
        },

        _calculateCosts: function()
        {
            var self = this;
            var caller = $(self.caller);
            var hidden = caller.find("input:hidden").first();
            var cfg = JSON.parse(hidden.val());
            var modal_body = $("#" + self._ticketsModalConfig.id + " div.modal-body").first();
            var ticket_cost_info = modal_body.find("input.cls-ticket-cost-info").first();
            var cargo_cost_info = modal_body.find("input.cls-cargo-cost-info").first();
            var comission_cost_info = modal_body.find("input.cls-comission-cost-info").first();
            var total_cost_info = modal_body.find("input.cls-total-cost-info").first();

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
                            ticket_cost_info.val(data.ticket_cost_info);
                            cargo_cost_info.val(data.cargo_cost_info);
                            comission_cost_info.val(data.comission_cost_info);
                            total_cost_info.val(data.total_cost_info);
                        }
                    }, // success
                });//$.ajax
        },

        _checkSubmittedValues: function()
        {
            var self = this;
            var modal_body = $("#" + self._ticketsModalConfig.id + " div.modal-body").first();
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

            if(!dr.val()) return "Необходимо заполнить: Дата рождения!";
            if(!doc_num.val()) return "Необходимо заполнить: Серия и номер!";

            if(!phone.val()) return "Необходимо заполнить: Телефон!";
            return "";
        },

        _showPrint: function(id, action, print)
        {
            if(!id)
                return;
            if(!action) 
                action = 'ticket-data';
            $.ajax({
                    url: '/account/reises/',
                    type: 'post',
                    dataType: "json",
                    data: {
                        id: id,
                        action: action
                    },
                    success: function( data ) {                                                
                        if(data.success==1)
                        {
                            var w = window.open(data.body,"_blank", "menubar=no,location=no,toolbar=no,directories=no,status=no,width=850,resizable=yes,scrollbars=yes");
                            if(!w) return alertMsg("Необходимо отключить блокировку всплывающих окон для " + window.location.host + " в настройках браузера!");
//                            w.document.open();
//                            w.document.write(data.body);
//                            w.document.close();
                            w.focus();
//                            if(print) w.print();
                        }
                        else
                            alertMsg('Билет '+id+' не найден!');
                    }, // success
                });//$.ajax
        },
        _showEditQuestion: function(id)
        {
            var self = this;
            if(!id) return;
            $.ajax({
                    url: '/account/reises/',
                    type: 'post',
                    dataType: "json",
                    data: {
                        action: 'request-ticket-data'
                    },
                    success: function( data ) {
                    if(data.success) {
                        var qWindow = self._mkModalWindow(self._ticketQuestionModalConfig);
                        qWindow.body.html(data.code.html);
                        self.element.append(qWindow);
                        eval(data.code.js);    
                        qWindow.find(":text[name='ticket-id']").val(id);
                    } else {
                        if(data.msg || data.err) alertMsg(data.msg + "\r\n\r\n" + (data.err?data.err:""));
                    }
                    }, // success
                });//$.ajax
        },

 });

})(jQuery);
