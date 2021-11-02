<?php
return <<<S
    <div class="row">
    <div class="col-md-12">                                    
        <table class="table table-hover table-striped table-bordered table-xxs">
            <thead>
                <tr>
                    <th>Папка</th>
                    <th>Документ</th>
                    <th>Действителен до</th>
                    <th>Информация</th>
                    <th>Действия</th>
                </tr>
            </thead>

            <tbody id="docs-body">
                {$docs_body}
            </tbody>                                
        </table>
    </div>                              
    </div>                                                  

<style>
    span.pull-right {
        margin-left: 5px;
        cursor: pointer;
    }
    .dropdown-menu {
     right:0; 
     left:auto;   
    }
    .dropdown-menu > li > div {
        padding: 5px;
        text-align: center;
    }
    .dropdown-menu > li > div > input {
        font-size: 11px;
    }
    .dropdown-menu > li > div > .send-to-email-ok {
        font-size: 11px;
        width: 50px;
        padding: unset;
        margin-top: 10px;
    }
    td.strike {
         text-decoration: line-through;         
    }
    td.w-120 {
         width: 120px;
    }                    
    td.w-150 {
         width: 150px;
    }                    
</style>
S;
