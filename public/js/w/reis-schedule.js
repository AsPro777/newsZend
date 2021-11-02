(function($) {

    $.widget("ui.reisSchedule", {
        
        options: {
            config: null,
        },
        idSched: 0,
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
        caller: null,
        
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
            id : 'reisScheduleModal',
            title : 'Выбор автобуса по умолчанию',
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
        
        _selectPointModalConfig: {
            id : 'selectPointModal',
            title : 'Выбор остановочного пункта',
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
            
            self._selectPointModalConfig.id = self.baseName+"SelectPointModal";
            self.selectPointModal = self._mkSelectPointModal(self._selectPointModalConfig);            

            self.table.append(self.th).append(self.tbody);
            el.append(self.table);
            el.append(self.busModal);
            el.append(self.cogModal);
            el.append(self.tarifsModal);
            el.append(self.selectPointModal);            
            
            self.submitter = $("#drv-schedulers-form input[type='submit']");
            self.submitter.hide().on("click", function(){reloadWindow(100000)});
            
            self.idSched = o.idSched;
            self._mkHeadDaysOfWeek();
            self._initFromJson(o.config);            
	},
        
        _mkHeadDaysOfWeek: function() 
        {
            var self = this, el = self.element; 
            
            for(i=0;i<7;i++)
            {
                var day = $("<th class='table-schedule-th'></th>")
                        .attr('id', self.baseName + '-days-head-'+i)
                        .html(
                            ' <input type=checkbox checked> <span>'+self._days[i]+'</span>'
                            );
                self.th.append(day);
            }            
            self.th.append($("<th class='table-schedule-th cog'></th>"));
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
        
        _initFromJson: function(config)
        {
//            console.log(config);
            if(!config) return;
            if(!config.days) return;            
            var self = this;
            
            self.tbody.empty();
            
            $.each(config.days, function(iDay,dayObj){                
                $.each(dayObj.times, function(tm,val){                
                    self.addRow(tm, true);
                    self._configCell({
                        day: iDay,
                        time: tm,
                        value: val
                    });
                });
            });
            
            self.tbody.find("td.cog ul > li > a")
                    .unbind("click")
                    .on("click", function(){
                        return self._rowCogMenuClick($(this));                        
                    });
        },
                      
        _removeRow: function(name)
        {
            var self = this, body = self.tbody;
            body.find("tr[name='"+name+"']").remove();
            self._resort();
            self.submitter.show(500);
        },
        
        addRow: function(time, isFromJson, isManual, theBus) // append row with time
        {
            var self = this, body = self.tbody;
            
            if(!isFromJson) 
                self.submitter.show(500);
                
            if( body.find("tr[name='"+time+"']").length>0 ) return;
            
            var tr = $("<tr></tr>")
                    .attr("name", time);
            
            for(var i=0; i<7; i++)
            {
                var dayChecked = self.th.find(":checkbox").eq(i).prop("checked");                
                var busname = $('<span class="busname ' + (dayChecked?"active":"") + '">'+self.busAbsent+'</span>');
                if( (theBus!=undefined) && (parseInt(theBus)>0) )
                    busname.text(theBus.split(":")[1]);
                var tm = $('<span class="time ' + (dayChecked?"active":"") + '">'+time+'</span>');
                var save = $('<span class="has-changes">Генерация приостановлена!</span>');
                var td = $("<td></td>").attr("name", i);                
                              
                var toggler = $('<i class="fa toggle" title="Вкл/Откл генерацию"></i>')
                        //.addClass(dayChecked?"fa-toggle-on":"fa-toggle-off")
                        .addClass("fa-toggle-off")
                        .on("click", function(){
                            $(this)
                                .toggleClass("fa-toggle-on")
                                .toggleClass("fa-toggle-off");
                        
                            var active = $(this).hasClass("fa-toggle-on");
                            var val = JSON.parse($(this).parent().find("input:hidden").val());
                            val.value.status = active;                            
                            $(this).parent().find("input:hidden").val(JSON.stringify(val));
                            self._refreshCell($(this).parent());
                            self.submitter.trigger("click");
                        });
                        
                var manual = $('<i class="fa manual" title="Запускать вручную или автоматически"></i>')
                        .addClass((isManual||!dayChecked)?"fa-hand-o-up":"fa-circle-o-notch fa-spin fa-fw")
                        .on("click", function(){  

                            var val = JSON.parse($(this).parent().find("input:hidden").val());
                            if( (val.day==undefined) || (val.time==undefined) )
                                return alertMsg("Эта операция пока недопустима!\r\n\r\nСначала сохраните расписание.");
                            
                            self.caller = $(this).parent();
                            self.caller.addClass("changed");
                            $(this)
                                .toggleClass("fa-circle-o-notch fa-spin fa-fw")
                                .toggleClass("fa-hand-o-up");
                            if($(this).hasClass("fa-hand-o-up"))
                                $(this).attr("title", "Запускать вручную или автоматически. Текущая установка: запускать генерацию рейсов вручную.");
                            else    
                                $(this).attr("title", "Запускать вручную или автоматически. Текущая установка: запускать генерацию рейсов автоматически.");
                             
                        
                            var active = $(this).hasClass("fa-hand-o-up");
                            val.value.manual = active;                            
                            $(this).parent().find("input:hidden").val(JSON.stringify(val));
                            
                            $.ajax({
                                    url: '/account/schedules/',
                                    type: 'post',
                                    dataType: "json",
                                    data: {
                                        'action': 'config-save',
                                        'id': self.idSched,
                                        'time': self.caller.parent().attr("name"),
                                        'day': self.caller.attr("name"),
                                        'manual': active
                                    },
                                    success: function( data ) {
                                        if(data.success==1)
                                        {
                                            reloadWindow();
                                        }
                                        else
                                            alertMsg("Конфигурация не обновлена! Ошибка сохранения!");
                                    }, // success
                                });//$.ajax                    
                            
                        });
                var manualStart = $('<i class="fa manual-start fa-play-circle fa-1x" title="Создать рейс"></i>')
                            .on("click", function(){

                                var val = JSON.parse($(this).parent().find("input:hidden").val());
                                if( (val.day==undefined) || (val.time==undefined) )
                                    return alertMsg("Эта операция пока недопустима!\r\n\r\nСначала сохраните расписание.");

                                self.caller = $(this).parent();
                                $.ajax({
                                        url: '/account/schedules/',
                                        type: 'post',
                                        dataType: "json",
                                        data: {
                                            'action': 'manual-start',
                                            'id': self.idSched,
                                            'time': self.caller.parent().attr("name"),
                                            'day': self.caller.attr("name")
                                        },
                                        success: function( data ) {
                                            if(data.success==1)
                                            {
                                                successMsg("Рейс создан!\r\n\r\nНомер рейса: "+data.id_reis+".\r\n\r\nДата и время отправления: "+data.date+".");
                                            }
                                            else
                                                alertMsg("Рейс не создан! "+(data.msg?("\r\n\r\n"+data.msg):""));
                                        }, // success
                                    });//$.ajax                    
                            });

                
                var costs = cog = tarifs = $(" ");
                if(isFromJson) 
                {
                    var bus = $('<i class="fa fa-bus fa-1x" title="Выбрать автобус"></i>')
                            .on("click", function(){

                                if(!self.idSched)
                                    return alertMsg("Эта операция пока недопустима!\r\n\r\nСначала сохраните расписание.");

                                self.caller = $(this).parent();
                                self.busModal.modal("show");
                            });
                    var cog = $('<i class="fa fa-road fa-1x" data-toggle="modal" title="Остановки"></i>')
                            .on("click", function(){
                                self.caller = $(this).parent();
                                self.cogModal.modal("show");
                            });

                    var tarifs = $('<i class="fa fa-ruble fa-1x" data-toggle="modal" title="Тарифы"></i>')
                            .on("click", function(){
                                self.caller = $(this).parent();
                                self.tarifsModal.modal("show");
                            });
                    costs = $('<i class="fa fa-sitemap fa-1x" title="Настройка цен"></i>')
                            .on("click", function(){         
                                var time = $(this).parent().parent().attr("name");
                                var day = $(this).parent().attr("name");
                                var location = window.location.protocol + "//" + window.location.host + "/account/schedule-costs/"+self.idSched+"/"+time+"/"+day;                            
                                window.location.href = location;
                            });
                }

                var initVal = '{"value":{"status":"'+0+'","manual":"'+((isManual||!dayChecked)?1:0)+'","bus":"'+(theBus?theBus:"0:")+'"}}';    
                var hidden = $("<input type='hidden'>")
                        .attr("name", 'config[days][' + i + '][times][' + time+']')
                        .val(initVal);
                
                td
                  .append(costs)
                  .append(tarifs)
                  .append(cog)
                  .append(bus)
          
                  .append(toggler)
                  .append(manual)
                  .append(manualStart)
          
                  .append(hidden)
                  .append(tm)
                  .append(busname)
                  .append(save);
          
                tr.append(td);
            }
            var rowMenu = '<div class="btn-group">' +
                        '<button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown">' +
                        '<span class="caret"></span>' +
                        '</button>' +
                        '<ul class="dropdown-menu pull-right">' +                            
                        '<li><a href="#" name=off><i class="fa fa-toggle-off"></i> Отключить ряд расписания</a></li>' +
                        '<li><a href="#" name=on><i class="fa fa-toggle-on"></i> Включить ряд расписания</a></li>' +
                        '<li class="divider"></li>' +
                        '<li><a href="#" name=manual><i class="fa active fa-hand-o-up"></i> Запускать ряд вручную</a></li>' +
                        '<li><a href="#" name=auto><i class="fa fa-circle-o-notch fa-spin fa-fw"></i>Запускать ряд автоматом</a></li>' +
                        '<li class="divider"></li>' +
                        '<li><a href="#" name=remove><i class="fa fa-trash"></i> Удалить ряд расписания</a></li>' +
                        '<li class="divider"></li>' +
                        '<li><a href="#" name=remove-reises><i class="fa fa-trash"></i> Отменить рейсы без билетов</a></li>' +
                        '</ul>' +
                        '</div>';         
            tr.append($("<td class=cog></td>").append(rowMenu));
            body.append(tr);
            self._resort();
        },
        
        _configCell: function(valConfig)      
        {
            var self = this, body = self.tbody;
            var cell = body.find("tr[name='"+valConfig.time+"'] td").eq(valConfig.day);
            cell.find("input:hidden").val(JSON.stringify(valConfig));    
            self._refreshCell(cell);
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
            
            return modal;
        },
                
        _mkBusModal: function(config)
        {
            var self = this, body = self.tbody;

            var modal = self._mkModalWindow(config);
            modal.body.html(self._mkBusModalBody());
            
            modal.on('show.bs.modal', function (e) {
                var select = modal.body.find("select").hide();
                $.ajax({
                        url: '/ajax/cb-bus',
                        type: 'post',
                        dataType: "json",
                        data: {
                          'id_sched': self.idSched,  
                        },
                        success: function( data ) {
                            self.selectData = $.extend({}, data);
                            var caller = $(self.caller);
                            var oldVal = JSON.parse(caller.find("input:hidden").val());
                            selectedIndex = oldVal.value.bus?parseInt(oldVal.value.bus):0;                            
                            
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
                var oldVal = JSON.parse(caller.find("input:hidden").val());
                oldVal.value.bus = id + ":" + title;
                caller.find("input:hidden").val(JSON.stringify(oldVal));
                caller.find("span.busname").html(title);
                if(id>0)
                    caller.find("i.fa-bus").addClass("active");
                else
                    caller.find("i.fa-bus").removeClass("active");
                caller.addClass("changed");                
                self.submitter.trigger("click");
            });
            
            return modal;            
        },
        _mkCogModal: function(config)
        {
            var self = this, body = self.tbody;

            var modal = self._mkModalWindow(config);
            modal.modalDialog.addClass("modal-lg");
            modal.body.html(self._mkCogModalBody());

            modal.on('show.bs.modal', function (e) {
                var caller = $(self.caller);
                var hidden = caller.find("input:hidden").first();
                var cfg = JSON.parse(hidden.val());

                modal.body.find("input[name='from-points']").sortableEndpoints("initFromJson", cfg.value.from_points);
                modal.body.find("input[name='trace-points']").sortableEndpoints("initFromJson", cfg.value.trace_points);
                modal.body.find("input[name='to-points']").sortableEndpoints("initFromJson", cfg.value.to_points);
            });
            
            modal.btnOK.on("click", function(){
                // validate >>
                var ok = true;

                var data_pts = $("#from-points-list").find("li > input.cls-data:hidden");
                if(data_pts.length<1) {
                    var m = "Необходимо указать пункт отправления!"; alertMsg(m); ok = false; return ok;
                }
                $.each(data_pts, function(i, obj){
                    var splitted = $(obj).val().split(":");
                    var id = parseInt(splitted[2]);
                    if(isNaN(id) || !id) {
                        var m = "Необходимо заполнить информацию о пункте посадки в городе отправления или удалить пункт!"; alertMsg(m); ok = false; return ok;
                    }
                });
                if(!ok) return ok;

                var data_pts = $("#trace-points-list").find("li > input.cls-data:hidden");
                $.each(data_pts, function(i, obj){
                    var splitted = $(obj).val().split(":");
                    var id = parseInt(splitted[2]);
                    if(isNaN(id) || !id) {
                        var m = "Необходимо заполнить информацию об промежуточном пункте или удалить его!"; alertMsg(m); ok = false; return ok;                
                    }
                });
                if(!ok) return ok;

                var data_pts = $("#to-points-list").find("li > input.cls-data:hidden");
                if(data_pts.length<1) {
                    var m = "Необходимо указать пункт прибытия!"; alertMsg(m); ok = false; return ok;
                }
                $.each(data_pts, function(i, obj){
                    var splitted = $(obj).val().split(":");
                    var id = parseInt(splitted[2]);
                    if(isNaN(id) || !id) {
                        var m = "Необходимо заполнить информацию о пункте высадки в городе прибытия или удалить пункт!"; alertMsg(m); ok = false; return ok;                
                    }
                });
                if(!ok) return ok;

                $.each($("#from-points-list,#trace-points-list,#to-points-list").find("input.cls-time"), function(i, obj){
                    if(i==0) return;
                    if(isNaN(parseInt($(obj).val())) || (parseInt($(obj).val())<=0) )
                    {
                        alertMsg("Необходимо указать время в пути для всех пунктов!");
                        return ok = false;
                    }
                });
                if(!ok) return ok;                                
                // << validate
                
                
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

                $.ajax({
                        url: '/account/schedules/',
                        type: 'post',
                        dataType: "json",
                        data: {
                            'action': 'config-save',
                            'id': self.idSched,
                            'time': caller.parent().attr("name"),
                            'day': caller.attr("name"),
                            'from-points': from, 
                            'trace-points': trace, 
                            'to-points': to
                        },
                        success: function( data ) {
                            if(data.success==1)
                            {
                               reloadWindow();
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
//console.log(cfg);                
                modal.body.find("input[name='cargo']").val(cfg.value.cargo);
                modal.body.find("input[name='tarifs']").sortableList("initFromJson", cfg.value.tarifs);
            });
            
            modal.btnOK.on("click", function(){
                var caller = $(self.caller);
                var hidden = caller.find("input:hidden").first();
                var cfg = JSON.parse(hidden.val());                                
                var tarifs = modal.body.find("input[name='tarifs']").sortableList("getConfig");
                var cargo = modal.body.find("input[name='cargo']").val();

                $.ajax({
                        url: '/account/schedules/',
                        type: 'post',
                        dataType: "json",
                        data: {
                            'action': 'config-save',
                            'id': self.idSched,
                            'day': caller.attr("name"),
                            'time': caller.parent().attr("name"),
                            'cargo': cargo,
                            'tarifs': tarifs
                        },
                        success: function( data ) {
                            if(data.success==1)
                            {
                                reloadWindow();
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
                    $(this).find("#add-point").val('');
                    $(this).find("#related-control").val(container.attr("id"));
                    $.ajax({
                            url: '/ajax/end-points-list',
                            type: 'post',
                            dataType: "json",
                            data: {
                              id_city: d[0]
                            },
                            success: function( data ) {
                                var select = $("#configSelectPointModal").find("select");
                                select.empty();
                                select.append( $('<option value="0">Укажите остановку</option>') );
                                $.each(data, function(i,v){
                                      select.append( $('<option value="'+v.id+'">'+v.value+'</option>'));
                                });
                                select.val(parseInt(d[2])?d[2]:0);
                            }, // success
                        });//$.ajax
            });
            
            modal.btnOK.on("click", function(){
                    // city data on caller
                    var li = $("#configSelectPointModal").data("li"); // li element
                    var topDiv = li.parent().parent();

                    var relatedInput = topDiv.find("input.ui-autocomplete-input").first();        
                    var ar = relatedInput.sortableEndpoints("getLiSetup").split(":");
                    if($("#configSelectPointModal").find("div.tab-pane.active").prop("id") == "add")
                    {
                        $.ajax({
                                url: '/account/util/',
                                type: 'post',
                                dataType: "json",
                                data: {
                                  id_city: $("#configSelectPointModal").find("#id-city").val(),
                                  name: $("#configSelectPointModal").find("#add-point").val(),
                                  action: 'add-point'
                                },
                                success: function( data ) {
                                  var newval = ar[0] + ":" + ar[1] + ":" + data[0] + ":" + data[1];
                                  relatedInput.sortableEndpoints("setLiSetup", li.attr("id"), newval);
                                }, // success
                            });//$.ajax        
                    }
                    else
                    {
                        var select = $("#configSelectPointModal").find("select");
                        var newval = ar[0] + ":" + ar[1] + ":" + select.val() + ":" + select.find(":selected").html();
                        relatedInput.sortableEndpoints("setLiSetup", li.attr("id"), newval);
                    }    
                   $("#configSelectPointModal").modal("hide"); 
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
            });
            
            return result;
        },
        
        _mkSelectPointModalBody: function()
        {            
            var self = this, o = self.options; 
            
            var ul = $('<ul class="nav nav-tabs" id="modalTab"></ul>');
            var li1 = $('<li class="active"><a href="#select" data-toggle="tab">Выбрать из списка</a></li>');
            var li2 = $('<!--li><a href="#add" data-toggle="tab">Добавить новую 1</a></li-->');
            ul.append(li1).append(li2);
                       
            var div = $("<div class=col-md-12></div>");
            var label = $('<br><label for="select-point">Конечная остановка</label><br>');
            var tabContent = $('<div class="tab-content"></div>');
            var tabPaneActive = $('<div class="tab-pane active" id="select"></div>');
            var tabPane = $('<div class="tab-pane" id="add"></div>');
            var select = $('<select name="select-point" class="form-control"><option value="">Укажите остановку</option></select>');
            var input = $('<input id="add-point" name="add-point" class="form-control" placeholder="Укажите остановку"></input>');

            var hiddens = $("<input type=hidden id=id-city><input type=hidden id=related-control>");

            div.append(ul).append(label).append(tabContent.append(tabPaneActive.append(select)).append(tabPane.append(input))).append(hiddens);

            return $(div.html());
        },
        _mkSortableField: function(name, label, placeholder, _class)
        {
            var field = $('<div class="'+_class+'" id="field-'+name+'"></div>');
            var label = $('<label for="'+name+'">'+label+'</label><br>');
            var input = $('<input type="text" name="'+name+'" class="form-control" placeholder="'+placeholder+'" value="">');

            return field.append(label).append(input);
        },
        
        _refreshCell: function(cell)
        {
            var self = this; 
            var config = JSON.parse($(cell).find("input:hidden").val());
            config = config.value;          
            var active = config.status==1;
            
            if(config.manual == undefined) config.manual = true;
            var manual = Boolean(config.manual);            

            if(active)
            {
                $(cell).find("i.fa-toggle-on, i.fa-toggle-off").removeClass("fa-toggle-off").addClass("fa-toggle-on");
                $(cell).find("span.time, span.busname, i.fa").addClass("active");
            }
            else
            {
                $(cell).find("i.fa-toggle-on, i.fa-toggle-off").removeClass("fa-toggle-on").addClass("fa-toggle-off");
                $(cell).find("span.time, span.busname, i.fa").removeClass("active");
            }

            if(manual)
            {
                $(cell).find("i.fa.manual").removeClass("fa-circle-o-notch fa-spin fa-fw").addClass("fa-hand-o-up")
                        .attr("title", "Запускать вручную или автоматически. Текущая установка: запускать генерацию рейсов вручную.");
                $(cell).find("i.fa.manual-start").show();
            }
            else
            {
                $(cell).find("i.fa.manual").addClass("fa-circle-o-notch fa-spin fa-fw").removeClass("fa-hand-o-up")
                        .attr("title", "Запускать вручную или автоматически. Текущая установка: запускать генерацию рейсов автоматически.");
                $(cell).find("i.fa.manual-start").hide();
            }
            
            if(config.costs)
            {
                var empty = true;
                $.each(config.costs, function(a,b){
                    empty = false;
                    return false;
                });
                if(empty)
                {
                    $(cell).addClass("changed").find("i.fa-sitemap").addClass("no-costs");
                }
            }
            
            if(config.bus)
            {
                var vals = config.bus.split(":");    
                $(cell).find("span.busname").html(vals[1]);
                if(vals[0]>0)
                    $(cell).find("i.fa-bus").removeClass("no-bus");
                else
                    $(cell).addClass("changed").find("i.fa-bus").addClass("no-bus");
            }            
        },
        
    _rowCogMenuClick: function(a)
    {
      var self = this;  
      
      return confirmMsg("Вы уверены?", function() 
      {          
        switch($(a).attr("name"))
        {
            case "on": 
            case "off": $.each($(a).closest("tr").find("input:hidden"), function(i, hidden){
                              var val = JSON.parse($(hidden).val());
                              val.value.status = $(a).attr("name")=="on";
                              $(hidden).val(JSON.stringify(val));
                         });
                break;
            case "manual":     
            case "auto": $.each($(a).closest("tr").find("input:hidden"), function(i, hidden){
                              var val = JSON.parse($(hidden).val());
                              val.value.manual = $(a).attr("name")=="manual";
                              $(hidden).val(JSON.stringify(val));
                         });
                break;
            case "remove": $(a).closest("tr").remove(); 
               break;
            case "remove-reises": $.ajax({
                                      url: '/account/schedules/',
                                      type: 'post',
                                      dataType: "json",
                                      data: {
                                          'action': 'remove-reises',
                                          'id': self.idSched,
                                          'time': $(a).closest("tr").attr("name"),                                        
                                      },
                                      success: function( data ) {
                                         var msg = "";
                                         if(data.deleted>0) 
                                             msg += "Отменено рейсов: "+data.deleted+"\r\n\r\n"
                                         if(data.count>0) 
                                             msg += "Осталось рейсов, на которые уже проданы билеты: "+data.count+"\r\n\r\n"
                                         if(data.deleted+data.count==0)
                                             msg = "Рейсов по данному расписанию и времени нет!\r\n\r\n"
                                         if(msg) 
                                             alertMsg(msg);
                                      }, // success
                                  });//$.ajax                    
                                  $(a).closest("div.btn-group").removeClass("open"); 
                                  return false;
               break;
            default:                 
        }

            self.submitter.trigger("click");
      });
    },
        
});	
        
})(jQuery);