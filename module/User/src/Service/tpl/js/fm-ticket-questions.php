<?php
return <<<S
   $("#ticketQuestionModal").on('show.bs.modal', function (e) {
        $("#configTicketsMapModal").css("display", "none");
   });

   $("#ticketQuestionModal").on('hide.bs.modal', function (e) {
        $("#configTicketsMapModal").css("display", "block");
        $("#ticketQuestionModal").remove();
   });

   $("#ticketQuestionModal div.modal-footer :button").last().on('click', function(){
        var button = $(this);
        button.attr("data-dismiss", null);

        var ticket_id = $("#ticketQuestionModal input[name='ticket-id']").val();
        var doc_num = $("#ticketQuestionModal input[name='doc-num']").val();

        if(isNaN(parseInt($.trim(ticket_id))))
            return (alertMsg("Некорректный или отсутствующий номер билета!\\r\\n\\r\\nПроверьте правильность ввода!") && false);

        if($.trim(doc_num) == "")
            return (alertMsg("Некорректный или отсутствующий номер удостоверения личности!\\r\\n\\r\\nПроверьте правильность ввода!") && false);
        
        window.location.href = '/account/ticket-edit/'+encodeURIComponent(id)+'/'+encodeURIComponent(doc_num.replace(/\\s/g, ''));
   });

   $("#ticketQuestionModal").modal("show");
S;
