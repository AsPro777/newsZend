<?php
$node = "modal-pax-list-import";
$accept_mimes = ".csv";
return <<<S
<form class="panel-body" name="$node-form" style="padding: 0 20px;">
    <div class="row" style="margin-top:15px;">
        <div class="col-md-12 center">
            <label for="csv" class="alert bg-teal" style="cursor:pointer;margin-bottom:-15px;">Выберите документ CSV</label>
            <input type="file" id=csv name=csv accept="{$accept_mimes}" style="display: none;">
            <div id=preview class="alert alert-danger alert-styled-left bold" style="margin-top:25px;margin-bottom:-20px;">Файл не выбран</div>
        </div>                       
    </div>
</form>
S;
