<?php
return <<<S

$("#modal-print-report div.input-group").css({"width":"100%", "margin-top": "5px"});

$("#modal-print-report input[name=date]")
.mask("00.0000", {placeholder: "мм.гггг", selectOnFocus: false});

$("#modal-print-report a.btn-print").unbind("click").on("click", function(){
    
    var format = $(this).attr("data-format");
    $.ajax({
        url: '/account/print-report/',
        type: 'post',
        dataType: "json",
        data: {
            action:         'carrier-reseller',
            format:         format,
            id_org_agent:   $("#modal-print-report select[name=org] option:selected").attr("data-org-agent"),
            date:           $("#modal-print-report input[name=date]").val()
        },
        success: function( data ) {                                                
            if(data.success==1)
            {
                var w = window.open(data.body, "_blank", "menubar=no,location=no,toolbar=no,directories=no,status=no,width=850,resizable=yes,scrollbars=yes");
                if(!w) return alertMsg("Необходимо отключить блокировку всплывающих окон для " + window.location.host + " в настройках браузера!");
            }
            else
                alertMsg('Нет данных для отчета!');
        }, // success
    });//$.ajax        
});
S;
