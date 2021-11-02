<?php
return <<<S
    <div class="panel tpl" data-node="questions">
          <div class="panel-heading">
            Укажите данные из предъявленного билета
          </div>
          <div class="panel-body get-ticket-form">

            <div class="row">
                <div class="col-md-8">
                    Номер билета
                </div>               
                <div class="col-md-4">
                    <input name="ticket-id" class="form-control" value="" type="text" placeholder="Номер билета" maxlength=20>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    Номер удостоверяющего документа пассажира
                </div>
                <div class="col-md-4">
                    <input name="doc-num" class="form-control" value="" type="text" placeholder="Номер документа" maxlength=20>
                </div>
            </div>

          </div>
    </div>
S;
