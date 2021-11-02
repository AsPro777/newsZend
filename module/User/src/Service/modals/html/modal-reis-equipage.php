<?php
return <<<S
    <div class="row">
    <div class="col-md-12">                                    
        <table class="table table-hover table-striped table-bordered table-xxs">
            <thead>
                <tr>
                    <th colspan=4>
                        <h5><i class="fa fa-user-circle position-left"></i> Водители</h5>                        
                        <div class="heading-btn-group" style="float:right;margin-top:-56px;margin-right: -10px;">
                            <a href="#" class="btn btn-link btn-float has-text btn-add-driver"><i class="icon-add text-teal-500"></i><span>Добавить</span></a>                        
                        </div>                        
                    </th>
                </tr>
            </thead>
            <thead>
                <tr>
                    <th>ФИО</th>
                    <th>ДР / Пол</th>
                    <th>Документ</th>
                    <th></th>
                </tr>
            </thead>

            <tbody id="drivers-body">
                {$drivers_body}
            </tbody>
                
            <thead>
                <tr>
                    <th colspan=4>
                        <h5><i class="fa fa-user-circle-o position-left"></i> Персонал</h5>
                        <div class="heading-btn-group" style="float:right;margin-top:-56px;margin-right: -10px;">
                            <a href="#" class="btn btn-link btn-float has-text btn-add-personal"><i class="icon-add text-teal-500"></i><span>Добавить</span></a>                        
                        </div>
                    </th>
                </tr>
            </thead>
                
            <thead>
                <tr>
                    <th>Должность, ФИО</th>
                    <th>Дата рождения, пол</th>
                    <th>Гражданство, документ</th>
                    <th></th>
                </tr>
            </thead>
                
            <tbody id="personal-body">
                {$personal_body}
            </tbody>
                
        </table>
    </div>                              
    </div>                                                  

S;
