<?php
// должно совпадать с code в spr_privilegie 
$s = <<<S
    <div class="panel tpl" data-node="permission">
          <div class="panel-heading">
            Базовый профиль пользователя
          </div>
          <div class="panel-body">
S;

foreach($extended_permissions as $key=>$val)
{       
    $label = htmlentities($val[0]);
    $checked = $val["checked"];
    $broken = $val["broken"];
    $s .= <<<S
            <div class="row">
                <div class="col-md-3">
                    $label
                </div>
                <div class="col-md-1">
                    <input name="$key" class="form-control" type="checkbox" $checked>
                </div>
                <div class="col-md-2">
                    $broken
                </div>
            </div>
S;
}

$s .= <<<S
          </div>
    </div>
S;

return $s;