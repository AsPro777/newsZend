 
var maxName=0;
var id;
var addIdForNewImg=0;
var ifAddMainPic=0;/*флаг того что в данный момент загружаем именно изображение для главной темы*/
var inputNumber=1;/*номер инпута при добавлении файлов изображения в текст. При каждом добавлении картинки инпут с типом Файл на форме будет иметь свой уникальный id*/
$(function () { 
        id=document.location.href;/*id новости*/
        id=id.split('/');
        var len=id.length;
        id=id[len-1];
        
   Array.prototype.max = function() {
        return Math.max.apply(null, this);
   };     
   /*id последней картинки*/  
   var textNews=$('textarea.edit-news').val();
   textNews=textNews.split('<img id=');
   var masId=[];
   for(var i=0;i<textNews.length;i++){
       var pos=textNews[i].indexOf('src');
       if(pos!==-1) {
                     var j=textNews[i].slice(0,pos);
                     j=j.split('"');
                     masId.push(Number(j[1]));
       }
   }
   if(masId.length!==0)addIdForNewImg=masId.max();
   
   /*наибольшее значение name*/  
   var textNews=$('textarea.edit-news').val();
   textNews=textNews.split('&name=');
   var masName=[];
   for(var i=0;i<textNews.length;i++){
       var pos=textNews[i].indexOf('&');
       if(pos!==-1) {
                     var j=textNews[i].slice(0,pos);
                     j=j.split('"');
                     masName.push(Number(j[0]));
       }
   }
   if(masId.length!==0)maxName=masName.max();
   
  /*привязка функции к кнопке х рядом с изображением в тексте новости. Удалим изображение из предварительного просмотра и из БД*/
    $("span.iconClose").on('click',function(){ 
                                               var src=$(this).prev().attr('src');
                                               deleteImg(src,$(this));
                                             });
                                             
  /*привязка функции к  кнопке закрытия страницы редактирования*/
  $("label#exit-button").on('click',function(){
      window.location.href="/account/news";
  });
  
  var publicNews=function(){
    var id=document.URL;
    id=id.split('/');
    id=id[id.length-1];
     /*получим данные для отображенияновости в окне предварительного просмотра*/
     $.ajax({
            url: '/account/news',
            type: 'post',
            dataType: "json",
            data: { idNews:id,
                    action: 'setPublicNews'},
            success: function( data )
                                    { if(data.success==true) { 
                                                               successMsg("Новость опубликована!");
                                                               window.location.href="/account/news";
                                                              }
                                       else alertMsg("Не удалось опубликовать новость!"); }
     });//$.ajax  
  };
  
  /*привяжем к кнопке Опубликовать функцию*/  
  $("label#public-button").on('click',publicNews);
    
  /*нажатие на кнопку Обновить*/
  $("input#submitRefreshButton")
    .on("click", function(obj){
        
        var newHead=$('input[name=editHeadNews]').val();
        var newText=$('textarea[name=editTextNews]').val();
        var newDate=$('input[name=editDateNews]').val();
        var files=$('input[type=file]').length;
                   
        if(($.trim(newHead)=='')&&($.trim(newText)=='')&&($.trim(newDate)=='')&&(files==0))
        {
          alertMsg("Информация для обновления отсутствует!");
          return false;
        }

        var input=$('form.panel-body').find('input:file'); 
        var form=document.querySelector('form.panel-body');
        var data = new FormData(form);/*создадим объект FormData для хранения данных из формы*/

        data.set("action","refresh-news");/*добавим в объект нужные поля*/
        data.set("id",id);
        
        loadFilesOnServer(input,form,data,"Новость обновлена"); 
        return true;
    });
    
    /*обновить у новости заголовок, текст, дату и прикрепленные файлы. Загрузка на сервер*/
    var loadFilesOnServer=function(input,form,data,msg){

    var request = new XMLHttpRequest();/*создаем объект XMLHttpRequest*/
    request.open("POST","/account/editNews", true);/*создаём POST-запроc(только инициализируем его не отправляем)(1-ый парам) отсылаемый в "/account/tickets" с типом запроса-асинхронный*/              
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
                            window.location.href = "/account/"+ajax_action+"/"+id;
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
     $("div.viewNews").append(addDialogImg);

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
                appendTo:"div.viewNews",
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
                            maxName=maxName+1;
                            var textNews=$('textarea.edit-news').val();
                            textNews=textNews.split('<img id=');
                            var pos=textNews[textNews.length-1].indexOf('src');
                            var idNews=textNews[textNews.length-1].slice(0,pos)+1;/*id последней картинки*/
                            
                            if($(this).find('input.alt').val()!=='')var alt=' alt="'+$(this).find('input.alt').val()+'" '; else var alt='';
                            if($(this).find('input.border').val()!=='')var border='border="'+$(this).find('input.border').val()+'" '; else var border='';
                            if($(this).find('input.hspace').val()!=='')var hspace='hspace="'+$(this).find('input.hspace').val()+'" '; else var hspace='';
                            if($(this).find('input.vspace').val()!=='')var vspace='vspace="'+$(this).find('input.vspace').val()+'" '; else var vspace='';
                            if($(this).find('input.height').val()!=='')var height='height="'+$(this).find('input.height').val()+'" '; else var height='';
                            if($(this).find('input.width').val()!=='')var width='width="'+$(this).find('input.width').val()+'" '; else var width='';
                            if($(this).find('select.align').val()!=='')var align='align="'+$(this).find('select.align').val()+'" '; else var align='';
                            var img=' <img id="'+addIdForNewImg+'" src="/account/news?id='+id+'&name='+maxName+'&selectPic=false" '+alt+border+hspace+vspace+height+width+align+'> ';
                            /*будем вставлять тег img в то место в текстареа где стоит курсор. id у нового img будет=наибольший id из массива img+1*/
                            var txtarea =document.getElementById('edit-text-news');
                                           
	                    //ищем первое положение выделенного символа
	                    var start = txtarea.selectionStart;
	                    //ищем последнее положение выделенного символа
	                    var end = txtarea.selectionEnd;
	                    // текст до + вставка + текст после (если этот код не работает, значит у вас несколько id)
	                    var finText = txtarea.value.substring(0, start) + img + txtarea.value.substring(end);
	                    // подмена значения
	                    txtarea.value = finText;
	                    // возвращаем фокус на элемент
	                    txtarea.focus();
	                    // возвращаем курсор на место - учитываем выделили ли текст или просто курсор поставили
	                    txtarea.selectionEnd = ( start == end )? (end + img.length) : end ;
                            
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
                appendTo:"div.viewNews",
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
  $("label#load-edit-images").on("click",function(){ 
      ifAddMainPic=0;
      var inp_lab='<label class="load-img" for="addImgInNewNews"></label>'+
              '<input type="file" name="addImgInNewNews" id=addImgInNewNews style="display:none;outline:0;opacity:0;pointer-events:none;user-select:none">';
      addDialog(inp_lab);
  });
  
    /*загрузка главного изображения*/
  $("label#load-edit-main-img").on("click",function(){
      ifAddMainPic=1;
     /* if($('form').is('#addMainImgInNewNews')==true)$('form').find("#addMainImgInNewNews").remove();*/
      var inp_lab='<label class="load-img" for="addMainImgInNewNews"></label>'+
              '<input type="file" name="addMainImgInNewNews" id=addMainImgInNewNews style="display:none;outline:0;opacity:0;pointer-events:none;user-select:none">';
      addDialog(inp_lab);
  });
                                          
   /*удаление изображения из окна предварительного просмотра и из базы*/
  var deleteImg=function(src,elem){
      
    var classPic=$(elem).parent().attr('class');
    
    if(classPic!=='singleNewsMainPic'){ alert(1221);
     var updateTextWithImg=$('div.singleNewsText').html();
     var textWithoutDiv=updateTextWithImg.split('<div id="deleteImg">');
     var updateText='';
     for(var i=0;i<textWithoutDiv.length;i++){
         if(textWithoutDiv[i].indexOf('<span class="iconClose">')!==-1){
             var textWithoutSpanAndDiv=textWithoutDiv[i].replace('<span class="iconClose"></span>','');
             textWithoutSpanAndDiv=textWithoutSpanAndDiv.replace('</div>','');
             textWithoutDiv[i]=textWithoutSpanAndDiv;
         }
         updateText=updateText+textWithoutDiv[i];
     }
      updateText=updateText.trim();/*удалим из текста новости div и span*/

     /*узнаем name удаляемого img*/
     var nameSrc=src.split('&');
     var nameImg=nameSrc[1].split('=');
     nameImg=nameImg[1];
    
     var updateTextWithoutImg=updateText.split(' ');
     var newText='';
     for(var i=0;i<updateTextWithoutImg.length;i++){
       if(updateTextWithoutImg[i]=='<img'){
           var img=updateTextWithoutImg[i];
           for(var j=i+1;j<updateTextWithoutImg.length;j++){
               img=img+' '+updateTextWithoutImg[j];
               if(updateTextWithoutImg[j].indexOf('>')>=0)
                {
                  var val='name='+nameImg;
                  var reg=new RegExp(val);
                  if(img.search(reg)!==-1)img='';
                  break;
                }
           }
           newText=newText+' '+img;
           img='';
           i=j+1;
       }
       if(i<updateTextWithoutImg.length)newText=newText+' '+updateTextWithoutImg[i];
    }
    
    newText=newText.trim();
  
    var data={'idNews':id,
              'textNews':newText,
              'nameNews':nameImg};
   }
   else {
         var data={'nameNews':'deleteMainPic',
                  'idNews':id};
        }
    
    $.ajax({
            url: '/account/news',/*получим строку из табл по id*/
            type: 'post',
            dataType: "json",
            data: { dataSend:data,
                    action: 'updateImagesNews'},
            success: function( data )
                                    { 
                                      if(data.success==true) window.location.reload();       
                                      else alertMsg("Не удалось удалить изображение!");       
                                    }
                            });
 
  }
  
  
});