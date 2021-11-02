<?php
/*
* - ИНН физического лица является последовательностью из 12 арабских цифр, из которых 
* первые две представляют собой код субъекта Российской Федерации согласно ст. 65 Конституции, 
* следующие две — номер местной налоговой инспекции, 
* следующие шесть — номер налоговой записи налогоплательщика и 
* последние две — так называемые «контрольные цифры» для проверки правильности записи.
* - ИНН индивидуального предпринимателя присваивается при регистрации физического лица в качестве индивидуального предпринимателя, 
* если данное лицо ранее его не имело. В ином случае используется имеющийся ИНН.
*   
* - ИНН юридического лица является последовательностью из 10 арабских цифр, из которых 
* первые две представляют собой код субъекта Российской Федерации согласно 65 статье Конституции 
* (или «99» для межрегиональной инспекции ФНС), 
* следующие две — номер местной налоговой инспекции, 
* следующие пять — номер налоговой записи налогоплательщика в территориальном разделе ОГРН 
* (Основной государственный регистрационный номер) и 
* последняя — контрольная цифра.   
* 
* - Структура КПП представляет собой девятизначный код: NNNNPPXXX.  
* 
* NNNN (4 знака) — код налогового органа, который осуществил постановку на учет 
* PP (2 знака) — причина постановки на учет (учёта сведений). 
* Символ P представляет собой цифру или заглавную букву латинского алфавита от A до Z.
* 
* Числовое значение символов PP может принимать значение в соответствии с ведомственным (ФНС) «Справочником причин постановки на учёт налогоплательщиков-организаций в налоговых органах (СППУНО)»[2]:
* 
* для российской организации от 01 до 50 (01 — по месту её нахождения);
* для иностранной организации от 51 до 99;
* 
* XXX (3 знака) — порядковый номер постановки на учет (учета сведений) в налоговом органе по соответствующему основанию
*/

if(!isset($opf) || empty($opf)) $opf = 2; // ИП

return <<<S

function chOPF(node)
{
    node = node?parseInt(node):2;
    if(node==1) /* ЮЛ */
    {
        $("div.panel.tpl input.form-control[name='inn']")
            .mask("0000000000", {placeholder: "__________", selectOnFocus: true})
            .attr("title", "ИНН юридического лица является последовательностью из 10 арабских цифр");
        $("div.panel.tpl input.form-control[name='ogrn']")
            .mask("0000000000000", {placeholder: "_____________", selectOnFocus: true})
            .attr("title", "Структура ОГРН(ИП) является последовательностью из 13 арабских цифр");;
        $("div.view-only-firm").show();
    }
    else if(node==2) /* ФЛ и ИП */
    {
        $("div.panel.tpl input.form-control[name='inn']")
            .mask("000000000000", {placeholder: "____________", selectOnFocus: true})
            .attr("title", "ИНН ИП является последовательностью из 12 арабских цифр");
        $("div.panel.tpl input.form-control[name='ogrn']")
            .mask("000000000000000", {placeholder: "_______________", selectOnFocus: true})
            .attr("title", "Структура ОГРН(ИП) является последовательностью из 15 арабских цифр");;
        $("div.view-only-firm").hide();
    }
    else
        return chOPF(2);    
}

$("div.panel.tpl select[name='country']").typeheadOnSelect({remote:true, url:'/ajax/get-country'});
$("div.panel.tpl select[name='country']").closest("div").find("button.btn").css({"margin-top":"4px", "height":"34px"});

$("div.panel.tpl").find("input").on("input", function(){
    $("span.btn-save-dropdown-row").show();
});

$("div.panel.tpl").find("select").
    on("change", function(){
        $("span.btn-save-dropdown-row").show();
        chOPF($(this).find(":selected").val());
    })
    .val('{$opf}');

 $("div.panel.tpl input.form-control[name='kpp']")
     .mask("000000000", {placeholder: "_________", selectOnFocus: true})
     .attr("title", "Структура КПП является последовательностью из 9 арабских цифр");

chOPF({$opf});

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

        $.each($("div.panel.tpl").find("select"), function(i, obj){
            if($(obj).val()) {
                data[$(obj).attr("name")] = $(obj).val();
                data[$(obj).attr("name")+'_txt'] = $(obj).find("option:selected").text();
            }
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
