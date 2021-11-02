<?php
$node = "modal-pax-list-import";
$accept_extentions = "csv";
$max_size = 5;

$accept_mimes = explode(",", ".csv,application/vnd.ms-excel");
$jstr = [];
foreach($accept_mimes as $item)
    $jstr[] = "'".trim($item)."'";
$accept_mimes = "[" . implode(',', $jstr) . "]";

return <<<S
    var f = $("#$node form[name='$node-form']");
     
    f.find("#preview, #csv").hide();        
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
               
            $("#preview").html("<b>Выбран файл:</b><br>" + files[0].name + '<br><br><input class="btn btn-sm btn-primary" type=button value="Загрузить...">').removeClass("alert-danger").addClass("alert-success").show();            
            
            $("#preview input.btn").on("click", function()
            {
                var data = new FormData(document.forms.namedItem("{$node}-form"));
                data.set("action", "download-pax-list");

                var request = new XMLHttpRequest();
                request.open("POST", "/account/zakaz", true);
                
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
                           f.append($("<input type=hidden name=result value=1>")); 
                           $("#$node button[data-dismiss='modal']").trigger("click");                        

                           $("#paxes-body").data("import")(data.html_rows);                                                  
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
S;
