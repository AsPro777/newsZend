
<?php
return <<<S
        <div class="panel">
            <div class="panel-body">
              <div class="row">
                 <div class="col-md-3">
                    <p>Продавец</p>
                 </div>
                 <div class="col-md-9">
                    <select id="seller-org" class="form-control">
                       <option value="" selected>Все</option>
                       {$options}
                    </select>
                 </div>
              </div>
              <br>
              <div class="row">
                 <div class="col-md-3">
                    <p>Тип продаж</p>
                 </div>
                 <div class="col-md-9">
                    <select id="access-type" class="form-control">
                       <option value="" selected>Все</option>
                       <option value="public">Сайт</option>
                       <option value="webservice">Служба</option>
                       <option value="offline">Касса</option>
                       <option value="widget">Виджет</option>
                    </select>
                 </div>
              </div>
              <br>
              <div class="row">
                 <div class="col-md-3">
                    <p>Номер заказа</p>
                 </div>
                 <div class="col-md-9">
                    <input name="id_order" id="id-order" style="width:30%" class="form-control">
                 </div>
              </div>
              <br>
              <div class="row">
                <div class="col-md-3">
                   <p>Тип билета</p>
                </div>
                <div class="col-md-9">
                  <select id="ticket-type" class="form-control">
                    <option value="" selected>Любой</option>
                    <option value="own">Свой</option>
                    <option value="back">Обратный</option>
                    <option value="another">Чужой</option>
                  </select>
                </div>
              </div>
              <br>
              <div class="row">
                <div class="col-md-3">
                   <p>Номер билета</p>
                </div>
                <div class="col-md-9">
                   <input name="id_ticket" id="id-ticket" style="width:30%;" class="form-control">
                </div>
            </div>
        </div>
S;
?>