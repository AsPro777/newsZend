<?php
return <<<S
        <div class="panel">
            <div class="panel-body">

                <div class="row" style="margin-bottom:12px">
                    <div class="col-md-5">
                        <label for="city" style="font-weight:bolder; font-size: 14px; margin-top: 7px;">Выберите город:</label>                        
                    </div>                                                
                    <div class="col-md-7">
                        <input name="city" class="form-control" type="text">
                        <input name="id_city" value="0" type="hidden">
                        <input name="id_country" value="0" type="hidden">
                    </div>

                </div>   
            </div>
        </div>                
S;
