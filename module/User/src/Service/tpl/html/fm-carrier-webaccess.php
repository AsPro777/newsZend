<?php
$access = filter_var($access, FILTER_VALIDATE_BOOLEAN);
$hidden = $access?"":"hidden";
$access = $access?"checked":"";
return <<<S
    <div class="panel tpl" data-node="webaccess">
          <div class="panel-heading">
            Доступ к веб-службе
          </div>
          <div class="panel-body">

            <div class="row">
                <div class="col-md-3">
                    Доступ разрешен
                </div>
                <div class="col-md-3">
                    <input name="access" type="checkbox" {$access}>
                </div>
            </div>

            <div class="row view-if-access {$hidden}">
                <div class="col-md-3">
                    Логин
                </div>
                <div class="col-md-3">
                    <input name="walogin" class="form-control" value="{$walogin}">
                </div>
            </div>

            <div class="row view-if-access {$hidden}">
                <div class="col-md-3">
                    Пароль
                </div>
                <div class="col-md-3">
                    <input name="wapassword" class="form-control" value="{$wapassword}">
                </div>
                <div class="col-md-3">
                    <button class="form-control password-generator btn bg-teal-300">Генератор</button>
                </div>
            </div>

          </div>
    </div>
S;
