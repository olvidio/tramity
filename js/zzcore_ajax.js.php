
function fnjs_selectAll(formulario,Name,val,aviso){
	aviso = typeof aviso !== 'undefined' ? aviso : 1;
	if (aviso == 1) {
		alert ("<?php use core\ConfigGlobal; printf (_("Sólo se seleccionan los ítems que se ha visualizado.")); ?>");
	}
	var form=$(formulario).attr('id');
	/* selecciono los elementos input del id=formulario */
	var selector=$('#'+form+' input');
	if(val==null){var val='toggle';}
	$(selector).each(function(i,item) {
			if($(item).attr('name') == Name) {
				switch (val) {
					case 'all':
						$(item).prop('checked',true);
						break;
					case 'none':
						$(item).prop('checked',false);
						break;
					case 'toggle':
						$(item).trigger("click");
						break;
				}
			}
		}
	);
}

function fnjs_solo_uno_grid(formulario) {
	var s=0;
	var form=$(formulario).attr('id');
	/* selecciono los elementos con class="slick-cell-checkboxsel" de las tablas del id=formulario */
	var sel=$('#'+form+' div.slick-cell-checkboxsel > input:checked');
	var s = sel.length;

	if ( s > 1 ) {
		alert ("<?php printf (_("Sólo puede seleccionar un elemento. Ha selecionado %s."),'"+s+"'); ?>");
	}
	if (s==0) {
		alert ("<?php printf (_("No ha seleccionado ninguna fila. debe hacer click en algún chekbox de la izquierda. ")); ?>");
	}
	return s;
}

function fnjs_solo_uno(formulario, multiple = false) {
	var s=0;
	var form=$(formulario).attr('id');
	/* selecciono los elementos con class="sel" de las tablas del id=formulario */
	var sel=$('#'+form+' input.sel:checked');
	var s = sel.length;

	if ( (s > 1) && (!multiple) ) {
		alert ("<?php printf (_("Sólo puede seleccionar un elemento. Ha selecionado %s."),'"+s+"'); ?>");
	}
	if (s==0) {
		alert ("<?php printf (_("No ha seleccionado ninguna fila. debe hacer click en algún chekbox de la izquierda. ")); ?>");
	}
	return s;
}

fnjs_proto=function(num,any){
	var siguiente="xx";
	var s=0;
	var numero;
	var prot_num=$(num).val();
	var prot_any=$(any).val();
	numero=prot_num.split("/");
	if (numero[1]) {
		$(num).val(numero[0]);
		$(any).val(numero[1]);
		prot_any=$(any).val();
  	} else {
		if (!prot_any) {
			calDate = new Date();
  			var year  = calDate.getFullYear();
			$(any).val(year);
			prot_any=year;
		}
	}

	/* Para el número de entrada */
	if (num=='#prot_num') {
		que="e1";
		prot_num=$('#prot_num').val();
		prot_any=$('#prot_any').val();
		id_lugar=0;
		siguiente="#origen_id_lugar";
		var primera_ref=0;
	}
	/* Para el número de origen */
	if (num=='#origen_num') {
		que="e2";
		id_lugar=$('#origen_id_lugar').val();
		var nom_lugar=$('#origen_id_lugar :selected').text();
		if (nom_lugar=='cr') {
			$('#grupo_destinos').show();
		}
		prot_num=$('#origen_num').val();
		prot_any=$('#origen_any').val();
		siguiente="#mas_ref_id_lugar";
		var primera_ref=0;
	}
	/* Para el número de referencia */
	if (num.substr(0,13)=='#ref_prot_num') {
		/* sólo recojo información de la primera referencia */
		if (num.substr(14,1)=='0') {
			que="e3";
			var nom='#ref_id_lugar_0';
			id_lugar=$(nom).val();
			prot_num=$(num).val();
			prot_any=$(any).val();
			siguiente="#f_doc_entrada";
		} else {
			$('#f_doc_entrada').focus();
			return; /* no hago nada */
		}
	}
	
	var url='<?= ConfigGlobal::$web ?>/scdl/registro/comprobar_protocolo.php';
	var parametros='que='+que+'&id_lugar='+id_lugar+'&prot_num='+prot_num+'&prot_any='+prot_any+'&PHPSESSID=<?php echo session_id(); ?>';
		 
	$.ajax({
		url: url,
		type: 'post',
		data: parametros,
		success: function (rta) {
			//alert ('respuesta: '+rta);
			rta2=jQuery.parseJSON(rta);
			if (rta2.rango) { alert("<?php echo _("nº de protocolo fuera de rango"); ?>"); s=1; }
			if (rta2.any) { alert("<?php echo _("No es de este año, ni del año pasado"); ?>"); s=1; }
			if (rta2.repe) { alert("<?php echo _("nº de protocolo repetido"); ?>");  s=1; }
			if (rta2.registrado) { alert("<?php echo _("nº de protocolo de origen ya registrado"); ?>");  s=1; }
			if (rta2.anulado) {
				alert("<?php printf(_("El escrito de referencia ha sido anulado")); ?>"+" ("+rta2.anulado+").");
			}
			if (rta2.salto) {
				alert("<?php printf(_("El nº de protocolo tiene un salto de más de %s números respecto al último"),$error_prot); ?>"+" ("+rta2.salto+").");
				s=1;
			}
			if (que=="e3") {
				if (rta2.asunto) {
					//caso de escrito reservado
					if (rta2.asunto_r) {
						$('#asunto').val(rta2.asunto_r);
						$('#asunto').prop("disabled",true);
						$('#asunto_org').val(rta2.asunto);
					} else {
						$('#asunto').val(rta2.asunto);
					}
				} 
				if (rta2.detalle) {
					//caso de escrito reservado
					if (rta2.detalle_r) {
						$('#detalle').val(rta2.detalle_r);
						$('#detalle').prop("disabled",true);
						$('#detalle_org').val(rta2.detalle);
					} else {
						$('#detalle').val(rta2.detalle);
					}
				} 
				if (rta2.reservado=="t") {
					$('#reservado').prop("checked",true);
				} else { 
					$('#reservado').prop("checked",false);
				}
				if (rta2.oficinas) {
					//pongo a 0 (por si ya habia algo)
					fnjs_quitar_oficinas();
					var array_of=rta2.oficinas.split(" ");
					$.each(array_of,function(i,id_of) {
						if (id_of) { //puede haber un espacio al final i lo cuenta.
							$('#mas_of').val(id_of);
							fnjs_mas_oficinas('x');
						}
					});
				}
			}
			if (s==1) { $(num).focus(); } else { $(siguiente).focus(); }
		}
	});
}

