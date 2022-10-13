<?php

use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\entity\EntradaCompartida;
use entradas\model\Entrada;
use etiquetas\model\entity\GestorEtiqueta;
use expedientes\model\Expediente;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use web\DateTimeLocal;
use web\Desplegable;
use web\Hash;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


$Qid_expediente = (integer)\filter_input(INPUT_POST, 'id_expediente');
$Qfiltro = (string)\filter_input(INPUT_POST, 'filtro');
$Qid_entrada = (integer)\filter_input(INPUT_POST, 'id_entrada');

$pagina_contestar = '';
// Añado la opcion de poder crear un expediente desde entradas
switch ($Qfiltro) {
    case 'entradas_semana':
    case 'escritos_cr':
    case 'permanentes_cr':
    case 'en_buscar':
        $oEntrada = new Entrada($Qid_entrada);
        $asunto = $oEntrada->getAsunto();

        $a_condicion = [];
        $str_condicion = (string)\filter_input(INPUT_POST, 'condicion');
        parse_str($str_condicion, $a_condicion);
        $a_condicion['filtro'] = $Qfiltro;
        switch ($Qfiltro) {
            case 'en_buscar':
                $pagina_cancel = web\Hash::link('apps/busquedas/controller/buscar_escrito.php?' . http_build_query($a_condicion));
                break;
            case 'permanentes_cr':
                $pagina_cancel = web\Hash::link('apps/busquedas/controller/lista_permanentes.php?' . http_build_query($a_condicion));
                // En los ctr, buscar en entradas compartidas:
                if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
                    $oEntrada = new EntradaCompartida($Qid_entrada);
                    $asunto = $oEntrada->getAsunto_entrada();
                }
                break;
            case 'escritos_cr':
                $a_condicion['opcion'] = 51;
                $pagina_cancel = web\Hash::link('apps/busquedas/controller/ver_tabla.php?' . http_build_query($a_condicion));
                break;
            case 'entradas_semana':
                $a_condicion['opcion'] = 52;
                $pagina_cancel = web\Hash::link('apps/busquedas/controller/ver_tabla.php?' . http_build_query($a_condicion));
                break;
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }
        break;
    case 'en_aceptado':
        $Qoficina = (string)\filter_input(INPUT_POST, 'oficina');
        $oEntrada = new Entrada($Qid_entrada);
        $asunto = $oEntrada->getAsunto();

        $url_cancel = 'apps/entradas/controller/entrada_lista.php';
        $pagina_cancel = Hash::link($url_cancel . '?' . http_build_query(['filtro' => $Qfiltro, 'oficina' => $Qoficina]));
        // En los ctr, ir directo a contestar:
        if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
            $url_contestar = 'apps/escritos/controller/escrito_from_entrada.php';
        } else {
            $url_contestar = $url_cancel;
        }
        $pagina_contestar = Hash::link($url_contestar . '?' . http_build_query(['filtro' => $Qfiltro, 'id_entrada' => $Qid_entrada]));
        break;
    case 'en_encargado':
        $Qencargado = (integer)\filter_input(INPUT_POST, 'encargado');
        $oEntrada = new Entrada($Qid_entrada);
        $asunto = $oEntrada->getAsunto();

        $url_cancel = 'apps/entradas/controller/entrada_lista.php';
        $pagina_cancel = Hash::link($url_cancel . '?' . http_build_query(['filtro' => $Qfiltro, 'encargado' => $Qencargado]));
        // En los ctr, ir directo a contestar:
        if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
            $url_contestar = 'apps/escritos/controller/escrito_from_entrada.php';
        } else {
            $url_contestar = $url_cancel;
        }
        $pagina_contestar = Hash::link($url_contestar . '?' . http_build_query(['filtro' => $Qfiltro, 'id_entrada' => $Qid_entrada]));
        break;
    default:
        if (empty($Qid_expediente) && $Qfiltro != 'en_aceptado') {
            exit ("Error, no existe el expediente");
        }

        $oExpediente = new Expediente();
        $oExpediente->setId_expediente($Qid_expediente);
        $asunto = $oExpediente->getAsunto();
        $id_ponente = $oExpediente->getPonente();

        $oCargo = new Cargo($id_ponente);
        $oficina_ponente = $oCargo->getId_oficina();

        $url_cancel = 'apps/expedientes/controller/expediente_lista.php';
        $pagina_cancel = Hash::link($url_cancel . '?' . http_build_query(['filtro' => $Qfiltro]));
}

/*
- a "para firmar" i "circulando" el botó "mov/cop" ha de fer:
    - "a borrador" (passa a "borrador") [els que són propis o de l'oficina]
    - "copia a borrador" (fa una copia a "borrador") [els que són propis o de l'oficina]
    - "còpia a 'Copias'" (fa una còpia a "Copias") [els que són d'altres oficines]
        
- a "para reunión" i "reunión día" el botó "mov/cop" ha de fer:
    - "copia a borrador" (fa una copia a "borrador") [els que són propis o de l'oficina]
    - "còpia a 'Copias'" (fa una còpia a "Copias") [els que són d'altres oficines]
        
- a "Acabados", "Copias" el botó "mov/cop" ha de fer:
    - "a borrador" (passa a "borrador")
    - "copia a borrador" (fa una copia a "borrador")
        
- a "Archivados" el botó "mov/cop" ha de fer:
    - "a borrador" (passa a "borrador")
    - "copia a borrador" (fa una copia a "borrador")
    - "còpia a oficina" (fa una còpia a una altre oficina)
*/

$oDesplCargosOficinaPendiente = [];
$oDesplCargosOficinaEncargado = [];
$oDesplCargos = [];
$a_botones = [];
$txt_plazo = '';
$f_plazo = '';
$hoy_iso = '';
$titulo = _("Acciones para el expediente");
switch ($Qfiltro) {
    case 'en_aceptado':
    case 'en_encargado':
        $titulo = _("Acciones para la entrada");
        $a_botones[4] = ['accion' => 'en_add_encargado',
            'txt' => _("Encargar a"),
            'tipo' => 'modal',
        ];
    case 'permanentes_cr':
    case 'en_buscar':
        $titulo = _("Acciones para la entrada");
        $a_botones[3] = ['accion' => 'en_visto',
            'txt' => _("marcar como visto"),
            'tipo' => '',
        ];
    case 'entradas_semana':
    case 'escritos_cr':
        $titulo = _("Acciones para la entrada");

        $a_botones[0] = ['accion' => 'en_add_expediente',
            'txt' => _("añadir a un expediente"),
            'tipo' => 'modal',
        ];
        $a_botones[1] = ['accion' => 'en_expediente',
            'txt' => _("crear un nuevo expediente"),
            'tipo' => '',
        ];
        $a_botones[2] = ['accion' => 'en_pendiente',
            'txt' => _("crear un nuevo pendiente de la oficina"),
            'tipo' => 'modal',
        ];
        $a_botones[5] = ['accion' => 'en_add_etiqueta',
            'txt' => _("Etiquetas"),
            'tipo' => 'modal1',
        ];

        $txt_plazo = _("plazo para contestar");
        $oHoy = new DateTimeLocal();
        $hoy_iso = $oHoy->getIso();
        $f_plazo = $oHoy->getFromLocal();

        $gesCargos = new GestorCargo();
        $a_posibles_cargos_oficina = $gesCargos->getArrayUsuariosOficina(ConfigGlobal::role_id_oficina());
        $oDesplCargosOficinaPendiente = new Desplegable('id_cargo_pendiente', $a_posibles_cargos_oficina, '', '');
        $oDesplCargosOficinaEncargado = new Desplegable('id_cargo_encargado', $a_posibles_cargos_oficina, '', '');
        break;
    case 'borrador_oficina':
    case 'borrador_propio':
        // los de la oficina
        if ($oficina_ponente == ConfigGlobal::role_id_oficina()) {
            $a_botones[0] = ['accion' => 'exp_eliminar',
                'txt' => _("eliminar"),
            ];
        }
        break;
    case 'firmar':
    case 'circulando':
        // sólo si soy el ponente (creador)
        if ($id_ponente == ConfigGlobal::role_id_cargo()) {
            $a_botones[0] = ['accion' => 'exp_a_borrador',
                'txt' => _("mover a borrador"),
            ];
        }
        // Si soy dtor de la oficina, al mover debo cambiar el creador.
        if ($oficina_ponente == ConfigGlobal::role_id_oficina() && ConfigGlobal::soy_dtor()) {
            $a_botones[0] = ['accion' => 'exp_a_borrador_cmb_creador',
                'txt' => _("mover a borrador"),
            ];
        }
        // los de la oficina
        if ($oficina_ponente == ConfigGlobal::role_id_oficina()) {
            $a_botones[1] = ['accion' => 'exp_cp_borrador',
                'txt' => _("copiar a borrador"),
            ];
        }
        // para todos
        $a_botones[2] = ['accion' => 'exp_cp_copias',
            'txt' => _("copiar a copias"),
        ];
        break;
    case 'reunion':
    case 'seg_reunion':
        // los de la oficina
        if ($oficina_ponente == ConfigGlobal::role_id_oficina()) {
            $a_botones[1] = ['accion' => 'exp_cp_borrador',
                'txt' => _("copiar a borrador"),
            ];
        }
        // para todos
        $a_botones[2] = ['accion' => 'exp_cp_copias',
            'txt' => _("copiar a copias"),
        ];
        break;
    case 'acabados':
    case 'acabados_encargados':
    case 'copias':
        // sólo si soy el ponente (creador)
        if ($id_ponente == ConfigGlobal::role_id_cargo()) {
            $a_botones[0] = ['accion' => 'exp_a_borrador',
                'txt' => _("mover a borrador"),
            ];
        }
        $a_botones[1] = ['accion' => 'exp_cp_borrador',
            'txt' => _("copiar a borrador"),
        ];
        break;
    case 'archivados':
        // sólo si soy el ponente (creador)
        if ($id_ponente == ConfigGlobal::role_id_cargo()) {
            $a_botones[0] = ['accion' => 'exp_a_borrador',
                'txt' => _("mover a borrador"),
            ];
        }
        $a_botones[1] = ['accion' => 'exp_cp_borrador',
            'txt' => _("copiar a borrador"),
        ];
        $a_botones[2] = ['accion' => 'exp_cp_oficina',
            'txt' => _("copiar a otro cargo"),
            'tipo' => 'modal',
        ];
        $gesCargos = new GestorCargo();
        $a_posibles_cargos = $gesCargos->getArrayCargos(ConfigGlobal::role_id_oficina());
        $oDesplCargos = new Desplegable('of_destino', $a_posibles_cargos, '', '');
        break;
    case 'fijar_reunion':
    case 'distribuir':
        // No hace nada.
        break;
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}

if (empty($a_botones)) {
    $a_botones[] = ['accion' => '',
        'txt' => _("no tiene permiso"),
    ];
}

// Etiquetas
$etiquetas = []; // No hay ninguna porque en archivar es cuando se añaden.
$gesEtiquetas = new GestorEtiqueta();
$cEtiquetas = $gesEtiquetas->getMisEtiquetas();
$a_posibles_etiquetas = [];
foreach ($cEtiquetas as $oEtiqueta) {
    $id_etiqueta = $oEtiqueta->getId_etiqueta();
    $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
    $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
}

if (!empty($oEntrada)) {
    $etiquetas = $oEntrada->getEtiquetasVisiblesArray();
}
$oArrayDesplEtiquetas = new web\DesplegableArray($etiquetas, $a_posibles_etiquetas, 'etiquetas');
$oArrayDesplEtiquetas->setBlanco('t');
$oArrayDesplEtiquetas->setAccionConjunto('fnjs_mas_etiquetas()');

// datepicker
$oFecha = new DateTimeLocal();
$format = $oFecha->getFormat();
$yearStart = date('Y');
$yearEnd = $yearStart + 2;

$vista = ConfigGlobal::getVista();

$a_campos = [
    'id_entrada' => $Qid_entrada,
    'id_expediente' => $Qid_expediente,
    'filtro' => $Qfiltro,
    //'oHash' => $oHash,
    'titulo' => $titulo,
    'asunto' => $asunto,
    'a_botones' => $a_botones,
    'pagina_cancel' => $pagina_cancel,
    'oDesplCargosOficinaPendiente' => $oDesplCargosOficinaPendiente,
    'oDesplCargosOficinaEncargado' => $oDesplCargosOficinaEncargado,
    'oDesplCargos' => $oDesplCargos,
    'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
    // para crea pendiente:
    'txt_plazo' => $txt_plazo,
    'f_plazo' => $f_plazo,
    'hoy_iso' => $hoy_iso,
    // datepicker
    'format' => $format,
    'yearStart' => $yearStart,
    'yearEnd' => $yearEnd,
    //Solo para saltar directo al contestar una entrada
    'pagina_contestar' => $pagina_contestar,
    'vista' => $vista,
];

$oView = new ViewTwig('expedientes/controller');
echo $oView->renderizar('expediente_accion.html.twig', $a_campos);