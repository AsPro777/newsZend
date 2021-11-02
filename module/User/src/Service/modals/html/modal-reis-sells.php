<?php
return <<<S
        <div class="panel">
            <!--div class="panel-body">
            </div-->
            <table class="table table-bordered table-xxs">
                <tr>
                    <th width=70>Место</th>
                    <th>Пассажир</th>
                    <th>Поездка</th>
                    <th>Комментарий</th>
                    <th width=150 class=center>Оплата</th>
                    <th width=150 class=center>Отказ</th>
                    <th width=150 class=center>
                        <a name="pax-zakaz-show-all-history" class="btn btn-default" title="Журнал операций">
                            <i class="icon-list3 position-left"></i> <span>Журнал...</span>
                        </a>
                    </th>
                </tr>
                {$table_rows}
            </table>
        </div>
S;
