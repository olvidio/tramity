<?php

// INICIO Cabecera global de URL de controlador *********************************
	use entradas\model\GestorEntrada;
use expedientes\model\GestorEscrito;
use lugares\model\entity\GestorLugar;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string) \filter_input(INPUT_POST, 'que');
$Qid_lugar = (integer) \filter_input(INPUT_POST, 'id_lugar');
$Qprot_num = (integer) \filter_input(INPUT_POST, 'prot_num');
$Qprot_any = (string) \filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.

$jsondata = [];
switch($Qque) {
    case 'buscar_entrada_correspondiente':
        $Qprot_any = core\any_2($Qprot_any);
        
        $aProt_origen = [ 'lugar' => $Qid_lugar,
            'num' => $Qprot_num,
            'any' => $Qprot_any,
        ];
        
        $id_entrada = '';
        $gesEntradas = new GestorEntrada();
        $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen);
        foreach ($cEntradas as $oEntrada) {
            $bypass = $oEntrada->getBypass();
            if ($bypass) continue;
            $id_entrada = $oEntrada->getId_entrada();
        }
                
        if (!empty($id_entrada)) {
            $jsondata['success'] = true;
            $jsondata['id_entrada'] = $id_entrada;
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = _("No se...");
        }
        
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        
        break;
    case 'buscar_referencia_correspondiente':
        $Qpara = (string) \filter_input(INPUT_POST, 'para');
        $Qprot_any = core\any_2($Qprot_any);
        
        // Si es de la dl busco en escritos, sino en entradas:
        $gesLugares = new GestorLugar();
        $id_sigla_local = $gesLugares->getId_sigla_local();
        if ($Qid_lugar == $id_sigla_local) {
            // Escritos
            $gesLugares = new GestorLugar();
            $aProt_local = [ 'id_lugar' => $Qid_lugar,
                'num' => $Qprot_num,
                'any' => $Qprot_any,
            ];
            $id_escrito = '';
            $gesEscritos = new GestorEscrito();
            $cEscritos = $gesEscritos->getEscritosByProtLocalDB($aProt_local);
            foreach ($cEscritos as $oEscrito) {
                $id_escrito = $oEscrito->getId_escrito(); 
                $jsondata['asunto'] = $oEscrito->getAsunto();
                $jsondata['detalle'] = $oEscrito->getDetalle();
                $jsondata['categoria'] = $oEscrito->getCategoria();
                $jsondata['visibilidad'] = $oEscrito->getVisibilidad();
                // los escritos van por cargos, las entradas por oficinas: pongo al director de la oficina:
                $id_ponente = $oEscrito->getPonente();
                $a_firmas = $oEscrito->getResto_oficinas();

                if ($Qpara == 'escrito') {
                    $jsondata['id_ponente'] = $id_ponente;
                    $jsondata['firmas'] = $a_firmas;
                }
                if ($Qpara == 'entrada') {
                    $oCargo = new Cargo($id_ponente);
                    $id_of_ponente = $oCargo->getId_oficina();
                    $jsondata['id_ponente'] = $id_of_ponente;
                    $a_oficinas = [];
                    foreach ($a_firmas as $id_cargo) {
                        $oCargo = new Cargo($id_cargo);
                        $id_oficina = $oCargo->getId_oficina();
                        $a_oficinas[] = $id_oficina;
                    }
                    $jsondata['oficinas'] = $a_oficinas;
                }
            }
        } else {
            // Entradas
            $aProt_origen = [ 'lugar' => $Qid_lugar,
                'num' => $Qprot_num,
                'any' => $Qprot_any,
            ];
            
            $id_entrada = '';
            $gesEntradas = new GestorEntrada();
            $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen);
            foreach ($cEntradas as $oEntrada) {
                $bypass = $oEntrada->getBypass();
                if ($bypass) continue;
                $id_entrada = $oEntrada->getId_entrada();
                $jsondata['asunto'] = $oEntrada->getAsunto();
                $jsondata['detalle'] = $oEntrada->getDetalle();
                $jsondata['categoria'] = $oEntrada->getCategoria();
                $jsondata['visibilidad'] = $oEntrada->getVisibilidad();
                // los escritos van por cargos, las entradas por oficinas: pongo al director de la oficina:
                //Ponente;
                $id_of_ponente = $oEntrada->getPonente();
                // oficinas
                $a_oficinas = $oEntrada->getResto_oficinas();
                
                if ($Qpara == 'entrada') {
                    $jsondata['id_ponente'] = $id_of_ponente;
                    $jsondata['oficinas'] = $a_oficinas;
                }
                if ($Qpara == 'escrito') {
                    $gesCargos = new GestorCargo();
                    //Ponente;
                    $id_ponente = $gesCargos->getDirectorOficina($id_of_ponente);
                    // oficinas
                    $a_oficinas = $oEntrada->getResto_oficinas();
                    $a_resto_cargos = [];
                    foreach ($a_oficinas as $id_oficina) {
                        $a_resto_cargos[] = $gesCargos->getDirectorOficina($id_oficina);
                    }
                    $jsondata['id_ponente'] = $id_ponente;
                    $jsondata['firmas'] = $a_resto_cargos;
                }
            }
        }
                
        if (!empty($id_entrada) || !empty($id_escrito)) {
            $jsondata['success'] = true;
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = _("No encuentro nada con esta ref.");
        }
        
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        
        break;
}