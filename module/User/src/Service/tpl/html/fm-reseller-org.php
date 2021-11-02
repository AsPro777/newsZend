<?php
// здесь переменные для формирования списка допустимых переменных в посте,
// если они не используются в коде ниже
// {$opf} {$opf_txt}
return <<<S
    <div class="panel tpl" data-node="org">
          <div class="panel-heading">
            Организация / ИП
          </div>
          <div class="panel-body">
                    
            <div class="row">
                <div class="col-md-4">
                    Организационно правовая форма
                </div>               
                <div class="col-md-8">
                    <select name="opf" class="form-control">
                        <option value="1">Юридическое лицо</option>
                        <option value="2">Индивидуальный предприниматель</option>
                    </select>
                </div>               
            </div>               
                    
            <div class="row">                
                <div class="col-md-4">
                    Полное наименование
                </div>               
                <div class="col-md-8">
                    <input name="title" class="form-control" value="{$title}" type="text">                    
                </div>               
            </div>               
                    
            <div class="row">                
                <div class="col-md-4">
                    Сокращенное наименование
                </div>               
                <div class="col-md-8">
                    <input name="title_short" class="form-control" value="{$title_short}" type="text">                    
                </div>               
            </div>               
                    
            <div class="row view-only-firm">                               
                <div class="col-md-4">
                    Должность руководителя
                </div>               
                <div class="col-md-8">
                    <input name="director" class="form-control" value="{$director}" type="text">                    
                </div>               
            </div>               
                    
            <div class="row">                    
                <div class="col-md-4">
                   ФИО руководителя
                </div>               
                <div class="col-md-8">
                    <input name="fio" class="form-control" value="{$fio}" type="text">                    
                </div>               
            </div>               
                    
            <div class="row view-only-firm">
                <div class="col-md-4">
                   Действует на основании
                </div>               
                <div class="col-md-8">
                    <input name="osnova" class="form-control" value="{$osnova}" type="text">                    
                </div>               
            </div>               
                    
            <div class="row">                               
                <div class="col-md-4">
                   ИНН
                </div>               
                <div class="col-md-8">
                    <input name="inn" class="form-control" value="{$inn}" type="text">                    
                </div>               
            </div>               
                    
            <div class="row view-only-firm">
                <div class="col-md-4">
                   КПП
                </div>               
                <div class="col-md-8">
                    <input name="kpp" class="form-control" value="{$kpp}" type="text">                    
                </div>               
            </div>               
                    
            <div class="row">                               
                <div class="col-md-4">
                   ОГРН(ИП)
                </div>               
                <div class="col-md-8">
                    <input name="ogrn" class="form-control" value="{$ogrn}" type="text">                    
                </div>               
            </div>               
   
          </div>
    </div>
S;
