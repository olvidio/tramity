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

$a_pills = [];

// acabados
$filtro = 'acabados';
$oExpedienteLista = new ExpedienteAcabadosLista($filtro);
$active = ($filtro === $Q_filtro) ? 'active' : '';
$aQuery = ['filtro' => $filtro];
$pag_lst = web\Hash::link('apps/escritos/controller/escrito_lista_correo.php?' . http_build_query($aQuery));
$num_orden = "1";
$text = _("escritos");
$explicacion = _("Escritos pendientes de enviar");
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

// entradas = 2
$filtro = 'en_aceptado';
$active = ($filtro === $Q_filtro) ? 'active' : '';
$aQuery = ['filtro' => $filtro];
$pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?' . http_build_query($aQuery));
$num_orden = '2';
$text = _("entradas"); //_("entradas");
$explicacion = _("Gestionar el correo de entrada.");
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
    'vista' => 'ctr_correo',
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