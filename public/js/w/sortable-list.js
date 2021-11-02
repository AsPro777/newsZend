(function($) {

    $.widget("ui.sortableList", {
        
        titleAppend: "Добавить...",
        options: {
            config: null,
        },
        container: null,
        ul: null,
        baseName: '',
        
	_create: function() 
	{
            var self = this, el = self.element, o = self.options;
            el.val('').hide();
            self.baseName = $(el).attr("name");
            self.container = $(el).parent(); // div

            self.container
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
                self.ul.append(self._templateBlock(":"));
                self._resort();
            });
            self.ul.append(li);
	},
        
        _resort: function() 
        {
            var self = this, el = self.element, o = self.options;
            $.each(self.ul.find("li:not(.add)"), function(index, li) {
               $(li).attr("name", self.baseName + "-" +(index+1));
               $(li).find("input.cls-data").attr("name",  self.baseName+"-arr["+index+"][data]");
            });                     
            self.ul.find("li.add").insertAfter(self.ul.find("li:not(.add):last"));
        },    
        
        initFromJson: function(config) 
        {
            var self = this;
            self.ul.find("li:not(.add)").remove();
            self.container.find("#"+self.baseName+"-id").first().val("");

            self._initFromJson(config);
        },

        _initFromJson: function(config)
        {
            if(!config || !config.length) return;
            var self = this;
            var hidden = self.container.find("#"+self.baseName+"-id").first();            
            var splitted = config[0]["data"].split(":");
            hidden.val(splitted[0]+":"+splitted[1]);

            $.each(config, function(prop,val){                
                    var data =  val["data"]+":"
                                +(val["time"]?val["time"]:"")+":"
                                +(val["cost"]?val["cost"]:"")+":"
                                +(val["in"]?val["in"]:"")+":"
                                +(val["out"]?val["out"]:"")+":"                    
                    self.ul.append(self._templateBlock(data));
            });
            self._resort();
        },
        
	_templateBlock: function(data)	
        {
            var self = this, el = self.element, o = self.options;
            var splitted = data.split(":");
            var sign = "minus";
            if(splitted[1]>0) sign="plus";
            var cost = Math.abs(splitted[1]) * ((sign=="minus")?-1:1);
          // Генерация нового блока для остановки
            var li = $("<li name='"+self.baseName+"-0'></li>");            
            li.append("<input class='cls-data' type=hidden value='"+data+"'>");
            li.append("<span class='glyphicon glyphicon-sort'> </span>");
            
            var span = $("<span></span>");
            var input_name = $("<input class='cls-name' placeholder='Наименование' value='"+splitted[0]+"'>");
            var sign = $("<i class='fa fa-"+sign+"-circle cls-sign' aria-hidden=true> </i>");
            
            var input_cost = $("<input class='cls-cost'>");
            input_cost.mask( ((sign=="minus")?"-":"") + "00", {placeholder: "0", selectOnFocus: true});
            input_cost.val(cost);
            
            span.append(input_name).append(" : ").append(sign).append(input_cost).append("%");
            li.append(span);
            li.append("<button type='button' class='close'>&times;</button>");

            input_name
            .on("keyup", function(){                                    
                var li = $(this).parent().parent();
                var h = li.find("input.cls-data").first();
                $(h).val( li.find("input.cls-name").val() + ":" + li.find("input.cls-cost").val() );
              })
            .on("blur", function(){                    
                var li = $(this).parent().parent();
                var cost = Math.abs(li.find("input.cls-cost").val());
                if(li.find("i.cls-sign").hasClass("fa-minus-circle")) cost = -1 * cost;
                li.find("input.cls-cost").val(cost);
                li.find("input.cls-data").val( li.find("input.cls-name").val() + ":" + cost );
              });
              
            input_cost
            .on("keyup", function(){
                var li = $(this).parent().parent();
                var h = li.find("input.cls-data").first();
                $(h).val( li.find("input.cls-name").val() + ":" + li.find("input.cls-cost").val() );
              })
            .on("blur", function(){                    
                var li = $(this).parent().parent();
                var cost = Math.abs(li.find("input.cls-cost").val());
                if(li.find("i.cls-sign").hasClass("fa-minus-circle")) cost = -1 * cost;
                li.find("input.cls-cost").val(cost);
                li.find("input.cls-data").val( li.find("input.cls-name").val() + ":" + cost );
              });
                                      
            sign
            .css({"cursor":"pointer"})
            .on("click", function(){                
                if($(this).hasClass("fa-minus-circle")) {
                    $(this).removeClass("fa-minus-circle").addClass("fa-plus-circle");
                    $(this).parent().parent().find("input.cls-cost").last().mask("00", {placeholder: "0", selectOnFocus: true});                    
                } else {
                    $(this).removeClass("fa-plus-circle").addClass("fa-minus-circle");
                    $(this).parent().parent().find("input.cls-cost").last().mask("-00", {placeholder: "0", selectOnFocus: true}); 
                }
                $(this).parent().parent().find("input.cls-cost").trigger("blur");
            });
            
            li.find( "button.close" ).on("click", function(){
                    $(this).parent().remove();
                    self._resort();
            });
            return li;
        },
               
        setLiSetup: function(liName, data) // set up selected li-block
        {
            var self = this;
//            console.info("setLiSetup: " + data);
            splitted = data.split(":");
            var container = self.container.find("li[name='"+liName+"']").first();
            container.find("input:hidden").first().val(data);            
        },
        
        getConfig: function()
        {
            var self = this, a = [];            
            $.each(self.container.find("li:not(.add)"), function(i, li){                
                
                var hidden = $(li).find("input:hidden").first();
                
                hidden = $(hidden).val().split(":");
                hidden = hidden.slice(0,2);

                var r = {
                    "data": hidden.join(":"),
                };
                a.push(r);
            })
//            console.log(a);
            return a;
        }
        
});	
        
})(jQuery);