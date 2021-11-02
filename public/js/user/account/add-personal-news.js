    var name='';
    var idPerson=0;
    /*var flag_select=0;
    var count=0;*/
    var addPersonalDiv=function(){
      var personalDiv=$('<div class="personalNews" />')
                      .append('<input name="person" class="form-control" type="text" placeholder="Введите ФИО, id, логин или номер телефона" />')
                      .append('<span class="iconClose" />');
      $('div.whoSend').append(personalDiv);

       $('input[name="person"]').autocomplete
       ({
         minLength: 0,
         source: function( request, response )
         {
           $.ajax({
                   url: '/account/get-user-info',
                   type: 'post',
                   dataType: "json",
                   data:
                       { 
                         query: request.term,
                       },
                   success: function( data )
                       { 
                         response( data );
                       },
                   beforeSend: function(jqXHR, settings) {},
           });//$.ajax
         },
        });
        
        var tbody=$("<tbody class='tbodyUsersInfo points-table-body'></tbody>");
        var table=$("<table class='usersTable table table-striped table-hover table-framed table-xxs'></table>"); 
        var thead="<thead class='theadUserInfo'>"+
                       "<tr>"+
                          "<th>Логин</th>"+
                          "<th>Тип учетной записи</th>"+
                          "<th>ФИО/Название организации</th>"+
                       "</tr>"+
                  "</thead>";
        $(table).html(thead);
        var divUserTable=$('<div class="divUsersTable"></div>');
        $(table).append(tbody);
        $(divUserTable).append(table);
        $('div.personalNews').after(divUserTable);
        
        /*привязка функции к событию выбора в инпуте из выпадающего списка*/
        $('input[name="person"]').bind( "autocompleteselect", function(event, ui){  
              idPerson=ui.item.id;
              name=ui.item.label;
              /*if(flag_select==0){
                var tbody=$("<tbody class='tbodyUsersInfo points-table-body'></tbody>");
                var table=$("<table class='usersTable table table-striped table-hover table-framed table-xxs'></table>"); 
                var thead="<thead class='theadUserInfo'>"+
                             "<tr>"+
                                "<th>Логин</th>"+
                                "<th>Тип учетной записи</th>"+
                                "<th>ФИО/Название организации</th>"+
                             "</tr>"+
                          "</thead>";
                $(table).html(thead);
                flag_select=1;
                var divUserTable=$('<div class="divUsersTable"></div>');
                $(table).append(tbody);
                $(divUserTable).append(table);
                $('div.personalNews').after(divUserTable);
              }*/
              
              $.ajax({
               url: '/account/news',
               type: 'post',
               dataType: "json",
               data: { action: 'autocompleteSelect',
                       id: ui.item.id },

               success: function( data )
               { 
                /* count++;*/
                 var tr=$('<tr class="trUserInfo" />'); 
                 var table=$('table.usersTable');
                 $(table).append(tr);
                 var tdLogin=$('<td class="tdUserInfo" />');
                 tdLogin.text(data.login);
                 tr.append(tdLogin);
                 
                 var tdUserType=$('<td class="tdUserInfo" />');
                 tdUserType.text(data.usrType);
                 tr.append(tdUserType);
                 
                 var tdFio=$('<td class="tdUserInfo" />');
                 tdFio.text(name);
                 tr.append(tdFio);
                 
                 var tdButton=$('<td class="buttons" />');
                 var spanBtn1=$('<span class="btn-xs btn-warning pull-right btn-remove"/>');
                 $(spanBtn1).on("click",function(ev,tar){
                     /*count--;*/
                     $(this).parent().parent().remove();
                     /*if(count==0) {
                         $('.theadUserInfo > tr').remove();
                     }
                     if($('.theadUserInfo').children()==''){}*/
                 });
                 var spanBtn2=$('<span class="glyphicon glyphicon-trash" />');
                 $(spanBtn1).append(spanBtn2);
                 $(tdButton).append(spanBtn1);
                 tr.append(tdButton);
               },
           });
             });
        
        $('span.iconClose').on('click',function(){
            $(this).prev().val('');
        });
    }

