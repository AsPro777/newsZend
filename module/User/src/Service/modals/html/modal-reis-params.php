<?php
return <<<S

    <div class="bordered-modal-content" id="params">

        <div class="row">
        <div class="col-md-4">
            <label>Сумма, руб *:</label>
        </div>
        <div class="col-md-2">
            <input type="text" name="cost" class="form-control" value="{$cost}">
        </div>                              
        </div>                              

        <div class="row">
        <div class="col-md-4">
            <label>Обратно:</label>
        </div>
        <div class="col-md-2">
            <input type="checkbox" name="obratno" class="form-control" {$obratno}>
        </div>                              
        </div>                              
            
        <div class="row">
        <div class="col-md-4">
            <label>Развоз:</label>
        </div>
        <div class="col-md-2">
            <input type="checkbox" name="razvoz" class="form-control" {$razvoz}>
        </div>                              
        </div>                              
            
        <div class="row">
        <div class="col-md-4">
            <label>Экскурсия:</label>
        </div>
        <div class="col-md-2">
            <input type="checkbox" name="excursiya" class="form-control" {$excursiya}>
        </div>                              
        </div>                              

    </div>
                               
S;
