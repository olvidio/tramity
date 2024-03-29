<?php

use core\ConfigGlobal;
use davical\model\Davical;
use entradas\model\entity\EntradaBypass;
use entradas\model\entity\EntradaDB;
use entradas\model\Entrada;
use entradas\model\GestorEntrada;
use lugares\model\entity\GestorGrupo;
use pendientes\model\Pendiente;
use usuarios\model\entity\Cargo;
use usuarios\model\PermRegistro;
use web\DateTimeLocal;
use web\Protocolo;
use function core\is_true;
use function core\isTrue;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_que = (string)filter_input(INPUT_POST, 'que');
// nuevo formato: id_entrada#comparida (compartida = boolean)
//$Q_id_entrada = (integer)filter_input(INPUT_POST, 'id_entrada');
$Qid_entrada = (string)filter_input(INPUT_POST, 'id_entrada');
$a_entrada = explode('#', $Qid_entrada);
$Q_id_entrada = (int)$a_entrada[0];
$compartida = !empty($a_entrada[1]) && is_true($a_entrada[1]);

$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');

$Q_origen = (integer)filter_input(INPUT_POST, 'origen');
$Q_prot_num_origen = (integer)filter_input(INPUT_POST, 'prot_num_origen');
$Q_prot_any_origen = (integer)filter_input(INPUT_POST, 'prot_any_origen');
$Q_prot_mas_origen = (string)filter_input(INPUT_POST, 'prot_mas_origen');

$Q_asunto_e = (string)filter_input(INPUT_POST, 'asunto_e');
$Q_f_escrito = (string)filter_input(INPUT_POST, 'f_escrito');
$Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
$Q_f_entrada = (string)filter_input(INPUT_POST, 'f_entrada');

$Q_detalle = (string)filter_input(INPUT_POST, 'detalle');
$Q_id_of_ponente = (integer)filter_input(INPUT_POST, 'of_ponente');
$Qa_oficinas = (array)filter_input(INPUT_POST, 'oficinas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$Q_categoria = (integer)filter_input(INPUT_POST, 'categoria');
$Q_visibilidad = (integer)filter_input(INPUT_POST, 'visibilidad');

$Q_plazo = (string)filter_input(INPUT_POST, 'plazo');
$Q_f_plazo = (string)filter_input(INPUT_POST, 'f_plazo');
$Q_bypass = (string)filter_input(INPUT_POST, 'bypass');
$Q_admitir_hidden = (string)filter_input(INPUT_POST, 'admitir_hidden');

/* genero un vector con todas las referencias. Antes ya llegaba así, pero al quitar [] de los nombres, legan uno a uno.  */
$Qa_referencias = (array)filter_input(INPUT_POST, 'referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_prot_num_referencias = (array)filter_input(INPUT_POST, 'prot_num_referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_prot_any_referencias = (array)filter_input(INPUT_POST, 'prot_any_referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_prot_mas_referencias = (array)filter_input(INPUT_POST, 'prot_mas_referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$error_txt = '';
$jsondata = [];
switch ($Q_que) {
    case 'guardar_etiquetas':
        $Qa_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        // si viene del serializeArray del jQuery, cada fila, a su vez es un array:
        $a_etiquetas = [];
        foreach ($Qa_etiquetas as $etiqueta) {
            if (is_array($etiqueta)) {
                $a_etiquetas[] = $etiqueta['value'];
            } else {
                $a_etiquetas[] = $etiqueta;
            }
        }
        // Se ponen cuando se han enviado...
        if ($compartida) {
            $gesEntradas = new GestorEntrada();
            $cEntradas = $gesEntradas->getEntradas(['id_entrada_compartida' => $Q_id_entrada]);
            $oEntrada = $cEntradas[0];
        } else {
            $oEntrada = new Entrada($Q_id_entrada);
        }

        if ($oEntrada->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe la entrada en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        // las etiquetas:
        $oEntrada->setEtiquetas($a_etiquetas);
        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt .= _("No se han podido guardar las etiquetas");
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
    case 'en_asignar':
        $Qid_oficina = ConfigGlobal::role_id_oficina();
        $Qid_cargo_encargado = (integer)filter_input(INPUT_POST, 'id_cargo_encargado');
        if ($compartida) {
            $gesEntradas = new GestorEntrada();
            $cEntradas = $gesEntradas->getEntradas(['id_entrada_compartida' => $Q_id_entrada]);
            $oEntrada = $cEntradas[0];
        } else {
            $oEntrada = new Entrada($Q_id_entrada);
        }

        if ($oEntrada->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe la entrada en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        // comprobar si es un cambio (ya estaba encargado a alguien)
        $encargado_old = $oEntrada->getEncargado();

        // Para los ctr, hay que cambiar el estado a ESTADO_ACEPTADO
        if ($_SESSION['oConfig']->getAmbito() !== Cargo::AMBITO_DL) {
            $oEntrada->setEstado(Entrada::ESTADO_ACEPTADO);
        }
        $oEntrada->setEncargado($Qid_cargo_encargado);
        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt .= $oEntrada->getErrorTxt();
        }

        // también hay que marcarlo como visto por quien lo encarga
        // Siempre que no sea el mismo:
        if (ConfigGlobal::role_id_cargo() !== $Qid_cargo_encargado) {
            $flag_encontrado = FALSE;
            $aVisto = $oEntrada->getJson_visto(TRUE);
            foreach ($aVisto as $key => $oVisto) {
                if (($oVisto['oficina'] === $Qid_oficina) && ($oVisto['cargo'] === $Qid_cargo_encargado)) {
                    $aVisto[$key]['visto'] = TRUE;
                    $flag_encontrado = TRUE;
                }
            }
            if (!$flag_encontrado) {
                $oVisto = [];
                $oVisto['oficina'] = $Qid_oficina;
                $oVisto['cargo'] = $encargado_old;
                $oVisto['visto'] = TRUE;
                $aVisto[] = $oVisto;
            }
            $oEntrada->setJson_visto($aVisto);
            if ($oEntrada->DBGuardar() === FALSE) {
                $error_txt .= $oEntrada->getErrorTxt();
            }
        }

        // Si es un cambio: marcar visto al anterior
        if (!empty($encargado_old)) {
            $flag_encontrado = FALSE;
            $aVisto = $oEntrada->getJson_visto(TRUE);
            foreach ($aVisto as $key => $oVisto) {
                if ($oVisto['oficina'] === $Qid_oficina && $oVisto['cargo'] === $Qid_cargo_encargado) {
                    $aVisto[$key]['visto'] = TRUE;
                    $flag_encontrado = TRUE;
                }
            }
            if (!$flag_encontrado) {
                $oVisto = [];
                $oVisto['oficina'] = $Qid_oficina;
                $oVisto['cargo'] = $encargado_old;
                $oVisto['visto'] = TRUE;
                $aVisto[] = $oVisto;
            }

            $oEntrada->setJson_visto($aVisto);
            if ($oEntrada->DBGuardar() === FALSE) {
                $error_txt .= $oEntrada->getErrorTxt();
            }
        }
        // y en cualquier caso: desmarcar al nuevo (podria estar marcado previamente)
        $aVisto = $oEntrada->getJson_visto(TRUE);
        foreach ($aVisto as $key => $oVisto) {
            if ($oVisto['oficina'] === $Qid_oficina && $oVisto['cargo'] === $Qid_cargo_encargado) {
                $aVisto[$key]['visto'] = FALSE;
            }
        }

        $oEntrada->setJson_visto($aVisto);
        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt .= $oEntrada->getErrorTxt();
        }

        if (!empty($error_txt)) {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = TRUE;
        }
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
    case 'en_visto':
        $Qid_oficina = ConfigGlobal::role_id_oficina();
        $Qid_cargo = ConfigGlobal::role_id_cargo();
        if ($compartida) {
            $gesEntradas = new GestorEntrada();
            $cEntradas = $gesEntradas->getEntradas(['id_entrada_compartida' => $Q_id_entrada]);
            $oEntrada = $cEntradas[0];
        } else {
            $oEntrada = new Entrada($Q_id_entrada);
        }

        if ($oEntrada->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe la entrada en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }

        $aVisto = $oEntrada->getJson_visto(TRUE);
        $oVisto = [];
        $oVisto['oficina'] = $Qid_oficina;
        $oVisto['cargo'] = $Qid_cargo;
        $oVisto['visto'] = TRUE;
        $aVisto[] = $oVisto;

        $oEntrada->setJson_visto($aVisto);
        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt .= $oEntrada->getErrorTxt();
        }

        $oEntrada->comprobarVisto();

        if (!empty($error_txt)) {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = TRUE;
        }
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
    case 'eliminar':
        if ($compartida) {
            $gesEntradas = new GestorEntrada();
            $cEntradas = $gesEntradas->getEntradas(['id_entrada_compartida' => $Q_id_entrada]);
            $oEntrada = $cEntradas[0];
        } else {
            $oEntrada = new Entrada($Q_id_entrada);
        }

        if ($oEntrada->DBEliminar() === FALSE) {
            $error_txt .= $oEntrada->getErrorTxt();
            exit($error_txt);
        }
        break;
    case 'guardar_destinos':
        $oEntradaBypass = new EntradaBypass($Q_id_entrada);
        $oEntradaBypass->DBCargar();
        // Al cargar si no existe, también borra el id_entrada, y hay que volver a asignarlo.
        $oEntradaBypass->setId_entrada($Q_id_entrada);
        //Q_asunto.
        $oPermisoRegistro = new PermRegistro();
        $perm_asunto = $oPermisoRegistro->permiso_detalle($oEntradaBypass, 'asunto');
        if ($perm_asunto >= PermRegistro::PERM_MODIFICAR) {
            $oEntradaBypass->setAsunto($Q_asunto);
            if ($oEntradaBypass->DBGuardar() === FALSE) {
                $error_txt .= $oEntradaBypass->getErrorTxt();
            }
        }
        // destinos
        $Q_grupo_dst = (string)filter_input(INPUT_POST, 'grupo_dst');
        $Q_f_salida = (string)filter_input(INPUT_POST, 'f_salida');

        // genero un vector con todos los grupos.
        $Qa_grupos = (array)filter_input(INPUT_POST, 'grupos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        /* genero un vector con todas las referencias. Antes ya llegaba así, pero al quitar [] de los nombres, legan uno a uno.  */
        $Qa_destinos = (array)filter_input(INPUT_POST, 'destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Qa_prot_num_destinos = (array)filter_input(INPUT_POST, 'prot_num_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Qa_prot_any_destinos = (array)filter_input(INPUT_POST, 'prot_any_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Qa_prot_mas_destinos = (array)filter_input(INPUT_POST, 'prot_mas_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

        // Si esta marcado como grupo de destinos, o destinos individuales.
        if (is_true($Q_grupo_dst)) {
            $descripcion = '';
            $gesGrupo = new GestorGrupo();
            $a_grupos = $gesGrupo->getArrayGrupos();
            foreach ($Qa_grupos as $id_grupo) {
                $descripcion .= empty($descripcion) ? '' : ' + ';
                $descripcion .= $a_grupos[$id_grupo];
            }
            $oEntradaBypass->setId_grupos($Qa_grupos);
            $oEntradaBypass->setDescripcion($descripcion);
        } else {
            $aProtDst = [];
            foreach ($Qa_destinos as $key => $id_lugar) {
                $prot_num = $Qa_prot_num_destinos[$key];
                $prot_any = $Qa_prot_any_destinos[$key];
                $prot_mas = $Qa_prot_mas_destinos[$key];

                if (!empty($id_lugar)) {
                    $oProtDst = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                    $aProtDst[] = $oProtDst->getProt();
                }
            }
            $oEntradaBypass->setJson_prot_destino($aProtDst);
            $oEntradaBypass->setId_grupos();
            if (empty($oEntradaBypass->getDescripcion())) {
                $oEntradaBypass->setDescripcion('x'); // no puede ser null.
            }
        }
        $oEntradaBypass->setF_salida($Q_f_salida);
        if ($oEntradaBypass->DBGuardar() === FALSE) {
            $error_txt .= $oEntradaBypass->getErrorTxt();
        }

        if (!empty($error_txt)) {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = TRUE;
        }
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
    case 'f_entrada':
        if ($Q_f_entrada === 'hoy') {
            $oHoy = new DateTimeLocal();
            $Q_f_entrada = $oHoy->getFromLocal();
        }
        if ($compartida) {
            $gesEntradas = new GestorEntrada();
            $cEntradas = $gesEntradas->getEntradas(['id_entrada_compartida' => $Q_id_entrada]);
            $oEntrada = $cEntradas[0];
        } else {
            $oEntrada = new Entrada($Q_id_entrada);
        }

        if ($oEntrada->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe la entrada en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oEntrada->setF_entrada($Q_f_entrada);
        if (empty($Q_f_entrada)) {
            $oEntrada->setEstado(Entrada::ESTADO_INGRESADO);
        } else {
            $oEntrada->setEstado(Entrada::ESTADO_ADMITIDO);
        }
        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt = $oEntrada->getErrorTxt();
            exit($error_txt);
        }
        break;
    case 'detalle':
        if ($compartida) {
            $gesEntradas = new GestorEntrada();
            $cEntradas = $gesEntradas->getEntradas(['id_entrada_compartida' => $Q_id_entrada]);
            $oEntrada = $cEntradas[0];
        } else {
            $oEntrada = new Entrada($Q_id_entrada);
        }

        if ($oEntrada->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe la entrada en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oEntrada->setDetalle($Q_detalle);
        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt = $oEntrada->getErrorTxt();
            exit($error_txt);
        }
        break;
    case 'guardar_ctr':
        if ($compartida) {
            $gesEntradas = new GestorEntrada();
            $cEntradas = $gesEntradas->getEntradas(['id_entrada_compartida' => $Q_id_entrada]);
            $oEntrada = $cEntradas[0];
        } else {
            $oEntrada = new Entrada($Q_id_entrada);
        }

        if ($oEntrada->DBCargar() === FALSE) {
            $err_cargar = sprintf(_("OJO! no existe la entrada en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_cargar);
        }
        $oEntrada->setAsunto($Q_asunto);
        $oEntrada->setDetalle($Q_detalle);
        $oEntrada->setCategoria($Q_categoria);
        $oEntrada->setVisibilidad($Q_visibilidad);
        switch ($Q_plazo) {
            case 'hoy':
                $oEntrada->setF_contestar('');
                break;
            case 'normal':
                $plazo_normal = $_SESSION['oConfig']->getPlazoNormal();
                $periodo = 'P' . $plazo_normal . 'D';
                $oF = new DateTimeLocal();
                $oF->add(new DateInterval($periodo));
                $oEntrada->setF_contestar($oF);
                break;
            case 'rápido':
                $plazo_rapido = $_SESSION['oConfig']->getPlazoRapido();
                $periodo = 'P' . $plazo_rapido . 'D';
                $oF = new DateTimeLocal();
                $oF->add(new DateInterval($periodo));
                $oEntrada->setF_contestar($oF);
                break;
            case 'urgente':
                $plazo_urgente = $_SESSION['oConfig']->getPlazoUrgente();
                $periodo = 'P' . $plazo_urgente . 'D';
                $oF = new DateTimeLocal();
                $oF->add(new DateInterval($periodo));
                $oEntrada->setF_contestar($oF);
                break;
            case 'fecha':
                $oEntrada->setF_contestar($Q_f_plazo);
                break;
            default:
                // Si no hay $Q_plazo, No pongo ninguna fecha a contestar
        }
        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt = $oEntrada->getErrorTxt();
            exit($error_txt);
        }
        if (!empty($error_txt)) {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = TRUE;
            $jsondata['id_entrada'] = $Q_id_entrada;
            $a_cosas = ['id_entrada' => $Q_id_entrada, 'filtro' => $Q_filtro];
            $pagina_mod = web\Hash::link('apps/entradas/controller/entrada_form.php?' . http_build_query($a_cosas));
            $jsondata['pagina_mod'] = $pagina_mod;
        }
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
    case 'guardar':
        if (!empty($Q_id_entrada)) {
            if ($compartida) {
                $gesEntradas = new GestorEntrada();
                $cEntradas = $gesEntradas->getEntradas(['id_entrada_compartida' => $Q_id_entrada]);
                $oEntrada = $cEntradas[0];
            } else {
                $oEntrada = new Entrada($Q_id_entrada);
            }

            if ($oEntrada->DBCargar() === FALSE) {
                $err_cargar = sprintf(_("OJO! no existe la entrada en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_cargar);
            }
            $oPermisoRegistro = new PermRegistro();
            $perm_asunto = $oPermisoRegistro->permiso_detalle($oEntrada, 'asunto');
            $perm_detalle = $oPermisoRegistro->permiso_detalle($oEntrada, 'detalle');
            if ($oEntrada->getModo_entrada() === Entrada::MODO_PROVISIONAL) {
                // borro el fichero pdf provisional, si existe
                $filename_pdf = ConfigGlobal::getDIR().'/log/entradas/entrada_' . $Q_id_entrada . '.pdf';
                if (file_exists($filename_pdf)) {
                    unlink($filename_pdf);
                }
            }
        } else {
            $oEntrada = new Entrada();
            $perm_asunto = PermRegistro::PERM_MODIFICAR;
            $perm_detalle = PermRegistro::PERM_MODIFICAR;
        }

        $oEntrada->setModo_entrada(Entrada::MODO_MANUAL);

        $oProtOrigen = new Protocolo($Q_origen, $Q_prot_num_origen, $Q_prot_any_origen, $Q_prot_mas_origen);
        $oEntrada->setJson_prot_origen($oProtOrigen->getProt());

        $aProtRef = [];
        foreach ($Qa_referencias as $key => $id_lugar) {
            $prot_num = $Qa_prot_num_referencias[$key];
            $prot_any = $Qa_prot_any_referencias[$key];
            $prot_mas = $Qa_prot_mas_referencias[$key];

            if (!empty($id_lugar)) {
                $oProtRef = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                $aProtRef[] = $oProtRef->getProt();
            }
        }
        $oEntrada->setJson_prot_ref($aProtRef);

        $oEntrada->setAsunto_entrada($Q_asunto_e);
        $oEntrada->setF_documento($Q_f_escrito, TRUE);
        $oEntrada->setF_entrada($Q_f_entrada);
        if ($perm_asunto >= PermRegistro::PERM_MODIFICAR) {
            $oEntrada->setAsunto($Q_asunto);
        }
        if ($perm_detalle >= PermRegistro::PERM_MODIFICAR) {
            $oEntrada->setDetalle($Q_detalle);
        }
        $oEntrada->setPonente($Q_id_of_ponente);
        $oEntrada->setResto_oficinas($Qa_oficinas);

        $oEntrada->setCategoria($Q_categoria);
        // visibilidad: puede que esté en modo solo lectura, mirar el hidden.
        if (empty($Q_visibilidad)) {
            $Q_visibilidad = (integer)filter_input(INPUT_POST, 'hidden_visibilidad');
        }
        $oEntrada->setVisibilidad($Q_visibilidad);


        // 5º Compruebo si hay que generar un pendiente
        switch ($Q_plazo) {
            case 'hoy':
                $oEntrada->setF_contestar('');
                break;
            case 'normal':
                $plazo_normal = $_SESSION['oConfig']->getPlazoNormal();
                $periodo = 'P' . $plazo_normal . 'D';
                $oF = new DateTimeLocal();
                $oF->add(new DateInterval($periodo));
                $oEntrada->setF_contestar($oF);
                break;
            case 'rápido':
                $plazo_rapido = $_SESSION['oConfig']->getPlazoRapido();
                $periodo = 'P' . $plazo_rapido . 'D';
                $oF = new DateTimeLocal();
                $oF->add(new DateInterval($periodo));
                $oEntrada->setF_contestar($oF);
                break;
            case 'urgente':
                $plazo_urgente = $_SESSION['oConfig']->getPlazoUrgente();
                $periodo = 'P' . $plazo_urgente . 'D';
                $oF = new DateTimeLocal();
                $oF->add(new DateInterval($periodo));
                $oEntrada->setF_contestar($oF);
                break;
            case 'fecha':
                $oEntrada->setF_contestar($Q_f_plazo);
                break;
            default:
                // Si no hay $Q_plazo, No pongo ninguna fecha a contestar
        }

        if (is_true($Q_admitir_hidden)) {
            // pasa directamente a asignado. Se supone que el admitido lo ha puesto el vcd.
            // en caso de ponerlo secretaria, al guardar pasa igualmente a asignado.
            $estado = Entrada::ESTADO_ASIGNADO;
        } else {
            $estado = Entrada::ESTADO_INGRESADO;
        }
        // si es el scdl, puede ser que pase a aceptado:
        if ($Q_filtro === 'en_asignado' || $Q_filtro === 'en_buscar') {
            $estado = Entrada::ESTADO_ACEPTADO;
        }

        if ($Q_filtro === 'en_buscar') { // NO tocar el estado
            $estado = $oEntrada->getEstado();
            $id_entrada = $Q_id_entrada;
        }
        $oEntrada->setEstado($estado);

        $oEntrada->setBypass(isTrue($Q_bypass));
        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt .= $oEntrada->getErrorTxt();
        }

        if (empty($error_txt) && $Q_filtro !== 'en_buscar') {
            $id_entrada = $oEntrada->getId_entrada();
            //////// Generar un Pendiente (hay que esperar e tener el id_entrada //////
            // Solo se genera en cuando el scdl lo acepta (filtro=en_asignado). Si no se mira esta condición
            // se van generando pendientes cada vez que se guarda.
            if ($Q_filtro === 'en_asignado') {
                if ($Q_plazo !== 'hoy') {
                    $Qid_pendiente = (integer)filter_input(INPUT_POST, 'id_pendiente');
                    if (empty($Qid_pendiente)) { // si no se ha generado el pendiente con "modificar pendiente"
                        $f_plazo = $oEntrada->getF_contestar()->getFromLocal();
                        $location = $oProtOrigen->ver_txt_num();
                        $prot_mas = $oProtOrigen->ver_txt_mas();

                        $id_reg = 'REN' . $id_entrada; // REN = Registro Entrada
                        $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
                        $parent_container = $oDavical->getNombreRecursoPorIdOficina($Q_id_of_ponente);
                        $calendario = 'registro';
                        $user_davical = $oDavical->getUsernameDavicalSecretaria();
                        $uid = '';
                        $oPendiente = new Pendiente($parent_container, $calendario, $user_davical, $uid);
                        $oPendiente->setId_reg($id_reg);
                        $oPendiente->setAsunto($Q_asunto);
                        $oPendiente->setStatus("NEEDS-ACTION");
                        $oPendiente->setF_inicio($Q_f_entrada);
                        $oPendiente->setF_plazo($f_plazo);
                        $oPendiente->setVisibilidad($Q_visibilidad);
                        $oPendiente->setDetalle($Q_detalle);
                        $oPendiente->setPendiente_con($Q_origen);
                        $oPendiente->setLocation($location);
                        $oPendiente->setRef_prot_mas($prot_mas);
                        $oPendiente->setId_oficina($Q_id_of_ponente);
                        // las firmas son cargos, buscar las oficinas implicadas:
                        $oPendiente->setOficinasArray($Qa_oficinas);
                        if ($oPendiente->Guardar() === FALSE) {
                            $error_txt .= _("No se han podido guardar el nuevo pendiente");
                        }
                    } else {
                        // meter el pendienteDB en davical con el id_reg que toque y borrarlo de pendienteDB.
                        $id_reg = 'REN' . $id_entrada; // REN = Registro Entrada
                        $oDavical = new Davical($_SESSION['oConfig']->getAmbito());
                        $parent_container = $oDavical->getNombreRecursoPorIdOficina($Q_id_of_ponente);
                        $calendario = 'registro';
                        $user_davical = $oDavical->getUsernameDavicalSecretaria();
                        $uid = '';
                        $oPendiente = new Pendiente($parent_container, $calendario, $user_davical, $uid);
                        $oPendiente->crear_de_pendienteDB($id_reg, $Qid_pendiente);
                    }
                }
            }

            //////// BY PASS //////
            if (is_true($Q_bypass) && !empty($Q_id_entrada)) {
                $oEntradaBypass = new EntradaBypass($Q_id_entrada);
                $oEntradaBypass->DBCargar(); // Mo pasa nada si no existe, ya se insertará
                //Q_asunto.
                if ($perm_asunto >= PermRegistro::PERM_MODIFICAR) {
                    if ($compartida) {
                        $gesEntradas = new GestorEntrada();
                        $cEntradas = $gesEntradas->getEntradas(['id_entrada_compartida' => $Q_id_entrada]);
                        $oEntrada = $cEntradas[0];
                    } else {
                        $oEntrada = new Entrada($Q_id_entrada);
                    }

                    if ($oEntrada->DBCargar() === FALSE) {
                        $err_cargar = sprintf(_("OJO! no existe la entrada en %s, linea %s"), __FILE__, __LINE__);
                        exit ($err_cargar);
                    }
                    $oEntrada->setAsunto($Q_asunto);
                    if ($oEntrada->DBGuardar() === FALSE) {
                        $error_txt .= $oEntrada->getErrorTxt();
                    }
                }
                // destinos
                $Q_grupo_dst = (string)filter_input(INPUT_POST, 'grupo_dst');
                $Q_f_salida = (string)filter_input(INPUT_POST, 'f_salida');

                // genero un vector con todos los grupos.
                $Qa_grupos = (array)filter_input(INPUT_POST, 'grupos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                /* genero un vector con todas las referencias. Antes ya llegaba así, pero al quitar [] de los nombres, legan uno a uno.  */
                $Qa_destinos = (array)filter_input(INPUT_POST, 'destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $Qa_prot_num_destinos = (array)filter_input(INPUT_POST, 'prot_num_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $Qa_prot_any_destinos = (array)filter_input(INPUT_POST, 'prot_any_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $Qa_prot_mas_destinos = (array)filter_input(INPUT_POST, 'prot_mas_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

                // Si esta marcado como grupo de destinos, o destinos individuales.
                if (is_true($Q_grupo_dst)) {
                    $descripcion = '';
                    $gesGrupo = new GestorGrupo();
                    $a_grupos = $gesGrupo->getArrayGrupos();
                    foreach ($Qa_grupos as $id_grupo) {
                        $descripcion .= empty($descripcion) ? '' : ' + ';
                        $descripcion .= $a_grupos[$id_grupo];
                    }
                    $oEntradaBypass->setId_grupos($Qa_grupos);
                    $oEntradaBypass->setDescripcion($descripcion);
                } else {
                    $aProtDst = [];
                    foreach ($Qa_destinos as $key => $id_lugar) {
                        $prot_num = $Qa_prot_num_destinos[$key];
                        $prot_any = $Qa_prot_any_destinos[$key];
                        $prot_mas = $Qa_prot_mas_destinos[$key];

                        if (!empty($id_lugar)) {
                            $oProtDst = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                            $aProtDst[] = $oProtDst->getProt();
                        }
                    }
                    $oEntradaBypass->setJson_prot_destino($aProtDst);
                    $oEntradaBypass->setId_grupos();
                    $oEntradaBypass->setDescripcion('x'); // no puede ser null.
                }
                if ($oEntradaBypass->DBGuardar() === FALSE) {
                    $error_txt .= $oEntradaBypass->getErrorTxt();
                }

                if (!empty($error_txt)) {
                    $jsondata['success'] = FALSE;
                    $jsondata['mensaje'] = $error_txt;
                } else {
                    $jsondata['success'] = TRUE;
                }
            }
        }


        if (!empty($error_txt)) {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = TRUE;
            $jsondata['id_entrada'] = $id_entrada;
            $a_cosas = ['id_entrada' => $id_entrada, 'filtro' => $Q_filtro];
            $pagina_mod = web\Hash::link('apps/entradas/controller/entrada_form.php?' . http_build_query($a_cosas));
            $jsondata['pagina_mod'] = $pagina_mod;
        }
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}