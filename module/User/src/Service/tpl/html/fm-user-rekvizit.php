<?php
// 
return <<<S
    <div class="panel tpl" data-node="rekvizit">
          <div class="panel-heading">
            Платежные реквизиты
          </div>
          <div class="panel-body">

            <div class="row">
                <div class="col-md-4">
                    Наименование банка
                </div>               
                <div class="col-md-8">
                    <input name="bankname" class="form-control" value="{$bankname}" type="text">                    
                </div>               
            </div>               
                    
            <div class="row">
                <div class="col-md-4">
                    БИК
                </div>               
                <div class="col-md-8">
                    <input name="bankbik" class="form-control" value="{$bankbik}" type="text">                    
                </div>               
            </div>               
                    
            <div class="row">
                <div class="col-md-4">
                    Корреспондентский счет
                </div>               
                <div class="col-md-8">
                    <input name="bankks" class="form-control" value="{$bankks}" type="text">                    
                </div>               
            </div>               
                    
            <div class="row">
                <div class="col-md-4">
                    Расчетный счет
                </div>               
                <div class="col-md-8">
                    <input name="bankrs" class="form-control" value="{$bankrs}" type="text">                    
                </div>               
            </div>               
                                  
          </div>
    </div>
S;
          