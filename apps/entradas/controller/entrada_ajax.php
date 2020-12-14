<?php
use entradas\model\GestorEntrada;
use entradas\model\entity\EntradaDocDB;
use ethercalc\model\Ethercalc;
use etherpad\model\Etherpad;
use usuarios\model\entity\GestorCargo;
use web\Lista;
use web\Protocolo;
use entradas\model\Entrada;

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
    case 'buscar':
        $Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
        $Qid_oficina = (integer) \filter_input(INPUT_POST, 'id_oficina');
        $Qid_origen = (string) \filter_input(INPUT_POST, 'id_origen');
        $Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
        $Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
        
        
        $gesEntradas = new GestorEntrada();
        $aWhere = [];
        $aOperador = [];
        
        $gesCargos = new GestorCargo();
        if (!empty($Qid_oficina)) {
            // buscar los posibles ponentes de una oficina:
            $cCargos = $gesCargos->getCargos(['id_oficina' => $Qid_oficina]);
            $a_id_cargos = [];
            foreach ($cCargos as $oCargo) {
                $a_id_cargos[] = $oCargo->getId_cargo();
            }
            $aWhere['ponente'] =  implode(',',$a_id_cargos);
            $aOperador['ponente'] = 'IN';
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
        
        $a_cabeceras = [ '', _("protocolo"), _("fecha"), _("asunto"), _("ponente"),''];
        $a_valores = [];
        $a = 0;
        $a_posibles_cargos = $gesCargos->getArrayCargos();
        $oProtOrigen = new Protocolo();
        foreach ($cEntradas as $oEntrada) {
            $a++;
            $id_entrada = $oEntrada->getId_entrada();
            $fecha_txt = $oEntrada->getF_entrada()->getFromLocal();
            $ponente = $oEntrada->getPonente();
            
            $ponente_txt = $a_posibles_cargos[$ponente];
            
            $oProtOrigen->setJson($oEntrada->getJson_prot_origen());
            
            $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_entrada('$id_entrada');\" >ver</span>";
            $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_entrada('$id_entrada','$Qid_expediente','$Qfiltro');\" >adjuntar</span>";
            
            $a_valores[$a][1] = $ver;
            $a_valores[$a][2] = $oProtOrigen->ver_txt();
            $a_valores[$a][3] = $fecha_txt;
            $a_valores[$a][4] = $oEntrada->getAsuntoDetalle();
            $a_valores[$a][5] = $ponente_txt;
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