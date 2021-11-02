<?php
return <<<S

var body = $("#paxes-body");
var data = [];
var trs = body.find("tr.not-ticket-row");

$.each(trs, function(place, tr){
    tr = $(tr);
    var d = {};
    for(var i=1; i<=8; i++)
    {
        var obj = tr.find("span[data-n='"+i+"']");
        d[i] = obj.attr("data-base-val");
    }
    data.push(d);
});

var ta = $("#textarea-pax-import-export"), text = '';
$.each(data, function(place, obj){
    var check = '';
    for(var i=1; i<=8; i++)
        check += obj[i];
    
    if(check=='') return;

    for(var i=1; i<=8; i++)
    {
        text += (obj[i] + '\\r\\n');
    }    
    text += ('##\\r\\n');
});
ta.val(text);
S;
