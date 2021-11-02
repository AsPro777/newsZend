<?php
return <<<S
$("div.panel.tpl").find("input, select").on("change", function(){
    $("span.btn-save-dropdown-row").show();
});

$("div.panel.tpl select[name='passport_type']").typeheadOnSelect({    
    remote:true,
    url:'/ajax/get-doc-type',        
    onSelectFn:function(){            
          var row = $("div.panel.tpl select[name='passport_type'] option:selected");
          var doc_num = $("div.panel.tpl input[name='passport']");
          if(row.length && row.attr("data-base")) {
                var option = JSON.parse(row.attr("data-base"));
                if(option.mask && (option.mask!=""))
                    doc_num.mask(option.mask, {placeholder: option.mask});
                else
                    doc_num.mask('_______________', {placeholder: " "});
                $("#doc-num-example-on-profile").html("<b>Подсказка: </b>"+(option.example?option.example:"Формат \"серия-номер\" смотри в документе.")).addClass("alert alert-info");
                $("span.btn-save-dropdown-row").show();
            } else {
                $("#doc-num-example-on-profile").html("").removeClass("alert alert-info");
                doc_num.mask('', {placeholder: " "});
            }
}});
$("div.panel.tpl select[name='passport_type']").closest("div").find("button.btn").css({"margin-top":"4px", "height":"34px"});
$("div.panel.tpl select[name='passport_type']").closest("div").find("input[name='__passport_type-typeheadOnSelect']").attr("name", "passport_type_txt");

$("div.panel.tpl input.my-control").addClass("my-control-on-profile").attr("name", "passport_type_txt");    
$("div.panel.tpl button.btn-my-control").addClass("btn-my-control-on-profile");    

$("div.panel.tpl input.form-control[name^='phone']").mask("0(000)000-0000", {placeholder: "7(___)___-____", selectOnFocus: false});
$("div.panel.tpl input.form-control[name='dr']").mask("00.00.0000", {placeholder: "дд.мм.гггг", selectOnFocus: false});

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
        $.each($("div.panel.tpl").find("input,select"), function(i, obj){
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
