<?php

use core\ConfigGlobal;
use entradas\model\GestorEntrada;
use expedientes\model\Expediente;
use expedientes\model\GestorExpediente;
use expedientes\model\entity\GestorEscritoDB;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use web\Lista;
use usuarios\model\entity\GestorOficina;

// INICIO Cabecera global de URL de controlador *********************************
require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();

$Qque = (string) \filter_input(INPUT_POST, 'que');

$Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
$Qasunto_buscar = (string) \filter_input(INPUT_POST, 'asunto_buscar');
$Qoficina_buscar = (integer) \filter_input(INPUT_POST, 'oficina_buscar');

$gesCargos = new GestorCargo();
$a_posibles_cargos = $gesCargos->getArrayCargos();
//n = 1 -> Entradas
//n = 2 -> Expedientes
//n = 3 -> Escritos-propuestas
switch ($Qque) {
    case 'quitar':
        $Qid_escrito = (integer) \filter_input(INPUT_POST, 'id_escrito');
        $Qtipo_antecedente = (string) \filter_input(INPUT_POST, 'tipo_doc');
        
        $antecedente = [ 'tipo'=> $Qtipo_antecedente, 'id' => $Qid_escrito ];
        $json_antecedente = json_encode($antecedente);
        $oExpediente = new Expediente($Qid_expediente);
        $oExpediente->DBCarregar();
        $oExpediente->delAntecedente($json_antecedente);
        $oExpediente->DBGuardar();
        
        echo $oExpediente->getHtmlAntecedentes();
        break;
    case 'adjuntar':
        $Qid_escrito = (integer) \filter_input(INPUT_POST, 'id_escrito');
        $Qtipo_antecedente = (string) \filter_input(INPUT_POST, 'tipo_doc');
        
        $antecedente = [ 'tipo'=> $Qtipo_antecedente, 'id' => $Qid_escrito ];
        $json_antecedente = json_encode($antecedente);
        $oExpediente = new Expediente($Qid_expediente);
        $oExpediente->DBCarregar();
        $oExpediente->addAntecedente($json_antecedente);
        $oExpediente->DBGuardar();
        
        echo $oExpediente->getHtmlAntecedentes();
        break;
	case 'buscar_1':
        //n = 1 -> Entradas
        $gesOficinas = new GestorOficina();
        $a_posibles_oficinas = $gesOficinas->getArrayOficinas();
	    $gesEntradas = new GestorEntrada();
	    $aWhere = [];
	    $aOperador = [];
	    if (!empty($Qoficina_buscar)) {
            $aWhere['ponente'] = $Qoficina_buscar;
	    }
	    if (!empty($Qasunto_buscar)) {
            // en este caso el operador es 'sin_acentos'
	        $aWhere['asunto_detalle'] = $Qasunto_buscar;
	    }
	    // por defecto, buscar sólo 15.
	    if (empty($Qasunto_buscar && empty($Qoficina_buscar))) {
	        $aWhere['_limit'] = 15;
	    }
	    $aWhere['_ordre'] = 'f_entrada DESC';
	    
	    $cEntradas = $gesEntradas->getEntradas($aWhere,$aOperador);
	    
	    $a_cabeceras = [ '',[ 'width' => 70, 'name' => _("fecha")],
	                       [ 'width' => 500, 'name' => _("asunto")],
	                       [ 'width' => 50, 'name' => _("ponente")]
	                   ,''];
	    $a_valores = [];
	    $a = 0;
	    foreach ($cEntradas as $oEntrada) {
	        $a++;
	        $id_entrada = $oEntrada->getId_entrada();
	        $fecha_txt = $oEntrada->getF_entrada()->getFromLocal();
	        $id_of_ponente = $oEntrada->getPonente();
	        
	        $ponente_txt = empty($a_posibles_oficinas[$id_of_ponente])? '?' : $a_posibles_oficinas[$id_of_ponente];
	        
	        $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_entrada('$id_entrada');\" >ver</span>";
	        $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_antecedente('entrada','$id_entrada','$Qid_expediente');\" >adjuntar</span>";
	        
	        $a_valores[$a][1] = $ver;
	        $a_valores[$a][2] = $fecha_txt;
	        $a_valores[$a][3] = $oEntrada->getAsuntoDetalle();
	        $a_valores[$a][4] = $ponente_txt;
	        $a_valores[$a][5] = $add;
	    }
	    
	    
	    $oLista = new Lista();
	    $oLista->setCabeceras($a_cabeceras);
	    $oLista->setDatos($a_valores);
	    echo $oLista->mostrar_tabla_html();
	    break;
	case 'buscar_2':
        //n = 2 -> Expediente
	    $gesExpediente = new GestorExpediente();
	    $aWhere = [];
	    $aOperador = [];
	    if (!empty($Qoficina_buscar)) {
            $aWhere['ponente'] = $Qoficina_buscar;
	    }
	    if (!empty($Qasunto_buscar)) {
            // en este caso el operador es 'sin_acentos'
	        $aWhere['asunto_detalle'] = $Qasunto_buscar;
	    }
	    // por defecto, buscar sólo 15.
	    if (empty($Qasunto_buscar && empty($Qoficina_buscar))) {
	        $aWhere['_limit'] = 15;
	    }
	    $aWhere['_ordre'] = 'f_aprobacion DESC';
	    
	    $cExpedientes = $gesExpediente->getExpedientes($aWhere,$aOperador);
	    
	    $a_cabeceras = [ '',[ 'width' => 70, 'name' => _("fecha")],
	                       [ 'width' => 500, 'name' => _("asunto")],
	                       [ 'width' => 50, 'name' => _("ponente")]
	                   ,''];
	    $a_valores = [];
	    $a = 0;
	    foreach ($cExpedientes as $oExpediente) {
	        $a++;
	        $id_expediente = $oExpediente->getId_expediente();
	        $fecha_txt = $oExpediente->getF_aprobacion()->getFromLocal();
	        $ponente = $oExpediente->getPonente();
	        
	        $ponente_txt = $a_posibles_cargos[$ponente];
	        
	        $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_expediente('$id_expediente');\" >ver</span>";
	        $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_antecedente('expediente','$id_expediente','$Qid_expediente');\" >adjuntar</span>";
	        
	        $a_valores[$a][1] = $ver;
	        $a_valores[$a][2] = $fecha_txt;
	        $a_valores[$a][3] = $oExpediente->getAsunto();
	        $a_valores[$a][4] = $ponente_txt;
	        $a_valores[$a][5] = $add;
	    }
	    
	    
	    $oLista = new Lista();
	    $oLista->setCabeceras($a_cabeceras);
	    $oLista->setDatos($a_valores);
	    echo $oLista->mostrar_tabla_html();
	    break;
	case 'buscar_3':
        //n = 3 -> Escrito
	    $gesEscrito = new GestorEscritoDB();
	    $aWhere = [];
	    $aOperador = [];
	    // Sólo los escritos que ya se han enviado
        $aWhere['f_salida'] = 'x';
	    $aOperador['f_salida'] = 'IS NOT NULL'; 
	    if (!empty($Qoficina_buscar)) {
            $aWhere['creador'] = $Qoficina_buscar;
	    }
	    if (!empty($Qasunto_buscar)) {
            $aWhere['asunto'] = $Qasunto_buscar;
            $aOperador['asunto'] = '~*';
	    }
	    // por defecto, buscar sólo 15.
	    if (empty($Qasunto_buscar && empty($Qoficina_buscar))) {
	        $aWhere['_limit'] = 15;
	    }
	    $aWhere['_ordre'] = 'f_aprobacion DESC';
	    
	    $cEscritos = $gesEscrito->getEscritosDB($aWhere,$aOperador);
	    
	    $a_cabeceras = [ '',[ 'width' => 70, 'name' => _("fecha")],
	                       [ 'width' => 500, 'name' => _("asunto")],
	                       [ 'width' => 50, 'name' => _("ponente")]
	                   ,''];
	    $a_valores = [];
	    $a = 0;
	    foreach ($cEscritos as $oEscrito) {
	        $a++;
	        $id_escrito = $oEscrito->getId_escrito();
	        $fecha_txt = $oEscrito->getF_aprobacion()->getFromLocal();
	        $ponente = $oEscrito->getCreador();
	        
	        $ponente_txt = $a_posibles_cargos[$ponente];
	        
	        $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito('$id_escrito');\" >ver</span>";
	        $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_antecedente('escrito','$id_escrito','$Qid_expediente');\" >adjuntar</span>";
	        
	        $a_valores[$a][1] = $ver;
	        $a_valores[$a][2] = $fecha_txt;
	        $a_valores[$a][3] = $oEscrito->getDetalle();
	        $a_valores[$a][4] = $ponente_txt;
	        $a_valores[$a][5] = $add;
	    }
	    
	    
	    $oLista = new Lista();
	    $oLista->setCabeceras($a_cabeceras);
	    $oLista->setDatos($a_valores);
	    echo $oLista->mostrar_tabla_html();
	    break;
	case 'buscar_exp':
        $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
        // Expediente de mi oficina en borrador
	    $gesExpediente = new GestorExpediente();
	    $aWhere = [];
	    $aOperador = [];
        $aWhere['estado'] = Expediente::ESTADO_BORRADOR;
        // posibles oficiales de la oficina:
        $oCargo = new Cargo(ConfigGlobal::role_id_cargo());
        $id_oficina = $oCargo->getId_oficina();
        $gesCargos = new GestorCargo();
        $a_cargos_oficina = $gesCargos->getArrayCargosOficina($id_oficina);
        $a_cargos = [];
        foreach (array_keys($a_cargos_oficina) as $id_cargo) {
            $a_cargos[] = $id_cargo;
        }
        if (!empty($a_cargos)) {
            $aWhere['ponente'] = implode(',',$a_cargos);
            $aOperador['ponente'] = 'IN';
        }
	    // por defecto, buscar sólo 15.
	    if (empty($Qasunto_buscar)) {
	        $aWhere['_limit'] = 15;
	    }
	    $aWhere['_ordre'] = 'f_aprobacion DESC';
	    
	    $cExpedientes = $gesExpediente->getExpedientes($aWhere,$aOperador);
	    
	    $a_cabeceras = [ '',[ 'width' => 70, 'name' => _("fecha")],
	                       [ 'width' => 500, 'name' => _("asunto")],
	                       [ 'width' => 50, 'name' => _("ponente")]
	                   ,''];
	    $a_valores = [];
	    $a = 0;
	    foreach ($cExpedientes as $oExpediente) {
	        $a++;
	        $id_expediente = $oExpediente->getId_expediente();
	        $fecha_txt = $oExpediente->getF_aprobacion()->getFromLocal();
	        $ponente = $oExpediente->getPonente();
	        
	        $ponente_txt = $a_posibles_cargos[$ponente];
	        
	        $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_expediente('$id_expediente');\" >"._("ver")."</span>";
	        $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_antecedente('entrada','$Qid_entrada','$id_expediente');\" >"._("adjuntar")."</span>";
	        
	        $a_valores[$a][1] = $ver;
	        $a_valores[$a][2] = $fecha_txt;
	        $a_valores[$a][3] = $oExpediente->getAsunto();
	        $a_valores[$a][4] = $ponente_txt;
	        $a_valores[$a][5] = $add;
	    }
	    
	    
	    $oLista = new Lista();
	    $oLista->setCabeceras($a_cabeceras);
	    $oLista->setDatos($a_valores);
	    echo $oLista->mostrar_tabla_html();
	    break;
}
