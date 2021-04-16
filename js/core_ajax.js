fnjs_mostrar_atras=function(id_div,htmlForm) {
	fnjs_borrar_posibles_atras();
	var name_div=id_div.substring(1);
	
	if ($(id_div).length) {
		$(id_div).html(htmlForm);
	} else {
		html = '<div id="'+name_div+'" style="display: none;">';
		html += htmlForm;
		html += '</div>';
		$('#cargando').prepend(html);
		
	}
	fnjs_ir_a(id_div);
}

fnjs_enviar_formulario=function(id_form,bloque) {
	fnjs_borrar_posibles_atras();
	if (!bloque) { bloque='#main'; }
	$(id_form).one("submit", function() { // catch the form's submit event
			$.ajax({ // create an AJAX call...
				data: $(this).serialize(), // get the form data
				type: 'post', // GET or POST
				url: $(this).attr('action'), // the file to call
				success:function (resposta) { fnjs_mostra_resposta (resposta,bloque); }
			});
			return false; // cancel original event to prevent form submitting
		});
	$(id_form).trigger("submit");
	$(id_form).off();
}

fnjs_update_div=function(bloque,ref) {
	fnjs_borrar_posibles_atras();
	var path=ref.replace(/\?.*$/,'');
	var pattern=/\?/;
	if (pattern.test(ref)) {
		parametros=ref.replace(/^[^\?]*\?/,'');
	} else {
		parametros=''; 
	}
	//var web_ref=ref.gsub(/\/var\//,'http://');  // cambio el directorio físico (/var/www) por el url (http://www)
	$(bloque).attr('refe',path);
	
	request=$.ajax({
		url: path,
		type: 'post',
		data: parametros,
		dataType: 'text'
	});
	
	request.done( function (resposta) {
		fnjs_mostra_resposta (resposta,bloque);
	});
	request.fail(function(JqXHR, textStatus, errorThrown){
		alert('An error occurred... Look at the console (F12 or Ctrl+Shift+I, Console tab) for more information!');
        console.error("Hi ha un error: "+ textStatus, errorThrown);
    });
	return false;
}

fnjs_borrar_posibles_atras=function() {
	if ($('#ir_a').length) $('#ir_a').remove() ;
	if ($('#ir_atras').length) $('#ir_atras').remove() ;
	if ($('#ir_atras2').length) $('#ir_atras2').remove() ;
	if ($('#js_atras').length) $('#js_atras').remove() ;
	if ($('#go_atras').length) $('#go_atras').remove() ;
}
	
fnjs_mostra_resposta=function(resposta,bloque) {
	if (resposta === null) {
		return true;
	}
	switch (typeof resposta) {
		case 'object':
			var myText=resposta.responseText;
			break;
		case 'string':
			var myText=resposta.trim();
			break;
		case 'undefined':
			return true;
			break;
	}
	$(bloque).empty();
	$(bloque).append(myText);
	fnjs_cambiar_link(bloque); 
}


fnjs_cambiar_link=function(id_div) {
	// busco si hay un id=ir_a que es para ir a otra página
	if ($('#ir_a').length) { fnjs_ir_a(id_div); return false; } 
	if ($('#go_atras').length) { fnjs_ir_a(id_div); return false; } 
	if ($('#ir_atras').length) { fnjs_left_side_show(); return true; } 
	if ($('#js_atras').length) { fnjs_ir_a(id_div); return true; } 
	var base=$(id_div).attr('refe');
	if (base) {
		var selector=id_div+" a[href]";
		$(selector).each(function(i) {
			var aa=this.href;
			// si tiene una ref a name(#):
			if (aa != undefined && aa.indexOf("#") != -1) {
				part=aa.split("#");
				this.href="";
				$(this).attr("onclick","location.hash = '#"+part[1]+"'; return false;");
			} else {
				url=fnjs_ref_absoluta(base,aa);
				var path=aa.replace(/[\?#].*$/,''); // borro desde el '?' o el '#'
				var extension=path.substr(-4);
				if (extension==".php" || extension=="html" || extension==".htm" ) { // documento web
					this.href="";
					$(this).attr("onclick","fnjs_update_div('"+id_div+"','"+url+"'); return false;");
				} else {
					this.href=url;
				}
			}
		});
	}
}

fnjs_ir_a=function(id_div) {
	var url=$(id_div+" [name='url']").val();
	var parametros=$(id_div+" [name='parametros']").val();
	var bloque=$(id_div+" [name='id_div']").val();
	
	fnjs_left_side_hide();

	$(bloque).attr('refe',url);
	fnjs_borrar_posibles_atras();
	$.ajax({
			url: url,
			type: 'post',
			data: parametros,
			complete: function (resposta) { fnjs_mostra_resposta (resposta,bloque); },
			error: fnjs_procesarError
			}) ;
	return false;
}


fnjs_left_side_show=function() {
  if ($('#left_slide').length) { $('#left_slide').show(); } 
}
fnjs_left_side_hide=function() {
  if ($('#left_slide').length) { $('#left_slide').hide(); } 
}


fnjs_logout=function() {
	var parametros='logout=si'; 
	top.location.href='index.php?'+parametros;
}


fnjs_sin_acentos=function(text) {
	var chars = {'á':'a','é':'e','í':'i','ó':'o','ú':'u','ç':'cz','à':'a','è':'e','ò':'o','ä':'a','ë':'e','ï':'i','ö':'o','ü':'u','â':'a','ê':'e','î':'i','ô':'o','û':'u','Á':'A','É':'E','Í':'I','Ó':'O','Ú':'U','Ç':'CZ','À':'A','È':'E','Ò':'O','Ä':'A','Ë':'E','Ï':'I','Ö':'O','Ü':'U','Â':'A','Ê':'E','Î':'I','Ô':'O','Û':'U','ñ':'nz','Ñ':'NZ'} 
	rta = text.replace(/[áéíóúçàèòäëïöüâêîôûÁÉÍÓÚÇÀÈÒÄËÏÖÜÂÊÎÔÛñÑ]/g, m => chars[m]);
	return rta;
}

modalPrompt=function (options) {
	var deferredObject = $.Deferred();
	var defaults = {
		type: "prompt", //alert, prompt,confirm 
		modalSize: 'modal-sm', //modal-sm, modal-lg
		okButtonText: 'Ok',
		cancelButtonText: 'Cancel',
		yesButtonText: 'Yes',
		noButtonText: 'No',
		headerText: 'Tramity',
		messageText: 'Message',
		alertType: 'default', //default, primary, success, info, warning, danger
		inputFieldType: 'text', //could ask for number,email,etc
	}
	$.extend(defaults, options);
  
	var _show = function(){
		var headClass = "navbar-default";
		switch (defaults.alertType) {
			case "primary":
				headClass = "alert-primary";
				break;
			case "success":
				headClass = "alert-success";
				break;
			case "info":
				headClass = "alert-info";
				break;
			case "warning":
				headClass = "alert-warning";
				break;
			case "danger":
				headClass = "alert-danger";
				break;
        }
		$('#main').append(
			'<div id="ezAlerts" class="modal fade">' +
			'<div class="modal-dialog" class="' + defaults.modalSize + '">' +
			'<div class="modal-content">' +
			'<div id="ezAlerts-header" class="modal-header ' + headClass + '">' +
			'<h4 id="ezAlerts-title" class="modal-title">Modal title</h4>' +
			'<button id="close-button" type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>' +
			'</div>' +
			'<div id="ezAlerts-body" class="modal-body">' +
			'<div id="ezAlerts-message" ></div>' +
			'</div>' +
			'<div id="ezAlerts-footer" class="modal-footer">' +
			'</div>' +
			'</div>' +
			'</div>' +
			'</div>'
		);

		$('.modal-header').css({
			'padding': '15px 15px',
			'-webkit-border-top-left-radius': '5px',
			'-webkit-border-top-right-radius': '5px',
			'-moz-border-radius-topleft': '5px',
			'-moz-border-radius-topright': '5px',
			'border-top-left-radius': '5px',
			'border-top-right-radius': '5px'
		});
    
		$('#ezAlerts-title').text(defaults.headerText);
		$('#ezAlerts-message').html(defaults.messageText);

		var keyb = false, backd = "static";
		var calbackParam = "";
		switch (defaults.type) {
			case 'alert':
				keyb = "true";
				backd = "true";
				$('#ezAlerts-footer').html('<button class="btn btn-' + defaults.alertType + '">' + defaults.okButtonText + '</button>').on('click', ".btn", function () {
					calbackParam = true;
					$('#ezAlerts').modal('hide');
				});
				break;
			case 'confirm':
				var btnhtml = '<button id="ezok-btn" class="btn btn-primary">' + defaults.yesButtonText + '</button>';
				if (defaults.noButtonText && defaults.noButtonText.length > 0) {
					btnhtml += '<button id="ezclose-btn" class="btn btn-default">' + defaults.noButtonText + '</button>';
				}
				$('#ezAlerts-footer').html(btnhtml).on('click', 'button', function (e) {
						if (e.target.id === 'ezok-btn') {
							calbackParam = true;
							$('#ezAlerts').modal('hide');
						} else if (e.target.id === 'ezclose-btn') {
							calbackParam = false;
							$('#ezAlerts').modal('hide');
						}
					});
				break;
			case 'prompt':
				$('#ezAlerts-message').html(defaults.messageText + '<br /><br /><div class="form-group"><input type="' + defaults.inputFieldType + '" class="form-control" id="prompt" /></div>');
				$('#ezAlerts-footer').html('<button class="btn btn-primary">' + defaults.okButtonText + '</button>').on('click', ".btn", function () {
					calbackParam = $('#prompt').val();
					$('#ezAlerts').modal('hide');
				});
				break;
		}
   
		$('#ezAlerts').modal({ 
          show: false, 
          backdrop: backd, 
          keyboard: keyb 
        }).on('hidden.bs.modal', function (e) {
			$('#ezAlerts').remove();
			deferredObject.resolve(calbackParam);
		}).on('shown.bs.modal', function (e) {
			if ($('#prompt').length > 0) {
				$('#prompt').focus();
			}
		}).modal('show');
	}
    
  _show();  
  return deferredObject.promise();    
}
