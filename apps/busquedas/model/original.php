<?php
/**
* tabla de datos del registro.
*
*@package	delegacion
*@subpackage	registro
*@author	Daniel Serrabou
*@since		21/5/03.
*		
*/

// INICIO Cabecera global de URL de controlador *********************************
	require_once ("global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

include_once("func_reg.php"); 

$oDBR = new PDO(ConfigGlobal::$str_conexio_reg);

/* posibles variables:
prot_num
prot_any

prot_num_cr
prot_any_cr

rta_id_lugar
rta_num
rta_any

ref_id_lugar
ref_num
ref_any

dest_id_lugar
origen_id_lugar

asunto
responsable

f_entrada_min
f_entrada_max
-------------------
lista_origen
lista_lugar

f_lista_min
f_lista_max
*/

//si vengo por un go_to:
!empty($_POST['go_to'])? $go=strtok($_POST['go_to'],"@"): $go="";
if ($go=="session" && isset($_SESSION['session_go_to']) ) {
	$g=strtok("@");
	empty($_SESSION['session_go_to'][$g]['prot_num'])? $prot_num='' : $prot_num=$_SESSION['session_go_to'][$g]['prot_num'];
	empty($_SESSION['session_go_to'][$g]['prot_any'])? $prot_any='' : $prot_any=$_SESSION['session_go_to'][$g]['prot_any'];
	empty($_SESSION['session_go_to'][$g]['simple'])? $simple='' : $simple=$_SESSION['session_go_to'][$g]['simple'];
	empty($_SESSION['session_go_to'][$g]['antiguedad'])? $antiguedad='' : $antiguedad=$_SESSION['session_go_to'][$g]['antiguedad'];
	empty($_SESSION['session_go_to'][$g]['origen_id_lugar'])? $origen_id_lugar='' : $origen_id_lugar=$_SESSION['session_go_to'][$g]['origen_id_lugar'];
	empty($_SESSION['session_go_to'][$g]['opcion'])? $opcion='' : $opcion=$_SESSION['session_go_to'][$g]['opcion'];
	empty($_SESSION['session_go_to'][$g]['mas'])? $mas='' : $mas=$_SESSION['session_go_to'][$g]['mas'];
	empty($_SESSION['session_go_to'][$g]['lugar'])? $lugar='' : $lugar=$_SESSION['session_go_to'][$g]['lugar'];
	empty($_SESSION['session_go_to'][$g]['f_min'])? $f_min='' : $f_min=$_SESSION['session_go_to'][$g]['f_min'];
	empty($_SESSION['session_go_to'][$g]['f_max'])? $f_max='' : $f_max=$_SESSION['session_go_to'][$g]['f_max'];
	empty($_SESSION['session_go_to'][$g]['asunto'])? $asunto='' : $asunto=$_SESSION['session_go_to'][$g]['asunto'];
	empty($_SESSION['session_go_to'][$g]['oficina'])? $oficina='' : $oficina=$_SESSION['session_go_to'][$g]['oficina'];
	empty($_SESSION['session_go_to'][$g]['dest_id_lugar'])? $dest_id_lugar='' : $dest_id_lugar=$_SESSION['session_go_to'][$g]['dest_id_lugar'];
	empty($_SESSION['session_go_to'][$g]['lista_origen'])? $lista_origen='' : $lista_origen=$_SESSION['session_go_to'][$g]['lista_origen'];
	empty($_SESSION['session_go_to'][$g]['lista_lugar'])? $lista_lugar='' : $lista_lugar=$_SESSION['session_go_to'][$g]['lista_lugar'];
} else{
	empty($_POST['prot_num'])? $prot_num="" : $prot_num=$_POST['prot_num'];
	empty($_POST['prot_any'])? $prot_any="" : $prot_any=$_POST['prot_any'];
	empty($_POST['simple'])? $simple="" : $simple=$_POST['simple'];
	empty($_POST['antiguedad'])? $antiguedad="" : $antiguedad=$_POST['antiguedad'];
	empty($_POST['origen_id_lugar'])? $origen_id_lugar="" : $origen_id_lugar=$_POST['origen_id_lugar'];
	empty($_POST['opcion'])? $opcion="" : $opcion=$_POST['opcion'];
	empty($_POST['mas'])? $mas="" : $mas=$_POST['mas'];
	empty($_POST['lugar'])? $lugar="" : $lugar=$_POST['lugar'];
	empty($_POST['f_min'])? $f_min="" : $f_min=$_POST['f_min'];
	empty($_POST['f_max'])? $f_max="" : $f_max=$_POST['f_max'];
	empty($_POST['asunto'])? $asunto="" : $asunto=$_POST['asunto'];
	empty($_POST['oficina'])? $oficina="" : $oficina=$_POST['oficina'];
	empty($_POST['dest_id_lugar'])? $dest_id_lugar="" : $dest_id_lugar=$_POST['dest_id_lugar'];
	empty($_POST['lista_origen'])? $lista_origen="" : $lista_origen=$_POST['lista_origen'];
	empty($_POST['lista_lugar'])? $lista_lugar="" : $lista_lugar=$_POST['lista_lugar'];
}

/*
* Defino un array con los datos actuales, para saber volver después de navegar un rato
*/
$session_sel=array (	'dir_pag'=>ConfigGlobal::$directorio."/scdl/registro",
				'url_pag'=>"/scdl/registro/",
				'pag'=>"registro_tabla.php",
				'target'=>"main",
				'prot_num'=>$prot_num,
				'prot_any'=>$prot_any,
				'simple'=>$simple,
				'antiguedad'=>$antiguedad,
				'origen_id_lugar'=>$origen_id_lugar,
				'opcion'=>$opcion,
				'mas'=>$mas,
				'lugar'=>$lugar,
				'f_min'=>$f_min,
				'f_max'=>$f_max,
				'asunto'=>$asunto,
				'oficina'=>$oficina,
				'dest_id_lugar'=>$dest_id_lugar,
				'lista_origen'=>$lista_origen,
				'lista_lugar'=>$lista_lugar
				 );
$session_go_to["sel"]=$session_sel;
$_SESSION['session_go_to']=$session_go_to;

// Utilizo la @ como separador (con #_... he tenido problemas)
$go_to="session@sel";

?>
<script>
fnjs_modificar_det=function(formulario){
	rta=fnjs_solo_uno(formulario);
	if (rta==1) {
  		$(formulario).attr('action',"scdl/registro/mod_detalle.php");
  		fnjs_enviar_formulario(formulario);
  	}
}
fnjs_modificar_of=function(formulario){
        rta=fnjs_solo_uno(formulario);
        if (rta==1) {
			$(formulario).attr('action',"scdl/registro/asunto_of.php");
			fnjs_enviar_formulario(formulario);
        }
}
fnjs_modificar=function(formulario){
	rta=fnjs_solo_uno(formulario);
	if (rta==1) {
  		$(formulario).attr('action',"scdl/registro/registro_modificar.php");
  		fnjs_enviar_formulario(formulario);
  	}
}
fnjs_borrar=function(formulario){
	rta=fnjs_solo_uno(formulario);
	var seguro;
	if (rta==1) {
		fnjs_buscar_pendiente(formulario);
		seguro=confirm("<?php echo _("¿Está Seguro que desea borrar este registro?");?>");
		if (seguro) {
			$(formulario).attr('action',"scdl/registro/registro_eliminar.php");
			fnjs_enviar_formulario(formulario);
		}
  	}
}
fnjs_buscar_pendiente=function(formulario){
	var form=$(formulario).attr('id');
	var id_reg;
	/* selecciono los elementos con class="sel" de las tablas del id=formulario */
	/* var sel=$('#'+formulario+' table .sel'); */
	$('#'+form+' input.sel').each(function(i){
		if($(this).prop('checked')== true) {
			// como ya he comprobado que sólo está uno seleccionado, es este.
			var array_dir=$(this).val().split('#');
			id_reg=array_dir[0];
		}
	});

	var url='<?= ConfigGlobal::$web ?>/scdl/registro/comprobar_protocolo.php';
	var parametros='que=anular&id_reg='+id_reg+'&PHPSESSID=<?php echo session_id(); ?>';
		 
	$.ajax({
		url: url,
		type: 'post',
		data: parametros,
		success: function (rta) {
			//rta_txt=rta.responseText;
			//alert ('respuesta: '+rta_txt);
			rta2=jQuery.parseJSON(rta);
			if (rta2.pendiente_txt) {
				alert(rta2.pendiente_txt);
			}
		}
	});
}

</script>
<?php

// Busco el id_lugar de la dl.
$query_id_dl="SELECT id_lugar
					FROM lugares
					WHERE sigla='".ConfigGlobal::$dele."' ";
//echo "query: $query_ref<br>";
$id_dl=$oDBR->query($query_id_dl)->fetchColumn();
// Busco el id_lugar de cr.
$query_id_cr="SELECT id_lugar
					FROM lugares
					WHERE sigla='cr' ";
//echo "query: $query_ref<br>";
$id_cr=$oDBR->query($query_id_cr)->fetchColumn();

?>
<div id="resultados" >
<?php
$cond_ent = '';
$cond_ap = '';
$cond_asunto = '';
$periodo = '';
/*
***************** BUSQUEDA CON MÁS OPCIONES **************** 
*/
switch ($opcion) {
	case 7:	// un protocolo concreto:	
		if (empty($mas)) {
			switch($lugar) {
				case $id_dl:
					$donde=" es.prot_num='".$prot_num."'";
					if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND es.prot_any='".$prot_any."'"; }
					//echo tabla_entradas($donde,"","");
					echo tabla_salidas($donde,"","");
				break;
				case $id_cr:
					$donde=" en.id_lugar='".$id_cr."' AND en.prot_num='".$prot_num."'";
					if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND en.prot_any='".$prot_any."'"; }
					echo tabla_entradas($donde,"","");
				break;
				default:
					$donde=" en.id_lugar='".$lugar."' AND en.prot_num='".$prot_num."'";
					if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND en.prot_any='".$prot_any."'"; }
					echo tabla_entradas($donde,"","");
				break;
			}
			$pag_mas="scdl/registro/registro_tabla.php?lugar=$lugar&prot_num=$prot_num&prot_any=$prot_any&opcion=$opcion&mas=1";
			echo "<p><span class=link onclick=fnjs_update_div('#main','$pag_mas')>"._("Buscar otros escritos con esta referencia").".</span></p>";
		} else {
			switch($lugar) {
				case $id_dl:
					$donde=" es.prot_num='".$prot_num."'";
					if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND es.prot_any='".$prot_any."'"; }
					// Escritos aprobados en la dl
					echo tabla_salidas($donde,"","");
					// Escritos recibidos en la dl
					echo tabla_entradas($donde,"","");
					//contesta a un escrito de 'dlb' => buscar en ref.
					$donde_ref="AND ref.prot_num='".$prot_num."'";
					if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde_ref.="AND ref.prot_any='".$prot_any."'"; }
					$sql_en= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
						en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
						u.sigla, en.f_doc_entrada
						FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u, referencias ref
						WHERE en.id_lugar=u.id_lugar AND ref.id_reg=es.id_reg AND ref.id_lugar=$id_dl $donde_ref
						";
					$txt_titulo=_("escritos recibidos en la Delegación con esta referencia");
					echo tabla_entradas("",$sql_en,"",$txt_titulo);
					
					$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
							ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
							FROM escritos es, aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), referencias ref, x_modo_envio x
							WHERE es.id_reg=ap.id_reg AND ref.id_reg=es.id_reg 
								AND ref.id_lugar=$id_dl AND x.id_modo_envio=ap.id_modo_envio
								$donde_ref
							";
					$txt_titulo=_("escritos aprobados en la Delegación con esta referencia");
					echo tabla_salidas("",$sql_sal,"",$txt_titulo);
				break;
				case $id_cr:
					$donde=" en.id_lugar='".$id_cr."' AND en.prot_num='".$prot_num."'";
					if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND en.prot_any='".$prot_any."'"; }
					echo tabla_entradas($donde,"","");
					// escritos aprobados enviados a cr.
					$donde=" AND dest.prot_num='".$prot_num."'";
					if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND dest.prot_any='".$prot_any."'"; }
					$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,
							es.distribucion_cr, ap.id_salida,ap.f_aprobacion,ap.f_salida
							FROM escritos es, aprobaciones ap LEFT JOIN destinos dest USING (id_salida), x_modo_envio x
							WHERE es.distribucion_cr='f' AND es.id_reg=ap.id_reg 
								AND dest.id_lugar=$id_cr AND x.id_modo_envio=ap.id_modo_envio
								$donde
							";
					$txt_titulo=_("escritos aprobados enviados a cr");
					echo tabla_salidas("",$sql_sal,"",$txt_titulo);
					// escritos de cr enviados a ctr.
					$donde=" AND en.prot_num='".$prot_num."'";
					if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND en.prot_any='".$prot_any."'"; }
					$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
							ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
							FROM escritos es, aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), entradas en, x_modo_envio x
							WHERE es.distribucion_cr='t' AND es.id_reg=ap.id_reg AND en.id_reg=es.id_reg 
								AND en.id_lugar=$id_cr AND x.id_modo_envio=ap.id_modo_envio
								$donde
							";
					$txt_titulo=_("escritos de cr enviados a los ctr");
					echo tabla_salidas("",$sql_sal,"",$txt_titulo);
					//contesta a un escrito de 'dlb' => buscar en ref.
					$donde_ref="AND ref.prot_num='".$prot_num."'";
					if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde_ref.="AND ref.prot_any='".$prot_any."'"; }
					$sql_en= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
						en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
						u.sigla, en.f_doc_entrada
						FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u, referencias ref
						WHERE en.id_lugar=u.id_lugar AND ref.id_reg=es.id_reg AND ref.id_lugar=$id_cr $donde_ref
						";
					$txt_titulo=_("escritos recibidos en la Delegación con esta referencia");
					echo tabla_entradas("",$sql_en,"",$txt_titulo);
					
					$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
							ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
							FROM escritos es, aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), referencias ref, x_modo_envio x
							WHERE es.id_reg=ap.id_reg AND ref.id_reg=es.id_reg 
								AND ref.id_lugar=$id_cr AND x.id_modo_envio=ap.id_modo_envio
								$donde_ref
							";
					$txt_titulo=_("escritos aprobados en la Delegación con esta referencia");
					echo tabla_salidas("",$sql_sal,"",$txt_titulo);
				break;
				default:
					$donde=" en.id_lugar='".$lugar."' AND en.prot_num='".$prot_num."'";
					if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND en.prot_any='".$prot_any."'"; }
					echo tabla_entradas($donde,"","");
					//contesta a un escrito de ctr... => buscar en destinos.
					$donde_dest="AND dest.prot_num='".$prot_num."'";
					if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde_dest.="AND dest.prot_any='".$prot_any."'"; }
					$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
							ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
							FROM escritos es LEFT JOIN destinos dest USING (id_reg), aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), x_modo_envio x
							WHERE es.id_reg=ap.id_reg 
								AND dest.id_lugar=$lugar AND x.id_modo_envio=ap.id_modo_envio
								$donde_dest
							";
					echo tabla_salidas("",$sql_sal,"");
					//contesta a un escrito de 'dlb' => buscar en ref.
					$donde_ref="AND ref.prot_num='".$prot_num."'";
					if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde_ref.="AND ref.prot_any='".$prot_any."'"; }
					$sql_en= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
						en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
						u.sigla, en.f_doc_entrada
						FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u, referencias ref
						WHERE en.id_lugar=u.id_lugar AND ref.id_reg=es.id_reg AND ref.id_lugar=$lugar $donde_ref
						";
					$txt_titulo=_("escritos recibidos en la Delegación con esta referencia");
					echo tabla_entradas("",$sql_en,"",$txt_titulo);
					
					$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
							ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
							FROM escritos es, aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), referencias ref, x_modo_envio x
							WHERE es.id_reg=ap.id_reg AND ref.id_reg=es.id_reg 
								AND ref.id_lugar=$lugar AND x.id_modo_envio=ap.id_modo_envio
								$donde_ref
							";
					$txt_titulo=_("escritos aprobados en la Delegación con esta referencia");
					echo tabla_salidas("",$sql_sal,"",$txt_titulo);
				break;
			}
		}
		break;
	case 1:	// Listado de los últimos
		if (!empty($antiguedad)) {
			switch ($antiguedad) {
				case "1m":
					$limite = date("d/m/Y",mktime(0, 0, 0, date("m")-1, date("d"),date("Y")));
					break;
				case "3m":
					$limite = date("d/m/Y",mktime(0, 0, 0, date("m")-3, date("d"),date("Y")));
					break;
				case "6m":
					$limite = date("d/m/Y",mktime(0, 0, 0, date("m")-6, date("d"),date("Y")));
					break;
				case "1a":
					$limite = date("d/m/Y",mktime(0, 0, 0, date("m"), date("d"),date("Y")-1));
					break;
				case "2a":
					$limite = date("d/m/Y",mktime(0, 0, 0, date("m"),date("d"),date("Y")-2));
					break;
			}
			$periodo="AND en.f_entrada > '$limite'";
			if ($antiguedad=="aa") $periodo="";
		}
		// Caso especial de querer ver los escritos de la dl. No se consulta el origen, sino el protocolo.
		// se omiten los de distribución de cr.
		if ($origen_id_lugar==$id_dl) {
			$cond_ap="AND f_aprobacion >= '$limite'";
			$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
				ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
				FROM escritos es LEFT JOIN destinos dest USING (id_reg), aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), x_modo_envio x
				WHERE es.id_reg=ap.id_reg AND x.id_modo_envio=ap.id_modo_envio AND distribucion_cr='f'
					$cond_ap
				GROUP BY es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
							ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
				";
			echo tabla_salidas("",$sql_sal,"");
		} elseif (!empty($origen_id_lugar)) {
			$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
				en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
				u.sigla,en.f_doc_entrada
				FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u
				WHERE en.id_lugar=u.id_lugar AND en.id_lugar='$origen_id_lugar' $cond_ent $cond_asunto $periodo
				";
			if (!empty($cond_of_pral)) {
				$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
					en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
					u.sigla, en.f_doc_entrada
					FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u, oficinas of
					WHERE en.id_lugar='$origen_id_lugar' AND en.id_lugar=u.id_lugar
					AND en.id_reg=of.id_reg AND en.id_entrada=of.id_e_s
					$cond_of_pral $cond_doc $cond_ent $cond_asunto 
					";
					/*
					GROUP BY es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,en.id_entrada,en.f_entrada,o_lugar,o_prot_num,o_prot_any,
					en.mas,u.sigla, en.f_doc_entrada
					";*/
			}
			//echo "sql: $sql<br>";
			echo tabla_entradas("",$sql,"");
		}
		echo "</div>";
		break;
	case 2:
		// buscar en asunto, detalle, asunto oficina. + periodo + oficina 
		// las fechas.
		if (!empty($f_min)) {
			$cond_ent="AND f_entrada >= '$f_min'";
			$cond_ap="AND f_aprobacion >= '$f_min'";
		}
		if (!empty($f_max)) {
			$cond_ent.="AND f_entrada <= '$f_max'";
			$cond_ap.="AND f_aprobacion <= '$f_max'";
		}

		if (!empty($asunto)){
			// en el escrito:
			// sólo pueden buscar en el detalle los directores.
			if ($GLOBALS['oPerm']->have_perm("dtor")) {
				$cond_asunto="(sin_acentos(asunto) ~* sin_acentos('.*$asunto.*') OR sin_acentos(detalle) ~* sin_acentos('.*$asunto.*'))";
			} else {
				$cond_asunto="(sin_acentos(asunto) ~* sin_acentos('.*$asunto.*')) AND reservado='f'";
			}
			/* para buscar en asunto oficina:
				busco las oficinas posibles, miro si tengo permiso para alguna de ellas
			*/
			$sql_of="SELECT id_oficina,sigla,permiso FROM oficinas JOIN x_oficinas USING (id_oficina) GROUP BY id_oficina,sigla,permiso";
			$oDBRSt_q_of=$oDBR->query($sql_of);
			$i=0;
			$cond_asunto_of="";
			foreach ($oDBRSt_q_of->fetchAll() as $row_of) {
				$i++;
				$sigla=$row_of['permiso'];
				$id_oficina=$row_of['id_oficina'];
				if ($GLOBALS['oPerm']->have_perm($sigla)) { 
					$cond_asunto_of.=" OR ( id_oficina=$id_oficina AND sin_acentos(asunto_of) ~* sin_acentos('.*$asunto.*') )";
				}
			}
			if (!empty($cond_asunto_of)) {
				$cond_asunto_of=substr($cond_asunto_of,3);
				$sql_of2="SELECT id_reg,id_e_s FROM oficinas WHERE $cond_asunto_of";
				//echo "sql: $sql_of2<br>";
				$oDBRSt_q_of2=$oDBR->query($sql_of2);
				$i=0;
				$cond_id_reg="";
				foreach ($oDBRSt_q_of2->fetchAll() as $row_of2) {
					$i++;
					extract($row_of2);
					$cond_id_reg.=" OR (es.id_reg=$id_reg)";
				}
			}

			if (!empty($cond_id_reg)) {
				$cond_asunto=" AND ($cond_asunto $cond_id_reg)";
			} else {
				$cond_asunto=" AND $cond_asunto";
			}
		}

		if (!empty($oficina)) {
			$cond_of="AND id_oficina='$oficina'";
			$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
			en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
			u.sigla, en.f_doc_entrada
			FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u, oficinas of
			WHERE en.id_lugar=u.id_lugar 
			AND en.id_reg=of.id_reg AND en.id_entrada=of.id_e_s
			$cond_ent $cond_of $cond_asunto
			";
			//echo "sql_1: $sql<br>";
			echo tabla_entradas("",$sql,"");
			$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
				ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
				FROM escritos es LEFT JOIN destinos dest USING (id_reg), aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), oficinas of, x_modo_envio x
				WHERE es.id_reg=ap.id_reg
					AND ap.id_reg=of.id_reg AND ap.id_salida=of.id_e_s AND x.id_modo_envio=ap.id_modo_envio
					$cond_ap $cond_of $cond_asunto
				GROUP BY es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
						ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
				";
			//echo "sql_2: $sql_sal<br>";
			echo tabla_salidas("",$sql_sal,"");
		} else {
			// si no hay que buscar en las oficinas 
			$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
				en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
				u.sigla, en.f_doc_entrada
				FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u
				WHERE en.id_lugar=u.id_lugar $cond_ent $cond_asunto
				";
			//echo "sql_3: $sql<br>";
			echo tabla_entradas("",$sql,"");

			$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
				ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
				FROM escritos es LEFT JOIN destinos dest USING (id_reg), aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), x_modo_envio x
				WHERE es.id_reg=ap.id_reg AND x.id_modo_envio=ap.id_modo_envio
					$cond_ap $cond_asunto
				GROUP BY es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
							ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
				";
			//echo "sql_4: $sql_sal<br>";
			echo tabla_salidas("",$sql_sal,"");
		}
		break;
	case 3:
		// buscar en origen, destino o ambos

		// las fechas.
		if (!empty($f_min)) {
			$cond_ent="AND f_entrada >= '$f_min'";
			$cond_ap="AND f_aprobacion >= '$f_min'";
		}
		if (!empty($f_max)) {
			$cond_ent.="AND f_entrada <= '$f_max'";
			$cond_ap.="AND f_aprobacion <= '$f_max'";
		}
		if (!empty($oficina)) { $cond_of="AND id_oficina='$oficina'"; }
		switch ($dest_id_lugar) {
			case $id_dl:
				switch ($origen_id_lugar) {
					case "":
						$txt_titulo=_("escritos recibidos en la Delegación");
						if (!empty($oficina)) {
							$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
								en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
								u.sigla, en.f_doc_entrada
								FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u, oficinas of
								WHERE en.id_lugar=u.id_lugar AND en.id_reg=of.id_reg AND en.id_entrada=of.id_e_s
								$cond_of $cond_ent
								";
						} else {
							$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
								en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
								u.sigla,en.f_doc_entrada
								FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u
								WHERE en.id_lugar=u.id_lugar $cond_ent 
								";
						}
						//echo "sql: $sql<br>";
						echo tabla_entradas("",$sql,"",$txt_titulo);
						break;
					case $id_cr:
					default:
						$query_ctr="SELECT nombre FROM lugares WHERE id_lugar=$origen_id_lugar";
						$oDBRSt_q_ctr=$oDBR->query($query_ctr);
						$nombre_ctr=$oDBRSt_q_ctr->fetchColumn();
						$txt_titulo=sprintf(_("escritos de %s recibidos en la Delegación"),$nombre_ctr);
						if (!empty($oficina)) {
							$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
								en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
								u.sigla, en.f_doc_entrada
								FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u, oficinas of
								WHERE en.id_lugar='$origen_id_lugar' AND en.id_lugar=u.id_lugar
								AND en.id_reg=of.id_reg AND en.id_entrada=of.id_e_s
								$cond_of $cond_ent
								";
						} else {
							$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
								en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
								u.sigla,en.f_doc_entrada
								FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u
								WHERE en.id_lugar=u.id_lugar AND en.id_lugar='$origen_id_lugar' $cond_ent 
								";
						}
						//echo "sql: $sql<br>";
						echo tabla_entradas("",$sql,"",$txt_titulo);
				}
			break;
			case $id_cr:
				$txt_titulo=_("escritos enviados a cr");
				if (!empty($oficina)) {
					$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
						ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
						FROM escritos es LEFT JOIN destinos dest USING (id_reg), aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), oficinas of, x_modo_envio x
						WHERE es.id_reg=ap.id_reg AND dest.id_lugar=$dest_id_lugar
							AND ap.id_reg=of.id_reg AND ap.id_salida=of.id_e_s AND x.id_modo_envio=ap.id_modo_envio
							$cond_ap $cond_of
						GROUP BY es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
							ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
						";
				} else {
					$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
						ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
						FROM escritos es LEFT JOIN destinos dest USING (id_reg), aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), x_modo_envio x
						WHERE es.id_reg=ap.id_reg AND dest.id_lugar=$dest_id_lugar AND x.id_modo_envio=ap.id_modo_envio
							$cond_ap
						GROUP BY es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
							ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
						";
				}
				echo tabla_salidas("",$sql_sal,"",$txt_titulo);
			break;
			case "":
				switch ($origen_id_lugar) {
					case $id_dl:
						$txt_titulo="";
						if (!empty($oficina)) {
							$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
								ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
								FROM escritos es LEFT JOIN destinos dest USING (id_reg), aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), oficinas of, x_modo_envio x
								WHERE es.id_reg=ap.id_reg AND x.id_modo_envio=ap.id_modo_envio AND distribucion_cr='f'
										AND ap.id_reg=of.id_reg AND ap.id_salida=of.id_e_s 
									$cond_ap $cond_of
								GROUP BY es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
											ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
								";
						} else {
							$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
								ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
								FROM escritos es LEFT JOIN destinos dest USING (id_reg), aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), x_modo_envio x
								WHERE es.id_reg=ap.id_reg AND x.id_modo_envio=ap.id_modo_envio AND distribucion_cr='f'
									$cond_ap
								GROUP BY es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
											ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
								";
						}
						echo tabla_salidas("",$sql_sal,"");
						break;
					case $id_cr:
					default:
						$query_ctr="SELECT nombre FROM lugares WHERE id_lugar=$origen_id_lugar";
						$oDBRSt_q_ctr=$oDBR->query($query_ctr);
						$nombre_ctr=$oDBRSt_q_ctr->fetchColumn();
						$txt_titulo=sprintf(_("escritos de %s recibidos en la Delegación"),$nombre_ctr);
						if (!empty($oficina)) {
							$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
								en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
								u.sigla, en.f_doc_entrada
								FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u, oficinas of
								WHERE en.id_lugar='$origen_id_lugar' AND en.id_lugar=u.id_lugar
								AND en.id_reg=of.id_reg AND en.id_entrada=of.id_e_s
								$cond_of $cond_ent
								";
						} else {
							$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
								en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
								u.sigla,en.f_doc_entrada
								FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u
								WHERE en.id_lugar=u.id_lugar AND en.id_lugar='$origen_id_lugar' $cond_ent 
								";
						}
						//echo "sql: $sql<br>";
						echo tabla_entradas("",$sql,"",$txt_titulo);
						break;
				}
			break;
			default:
				$query_ctr="SELECT nombre FROM lugares WHERE id_lugar=$dest_id_lugar";
				$oDBRSt_q_ctr=$oDBR->query($query_ctr);
				$nombre_ctr=$oDBRSt_q_ctr->fetchColumn();
				switch ($origen_id_lugar) {
					case $id_dl:
						$txt_titulo=sprintf(_("escritos de %s enviados a %s"),ConfigGlobal::$dele,$nombre_ctr);
						$cond_distribucion=" AND distribucion_cr='f'";
						break;
					case $id_cr:
						$txt_titulo=sprintf(_("escritos de cr enviados a %s"),$nombre_ctr);
						$cond_distribucion=" AND distribucion_cr='t'";
						break;
					case "":
						$txt_titulo=sprintf(_("escritos enviados a %s"),$nombre_ctr);
						$cond_distribucion="";
						break;
				}
				if (!empty($oficina)) {
					$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
						ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
						FROM escritos es LEFT JOIN destinos dest USING (id_reg), aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), oficinas of, x_modo_envio x
						WHERE es.id_reg=ap.id_reg AND dest.id_lugar=$dest_id_lugar
							AND ap.id_reg=of.id_reg AND ap.id_salida=of.id_e_s AND x.id_modo_envio=ap.id_modo_envio
							$cond_ap $cond_of $cond_distribucion
						GROUP BY es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
							ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
						";
				} else {
					$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
						ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
						FROM escritos es LEFT JOIN destinos dest USING (id_reg), aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), x_modo_envio x
						WHERE es.id_reg=ap.id_reg AND dest.id_lugar=$dest_id_lugar AND x.id_modo_envio=ap.id_modo_envio
							$cond_ap $cond_distribucion
						GROUP BY es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
							ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
						";
				}
				echo tabla_salidas("",$sql_sal,"",$txt_titulo);
		}
	case 4:
		// --------------------- listar ---------------
		// las fechas.
		if (!empty($f_min)) {
			$cond_ent="AND f_entrada >= '$f_min'";
			$cond_ap="AND f_aprobacion >= '$f_min'";
		}
		if (!empty($f_max)) {
			$cond_ent.="AND f_entrada <= '$f_max'";
			$cond_ap.="AND f_aprobacion <= '$f_max'";
		}
		if (!empty($oficina)) { $cond_of="AND id_oficina='$oficina'"; }

		switch ($lista_origen) {
			case "dl":
				$txt_titulo="";
				if (!empty($oficina)) {
					$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
						ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
						FROM escritos es LEFT JOIN destinos dest USING (id_reg), aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), oficinas of, x_modo_envio x
						WHERE es.id_reg=ap.id_reg AND x.id_modo_envio=ap.id_modo_envio AND distribucion_cr='f'
								AND ap.id_reg=of.id_reg AND ap.id_salida=of.id_e_s 
							$cond_ap $cond_of
						GROUP BY es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
									ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
						";
				} else {
					$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
						ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
						FROM escritos es LEFT JOIN destinos dest USING (id_reg), aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), x_modo_envio x
						WHERE es.id_reg=ap.id_reg AND x.id_modo_envio=ap.id_modo_envio AND distribucion_cr='f'
							$cond_ap
						GROUP BY es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
									ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
						";
				}
				echo tabla_salidas("",$sql_sal,"");
				break;
			case "cr_dl":
				$lista_lugar=$id_cr;
			case "de":
				$query_ctr="SELECT nombre FROM lugares WHERE id_lugar=$lista_lugar";
				$oDBRSt_q_ctr=$oDBR->query($query_ctr);
				$nombre_ctr=$oDBRSt_q_ctr->fetchColumn();
				$txt_titulo=sprintf(_("escritos de %s recibidos en la Delegación"),$nombre_ctr);
				if (!empty($oficina)) {
					$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
						en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
						u.sigla, en.f_doc_entrada
						FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u, oficinas of
						WHERE en.id_lugar='$lista_lugar' AND en.id_lugar=u.id_lugar
						AND en.id_reg=of.id_reg AND en.id_entrada=of.id_e_s
						$cond_of $cond_ent
						";
				} else {
					$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
						en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
						u.sigla,en.f_doc_entrada
						FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u
						WHERE en.id_lugar=u.id_lugar AND en.id_lugar='$lista_lugar' $cond_ent 
						";
				}
				//echo "sql: $sql<br>";
				echo tabla_entradas("",$sql,"",$txt_titulo);
				break;
			break;
			case "cr_ctr":
				$txt_titulo=_("escritos de cr enviados a centros");
				$cond_distribucion=" AND distribucion_cr='t'";
				if (!empty($oficina)) {
					$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
						ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
						FROM escritos es LEFT JOIN destinos dest USING (id_reg), aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), oficinas of, x_modo_envio x
						WHERE es.id_reg=ap.id_reg
							AND ap.id_reg=of.id_reg AND ap.id_salida=of.id_e_s AND x.id_modo_envio=ap.id_modo_envio
							$cond_ap $cond_of $cond_distribucion
						GROUP BY es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
							ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
						";
				} else {
					$sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
						ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
						FROM escritos es LEFT JOIN destinos dest USING (id_reg), aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), x_modo_envio x
						WHERE es.id_reg=ap.id_reg AND x.id_modo_envio=ap.id_modo_envio
							$cond_ap $cond_distribucion
						GROUP BY es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
							ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
						";
				}
				echo tabla_salidas("",$sql_sal,"",$txt_titulo);
			break;
		}
	break;
}
//echo "op: $opcion, lugar: $lugar, w: $donde<br>";
?>
