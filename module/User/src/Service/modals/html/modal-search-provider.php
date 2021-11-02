<?php
return <<<S

    <div class="bordered-modal-content">
    <form id="modal-search-provider-form" method=post action="/account/search-provider">
        <div class="row">
            <div class="col-md-4">
                <label>Дата отправления:</label>
            </div>
            <div class="col-md-8 left">
                <div class="input-group">
                    <input 
                        name="date" 
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
                    <input class="form-control" name="from">
                </div>
            </div>
        </div>
                    
        <div class="row">
            <div class="col-md-4">
                <label>Куда:</label>
            </div>
            <div class="col-md-8 left">
                <div class="input-group">
                    <input class="form-control" name="to">
                </div>
            </div>
        </div>
    </form>
    </div>
                               
S;
