<?php
use busquedas\model\Buscar;
use busquedas\model\VerTabla;
use expedientes\model\Escrito;
use lugares\model\entity\GestorLugar;
use web\DateTimeLocal;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

$Qopcion = (integer) \filter_input(INPUT_POST, 'opcion');
$Qmas = (integer) \filter_input(INPUT_POST, 'mas');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

$gesLugares = new GestorLugar();
$id_sigla_local = $gesLugares->getId_sigla_local();

$filtro = empty($Qfiltro)? 'en_buscar' : $Qfiltro;
$Qmas = '';
$a_condicion = []; // para poner los parámetros de la búsqueda y poder actualizar la página.
$a_condicion['opcion'] = $Qopcion;
switch ($Qopcion) {
    case 71: // buscar en referencias (mismo formulario que 7):
        $Qid_lugar = (integer) \filter_input(INPUT_POST, 'id_lugar');
        $Qprot_num = (integer) \filter_input(INPUT_POST, 'prot_num');
        $Qprot_any = (string) \filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.

        $Qprot_any = core\any_2($Qprot_any);
        
        $a_condicion['id_lugar'] = $Qid_lugar;
        $a_condicion['prot_num'] = $Qprot_num;
        $a_condicion['prot_any'] = $Qprot_any;
        $str_condicion = http_build_query($a_condicion);
        
        $oBuscar = new Buscar();
        $oBuscar->setRef(TRUE);
        $oBuscar->setId_sigla($id_sigla_local);
        $oBuscar->setId_lugar($Qid_lugar);
        $oBuscar->setProt_num($Qprot_num);
        $oBuscar->setProt_any($Qprot_any);
        
        $aCollection = $oBuscar->getCollection($Qopcion, $Qmas);
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCondicion($str_condicion);
            $oTabla->setCollection($cCollection);
            $oTabla->setFiltro($filtro);
            echo $oTabla->mostrarTabla();
        }
        
        break;
    case 7: // un protocolo concreto:
        $Qid_lugar = (integer) \filter_input(INPUT_POST, 'id_lugar');
        $Qprot_num = (integer) \filter_input(INPUT_POST, 'prot_num');
        $Qprot_any = (string) \filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.

        $Qprot_any = core\any_2($Qprot_any);
        
        $a_condicion['id_lugar'] = $Qid_lugar;
        $a_condicion['prot_num'] = $Qprot_num;
        $a_condicion['prot_any'] = $Qprot_any;
        $str_condicion = http_build_query($a_condicion);
        
        $oBuscar = new Buscar();
        $oBuscar->setId_sigla($id_sigla_local);
        $oBuscar->setId_lugar($Qid_lugar);
        $oBuscar->setProt_num($Qprot_num);
        $oBuscar->setProt_any($Qprot_any);
        
        $aCollection = $oBuscar->getCollection($Qopcion, $Qmas);
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCondicion($str_condicion);
            $oTabla->setCollection($cCollection);
            $oTabla->setFiltro($filtro);
            echo $oTabla->mostrarTabla();
        }
        $btn_mas = "<button id=\"btn_mas\" type=\"button\" class=\"btn btn-primary\" onClick=\"fnjs_buscar_mas();\" >";
        $btn_mas .= _("Buscar otros escritos con esta referencia");
        $btn_mas .= "</button>";
        echo $btn_mas;
        break;
    case 1:	// Listado de los últimos
        $Qantiguedad = (string) \filter_input(INPUT_POST, 'antiguedad');
        $Qorigen_id_lugar = (integer) \filter_input(INPUT_POST, 'origen_id_lugar');

        $a_condicion['antiguedad'] = $Qantiguedad;
        $a_condicion['origen_id_lugar'] = $Qorigen_id_lugar;
        $str_condicion = http_build_query($a_condicion);
        
        $oBuscar = new Buscar();
        $oBuscar->setAntiguedad($Qantiguedad);
        $oBuscar->setOrigen_id_lugar($Qorigen_id_lugar);
        $oBuscar->setLocal_id_lugar($id_sigla_local);
        
        $aCollection = $oBuscar->getCollection($Qopcion, $Qmas);
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCondicion($str_condicion);
            $oTabla->setCollection($cCollection);
            $oTabla->setFiltro($filtro);
            echo $oTabla->mostrarTabla();
        }
        break;
    case 2:
        // buscar en asunto, detalle, + periodo + oficina
        // las fechas.
        $Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
        $Qf_max = (string) \filter_input(INPUT_POST, 'f_max');
        $Qf_min = (string) \filter_input(INPUT_POST, 'f_min');
        $Qoficina = (integer) \filter_input(INPUT_POST, 'oficina');
        
        $a_condicion['asunto'] = $Qasunto;
        $a_condicion['f_max'] = $Qf_max;
        $a_condicion['f_min'] = $Qf_min;
        $a_condicion['oficina'] = $Qoficina;
        $str_condicion = http_build_query($a_condicion);
        
        $oBuscar = new Buscar();
        $oBuscar->setAsunto($Qasunto);
        $oBuscar->setF_max($Qf_max);
        $oBuscar->setF_min($Qf_min);
        $oBuscar->setOficina($Qoficina);
        
        $aCollection = $oBuscar->getCollection($Qopcion, $Qmas);
        
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCondicion($str_condicion);
            $oTabla->setCollection($cCollection);
            $oTabla->setFiltro($filtro);
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
        
        $a_condicion['origen_id_lugar'] = $Qorigen_id_lugar;
        $a_condicion['dest_id_lugar_2'] = $Qdest_id_lugar;
        $a_condicion['f_max'] = $Qf_max;
        $a_condicion['f_min'] = $Qf_min;
        $a_condicion['oficina'] = $Qoficina;
        $str_condicion = http_build_query($a_condicion);
        
        // para el caso de la dl, son todas las entradas
        $flag = 0;
        if ($Qdest_id_lugar == $id_sigla_local) {
            $oBuscar = new Buscar();
            $oBuscar->setId_sigla($id_sigla_local);
            $oBuscar->setLocal_id_lugar($id_sigla_local);
            $oBuscar->setF_max($Qf_max);
            $oBuscar->setF_min($Qf_min);
            $oBuscar->setOficina($Qoficina);
            $flag = 1;
        }
        if ($Qorigen_id_lugar == $id_sigla_local) {
            // son todos los que tienen protocolo local
            $oBuscar = new Buscar();
            $oBuscar->setLocal_id_lugar($id_sigla_local);
            $oBuscar->setOrigen_id_lugar($Qorigen_id_lugar);
            $oBuscar->setF_max($Qf_max);
            $oBuscar->setF_min($Qf_min);
            $oBuscar->setOficina($Qoficina);
            $flag = 1;
        }
        if ($flag === 0) {
            $oBuscar = new Buscar();
            $oBuscar->setOrigen_id_lugar($Qorigen_id_lugar);
            $oBuscar->setDest_id_lugar($Qdest_id_lugar);
            $oBuscar->setF_max($Qf_max);
            $oBuscar->setF_min($Qf_min);
            $oBuscar->setOficina($Qoficina);
        }
        
        $aCollection = $oBuscar->getCollection($Qopcion, $Qmas);
        
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCondicion($str_condicion);
            $oTabla->setCollection($cCollection);
            $oTabla->setFiltro($filtro);
            echo $oTabla->mostrarTabla();
        }
        break;
    case 4:
        $Qlista_origen = (string) \filter_input(INPUT_POST, 'lista_origen');
        $Qid_lugar = (integer) \filter_input(INPUT_POST, 'lista_lugar');
        $Qf_max = (string) \filter_input(INPUT_POST, 'f_max');
        $Qf_min = (string) \filter_input(INPUT_POST, 'f_min');
        $Qoficina = (integer) \filter_input(INPUT_POST, 'oficina');
        
        $a_condicion['lista_origen'] = $Qlista_origen;
        $a_condicion['lista_lugar'] = $Qid_lugar;
        $a_condicion['f_max'] = $Qf_max;
        $a_condicion['f_min'] = $Qf_min;
        $a_condicion['oficina'] = $Qoficina;
        $str_condicion = http_build_query($a_condicion);
        
        switch ($Qlista_origen) {
            case "dl":
                $opcion = 41;
                
                // son todos los que tienen protocolo local
                $oBuscar = new Buscar();
                $oBuscar->setLocal_id_lugar($id_sigla_local);
                $oBuscar->setF_max($Qf_max);
                $oBuscar->setF_min($Qf_min);
                $oBuscar->setOficina($Qoficina);
                
                $aCollection = $oBuscar->getCollection($opcion, $Qmas);
                foreach ($aCollection as $key => $cCollection) {
                    $oTabla = new VerTabla();
                    $oTabla->setKey($key);
                    $oTabla->setCondicion($str_condicion);
                    $oTabla->setCollection($cCollection);
                    $oTabla->setFiltro($filtro);
                    echo $oTabla->mostrarTabla();
                }
                break;
            case "de":
                $opcion = 42;
                $Qid_lugar = (integer) \filter_input(INPUT_POST, 'lista_lugar');
                
                $a_condicion['lista_lugar'] = $Qid_lugar;
                $str_condicion = http_build_query($a_condicion);
                
                $oBuscar = new Buscar();
                $oBuscar->setOrigen_id_lugar($Qid_lugar);
                $oBuscar->setF_max($Qf_max);
                $oBuscar->setF_min($Qf_min);
                $oBuscar->setOficina($Qoficina);
                
                $aCollection = $oBuscar->getCollection($opcion, $Qmas);
                foreach ($aCollection as $key => $cCollection) {
                    $oTabla = new VerTabla();
                    $oTabla->setKey($key);
                    $oTabla->setCondicion($str_condicion);
                    $oTabla->setCollection($cCollection);
                    $oTabla->setFiltro($filtro);
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
                    $oTabla->setCondicion($str_condicion);
                    $oTabla->setCollection($cCollection);
                    $oTabla->setFiltro($filtro);
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
                    $oTabla->setCondicion($str_condicion);
                    $oTabla->setCollection($cCollection);
                    $oTabla->setFiltro($filtro);
                    echo $oTabla->mostrarTabla();
                }
                break;
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }
        break;
    case 6:
        // buscar escrito con: accion = plantilla.
        // buscar en asunto, detalle, + periodo + oficina
        // las fechas.
        $Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
        $Qf_max = (string) \filter_input(INPUT_POST, 'f_max');
        $Qf_min = (string) \filter_input(INPUT_POST, 'f_min');
        $Qoficina = (integer) \filter_input(INPUT_POST, 'oficina');
        
        $a_condicion['asunto'] = $Qasunto;
        $a_condicion['f_max'] = $Qf_max;
        $a_condicion['f_min'] = $Qf_min;
        $a_condicion['oficina'] = $Qoficina;
        $str_condicion = http_build_query($a_condicion);
        
        $oBuscar = new Buscar();
        $oBuscar->setAccion(Escrito::ACCION_PLANTILLA);
        $oBuscar->setAsunto($Qasunto);
        $oBuscar->setF_max($Qf_max);
        $oBuscar->setF_min($Qf_min);
        $oBuscar->setOficina($Qoficina);
        
        $aCollection = $oBuscar->getCollection($Qopcion, $Qmas);
        
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCondicion($str_condicion);
            $oTabla->setCollection($cCollection);
            $oTabla->setFiltro($filtro);
            echo $oTabla->mostrarTabla();
        }
        break;
    case 51:
    	//'escritos_cr':
		// recibidos los ultimos 7 dias
		$oHoy = new DateTimeLocal();
		$oIni = new DateTimeLocal();
		$oIni->sub(new \DateInterval('P7D'));

		$gesLugares = new GestorLugar();
		$id_cr = $gesLugares->getId_cr();

		$a_condicion['lista_lugar'] = $id_cr;
		$str_condicion = http_build_query($a_condicion);

		// son todos los que tienen protocolo local
		$oBuscar = new Buscar();
		$oBuscar->setOrigen_id_lugar($id_cr);
		$oBuscar->setF_max($oHoy->getIso(),FALSE);
		$oBuscar->setF_min($oIni->getIso(),FALSE);

		$aCollection = $oBuscar->getCollection(5);
		foreach ($aCollection as $key => $cCollection) {
			$oTabla = new VerTabla();
			$oTabla->setKey($key);
			$oTabla->setCondicion($str_condicion);
			$oTabla->setCollection($cCollection);
			$oTabla->setFiltro($filtro);
			echo $oTabla->mostrarTabla();
		}
		break;
    case 52:
    	//'entradas_semana':
    	// recibidos los ultimos 15 dias
    	$oHoy = new DateTimeLocal();
    	$oIni = new DateTimeLocal();
    	$oIni->sub(new \DateInterval('P15D'));
    	
    	// no filtro por origen: todos (dl y cr)
    	$oBuscar = new Buscar();
    	$oBuscar->setF_max($oHoy->getIso(),FALSE);
    	$oBuscar->setF_min($oIni->getIso(),FALSE);
    	
    	$aCollection = $oBuscar->getCollection(5);
    	foreach ($aCollection as $key => $cCollection) {
			$oTabla = new VerTabla();
			$oTabla->setKey($key);
			$oTabla->setCollection($cCollection);
			$oTabla->setFiltro($filtro);
			echo $oTabla->mostrarTabla();
    	}
    	break;
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}
