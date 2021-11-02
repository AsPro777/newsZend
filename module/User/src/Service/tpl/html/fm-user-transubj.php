<?php
return <<<S
    <div class="panel tpl" data-node="transubj">
          <div class="panel-heading">
            Данные автовокзала
          </div>
          <div class="panel-body">

            <div class="row">                
                <div class="col-md-4">
                    Полное наименование автовокзала
                </div>               
                <div class="col-md-8">
                    <input name="full_name" class="form-control" value="{$full_name}" type="text">                    
                </div>               
            </div>               
   
            <div class="row">                
                <div class="col-md-4">
                    Сокращенное наименование автовокзала
                </div>               
                <div class="col-md-8">
                    <input name="short_name" class="form-control" value="{$short_name}" type="text">                    
                </div>               
            </div>                  

            <div class="row">
                <div class="col-md-4">
                    Ближайший населенный пункт
                </div>
                <div class="col-md-8">
                    <div class="input-group">
                        <select name="city" class="form-control" data-init-id="{$city}" data-init-val="{$city_txt}"></select>                    
                    </div>
                </div>
            </div>
                    
            <div class="row">                
                <div class="col-md-4">
                    Идентификатор АЦБПДП
                </div>               
                <div class="col-md-2">
                    <input name="acbpdp" class="form-control" value="{$acbpdp}" type="text">                    
                </div>              
                    
            </div>               
                       
          </div>
    </div>
S;
