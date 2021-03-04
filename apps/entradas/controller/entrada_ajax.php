<?php
use entradas\model\Entrada;
use entradas\model\GestorEntrada;
use entradas\model\entity\EntradaDocDB;
use entradas\model\entity\GestorEntradaBypass;
use ethercalc\model\Ethercalc;
use etherpad\model\Etherpad;
use pendientes\model\GestorPendienteEntrada;
use pendientes\model\Pendiente;
use usuarios\model\entity\GestorOficina;
use web\Lista;
use web\Protocolo;
use usuarios\model\entity\Oficina;
use usuarios\model\PermRegistro;

// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

// El delete es via POST!!!";

$Qque = (string) \filter_input(INPUT_POST, 'que');

switch ($Qque) {   
    case 'perm_ver':
        $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
        $oEntrada = new Entrada($Qid_entrada);
        $oPermiso = new PermRegistro();
        $perm = $oPermiso->permiso_detalle($oEntrada,'asunto');
        if ($perm < PermRegistro::PERM_VER) {
            $mensaje = _("No tiene permiso para ver la entrada");
        } else {
            $mensaje = '';
        }

        if (empty($mensaje)) {
            $jsondata['success'] = true;
            $jsondata['mensaje'] = 'ok';
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $mensaje;
        }

        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        break;
    case 'modificar_anular':
        $error_txt = '';
        $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
        $Qtext = (string) \filter_input(INPUT_POST, 'text');
        $Qelim_pendientes = (integer) \filter_input(INPUT_POST, 'elim_pendientes');

        $oEntrada = new Entrada($Qid_entrada);
        $oEntrada->DBCarregar();
        $oEntrada->setAnulado($Qtext);
        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt = $oEntrada->getErrorTxt();
        }
        // Mirar si hay pendientes
        if (!empty($Qelim_pendientes)) {
            $gesPendientes = new GestorPendienteEntrada();
            $cUids = $gesPendientes->getArrayUidById_entrada($Qid_entrada);
            if (!empty($cUids)) {
                $resource = 'registro';
                $cargo = 'secretaria';
                foreach ($cUids as $uid => $parent_container) {
                    $oPendiente = new Pendiente($parent_container, $resource, $cargo, $uid);
                    $oPendiente->eliminar();
                }
            }
        }
        if (empty($error_txt)) {
            $jsondata['success'] = true;
            $jsondata['mensaje'] = 'ok';
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $error_txt;
        }

        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        break;
    case 'modificar_detalle':
        $error_txt = '';
        $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
        $Qdetalle = (string) \filter_input(INPUT_POST, 'text');
        $oEntrada = new Entrada($Qid_entrada);
        $oEntrada->DBCarregar();
        $oEntrada->setDetalle($Qdetalle);
        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt = $oEntrada->getErrorTxt();
        }
        if (empty($error_txt)) {
            $jsondata['success'] = true;
            $jsondata['mensaje'] = 'ok';
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $error_txt;
        }

        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        break;
    case 'get_anular':
        $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
        $oEntrada = new Entrada($Qid_entrada);
        $anulado = $oEntrada->getAnulado();
        $mensaje = '';

        if (empty($mensaje)) {
            $jsondata['success'] = true;
            $jsondata['detalle'] = $anulado;
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $mensaje;
        }

        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        break;
    case 'get_detalle':
        $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
        $oEntrada = new Entrada($Qid_entrada);
        $oPermiso = new PermRegistro();
        $perm = $oPermiso->permiso_detalle($oEntrada,'detalle');
        if ($perm < PermRegistro::PERM_MODIFICAR) {
            $mensaje = _("No tiene permiso para modificar el detalle");
        } else {
            $detalle = $oEntrada->getDetalle();
            $mensaje = '';
        }

        if (empty($mensaje)) {
            $jsondata['success'] = true;
            $jsondata['detalle'] = $detalle;
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $mensaje;
        }

        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        break;
    case 'comprobar': //antes de eliminar
        $bypass_txt = '';
        $pendientes_txt = '';
        $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
        // Comprobar si tiene pendientes
        $gesPendientes = new GestorPendienteEntrada();
        $cUids = $gesPendientes->getArrayUidById_entrada($Qid_entrada);
        if (!empty($cUids)) {
            $c = count($cUids);
            $pendientes_txt = sprintf(_("Esta entrada tiene %s pendientes asociados."),$c);
        }
        // comprobar si tiene bypass
        $gesByPass = new GestorEntradaBypass();
        $cByPass = $gesByPass->getEntradasBypass(['id_entrada' => $Qid_entrada]);
        if (is_array($cByPass) && !empty($cByPass)) {
            $c = count($cByPass);
            $bypass_txt = sprintf(_("Esta entrada tiene %s envios a ctr."),$c);
        }
        
        $mensaje = '';
        if (!empty($bypass_txt)) {
            $mensaje .= $bypass_txt;
        }
        if (!empty($pendientes_txt)) {
            $mensaje .= empty($mensaje)? '' : "<br>";
            $mensaje .= $pendientes_txt;
        }

        $jsondata['success'] = true;
        $jsondata['mensaje'] = $mensaje;

        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        break;
    case 'eliminar':
        $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
        $error_txt = '';
        if (!empty($Qid_entrada)) {
            $oEntrada = new Entrada($Qid_entrada);
            // eliminar los pendientes
            $gesPendientes = new GestorPendienteEntrada();
            $cUids = $gesPendientes->getArrayUidById_entrada($Qid_entrada);
            if (!empty($cUids)) {
                $resource = 'registro';
                $cargo = 'secretaria';
                foreach ($cUids as $uid => $parent_container) {
                    $oPendiente = new Pendiente($parent_container, $resource, $cargo, $uid);
                    $oPendiente->eliminar();
                }
            }
            // eliminar la entrada y bypass
            if ($oEntrada->DBEliminar() === FALSE ) {
                $error_txt .= $oEntrada->getErrorTxt();
            }
        } else {
            $error_txt = _("No existe la entrada");
        }
        if (empty($error_txt)) {
            $jsondata['success'] = true;
            $jsondata['mensaje'] = 'ok';
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $error_txt;
        }

        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        break;
    case 'buscar':
        $Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
        $Qid_oficina = (integer) \filter_input(INPUT_POST, 'id_oficina');
        $Qid_origen = (string) \filter_input(INPUT_POST, 'id_origen');
        $Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
        $Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
        
        
        $gesEntradas = new GestorEntrada();
        $aWhere = [];
        $aOperador = [];
        
        if (!empty($Qid_oficina)) {
            // buscar los posibles ponentes de una oficina:
            $aWhere['ponente'] =  $Qid_oficina;
        }
        
        if (!empty($Qasunto)) {
            $aWhere['asunto'] = $Qasunto;
            $aOperador['asunto'] = '~*';
            
        }
        
        $aWhere['_ordre'] = 'f_entrada DESC';
        
        if (!empty($Qid_origen)) {
            $cEntradas = $gesEntradas->getEntradasByLugarDB($Qid_origen,$aWhere,$aOperador);
        } else {
            $cEntradas = $gesEntradas->getEntradas($aWhere,$aOperador);
        }
        
        $a_cabeceras = [ '', _("protocolo"), _("fecha"), _("asunto"), _("oficina ponente"),''];
        $a_valores = [];
        $a = 0;
        $gesOficinas = new GestorOficina();
        $a_posibles_oficinas = $gesOficinas->getArrayOficinas();
        $oProtOrigen = new Protocolo();
        foreach ($cEntradas as $oEntrada) {
            $a++;
            $id_entrada = $oEntrada->getId_entrada();
            $fecha_txt = $oEntrada->getF_entrada()->getFromLocal();
            $id_of_ponente = $oEntrada->getPonente();
            
            $of_ponente_txt = $a_posibles_oficinas[$id_of_ponente];
            
            $oProtOrigen->setJson($oEntrada->getJson_prot_origen());
            
            $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_entrada('$id_entrada');\" >ver</span>";
            $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_entrada('$id_entrada','$Qid_expediente','$Qfiltro');\" >adjuntar</span>";
            
            $a_valores[$a][1] = $ver;
            $a_valores[$a][2] = $oProtOrigen->ver_txt();
            $a_valores[$a][3] = $fecha_txt;
            $a_valores[$a][4] = $oEntrada->getAsuntoDetalle();
            $a_valores[$a][5] = $of_ponente_txt;
            $a_valores[$a][6] = $add;
        }
        
        
        $oLista = new Lista();
        $oLista->setCabeceras($a_cabeceras);
        $oLista->setDatos($a_valores);
        echo $oLista->mostrar_tabla_html();
        break;
    case 'guardar':
        $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
        $Qf_escrito = (string) \filter_input(INPUT_POST, 'f_escrito');
        $Qtipo_doc = (integer) \filter_input(INPUT_POST, 'tipo_doc');

        //$Qtipo = EntradaDocDB::TIPO_ETHERPAD;

        if (!empty($Qid_entrada)) {
            $oEntradaDocBD = new EntradaDocDB($Qid_entrada);
            $oEntradaDocBD->setF_doc($Qf_escrito);
            $oEntradaDocBD->setTipo_doc($Qtipo_doc);

            $error = FALSE;
            if ($oEntradaDocBD->DBGuardar() === FALSE) {
                $error_txt = $oEntradaDocBD->getErrorTxt();
                $error = TRUE;
            }
        } else {
            $error = TRUE;
        }

        $jsondata = [];
        if ($error === TRUE) {   
            $jsondata['error'] = true;
        } else {
            switch($Qtipo_doc) {
                case EntradaDocDB::TIPO_ETHERCALC : 
                    $oEthercalc = new Ethercalc();
                    $oEthercalc->setId(Ethercalc::ID_ENTRADA, $Qid_entrada);
                    $padID = $oEthercalc->getPadId();
                    $url = $oEthercalc->getUrl();

                    $fullUrl = "$url/$padID";

                    $jsondata['error'] = false;
                    $jsondata['url'] = $fullUrl;
                    break;
                case EntradaDocDB::TIPO_ETHERPAD : 
                    $oEtherpad = new Etherpad();
                    $oEtherpad->setId(Etherpad::ID_ENTRADA, $Qid_entrada);
                    $padID = $oEtherpad->getPadId();
                    // add user access to pad (Session)
                    //$oEtherpad->addUserPerm($id_entrada);
                    $url = $oEtherpad->getUrl();

                    $fullUrl = "$url/p/$padID?showChat=false&showLineNumbers=false";
                    
                    $jsondata['error'] = false;
                    $jsondata['url'] = $fullUrl;
                    break;
            }
        }
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        break;
}