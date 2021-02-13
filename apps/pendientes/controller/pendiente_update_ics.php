<?php
use function core\montar_rrule;
use lugares\model\entity\Lugar;
use pendientes\model\Pendiente;
use pendientes\model\entity\PendienteDB;
use usuarios\model\entity\GestorOficina;

/**
* Esta página actualiza la base de datos del registro.
*
* Se le puede pasar la varaible $nueva.
*	Si es 1 >> inserta una nueva entrada.
*	Si es 2 >> modifica un pendiente.
*	Si es 3 >> elimina un pendiente: lo marca como cancelado.
*	Si es 4 >> marca como contestado un pendiente.
* 
*
*@package	delegacion
*@subpackage	registro
*@author	Daniel Serrabou
*@since		20/5/03.
*		
*/
// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************
// FIN de  Cabecera global de URL de controlador ********************************

// ----------------------------------------------------------------------------------------------
/* Resetear valores iniciales */

$Qgo            = (string) \filter_input(INPUT_POST, 'go');
$Qcalendario    = (string) \filter_input(INPUT_POST, 'calendario');
$Qnuevo         = (string) \filter_input(INPUT_POST, 'nuevo');
$Quid           = (string) \filter_input(INPUT_POST, 'uid');
$Qid_reg        = (string) \filter_input(INPUT_POST, 'id_reg');
$Qstatus        = (string) \filter_input(INPUT_POST, 'status');
$Qf_inicio      = (string) \filter_input(INPUT_POST, 'f_inicio');
$Qf_acabado     = (string) \filter_input(INPUT_POST, 'f_acabado');
$Qf_plazo       = (string) \filter_input(INPUT_POST, 'f_plazo');
$Qcal_oficina   = (string) \filter_input(INPUT_POST, 'cal_oficina');
$Qid_oficina    = (string) \filter_input(INPUT_POST, 'id_oficina');
if (empty($Qcal_oficina) && !empty($Qid_oficina)) { // si soy secretaria puede ser que haya definido la oficina posteriormente
    $gesOficinas = new GestorOficina();
    $a_posibles_oficinas = $gesOficinas->getArrayOficinas();
    $sigla = $a_posibles_oficinas[$Qid_oficina];
    $Qcal_oficina="oficina_$sigla";	
}

$Qref_id_lugar  = (string) \filter_input(INPUT_POST, 'ref_id_lugar');
$Qref_prot_num  = (string) \filter_input(INPUT_POST, 'ref_prot_num');
$Qref_prot_any  = (string) \filter_input(INPUT_POST, 'ref_prot_any');
$Qref_prot_mas  = (string) \filter_input(INPUT_POST, 'ref_prot_mas');

$Qobserv        = (string) \filter_input(INPUT_POST, 'observ');
$Qvisibilidad     = (string) \filter_input(INPUT_POST, 'visibilidad');
$Qdetalle       = (string) \filter_input(INPUT_POST, 'detalle');
$Qencargado     = (string) \filter_input(INPUT_POST, 'encargado');
$Qpendiente_con = (string) \filter_input(INPUT_POST, 'pendiente_con');
$Qexdates       = (string) \filter_input(INPUT_POST, 'exdates');
$Qasunto        = (string) \filter_input(INPUT_POST, 'asunto');
$rrule='';

$Qa_etiquetas = (array)  \filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_oficinas = (array)  \filter_input(INPUT_POST, 'oficinas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);


/* Para mantener las comillas en el asunto */
$trans = get_html_translation_table(HTML_ENTITIES);
$trans = array_flip($trans);
$asunto = strtr($Qasunto, $trans);

// meter el protocolo en el campo LOCATION (text)
if (!empty($Qref_id_lugar)) {
    $oLugar = new Lugar($Qref_id_lugar);
    $location = $oLugar->getSigla();
    $location .= empty($Qref_prot_num)? '' : ' '.$Qref_prot_num;
    $location .= empty($Qref_prot_any)? '' : '/'.$Qref_prot_any;
    $location .= empty($Qref_prot_mas)? '' : ' '.$Qref_prot_mas;
} else {
    $location = '';
}


$cargo = $_SESSION['session_auth']['role_actual'];
$txt_err = '';

if (!empty($_POST['simple_per'])) { // sólo para los periodicos.
	if (!empty($_POST['f_until'])) { $request['until']=$_POST['f_until']; }
	switch ($_POST['periodico_tipo']) {
		case "periodico_d_a":
			$request['tipo']="d_a";
			$request['tipo_dia']=$_POST['tipo_dia'];
			switch($_POST['tipo_dia']){
				case "num":
					$request['dias']=$_POST['a_dia_num'];
					$request['meses']=$_POST['mes_num'];
				break;
				case "ref":
					$request['ordinal']=$_POST['ordinal_a'];
					$request['dia_semana']=$_POST['dia_semana_a'];
					$request['meses']=$_POST['mes_num_ref'];
				break;
				case "num_dm":
					$request['dias']=$_POST['dias'];
					$request['meses']=$_POST['meses'];
				break;
			}
			$rrule=montar_rrule($request);
			break;
		case "periodico_d_m":
			$request['tipo']="d_m";
			$request['tipo_dia']=$_POST['tipo_dia'];
			switch($_POST['tipo_dia']){
				case "num":
					$request['dias']=$_POST['dia_num'];
				break;
				case "ref":
					$request['ordinal']=$_POST['ordinal'];
					$request['dia_semana']=$_POST['dia_semana'];
				break;
			}
			$rrule=montar_rrule($request);
			break;
		case "periodico_d_s":
			$request['tipo']="d_s";
			$request['tipo_dia']=$_POST['tipo_dia'];
			$request['dias']=$_POST['dias_w'];
			//print_r($_POST['dias_w']);
			$rrule=montar_rrule($request);
			break;
		case "periodico_d_d":
			$request['tipo']="d_d";
			//print_r($dias_w);
			$rrule=montar_rrule($request);
			break;
	}
}

switch ($Qnuevo) {
	case "1": //nuevo pendiente
		// si vengo de entradas, primero lo guardo en una tabla temporal hasta que sepa el id_reg
		if ($Qgo=="entradas") { 
			if (empty($Qf_plazo)) $Qf_plazo=$Qf_inicio; // En el caso de periodico, no tengo fecha plazo.
			    
			$oPendienteDB = new PendienteDB();
			$oPendienteDB->setAsunto($asunto);
			$oPendienteDB->setStatus($Qstatus);
			$oPendienteDB->setF_inicio($Qf_inicio);
			$oPendienteDB->setF_acabado($Qf_acabado);
			$oPendienteDB->setF_plazo($Qf_plazo);
			$oPendienteDB->setRef_mas($Qref_prot_mas);
			$oPendienteDB->setObserv($Qobserv);
			$oPendienteDB->setvisibilidad($Qvisibilidad);
			$oPendienteDB->setDetalle($Qdetalle);
			$oPendienteDB->setEncargado($Qencargado);
			$oPendienteDB->setPendiente_con($Qpendiente_con);
			$oPendienteDB->setId_oficina($Qid_oficina);
			$oPendienteDB->setRrule($rrule);
            // las oficinas implicadas:
            $oPendienteDB->setOficinasArray($Qa_oficinas);
            // las etiquetas:
            $oPendienteDB->setEtiquetasArray($Qa_etiquetas);
			$oPendienteDB->DBGuardar();
			
			$id_pendiente = $oPendienteDB->getId_pendiente();
			
			if (strlen($id_pendiente) > 10 ) {
				$txt_err .= "ERROR: $id_pendiente \n";
			} else {
			    
			    
                $jsondata['id_pendiente'] = $id_pendiente;
                $jsondata['f_plazo'] = $Qf_plazo;
			    
                if (empty($txt_err)) {
                    $jsondata['success'] = true;
                    $jsondata['mensaje'] = 'ok';
                } else {
                    $jsondata['success'] = false;
                    $jsondata['mensaje'] = $txt_err;
                }

                //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
                header('Content-type: application/json; charset=utf-8');
                echo json_encode($jsondata);
                exit();
			}
		} else {
            $oPendiente = new Pendiente($Qcal_oficina, $Qcalendario, $cargo, $Quid);
            $oPendiente->setId_reg($Qid_reg);
            $oPendiente->setAsunto($asunto);
            $oPendiente->setStatus($Qstatus);
            $oPendiente->setF_inicio($Qf_inicio);
            $oPendiente->setF_acabado($Qf_acabado);
            $oPendiente->setF_plazo($Qf_plazo);
            $oPendiente->setRef_prot_mas($Qref_prot_mas);
            $oPendiente->setObserv($Qobserv);
            $oPendiente->setvisibilidad($Qvisibilidad);
            $oPendiente->setDetalle($Qdetalle);
            $oPendiente->setEncargado($Qencargado);
            $oPendiente->setPendiente_con($Qpendiente_con);
            $oPendiente->setRrule($rrule);
            $oPendiente->setExdates($Qexdates);
            $oPendiente->setLocation($location);
            $oPendiente->setId_oficina($Qid_oficina);
            // las oficinas implicadas:
            $oPendiente->setOficinasArray($Qa_oficinas);
            // las etiquetas:
            $oPendiente->setEtiquetasArray($Qa_etiquetas);
            if ($oPendiente->Guardar() === FALSE ) {
                $txt_err .= _("No se han podido guardar el nuevo pendiente");
            }
		}
		//$go='lista';
		break;
	case "2": //modificar pendiente
		// 1º actualizo el escrito 
        $oPendiente = new Pendiente($Qcal_oficina, $Qcalendario, $cargo, $Quid);
        if (!empty($Qid_reg)) {
            $oPendiente->setId_reg($Qid_reg);
        }
        $oPendiente->setAsunto($asunto);
        $oPendiente->setStatus($Qstatus);
        $oPendiente->setF_inicio($Qf_inicio);
        $oPendiente->setF_acabado($Qf_acabado);
        $oPendiente->setF_plazo($Qf_plazo);
        $oPendiente->setRef_prot_mas($Qref_prot_mas);
        $oPendiente->setObserv($Qobserv);
        $oPendiente->setvisibilidad($Qvisibilidad);
        $oPendiente->setDetalle($Qdetalle);
        $oPendiente->setEncargado($Qencargado);
        $oPendiente->setPendiente_con($Qpendiente_con);
        $oPendiente->setRrule($rrule);
        $oPendiente->setExdates($Qexdates);
        $oPendiente->setLocation($location);
        // las oficinas implicadas:
        $oPendiente->setOficinasArray($Qa_oficinas);
        // las etiquetas:
        $oPendiente->setEtiquetasArray($Qa_etiquetas);
        if ($oPendiente->Guardar() === FALSE ) {
            $txt_err .= _("No se han podido modificar el pendiente");
        }

        /*
		// vuelvo
		if ($Qgo=="entradas" || $Qgo=="salidas" || $Qgo=="mov_iese") { 
			?>
			<!-- jQuery -->
			<script type="text/javascript" src='<?php echo ConfigGlobal::$web_scripts.'/jquery-ui-latest/js/jquery-1.7.1.min.js'; ?>'></script>
			<script>
			<?php if ($go=="entradas") { ?>
				window.opener.document.forms['form_entrada']['id_pendiente'].value=<?= $_POST['id_pendiente'] ?>;
			<?php } ?>
			window.close();
			</script>
			<?php
		} 
		if ($Qgo=="salidas") {
		    $go_to="registro_salidas.php?busca_ap_prot_num=".$_POST['busca_ap_prot_num']."&busca_ap_prot_any=${_POST['busca_ap_prot_any']}";
		}
		$go='lista';
		*/
		break;
	case "3": //eliminar pendiente. Lo pongo como cancelado.
		//vengo de un checkbox
		if (!empty($_POST['sel'])) { // puedo seleccionar más de uno.
			foreach ($_POST['sel'] as $id) {
				$uid=strtok($id,'#');
				$cal_oficina=strtok('#');
				// miro si es una recursiva de un pendiente:
				$f_recur=strtok('#');
                $oPendiente = new Pendiente($cal_oficina, $Qcalendario, $cargo, $uid);
				if (!empty($f_recur)) {
					$oPendiente->marcar_excepcion($f_recur);
				} else {
					$oPendiente->marcar_contestado("eliminar");
				}
			}
		} else {
		    echo _("No se cual tengo que eliminar.");
		}
		break;
	case "4": //marcar com contestado
		//vengo de un checkbox
		if (!empty($_POST['sel'])) { // puedo seleccionar más de uno.
			foreach ($_POST['sel'] as $id) {
				$uid=strtok($id,'#');
				$cal_oficina=strtok('#');
				// miro si es una recursiva de un pendiente:
				$f_recur=strtok('#');
                $oPendiente = new Pendiente($cal_oficina, $Qcalendario, $cargo, $uid);
				if (!empty($f_recur)) {
					$oPendiente->marcar_excepcion($f_recur);
				} else {
					$oPendiente->marcar_contestado("contestado");
				}
			}
		} else {
		    echo _("No se cual tengo que eliminar.");
		}
		break;
} // fin del switch de nuevo.	


if (empty($txt_err)) {
    $jsondata['success'] = true;
    $jsondata['mensaje'] = 'ok';
} else {
    $jsondata['success'] = false;
    $jsondata['mensaje'] = $txt_err;
}

//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata);
exit();
/*
$go_to = empty($_POST['go_to'])? '' : $_POST['go_to'];
if ($go=="lista" || $Qgo=="lista")  { $go_to="session@sel"; } 
if ($Qgo=="otro")  { $go=''; } // no se mueve del formulario.
if ($go=="") $go_to="";
if (!empty($go_to)) {
	include_once (ConfigGlobal::$dir_programas.'/func_web.php');
	//echo "go_to: $go_to<br>";
	$r=ir_a($go_to);
}
*/
