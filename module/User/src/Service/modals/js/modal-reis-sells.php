<?php
$node = "modal-reis-sells";
return <<<S

var save_td_value = '';
var max_save_td_value = 200;

$("#{$node} td.editable[name='comment']").on("click", function(){
    var action = 'pax-set-comment';
    var doc = $(this).attr("data-doc");
    var textarea = $(this).find("textarea");
    if(textarea.length>0) return false;

    if(!doc) return false;
    if($("textarea[name='comment']").length) return false;

    var td = $(this);
    save_td_value = td.text();
    $(this).css({"padding":0});
    textarea = $("<textarea name=comment rows=6 placeholder='Комментарий до "+max_save_td_value+" символов' />").text($(this).text()).css({"width":"100%", "margin":0, "padding":0, "border":"solid 1px gray"});
    var submitter = $('<a name=submit type=button class="btn border-teal text-teal-800 btn-flat">Сохранить</a>').css({"float":"right","padding":"0 5px","margin":"1px"});
    var canceler = $('<a name=cancel type=button class="btn border-danger text-danger-800 btn-flat">Отмена</a>').css({"float":"left","padding":"0 5px","margin":"1px"});

    textarea.on("click", function(){
        return false;
    });
    canceler.on("click", function(){
        td.text(save_td_value);
        return false;
    });
    submitter.on("click", function(){
        var comment = textarea.val().substring(0, max_save_td_value);
        $.ajax({
            url: "/account/" + ajax_action,
            type: 'post',
            dataType: "json",
            data: {
                action: action,
                reis: '{$id_reis}',
                doc: doc,
                comment: comment
            },
            success: function( data ) {
                 if(data.success==1) {
                    successMsg("Успешно!", function(){
                        td.text(data.comment).css({"padding":"10px"});
                    });
                 }
                else
                   alertMsg("Ошибка сохранения!<br>" + (data.msg?data.msg:""));
            } // success
        });//$.ajax

        return false;
    });

    $(this).empty();
    $(this).append(textarea).append($("<div />").css({"text-align":"center", "width":"100%"}).append(canceler).append(submitter));
    textarea.focus();
});

$("#{$node} a[name='pax-zakaz-show-all-history']").on("click", function(){
    var action = 'modal-'+$(this).attr("name");
    var w = modalTpl({
        id: action,
        //class: 'modal-xs',
        title: "История всех операций с пассажирами",
        body: "Загрузка...",
        "ajax-url": "/account/" + ajax_action,
        "ajax-data": {
            action: action,
            reis: '{$id_reis}'
        },
    }).appendTo($("body"));
    w.modal("show");
});

$("#{$node} a[name='pax-zakaz-show-history']").on("click", function(){
    var fio = $(this).closest("tr").find("span[name='fio']").text();
    var action = 'modal-'+$(this).attr("name");
    var doc = $(this).attr("data-doc");

    var w = modalTpl({
        id: action,
        //class: 'modal-xs',
        title: "История операций пассажира: " + fio,
        body: "Загрузка...",
        "ajax-url": "/account/" + ajax_action,
        "ajax-data": {
            action: action,
            reis: '{$id_reis}',
            doc: doc
        },
    }).appendTo($("body"));
    w.modal("show");
});

$("#{$node} a[name='pax-zakaz-do-cancel']").on("click", function(){
    var fio = $(this).closest("tr").find("span[name='fio']").text();
    var canceler = $(this).closest("tr");
    var action = $(this).attr("name");
    var doc = $(this).attr("data-doc");

    confirmMsg("Вы действительно хотите оформить отказ от поездки для пассажира<br>"+fio+"?", function(){
        $.ajax({
            url: "/account/" + ajax_action,
            type: 'post',
            dataType: "json",
            data: {
                action: action,
                reis: '{$id_reis}',
                doc: doc
            },
            success: function( data ) {
                 if(data.success==1) {
                    successMsg("Успешно!", function(){
                        canceler.find("td.clearable").text('');
                    });
                 }
                else
                   alertMsg("Ошибка сохранения!<br>" + (data.msg?data.msg:""));
            } // success
        });//$.ajax
    });
});

$("#{$node} a[name='pax-zakaz-do-pay']").on("click", function(){
    var canceler = $(this).closest("tr").find("a[name='pax-zakaz-do-cancel']");
    var action = 'modal-' + $(this).attr("name");
    var place = $(this).attr("data-place");
    var doc = $(this).attr("data-doc");
    var w = modalTpl({
        id: action,
        class: 'modal-xs',
        title: "Прием оплаты",
        body: "Загрузка...",
        "ajax-url": "/account/" + ajax_action,
        "ajax-data": {
            action: action
        },
        "submit-function": function(){
            if($("#"+action).data("validate")())
            {
                $.ajax({
                    url: "/account/" + ajax_action,
                    type: 'post',
                    dataType: "json",
                    data: {
                        action: action + '-save',
                        reis: '{$id_reis}',
                        place: place,
                        doc: doc,
                        sum: $("#"+action).data("params")["sum"]
                    },
                    success: function( data ) {
                         if(data.success==1) {
                            successMsg("Успешно!", function(){
                                $("#"+action).modal("hide");
                                canceler.find("span").text(data.payed);
                            });
                         }
                        else
                           alertMsg("Ошибка сохранения!<br>" + (data.msg?data.msg:""));
                    } // success
                });//$.ajax
            }
            return false;
        }
    }).appendTo($("body"));
    w.modal("show");
});

$("#{$node}").data("validate", function()
{
    var params = {}, c = $("#{$node}");
    c.data("params", params);

//    if( c.find("input[name='actual_to_date']").val() == '' )
//        return alertMsg("Необходимо выбрать дату!");
//    if( c.find("input[name='actual_to_time']").val() == '' )
//        return alertMsg("Необходимо выбрать время!");
//
//    params.actual_to_date = c.find("input[name='actual_to_date']").val();
//    params.actual_to_time = c.find("input[name='actual_to_time']").val();

    c.data("params", params);
    return true;
});

S;
