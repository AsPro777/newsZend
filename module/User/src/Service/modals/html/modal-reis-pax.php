<?php
return <<<S
    <div class="row">
    <div class="col-md-12">                                    
        <table class="table table-hover table-striped table-bordered table-xxs">
            <thead>
                <tr>
                    <th colspan=7>
                        <h5><i class="fa fa-user-circle position-left"></i> Пассажиры</h5>                        
                        <div class="heading-btn-group">
                           
                            <div id="mass_selector" class="btn-group" title="Выберите вид информации для группового назначения">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <span name="data-node-title"> Пакетное назначение</span> <span class="caret"></span>
                                </button>                                
                                <ul class="dropdown-menu">
                                    <li><a data-node="mass_grazhd"> Гражданство</a></li>
                                    <li><a data-node="mass_docs"> Удостоверение</a></li>
                                    <li><a data-node="mass_from"> Посадка</a></li>
                                    <li><a data-node="mass_to"> Высадка</a></li>
                                </ul>
                            </div>

                            <div id="mass_places" class="btn-group">
                                <input type="text" style="width:100px;" placeholder="1-5,8,10-19" title="Введите номера мест через ',' или '-', для которых \nбудет произведено групповое назначение.\nМожно оставить пустым и выбрать чекбоксы в правом столбце.">
                            </div>

                            <div id="mass_grazhd" class="btn-group hidden mass">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <span name="data-node-title" data-id="643" title="Россия"> Россия</span> <span class="caret"></span>
                                </button>                                
                                <ul class="dropdown-menu">
                                    {$mass_grazhd}
                                </ul>
                            </div>

                            <div id="mass_docs" class="btn-group hidden mass">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <span name="data-node-title" data-id="0" data-mask="0000 000000" title="Пасспорт гражданина РФ"> Пасспорт гражданина РФ</span> <span class="caret"></span>
                                </button>                                
                                <ul class="dropdown-menu">
                                    {$mass_docs}
                                </ul>
                            </div>

                            <div id="mass_from" class="btn-group hidden mass">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <span name="data-node-title"> Место посадки</span> <span class="caret"></span>
                                </button>                                
                                <ul class="dropdown-menu">
                                    {$mass_from}
                                </ul>
                            </div>

                            <div id="mass_to" class="btn-group hidden mass">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    <span name="data-node-title"> Место высадки</span> <span class="caret"></span>
                                </button>                                
                                <ul class="dropdown-menu">
                                    {$mass_to}
                                </ul>
                            </div>

                            <div id="mass_OK" class="btn-group">
                                <button type="button" class="btn">
                                    <span> OK </span>
                                </button>                                
                            </div>

                            <a href="#" class="btn btn-link btn-float has-text btn-pax-export" style="float:right;" title="Экспорт списка"><i class="icon-upload4 text-teal-300"></i><span>Скачать</span></a>
                            <a href="#" class="btn btn-link btn-float has-text btn-pax-import" style="float:right;" title="Импорт списка"><i class="icon-download4 text-teal-300"></i><span>Загрузить</span></a>
                            <a href="#" class="btn btn-link btn-float has-text btn-pax-tpl" style="float:right;" title="Шаблон импорта списка"><i class="icon-insert-template text-teal-300"></i><span>Шаблон</span></a>
                            <a href="#" class="btn btn-link btn-float has-text btn-pax-add" style="float:right;" title="Добавить место"><i class="icon-add text-teal-300"></i><span>Место</span></a>
                        </div>                        
                    </th>
                </tr>
            </thead>
            <thead>
                <tr>
                    <th>№ - Место</th>
                    <th>ФИО</th>
                    <th>ДР</th>
                    <th>Пол</th>
                    <th>Гражданство</th>
                    <th>Удостоверение личности</th>
                    <th>Посадка / Высадка</th>
                    <th id="all_checkboxes" width=10>Все</th>
                </tr>
            </thead>

            <tbody id="paxes-body" data-id="{$id_reis}">
                {$paxes_body}
            </tbody>                                
        </table>
    </div>                              
    </div>                                                  

S;
