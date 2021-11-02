$(function () {

/*Тест*/
$("a.btn.btn-sms").on("click",function(){
        $.ajax({
                  url: '/account/'+ajax_action,
                  type:'post',
                  dataType:"json",
                   data: {
                       action: 'sms'
                   },
                   success: function(data)
                   {
                   }
        });//$ajax
        return false;
    });

/*клик по кнопке Обновить*/
$("a.btn.btn-reset").on("click",function(){
        window.location.href='/account/responses';
        return false;
    });

/*клик по радиокнопкам*/
$(".styled, .multiselect-container input")
        .uniform({ radioClass: 'choice'})
        .on("click", function(){
            _post();
            return false;
        });

/*
$('.datepicker').pickadate({
     format: 'dd mmmm, yyyy',
});*/


$("input.form-control[name='dt']")
    .mask("00.00.0000", {placeholder: "дд.мм.гггг", selectOnFocus: false})
    .pickadate( $.extend(pickadateConfig, {
        onClose: function(context) {
            _post();
            return false;
        }

    }))
    .val(filter_dt);


/*календарь стрелки влево вправо*/
$("span.input-group-addon.dt-step")
        .on("click", function(){
            var increment = parseInt($(this).attr("data-increment"));
            var d = new Date();
            if($("input.form-control[name='dt']").val()==''){
                var dt=Array();
                dt[0]=d.getDate();
                dt[1]=d.getMonth()+1;
                dt[2]=d.getFullYear();

                var increment=0;
            }
            else {
                var dt = $("input.form-control[name='dt']").val().split(".");
            }
            if(dt.length<3)
            {
              d.setDate(increment+parseInt(dt[0]));
              dt = [
                    ('0' + d.getDate()).slice(-2),
                    ('0' + (d.getMonth() + 1)).slice(-2),
                    d.getFullYear()
                   ].join('.');
            }
            else
            {
                d.setFullYear(dt[2]);
                d.setMonth(dt[1]-1);
                d.setDate(increment+parseInt(dt[0]));
                dt = [
                      ('0' + d.getDate()).slice(-2),
                      ('0' + (d.getMonth() + 1)).slice(-2),
                      d.getFullYear()
                     ].join('.');
            }

            $("input.form-control[name='dt']").val(dt);
            _post();
            return false;
        });

/*кнопка "Показать следующие 50 записей"*/
$("#next-page").on("click", function(){
        var self = this;
        var page = 1 + parseInt($(self).attr("data-page"));
        var value = $('input[name="accepted"]:checked').val();
        var dt=$('#picker_events').val();
        var data = {
                     start: page,
                     action: "next",
                     radio: value,
                     dt:dt
                 };
         $.ajax({
                 url: '/account/'+ajax_action,/*ajax_action берется из частичного представления scrypt-and-style*/
                 type: 'post',
                 dataType: "json",
                 data: data,
                 success: function( data ) {
                     if(data.success) {
                         $(self).attr("data-page",page);
                         var tb = $("#points-table-body");

                         $.each(data.items, function(i, row){
                            var tr = $("<tr class='group-heading' data-id="+row.id+" />");
                            var td=$("<td class='date_response'/>");

                            var date=String(row.dateReg).split(/[-: ]/g);
                            var d=date[2]+'.'+date[1]+'.'+date[0]+' '+date[3]+':'+date[4];

                            tr.append(td);
                            td.append($("<div id='content'/>")).text(d);
                            tb.append(tr);

                            var td=$("<td class='device_response'/>");
                            tr.append(td);
                            switch(row.data.device){
                                   case 'Компьютер': var deviceClass =  "fa fa-desktop"; break;
                                   case 'Ноутбук': var deviceClass =  "fa fa-laptop"; break;
                                   case 'Смартфон': var deviceClass =  "fa fa-mobile"; break;
                                   case 'Касса': $deviceClass = "fa fa-user"; break;
                                   default: var deviceClass =  "неизвестно";
                            }
                            var div=$("<div id='content_device' title="+row.data.device+"/>");
                            var i=$("<i class='device "+deviceClass+"'/>");
                            i.append($("<div />"));
                            div.append(i);
                            td.append(div);

                            var td=$("<td class='mark_response'/>");
                            var div=$("<div id='content'/>");
                            switch(row.data.mark){
                                   case '5': var width=100; break;
                                   case '4': var width=80; break;
                                   case '3': var width=50; break;
                                   case '2': var width=40; break;
                                   case '1': var width=20; break;
                                   default:  var width=0;
                            }
                            div.append($("<div id='value_bar' style='width:"+width+"%' />").text(row.data.mark));
                            td.append(div);
                            tr.append(td);

                            var td=$("<td class='comment_response'/>");
                            td.append($("<div id='' />").attr('id','comment').text(row.data.remark));
                            tr.append(td);

                            var td=$("<td class='button_response' />");

                            if(!row.deleted)
                            {
                                var span=$("<span class='btn-xs btn-warning pull-right delete notDeleted' title='Удалить' />").bind('click',delete_response);
                                span.append($("<span class='glyphicon glyphicon-trash'/>"));
                                td.append(span);
                            }

                            if(!row.readed)
                            {
                                var span=$("<span class='btn-xs btn-info pull-right read notReaded' title=\"Отметить как 'Прочитано'\" />").bind('click',read_response);
                                span.append($("<span class='glyphicon glyphicon-eye-open'/>"));
                                td.append(span);
                            }

                            tr.append(td);
                         });
                         //if(data.flag==1)$('a#next-page').css('opacity',0.2).css('cursor','default').unbind('mouseenter mouseleave click');
                         if(data.flag==1) $('a#next-page').remove();
                     }
                 } // success
             });//$.ajax

        return false;
    });

/*удаление строки со страницы*/
var delete_response = function()
{
     var self = $(this);
     var id = self.closest("tr").attr("data-id");
     $.ajax({
               url: '/account/'+ajax_action,
               type: 'post',
               dataType: "json",
               data: {
                   action: 'delete-response',
                   id:id
               },
               success: function( data )
               {
                 if(data.success) self.closest("tr").remove();
               }
           });//$.ajax
}

/*отметить строку как прочитанную*/
var read_response=function()
{
    var self = $(this);
    var id=$(this).closest("tr").attr("data-id");
    $.ajax({
              url: '/account/'+ajax_action,
              type:'post',
              dataType:"json",
               data: {
                   action: 'read-response',
                   id:id
               },
               success: function(data)
               {
                 if(data.success) self.remove();
               }
    });//$ajax
}

$('span.delete').on('click',delete_response);
$('span.read').bind('click',read_response);

var _post = function()
{
    var node = $("div.page-header-content #filter-form div.filter:visible");

    $("#filter-form input[name=dt]").val($("input.form-control[name='dt']").val());
    $("#filter-form input[name=accepted]").val($("input.styled:radio:checked").val());

    $("#filter-form").append($("<input name=action />").val(node.attr("id")));
    $("#filter-form")[0].submit();
}


if(button_flag=='1' )$('a#next-page').remove();

})