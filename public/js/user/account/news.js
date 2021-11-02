var addFileCount=0;
var addIdForNewImg=0;

var ifAddMainPic=0;/*флаг того что в данный момент загружаем именно изображение для главной темы*/
var inputNumber=0;/*номер инпута при добавлении файлов изображения в текст. При каждом добавлении картинки инпут с типом Файл на форме будет иметь свой уникальный id*/
$(function () {   
  if(flag==1) $('a#next-page').remove();/*если изначально на самой первой стр записей меньше pageSize то не отображать кнопку Следующие*/
  var addDelDialog=$('<div class="delDialog" />').html('<span class="textDelDialog">Удалить новость?</span>');


/*клик по радиокнопкам*/
$("input.styled")
        .on("click", function(){
            $('input:radio').parent().removeClass('checked');
            $(this).parent().addClass('checked');
            _post();
            return false;
        });
      
/*вернуть отфильтрованую строку*/      
var _post=function(){
    var node = $("div.page-header-content #filter-form div.filter:visible");

    $("#filter-form input[name=accepted]").val($("input.styled:radio:checked").val());

    $("#filter-form").append($("<input name=action />").val(node.attr("id")));
    $("#filter-form")[0].submit();    
 }
 
 /*вернет id кнопки на которой клик произошел*/
 var getThisId=function(tag){ alert(11);
     var aHref=$(tag).parent().parent().find('a.aForMainPic').attr('href');
     var masHref=aHref.split('/');
     return masHref[masHref.length-1]; 
 };
     
/*удалить новость*/    
  var deleteNews=function(){
      var id=getThisId(this);
      addDelDialog.dialog
              ({
                width: 230,
                height: 190,
                resizable: false,
                appendTo:"div.content",
                buttons:
                      [{
                        text: "Да",
                        click: function() { 
                                            $.ajax({
                                                    url: '/account/news',
                                                    type: 'post',
                                                    dataType: "json",
                                                    data: { idNews:id,
                                                            action: 'deleteNews'},
                                                    success: function( data )
                                                                            {
                                                                              if(data.success==true) {
                                                                                  successMsg("Новость удалена!",
                                                                                  function(){ window.location.href = "/account/"+ajax_action; });
                                                                              }
                                                                              else  alertMsg("Не удалось удалить новость!");                             
                                                                            }
                                                   });//$.ajax
                                                   $( this ).dialog( "close" );
                                           }
                      },
                      {
                        text: "Нет",
                        click: function() {   $( this ).dialog( "close" );   }
                      }]
                });   
  };
  
  /*привязка функции Удалить новость к  кнопке*/
  $('div#deleteNews').on('click',deleteNews);
  
  /*опубликовать новость*/
  var publicNews=function(){
     var id=getThisId(this);
     var head=$(this).parent().parent().find('span.head').text();
     var dataImg=$(this).parent().parent().find('img').attr('data-img');
     if(dataImg=='true') {
                          var src=$(this).parent().parent().find('img').attr('src');
                         
                          var mainImg='<p class="singleNewsMainPic">'+
                                        '<img src='+src+' width=450 height=250>'+
                                      '</p>';
                         }
     else var mainImg='';
     
     /*получим данные для отображенияновости в окне предварительного просмотра*/
     $.ajax({
            url: '/account/news',
            type: 'post',
            dataType: "json",
            data: { idNews:id,
                    action: 'getDataNews'},
            success: function( data )
                                    { if(data.success==true) { 
                                            var publicDialog='<div class=singleNewsContent>'+
                                                                  '<h1 class="singleNewsHead">'+
                                                                       head+
                                                                  '</h1>'+
                                                                       mainImg+
                                                                  '<div class="singleNewsText">'+
                                                                       data.text+
                                                                  '</div>'+
                                                             '</div>';
                                            var addPubDialog=$('<div class="pubDialog" />').html(publicDialog);
                                            addPubDialog.dialog
                                                               ({
                                                                  width: window.outerWidth-150,
                                                                  height: window.outerHeight-150,
                                                                  resizable: false,
                                                                  appendTo:"div.content",
                                                                  buttons:
                                                                          [{
                                                                             text: "Опубликовать",
                                                                             click: function() { 
                                                                                                 $.ajax({
                                                                                                        url: '/account/news',
                                                                                                        type: 'post',
                                                                                                        dataType: "json",
                                                                                                        data: { idNews:id,
                                                                                                                action: 'setPublicNews'},
                                                                                                        success: function( data )
                                                                                                                                {
                                                                                                                                 if(data.success==true) {
                                                                                                                                                          successMsg("Новость опубликована!",
                                                                                                                                                          function(){ window.location.href = "/account/"+ajax_action; });
                                                                                                                                                        }
                                                                                                                                 else  alertMsg("Не опубликовать удалить новость!");                             
                                                                                                                                }
                                                                                                      });
                                                                                                 $( this ).dialog( "close" );
                                                                                                }
                                                                            },
                                                                            {
                                                                              text: "Закрыть",
                                                                              click: function() {   $( this ).dialog( "close" );   }
                                                                            }]
                                                                });
                                                                if(data.public==true) $('div.ui-dialog').css('background-color','rgb(36, 36, 36)').css('color','rgb(255, 255, 255)');
                                                                if(data.inside==true) $('div.ui-dialog').css('background-color','rgb(255, 255, 255)');
                                                              }
                                       else alertMsg("Не удалось загрузить новость для просмотра!"); }
     });//$.ajax
  };

 /*привязка функции Опубликовать новость к  кнопке*/
  $('div#publicNews').on('click',publicNews);
        
   /*открывает диалоговое окно при нажатии на Загрузить файлы*/           
   var addDialog=function(inp_lab){
     if(ifAddMainPic==0){
       var imgDialog='<div class="div-add-img">'+
                        '<span class="add-img ">Выберите файл</span>'+
                        '<input class="add-img load" style="margin-left: 48px;">'+
                        inp_lab+
                        '<p> </p>'+
                        '<span class="add-img">Текст для изображения</span>'+
                        '<input class="add-img alt">'+
                        '<p> </p>'+
                        '<span class="add-img">Толщина рамки</span>'+
                        '<input class="add-img border" style="margin-left: 46px;">'+
                        '<p> </p>'+
                        '<span class="add-img">Горизонтальный отступ</span>'+
                        '<input class="add-img hspace" style="margin-left: 2px;">'+
                        '<p> </p>'+
                        '<span class="add-img">Вертикальный отступ</span>'+
                        '<input class="add-img vspace" style="margin-left: 14px;">'+
                        '<p> </p>'+
                        '<span class="add-img">Высота изображения</span>'+
                        '<input class="add-img height" style="margin-left: 15px;">'+
                        '<p> </p>'+
                        '<span class="add-img">Ширина изображения</span>'+
                        '<input class="add-img width" style="margin-left: 13px;">'+
                        '<p> </p>'+
                        '<span class="add-img">Выравнивание</span>'+
                        '<select class="add-img align" style="margin-left: 54px;">'+
                          '<option style="display:none">'+
                          '<option value="bottom">bottom</option>'+
                          '<option value="left">left</option>'+
                          '<option value="middle">middle</option>'+
                          '<option value="right">right</option>'+
                          '<option value="top">top</option>'+
                        '</select>'+
                        '<p> </p>'+
                      '</div>';  
     }
     else{
         var imgDialog='<div class="div-add-img">'+
                        '<span>Выберите файл</span>'+
                        '<input class="add-img load" style="margin-left: 20px;">'+
                        inp_lab+
                       '</div>';
     }
     var addDialogImg =$("<div id='dialog-images' />").append(imgDialog);  
     $("div.content").append(addDialogImg);
     $('input.border , input.hspace , input.vspace , input.height , input.width').mask('0000000000000000000000000');
     $("button.ui-dialog-titlebar-close").css('visibility','hidden');
     var showLoadFiles=function(file,loadDiv){/*выбор файла для загрузки*/
      if(file)
      {
               var accept_mimes = ['image/jpeg','image/png','image/jpg'];
               if( accept_mimes.indexOf(file[0].type)==-1 ){
                 return alertMsg('Принимаются только файлы jpeg, jpg или png!');}
               if(parseInt(file[0].size) > 1024*1000*15){
                 return alertMsg('Размер файла не должен превышать {$max_size} Mb!');}
               $('input.load').val(file[0].name);
               if($('span.iconClose').length==0){/*если мы первый раз нажимаем на Загрузить файлы*/
                 var divCloseLoadFile=$('<span class="iconClose"></span>');/*значек Закрыть после спана с именем файла*/
                 $('input.load').after(divCloseLoadFile);
                 
                 $('span.iconClose').bind('click',function(){ 
                 $('.div-add-img').find('#addImgInNewNews').remove();
                 var input=$('<input type="file" name=addImgInNewNews id=addImgInNewNews style="display:none;outline:0;opacity:0;pointer-events:none;user-select:none">').on("change",changeFile);
                 $('label.load-img').after(input);
                 $('input.load').val('');
                 $(this).remove();
                 });
               }  /*если жмем второй раз файл в инпуте перезапишется*/          
      } // if
     };
     
     /*реакция на выбор изображения*/
     var changeFile=function(){ 
       var files = $(this).prop("files");  
       var inputLoadFile=$(this).clone();/*создать инпут с типом Файл связаный с загруженым файлом путем клонирования*/
       /*inputLoadFile.attr('style','display:none');*/
       if(ifAddMainPic==1){
          if($('form').find('#inputLoadMainImg').length!==0)$('input#inputLoadMainImg').remove();
          inputLoadFile.attr('id','inputLoadMainImg');
          inputLoadFile.attr('name','inputLoadMainImg'); 
       }
       else{
          inputLoadFile.attr('id','inputLoadFile'+inputNumber);
          inputLoadFile.attr('name','inputLoadFile'+inputNumber);
      }
       inputNumber++;
       var formLoadFile=$('form.panel-body'); 
       formLoadFile.append(inputLoadFile);
         
       showLoadFiles(files); 
     };
     
     if(ifAddMainPic==0){
       addDialogImg.dialog
              ({
                width: 350,
                height: 400,
                resizable: false,
                appendTo:"div.content",
                buttons:
                      [{
                        text: "Закрыть",
                        click: function() {   
                            if($('form').find('input[type=file]').length!==0) $('form').find('input[type=file]').remove();
                            $( this ).dialog( "close" );    }
                      },
                      {
                        text: "Ок",
                        click: function() {
                          if($('input.load').val()!=='' ){
                            addIdForNewImg=addIdForNewImg+1;
                            var textNews=$('textarea.add-news').val();
                            if($(this).find('input.alt').val()!=='')var alt=' alt="'+$(this).find('input.alt').val()+'" '; else var alt='';
                            if($(this).find('input.border').val()!=='')var border='border="'+$(this).find('input.border').val()+'" '; else var border='';
                            if($(this).find('input.hspace').val()!=='')var hspace='hspace="'+$(this).find('input.hspace').val()+'" '; else var hspace='';
                            if($(this).find('input.vspace').val()!=='')var vspace='vspace="'+$(this).find('input.vspace').val()+'" '; else var vspace='';
                            if($(this).find('input.height').val()!=='')var height='height="'+$(this).find('input.height').val()+'" '; else var height='';
                            if($(this).find('input.width').val()!=='')var width='width="'+$(this).find('input.width').val()+'" '; else var width='';
                            if($(this).find('select.align').val()!=='')var align='align="'+$(this).find('select.align').val()+'" '; else var align='';
                            var img='<img id="'+addIdForNewImg+'" src="/account/news?" '+alt+border+hspace+vspace+height+width+align+'> ';
                            /*textNews=textNews+' '+'<a target="_blanc" href="/account/news?">'+img+'</a>';*/
                            textNews=textNews+' '+img;
                            $('textarea').val(textNews);
                        }
                            $( this ).dialog( "close" );
                        }
                      }]
                });
     }
     else{
        addDialogImg.dialog
              ({
                width: 330,
                height: 200,
                resizable: false,
                appendTo:"div.content",
                buttons:
                      [{
                        text: "Закрыть",
                        click: function() {   
                            if($('form').find('input#inputLoadMainImg').length!==0) $('form').find('input#inputLoadMainImg').remove();
                            $( this ).dialog( "close" );    }
                      },
                      {
                        text: "Ок",
                        click: function() {   $( this ).dialog( "close" );   }
                      }]
                }); 
     }
     /* $("div.div-add-img").find("[name^=addImgInNewNews]").unbind("change").on("change",changeFile);*/
     /* $("div.div-add-img").find("#addMainImgInNewNews").unbind("change").on("change",changeMainPic);*/
     $("div.div-add-img").find(":file").unbind("change").on("change",changeFile);
     return;
   };
      
/*загрузка изображений для вставки в текст новости*/      
  $("label#load-images").on("click",function(){
      ifAddMainPic=0;
      var inp_lab='<label class="load-img" for="addImgInNewNews"></label>'+
              '<input type="file" name="addImgInNewNews" id=addImgInNewNews style="display:none;outline:0;opacity:0;pointer-events:none;user-select:none">';
      addDialog(inp_lab);
  }); 
  
  /*загрузка главного изображения*/
  $("label#load-main-img").on("click",function(){
      ifAddMainPic=1;
     /* if($('form').is('#addMainImgInNewNews')==true)$('form').find("#addMainImgInNewNews").remove();*/
      var inp_lab='<label class="load-img" for="addMainImgInNewNews"></label>'+
              '<input type="file" name="addMainImgInNewNews" id=addMainImgInNewNews style="display:none;outline:0;opacity:0;pointer-events:none;user-select:none">';
      addDialog(inp_lab);
  });
      
  /*если кликнули чекбокс В учетную запись то ниже открыть Кому посылать с комбобоксом*/     
  $("#onSite, #onUserAccount").on('change',function(){
    var sendSite = $("#onSite").prop("checked");
    var sendAccount=$("#onUserAccount").prop("checked");
    if ((sendSite == true)||(sendAccount == true)) 
        $("input#submitNews").css("opacity", 1).css("cursor", "pointer");
    
    if ((sendSite == false)&&(sendAccount == false)) 
        $("input#submitNews").css("opacity", 0.5).css("cursor", "not-allowed");
    
    if((sendAccount==true)&&($('legend.whoSend').length)==0){
      var legend=$('<legend class="whoSend" />').text('Кому выводить');
      var fieldset=$('<fieldset />').append(legend);
      var combo=$('<select name="whoSend" id="whoSend" />').append('<option selected value="all" label="Всем" />')
                                                           .append('<option value="account" label="По типам учетных записей" />')
                                                           .append('<option value="personal" label="Персонально" />');
      combo.on('change',function(){/*если выбрали в комбобоксе*/ 
               var sendOpt = $("#whoSend").val();
               if(sendOpt=='account'){/*если выбрали По типу учетной записи*/
                         var divAccount=$('<div id="divAccount" />');
                         var carrier=$('<label />').text(' Перевозчик').prepend('<input type="checkbox" id="carrier" name="carrier" value="3" checked />');
                         var passenger=$('<label />').text(' Пассажир').prepend('<input type="checkbox" id="passenger" name="passenger" value="2" checked />');
                         var terminal=$('<label />').text(' Вокзал').prepend('<input type="checkbox" id="terminal" name="terminal" value="5" checked />');
                         divAccount.append('<br>').append(carrier).append('<br>').append(passenger).append('<br>').append(terminal);
                         $('legend.whoSend').parent().after(divAccount);
               }
               if(sendOpt=='personal'){/*Если выбрали Персонально то открыть модальное окно*/
                         if($('div#divAccount').length!==0)$('div#divAccount').empty();
                         addPersonalDiv();/*подключит функцию Автозаполнение для инпута */

               return false;
             }
             if(sendOpt=='all'){
                if($('div#divAccount').length!==0)$('div#divAccount').empty(); 
             }
  });
      fieldset.append(combo);
      var whoSend=$('<div class="whoSend" />').append(fieldset);
      $('div.whenSend').after(whoSend);
      return false;
    }
    
    if(sendAccount==false){
        var whoSend=$('div.whoSend');
        if(whoSend.length!==0) $('div.whoSend').empty();
    }
  });  
  
  var inputNumber=0;
  
  $("a.btn.btn-show-form").on("click",function(){
    $("div.new-news").show();
  });

  $("div.new-news input[type='reset']")
    .on("click", function(){
        if($('form').find('input[type=file]').length!==0) $('form').find('input[type=file]').remove();
        $("div.new-news").hide();
    });
    
/*кнопка "Показать следующие 5 записей"*/
$("#next-page").on("click", function(){
        var self = this; 
        var page = 1 + parseInt($(self).attr("data-page"));
        var prop=$('div#filterNews').find('span.checked').find('input').val();
        var data = {
                     start: page,
                     action: "next",
                     userTypeId: user_type_id,
                     property: prop
                 };
         $.ajax({
                 url: '/account/'+ajax_action,/*ajax_action берется из частичного представления scrypt-and-style*/
                 type: 'post',
                 dataType: "json",
                 data: data,
                 success: function( data ) { 
                     if(data.success) { alert(11);
                         $(self).attr("data-page",page);
                         $.each(data.heads,function(i,row){
                            var ul = $("<ul class='media-list chat-stacked content-group' />");
                            var liDat=$("<li class='media date-step content-divider text-muted' />");
                            var date=String(row.dateReg).split(/[-: ]/g);
                            var d=date[0]+'-'+date[1]+'-'+date[2]+' Новость-';
                            if(row.personal==true) d=d+' персональная';
                            if(row.public==true) d=d+' для паблика';
                            if(row.inside==true) d=d+' внутренняя';
                            if(row.carrier==true) d=d+' для перевозчика';
                            if(row.termanal==true) d=d+' для терминала';
                            if(row.passenger==true) d=d+' для пассажира';
                            var span=$("<span />").text(d);
                            var i=$("<i class='icon-calendar2 position-right text-muted' />");
                            span.prepend(i);
                            liDat.append(span);
                            ul.append(liDat);
                            
                            if(row.addFiles!=='{}') var obj = $.parseJSON(row.addFiles);
                            else var obj='';
                            var liText=$("<li class='media' />");
                            var divBody=$("<div class='media-body' />");
                            var aHead=$('<a href=/account/singleNews/'+row.id+'/>');
                            var span=$("<span class='head' />").text(row.head);
                            aHead.append(span);
                            divBody.append(aHead);
                            liText.append(divBody);
                            ul.append(liText);

                            if(obj!=='')
                                if(obj.mainPic.length!==0)
                                  var imgMainPic=$('<img src="/account/news?id='+row.id+'&name='+obj.mainPic[0].name+'&selectPic=true" width=70 height=70 data-img=true />');
                                else var imgMainPic=$('<img src="/img/about.jpg" width=70 height=70 data-img=false />');
                            else var imgMainPic=$('<img src="/img/about.jpg" width=70 height=70 />');
                            var aMainPic=$('<a class="aForMainPic" href=/account/singleNews/'+row.id+'/>');
                            aMainPic.append(imgMainPic);
                            divBody.append(aMainPic);

                            var text=row.text;
                            var masNoImg=text.split(/<img[^>]*?>/g);
                            var longTextNoImg=masNoImg.join(' '); 
                            var textNoImg=longTextNoImg.slice(0,100);
                            var div=$('<div class="textForShortNews" />');
                            div.text(textNoImg);
                            divBody.append(div);
                            
                            $('div.content').append(ul);
                        
                            if(user_type_id==1){
                              var divButton=$('<div class="changeNewsButtons" />');
                              var changeNews=$('<div id="changeNews" />');
                              var a=$('<a href=/account/edit-news/'+row.id+' />').append('<label class="btn bg-teal-400">Редактировать</label>');
                              changeNews.append(a);
                              divButton.append(changeNews);
                              
                              var delNews=$('<div class="deleteNews" />').append('<label class="btn bg-teal-400">Удалить</label>');
                              delNews.on('click',deleteNews);
                              divButton.append(delNews);
                              if(row.status=='draft'){
                                var pubNews=$('<div class="publicNews" />').append('<label class="btn bg-teal-400">Просмотреть/опубликовать</label>');
                                pubNews.on('click',publicNews);
                                divButton.append(pubNews);
                              }
                              divBody.append(divButton);
                            }
                            
                         });

                         if(data.flag==1) $('a#next-page').remove();
                     }
                 } // success
             });//$.ajax

        return false;
    });
  
/*отправить на сервер заголовок, текст новости и прикрепленные файлы если таковые имеются*/
  $("input#submitNews")
    .on("click", function(obj){
        addIdForNewImg=0;
        
        if($("div.new-news input[name='name']").val() == "")
        {
            alertMsg("Необходимо ввести заголовок!");
            return false;
        }
        if($("div.new-news textarea[name='text']").val() == "")
        {
            alertMsg("Необходимо ввести текст новости!");
            return false;
        }
        if($("div.new-news textarea[name='text']").val().length < 20)
        {
            alertMsg("Слишком короткий текст новости!");
            return false;
        }
  
        /*var input=$('input#addFilesInNews');*/
        var input=$('form.panel-body').find('input:file'); 
        /*$(obj.currentTarget).parent().parent().find('input#addFilesInNews').attr('type','text');*/
        var form=document.querySelector('form.panel-body');
        var data = new FormData(form);/*создадим объект FormData для хранения данных из формы*/

        data.set("action","new-news");/*добавим в объект нужные поля*/
        
        
        
        if($('div.whoSend').find('select#whoSend').length!==0) { 
              var whoSend=$('select#whoSend').val();
              data.set("whoSend",whoSend);
              if(whoSend=='personal') data.set("idPerson",idPerson);
        }
        
        loadFilesOnServer(input,form,data,"Новость сохранена"); 
        return true;
    });

/*загрузить данные на сервер*/
var loadFilesOnServer=function(input,form,data,msg){

    var request = new XMLHttpRequest();/*создаем объект XMLHttpRequest*/
    request.open("POST","/account/news", true);/*создаём POST-запроc(только инициализируем его не отправляем)(1-ый парам) отсылаемый в "/account/tickets" с типом запроса-асинхронный*/              
         request.onload = function(oEvent)
                {
                   if (request.status == 200)
                   {
                       try{
                            var data = JSON.parse(request.response);
                        } catch(err)
                        {
                            alertMsg("Неудачно!<p>" + request.responseText);
                        }
                        if(data.success)
                        {
                            successMsg(msg+"!", function(){
                            window.location.href = "/account/"+ajax_action;
                           });
                        }else{
                               alertMsg("Неудачно!<p>" + data.msg)
                        };
                   } else {
                     $("#preview").html("Не удалось загрузить файл! Описание ошибки сервера: <br> " + request.status + '<br>' + request.statusText + '<br>' + request.responseText);
                   }
                }; 
        request.send(data);
        return true;
}
      
// of $(function () {
})

