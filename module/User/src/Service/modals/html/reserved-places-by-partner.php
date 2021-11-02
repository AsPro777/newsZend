<?php
$sell_column = $is_dispatcher ? "<th width=170></th>" : "";
return <<<S
        <div class="panel">
            <table class="table table-bordered table-xxs" id="modal-table" name="{$id_reis}">
                <tr>
                    <th width=50>Место</th>
                    <th>Статус</th>
                    <th width=170></th>
                    {$sell_column}
                </tr>
                {$table_rows}
            </table>
        </div>
S;
