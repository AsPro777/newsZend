(function($) {

    $.widget("ui.docUploader", {        
        
        options: {
            id_obj: 0,
            table_obj: "",
            tag: "",
            title: "",
            valid_to: "",
        },
        _root: false,
        _modalConfig: {
            id : 'uploadModalDialog',
            title : 'Загрузка документа',
            btnCloseTitle : 'Отмена',
            btnOkTitle : 'Загрузить'
        },
		
	_create: function() 
	{
            var self = this, el = self.element, o = self.options;
            self._root = $("<div />").attr("id", "docUploaderRoot").appendTo("body");
            
            self.table = $("<table class=table-schedule></table>").attr("name", self.baseName+"-table");
            self.th = $("<tr class=table-schedule-head-tr></tr>");
            self.tbody = $("<tbody></tbody>");
            self.table.append(self.th).append(self.tbody);
            
            self.modal = self._mkModal(self._modalConfig);
                        
            self._root.append(self.modal);
            self.modal.modal("show");
	},
        
	_destroy: function() 
	{
            var self = this;
            self._root.remove();
            self._root = false;
	},
        
        _mkModalWindow: function(config)
        {
            var self = this, body = self.tbody;

            var id = config.id;
            var title = config.title;
            var btnCloseTitle = config.btnCloseTitle;
            var btnOkTitle = config.btnOkTitle;

            var modal = $('<div class="modal fade" id="'+id+'" tabindex="-1" role="dialog" aria-labelledby="'+id+'ModalLabel" aria-hidden="true"></div>')
            var dialog = $('<div class="modal-dialog"></div>');
            var content = $('<div class="modal-content"></div>');
            var header = $('<div class="modal-header"></div>');
            var closer = $('<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>');
            var title = $('<h4 class="modal-title" id="'+id+'ModalLabel">'+title+'</h4>');
            var body = $('<div class="modal-body"></div>');
            var footer = $('<div class="modal-footer"></div>');
            var btnClose = $('<button type="button" class="btn btn-default" data-dismiss="modal">'+btnCloseTitle+'</button>');
            var btnOK = $('<button type="button" class="btn btn-primary" data-dismiss="modal">'+btnOkTitle+'</button>');

            modal.append(dialog.append(content.append(header.append(closer).append(title)).append(body).append(footer.append(btnClose).append(btnOK))));
            modal.btnOK = btnOK;
            modal.modalDialog = dialog;
            modal.body = body;
            modal.title = title;

            return modal;
        },
        
        _mkModal: function(config)
        {
            var self = this;

            var modal = self._mkModalWindow(config);

            modal.body.html(self._mkModalBody());

            modal.on('show.bs.modal', function (e) {
            });

            modal.btnOK.on("click", function() {                    
                if($("#"+self._modalConfig.id+"Form input[name='title']").val() == "") 
                {
                    alertMsg("Необходимо ввести наименование (краткое описание) документа!");
                    return false;
                }

                var d = $("#"+self._modalConfig.id+"Form input[name='valid_to']").val();
                if(d != "") 
                {
                    var ivd = self._isInvalidDate(d, true);
                    if(ivd!==false) 
                    {
                        alertMsg(ivd);
                        return false;
                    }
                }

                if($("#"+self._modalConfig.id+"Form input[name='new_scan']").val() == "") 
                {
                    alertMsg("Необходимо выбрать файл документа!");
                    return false;
                }

                $.ajax({
                    url: "/account/docs",
                    type: "POST",
                    data: new FormData($("#"+self._modalConfig.id+"Form")[0]),
                    mimeTypes:"multipart/form-data",                    
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(data){
                        data = JSON.parse(data);                        
                        if(data.success) {
                        }else{ 
                            alertMsg(data.msg)
                        };
                        var btn = self.element.closest("tbody#points-table-body").find("tr[name='"+self.options.id_obj+"']").find("span.btn-docs");                        
                        self.element.closest("tr.dropdown-row").find("span.btn-cancel-dropdown-row").trigger("click");
                        btn.trigger("click");
                    }
                 });
            });
            
            return modal;
        },
                
        _mkModalBody: function()
        {
            var self = this, o = self.options;
            var div = $("<form />")
                    .attr("id", this._modalConfig.id+"Form")
                    .attr("method", "POST")
                    .attr("enctype", "multipart/form-data");
            
            var divRow = $('<div class="row"></div>');            
            var divCol = $('<div class="col-md-12"></div>');
            var title = $('<input name="title" class="form-control" placeholder="Наименование документа">');
            divCol.append(title);
            divRow.append(divCol);
            div.append(divRow);
            
            var divRow = $('<div class="row"></div>');            
            var divCol = $('<div class="col-md-12"></div>');
            var tag = $('<select name="tag" class="form-control" />');
            /* see also docs.js*/
            var scan_cats = {
                        bk: 'Без категории',
                        regpaper: 'Свидетельство о регистрации ЮЛ или ИП',
                        licensy: 'Лицензия перевозчика пассажиров',
                        international: 'Удостоверение допуска к международным перевозкам',
                        ugadn: 'Уведомление УГАДН',
                        passport: 'Паспорт или другое удостоверение личности',
                        vu: 'Водительское удостоверение',
                        paxpolis: 'Страховка пассажиров'
            }; // of scan_cats
            $.each(scan_cats, function(key, val){
                tag.append($("<option />").attr("value", key).text(val));
            });
            divCol.append(tag);
            divRow.append(divCol);
            div.append(divRow);
            
            var divRow = $('<div class="row"></div>');
            var divCol_1 = $('<div class="col-md-3"></div>');            
            var validToL = $('<span />').text("Действует до:");
            var divCol_2 = $('<div class="col-md-9"></div>');
            var validTo = $('<input name="valid_to" class="form-control">').mask("00.00.0000", {placeholder: "дд.мм.гггг", selectOnFocus: false});
            divCol_1.append(validToL);
            divCol_2.append(validTo);
            divRow.append(divCol_1).append(divCol_2);
            div.append(divRow);
            
            var divRow = $('<div class="row"></div>');
            var divCol_1 = $('<div class="col-md-3"></div>');            
            var validToL = $('<span />').text("Файл:");
            var divCol_2 = $('<div class="col-md-9"></div>');
            var validTo = $('<input name="new_scan" type="file" required >');
            var mode = $('<input name="mode" type="hidden" value="ajax">');
            var action = $('<input name="action" type="hidden" value="newfile">');
            var table_obj = $('<input name="table_obj" type="hidden" value="usr">');
            var id_obj = $('<input name="id_obj_owner" type="hidden">').val(o.id_obj);            
            divCol_1.append(validToL);
            divCol_2.append(validTo);
            divRow.append(divCol_1).append(mode).append(action).append(id_obj).append(table_obj).append(divCol_2);
            div.append(divRow);            
            
            return div;
        },

        _isInvalidDate: function (date, futureOnly)
        {
            var saved = date;
            var appendix = "\r\n\r\nДля установки неограниченной даты оставьте поле пустым.";
            date = date.split('.');
            var d = date[0], m = date[1], y = date[2];
            var dt = new Date();
            dt.setUTCFullYear(y, m-1, d);

            var result = ((y == dt.getUTCFullYear()) && ((m-1) == dt.getUTCMonth()) && (d == dt.getUTCDate()));
            if(!result) 
                return ("Некорректная дата: "+saved+"!"+appendix);

            var nextMonth = new Date();
            nextMonth.setUTCFullYear(nextMonth.getFullYear(), nextMonth.getMonth()+1, nextMonth.getDate());

            if( futureOnly && (nextMonth > dt) )
            {
                return ("Дата истечения должна быть в будущем, не менее месяца от сегодняшней даты!\r\n\r\nВы установили дату: "+saved+"!"+appendix);
            }

            return false;
        }
        
    });//widget

})(jQuery);