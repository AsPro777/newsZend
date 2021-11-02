<?php
$is_sales_info_email = filter_var($is_sales_info_email, FILTER_VALIDATE_BOOLEAN);
$hidden_sales_info_email = $is_sales_info_email?"":"hidden";
$is_sales_info_email = $is_sales_info_email?"checked":"";

return <<<S
    <div class="panel tpl" data-node="notify">
          <div class="panel-heading">
            Уведомления
          </div>
          <div class="panel-body">
                    
            <div class="row">                               
                <div class="col-md-3">
                    Информировать о продажах на email
                </div>               
                <div class="col-md-9">
                    <input name="is_sales_info_email" {$is_sales_info_email} type=checkbox>
                </div>                
            </div>                
                    
            <div class="row view-if-access {$hidden_sales_info_email}">
                <div class="col-md-3">
                    E-mail для информирования
                </div>               
                <div class="col-md-9">
                    <input name="sales_info_email" class="form-control" value="{$sales_info_email}" placeholder="username@mail.xx" maxlength="255" type="text">                    
                </div>                
            </div>                
                                        
          </div>
    </div>
S;
