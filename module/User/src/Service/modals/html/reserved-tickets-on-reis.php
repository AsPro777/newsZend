<?php
return <<<S
        <div class="panel">
            <div class="panel-body">
                {$reis_info}
            </div>
            <table class="table table-bordered table-xxs">
                <tr>
                    <th width=50>Место</th>
                    <th>Пассажир</th>
                    <th width=120>Операции</th>
                </tr>
                {$table_rows}
            </table>
        </div>
S;
