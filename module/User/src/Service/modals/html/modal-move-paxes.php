<?php
return <<<S
    <div class="row">
    <div class="col-md-12">                                    
        <table class="table table-hover table-striped table-bordered table-xxs">
            <thead>
                <tr>
                    <th>
                        На рейс: 
                    </th>
                    <th colspan=3>
                        <select name=to_reis class="form-control">{$to_reis_options}</select>
                    </th>
                    <th class=center>
                        <i class="fa fa-refresh" id=to_reis_reload></i>
                    </th>
                </tr>
                <tr>
                    <th>
                        Выборочно: 
                    </th>
                    <th colspan=4>
                        <input id=selected_only type=checkbox> <label for=selected_only class=text-muted> (снимает ограничения с заполненных рейсов в списке!)</label>
                    </th>
                </tr>
            </thead>
            <thead>
                <tr>
                    <th>ФИО</th>
                    <th>Тел. / Док.</th>
                    <th>Откуда / Куда</th>
                    <th>Места</th>                    
                    <th>Все <input id=select_all type=checkbox data-node=selection disabled checked></th>
                </tr>
            </thead>

            <tbody id="paxes-body" data-id="{$id_reis}">
                {$paxes_body}
            </tbody>                                
        </table>
    </div>                              
    </div>                                                  

S;
