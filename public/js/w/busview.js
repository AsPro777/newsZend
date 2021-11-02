(function($) {

    $.widget("ui.busview", {
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
            var table = $("<table></table>");
            var tr = $("<tr></tr>");
            tr.append(this._busContainer);
            table.append(tr);
            el.append(table);
            this._makeSalon();    
	},
        
        _getSalonColumns: function()
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
console.log(1);
            var stat = this._getVisibleStat();
            
            // в салоне всегда должно быть d.size видимых кресел
            if(stat.total < d.size)
            {
                while(stat.total < d.size)
                {
                    d.salon[stat.salonLastIndex + 1] = stat.salonLastValue + 1;
                    d.hiddenSalon[stat.salonLastIndex + 1] = 0;
                    if(stat.salonLastIndex + 1 > d.salon.maxSize) d.salon.maxSize += d.cols;
console.log(2.1);
                    stat = this._getVisibleStat();
                }
            } 
            else if(stat.total > d.size)
            {
                while(stat.total > d.size)
                {
                    d.salon[stat.salonLastIndex] = 0;
                    d.hiddenSalon[stat.salonLastIndex] = 0;
console.log(2.2);
                    stat = this._getVisibleStat();
                }
            }
                
            // упорядочить номера видимых кресел
            this._renumberPlaces();
console.log(3);
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
console.log(4);
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
            console.info(d);
        },
        
        _makeSalon: function (tabs)
        {
            var self = this, el = self.element, d = self._data;

            this._busContainer.empty();
            this._salon = $("<div id=the_bus_salon></div>")
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
                    var isActive = ((d.salon[i]>0)?1:0);
                    var isHidden = (d.hiddenSalon[i] != 0);
                    var isProhod = ( (k==3) && (i<d.salon.maxSize-d.cols) );
                    var isUse19 = ( (i==3) && (d.type=="micro") && d.use19 );
                    activeCnt += (isActive && !isHidden?1:0);
                    var isPlace = (isActive || isHidden);
                    var place = this._makePlace({prohod:!isActive, clickable: ((isPlace && !isProhod) || isUse19)});
                    place
                    .attr("name", i)
                    .text((d.salon[i]==0)?"":d.salon[i]);
            
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
        },
                              
        setJson: function(json)
        {        	
            var self = this;
            var data = {
              callback: "v0",
              options: $.extend({}, this.options),
              data: $.extend({}, this._data)
            };
            jsonData = $.extend(data, JSON.parse(json.toString()));
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
