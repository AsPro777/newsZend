<?php
return <<<S
    <div class="panel tpl" data-node="strah">
          <div class="panel-heading">
            Контакты
          </div>
          <div class="panel-body">

            <div class="row">
                <div class="col-md-3">
                    Наименование страхователя
                </div>               
                <div class="col-md-9">
                    <input name="name" class="form-control" value="{$name}">                    
                </div>               
            </div>               
                    
            <div class="row">                
                <div class="col-md-3">
                    Адрес организации
                </div>               
                <div class="col-md-9">
                    <input name="address" class="form-control" value="{$address}">                    
                </div>               
            </div>               
                    
            <div class="row">                               
                <div class="col-md-3">
                    Договор
                </div>               
                <div class="col-md-9">
                    <input name="dogovor" class="form-control" value="{$dogovor}">                    
                </div>               
            </div>               
                    
            <div class="row">                               
                <div class="col-md-3">
                    Дата договора
                </div>               
                <div class="col-md-9">
                    <input name="begin" class="form-control" value="{$begin}">                    
                </div>                
            </div>                
                    
            <div class="row">                               
                <div class="col-md-3">
                    Срок действия договора
                </div>               
                <div class="col-md-9">
                    <input name="end" class="form-control" value="{$end}">                    
                </div>                
            </div>                
                    
          </div>
    </div>
S;
