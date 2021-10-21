<?php
use core\ConfigGlobal;
use etherpad\model\Etherpad;
use expedientes\model\Escrito;
use expedientes\model\entity\Accion;
use plantillas\model\entity\Plantilla;
use web\DateTimeLocal;
// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string) \filter_input(INPUT_POST, 'que');

switch($Qque) {
    case 'copiar':
        // crear escrito
        $Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
        $Qid_plantilla = (integer) \filter_input(INPUT_POST, 'id_plantilla');
        $Qfiltro = (integer) \filter_input(INPUT_POST, 'filtro');
        // Para saber el nombre de la plantilla:
        $oPlantilla = new Plantilla($Qid_plantilla);
        $asunto = $oPlantilla->getNombre();
        
        // crear un nuevo escrito:
        $oEscrito = new Escrito();
        $oEscrito->setAccion(Escrito::ACCION_PLANTILLA);
        $oEscrito->setModo_envio(Escrito::MODO_MANUAL);
        $oEscrito->setTipo_doc(Escrito::TIPO_ETHERPAD);
        
        $oHoy = new DateTimeLocal();
        $f_escrito = $oHoy->getFromLocal();
        $id_ponente = ConfigGlobal::role_id_cargo();
        
        $oEscrito->setF_escrito($f_escrito);
        $oEscrito->setCreador($id_ponente);
        $oEscrito->setOK(Escrito::OK_NO);

        // El sunto no puede ser nulo (cojo el nombre de la plantilla)
        $oEscrito->setAsunto($asunto);
        
        if ($oEscrito->DBGuardar() === FALSE) {
            echo _("hay un error, no se ha guardado");
            echo "\n".$oEscrito->getErrorTxt();
        }

        $id_escrito = $oEscrito->getId_escrito();
        
        $oAccion = new Accion();
        $oAccion->setId_expediente($Qid_expediente);
        $oAccion->setId_escrito($id_escrito);
        $oAccion->setTipo_accion(Escrito::ACCION_PLANTILLA);
        if ($oAccion->DBGuardar() === FALSE) {
            echo _("hay un error, no se ha guardado");
            echo "\n".$oAccion->getErrorTxt();
        }
        
        
        //clone:
        $oEtherpad = new Etherpad();
        $oEtherpad->setId(Etherpad::ID_PLANTILLA,$Qid_plantilla);
        $sourceID = $oEtherpad->getPadId();
        
        $oNewEtherpad = new Etherpad();
        $oNewEtherpad->setId(Etherpad::ID_ESCRITO, $id_escrito);
        $destinationID = $oNewEtherpad->getPadID();
        
        $rta = $oEtherpad->copyPad($sourceID, $destinationID, 'true');
        
        /* con el Html, no hace bien los centrados (quizá más)
         * con el Text no coje los formatos.
        // copiar etherpad:
        $oEtherpad = new Etherpad();
        $oEtherpad->setId(Etherpad::ID_PLANTILLA,$Qid_plantilla);
        //$padID = $oEtherpad->getPadId();
        //$txtPad = $oEtherpad->getTexto($padID);
        $htmlPad = $oEtherpad->getHHtml();
        
        // canviar el id, y clonar el etherpad con el nuevo id
        $oNewEtherpad = new Etherpad();
        $oNewEtherpad->setId(Etherpad::ID_ESCRITO, $id_escrito);
        $padId = $oNewEtherpad->getPadID();
        //$oNewEtherpad->setText($txtPad);
        $oNewEtherpad->setHTML($padId,$htmlPad);
        $oNewEtherpad->getPadId(); // Aqui crea el pad y utiliza el $txtPad
        */
        
        
        $jsondata['success'] = true;
        $jsondata['id_escrito'] = $id_escrito;
        $a_cosas = [ 'id_escrito' => $id_escrito, 'id_expediente' => $Qid_expediente, 'filtro' => $Qfiltro];
        $pagina_mod = web\Hash::link('apps/expedientes/controller/escrito_form.php?'.http_build_query($a_cosas));
        $jsondata['pagina_mod'] = $pagina_mod;
        
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        
        
        
        break;
	case "guardar_escrito":
        $Qid_escrito = (integer) \filter_input(INPUT_POST, 'id_escrito');
        $Qid_plantilla = (integer) \filter_input(INPUT_POST, 'id_plantilla');
        $Qnombre = (string) \filter_input(INPUT_POST, 'nombre');
	    $Qa_lugares = (array)  \filter_input(INPUT_POST, 'lugares', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

		if (empty($Qnombre)) { echo _("debe poner un nombre"); }
		
		$oEscrito = new Escrito($Qid_escrito);
		$oEscrito->DBCarregar();
		// borrar destinos existentes
		$oEscrito->setJson_prot_destino([]);
		$oEscrito->setId_grupos();
		// poner nueva seleccion
		$oEscrito->setDestinos($Qa_lugares);
		$oEscrito->setDescripcion($Qnombre);

		if ($oEscrito->DBGuardar() === FALSE) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oEscrito->getErrorTxt();
		}
        break;
	case "eliminar":
	    $a_sel = (array)  \filter_input(INPUT_POST, 'sel', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
	    if (!empty($a_sel)) { //vengo de un checkbox
            $Qid_plantilla = (integer) strtok($a_sel[0],"#");
            $oPlantilla = new Plantilla($Qid_plantilla);
            if ($oPlantilla->DBEliminar() === FALSE) {
                echo _("hay un error, no se ha eliminado");
                echo "\n".$oPlantilla->getErrorTxt();
            }
	    }
		break;
	case "nuevo":
	case "guardar":
        $Qid_plantilla = (integer) \filter_input(INPUT_POST, 'id_plantilla');
        $Qnombre = (string) \filter_input(INPUT_POST, 'nombre');

		if (empty($Qnombre)) { echo _("debe poner un nombre"); }

		if(empty($Qid_plantilla)) {
            $oPlantilla = new Plantilla();
		} else {
            $oPlantilla = new Plantilla(array('id_plantilla' => $Qid_plantilla));
		}
        $oPlantilla->DBCarregar();
        $oPlantilla->setNombre($Qnombre);
		if ($oPlantilla->DBGuardar() === FALSE) {
			echo _("hay un error, no se ha guardado");
			echo "\n".$oPlantilla->getErrorTxt();
		}
        break;
	default:
	    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
	    exit ($err_switch);
}