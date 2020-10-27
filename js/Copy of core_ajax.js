function fnjs_mostrar_atras(id_div,htmlForm) {
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

function fnjs_enviar_formulario(id_form,bloque) {
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

function fnjs_update_div(bloque,ref) {
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
	debugger;	
		alert('An error occurred... Look at the console (F12 or Ctrl+Shift+I, Console tab) for more information!');
        console.error("Hi ha un error: "+ textStatus, errorThrown);
    });
	return false;
}

function fnjs_borrar_posibles_atras() {
	if ($('#ir_a').length) $('#ir_a').remove() ;
	if ($('#ir_atras').length) $('#ir_atras').remove() ;
	if ($('#ir_atras2').length) $('#ir_atras2').remove() ;
	if ($('#js_atras').length) $('#js_atras').remove() ;
	if ($('#go_atras').length) $('#go_atras').remove() ;
}
	
function fnjs_mostra_resposta(resposta,bloque) {
	switch (typeof resposta) {
		case 'object':
			var myText=resposta.responseText;
			break;
		case 'string':
			var myText=resposta.trim();
			break;
	}
	$(bloque).empty();
	$(bloque).append(myText);
	fnjs_cambiar_link(bloque); 
}


function fnjs_cambiar_link(id_div) {
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

function fnjs_ir_a(id_div) {
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


function fnjs_left_side_show() {
  if ($('#left_slide').length) { $('#left_slide').show(); } 
}
function fnjs_left_side_hide() {
  if ($('#left_slide').length) { $('#left_slide').hide(); } 
}


function fnjs_logout() {
	var parametros='logout=si'; 
	top.location.href='index.php?'+parametros;
}


function fnjs_sin_acentos(text) {
	var chars = {'á':'a','é':'e','í':'i','ó':'o','ú':'u','ç':'cz','à':'a','è':'e','ò':'o','ä':'a','ë':'e','ï':'i','ö':'o','ü':'u','â':'a','ê':'e','î':'i','ô':'o','û':'u','Á':'A','É':'E','Í':'I','Ó':'O','Ú':'U','Ç':'CZ','À':'A','È':'E','Ò':'O','Ä':'A','Ë':'E','Ï':'I','Ö':'O','Ü':'U','Â':'A','Ê':'E','Î':'I','Ô':'O','Û':'U','ñ':'nz','Ñ':'NZ'} 
	rta = text.replace(/[áéíóúçàèòäëïöüâêîôûÁÉÍÓÚÇÀÈÒÄËÏÖÜÂÊÎÔÛñÑ]/g, m => chars[m]);
	return rta;
}
