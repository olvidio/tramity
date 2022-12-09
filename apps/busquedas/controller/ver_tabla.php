<?php

use busquedas\model\Buscar;
use busquedas\model\VerTabla;
use escritos\model\Escrito;
use lugares\domain\repositories\LugarRepository;
use web\DateTimeLocal;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

$Q_opcion = (integer)filter_input(INPUT_POST, 'opcion');
$Q_mas = (integer)filter_input(INPUT_POST, 'mas');
$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');

$LugarRepository = new LugarRepository();
$id_sigla_local = $LugarRepository->getId_sigla_local();

$filtro = empty($Q_filtro) ? 'en_buscar' : $Q_filtro;
$Q_mas = '';
$a_condicion = []; // para poner los parámetros de la búsqueda y poder actualizar la página.
$a_condicion['opcion'] = $Q_opcion;
switch ($Q_opcion) {
    case 8:
        // buscar por etiquetas
        $Q_andOr = (string)filter_input(INPUT_POST, 'andOr');
        $Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $a_etiquetas_filtered = array_filter($Q_a_etiquetas);

        $a_condicion['etiquetas'] = $a_etiquetas_filtered;
        $a_condicion['andOr'] = $Q_andOr;
        $str_condicion = http_build_query($a_condicion);

        $oBuscar = new Buscar();
        $oBuscar->setEtiquetas($a_etiquetas_filtered);
        $oBuscar->setAndOr($Q_andOr);

        $aCollection = $oBuscar->getCollection($Q_opcion, $Q_mas);

        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCondicion($str_condicion);
            $oTabla->setCollection($cCollection);
            $oTabla->setFiltro($filtro);
            $oTabla->setBotonesDefault();
            $oTabla->mostrarTabla();
        }
        break;
    case 71: // buscar en referencias (mismo formulario que 7):
        $Q_id_lugar = (integer)filter_input(INPUT_POST, 'id_lugar');
        $Q_prot_num = (integer)filter_input(INPUT_POST, 'prot_num');
        $Q_prot_any = (string)filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.

        $Q_prot_any = core\any_2($Q_prot_any);

        $a_condicion['id_lugar'] = $Q_id_lugar;
        $a_condicion['prot_num'] = $Q_prot_num;
        $a_condicion['prot_any'] = $Q_prot_any;
        $str_condicion = http_build_query($a_condicion);

        $oBuscar = new Buscar();
        $oBuscar->setRef(TRUE);
        $oBuscar->setId_sigla($id_sigla_local);
        $oBuscar->setId_lugar($Q_id_lugar);
        $oBuscar->setProt_num($Q_prot_num);
        $oBuscar->setProt_any($Q_prot_any);

        $aCollection = $oBuscar->getCollection($Q_opcion, $Q_mas);
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCondicion($str_condicion);
            $oTabla->setCollection($cCollection);
            $oTabla->setFiltro($filtro);
            $oTabla->setBotonesDefault();
            $oTabla->mostrarTabla();
        }

        break;
    case 7: // un protocolo concreto:
        $Q_id_lugar = (integer)filter_input(INPUT_POST, 'id_lugar');
        $Q_prot_num = (integer)filter_input(INPUT_POST, 'prot_num');
        $Q_prot_any = (string)filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.

        $Q_prot_any = core\any_2($Q_prot_any);

        $a_condicion['id_lugar'] = $Q_id_lugar;
        $a_condicion['prot_num'] = $Q_prot_num;
        $a_condicion['prot_any'] = $Q_prot_any;
        $str_condicion = http_build_query($a_condicion);

        $oBuscar = new Buscar();
        $oBuscar->setId_sigla($id_sigla_local);
        $oBuscar->setId_lugar($Q_id_lugar);
        $oBuscar->setProt_num($Q_prot_num);
        $oBuscar->setProt_any($Q_prot_any);

        $aCollection = $oBuscar->getCollection($Q_opcion, $Q_mas);
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCondicion($str_condicion);
            $oTabla->setCollection($cCollection);
            $oTabla->setFiltro($filtro);
            $oTabla->setBotonesDefault();
            $oTabla->mostrarTabla();
        }
        $btn_mas = "<button id=\"btn_mas\" type=\"button\" class=\"btn btn-primary\" onClick=\"fnjs_buscar_mas();\" >";
        $btn_mas .= _("Buscar otros escritos con esta referencia");
        $btn_mas .= "</button>";
        echo $btn_mas;
        break;
    case 1:    // Listado de los últimos
        $Q_antiguedad = (string)filter_input(INPUT_POST, 'antiguedad');
        $Q_origen_id_lugar = (integer)filter_input(INPUT_POST, 'origen_id_lugar');

        $a_condicion['antiguedad'] = $Q_antiguedad;
        $a_condicion['origen_id_lugar'] = $Q_origen_id_lugar;
        $str_condicion = http_build_query($a_condicion);

        $oBuscar = new Buscar();
        $oBuscar->setAntiguedad($Q_antiguedad);
        $oBuscar->setOrigen_id_lugar($Q_origen_id_lugar);
        $oBuscar->setLocal_id_lugar($id_sigla_local);

        $aCollection = $oBuscar->getCollection($Q_opcion, $Q_mas);
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCondicion($str_condicion);
            $oTabla->setCollection($cCollection);
            $oTabla->setFiltro($filtro);
            $oTabla->setBotonesDefault();
            $oTabla->mostrarTabla();
        }
        break;
    case 2:
        // buscar en asunto, detalle, + periodo + oficina
        // las fechas.
        $Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
        $Q_f_max = (string)filter_input(INPUT_POST, 'f_max');
        $Q_f_min = (string)filter_input(INPUT_POST, 'f_min');
        $Q_oficina = (integer)filter_input(INPUT_POST, 'oficina');

        $a_condicion['asunto'] = $Q_asunto;
        $a_condicion['f_max'] = $Q_f_max;
        $a_condicion['f_min'] = $Q_f_min;
        $a_condicion['oficina'] = $Q_oficina;
        $str_condicion = http_build_query($a_condicion);

        $oBuscar = new Buscar();
        $oBuscar->setAsunto($Q_asunto);
        $oBuscar->setF_max($Q_f_max);
        $oBuscar->setF_min($Q_f_min);
        $oBuscar->setOficina($Q_oficina);

        $aCollection = $oBuscar->getCollection($Q_opcion, $Q_mas);

        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCondicion($str_condicion);
            $oTabla->setCollection($cCollection);
            $oTabla->setFiltro($filtro);
            $oTabla->setBotonesDefault();
            $oTabla->mostrarTabla();
        }
        break;
    case 3:
        // buscar en destino
        $Q_dest_id_lugar = (integer)filter_input(INPUT_POST, 'dest_id_lugar_2');
        $Q_f_max = (string)filter_input(INPUT_POST, 'f_max');
        $Q_f_min = (string)filter_input(INPUT_POST, 'f_min');
        $Q_oficina = (integer)filter_input(INPUT_POST, 'oficina');

        $a_condicion['dest_id_lugar_2'] = $Q_dest_id_lugar;
        $a_condicion['f_max'] = $Q_f_max;
        $a_condicion['f_min'] = $Q_f_min;
        $a_condicion['oficina'] = $Q_oficina;
        $str_condicion = http_build_query($a_condicion);

        // para el caso de la dl, son todas las entradas
        if ($Q_dest_id_lugar == $id_sigla_local) {
            $oBuscar = new Buscar();
            $oBuscar->setId_sigla($id_sigla_local);
            $oBuscar->setLocal_id_lugar($id_sigla_local);
            $oBuscar->setF_max($Q_f_max);
            $oBuscar->setF_min($Q_f_min);
            $oBuscar->setOficina($Q_oficina);
        } else {
            $oBuscar = new Buscar();
            $oBuscar->setDest_id_lugar($Q_dest_id_lugar);
            $oBuscar->setF_max($Q_f_max);
            $oBuscar->setF_min($Q_f_min);
            $oBuscar->setOficina($Q_oficina);
        }

        $aCollection = $oBuscar->getCollection($Q_opcion, $Q_mas);

        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCondicion($str_condicion);
            $oTabla->setCollection($cCollection);
            $oTabla->setFiltro($filtro);
            $oTabla->setBotonesDefault();
            $oTabla->mostrarTabla();
        }
        break;
    case 9:
        // buscar en origen, destino o ambos
        $Q_origen_id_lugar = (integer)filter_input(INPUT_POST, 'origen_id_lugar');
        $Q_f_max = (string)filter_input(INPUT_POST, 'f_max');
        $Q_f_min = (string)filter_input(INPUT_POST, 'f_min');
        $Q_oficina = (integer)filter_input(INPUT_POST, 'oficina');

        $a_condicion['origen_id_lugar'] = $Q_origen_id_lugar;
        $a_condicion['f_max'] = $Q_f_max;
        $a_condicion['f_min'] = $Q_f_min;
        $a_condicion['oficina'] = $Q_oficina;
        $str_condicion = http_build_query($a_condicion);

        // para el caso de la dl, son todas las entradas
        if ($Q_origen_id_lugar == $id_sigla_local) {
            // son todos los que tienen protocolo local
            $oBuscar = new Buscar();
            $oBuscar->setLocal_id_lugar($id_sigla_local);
            $oBuscar->setOrigen_id_lugar($Q_origen_id_lugar);
            $oBuscar->setF_max($Q_f_max);
            $oBuscar->setF_min($Q_f_min);
            $oBuscar->setOficina($Q_oficina);
        } else {
            $oBuscar = new Buscar();
            $oBuscar->setOrigen_id_lugar($Q_origen_id_lugar);
            $oBuscar->setF_max($Q_f_max);
            $oBuscar->setF_min($Q_f_min);
            $oBuscar->setOficina($Q_oficina);
        }

        $aCollection = $oBuscar->getCollection($Q_opcion, $Q_mas);

        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCondicion($str_condicion);
            $oTabla->setCollection($cCollection);
            $oTabla->setFiltro($filtro);
            $oTabla->setBotonesDefault();
            $oTabla->mostrarTabla();
        }
        break;
    case 4:
        $Q_lista_origen = (string)filter_input(INPUT_POST, 'lista_origen');
        $Q_lista_lugar = (integer)filter_input(INPUT_POST, 'lista_lugar');
        $Q_f_max = (string)filter_input(INPUT_POST, 'f_max');
        $Q_f_min = (string)filter_input(INPUT_POST, 'f_min');
        $Q_oficina = (integer)filter_input(INPUT_POST, 'oficina');

        $a_condicion['lista_origen'] = $Q_lista_origen;
        $a_condicion['lista_lugar'] = $Q_lista_lugar;
        $a_condicion['f_max'] = $Q_f_max;
        $a_condicion['f_min'] = $Q_f_min;
        $a_condicion['oficina'] = $Q_oficina;
        $str_condicion = http_build_query($a_condicion);

        switch ($Q_lista_origen) {
            case "dl":
                $opcion = 41;

                // son todos los que tienen protocolo local
                $oBuscar = new Buscar();
                $oBuscar->setLocal_id_lugar($id_sigla_local);
                $oBuscar->setF_max($Q_f_max);
                $oBuscar->setF_min($Q_f_min);
                $oBuscar->setOficina($Q_oficina);

                $aCollection = $oBuscar->getCollection($opcion, $Q_mas);
                foreach ($aCollection as $key => $cCollection) {
                    $oTabla = new VerTabla();
                    $oTabla->setKey($key);
                    $oTabla->setCondicion($str_condicion);
                    $oTabla->setCollection($cCollection);
                    $oTabla->setFiltro($filtro);
                    $oTabla->setBotonesDefault();
                    $oTabla->mostrarTabla();
                }
                break;
            case "de":
                $opcion = 42;
                $Q_lista_lugar = (integer)filter_input(INPUT_POST, 'lista_lugar');

                $a_condicion['lista_lugar'] = $Q_lista_lugar;
                $str_condicion = http_build_query($a_condicion);

                $oBuscar = new Buscar();
                $oBuscar->setOrigen_id_lugar($Q_lista_lugar);
                $oBuscar->setF_max($Q_f_max);
                $oBuscar->setF_min($Q_f_min);
                $oBuscar->setOficina($Q_oficina);

                $aCollection = $oBuscar->getCollection($opcion, $Q_mas);
                foreach ($aCollection as $key => $cCollection) {
                    $oTabla = new VerTabla();
                    $oTabla->setKey($key);
                    $oTabla->setCondicion($str_condicion);
                    $oTabla->setCollection($cCollection);
                    $oTabla->setFiltro($filtro);
                    $oTabla->setBotonesDefault();
                    $oTabla->mostrarTabla();
                }
                break;
            case "cr_dl":
                $opcion = 43;
                $LugarRepository = new LugarRepository();
                $id_cr = $LugarRepository->getId_cr();

                // son todos los que tienen protocolo local
                $oBuscar = new Buscar();
                $oBuscar->setOrigen_id_lugar($id_cr);
                $oBuscar->setF_max($Q_f_max);
                $oBuscar->setF_min($Q_f_min);
                $oBuscar->setOficina($Q_oficina);

                $aCollection = $oBuscar->getCollection($opcion, $Q_mas);
                foreach ($aCollection as $key => $cCollection) {
                    $oTabla = new VerTabla();
                    $oTabla->setKey($key);
                    $oTabla->setCondicion($str_condicion);
                    $oTabla->setCollection($cCollection);
                    $oTabla->setFiltro($filtro);
                    $oTabla->setBotonesDefault();
                    $oTabla->mostrarTabla();
                }
                break;
            case "cr_ctr":
                $opcion = 44;
                $LugarRepository = new LugarRepository();
                $id_cr = $LugarRepository->getId_cr();

                // son todos los que tienen protocolo local
                $oBuscar = new Buscar();
                $oBuscar->setByPass(TRUE);
                $oBuscar->setOrigen_id_lugar($id_cr);
                $oBuscar->setF_max($Q_f_max);
                $oBuscar->setF_min($Q_f_min);
                $oBuscar->setOficina($Q_oficina);

                $aCollection = $oBuscar->getCollection($opcion, $Q_mas);
                foreach ($aCollection as $key => $cCollection) {
                    $oTabla = new VerTabla();
                    $oTabla->setKey($key);
                    $oTabla->setCondicion($str_condicion);
                    $oTabla->setCollection($cCollection);
                    $oTabla->setFiltro($filtro);
                    $oTabla->setBotonesDefault();
                    $oTabla->mostrarTabla();
                }
                break;
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }
        break;
    case 6:
        // buscar escrito con: acción = plantilla.
        // buscar en asunto, detalle, + periodo + oficina
        // las fechas.
        $Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
        $Q_f_max = (string)filter_input(INPUT_POST, 'f_max');
        $Q_f_min = (string)filter_input(INPUT_POST, 'f_min');
        $Q_oficina = (integer)filter_input(INPUT_POST, 'oficina');

        $a_condicion['asunto'] = $Q_asunto;
        $a_condicion['f_max'] = $Q_f_max;
        $a_condicion['f_min'] = $Q_f_min;
        $a_condicion['oficina'] = $Q_oficina;
        $str_condicion = http_build_query($a_condicion);

        $oBuscar = new Buscar();
        $oBuscar->setAccion(Escrito::ACCION_PLANTILLA);
        $oBuscar->setAsunto($Q_asunto);
        $oBuscar->setF_max($Q_f_max);
        $oBuscar->setF_min($Q_f_min);
        $oBuscar->setOficina($Q_oficina);

        $aCollection = $oBuscar->getCollection($Q_opcion, $Q_mas);

        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCondicion($str_condicion);
            $oTabla->setCollection($cCollection);
            $oTabla->setFiltro($filtro);
            $oTabla->setBotonesDefault();
        }
        break;
    case 51:
        //'escritos_cr':
        // recibidos los últimos 7 dias
        $oHoy = new DateTimeLocal();
        $oIni = new DateTimeLocal();
        $oIni->sub(new DateInterval('P7D'));

        $LugarRepository = new LugarRepository();
        $id_cr = $LugarRepository->getId_cr();

        $a_condicion['lista_lugar'] = $id_cr;
        $str_condicion = http_build_query($a_condicion);

        // son todos los que tienen protocolo local
        $oBuscar = new Buscar();
        $oBuscar->setOrigen_id_lugar($id_cr);
        $oBuscar->setF_max($oHoy->getIso(), FALSE);
        $oBuscar->setF_min($oIni->getIso(), FALSE);

        $aCollection = $oBuscar->getCollection(5);
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCondicion($str_condicion);
            $oTabla->setCollection($cCollection);
            $oTabla->setFiltro($filtro);
            $oTabla->setBotonesDefault();
            $oTabla->mostrarTabla();
        }
        break;
    case 52:
        //'entradas_semana':
        // recibidos los últimos 15 dias
        $oHoy = new DateTimeLocal();
        $oIni = new DateTimeLocal();
        $dias = $_SESSION['oConfig']->getPeriodoEntradas();
        $oIni->sub(new DateInterval("P${dias}D"));

        // no filtro por origen: todos (dl y cr)
        $oBuscar = new Buscar();
        $oBuscar->setF_max($oHoy->getIso(), FALSE);
        $oBuscar->setF_min($oIni->getIso(), FALSE);

        $aCollection = $oBuscar->getCollection(5);
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCollection($cCollection);
            $oTabla->setFiltro($filtro);
            $oTabla->setBotonesDefault();
            $oTabla->mostrarTabla();
        }
        break;
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}
