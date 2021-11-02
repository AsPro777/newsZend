<?php
return <<<S
$("a[name='sold-action']").on("click", function(){

    var tr = $(this).closest("tr");
    var id = tr.attr("name");
    var tag = $(this).attr("data-id");

    confirmMsg("Вы уверены?", function(){

        $.ajax({
                url: '/account/' + ajax_action,
                type: 'post',
                dataType: "json",
                data: {
                    id: id,
                    id_reis: '{$id_reis}',
                    action: 'sold-action',
                    tag: tag
                },
                success: function( data ) {
                    if(data.success==1)
                    {
                        successMsg("Операция выполнена!", function(){
                            $('#calendar').fullCalendar('refetchEvents');
                        });
                        tr.remove();
                    }
                    else
                        alertMsg(data.msg?data.msg:"Нет данных по указанному билету!");
                }, // success
            });//$.ajax
   });

   return false;
});

$("td.editable").on("click", function(){
  var self = this;

  if($(self).attr("editing")==1) // текущим пока является режим редактирования
    return false;

  // текущим пока является режим просмотра
  $(self).attr("editing", 1);
  $(self).attr("data-save", $(self).html());
  $(self).attr("data-padding", $(self).css("padding"));
  var textarea = $("<textarea />").val($(self).text())
                 .css({"width":"100%","height":$(self).css("height"),"border":"1px solid lightgray"});
  var div = '<div style="width:max-content;margin:auto;">' +
            '<button type="submit" class="btn btn-primary btn-sm editable-submit" style="padding: 0px 10px;"><i class="glyphicon glyphicon-ok" style="font-size:10px;"></i></button>' +
            '<button type="button" class="btn btn-default btn-sm editable-cancel" style="margin-left:20px;padding: 0px 10px;"><i class="glyphicon glyphicon-remove" style="font-size:10px;"></i></button>' +
            '</div>';
  $(self).html('').append(textarea).append(div).css({"padding":0});
  $(self).find("textarea").select();

  $(self).find("button").on("click", function(){
    if($(this).hasClass("editable-cancel"))
    {
        $(self).html($(self).attr("data-save")).css("padding", $(self).attr("data-padding"));
        $(self).attr("editing", null).attr("data-padding", null).attr("data-save", null);
        return false;
    }
    if($(this).hasClass("editable-submit"))
    {
        var data = {
                  action: 'edit-comment',
                  id: $(this).closest("tr").attr("name"),
                  comment: $(this).closest("td").find("textarea").val()
        };
        $.ajax({
                  url: '/account/' + ajax_action,
                  type: 'post',
                  dataType: "json",
                  data: data,
                  success: function( data ) {
                      if(data.success==1)
                      {
                          successMsg("Операция выполнена!", function(){
                            $(self).html(data.comment).css("padding", $(self).attr("data-padding"));
                            $(self).attr("editing", null).attr("data-padding", null).attr("data-save", null);
                          });
                      }
                      else
                          alertMsg(data.msg?data.msg:"Нет данных по указанному билету!");
                  }, // success
        });//$.ajax
    }
  });

});
S;
