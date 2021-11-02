(function($) {

    $.widget("ui.mybus", {
    options: {
        type: "not-scheme", // "bigbus" or "micro"
        use19: false,
        use2x: false,
        rows: 5,
        size:21,
    },
    _data: {
        type: "not-scheme",
        use19: false,
        use2x: false,
        size: 21,
        salon: {}, // id=>number (проходы - 0, места - номер места)
        cabina: {}, // id=>active
    },
    _dataClear: {
        type: "not-scheme",
        use19: false,
        use2x: false,
        size: 0,
        //cols: 0,
        salon: {},
        cabina: {},
    },
    _optionsClear: {
        type: "not-scheme", // "bigbus" or "micro"
        use19: false,
        use2x: false,
        rows: 0,
        size: 0,
    },
	_mode: "view", // numbering, view, config
	_salon: null,
	_busContainer: null,
    _masterContainer: null,
    _driverRow: null,
    _manualNumbers: null,
    _okBtn: null,

	_create: function()
	{
            var self = this, el = self.element, o = self.options;
            if(!o.size) o.size = 21;
            this._makeData();
            this._busContainer = $("<td name='bus-container'></td>");
            this._masterContainer = $("<td name='master-container' width='380'></td>");
            var table = $("<table></table>");
            var tr = $("<tr></tr>");
            tr.append(this._busContainer);
            tr.append(this._masterContainer);
            table.append(tr);
            el.append(table);
            this._makeMaster();
            this._makeSalon();
	},

        _makeMaster: function()
        {
            var self = this, el = self.element, o = self.options;
            var tabs = $("<div id='bus-configurator-tabs'><ul></ul></div>");

            tabs = this._makeMasterStep1(tabs);
            tabs = this._makeMasterStep2(tabs);

            this._masterContainer.html(tabs);
            $("#bus-configurator-tabs").tabs({
                activate: function( event, ui ) {
                    switch($(ui.newPanel).prop("id")) {
                        case "step-1": return self._prepareStep1();
                        case "step-2": return self._prepareStep2();
                    }
                },
            });

            this._masterContainer.append('<div class="alert alert-danger" id="manualNumbersAlertMessage"></div>');
        },

        _makeMasterStep1: function(tabs)
        {
            var self = this, el = self.element;
            var node = "step-1";
            var content = "Выберите тип автобуса, укажите параметры.";
            content += '<div class="row" id="' + node + '">';
            content += '<div class="col-lg-12">';

            // добавляем новый тип конфигурации автобуса
            content += '<div class="input-group">';
            content += '<span class="input-group-addon"><input name=type type="radio" value="not-scheme"></span>';
            content += '<input class="form-control" value="Нет схемы" disabled>';
            content += '</div>';

            content += '<div class="input-group">';
            content += '<span class="input-group-addon"><input name=type type="radio" value="micro"></span>';
            content += '<input class="form-control" value="Микроавтобус (до 21 места)" disabled>';
            content += '</div>';

            content += '<div class="input-group">';
            content += '<span class="input-group-addon"><input name=type type="radio" value="bigbus"></span>';
            content += '<input class="form-control" value="Автобус (от 20 мест)" disabled>';
            content += '</div>';

            content += '<p>';

            content += '<div class="input-group">';
            content += '<span class="input-group-addon" style="width:250px;text-align:left;">Всего мест в автобусе: </span>';
            content += '<input name=size class="form-control" value="0" type="number" min="5" max="80">';
            content += '</div>';

            content += '<div class="input-group radio">';
            content += '<input class="form-control" value="Дополнительное место" disabled style="width:250px;text-align:left;">';
            content += '<span class="input-group-addon" style="width:65px;"><input name=type type="checkbox" value="use19"></span>';
            content += '</div>';

            content += '<div class="input-group radio">';
            content += '<input class="form-control" value="Места рядом с водителем" disabled style="width:250px;text-align:left;">';
            content += '<span class="input-group-addon" style="width:65px;"><input name=type type="checkbox" value="use2x"></span>';
            content += '</div>';

            content += '<p style="padding-top:10px;">';
            content += "Уберите лишние кресла из салона, кликнув по ним. Повторный клик возвращает кресло.<p>";
            content += "<b>Внимание!</b><br>При расстановке кресел нумерация производится автоматически!";

            content += '</div>';
            content += '</div>';

            tabs.find("ul").append("<li><a href=#" + node + ">Конфигурация салона</a></li>");
            tabs.append("<div id=" + node + "><p>" + content + "</div>");

            $(tabs).find("div[id='"+node+"'] input[type='radio'][value='"+self.options.type+"']").prop("checked", true);

            $(tabs).find("div[id='"+node+"'] input[type='radio']").on("click", function(a){

                if($(this).val()=="micro") {
                    el.mybus("option", {type: $(this).val(), size: 21});
                    $("#"+node+" div.radio").show("slow");
                }
                else if ($(this).val()=="bigbus") {
                    el.mybus("option", {type: $(this).val(), size: 44});
                    $("#"+node+" div.radio").hide("slow");
                }
                else {
                    el.mybus("option", {type: $(this).val(), size: 6});
                    $("#"+node+" div.radio").hide("slow");
                }
                $("#"+node+" input[name='size']").val(el.mybus("option", "size"));
            });

            $(tabs).find("div[id='"+node+"'] input[value='use19']").on("change", function(a){
                        el.mybus("option", {
                            use19: $(this).prop("checked")
                        });
                    });

            $(tabs).find("div[id='"+node+"'] input[value='use2x']").on("change", function(a){
                        el.mybus("option", {
                            use2x: $(this).prop("checked")
                        });
                    });

            $(tabs).find("div[id='"+node+"'] input[name='size']")
                    .on("blur", function(){self._changeNumPlaces(this)})
                    .on("change", function(){self._changeNumPlaces(this)})
                    .on("focus", function(){$(this).select()});

            return tabs;
        },

        _prepareStep1: function()
        {
            console.log("_prepareStep1");
            var self = this, el = self.element;

            this.setMode("config");


            if(self.options.type == "micro")
            {
                $("#step-1 div.radio").show();
            }
            else
            {
                $("#step-1 div.radio").hide();
            }

            $("#step-1 input[type='radio'][value='"+self.options.type+"']").prop("checked", true);
            $("#step-1 input[value='use19']").attr("checked", self.options.use19?true:false);
            $("#step-1 input[value='use2x']").attr("checked", self.options.use2x?true:false);
            $("#step-1 input[name='size']").val(el.mybus("option", "size"));

            this._checkNumbers();
        },

        _changeNumPlaces: function(input)
        {
            var self = this, el = self.element;
            var input = $(input);
            var val = parseInt(input.val());

            if( (val<input.prop("min")) || (val>input.prop("max")) )
            {
                alertMsg("Количество мест должно быть не менее " + input.prop("min") + " и не более " + input.prop("max") + "!", function(){
                    if(val<input.prop("min")) input.val(input.prop("min"));
                    if(val>input.prop("max")) input.val(input.prop("max"));
                    return setTimeout(function(){input.focus()}, 100);
                });
                return false;
            }

            el.mybus("option", {size: val});
        },

        _makeMasterStep2: function(tabs)
        {
            var node = "step-2";
            var content = "При необходимости впишите номера мест вручную. Когда будет все готово, переходите к записи.";
            tabs.find("ul").append("<li><a href=#" + node + ">Ручная нумерация</a></li>");
            tabs.append("<div id=" + node + "><p>" + content + "</div>");
            return tabs;
        },

        _prepareStep2: function()
        {
            console.log("_prepareStep2");
            this.setMode("numbering");
        },

        _getSalonColumns: function(type)
        {
            return (type=="micro")?4:5;
        },

        _getVisibleStat: function()
        {
            var d = this._data, result = {
                salonCount:"0",
                salonLastIndex:"0",
                salonLastValue:"0",
                salonLastRowPos:"0",
                salonLastRowProhodPos:"0",

                cabinaCount:"0",
                cabinaLastIndex:"0",
                cabinaLastValue:"0",
                total:"0"
            };

            for(var i=1; i<=d.salon.maxSize; i++)
                if(d.salon[i])
                {
                    result.salonCount++;
                    result.salonLastIndex = i;
                    result.salonLastValue = d.salon[i];
                }
                else
                {
                    if(d.hiddenSalon[i]) result.salonLastIndex = i;
                    d.salon[i] = 0;
                }

            if(d.use2x)
            for(var i=1; i<=2; i++)
                if(d.cabina[i]!=0)
                {
                    result.cabinaCount++;
                    result.cabinaLastIndex = i;
                    result.cabinaLastValue = d.cabina[i];
                }
                else
                {
                    d.cabina[i] = 0;
                }

            result.total = parseInt(result.cabinaCount) + result.salonCount;

            result.salonLastRowPos = Math.trunc(result.salonLastIndex / d.cols) * d.cols + 1;
            if(result.salonLastRowPos>result.salonLastIndex) result.salonLastRowPos -= d.cols;

            result.salonLastRowProhodPos = result.salonLastRowPos + 2;

            console.log("_getVisibleStat:");
            console.log(result);
            return result;
        },

        _makeData: function()
        {
            var self = this,
                o = self.options,
                d = self._data,
                // мест в салоне без проходов
                standardCnt = o.size - (o.use2x ? 2 : 0),
                cols = this._getSalonColumns(o.type),
                // рядов мест с учетом полного последнего ряда
                rows = Math.ceil((standardCnt-cols)/(cols-1))+1,
                // мест в салоне c проходами
                salonSize = cols*rows;

            d.cols = cols;
//            d.rows = rows;
            d.type = o.type;
            d.use19 = (o.type=="micro")?o.use19:false;
            d.use2x = (o.type=="micro")?o.use2x:false;
            d.size = o.size;
            d.salon = undefined; // id=>number (проходы - 0, места - номер места)
            d.salon = {}; // id=>number (проходы - 0, места - номер места)
            d.cabina = undefined; // id=>active
            d.cabina = {}; // id=>active
            d.hiddenSalon = undefined; // скрытые кликом кресла
            d.hiddenSalon = {}; // скрытые кликом кресла
            d.hiddenCabina = undefined; // скрытые кликом кресла
            d.hiddenCabina = {}; // скрытые кликом кресла
            d.salon.maxSize = salonSize; // полная матрица салона (без кабины)

            var index = 1;
            var checkSize = d.use19?(d.size-1):d.size;
            for(var i=1; i<=d.salon.maxSize; )
            {
                for(j=1; j<=cols; j++)
                {
                    if(index>checkSize) break;
                    d.hiddenSalon[i] = 0;
                    if(d.salon.maxSize - d.cols <= (d.use19 ? (i+1) : i) )
                        d.salon[i] = index++;
                    else
                        d.salon[i] = (j!=3) ? index++ : 0;
                    i++;
                }
                if(index>checkSize) break;
            }

            if(d.use19)
                d.salon[3] = d.size;

            if(d.use2x)
            {
                d.hiddenCabina[1] = 0;
                d.cabina[1] = d.size-2;
                d.hiddenCabina[2] = 0;
                d.cabina[2] = d.size-1;
            }

            console.info("_makeData:");
            console.info(d);
           this._correctData();
        },

        _renumberPlaces: function()
        {
            var self = this,
                d = self._data,
                hideInSalon = 0,
                newNum = 1;

            if(d.use2x) hideInSalon += 2;
            // упорядочить номера видимых кресел
            for(var i=1; i<=d.salon.maxSize; i++)
            {
                if(i==3) continue; // use19
                if(newNum <= d.size-hideInSalon)
                {
                    if(d.salon[i])
                        d.salon[i] = newNum++;
                }
                else d.hiddenSalon[i] = d.salon[i] = 0; // занулить хвостовые
            }

            if(d.use2x)
                for(var i=1; i<=2; i++)
                {
                    if(d.cabina[i]>0)
                        d.cabina[i] = newNum++;
                }

            if(d.use19)
                if(d.salon[3]>0)
                    d.salon[3] = newNum;
        },

        _correctData: function()
        {
            var self = this, d = self._data;
            var stat = this._getVisibleStat();

            // в салоне всегда должно быть d.size видимых кресел
            if(stat.total < d.size)
            {
                while(stat.total < d.size)
                {
                    d.salon[stat.salonLastIndex + 1] = stat.salonLastValue + 1;
                    d.hiddenSalon[stat.salonLastIndex + 1] = 0;
                    if(stat.salonLastIndex + 1 > d.salon.maxSize) d.salon.maxSize += d.cols;
                    stat = this._getVisibleStat();
                }
            }
            else if(stat.total > d.size)
            {
                while(stat.total > d.size)
                {
                    d.salon[stat.salonLastIndex] = 0;
                    d.hiddenSalon[stat.salonLastIndex] = 0;
                    stat = this._getVisibleStat();
                }
            }

            // упорядочить номера видимых кресел
            this._renumberPlaces();
            var stat = this._getVisibleStat();

            // В пред-последнем ряду д.б. проход
      // console.log(d.salon);
      // console.log(d.hiddenSalon);
            var predLastRowProhodPos = stat.salonLastRowProhodPos - d.cols;
            if(parseInt(d.salon[predLastRowProhodPos])>0)
            {
                d.hiddenSalon[predLastRowProhodPos] = d.salon[predLastRowProhodPos];
                d.salon[predLastRowProhodPos] = 0;
                return this._correctData();
            }

            // Последний ряд д.б. без прохода!!!
            // Если в последнем ряду только первое кресло, то в предыдущем ряду
            // разместить среднее и убрать последний ряд

            // В этом случае упаковать предпоследний ряд и убрать последний
            if( (parseInt(d.salon[stat.salonLastRowProhodPos])==0) &&
                (parseInt(d.hiddenSalon[stat.salonLastRowProhodPos])==0) &&
                (stat.salonLastRowProhodPos<d.salon.maxSize) )
            {
                for(var i=stat.salonLastRowProhodPos; i<d.salon.maxSize; i++)
                {
                    d.salon[i] = d.salon[i+1];
                    d.hiddenSalon[i] = d.hiddenSalon[i+1];
                }
                d.salon[i] = 0;
                d.hiddenSalon[i] = 0;
            }
            // отрезать лишние задние ряды
            var stat = this._getVisibleStat();

            var start = stat.salonLastRowPos + d.cols;
            while(start <= d.salon.maxSize)
            {
                d.salon[d.salon.maxSize] = undefined;
                delete d.salon[d.salon.maxSize];
                d.hiddenSalon[d.salon.maxSize] = undefined;
                delete d.hiddenSalon[d.salon.maxSize];
                d.salon.maxSize -= 1;
            }

            console.info("_correctData:");
            //console.info(d);
        },

        _makeSalon: function (tabs)
        {
            console.log("### bus.js :: _makeSalon()");
            var self = this, el = self.element, d = self._data;

            this._busContainer.empty();
            this._salon = $("<div id=the_bus_salon><p style='display: none'>Нет</br>схемы</p></div>")
            .css({
                    backgroundColor: "lightgray",
                    border: "#000 solid 1px",
                    "padding": "2px",
                    height: (self.options.height*1 + 1) * (d.rows*1+1) + "px",
                    display: "table",
                    "border-collapse": "unset !important",
                    "min-width": "100px",
                    "border-radius": "10px"
                });

            this._driverRow = $("<div></div>")
            .css({
                    height: (self.options.height*1 + 1)+ "px",
                    display: "table-row",
                    "white-space": "nowrap"
                    });
            this._salon.append(this._driverRow);
            this._driverRow.append(this._makePlace({num:"B", "margin-left": "20px", "margin-top": "10px"}));

            this._makePlaces();
            this._busContainer.append(this._salon);

            if ( self.options.type == "not-scheme" ) {
                this._setNotScheme();
            } else {
                this._unSetNotScheme();
            }
        },

        _makePlaces: function()
        {
            var self = this,
                el = self.element,
                o = self.options,
                d = self._data,
                activeCnt = 0,
                lastRowValue = 0,
                i = 1;
            var stat = this._getVisibleStat();
            for(i=1; i<=d.salon.maxSize; /*empty!*/)
            {
                var row = $("<div></div>")
                    .css({
                        height: (o.height*1 + 1)+ "px",
                        display: "table-row",
                        "white-space": "nowrap"
                });

                for(var k=1; k<=d.cols; k++)
                {
                    var isOutBound = activeCnt > stat.salonCount;
                    var isActive = ((d.salon[i]>0)?1:0);
                    var isHidden = (d.hiddenSalon[i] != 0);
                    var isProhod = ( (k==3) && (i<d.salon.maxSize-d.cols-1));
//                console.log("i="+i+", activeCnt="+activeCnt);
                    var isUse19 = ( (i==3) && (d.type=="micro") && d.use19 );
                    activeCnt += (isActive && !isHidden?1:0);
                    var isPlace = (isActive || isHidden);
                    var place = this._makePlace({prohod:!isActive, clickable: ((isPlace && !isProhod && !isOutBound) || isUse19)});

                    place.attr("name", i).text((d.salon[i]==0)?"":d.salon[i]);

                    if( (i==3) && (d.type=="micro") && d.use19 ) place.addClass("use19");

                    row.append(place);
                    i++;
                }

                this._salon.append(row);
            }

            if( (d.type=="micro") && d.use2x )
            {
                for(j=1; j<=2; j++)
                {
                    var isActive = ((d.cabina[j]>0)?1:0);
                    var isHidden = d.hiddenCabina[j] != 0;
                    activeCnt += (isActive && !isHidden?1:0);
                    var place = this._makePlace({"margin-left": (j==1)?"13px":"0", prohod:!isActive, clickable:true});
                    place
                    .attr("name", j)
                    .text((d.cabina[j]==0)?"":d.cabina[j])
                    .addClass("use2x");

                    this._driverRow.append(place);
                }
            }
        },

	_makePlace: function(o)
        {
            var self = this,
            result = $("<div class='bus_place'></div>")
                .text(o.num)
                .css({
                       width: "30px",
                       height: "30px",
                       display: "inline-block",
                       "vertical-align": "middle",
                       "text-align": "center",
                       "border-radius": "7px",
                       "margin-left": o["margin-left"]?o["margin-left"]:"1px",
                       "margin-top": o["margin-top"]?o["margin-top"]:"10px",
                       cursor: o.clickable?"pointer":"default"
                })
               .addClass(o.clickable?"clickable":"");

            if(o.prohod == true)
                result.addClass("prohod");
            else
                result.addClass("place_active");

            if(o.clickable)
                this._on( result, {click: "placeClickEdit"});

            return result;
	},

        placeClickEdit: function(a)
        {
            if (this._mode != "config") return;

            var place = $(a.target);
            if( place.hasClass("place_active") ) this._hidePlace(place);
            else this._showPlace(place);
            this._checkNumbers();
	},

        _hidePlace: function(place)
        {
            var d = this._data;
            var id = parseInt(place.attr("name"));

            if( place.hasClass("use2x") )
            {
                d.hiddenCabina[id] = d.cabina[id];
                d.cabina[id] = 0;
            }
            else
            {
                d.hiddenSalon[id] = d.salon[id];
                d.salon[id] = 0;
            }

            this._correctData();
            this._makeSalon();
        },

        _showPlace: function(place)
        {
            var d = this._data;
            var id = place.attr("name");

            if( place.hasClass("use2x") )
            {
                this._data.cabina[id] = this._data.hiddenCabina[id];
                this._data.hiddenCabina[id] = 0;
            }
            else
            {
                this._data.salon[id] = this._data.hiddenSalon[id];
                this._data.hiddenSalon[id] = 0;
            }

            this._correctData();
            this._makeSalon();
        },

        _refresh: function()
        {
            console.log("_refresh");
            var self = this, saveIndex = 1;
            // убираем надписи с кресел
            this._salon.find("div.bus_place.clickable").text("");

            // Основной салон без пристяжного кресла
            this._salon.find("div.bus_place.clickable.place_active")
            //.not(".use19")
            .not(".use2x")
            .each(function(index, value)
            {
                var val = self._data.salon[$(this).attr("name")];
                switch(self._mode)
                {
                    case "numbering": showText = "<input value='"+val+"' class=bus-place-number-input>"; break;
                    default: showText = val; break;
                }
                $(this).html(showText);
            });

            // Места рядом с водителем
            this._salon.find("div.bus_place.clickable.place_active.use2x")
            .each(function(index, value)
            {
                var val = self._data.cabina[$(this).attr("name")];
                switch(self._mode)
                {
                    case "numbering": showText = "<input value='"+val+"' class=bus-place-number-input>"; break;
                    default: showText = val; break;
                }
                $(this).html(showText);
            });

            $("input.bus-place-number-input")
               .on("blur", function(){self._blurNumberInput(this)})
               .on("focus", function(){$(this).select();});
        },

        _blurNumberInput: function(a)
        {
            var input = $(a);
            var place = input.parent();
            var val = input.val();
            var index = place.attr("name");
            if(place.hasClass("use2x"))
                this._data.cabina[index] = val;
            else
                this._data.salon[index] = val;
            this._checkNumbers();
        },


        ready: function()
        {
            return this._checkNumbers();
        },

        _checkNumbers: function()
        {
            this.element.find("#manualNumbersAlertMessage").hide();

            var allRight = false;
            var all = [], allIndex = 0, checks = [];

            $.each(this._data.salon, function(index, value)
            {
                if(isNaN(parseInt(index))) return;
                value = parseInt(value);
                if(!isNaN(value) && (value>0)) all[allIndex++] = value;
            });

            $.each(this._data.cabina, function(index, value)
            {
                if(isNaN(parseInt(index))) return;
                value = parseInt(value);
                if(!isNaN(value) && (value>0)) all[allIndex++] = value;
            });

            for(var i = 0; i<this.options.size; i++)
                checks[i] = i+1;

            $.each(all, function(allIndex, allValue) {
                if(isNaN(parseInt(allIndex)))
                    return;

                $.each(checks, function(index, value) {
                    if(parseInt(allValue) === parseInt(value))
                    {
                        checks.splice(index,1);
                        return false;
                    }
                });
            });

            if(checks.length == 0)
            {
                this._okBtnShow(true, "");
                return true;
            }
            else
            {
                var numbers = "";
                $.each(checks, function(index, value) {
                      numbers += (numbers?", ":" ") + value;
                    });
                this._okBtnShow(false, "Отсутствуют номера: " + numbers + ".");
            }
            return false;
        },

        getJson: function()
        {
            var data = {
                    callback: "v0",
                    options: $.extend({}, this.options),
                    data: $.extend({}, this._packData(this._data))
            };

            return JSON.stringify(data);
        },

        setJson: function(json)
        {
            console.log("setJson:", json);
            let self = this;
            let data = {};

            let inputJson = JSON.parse(json.toString());
            data = this._getDataForNonScheme(inputJson);

            let jsonData = $.extend(data, inputJson);

            if(jsonData.options)
                this.options = $.extend(this.options, jsonData.options);
            if(jsonData.data)
                this._data = $.extend({}, this._data, jsonData.data);

            $.each(this._data.salon, function(i, v){
               self._data.hiddenSalon[i]=(parseInt(v)===0) ? 1 : 0;
            });

            $.each(this._data.cabina, function(i, v){
               self._data.hiddenCabina[i]=(parseInt(v)===0) ? 1 : 0;
            });

            if (!$.isEmptyObject(inputJson)){
                this._makeSalon();
            } else {
                $("#step-1 input[type='radio'][value='"+self.options.type+"']").prop("checked", true);
            }

            let t = $("#bus-configurator-tabs");
            if(t.tabs("option", "active") == 0)
                self._prepareStep1();
            else
                t.tabs("option", "active", 0);
        },

        _packData: function(data)
        {
            var d = $.extend({}, this._data);
            delete d.hiddenCabina;
            delete d.hiddenSalon;
            return d;
        },

        _setOptions: function()
        {
            console.log("_setOptions()");
            this._superApply( arguments );
            /// Обработка группы опций ниже (если передано сразу несколько)
            this._makeData();
            this._makeSalon();
            this._checkNumbers();
       },

        _setOption: function( key, value )
        {
            this._super( key, value ); // это запись в this.options
            /// Обработка единичной опции по key
        },

        setMode: function(mode)
        {
            console.log("setMode()");
            if(mode == this._mode) return;
            // numbering, view, config
            if( (mode != "view") && (mode != "numbering") && (mode != "config") )
                this._mode = "view";
            else
                this._mode = mode;

            if(this._mode == "config")
            {
                this._makeSalon();
            }
            else
                this._refresh();
        },

        getMode: function()
        {
            return this._mode;
        },

        setOkBtnId: function(id)
        {
            console.log("### bus.js :: setOkBtnId()");
            console.log("id: ", id);
            this._okBtn = $("#"+id);
            this._okBtn.hide();
        },

        okBtnShow: function()
        {
            console.log("### bus.js :: okBtnShow()");
            this._okBtn.show();
        },

        _okBtnShow: function(value, msg)
        {
            console.log("### bus.js :: _okBtnShow()");
            console.log("this._okBtn: ", this._okBtn);
            if(this._okBtn)
            {
                var errorDiv = this.element.find("#manualNumbersAlertMessage");
                if(value)
                {
                    this._okBtn.show();
                    errorDiv.hide();
                }
                else
                {
                    this._okBtn.hide();
                    if(msg !== "") errorDiv.show().html(msg);
                }
            }
        },

        getSize: function()
        {
            return this._data.size;
        },

        _getDataForNonScheme: function (inputJson) {
            console.log("### bus.js :: _getDataForNonScheme");
            console.log("inputJson: ", inputJson);
            let data = {};
            if ($.isEmptyObject(inputJson)) {
                data = {
                    callback: "v0",
                    options: $.extend({}, this._optionsClear),
                    data: $.extend({}, this._dataClear)
                };
                this._setNotScheme();
            } else {
                data = {
                    callback: "v0",
                    options: $.extend({}, this.options),
                    data: $.extend({}, this._data)
                };

            }
            return data;
        },

        _setNotScheme: function () {
        console.log("### bus.js :: _setNotScheme");
            $("#bus-configurator-tabs").tabs({
                disabled: [1]
            });
            $("#the_bus_salon").hide();
            $(".no-scheme").hide();
            $("#step-1 .input-group.radio input").prop("disabled", true);
            if ( $(".no-scheme").length == 0 ) {
                $("td[name='bus-container']").append("<p class='no-scheme'>Нет</br>схемы</p>")
            } else {
                $(".no-scheme").show();
            }

            $("#step-1 input[name='size']").val(this.options.size);
            $("#step-1 div.radio").hide("slow");
            this.element.find("#manualNumbersAlertMessage").hide();
        },

        _unSetNotScheme: function () {
        console.log("### bus.js :: _unSetNotScheme");
            $("#bus-configurator-tabs").tabs({
                disabled: []
            });
            $("#the_bus_salon").show();
            $(".no-scheme").hide();
            $("#step-1 .input-group.radio input").prop("disabled", false);
        }
});

})(jQuery);
