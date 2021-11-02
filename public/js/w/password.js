(function($) {

    $.widget("ui.password", {
        		
        config: {
            id: 0,
            title: 'Смена пароля у пользователя ',
            btnCloseTitle: 'Отмена',
            btnOkTitle: 'OK'
        },
        
	_create: function() 
	{
            var self = this, el = self.element, o = self.options;            
            if(!o.id) return false;
            self.config.id = o.id;
            self.config.title = "Смена пароля у пользователя - " + o.login;
            
            var modal = self._mkModalWindow(self.config);
            modal.body.html(self._mkModalBody());

            $("body").append($(modal));
            modal.on('show.bs.modal', function (e) {
            });

            modal.btnOK.on("click", function(){
                var p = modal.find(":text[name='password']").val();                
                if(!p || (p.length<6)) {
                    alertMsg("Длина пароля должна быть не менее 6 символов!");
                    return false;
                }
                var data = {
                             id: o.id,
                             login: o.login,
                             password: p                             
                         };

                $.ajax({
                        url: '/account/change-someone-password',
                        type: 'post',
                        dataType: "json",
                        data: data,
                        success: function( data ) {
                            if(data.success) {
                                if(data.msg) successMsg(data.msg);                                
                                $("#password-"+o.id).modal("hide");
                            } else {
                                if(data.msg || data.err) alertMsg(data.msg + "\r\n\r\n" + (data.err?data.err:""));                                
                            }                            
                        } // success
                    });//$.ajax
                return false;
            });
            
            modal.find(":button.password-generator").on("click", function(){
                var passwd = '';
                var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                for (i=1;i<8;i++) {
                  var c = Math.floor(Math.random()*chars.length + 1);
                  passwd += chars.charAt(c);
                }
                modal.find(":text[name='password']").val(passwd);
            });

            modal.on('hide.bs.modal', function (e) {
                $("#password-"+o.id).remove();
                self.destroy();
            });
                        
            $("#password-"+o.id).modal("show");
        },
        
        _mkModalWindow: function(config)
        {
            var self = this, body = self.tbody;

            var id = config.id;
            var title = config.title;
            var btnCloseTitle = config.btnCloseTitle;
            var btnOkTitle = config.btnOkTitle;

            var modal = $('<div class="modal fade" id="password-'+id+'" tabindex="-1" role="dialog" aria-labelledby="'+id+'ModalLabel" aria-hidden="true"></div>')
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
        _mkModalBody: function()
        {
            var divRow = $('<div class="row"></div>');
            var divCol_1 = $('<div class="col-md-3">Новый пароль:</div>');
            var divCol_2 = $('<div class="col-md-6"><input name="password" class="form-control" maxlength="64"></div>');
            var divCol_3 = $('<div class="col-md-3"><button type="button" class="btn btn-primary password-generator">Генерация</button></div>');            
            divRow.append(divCol_1).append(divCol_2).append(divCol_3);
            return divRow;
        },
		
});	
	        
})(jQuery);