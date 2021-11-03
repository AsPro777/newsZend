(function($) {

    $.widget("ui.mybusview", {
	options: {
            type: "bigbus", // "bigbus" or "micro"
            use19: false,
            use2x: false,
			rows: 5,
			size:21,
		},
        _data: {
            type: "bigbus",
            use19: false,
            use2x: false,
            size: 21,
            salon: {}, // id=>number (проходы - 0, места - номер места)
            cabina: {}, // id=>active
        },
	_salon: null,
	_busContainer: null,
        _driverRow: null,

	_create: function()
	{
//console.log("_create");
            var self = this, el = self.element, o = self.options;
            if(!o.size) o.size = 21;
            this._makeData();
            this._busContainer = $("<td name='bus-container'></td>");
            var table = $("<table></table>").css({"border-collapse":"inherit"});
            var tr = $("<tr></tr>");
            tr.append(this._busContainer);
            table.append(tr);
            el.append(table);
            this._makeSalon();
	},

        _getSalonColumns: function(type)
        {
//            console.log("_getSalonColumns");
            return (type=="micro")?4:5;
        },

        _getVisibleStat: function()
        {
//            console.log("_getVisibleStat");
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

            return result;
        },

        _makeData: function()
        {
//            console.log("_makeData");
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

           this._correctData();
        },

        _renumberPlaces: function()
        {
//            console.log("_renumberPlaces");
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
//            console.log("_correctData");
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
        },

        _makeSalon: function (tabs)
        {
//            console.log("_makeSalon");
            var self = this, el = self.element, d = self._data;

            this._busContainer.empty();
            this._salon = $("<div id=the_bus_salon></div>")
            .css({
//                    backgroundColor: "lightgray",
                    border: "rgb(242, 231, 231) solid 1px",
                    "padding": "2px",
                    height: (self.options.height*1 + 1) * (d.rows*1+1) + "px",
                    display: "inline-block",
                    "border-collapse": "unset !important",
                    "min-width": "100px",
                    "border-radius": "10px"
                });

            this._driverRow = $("<div></div>").css({height: (self.options.height*1 + 1)+ "px", display: "table-row", "white-space": "nowrap"});
            this._salon.append(this._driverRow);
            this._driverRow.append(this._makePlace({num:"B", "margin-left": "20px", "margin-top": "10px"}));

            if (this.options.type !== "not-scheme") {
            this._makePlaces();
            } else {
                this._makePlacesForNotScheme();
            }

            this._busContainer.append(this._salon);
        },

        _makePlaces: function()
        {
//            console.log("_makePlaces");
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

        _makePlacesForNotScheme: function()
        {
//            console.log("_makePlacesForNotScheme");
            var self = this,
                el = self.element,
                o = self.options,
                d = self._data,
                pl = 1;
            var stat = this._getVisibleStat();
            for(let i=0; i <= Math.ceil(o.size/6); i++)
            {
                let row = $("<div></div>").css({height: (o.height*1 + 1)+ "px", display: "table-row", "white-space": "nowrap"});
                for (let j=0; j <= 4; j++) {
                    if ( pl <= o.size ) {
                        var place = this._makePlace({prohod:false, clickable: true, vacant: true});
                        place.attr("name", i).text(pl);
                        row.append(place);
                        pl++;
                    }
                }

                this._salon.append(row);
            }
        },

	_makePlace: function(o)
        {
//            console.log("_makePlace");
            var self = this,
            result = $("<div class='bus_place'></div>")
                .text(o.num)
                .css({
                       width: "25px",
                       height: "25px",
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

//            if(o.clickable)
//                this._on( result, {click: "placeClickEdit"});

            return result;
	},

//        placeClickEdit: function(a)
//        {
//            console.log("placeClickEdit");
//
//            var place = $(a.target);
//            console.log(place.text());
//            if( place.hasClass("place_active") ) this._hidePlace(place);
//            else this._showPlace(place);
//            this._checkNumbers();
//	},

//        _hidePlace: function(place)
//        {
//            console.log("_hidePlace");
//            var d = this._data;
//            var id = parseInt(place.attr("name"));
//
//            if( place.hasClass("use2x") )
//            {
//                d.hiddenCabina[id] = d.cabina[id];
//                d.cabina[id] = 0;
//            }
//            else
//            {
//                d.hiddenSalon[id] = d.salon[id];
//                d.salon[id] = 0;
//            }
//
//            this._correctData();
//            this._makeSalon();
//        },
//
//        _showPlace: function(place)
//        {
//            console.log("_showPlace");
//            var d = this._data;
//            var id = place.attr("name");
//
//            if( place.hasClass("use2x") )
//            {
//                this._data.cabina[id] = this._data.hiddenCabina[id];
//                this._data.hiddenCabina[id] = 0;
//            }
//            else
//            {
//                this._data.salon[id] = this._data.hiddenSalon[id];
//                this._data.hiddenSalon[id] = 0;
//            }
//
//            this._correctData();
//            this._makeSalon();
//        },

        setJson: function(json)
        {
//console.log("setJson", json);
            var self = this;
            var data = {
              callback: "v0",
              options: $.extend({}, this.options),
              data: $.extend({}, this._data)
            };
            jsonData = $.extend(data, JSON.parse(JSON.parse(json)));
//console.log("jsonData", jsonData);
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
            this._makeSalon();
        },

});

})(jQuery);