<?php
return <<<S

    <div class="bordered-modal-content">

        <div class="input-group">
            <select class="form-control" id="search-phrase"></select>
        </div>
        
        <ul class="media-list">
                <li class="media">
                        <div class="media-left">
                                <a href="#" class="border-success text-success btn-flat btn-rounded btn-icon btn-xs"><i class="icon-checkmark3"></i></a>
                        </div>

                        <div class="media-body">
                            Примеры ввода: &laquo;Моск - Санкт&raquo;, &laquo;Москва - Владимир - {$example_date_1}&raquo;, &laquo;{$example_date_2} - Моск - Влади&raquo;, &laquo;вор-моск-02&raquo;.
                            <div class="media-annotation"></div>
                        </div>

                        <div class="media-right media-middle"></div>
                </li>                
        </ul>

    </div>
                               
S;
