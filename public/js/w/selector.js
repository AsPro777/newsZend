(function($) {

    $.widget("ui.myselector", {
        
        emptyHtml: "<li><span class='glyphicon glyphicon-warning-sign'> </span> <span name=city-name>Выберите</span></li>",
        options: {
            valueInputPathCity: null,
            valueInputPathCountry: null,
            id: 0,
            country_id: 0,
            name: "",
            selected: false,
            cancelable: true
        },
		
	_create: function() 
	{
            var self = this, el = self.element, o = self.options, container = $(el).parent();
            
            container.attr("myselector-container", "yes");            
            this.resultElement = $("<ul name=city-name-ul class='form-control'></ul>")                                
                                .css({"margin":0, "border":"none"});        
            this.elementPath = container.get(0).tagName + "[myselector-container='yes'] input[name='"+$(el).attr('name')+"']";            
            
            $(self.elementPath).attr("placeholder", "Начните ввод...");

            if(o.id && o.name && o.selected)
            {               
                $(o.valueInputPathCity).val(o.id);
                $(o.valueInputPathCountry).val(o.id_country);
                $(self.elementPath).attr("readonly", "readonly").hide();
                self.resultElement
                        .html("<li style='list-style-type: none;'><button type='button' class='close'>&times;</button><span class='glyphicon glyphicon-tower'> </span> <span name=city-name>"+o.name+"</span></li>");
                $(self.resultElement)
                        .find("button.close")
                         .on("click", function(){
                              $(self.resultElement).html(self.emptyHtml);
                              $(self.elementPath).val("").attr("readonly", null).show().focus();
                              container.find("ul li").css({"color":"red"});
                              container.find("ul").css({"border":"none"});
                              if(typeof self.options.reset === "function") self.options.reset();
                         });
                         
                if(o.cancelable!==true)
                    $(self.resultElement).find("button.close").remove();                  
                
                if(typeof self.options.callback === "function") 
                    self.options.callback({item: JSON.parse(o.selected)});
            }
            else
                self.resultElement.html(self.emptyHtml);
                        
            $(el).parent().append(this.resultElement);
            $(el).val(o.name);
            el.autocomplete({
                source: function( request, response ) {
                  $.ajax({
                          url: '/ajax/citys-list',
                          type: 'post',
                          dataType: "json",
                          data: {
                            query: request.term,
                            exclude: ''
                          },
                          success: function( data ) {
                            response( data );
                          },
                      });//$.ajax
                },
                minLength: 2,
                select: function( event, ui ) {
                    $(self.elementPath).attr("readonly","readonly").hide();
                    $(o.valueInputPathCity).val(ui.item.id);
                    $(o.valueInputPathCountry).val(ui.item.id_country);
                    $(self.resultElement)
                            .html("<li style='list-style-type: none;'><button type='button' class='close'>&times;</button><span class='glyphicon glyphicon-tower'> </span> <span name=city-name>"+ui.item.value+"</span></li>");                    
                    $(self.resultElement)
                            .find("button.close")
                            .on("click", function(){
                              $(self.resultElement).html(self.emptyHtml);
                                $(o.valueInputPathCity).val("");
                                $(o.valueInputPathCountry).val("");
                                $(self.elementPath).val("").attr("readonly", null).show().focus();
                                container.find("ul li").css({"color":"red"});
                                container.find("ul").css({"border":"none"});
                                if(typeof self.options.reset === "function") self.options.reset();
                            });

                    if(o.cancelable!==true)
                        $(self.resultElement).find("button.close").remove();
                    
                    container.find("ul li").css({"color":"green"});   
                    container.find("ul").css({"border":"solid 1px #ddd"});
                    if(typeof self.options.callback === "function") self.options.callback(ui);
                }
            });
            
            container.find("ul li").css({"color":$(self.elementPath).val()?"green":"red"});
	},
		
});	
	
        
$.widget("ui.cityselector", $.ui.myselector, {
        emptyHtml: "<li style='list-style-type: none; display: none;'><span class='glyphicon glyphicon-warning-sign'> </span> <span name=city-name>Выберите город из списка</span></li>"
});
    
})(jQuery);