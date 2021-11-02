<?php
return <<<S
        <table class="table table-bordered table-xxs">
            <thead>
            <tr>
                <th><b>Документы</b></th>
                <th width=70></th>
            </tr>
            </thead>

            <tbody>
            <tr><td>Договор фрахтования</td>            <td class="buttons"  data-node="" data-path="{$pdfUrl}" title="Печатать договор"></td></tr>
            <tr><td>Заявка на перевозку пассажиров</td> <td class="buttons"  data-node="zayavka-na-perevoz-data" title="Печатать заявку на перевозку пассажиров"></td></tr>
            <tr><td>Заказ - наряд</td>                  <td class="buttons"  data-node="zakaz-naryad-data" title="Печатать заказ - наряд"></td></tr>
            <tr><td>Список пассажиров</td>              <td class="buttons"  data-node="pax-list-data" title="Печатать список пассажиров"></td></tr>
            <tr><td>Путевой лист</td>                   <td class="buttons"  data-node="print-path-list" data-path="/account/path-list" title="Печатать путевой лист"></td></tr>
            <tr><td>Акт выполненных работ</td>          <td class="buttons"  data-node="akt-vipolnennih-rabot-data" title="Печатать акт выполненных работ"></td></tr>
            </tbody>

        </table>
S;
