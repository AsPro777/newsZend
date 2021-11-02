<?php
$node = "modal-add-document";
$accept_mimes = User\Service\AccauntDocsService::acceptMimes();
$accept_extentions = User\Service\AccauntDocsService::acceptExtentions();
return <<<S
<form class="panel-body" name="$node-form" style="padding: 0 20px;">

    <div class="row title">
       <div class="col-md-4">                    
            Наименование:
        </div>              
        <div class="col-md-8">
            <input name="title" class="form-control" value="">
        </div>                      
    </div>

    <div class="row valid_to">
       <div class="col-md-4">                    
            Действителен до:
        </div>               
        <div class="col-md-8">
            <input name="valid_to" class="form-control" value="">
        </div>                       
    </div>
    <div class="row">
        <div class="col-md-12 text-muted">
            Оставьте поле пустым, если срок действия документа неограничен.
            <br>
            <br>
        </div>                       
    </div>

    <div class="row folder">
       <div class="col-md-4">                    
            Путь для сохранения:
        </div>               
        <div class="col-md-8">
            <input name="folder" class="form-control" value="{$folder}">
        </div>                       
    </div>
            
    <div class="row">
        <div class="col-md-12 text-muted">
            Вы можете ввести произвольный путь, разделяя папки символом <b>/</b>. 
            <br>
            Если путь пустой, то файл загрузится в папку <i>'{$default_folder}'.</i>
            <br>
            Пример: Автобусы/Р533СР/фото
        </div>                       
    </div>

    <div class="row" style="margin-top:15px;">
        <div class="col-md-12 center">
            <label for="scan" class="alert bg-teal" style="cursor:pointer;margin-bottom:-15px;">Выберите скан или документ<br>({$accept_extentions})</label><input type="file" id=scan name=scan accept="{$accept_mimes}">
            <div id=preview class="alert alert-danger alert-styled-left bold" style="margin-top:25px;margin-bottom:-20px;">Файл не выбран</div>
        </div>                       
    </div>
    <input type=hidden name=id_owner value="{$id_owner}">        
    <input type=hidden name=tag value="{$tag}">        
    <input type=hidden name=id_obj value="{$id_obj}">        
    <input type=hidden name=table_obj value="{$table_obj}">        
</form>
S;
