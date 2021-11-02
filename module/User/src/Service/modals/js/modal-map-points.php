<?php
return <<<S

    var mode = '{$mode}';
    var center = {$center};
    var points_map = false;
    var point_placemark = false;
    var city_coord = [0,0];
    var clusterer;
    var balloonContentLayout;
    var buttonLayout;
    var button;
    var selected_city_obj;
    var adding = false;
    var create_btn_title = "Создать остановку";

    var make_info_icons = function(conf)
    {
        var info_point_icons = {$info_point_icons};
        if(!conf) return "";

        if(typeof conf == 'string') conf = JSON.parse(conf);
        var result = '';

        $.each(conf, function(node, tags){
            if(info_point_icons[node])
             $.each(tags, function(i, tag){
               if(info_point_icons[node][tag])
               {
                    var inf = info_point_icons[node][tag];
                    result += ( '<i class="' + inf['icon'] + '" title="' + inf['name'] + '\\r\\n' + inf['info'] + '" style="padding-left:3px;color:#007332;font-size:12px;"></i>' );
               }
             })
        });
        return result;
    }

    var ymaps_init = function(){

        button = new ymaps.control.Button({
            data: {
                content: "<i class='icon-add'></i> "+create_btn_title,
                title: "Создать новую остановку"
            },
            options: {
                selectOnClick: true,
                maxWidth: 160,
                size: "large"
            }
        });

        points_map = new ymaps.Map("ymaps", {
            center: center,
            zoom: 7,
            controls: ['zoomControl', 'searchControl', 'fullscreenControl', 'trafficControl', 'typeSelector'],
        },{
            searchControlFloat: 'right',
            typeSelectorFloat: 'none',

            searchControlSize: 'small',
            trafficControlSize: 'small',
            typeSelectorSize: 'small',

            searchControlFloatIndex: 100,
            trafficControlFloatIndex: 200,
            typeSelectorFloatIndex: 300,
            fullscreenControlFloatIndex: 400,

            typeSelectorPosition: {
                top: '10px',
                right: '152px'
            },

            suppressMapOpenBlock: true,
            yandexMapDisablePoiInteractivity: false,

        });

        button.events
            .add('press', function () {
                //
              }
            )
            .add('select', function () {
                var footer = $("#modal-map-points div.modal-footer");
                var coords = points_map.getCenter();
                adding = true;

                footer.find("button.btn-submit").hide();
                $("#modal-map-points").data("selected", false);
                var pm = $("#modal-map-points").data("selected_placemark");
                if( pm )
                {
                    var preset = pm.options.get('preset');
                    preset = ( typeof preset === 'string' ) ? preset.split('#') : preset[0].split('#');
                    preset[1] = preset[1].replace('green', 'blue');
                    pm.options.set('preset', preset.join('#'));
                }
                $("#modal-map-points").data("selected_placemark", false);

                var b = $('<button class="btn btn-primary" id="btn-save-new-point">Добавить остановку!</button>')
                .hide()
                .on("click", function(){
                if($("#modal-map-points").data("validate")())
                {
                    var table = "<table class='alert-table'>"+
                                "<tr><th>Выбран:</th><td>"+$("#modal-map-points span[name='city-name']").text()+"</td></tr>"+
                                "<tr><th>Адрес:</th><td>"+$("#modal-map-points input[name='address']").val()+"</td></tr>"+
                                "<tr><th>Остановка:</th><td>"+$("#modal-map-points input[name='name']").val()+"</td></tr>"+
                                "</table>";

                    confirmMsg("Проверьте правильность введенных данных:"+table+"<p>Сохранить?", function(){
                        $.ajax({
                            url: "/account/points",
                            type: 'post',
                            dataType: "json",
                            data: $.extend({action: 'new-map-point'}, $("#modal-map-points").data("params")),
                            success: function( data ) {
                                if(data.success) {
                                    successMsg("Успешно!", function(){
                                        button.deselect();
                                        clusterer.removeAll();
                                        $("#modal-map-points").data("selected", false);
                                        $('#modal-map-points button[name=modetoggler]').trigger("click");
                                        load_claster(selected_city_obj);
                                    });
                                }
                                else
                                   alertMsg(data.msg?data.msg:"Ошибка сохранения!");
                            } // success
                        });//$.ajax
                    });
                }
                });
                footer.append(b);

                var bc = coords;
                bc[0] += 0.001
                points_map.balloon.open(bc, {
                    contentHeader: 'Как добавить остановку?',
                    contentBody: '1. Переместите красный маркер новой остановки в нужное место или кликните по нужному месту.<br>'+
                                 '2. Укажите наименование остановки.<br>'+
                                 '3. Нажмите кнопку "'+b.text()+'" для сохранения.<p>'+
                                 '4. Отожмите кнопку "'+create_btn_title+'" для отмены создания.<p>'+
                                 '<buttton class="btn btn-primary" style="float:right;" onclick=\'var v=$(this).closest("ymaps").find("ymaps").attr("class").split("balloon")[0];$("ymaps."+v+"balloon__close-button").trigger("click"); return false;\'>Закрыть</button>',
                    closeTimeout: 10000,
                    openTimeout: 3000,
                });

                point_placemark = new ymaps.Placemark(points_map.getCenter(), {
                    iconCaption: 'поиск...'
                    }, {
                        preset: 'islands#redDotIconWithCaption',
                        draggable: true
                    });

                points_map.geoObjects.add(point_placemark);
                point_placemark.events.add('dragend', function () {
                    getAddress(point_placemark.geometry.getCoordinates());
                });
                getAddress(coords);
            })
            .add('deselect', function () {
                var footer = $("#modal-map-points div.modal-footer");
                var coords = points_map.getCenter();
                adding = false;

                $("#btn-save-new-point").remove();
                footer.find("button.btn-submit").show();
                $("#point-name").hide();

                points_map.balloon.close();
                points_map.geoObjects.remove(point_placemark);
                point_placemark = false;
            });

        if((mode!='view'))
        {
            points_map.controls.add(button, {
                right: 5, top: 5
            });
        }

        balloonContentLayout = ymaps.templateLayoutFactory.createClass(
            '<table class="table table-hover table-condensed table-map-balloon">'+
            '<thead><tr><th colspan=2><b>{{properties.hintContent}}</b></th></tr></thead>'+
            '<tr><th>Статус: </th><td name=status data-status={{properties.all.moderated}}></td></tr>'+
            '<tr>'+
            (
                ( ( mode=='edit' ) || ( mode=='moderation' ) )
                ?
                '<td><button class="btn btn-info" style="width:100%" name="bpmcity" data-id="{{properties.id}}">Город</button></td>'
                :
                '<th>Адрес:</th>'
            )+
            '<td>{{properties.all.address}}</td></tr>'+
            '<tr><th>ОКАТО:</th><td>{{properties.all.ocatd}}</td></tr>'+
            '<tr><th>КЛАДР:</th><td>{{properties.all.code}}</td></tr>'+
            '<tr><th>Координаты: </th><td>{{properties.all.x}}, {{properties.all.y}}</td></tr>'+
            (
                ( ( mode=='edit' ) || ( mode=='moderation' ) ) ?
                '<tr><th>Наименование: </th><td><input style="width:100%; border:1px solid lightgray; border-radius: 3px;" name="name" value="{{properties.all.point}}"></td></tr>'+

                '<tr><td><button class="btn btn-info" style="width:100%" name="bpmtags" data-id="{{properties.id}}">Иконки...</button></td><td>$[properties.all.info_icons]</td></tr>'+

                '<tr><td colspan=2 style="padding-top:10px;">'+
                '<button class="btn btn-primary" name="bpmsave" data-address="{{properties.all.address}}" data-name="{{properties.all.point}}" data-x="{{properties.all.x}}" data-y="{{properties.all.y}}" data-id="{{properties.id}}">Сохранить!</button>'+
                '<button class="btn btn-danger" style="float:right" name="bpmdel" data-id="{{properties.id}}">Удалить!</button>'+
                (
                    ( mode=='moderation')
                     ?
                     '<button class="btn btn-success" style="float:right; margin:0 5px" name="bpmmoder" data-address="{{properties.all.address}}" data-name="{{properties.all.point}}" data-x="{{properties.all.x}}" data-y="{{properties.all.y}}" data-id="{{properties.id}}" data-moderated="{{properties.all.moderated}}">Модерировать</button>'
                     :
                     ''
                    ) +
                '</td></tr>'
                :
                '<tr><td>Иконки</td><td>$[properties.all.info_icons]</td></tr>'
            ) +
            '</table>',

            {
                build: function () {
                    balloonContentLayout.superclass.build.call(this);
                    if(( mode=='edit' ) || ( mode=='moderation' ))
                    {
                        $('button[name=bpmsave]').bind('click', this.save);
                        $('button[name=bpmdel]').bind('click', this.del);
                        $('button[name=bpmtags]').bind('click', this.change_tags);
                        $('button[name=bpmcity').bind('click', this.change_city);

                        if($('button[name=bpmmoder]').attr("data-moderated")=="true")
                            $('button[name=bpmmoder]').remove();
                        else
                            $('button[name=bpmmoder]').bind('click', this.moder);
                    }

                    var m = '<i class="icon-blocked text-danger-300" style="padding:0;"></i> На модерации!</b>';
                    $.each($("td[name=status]"), function(i, obj){
                        if($(obj).attr("data-status") == 'false')
                            $(obj).html(m);
                        else
                            $(obj).closest("tr").remove();
                    });
                },

                clear: function () {
                    $('button[name=bpmsave], button[name=bpmdel], button[name=bpmmoder]').unbind('click');
                    balloonContentLayout.superclass.clear.call(this);
                },

                moder: function () {
                        var btn = $(this);
                        var newName = $(this).closest('table').find("input[name='name']").val();
                        var isNewName = $(this).attr("data-name") != newName;

                        var table = "<table class='alert-table'>"+
                         "<tr><td>Выбран:</td><td>"+$("#modal-map-points span[name='city-name']").text()+"</td></tr>"+
                        ( isNewName
                            ?
                            "<tr><th>Новое наименование:</th><td>"+newName+"</td></tr>"
                            :
                            "<tr><td>Остановка:</td><td>"+$(this).attr('data-name')+"</td></tr>"
                        )+
                         "<tr><td>Адрес:</td><td>"+$(this).attr('data-address')+"</td></tr>"+
                         "<tr><td>Координаты:</td><td>["+$(this).attr('data-x')+", "+$(this).attr('data-y')+"]</td></tr>"+
                         "</table>";

                        confirmMsg("Проверьте правильность введенных данных:"+table+"<p>&nbsp;</p><p>Принять остановку в систему?", function(){
                               $.ajax({
                                    url: "/account/points",
                                    type: 'post',
                                    dataType: "json",
                                    data: {
                                            action: 'moderate-point',
                                            id: btn.attr('data-id'),
                                            name: isNewName?newName:null
                                    },
                                    success: function( data ) {
                                        if(data.success) {
                                            clusterer.removeAll();
                                            load_claster(selected_city_obj);
                                        }
                                        else
                                           alertMsg(data.msg?data.msg:"Ошибка удаления!");
                                    } // success
                                });//$.ajax
                        }); // confirm
                },

                save: function () {
                        var btn = $(this);
                        var newName = $(this).closest('table').find("input[name='name']").val();
                        var oldName = $(this).attr("data-name");
                        var isNewName = oldName != newName;

                        var table = "<table class='alert-table'>"+
                         "<tr><td>Выбран:</td><td>"+$("#modal-map-points span[name='city-name']").text()+"</td></tr>"+
                        ( isNewName
                            ?
                            "<tr><th>Новое наименование:</th><td>"+newName+"</td></tr>"
                            :
                            "<tr><td>Остановка:</td><td>"+$(this).attr('data-name')+"</td></tr>"
                        )+
                         "<tr><th>Новый адрес:</th><td>"+$(this).attr('data-address')+"</td></tr>"+
                         "<tr><th>Новые координаты:</th><td>["+$(this).attr('data-x')+", "+$(this).attr('data-y')+"]</td></tr>"+
                         "</table>";

                        confirmMsg("Проверьте правильность введенных данных:"+table+"<p>&nbsp;</p><p>Сохранить?", function(){
                               $.ajax({
                                    url: "/account/points",
                                    type: 'post',
                                    dataType: "json",
                                    data: {
                                            action: 'edit-map-point',
                                            id: btn.attr('data-id'),
                                            x: btn.attr('data-x'),
                                            y: btn.attr('data-y'),
                                            address: btn.attr('data-address'),
                                            name: isNewName?newName:oldName
                                    },
                                    success: function( data ) {
                                        if(data.success) {
                                            clusterer.removeAll();
                                            load_claster(selected_city_obj);
                                        }
                                        else
                                           alertMsg(data.msg?data.msg:"Ошибка сохранения!");
                                    } // success
                                });//$.ajax
                        }); // confirm
                }, // save

                del: function () {
                        var btn = $(this);
                        confirmMsg("Вы уверены?<p>&nbsp;</p><p>Удалить остановку?", function(){
                               $.ajax({
                                    url: "/account/points",
                                    type: 'post',
                                    dataType: "json",
                                    data: {
                                            action: 'delete-map-point',
                                            id: btn.attr('data-id'),
                                    },
                                    success: function( data ) {
                                        if(data.success) {
                                            clusterer.removeAll();
                                            load_claster(selected_city_obj);
                                        }
                                        else
                                           alertMsg(data.msg?data.msg:"Ошибка удаления!");
                                    } // success
                                });//$.ajax
                        }); // confirm
                }, // del

                change_city: function () {
                        var btn = $(this);

                        var w = modalTpl({
                            id: 'modal-select-city',
                            title: "Назначение города остановки",
                            "submit-text": "Назначить!",
                            "close-text": "Отмена",
                            "ajax-url": "/account/points",
                            "ajax-data": {
                                action: 'modal-select-city',
                            },
                            "submit-function": function(){
                                if($("#modal-select-city").data("validate")())
                                {
                                    //console.log($("#modal-select-city").data("params"));
                                    var data = $("#modal-select-city").data("params");
                                    data.action = 'change-city-map-point';
                                    data.id = btn.attr('data-id');
                                    $.ajax({
                                         url: "/account/points",
                                         type: 'post',
                                         dataType: "json",
                                         data: data,
                                         success: function( data ) {
                                             w.hide();
                                             if(data.success) {
                                                 successMsg("Успешно!", function(){
                                                     window.location.reload();
                                                 });
                                             }
                                             else
                                                alertMsg(data.msg?data.msg:"Ошибка!");
                                         } // success
                                     });//$.ajax

                                };
                                return false;
                            } // submit
                        }).appendTo($("body"));
                        w.modal("show");

                }, // change_city

                change_tags: function () {
                        var btn = $(this);

                        var w = modalTpl({
                            id: 'modal-select-point-tags',
                            title: "Выбор набора иконок для остановки",
                            "submit-text": "Сохранить!",
                            "close-text": "Отмена",
                            "ajax-url": "/account/points",
                            "ajax-data": {
                                action: 'modal-select-point-tags',
                                id: btn.attr('data-id'),
                            },
                            "submit-function": function(){
                                var data = {};
                                data.action = 'change-point-tags';
                                data.id = btn.attr('data-id');

                                data.tags = [];
                                $.each($("#modal-select-point-tags :checkbox[name='tag']"), function(i, box){
                                    data.tags.push('"'+$(box).attr("data-tag")+'":'+$(box).prop("checked").toString());
                                });
                                data.tags = '{' +data.tags.join(',')+ '}';
                                data.tags = JSON.parse(data.tags);

                                $.ajax({
                                     url: "/account/points",
                                     type: 'post',
                                     dataType: "json",
                                     data: data,
                                     success: function( data ) {
                                         w.hide();
                                         if(data.success) {
                                             successMsg("Успешно!", function(){
                                                 window.location.reload();
                                             });
                                         }
                                         else
                                            alertMsg(data.msg?data.msg:"Ошибка!");
                                     } // success
                                 });//$.ajax
                                return false;
                            } // submit
                        }).appendTo($("body"));
                        w.modal("show");

                }, // change_tags
        });

        clusterer = new ymaps.Clusterer({
            preset: 'islands#invertedBlueClusterIcons',
            groupByCoordinates: false,
            maxZoom: 14,
            gridSize: 256,
            hasBalloon: true,
            hasHint: false,
            clusterDisableClickZoom: false,
            clusterHideIconOnBalloonOpen: true,
        });

       points_map.events.add('click', function (e) {

           if(!adding) return;

           var coords = e.get('coords');

           if (point_placemark)
               point_placemark.geometry.setCoordinates(coords);
           else {
               point_placemark = new ymaps.Placemark(coords, {
                                iconCaption: 'поиск...'
                            }, {
                                preset: 'islands#redDotIconWithCaption',
                                draggable: true
                            });

               points_map.geoObjects.add(point_placemark);
               point_placemark.events.add('dragend', function () {
                   getAddress(point_placemark.geometry.getCoordinates());
               });
           }
           getAddress(coords);
       });

        $("#modal-map-points #ymaps, #modal-map-points #point-name").hide();
    };

    var getAddress = function(coords)
    {
        point_placemark.properties.set('iconCaption', 'поиск...');
        ymaps.geocode(coords).then(function (res) {
            var firstGeoObject = res.geoObjects.get(0);
            point_placemark.properties
                .set({
                    iconCaption: [
                        firstGeoObject.getLocalities().length ? firstGeoObject.getLocalities() : firstGeoObject.getAdministrativeAreas(),
                        firstGeoObject.getThoroughfare() || firstGeoObject.getPremise()
                    ].filter(Boolean).join(', '),
                });
//                console.log('Все данные геообъекта: ', firstGeoObject.properties.getAll());
//                console.log('Метаданные ответа геокодера: ', res.metaData);
//                console.log('Метаданные геокодера: ', firstGeoObject.properties.get('metaDataProperty.GeocoderMetaData'));
//                console.log('precision', firstGeoObject.properties.get('metaDataProperty.GeocoderMetaData.precision'));
//                console.log('Тип геообъекта: %s', firstGeoObject.properties.get('metaDataProperty.GeocoderMetaData.kind'));
//                console.log('Название объекта: %s', firstGeoObject.properties.get('name'));
//                console.log('Описание объекта: %s', firstGeoObject.properties.get('description'));
//                console.log('Полное описание объекта: %s', firstGeoObject.properties.get('text'));
//                console.log('');
//                console.log('Государство: %s', firstGeoObject.getCountry());
//                console.log('Населенный пункт: %s', firstGeoObject.getLocalities().join(', '));
//                console.log('Адрес объекта: %s', firstGeoObject.getAddressLine());
//                console.log('Наименование здания: %s', firstGeoObject.getPremise() || '-');
//                console.log('Номер здания: %s', firstGeoObject.getPremiseNumber() || '-');

                $("#modal-map-points input[name='name']").val(firstGeoObject.properties.get('name'));
                $("#modal-map-points input[name='address']").val(firstGeoObject.getAddressLine());
                $("#modal-map-points input[name='x']").val(coords[0]);
                $("#modal-map-points input[name='y']").val(coords[1]);

                $("#modal-map-points #point-name, #btn-save-new-point").show();
        });
    }

    var getEditAddress = function(placemark)
    {
        placemark.properties.set('iconCaption', 'поиск...');

        var coords = placemark.geometry.getCoordinates();
        ymaps.geocode(placemark.geometry.getCoordinates())
            .then(function (res) {
                var firstGeoObject = res.geoObjects.get(0);
                var all = placemark.properties.get("all");
                all.x = coords[0];
                all.y = coords[1];
                all.address = firstGeoObject.getAddressLine();

                placemark.properties
                    .set({
                        iconCaption: [
                            firstGeoObject.getLocalities().length ? firstGeoObject.getLocalities() : firstGeoObject.getAdministrativeAreas(),
                            firstGeoObject.getThoroughfare() || firstGeoObject.getPremise()
                        ].filter(Boolean).join(', '),
                        all: all
                    });
                placemark.options.set('preset', 'islands#violetDotIconWithCaption');
            });
    }

    var search_on_map_by_name = function(name)
    {
        ymaps.geocode(name, {
        }).then(function (res) {
                var firstGeoObject = res.geoObjects.get(0),
                    bounds = firstGeoObject.properties.get('boundedBy');

                city_coord = firstGeoObject.geometry.getCoordinates(),
                points_map.panTo(city_coord, {
                    duration: 2000
                });
            });
    };

    var randomize_coords = function(coords)
    {
        var min = 0.0001;
        var max = 0.0002;
        var delta = max - min;
        var signum = Math.random()*delta + min > delta/2 + min;

        var crd = $.extend([], coords);
        crd[0] = parseFloat(coords[0] + (signum?1:-1) * Math.random() * delta + min);
        crd[1] = parseFloat(coords[1] + (signum?1:-1) * Math.random() * delta + min);

        return crd;
    };

    var load_claster = function(obj)
    {
        $.ajax({
            url: '/ajax/end-points-list',
            type: 'post',
            dataType: "json",
            data: {
                id_city: obj.item.id,
                id_country: obj.item.id_country
            },
            success: function( data ) {
                var geoObjects = [], last_coord = points_map.getCenter();
                $.each(data, function(i, point){

                    data[i].info_icons = make_info_icons(point.tags);

                    var coord = [0,0];
                    var preset = 'islands#grayDotIconWithCaption';
                    var hintContent = point.point;
                    var iconContent = '';

                    if( (point.x!=0) && (point.y!=0) ) {
                        coord[0] = parseFloat(point.x);
                        coord[1] = parseFloat(point.y);
                        preset = 'islands#blueDotIconWithCaption';
                    }
                    else
                        coord = randomize_coords(city_coord);

                    if(!point.moderated)
                    {
                        preset = 'islands#redStretchyIconWithCaption';
                        iconContent = '<i class="icon-blocked text-danger-300" style="padding:0;"></i>';
                    }

                    last_coord = coord;
                    var placemark = new ymaps.Placemark(coord, {
                                     iconContent: iconContent,
                                     iconCaption: point.point,
                                     hintContent: hintContent,
                                     balloonContentBody: '<b>Остановка: </b>' + point.point + '<p><b>Адрес: </b>' + (point.address?(point.address+'<p>'):'') + '<p><hr size=1 color=lightgray noshade>',
                                     balloonContentHeader: '[' + point.x + ', ' + point.y + ']',
                                     clusterCaption: '[' + point.x + ', ' + point.y + ']',
                                     id: point.id,
                                     all: point
                                 }, {
                                     preset: preset,
                                     draggable: (( mode=='edit' ) || ( mode=='moderation' )),
                                     balloonContentLayout: balloonContentLayout,
                                 });
                    if(( mode=='edit' ) || ( mode=='moderation' )) {
                        placemark.events.add('dragend', function () {
                            getEditAddress(placemark);
                        });
                    }

                    placemark.events.add('click', function () {

                        if(mode!='select') return;

                        $.each(geoObjects, function(i, marker){
                            if( typeof marker.options.get('preset') === 'string' )
                            {
                                var preset = marker.options.get('preset');
                                preset = preset.split('#');
                                preset[1] = preset[1].replace('green', 'blue');
                                marker.options.set('preset', preset.join('#'));
                            }
                        });

                        var preset = placemark.options.get('preset');
                        preset = ( typeof preset === 'string' ) ? preset.split('#') : preset[0].split('#');
                        preset[1] = preset[1].replace('blue', 'green');
                        placemark.options.set('preset', preset.join('#'));

                        $("#modal-map-points").data("selected_placemark", placemark);
                        $("#modal-map-points").data("selected", placemark.properties.get("all"));
                    });

                    geoObjects[i] = placemark;
                    clusterer.add(geoObjects);
                    points_map.geoObjects.add(clusterer);

                    if(geoObjects.length>1)
                        points_map.setBounds(clusterer.getBounds(), {
                            checkZoomRange: true
                        });
                    else
                        points_map.panTo(last_coord, {
                            duration: 2000
                        });
                });
                } // success
        });//$.ajax
    }

    ymaps_init();


$( "#modal-map-points input[name='city']" ).cityselector({
    valueInputPathCity: "#modal-map-points input[name='id_city']",
    valueInputPathCountry: "#modal-map-points input[name='id_country']",
    cancelable: {$cancelable},
    id: {$id_city},
    id_country: {$id_country},
    name: '{$name}',
    selected: '{$selected}',
    callback: function(obj){
        selected_city_obj = obj;
        $("#modal-map-points #ymaps").show();
        search_on_map_by_name(obj.item.geocode);
        load_claster(obj);
    },

    reset: function(){
        $("#modal-map-points #ymaps, #modal-map-points #point-name").hide();
        $("#modal-map-points input[name='name']").val('');
        $("#modal-map-points input[name='address']").val('');
        $("#modal-map-points input[name='x']").val(0);
        $("#modal-map-points input[name='y']").val(0);

        if(point_placemark)
        {
            points_map.geoObjects.remove(point_placemark);
            points_map.geoObjects.removeAll();
            point_placemark = false;
        }
        clusterer.removeAll();
        $("#modal-map-points").data("selected", false);
    }
});

$("#modal-map-points").data("validate", function()
{
    var params = {}, c = $("#modal-map-points");
    c.data("params", params);

    if( parseInt( "0" + c.find("input[name='id_city']").val() ) == 0 )
        return alertMsg("Необходимо выбрать город из списка!");

    if( !c.find("input[name='name']").val() )
        return alertMsg("Необходимо указать наименование остановки!");

    if( parseFloat( "0" + c.find("input[name='x']").val() ) == 0 )
        return alertMsg("Необходимо выбрать остановку на карте!");

    if( parseFloat( "0" + c.find("input[name='y']").val() ) == 0 )
        return alertMsg("Необходимо выбрать остановку на карте!");

   params.id_city = parseInt( c.find("input[name='id_city']").val() );
   params.x = c.find("input[name='x']").val();
   params.y = c.find("input[name='y']").val();
   params.id_country = parseInt( c.find("input[name='id_country']").val() );
   params.name = c.find("input[name='name']").val();
   params.address = c.find("input[name='address']").val();

    c.data("params", params);
    return true;
});

$("#modal-map-points input[name='city']").focus();

S;
