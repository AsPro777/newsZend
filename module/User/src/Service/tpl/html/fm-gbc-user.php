<?php
// здесь переменные для формирования списка допустимых переменных в посте,
// если они не используются в коде ниже
// {$sex}

$sexOptions = "";
$sexOptions .= "<option value=1 ". (($sex==1)?"selected":"") .">Мужской</option>";
$sexOptions .= "<option value=0 ". (($sex==0)?"selected":"") .">Женский</option>";

return <<<S
    <div class="panel tpl" data-node="user">
          <div class="panel-heading">
            Базовый профиль пользователя
          </div>
          <div class="panel-body">

            <div class="row">
                <div class="col-md-3">
                    Дата рождения
                </div>
                <div class="col-md-9">
                    <input name="dr" class="form-control" value="{$dr}" placeholder="дд.мм.гггг" maxlength="10" type="text">                
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    Пол
                </div>
                <div class="col-md-9">
                    <select name="sex" class="form-control">
                    $sexOptions
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    Удостоверение личности
                </div>
                <div class="col-md-9">
                    <select name="passport_type" class="form-control" data-init-id="{$passport_type}" data-init-val="{$passport_type_txt}"></select>                    
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    Серия и номер удостоверения
                </div>
                <div class="col-md-9">
                    <input name="passport" class="form-control" value="{$passport}" type="text">
                    <div id="doc-num-example-on-profile"></div>
                </div>
            </div>

          </div>
    </div>
S;
