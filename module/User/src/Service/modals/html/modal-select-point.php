<?php
return <<<S
        <ul class="nav nav-tabs bg-teal" id="modalTab">    
          <li class="active"><a href="#select" data-toggle="tab">Выбрать из списка</a></li>    
          <li><a href="#add" data-toggle="tab" tech-tag="5">Добавить новую</a></li>
        </ul>          
          
        <div class="panel">
            <div class="panel-body">                     
                <div class="col-md-12">
                    <label for="select-point">Конечная остановка</label><br>
                    <div class="tab-content">
                      <div class="tab-pane active" id="select">
                        <select name="select-point" class="form-control"><option value="">Укажите остановку</option>{$options}</select>
                      </div>                                
                      <div class="tab-pane" id="add">
                          <input id="add-point" name="add-point" class="form-control" placeholder="Укажите наименование новой остановки"></input>
                      </div>                                
                    </div>                                
                </div>                                
            </div>
        </div>        
S;
