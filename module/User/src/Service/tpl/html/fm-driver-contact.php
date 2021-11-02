<?php
return <<<S
    <div class="panel tpl" data-node="contact">
          <div class="panel-heading">
            Контакты
          </div>
          <div class="panel-body">

            <div class="row">                
                <div class="col-md-3">
                    Адрес проживания
                </div>               
                <div class="col-md-9">
                    <input name="faddress" class="form-control" value="{$faddress}" type="text">                    
                </div>               
            </div>               
                    
            <div class="row">                               
                <div class="col-md-3">
                    Телефон основной
                </div>               
                <div class="col-md-9">
                    <input name="phone1" class="form-control" value="{$phone1}" placeholder="7(___)___-____" maxlength="16" type="text">                    
                </div>               
            </div>               
                    
            <div class="row">                               
                <div class="col-md-3">
                    Первый резервный телефон
                </div>               
                <div class="col-md-9">
                    <input name="phone2" class="form-control" value="{$phone2}" placeholder="7(___)___-____" maxlength="16" type="text">                    
                </div>                
            </div>                
                    
            <div class="row">                               
                <div class="col-md-3">
                    Второй резервный телефон
                </div>               
                <div class="col-md-9">
                    <input name="phone3" class="form-control" value="{$phone3}" placeholder="7(___)___-____" maxlength="16" type="text">                    
                </div>                
            </div>                
                    
          </div>
    </div>
S;
