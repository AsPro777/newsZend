<?php
// 
$lcensypermanently = filter_var($lcensypermanently, FILTER_VALIDATE_BOOLEAN);
$hidden = $lcensypermanently?"hidden":"";
$lcensypermanently = $lcensypermanently?"checked":"";
return <<<S
    <div class="panel tpl" data-node="licensy">
          <div class="panel-heading">
            Лицензии
          </div>
          <div class="panel-body">

            <div class="row">
                <div class="col-md-3">
                    Номер лицензии
                </div>               
                <div class="col-md-6">
                    <input name="lcensynum" class="form-control" value="{$lcensynum}" type="text">                    
                </div>               

                <div class="col-md-3">
                    <input name="lcensypermanently" type="checkbox" {$lcensypermanently}> <span class="with-checkbox">Бессрочная</span>
                </div>               
            </div>               
                    
            <div class="row view-if-lcensypermanently $hidden">
                <div class="col-md-3">
                    Действует до
                </div>               
                <div class="col-md-3">
                    <input name="lcensydtend" class="form-control" value="{$lcensydtend}" type="text">                    
                </div>               
            </div>               
                      
            <div class="row">                               
                &nbsp;
            </div>               
   
            <div class="row">                    
                <div class="col-md-3">
                   № удост. допуска к МП
                </div>               
                <div class="col-md-6">
                    <input name="internationalnum" class="form-control" value="{$internationalnum}" type="text">                    
                </div>               
            </div>               
                    
            <div class="row">                               
                <div class="col-md-3">
                   Действует до
                </div>               
                <div class="col-md-3">
                    <input name="internationaldtend" class="form-control" value="{$internationaldtend}" type="text">                    
                </div>               
            </div>               
                    
            <div class="row">                               
                &nbsp;
            </div>               

            <div class="row">                               
                <div class="col-md-3">
                   № уведомления УГАДН
                </div>               
                <div class="col-md-6">
                    <input name="ugadnnum" class="form-control" value="{$ugadnnum}" type="text">                    
                </div>               
            </div>               
                    
            <div class="row">                                                              
                <div class="col-md-3">
                   Действует до
                </div>               
                <div class="col-md-3">
                    <input name="ugadndtend" class="form-control" value="{$ugadndtend}" type="text">                    
                </div>               
            </div>               
                                  
          </div>
    </div>
S;
