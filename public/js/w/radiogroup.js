(function($) {

    $.widget("ui.myRadiogroup", {
        
        options: {    
            label: '',
            data: []
        },
        _content: '',
        _selected: false,
	_create: function() 
	{
            var self = this, el = self.element, o = self.options;
            var name = 'radiogroup-' + Math.random();
            var focused = false;
            content = $('<div class="form-group" />');
            if(o.label) content.append($('<label class="display-block text-semibold" />').text(o.label));
            $.each(o.data, function(i, item){
                var radio = $('<label class="radio-inline radio-right" />');
                var checked = $('<span />'); 
                var input = $('<input type="radio" class="styled" />').attr("name", name).val(item.value).attr("data-value", item.text);                
                if(!focused) focused = input;
                if(o.checked === item.value)
                {
                    input.prop("checked", "checked");
                    focused = input;
                }
                var text = $('<span />').text(item.text);
                content.append(radio.append(checked.append(input)).append(text));
            });
                        
            content.find(".styled").uniform({
                    radioClass: 'choice'
            });
                        
            content.find(":radio")
                    .on("change", function(){
                        self._selected = {
                                id:$(this).val(),
                                txt: $(this).attr("data-value"),
                        };
                    });
            
            self._content = content;    
            el.append(self._content);
        },
        get: function()
        {
            var self = this, el = self.element, o = self.options;
            return self._selected;
        },
        clear: function()
        {
            var self = this, el = self.element, o = self.options;
            $(this).attr("data-value", "");
            $(this).val("");
            self._selected = {
                    id:"",
                    txt: "",
            };
        }
});	
        
})(jQuery);