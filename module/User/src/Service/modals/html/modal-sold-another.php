<?php
return <<<S
        <div class="panel" style="overflow: auto;">
            <div class="panel-body">
                {$reis_info}
            </div>
            <table class="table table-bordered table-xxs">
                <tr>
                    <th rowspan=2 width=70>Место</th>
                    <th rowspan=2 width=70>Билет</th>
                    <th rowspan=2>Поездка</th>
                    <th rowspan=2>Пассажир</th>
                    <th rowspan=2 width=200>Стоимость</th>
                    <th colspan=4 class=center>Действия и комиссия</th>
                    <th rowspan=2>Комментарий</th>
                </tr>
                <tr>
                    <th width=120 class=center>Отказ</th>
                    <th width=120 class=center>Возврат</th>
                    <th width=120 class=center>Отмена рейса</th>
                    <th width=120 class=center>Печать</th>
                </tr>
                {$table_rows}
            </table>
        </div>
S;
