<?php
return <<<S

    <div class="bordered-modal-content" id="modal-print-report">
                        
        <div class="row">
            <div class="col-md-4">
                <label>Отчетный месяц:</label>
            </div>
            <div class="col-md-8 left">
                <input name="date" class="form-control" value="{$today}">
            </div>
        </div>
        
        <div class="row" style="margin-top:30px;">
            <div class="col-md-4">
                <label>{$label_1}:</label>
            </div>
            <div class="col-md-8 left">
                &nbsp;{$doveritel}
            </div>
        </div>
   
        <div class="row" style="margin-top:10px;">
            <div class="col-md-4">
                <label>{$label_2}:</label>
            </div>
            <div class="col-md-8 left">
                <select class="form-control" name="org">{$options}</select>
            </div>
        </div>
                    
        <div class="row" style="margin-top:30px;">
            <div class="col-md-12" style="text-align:center">
                <a class="btn btn-link btn-float has-text btn-print" data-format="html"><i class="icon-file-text text-teal-300"></i><span>HTML</span></a>
                <a class="btn btn-link btn-float has-text btn-print" data-format="word"><i class="icon-file-word text-teal-300"></i><span>Word</span></a>
                <!--a class="btn btn-link btn-float has-text btn-print" data-format="excel"><i class="icon-file-excel text-teal-300"></i><span>Excel</span></a-->
                <a class="btn btn-link btn-float has-text btn-print" data-format="pdf"><i class="icon-file-pdf text-teal-300"></i><span>PDF</span></a>
            </div>
        </div>
                    
                
    </div>
                               
S;
