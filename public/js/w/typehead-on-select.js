$.widget("ui.typeheadOnSelect", {

    input: null,
    button_reset: null,
    button_reset_span: null,
    button_down: null,
    button_down_span: null,
    options: {
        url: null,
        maxlength: 100,
    },
    data: [],
    reset_val: null,

    _create: function()
    {
        var self = this, el = self.element, o = self.options;
        var self_name = $(el).attr("name");
        if(!self_name) self_name = Math.random()*100000;
        self.input = $("<input value='' placeholder='Начните ввод и выберите из списка'></input>")
                    .attr("name", "__"+self_name+"-typeheadOnSelect")
                    .attr("maxlength", o.maxlength)
                    .on("blur", function( event, ui )
                    {
                      self.input.val( el.find(":selected").text() );
                      if(o.onSelectFn) o.onSelectFn(1);
                      return false;
                    })
                    .on("focus", function()
                    {
                       $(this).select();
                       if(o.onSelectFn) o.onSelectFn(2);
                    })
                    .addClass("form-control");
        self.data = [];
        $.each($(el).find("option"), function(i, opt){
            self.data.push({
                id: $(opt).val(),
                label: $(opt).text()
            })
        });

        if(o.remote)
        {
            self.input.autocomplete({
                source: function( request, response ) {
                  $.ajax({
                          url: o.url,
                          type: 'post',
                          dataType: "json",
                          data: {
                            action: o.action,
                            query: request.term,
                          },
                          success: function( data ) {
                            el.empty();
                            $.each(data, function(i, val){
                                var op = $("<option></option>")
                                        .text(val.value?val.value:val.label)
                                        .attr("value", val.id)
                                        .attr("data-base", JSON.stringify(val));
                                el.append(op);
                            });
                            response( data );
                          },
                      });//$.ajax
                  },
                minLength: 2,
                select: function( event, ui ) {
                    el.val(ui.item.id);
                    self.input.prop('readonly','readonly');
                    self.button_down.prop("disabled", 'disabled');
                    if(o.onSelectFn) o.onSelectFn(3);
                 }
            });
        }
        else
        {
            self.input.autocomplete({
                source: self.data,
                minLength: 2,
                select: function( event, ui ) {
                    el.val(ui.item.id);
                    self.input.prop('readonly','readonly');
                    self.button_down.prop("disabled", 'disabled');
                    if(o.onSelectFn) o.onSelectFn(3);
                 }
            });

            el.on("change", function(a){
                var val = $(a.target).val();
                var text = $(a.target).find("option:selected").text();
                console.log("change", val, text);

                self.input.prop('readonly','readonly').val(text);
                self.button_down.prop("disabled", 'disabled');
                if(o.onSelectFn) o.onSelectFn('3.1');
            });
        }

        el.hide();
        el.after(self.input);
        var group = $("<div>").addClass("input-group-btn");
        self.input.after(group);

        self.button_reset_span = $("<i class='icon-cross'></i>").css("cursor", "pointer");
        self.button_reset = $("<button type=button class='btn bg-teal' />")
                    .on("click", function(){
                        el.val(self.reset_val);
                        self.input.val(el.find(":selected").text()).prop('readonly',null);
                        self.button_down.prop("disabled", null);
                        if(o.onSelectFn) o.onSelectFn(4);
                        return false;
                    });
        self.button_reset.append(self.button_reset_span).attr("title", "Сброс выбора");

        group.append(self.button_reset);

        self.button_down_span = $("<i class='icon-chevron-down'></i>").css("cursor", "pointer");
        self.button_down = $("<button type=button class='btn bg-teal' />")
                    .on("click", function(){
//                        el.val("");
//                        self.input.val("");
                        self.input.autocomplete( "option", "minLength", 0);
                        self.input.autocomplete( "search", "" ).focus();
                        setTimeout(function(){
                            self.input.autocomplete( "option", "minLength", 2);
                        }, 1000);
                        return false;
                    });
        self.button_down.append(self.button_down_span).attr("title", "Весь список");

        group.append(self.button_down);

        // для инициализации контрола существующими данными нужно на select
        // прицепить data-init-id и data-init-val, по которым создастся каркас для дальнейшей работы
        if(el.attr("data-init-id") && el.attr("data-init-val"))
        {
            el.append(
                    $("<option></option>")
                    .attr("value",el.attr("data-init-id"))
                    .text(el.attr("data-init-val"))
                    .attr("data-base", JSON.stringify({}))
            );
            el.val(el.attr("data-init-id"));

            self.button_down.prop("disabled", 'disabled');
            self.input.val( el.attr("data-init-val") );
            self.input.prop('readonly','readonly');
        }

        // это событие нужно вызвать вручную при завершении загрузки option's
        // нужно только если данные подггребаются из существующих option's
        el.on("onload", function(a,b){

            self.data = [];
            self.reset_val = null;
            $.each(el.find("option"), function(i, val){
                val = $(val);
                if(val.attr("selected") != undefined) {
                    self.reset_val = val.val();
                    self.input.val(val.text());
                    if(o.onSelectFn) o.onSelectFn(5);
                }
                self.data.push({
                    id: val.val(),
                    label: val.text()
                })
            });

            if( (self.reset_val===null) && (el.find("option").length>0) ) {
                var x = el.find("option").first();
                self.reset_val = x.attr("selected", true).val();
                self.input.val(x.text());
            }

            self.input.autocomplete({
              minLength: 0,
              source: self.data,
              focus: function( event, ui ) {
                self.input.val( ui.item.label );
               return false;
              },
              select: function( event, ui ) {
                el.val( ui.item.id );
                self.input.val( ui.item.label ).prop('readonly','readonly');
                self.button_down.prop("disabled", "disabled");
                if(o.onSelectFn) o.onSelectFn(6);
                return false;
              }
            });

            if(o.onSelectFn) o.onSelectFn(7);
        });
    },

    _remove: function()
    {
      var self = this, el = self.element;
      $(el).siblings().remove();
      $(el).show();
      $(el).empty();
    },

    recreate: function(options)
    {
        this._remove();
        this._create(this.options);
    },

    clear: function()
    {
        var self = this;
        var el = self.element;
        el.val(0);
        setTimeout(function(){
            self.input.val("").prop('readonly',null);
            self.button_down.prop("disabled", null);
        }, 100);
    }
});

