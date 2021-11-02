(function($) {

    $.widget("ui.sortableEndpoints", {

        titleAppend: "Добавить...",
        titleSelect: "Выбрать пункт...",
        options: {
            modal: null,
            config: null,
            showCity: true,
            onlyOneCity: true,
        },
        container: null,
        modal: null,
        ul: null,
        baseName: '',
        showCity: true,
        onlyOneCity: true,
        indexIncrement: 1,

	_create: function()
	{
            var self = this, el = self.element, o = self.options;
            el.val('');
            self.baseName = $(el).attr("name");
            self.container = $(el).parent(); // div
            self.modal = o.modal; // modal dlg
            self.showCity = o.showCity; // show city in caption
            self.onlyOneCity = o.onlyOneCity; // clear list on change city

            self.container
            .append("<input type='hidden' id='"+self.baseName+"-id' value='0:'>")
            .append("<ul id='"+self.baseName+"-list' class='form-control points-list'></ul>");

            self.ul = self.container.find("ul");
            self._initFromJson(o.config);

            // Sortable init
            self.ul.sortable({
                placeholder: "ui-state-highlight",
                update: function( event, ui ) {
                    self._resort();
                },
            });
            self.ul.disableSelection();

            // блок "+"
            var li = $("<li class='add'></li>")
            .append("<button type='button' class='close add'>&plus;</button>")
            .append("<span class='glyphicon glyphicon-plus'></span>&nbsp;")
            .append("<button type='button' class='add-point'>"+self.titleAppend+"</button>")
            .on("click", function(){
                var hidden = self.container.find("#"+self.baseName+"-id");
                var data = hidden.val();
                var id_city = "";
                var id_country = "";
                var name = "";

                if(self.onlyOneCity)
                {
                    var li_data = self.ul.find(":hidden.cls-data").first();
                    if(li_data.length)
                    {
                        li_data     = li_data.val().split(":");
                        id_city     = li_data[0]?li_data[0]:0;
                        name        = li_data[1]?li_data[1]:"";
                        id_country  = li_data[4]?li_data[4]:0;
                    }
                }

                var w = modalTpl({
                    id: 'modal-map-points',
                    title: "Выбор города и остановок в нем",
                    class: 'modal-lg',
                    "submit-text": "Выбрать!",
                    "close-text": "Отмена",
                    "ajax-url": "/account/points",
                    "ajax-data": {
                        action: 'modal-map-points',
                        mode: 'select',
                        only_one_city: self.onlyOneCity,
                        id_city: id_city,
                        id_country: id_country,
                        name: name,
                    },
                    "submit-function": function(){
                        var selected = $("#modal-map-points").data("selected");
                        if(!selected)
                            alertMsg("Ничего не выбрано!");
                        else
                        {
                            var splitted = [];
                            splitted[0] = selected.id_city;
                            splitted[1] = selected.city;
                            splitted[2] = selected.id;
                            splitted[3] = selected.point;
                            splitted[4] = selected.id_country;
                            splitted[5] = "";
                            var newval = splitted.join(":");
                            self.ul.append(self._templateBlock(newval));
                            self._resort();
                            confirmMsg("Добавлено!<p>Выбрать еще одну остановку?", false, function(){
                                $("#modal-map-points").modal("hide");
                            });
                        }
                        return false;
                    } // submit
                }).appendTo($("body"));
                w.modal("show");
            });
            self.ul.append(li);

            el.hide();
	},

        _resort: function()
        {
            var self = this, el = self.element, o = self.options;
            $.each(self.ul.find("li:not(.add)"), function(index, li) {
               $(li).attr("name", self.baseName + "-" +(index+1));
               $(li).find("span.select-point > span.badge").text(index+1);
               $(li).find("input.cls-data").attr("name",  self.baseName+"-arr["+index+"][data]");
               $(li).find("input.cls-time").attr("name",  self.baseName+"-arr["+index+"][time]");
            });
            self.ul.find("li.add").insertAfter(self.ul.find("li:not(.add):last"));
        },

        initFromJson: function(config)
        {
            var self = this;
            self.ul.find("li:not(.add)").remove();
            self.container.find("#"+self.baseName+"-id").first().val("0:");

            self._initFromJson(config);
        },

        _initFromJson: function(config)
        {
            if(!config) return;
            if(!config[0]) return;
            if(!config[0]["data"]) return;
            var self = this;
            var hidden = self.container.find("#"+self.baseName+"-id").first();
            var splitted = config[0]["data"].split(":");
            hidden.val(splitted[0]+":"+splitted[1]+":0::"+splitted[4]);

            $.each(config, function(prop,val){
                    var data = val["data"]+":"+(val["time"]?val["time"]:"");
                    self.ul.append(self._templateBlock(data));
            });
            self._resort();
        },

	_templateBlock: function(data)
        {
            var self = this, el = self.element, o = self.options;
            var splitted = data.split(":");
            var title = (self.showCity?(splitted[1]+" - "):"") + (splitted[3]?splitted[3]:self.titleSelect);
          // Генерация нового блока для остановки
            var li = $("<li id='li-"+self.baseName+"-"+(self.indexIncrement)+"' name='"+self.baseName+"-0'></li>");
            self.indexIncrement += 1;
            li.append("<input class='cls-data' type=hidden value='"+data+"'>" +
                      "<span class='select-point'>" +
                      "<span class='glyphicon glyphicon-sort'></span>" +
                      "<span class='badge'></span>" +
                      "<span class=title>"+title+"</span>" +
                      "<input class='input-v cls-time' value='"+(splitted[5]?splitted[5]:"")+"'>" +
                      "<span class='fa fa-question-circle-o float-right' title='Время в пути в минутах, отсчитываемое от первой остановки маршрута'>  В пути, минут:</span>" +
                      "</span>" +
                      "<button type='button' class='close'>&times;</button>"
                      );

            li.find( "button.close" ).on("click", function(){
                var lis = self.ul.find("li:not(.add)");
                if( o.onlyOneCity && (lis.length==1) ) return;

                $(this).parent().remove();
                self._resort();
            });

            li.find( "span.title" )
                    .css({"cursor":"pointer"})
              .on("click", function(){
                var hidden = $(this).closest("li").find(":hidden");
                var li_title = $(this);
                var li_data     = hidden.val().split(":");

                var id_city     = li_data[0]?li_data[0]:0;
                var name        = li_data[1]?li_data[1]:"";
                var id_country  = li_data[4]?li_data[4]:0;

                var w = modalTpl({
                    id: 'modal-map-points',
                    title: "Выбор города и остановок в нем",
                    class: 'modal-lg',
                    "submit-text": "Выбрать!",
                    "close-text": "Отмена",
                    "ajax-url": "/account/points",
                    "ajax-data": {
                        action: 'modal-map-points',
                        mode: 'select',
                        only_one_city: self.onlyOneCity,
                        id_city: id_city,
                        id_country: id_country,
                        name: name,
                    },
                    "submit-function": function(){
                        var selected = $("#modal-map-points").data("selected");
                        if(!selected)
                            alertMsg("Ничего не выбрано!");
                        else
                        {
                            var splitted = [];
                            splitted[0] = selected.id_city;
                            splitted[1] = selected.city;
                            splitted[2] = selected.id;
                            splitted[3] = selected.point;
                            splitted[4] = selected.id_country;
                            splitted[5] = "";
                            var newval = splitted.join(":");
                            hidden.val(newval);
                            li_title.text(selected.city + ' - ' + selected.point);
                            li_title.closest("li").find("input.cls-time").val('');
                            self._resort();
                            successMsg("Выполнено!<p>", function(){
                                $("#modal-map-points").modal("hide");
                            });
                        }
                        return false;
                    } // submit
                }).appendTo($("body"));
                w.modal("show");
            });

            li.find( "input.cls-time" ).mask("00000", {placeholder: "0", selectOnFocus: true});
            li.find("span.select-point > span.badge").addClass("selected");
            return li;
        },

        getLiSetup: function(li) // return selected city
        {
            var self = this;
            if(li) // li - container
                return $(li).find("input:hidden").val();
            else
                return self.container.find("input:hidden").first().val();
        },

        setLiSetup: function(liName, data) // set up selected li-block
        {
            var self = this;
//            console.info("setLiSetup: " + data);
            splitted = data.split(":");
            var container = self.container.find("#"+liName).first();
            container.find("input:hidden").first().val(data);
            container.find("button.select-point").first().text( (self.showCity?(splitted[1]+" - "):"") + splitted[3] ).addClass("selected");
            container.find("span.select-point > span.badge").addClass("selected");

        },

        getConfig: function()
        {
            var self = this, a = [];
            $.each(self.container.find("li:not(.add)"), function(i, li){

                var hidden = $(li).find("input:hidden").first();
                var time = $(li).find("input.cls-time").first();

                hidden = $(hidden).val().split(":");
                hidden[5] = $(time).val();
                hidden = hidden.slice(0,6);

                var r = {
                    "data": hidden.join(":"),
                    "time": hidden[5],
                };
                a.push(r);
            })
            return a;
        }

});

})(jQuery);