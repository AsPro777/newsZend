<?php
return <<<S
        <div class="panel" style="overflow: auto;">
            <div class="panel-body">
                {$reis_info}
            </div>
            <table class="table table-bordered table-xxs">
                <tr>
                    <th width=50>Место</th>
                    <th width=50>Билет</th>
                    <th>Пассажир</th>
                    <th>Цена билета</th>
                    <th>Комиссия агента</th>
                    <th>К оплате</th>
                    <th>Комментарии</th>
                    <th class=center>Операции</th>
                </tr>
                {$table_rows}
            </table>
        </div>
S;
