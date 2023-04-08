<?php
// INICIO Cabecera global de URL de controlador *********************************
use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\EntradaLista;
use expedientes\model\ExpedienteAcabadosLista;
use expedientes\model\ExpedienteArchivadosLista;
use expedientes\model\ExpedienteBorradorLista;
use expedientes\model\ExpedienteCirculandoLista;
use expedientes\model\ExpedienteParaFirmarLista;
use expedientes\model\ExpedientepermanentesClLista;
use usuarios\model\entity\Usuario;
use function core\is_true;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

/* Necesario para cargar solo una vez las paginas css y js. (_css_default.html.twig) 
 * En concreto hay un problema con bootstrap.js y popper.js
 */
$peticion_ajax = 0;
if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // handle request as AJAX
    $peticion_ajax = 1;
}

$username = $_SESSION['session_auth']['username'];
$oUsuario = new Usuario(ConfigGlobal::mi_id_usuario());
$username = empty($oUsuario->getNom_usuario()) ? $username : $oUsuario->getNom_usuario();

$Q_tabs = (string)filter_input(INPUT_POST, 'tabs');
$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');

// grupos de menus: dropdown
$a_grupos = [1 => _("Expedientes"),
    2 => _("Entradas"),
];

$a_pills = [];

$filtro = 'borrador_propio';
$oExpedienteLista = new ExpedienteBorradorLista($filtro);
$active = ($filtro === $Q_filtro) ? 'active' : '';
$aQuery = ['filtro' => $filtro];
$pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?' . http_build_query($aQuery));
$num_orden = "1#1";
$text = _("personal borrador"); // _("borrador (propio)");
$explicacion = _("Expedientes de trabajo propio");
$num = $oExpedienteLista->getNumero();
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => $num,
    'active' => $active,
    'class' => 'btn-expediente',
    'explicacion' => $explicacion,
    'ver_orden' => false,
];
$a_pills[$num_orden] = $pill;

// firmar
$filtro = 'firmar';
$oExpedienteLista = new ExpedienteParaFirmarLista($filtro);
$active = ($filtro === $Q_filtro) ? 'active' : '';
$aQuery = ['filtro' => $filtro];
$pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?' . http_build_query($aQuery));
$num_orden = "1#2";
$text = _("para firmar");
$explicacion = _("Expedientes para revisión del consejo local");
$num = $oExpedienteLista->getNumero();
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => $num,
    'active' => $active,
    'class' => 'btn-expediente',
    'explicacion' => $explicacion,
    'ver_orden' => false,
];
$a_pills[$num_orden] = $pill;

// circulando
$filtro = 'circulando';
$oExpedienteLista = new ExpedienteCirculandoLista($filtro);
$active = ($filtro === $Q_filtro) ? 'active' : '';
$aQuery = ['filtro' => $filtro];
$pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?' . http_build_query($aQuery));
$num_orden = "1#3";
$text = _("circulando");
$explicacion = _("Expedientes para revisión del consejo local");
$num = $oExpedienteLista->getNumero();
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => $num,
    'active' => $active,
    'class' => 'btn-expediente',
    'explicacion' => $explicacion,
    'ver_orden' => false,
];
$a_pills[$num_orden] = $pill;

// acabados
$filtro = 'acabados';
$oExpedienteLista = new ExpedienteAcabadosLista($filtro);
$active = ($filtro === $Q_filtro) ? 'active' : '';
$aQuery = ['filtro' => $filtro];
$pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?' . http_build_query($aQuery));
$num_orden = "1#4";
$text = _("acabados");
$explicacion = _("Expedientes para enviar o archivar");
$num = $oExpedienteLista->getNumero();
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => $num,
    'active' => $active,
    'class' => 'btn-expediente',
    'explicacion' => $explicacion,
    'ver_orden' => false,
];
$a_pills[$num_orden] = $pill;

// archivados = 9
$filtro = 'archivados';
$oExpedienteLista = new ExpedienteArchivadosLista($filtro);
$active = ($filtro === $Q_filtro) ? 'active' : '';
$aQuery = ['filtro' => $filtro];
$pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?' . http_build_query($aQuery));
$num_orden = "1#5";
$text = _("archivados");
$explicacion = _("Expedientes de la propia oficina archivados, una vez finalizados todos los pasos");
// No hace falta el número:
$num = '';
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => $num,
    'active' => $active,
    'class' => 'btn-expediente',
    'explicacion' => $explicacion,
    'ver_orden' => false,
];
$a_pills[$num_orden] = $pill;


// entradas = 12
if (is_true(ConfigGlobal::soy_dtor())) {
    $filtro = 'en_aceptado';
    $active = ($filtro === $Q_filtro) ? 'active' : '';
    $aQuery = ['filtro' => $filtro];
    $pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?' . http_build_query($aQuery));
    $num_orden = '2#1';
    $text = _("correo de entrada"); //_("entradas");
    $explicacion = _("Gestionar el correo de entrada");
    $oEntradaLista = new EntradaLista();
    $oEntradaLista->setFiltro($filtro);
    $num = $oEntradaLista->getNumero();
    $pill = ['orden' => $num_orden,
        'text' => $text,
        'pag_lst' => $pag_lst,
        'num' => $num,
        'active' => $active,
        'class' => 'btn-entrada',
        'explicacion' => $explicacion,
        'ver_orden' => false,
    ];
    $a_pills[$num_orden] = $pill;
}

// entradas = 13
$filtro = 'en_encargado';
$active = ($filtro === $Q_filtro) ? 'active' : '';
$aQuery = ['filtro' => $filtro];
$pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?' . http_build_query($aQuery));
$num_orden = '2#2';
$text = _("correo encargado"); // _("entradas encargadas");
$explicacion = _("Ver el correo de entrada encargado a alguien");
$oEntradaLista = new EntradaLista();
$oEntradaLista->setFiltro($filtro);
$num = $oEntradaLista->getNumero();
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => $num,
    'active' => $active,
    'class' => 'btn-entrada',
    'explicacion' => $explicacion,
    'ver_orden' => false,
];
$a_pills[$num_orden] = $pill;

// buscar = 20
$filtro = 'en_buscar';
$active = ($filtro === $Q_filtro) ? 'active' : '';
$aQuery = ['filtro' => $filtro];
$pag_lst = web\Hash::link('apps/busquedas/controller/buscar_escrito.php?' . http_build_query($aQuery));
$num_orden = 20;
$text = _("archivo de escritos");
$explicacion = _("Buscar Entradas, escritos, etc. registrados en secetaría");
$num = '';
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => $num,
    'active' => $active,
    'class' => 'btn-pendiente',
    'explicacion' => $explicacion,
    'ver_orden' => false,
];
$a_pills[$num_orden] = $pill;

// entradas de la semana = 21
$filtro = 'entradas_semana';
$active = ($filtro === $Q_filtro) ? 'active' : '';
$aQuery = ['filtro' => $filtro, 'opcion' => 52];
$pag_lst = web\Hash::link('apps/busquedas/controller/ver_tabla.php?' . http_build_query($aQuery));
$num_orden = '2#3';
$text = _("entradas recientes");
$dias = $_SESSION['oConfig']->getPeriodoEntradas();
$explicacion = sprintf(_("Correo de dl y cr (marcado como visto o encargado) de los últimos %s días"), $dias);
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => '',
    'active' => $active,
    'class' => 'btn-entrada',
    'explicacion' => $explicacion,
    'ver_orden' => false,
];
$a_pills[$num_orden] = $pill;

// permanentes de cl
$filtro = 'permanentes_cl';
$oExpedienteLista = new ExpedientepermanentesClLista($filtro);
$active = ($filtro === $Q_filtro) ? 'active' : '';
$aQuery = ['filtro' => $filtro];
$pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?' . http_build_query($aQuery));
$num_orden = 21;
$text = _("permanentes cl");
$explicacion = _("permanentes del cl");
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => '',
    'active' => $active,
    'class' => 'btn-pendiente',
    'explicacion' => $explicacion,
    'ver_orden' => false,
];
$a_pills[$num_orden] = $pill;

// buscar = 22
$filtro = 'permanentes_cr';
$active = ($filtro === $Q_filtro) ? 'active' : '';
$aQuery = ['filtro' => $filtro];
$pag_lst = web\Hash::link('apps/busquedas/controller/lista_permanentes.php?' . http_build_query($aQuery));
$num_orden = 22;
$text = _("permanentes");
$explicacion = _("Escritos permanentes");
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => '',
    'active' => $active,
    'class' => 'btn-pendiente',
    'explicacion' => $explicacion,
    'ver_orden' => false,
];
$a_pills[$num_orden] = $pill;

// pendientes = 30
$filtro = 'pendientes';
$active = ($filtro === $Q_filtro) ? 'active' : '';
$aQuery = ['filtro' => $filtro,
    'periodo' => 'hoy',
];
$pag_lst = web\Hash::link('apps/pendientes/controller/pendiente_tabla.php?' . http_build_query($aQuery));
$pagina_inicio = $pag_lst;
$num_orden = 30;
$text = _("pendientes");
$explicacion = _("Gestionar pendientes del registro y/o la oficina");
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => '',
    'active' => $active,
    'class' => 'btn-pendiente',
    'explicacion' => $explicacion,
    'ver_orden' => false,
];
$a_pills[$num_orden] = $pill;

// documentos = 40
$filtro = 'documentos';
$active = ($filtro === $Q_filtro) ? 'active' : '';
$aQuery = ['filtro' => $filtro,
];
$pag_lst = web\Hash::link('apps/documentos/controller/documentos_lista.php?' . http_build_query($aQuery));
$num_orden = 40;
$text = _("documentos");
$explicacion = _("Introducir y gestionar documentos externos a la base de datos de Tramity");
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => '',
    'active' => $active,
    'class' => 'btn-documento',
    'explicacion' => $explicacion,
    'ver_orden' => false,
];
$a_pills[$num_orden] = $pill;

// ordenar:
ksort($a_pills);

$pagina_profile = web\Hash::link('apps/usuarios/controller/personal.php?' . http_build_query([]));
$pagina_etiquetas = web\Hash::link('apps/etiquetas/controller/etiqueta_lista.php?' . http_build_query([]));
$url_ajax = web\Hash::link('apps/usuarios/controller/usuario_update.php');

$mi_idioma = ConfigGlobal::mi_Idioma_short();
$nombre_entidad = ConfigGlobal::nombreEntidad();
$doc_help = 'apps/ayuda/ctr/ManualTramityCtr.html';

$a_campos = [
    'nombre_entidad' => $nombre_entidad,
    'role_actual' => ConfigGlobal::role_actual(),
    'username' => $username,
    'mi_idioma' => $mi_idioma,
    'error_fecha' => $_SESSION['oConfig']->getPlazoError(),
    'pagina_profile' => $pagina_profile,
    'pagina_etiquetas' => $pagina_etiquetas,
    // para tabs
    'a_pills' => $a_pills,
    'a_grupos' => $a_grupos,
    'vista' => 'ctr',
    'filtro' => $filtro,
    'a_roles' => $_SESSION['session_auth']['a_roles'],
    'peticion_ajax' => $peticion_ajax,
    'pagina_inicio' => $pagina_inicio,
    'doc_help' => $doc_help,
    // problemas con https
    'url_ajax' => $url_ajax,
];
$oView = new ViewTwig('usuarios/controller');

if (empty($Q_tabs)) {
    $oView->renderizar('usuario_home.html.twig', $a_campos);
} else {
    $oView->renderizar('usuario_tabs.html.twig', $a_campos);
}