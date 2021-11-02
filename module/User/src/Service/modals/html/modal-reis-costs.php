<?php
return <<<S

<style>
#citys-to tr > td {
    text-align: center;
    padding-left: 2px;
    padding-right: 2px;
}
#citys-to thead > tr > td:nth-child(even) {
    background-color: #cdecde;
}
#citys-to tbody > tr > td:nth-child(odd) {
    background-color: #cdecde;
}
#citys-to tbody > tr > td:nth-child(1) {
    text-align: left;
}
#citys-to tr {
    border-bottom: 1px solid #ddd;
}
</style>

<div id="edit-time-costs">
      <ul class="nav nav-tabs bg-teal">
      <li id="li-view-costs" class="active">{$li_view_costs}</li>
      <li id="li-edit-costs">{$li_edit_costs}</li>
      </ul>
    <p>

    <div class="tab-content">

    <div class="tab-pane" id="edit-costs">

      <div>
        <div class="panel">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-ruble fa-1x"> </i> Изменение цен <span id="span-edit-costs-title"></span></h3>
              </div>
              <div class="panel-body">
                <table class="table">
                    <tr>
                        <th>Промежуточные пункты и остановки</th>
                        <th>Цена от "<span name="city-from"> - </span>" до:</th>
                        <th> </th>
                    </tr>
                    <tr>
                        <td id="citys-from">
                            {$citys_from}
                        </td>

                        <td id="citys-to">
                            {$citys_to}
                        </td>

                        <td width="60%">
                        </td>
                    </tr>
                </table>

                <div class="row row-submit" style="text-align:center">
                    <input class="btn btn-sm btn-primary save" value="Сохранить" type="button">
                    <input class="btn btn-sm btn-primary copy" value="Сохранить и копировать на все рейсы расписания" type="button">
                    <input class="btn btn-sm btn-primary reset" value="Восстановить исходные" type="button">
                    <input type="hidden" class="id" value="{$id}">
                    <input type="hidden" class="wa_id" value="">
                </div>

              </div>
        </div>

      </div>

    </div>

    <div class="tab-pane active" id="view-costs">
        <div class="panel" style="table-layout:fixed;">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-ruble fa-1x"> </i> Установленные цены <span id="span-view-costs-title"></span></h3>
              </div>
              <div class="panel-body" style="overflow: auto;">
                <table class="table table-condensed">
                        <tbody id="view-costs-tbody">
                                <tr>
                                        <td width=25%>
                                            <span style="float:left">
                                            <big>От</big> <i class="fa fa-arrow-circle-o-down fa-2x text-teal-600" aria-hidden="true"></i>
                                            </span>
                                            <span style="float:right">
                                            <big>До остановки</big> <i class="fa fa-arrow-circle-o-right fa-2x text-teal-600" aria-hidden="true"></i></td>
                                            </span>
                                        {$first_row_data}
                                </tr>
                                {$data_rows}
                        </tbody>
                </table>
             </div>
              <div class="panel-footer">
                    <div class="media-body">
                        <div class="text-muted mt-10"><strong>Внимание!</strong> Если цена не установлена (X), то в интерфейсе продажи билетов остановка не будет показана и до нее нельзя продать билет!</div>
		    </div>
              </div>
      </div>
    </div>

    </div>
</div>

S;
