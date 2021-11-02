<?php
return <<<S

$("#modal-move-paxes select[name='to_reis']")
    .unbind("change")
    .on("change", function(){
    
        $("#modal-move-paxes input.place").val('').removeClass('bg-danger');
        var id = $(this).val();

        $.ajax({
            url: "/account/"+ajax_action,
            type: 'post',
            dataType: "json",
            data: {
                action: 'get-empty-places-on-reis',
                id_reis: id,
            },
            success: function( data ) {
                if(data.success)
                {
                    var empty_places = data.places.split(',');
                    $.each(empty_places, function(i, val){

                        var input = $("#modal-move-paxes input.place[name='" + val + "']");
                        if(input.length==0) return;

                        var checkbox = $("#modal-move-paxes :checkbox[data-name='" + val + "']");
                        if(checkbox.prop("checked")==false) return;

                        input.val(val);
                        delete empty_places[i];
                    });

                    $.each($("#modal-move-paxes input.place").filter( function(value) {return !isNaN(value);} ), function(i, obj){

                        empty_places = empty_places.filter( function(value) {return !isNaN(value);} );                        
                        if( !empty_places.length ) return;
                        
                        var input    = $("#modal-move-paxes   input.place[name='" + $(obj).attr('name') + "']");
                        if(input.val()) return;

                        var checkbox = $("#modal-move-paxes :checkbox[data-name='" + $(obj).attr('name') + "']");
                        if(checkbox.prop("checked")==false) return;
                        
                        input.val(empty_places[0]);
                        delete empty_places[0];
                    });


                    var check_count = $("#modal-move-paxes :checkbox[data-type='select-place']:checked").length;
                    if(check_count==0)
                        return alertMsg("Не отмечены пассажиры для перемещения!");
                    
                    if($("#modal-move-paxes #selected_only").prop("checked"))    
                    {
                        var sets_count = $("#modal-move-paxes input.place").filter( function(value) {return this.value;} ).length;
                        if(check_count>sets_count)
                            return alertMsg("Не всем отмеченным пассажирам назначены места для перемещения!", function(){
                            $.each($("#modal-move-paxes :checkbox[data-type='select-place']:checked"), function(i, obj){
                                $("#modal-move-paxes input.place[name='"+$(obj).attr("name")+"']").filter( function(value) {return !this.value;} ).addClass('bg-danger');
                            });                            
                        });
                    }
                    else
                        if($("#modal-move-paxes input.place").filter( function(value) {return !this.value;} ).length>0)
                            alertMsg("Недостаточно свободных мест для перемещения всех пассажиров!", function(){
                                $("#modal-move-paxes input.place").filter( function(value) {return !this.value;} ).addClass('bg-danger');
                            });
                }
            } // success
        });//$.ajax                                        

    });

$("#modal-move-paxes").data("validate", function()
{        
    var params = [], c = $("#modal-move-paxes"), validated = true;
    c.data("params", params);

    $.each(c.find("input.place").filter( function(value) {return !isNaN(value);} ), function(i, obj){

        var checkbox = $("#modal-move-paxes :checkbox[data-name='" + $(obj).attr('name') + "']");

        if(checkbox.prop("checked"))
            params.push([$(obj).closest("tr").attr("name"), $(obj).val(), $(obj).attr("data-reserved")]);
    });

    validated = (params.length > 0) && c.find("select[name='to_reis']").val();

    if( c.find("select[name='to_reis']").val() == 0 )
    {
        return alertMsg("Не выбран рейс для перемещения!");
    }

    if( params.length <= 0 )
    {
        return alertMsg("Не задано ни одного пассажира для перемещения!");
    }

    c.data("params", params);    
    return true;     
});


$("#modal-move-paxes #selected_only")
    .unbind("change")
    .on("change", function(){
        $("#modal-move-paxes :checkbox[data-node='selection']")
            .prop("checked", true)
            .attr("disabled", !$(this).prop("checked"));

        $("#modal-move-paxes select[name='to_reis'] option[data-disabled='true']")
            .attr("disabled", !$(this).prop("checked"));
        
    });

$("#modal-move-paxes #select_all")
    .unbind("change")
    .on("change", function(){
        $("#modal-move-paxes :checkbox[data-type='select-place']").prop("checked", $(this).prop("checked"));
    });

$("#modal-move-paxes #to_reis_reload")
    .css({"cursor":"pointer"})
    .unbind("click")
    .on("click", function(){
        $("#modal-move-paxes select[name='to_reis']").trigger("change");
    });


S;
