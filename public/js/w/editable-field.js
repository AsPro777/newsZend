(function($) {

    $.widget("ui.myEditable", {
        
        options: {    
            emptytext: 'пусто',
            input: '',
            init: function(){},
            backup: function(){return ''},
            submit: function(input){return false;},
            shownext: function(el){return false;},
            onshow: function(){return false;},
        },
        _content: '',
        _saved_value: '',
        _emptytextlabel: '',
        
	_create: function() 
	{
            var self = this, el = self.element, o = self.options;
            
            self._emptytextlabel = $('<span class="editable-empty-text-label editable-click" />')
                .text(o.emptytext)
                .css({"cursor":"pointer", "opacity":"0.3"})
                .on("click", function(){self._show()});
        
            el.before(self._emptytextlabel);
            
            el.addClass("editable-click editable-value")
                .css({"cursor":"pointer"})
                .on("click", function(){self._show()});
        
            self._backup();
            self._emptytext();
        },
        
        _emptytext: function()
        {
            var self = this, el = self.element, o = self.options;
            
            if(self._saved_value != '')
            {
                $(el).removeClass("editable-empty");
                self._emptytextlabel.hide();
            }
            else
            {                
                $(el).addClass("editable-empty");
                self._emptytextlabel.show();                
            }
        },
        
        _do: function()
        {
            var self = this, el = self.element, o = self.options;
                    
            if(self._content != '') 
            {
                if(typeof o.init === 'function') o.init(o.input);            
                self._content.show();
                return ;
            }
            
            var content = '' +
            '<span class="editable-container editable-inline">' +
            '<div>' +
            '    <div class="editableform-loading" style="display: none;"></div>' +
            '    <form class="editableform">' +
            '        <div class="control-group form-group">' +
            '            <div>' +
            '                <div class="editable-input" style="position: relative;">' +            
            '                    <span class="editable-clear-x hidden"></span>' +
            '                </div>' +
            '                <div class="editable-buttons">' +
            '                    <button type="submit" class="btn btn-primary btn-sm editable-submit"><i class="glyphicon glyphicon-ok"></i></button>' +
            '                    <button type="button" class="btn btn-default btn-sm editable-cancel"><i class="glyphicon glyphicon-remove"></i></button>' +
            '                </div>' +
            '            </div>' +
            '            <div class="editable-error-block help-block" style="display: none;"></div>' +
            '        </div>' +
            '    </form>' +
            '</div>' +
            '</div>';
            
            self._content = $(content);
            self._content.find("div.editable-input").prepend(o.input);
            $(el).after(self._content);
                        
            self._content.find("button.editable-submit").on("click", function(){return self._submit();});
            self._content.find("button.editable-cancel").on("click", function(){return self._cancel()});	
            self._content.find("span.editable-clear-x").on("click", function(){return self._clear()});	
                        
            o.input.focus();
            if(typeof o.init === 'function') o.init(o.input);            
            self._emptytext();
        },
        
        _undo: function()
        {
            var self = this, el = self.element, o = self.options;
            self._content.hide();
        },
        
        _hide: function()
        {
            var self = this, el = self.element, o = self.options;
            self._undo();
            el.show();
            $("div.full-screen-glass").remove();
        },        
        
        show: function()
        {
            var self = this, el = self.element, o = self.options;
            self._show();
        },
        
        _show: function()
        {
            var self = this, el = self.element, o = self.options;
            self._backup();
            self._do();
            el.hide();
            self._emptytextlabel.hide();
            el.parent().append(
                $("<div class='full-screen-glass' />")
                .on("click", function(){self._cancel()})   
            );
            if(typeof o.onshow === 'function') o.onshow();       
            
            self._content
                    .on("keydown", function(e){                        
                        if(e.keyCode != 27) return true;
                        self._cancel();
                        return false;
                    });
        },        
        
        _backup: function()
        {
            var self = this, el = self.element, o = self.options;            
            if(typeof o.backup === 'function') self._saved_value = o.backup();
            if(typeof o.backup === 'string') self._saved_value = o.backup;
        },                        
        
        _submit: function()
        {
            var self = this, el = self.element, o = self.options;
            if(typeof o.submit === 'function') o.submit(o.input);            
            self._backup();            
            self._emptytext();                
            self._hide();
            setTimeout(function(){
                if(typeof o.shownext === 'function') o.shownext(el);
            }, 100);
            return false;
        },        
        
        _cancel: function()
        {
            var self = this, el = self.element, o = self.options;
            self._emptytext();            
            self._hide();
            return false;
        },                
        
        _clear: function()
        {
            var self = this, el = self.element, o = self.options;            
            return false;
        }, 
        
        clear: function()
        {
            var self = this, el = self.element, o = self.options;                        
            self._saved_value = '';
            self._emptytext();
        }
});	
        
})(jQuery);