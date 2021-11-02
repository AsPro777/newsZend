<?php
return <<<S

    <div class="bordered-modal-content">
        
        <div class="row">
            <div class="col-md-4">
                <label>Дата отправления:</label>
            </div>
            <div class="col-md-8 left">
                <div class="input-group">
                    <input 
                        name="dt-start" 
                        class="form-control" 
                        value="{$dt_start}"
                    >
                </div>
            </div>
        </div>
                    
        <div class="row">
            <div class="col-md-4">
                <label>Откуда:</label>
            </div>
            <div class="col-md-8 left">
                <div class="input-group">
                    <select class="form-control" name="search-from"></select>
                </div>
            </div>
        </div>
                    
        <div class="row">
            <div class="col-md-4">
                <label>Куда:</label>
            </div>
            <div class="col-md-8 left">
                <div class="input-group">
                    <select class="form-control" name="search-to"></select>
                </div>
            </div>
        </div>

    </div>
                               
S;
