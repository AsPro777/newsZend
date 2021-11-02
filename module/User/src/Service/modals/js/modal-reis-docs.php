<?php
return <<<S

$("#modal-reis-docs *.buttons")
    .append($("<i />").addClass("fa fa-print fa-1x text-teal-300"))
    .css("cursor","pointer")
    .on("click", function(){
        var node = $(this).attr("data-node");
        var url = $(this).attr("data-path")?$(this).attr("data-path"):'/account/reises/';

        if(node !== "")
            $.ajax({
                    url: url,
                    type: 'post',
                    dataType: "json",
                    data: {
                        id_reis: {$id_reis},
                        id_firm: {$id_firm},
                        action: node
                    },
                    success: function( data ) {
                        if(data.success==1)
                        {
                            var w = window.open("","_blank", "menubar=no,location=no,toolbar=no,directories=no,status=no,width=850,resizable=yes,scrollbars=yes");
                            if(!w) return alertMsg("Необходимо отключить блокировку всплывающих окон для " + window.location.host + " в настройках браузера!");
                            w.document.open();
                            w.document.write(data.body);
                            w.document.close();
                            w.focus();
                            if(print) w.print();
                        }
                        else
                            alertMsg('Данные по запросу не найдены!' + (data.msg?('<p>'+data.msg):''));
                    }, // success
                });//$.ajax
        else
        {
            if(url == '/account/reises/')
                return alertMsg("Файл договора отсутствует!");
        }

    })
    ;

// Печать и скачивание заказ-наряда

var td = $("#modal-reis-docs td.buttons[data-node='zakaz-naryad-data']").unbind("click").css("cursor","default");
var item = td.find("i.fa").css({"margin-right":"5px"}).clone();
item.removeClass("fa-print").addClass("fa-file-word-o download");
td.append(item);

td.find("i.fa").css("cursor","pointer").on("click", function(){
        var node = $(this).closest("td").attr("data-node") + ($(this).hasClass("download")?"-word":"");
        var fmt = $(this).hasClass("download") ? "word" : "html";

        $.ajax({
                url: '/account/reises/',
                type: 'post',
                dataType: "json",
                data: {
                    id_reis: {$id_reis},
                    id_firm: {$id_firm},
                    action: node
                },
                success: function( data ) {
                    if(data.success==1)
                    {
//                        if(fmt=="word")
//                        {
                            var w = window.open(data.body, "_blank", "menubar=no,location=no,toolbar=no,directories=no,status=no,width=850,resizable=yes,scrollbars=yes");
                            if(!w) return alertMsg("Необходимо отключить блокировку всплывающих окон для " + window.location.host + " в настройках браузера!");
//                        }
//                        else
//                        {
//                            var w = window.open("","_blank", "menubar=no,location=no,toolbar=no,directories=no,status=no,width=850,resizable=yes,scrollbars=yes");
//                            if(!w) return alertMsg("Необходимо отключить блокировку всплывающих окон для " + window.location.host + " в настройках браузера!");
//                            w.document.open();
//                            w.document.write(data.body);
//                            w.document.close();
//                            w.focus();
//                            if(print) w.print();
//                        }
                    }
                    else
                        alertMsg('Данные по запросу не найдены!' + (data.msg?('<p>'+data.msg):''));
                }, // success
            });//$.ajax
    });

S;
