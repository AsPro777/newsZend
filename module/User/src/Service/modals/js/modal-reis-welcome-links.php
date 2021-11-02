<?php
$node = "modal-reis-welcome-links";
return <<<S
var today = new Date();
$("#{$node} input.form-control[name='actual_to_date']")
    .mask("00.00.0000", {placeholder: "дд.мм.гггг", selectOnFocus: false})
    .pickadate( $.extend( pickadateConfig, {
        min: [today.getFullYear(),today.getMonth(),today.getDate()],
        max: [$max_year,$max_month,$max_date]
   } ) );

$("#{$node} input.form-control[name='actual_to_time']")
    .mask("00:00", {placeholder: "чч:мм", selectOnFocus: false})
    .pickatime({
        format: 'HH:i',
        formatLabel: 'HH:i',
        formatSubmit: 'HH:i',
        interval: 60
   });

$("#{$node} input.form-control[name='link']")
    .focus(function()
    {
        $(this).select();
    })
    .select();

$("#{$node}").data("validate", function()
{
    var params = {}, c = $("#{$node}");
    c.data("params", params);

    if( c.find("input[name='actual_to_date']").val() == '' )
        return alertMsg("Необходимо выбрать дату!");
    if( c.find("input[name='actual_to_time']").val() == '' )
        return alertMsg("Необходимо выбрать время!");

    params.actual_to_date = c.find("input[name='actual_to_date']").val();
    params.actual_to_time = c.find("input[name='actual_to_time']").val();

    c.data("params", params);
    return true;
});

S;
