<?php
return <<<S

    function getRaw(name, object)
    {
        var raw = {};
        if(typeof object === "object")
            $.each(object, function(n, val){                
                raw = $.extend({}, raw, getRaw(n,val));
            });            
        else
            if(name) raw[name] = object;
        return raw;
    }

    function addDriverRow(id, driver)
    {
        driver.dolzhn = "Водитель";
        console.log(driver);
        var container = $("tbody#drivers-body");
        if(container.find("tr[name='"+id+"']").length) return true;
        var user = driver.data.profile.user;
        var tr = $("<tr />").attr("name", id);
        tr.append($("<td />").text( (driver.f?driver.f:"") + " " + (driver.i?driver.i:"") + " " + (driver.o?driver.o:"") ));
        tr.append($("<td />").text( (user.dr?user.dr:"") +", "+ (user.sex_txt?user.sex_txt:"") ) );        
        tr.append($("<td />").html( (user.country_txt?user.country_txt:"") + "\\r\\n" + (user.passport_type_txt?user.passport_type_txt:"") + ",<br>S№: " + (user.passport?user.passport:"")) );
        
        var raw = getRaw(false, driver);
        console.log(raw);
        
        tr.append($("<td />")
                    .append($("<input type=hidden />").attr("name", "driver["+id+"]").val(JSON.stringify(raw)))
                    .append($('<ul class="icons-list"><li title="Удалить" class="text-danger-600 driver-remove"><a href="#"><i class="icon-trash"></i></a></li></ul>'))
                );
        
        container.append(tr);
        
        tr.find("li.driver-remove").on("click", function(){
            var self = this; 
            confirmMsg("Вы уверены?", function(){
                $(self).closest("tr").remove(); 
            });          
        });
        
        return true;
    }

    function addPersonalRow(personal)
    {
        console.log(personal);
        var container = $("tbody#personal-body");
        var user = personal;
        var id = user.passport.toString().replace(new RegExp(" ",'g'), "");

        if(container.find("tr[name='"+id+"']").length) 
        {
            if( !confirm("Человек с таким номером документа уже есть в списке!\\r\\n\\r\\nПерезаписать уже имеющиеся данные?")) return false;
            container.find("tr[name='"+id+"']").remove();
        }
        
        var tr = $("<tr />").attr("name", id);
        tr.append($("<td />").html( user.dolzhn + " -<br>" + user.f + " " + user.i + " " + user.o) );
        tr.append($("<td />").html( user.dr +"г.<br>"+ user.sex_txt ) );        
        tr.append($("<td />").html( user.grazhd_txt + ",<br>" + user.passport_type_txt + ",<br>S№:  " + user.passport) );
        
        tr.append($("<td />")
                    .append($("<input type=hidden />").attr("name", "personal["+id+"]").val(JSON.stringify(user)))
                    .append($('<ul class="icons-list"><li title="Удалить" class="text-danger-600 personal-remove"><a href="#"><i class="icon-trash"></i></a></li></ul>'))
                );
        
        container.append(tr);
        
        tr.find("li.personal-remove").on("click", function(){
            var self = this; 
            confirmMsg("Вы уверены?", function(){
                $(self).closest("tr").remove(); 
            });          
        });
        
        return true;
    }

    function isValidUser(user)
    {
        if(!user.dolzhn) return alertMsg("Не указана должность!");
        if(!user.f) return alertMsg("Не указана фамилия!");
        if(!user.i) return alertMsg("Не указано имя!");
        if(!user.o) return alertMsg("Не указано отчество!");
        if(!user.sex) return alertMsg("Не указан пол!");
        if(!user.dr) return alertMsg("Не указана дата рождения!");
        if(!user.grazhd) return alertMsg("Не указано гражданство!");
        if(!user.passport_type) return alertMsg("Не указано удостоверение личности!");
        if(!user.passport) return alertMsg("Не указаны серия и номер удостоверения личности!");
        return true;
    }

    $("#modal-reis-equipage").data("validate", function()
    {        
        var drivers = {};
        var personal = {};
        $("#modal-reis-equipage").data("drivers", drivers);
        $("#modal-reis-equipage").data("personal", personal);

        if(!$("tbody#drivers-body > tr").length)
            return alertMsg("Должен быть указан хотя-бы один водитель!");            
        
        var verified = true;
        $.each($("tbody#drivers-body > tr"), function(i, tr)
        {
            var hidden = $(tr).find("input:hidden");
            var user = JSON.parse($(hidden).val());
            user.id = $(tr).attr("name");
            drivers[$(tr).attr("name")] = user;
            var result = isValidUser(user);
            if(!result) 
                return verified = alertMsg("У водителя " + user.f +" " + user.i +" "+user.o + " неполная информация!");                
        });
        
        if(verified)
        $.each($("tbody#personal-body > tr"), function(i, tr)
        {
            var hidden = $(tr).find("input:hidden");
            var user = JSON.parse($(hidden).val());
            personal[$(tr).attr("name")] = user;
            var result = isValidUser(user);
            if(!result) 
                return verified = alertMsg("У члена экипажа " + user.f +" " + user.i +" "+user.o + " неполная информация!");                
        });
                
        $("#modal-reis-equipage").data("drivers", drivers);
        $("#modal-reis-equipage").data("personal", personal);
        return verified; 
    });

    $("a.btn-add-driver").on("click", function(){
        var w = modalTpl({
            id: "modal-select-driver",
            title: "Выбор водителя",
            body: "Загрузка...",
            "ajax-url": "/account/" + ajax_action,
            "ajax-data": {
                action: 'modal-select-driver',
                id: 0,
                owner_id: '$owner_id',
            },
            "ajax-callback": function(data){
                w.drivers = data.drivers;
            },
            "submit-function": function(){
                var selected = $("#modal-select-driver").find("select#driver").val();                
                return addDriverRow(selected, w.drivers[selected]);
            }
        }).appendTo($("body"));
        w.modal("show");        
    });

    $("a.btn-add-personal").on("click", function(){
        var w = modalTpl({
            id: "modal-add-personal",
            title: "Персонал рейса",
            body: "Загрузка...",
            "ajax-url": "/account/" + ajax_action,
            "ajax-data": {
                action: 'modal-add-personal',
                id: 0
            },
            "submit-function": function(){
                var c = $("#modal-add-personal").find("div.modal-body");
                var personal = {};
                $.each(c.find(".form-control"), function(i, control){
                    personal[$(control).attr("name")] = $(control).val();
                });                
                personal.sex_txt = c.find("select[name='sex'] :selected").text();                
                console.log(personal);
                
                if(!personal.dolzhn) return alertMsg("Не указана должность!");
                if(!personal.f) return      alertMsg("Не указана фамилия!");
                if(!personal.i) return      alertMsg("Не указано имя!");
                if(!personal.o) return      alertMsg("Не указано отчество!");
                if(!personal.dr) return     alertMsg("Не указана дата рождения!");
                if(!personal.grazhd) return alertMsg("Не указано гражданство!");
                if(!personal.passport_type) return  alertMsg("Не указано удостоверение личности!");
                if(!personal.passport) return       alertMsg("Не указаны серия и номер удостоверения личности!");
                                    
                return addPersonalRow(personal);
            }
        }).appendTo($("body"));
        w.modal("show");        
    });

    $("#modal-reis-equipage").find("li.driver-remove, li.personal-remove").on("click", function(){
        var self = this; 
        confirmMsg("Вы уверены?", function(){                 
            console.log($(self).closest("tr"));
            $(self).closest("tr").remove();
        });
    });
        
S;
