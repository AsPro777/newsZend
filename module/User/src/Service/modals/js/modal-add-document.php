<?php
$node = "modal-add-document";
$accept_extentions = User\Service\AccauntDocsService::acceptExtentions();
$max_size = User\Service\AccauntDocsService::maxFileSize();

$accept_mimes = explode(",", User\Service\AccauntDocsService::acceptMimes());
$jstr = [];
foreach($accept_mimes as $item)
    $jstr[] = "'$item'";
$accept_mimes = "[" . implode(',', $jstr) . "]";

return <<<S
    var f = $("#$node form[name='$node-form']");
    f.find(":input[name='valid_to']").mask("00.00.0000", {placeholder: "дд.мм.гггг", selectOnFocus: false});

    var isInvalidDate = function (date, futureOnly)
    {
        var saved = date;
        var appendix = "\\r\\n\\r\\nДля установки неограниченной даты оставьте поле пустым.";
        date = date.split('.');
        var d = date[0], m = date[1], y = date[2];
        var dt = new Date();
        dt.setUTCFullYear(y, m-1, d);

        var result = ((y == dt.getUTCFullYear()) && ((m-1) == dt.getUTCMonth()) && (d == dt.getUTCDate()));
        if(!result)
            return ("Некорректная дата: "+saved+"!"+appendix);

        var nextMonth = new Date();
        nextMonth.setUTCFullYear(nextMonth.getFullYear(), nextMonth.getMonth()+1, nextMonth.getDate());

        if( futureOnly && (nextMonth > dt) )
        {
            return ("Дата истечения должна быть в будущем, не менее месяца от сегодняшней даты!\\r\\n\\r\\nВы установили дату: "+saved+"!"+appendix);
        }

        return false;
    }

    f.find("#preview, #scan").hide();
    f.find(":file").on("change", function(){
        $("#preview").text("Файл не выбран").addClass("alert-danger").removeClass("alert-success");
        var files = $(this).prop("files");
        if(files[0])
        {
            var accept_mimes = {$accept_mimes};
            if( accept_mimes.indexOf(files[0].type)==-1 )
                return alertMsg('Принимаются только файлы {$accept_extentions}!');
            if(parseInt(files[0].size) > 1024*1000*{$max_size})
                return alertMsg('Размер файла не должен превышать {$max_size} Mb!');

            var title_input = f.find(":input[name='title']");
            if(title_input.val()=="") title_input.val(files[0].name);

            $("#preview").html("<b>Выбран файл:</b><br>" + files[0].name + '<br><br><input class="btn btn-sm btn-primary" type=button value="Загрузить...">').removeClass("alert-danger").addClass("alert-success").show();


            $("#preview input.btn").on("click", function()
            {
                var valid_to = f.find(":input[name='valid_to']").val(); console.log(valid_to);
                if( (valid_to!="") && ( check = isInvalidDate(valid_to, true) ) )
                {
                    alertMsg(check);
                    return false;
                }

                var data = new FormData(document.forms.namedItem("{$node}-form"));
                data.set("action", "new-file-post");

                var request = new XMLHttpRequest();
                request.open("POST", "/account/docs", true);

                request.onloadstart = function(oEvent)
                {
                    $( "#load-indicator" ).show();
                };
                request.onloadend = function(oEvent)
                {
                    $( "#load-indicator" ).hide();
                };

                request.onload = function(oEvent)
                {
                   if (request.status == 200)
                   {
                       try{
                            var data = JSON.parse(request.response);
                        } catch(err)
                        {
                            alertMsg("Неудачно!<p>" + request.responseText);
                        }

                        if(data.success)
                        {
                           successMsg("Успешно!", function(){
                               f.append($("<input type=hidden name=result value=1>"));
                               $("#$node button[data-dismiss='modal']").trigger("click");
                           });
                        }else{
                               alertMsg("Неудачно!<p>" + data.msg)
                        };
                   } else {
                     $("#preview").html("Не удалось загрузить файл! Описание ошибки сервера: <br> " + request.status + '<br>' + request.statusText + '<br>' + request.responseText);
                   }
                }; // onload
                request.send(data);
            }); // click
        } // if
    });

   $("#$node form[name='$node-form'] div.row").css({"padding-top":"5px"});
S;
