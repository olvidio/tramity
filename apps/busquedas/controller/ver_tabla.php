<?php
use busquedas\model\VerTabla;
use function core\any_4;
use lugares\model\entity\GestorLugar;
use busquedas\model\Buscar;
use entradas\model\GestorEntrada;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

$Qopcion = (integer) \filter_input(INPUT_POST, 'opcion');
$Qmas = (integer) \filter_input(INPUT_POST, 'mas');

$gesLugares = new GestorLugar();
$id_sigla = $gesLugares->getId_sigla();

$Qmas = '';

switch ($Qopcion) {
    case 7: // un protocolo concreto:
        $Qid_lugar = (integer) \filter_input(INPUT_POST, 'lugar');
        $Qprot_num = (integer) \filter_input(INPUT_POST, 'prot_num');
        $Qprot_any = (integer) \filter_input(INPUT_POST, 'prot_any');
        
        $Qprot_any = empty($Qprot_any)? '' : core\any_4($Qprot_any);
        
        $oBuscar = new Buscar();
        $oBuscar->setId_sigla($id_sigla);
        $oBuscar->setId_lugar($Qid_lugar);
        $oBuscar->setProt_num($Qprot_num);
        $oBuscar->setProt_any($Qprot_any);
        
        $aCollection = $oBuscar->getCollection($Qopcion, $Qmas);
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCollection($cCollection);
            echo $oTabla->mostrarTabla();
        }
        break;
    case 1:	// Listado de los últimos
        $Qantiguedad = (string) \filter_input(INPUT_POST, 'antiguedad');
        $Qorigen_id_lugar = (integer) \filter_input(INPUT_POST, 'origen_id_lugar');
        
        $oBuscar = new Buscar();
        $oBuscar->setAntiguedad($Qantiguedad);
        $oBuscar->setOrigen_id_lugar($Qorigen_id_lugar);
        
        $aCollection = $oBuscar->getCollection($Qopcion, $Qmas);
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCollection($cCollection);
            echo $oTabla->mostrarTabla();
        }
        break;
    case 2:
        // buscar en asunto, detalle, asunto oficina. + periodo + oficina
        // las fechas.
        $Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
        $Qf_max = (string) \filter_input(INPUT_POST, 'f_max');
        $Qf_min = (string) \filter_input(INPUT_POST, 'f_min');
        $Qoficina = (integer) \filter_input(INPUT_POST, 'oficina');
        
        $oBuscar = new Buscar();
        $oBuscar->setAsunto($Qasunto);
        $oBuscar->setF_max($Qf_max);
        $oBuscar->setF_min($Qf_min);
        $oBuscar->setOficina($Qoficina);
        
        $aCollection = $oBuscar->getCollection($Qopcion, $Qmas);
        
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCollection($cCollection);
            echo $oTabla->mostrarTabla();
        }
        break;
    case 3:
        // buscar en origen, destino o ambos
        $Qorigen_id_lugar = (integer) \filter_input(INPUT_POST, 'origen_id_lugar');
        $Qdest_id_lugar = (integer) \filter_input(INPUT_POST, 'dest_id_lugar_2');
        $Qf_max = (string) \filter_input(INPUT_POST, 'f_max');
        $Qf_min = (string) \filter_input(INPUT_POST, 'f_min');
        $Qoficina = (integer) \filter_input(INPUT_POST, 'oficina');
        
        $oBuscar = new Buscar();
        $oBuscar->setOrigen_id_lugar($Qorigen_id_lugar);
        $oBuscar->setDest_id_lugar($Qdest_id_lugar);
        $oBuscar->setF_max($Qf_max);
        $oBuscar->setF_min($Qf_min);
        $oBuscar->setOficina($Qoficina);
        
        $aCollection = $oBuscar->getCollection($Qopcion, $Qmas);
        
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCollection($cCollection);
            echo $oTabla->mostrarTabla();
        }
        break;
    case 4:
        $Qlista_origen = (string) \filter_input(INPUT_POST, 'lista_origen');
        $Qid_lugar = (integer) \filter_input(INPUT_POST, 'lista_lugar');
        $Qf_max = (string) \filter_input(INPUT_POST, 'f_max');
        $Qf_min = (string) \filter_input(INPUT_POST, 'f_min');
        $Qoficina = (integer) \filter_input(INPUT_POST, 'oficina');
        
        switch ($Qlista_origen) {
            case "dl":
                $opcion = 41;
                $gesLugares = new GestorLugar();
                $id_sigla = $gesLugares->getId_sigla();
                
                // son todos los que tienen protocolo local
                $oBuscar = new Buscar();
                $oBuscar->setLocal_id_lugar($id_sigla);
                $oBuscar->setF_max($Qf_max);
                $oBuscar->setF_min($Qf_min);
                $oBuscar->setOficina($Qoficina);
                
                $aCollection = $oBuscar->getCollection($opcion, $Qmas);
                foreach ($aCollection as $key => $cCollection) {
                    $oTabla = new VerTabla();
                    $oTabla->setKey($key);
                    $oTabla->setCollection($cCollection);
                    echo $oTabla->mostrarTabla();
                }
                break;
            case "de":
                $opcion = 42;
                $Qid_lugar = (integer) \filter_input(INPUT_POST, 'lista_lugar');
                $oBuscar = new Buscar();
                $oBuscar->setOrigen_id_lugar($Qid_lugar);
                $oBuscar->setF_max($Qf_max);
                $oBuscar->setF_min($Qf_min);
                $oBuscar->setOficina($Qoficina);
                
                $aCollection = $oBuscar->getCollection($opcion, $Qmas);
                foreach ($aCollection as $key => $cCollection) {
                    $oTabla = new VerTabla();
                    $oTabla->setKey($key);
                    $oTabla->setCollection($cCollection);
                    echo $oTabla->mostrarTabla();
                }
                break;
            case "cr_dl":
                $opcion = 43;
                $gesLugares = new GestorLugar();
                $id_cr = $gesLugares->getId_cr();
                
                // son todos los que tienen protocolo local
                $oBuscar = new Buscar();
                $oBuscar->setOrigen_id_lugar($id_cr);
                $oBuscar->setF_max($Qf_max);
                $oBuscar->setF_min($Qf_min);
                $oBuscar->setOficina($Qoficina);
                
                $aCollection = $oBuscar->getCollection($opcion, $Qmas);
                foreach ($aCollection as $key => $cCollection) {
                    $oTabla = new VerTabla();
                    $oTabla->setKey($key);
                    $oTabla->setCollection($cCollection);
                    echo $oTabla->mostrarTabla();
                }
                break;
            case "cr_ctr":
                $opcion = 44;
                $gesLugares = new GestorLugar();
                $id_cr = $gesLugares->getId_cr();
                
                // son todos los que tienen protocolo local
                $oBuscar = new Buscar();
                $oBuscar->setByPass(TRUE);
                $oBuscar->setOrigen_id_lugar($id_cr);
                $oBuscar->setF_max($Qf_max);
                $oBuscar->setF_min($Qf_min);
                $oBuscar->setOficina($Qoficina);
                
                $aCollection = $oBuscar->getCollection($opcion, $Qmas);
                foreach ($aCollection as $key => $cCollection) {
                    $oTabla = new VerTabla();
                    $oTabla->setKey($key);
                    $oTabla->setCollection($cCollection);
                    echo $oTabla->mostrarTabla();
                }
                break;
        }
        break;
}
