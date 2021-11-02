<?php
return <<<S

var date = moment(); 

$("#search-phrase").typeheadOnSelect({
    remote:true, 
    url:'/ajax/get-search-phrase',
    onSelectFn: function(){
        var selected = $("#search-phrase").val();        
        if(!selected || (selected==0)) 
        {            
            $("#search-phrase").typeheadOnSelect("clear");
            return;
        }
        selected = selected.split("-");
        if(selected[0]=="near")
            window.location.href = "/account/sells?near=" + selected[1] + "-" + selected[2] + "&date=" + selected[3];
        else
            window.updateSearchState(selected[0]+"-"+selected[1], selected[2]);
    }
});        
        
$("#search-phrase").next().attr("placeholder", 'Начните ввод. Например: «Воронеж - Москва - ' + date.format("DD.MM.YYYY") + '» или «' + date.format("DD.MM.YYYY") + ' - Воронеж - Москва»');


S;
