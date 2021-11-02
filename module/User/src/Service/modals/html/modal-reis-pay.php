<?php
return <<<S

    <div class="bordered-modal-content">

        <div class="row">
            <div class="col-md-3">
                <label>Условия договора:</label>
            </div>
            <div class="col-md-9 left">
                <label>{$percent}</label>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <label>Сумма по заявке:</label>
            </div>
            <div class="col-md-9 left">
                <label>{$cost} руб.</label>
            </div>
        </div>
                
        <div class="row">
            <div class="col-md-3">
                <label>Уже оплачено:</label>
            </div>
            <div class="col-md-9 left">
                <label><b>{$payed} руб.</b></label>
                <p>
                {$pay_history}
            </div>
        </div>
                
        <div class="row">
            <div class="col-md-3">
                <label>Зачислить оплату:</label>
            </div>
            <div class="col-md-2 left">
                <input 
                    type="text" 
                    name="payment" 
                    class="form-control" 
                    value="{$payment}"
                    data-payed="{$payed}"
                    data-level="{$cost_level}"
                    data-max-cost="{$cost}"
                >
            </div>
            <div class="col-md-7 left">
                руб.
            </div>
        </div>

    </div>
                               
S;
