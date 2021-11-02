<?php
return <<<S

$('#id-order').mask('0000000000');
$('#id-ticket').mask('0000000000');

$("#modal-filter-billing").data("validate", function()
{
    var params = {}, c = $("#modal-filter-billing");
    c.data("params", params);

    params.id_seller_org = parseInt( "0"+c.find("#seller-org").val() );
    params.access_type = c.find("#access-type").val();
    params.id_order = c.find("#id-order").val();
    params.ticket_type = c.find("#ticket-type").val();
    params.id_ticket = c.find("#id-ticket").val();

    c.data("params", params);
    return true;
});
S;
?>