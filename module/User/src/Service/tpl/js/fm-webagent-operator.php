<?php
return <<<S

$("div.panel.tpl").find("input").on("input", function(){
    $("span.btn-save-dropdown-row").show();
});

$("div.panel.tpl").find("input[name='acbpdp']").
    on("change", function(){
        $("span.btn-save-dropdown-row").show();
    })
    .val('{$acbpdp}');

 $("div.panel.tpl input.form-control[name='acbpdp']")
     .mask("0000000000", {placeholder: "", selectOnFocus: false});

$("span.btn-save-dropdown-row")
    .unbind("click")
    .on("click", function() {
        var trSetup = $(this).parent().parent().first();
        var trUser = trSetup.prev("tr");
        var data = {
            id: $(trUser).attr("name"),
            action: "set-profile",
            block: $("div.panel.tpl").attr("data-node"),            
        };        
        $.each($("div.panel.tpl").find("input"), function(i, obj){
            //if($(obj).val())
                data[$(obj).attr("name")] = $(obj).val();
        });

        $.ajax({
                url: '/account/'+ajax_action,
                type: 'post',
                dataType: "json",
                data: data,
                success: function( data ) {
                    if(data.success) {                            
                        toggleDropdownRow(trSetup, "", false);
                        if(data.msg) trUser.find(".result").html('<span class="label label-success">Выполнено!</span>');
                    } else {                            
                        if(data.msg || data.err) alertMsg(data.msg + "\\r\\n\\r\\n" + (data.err?data.err:""));
                    }
                } 
            });
});

S;
