<?php
// INICIO Cabecera global de URL de controlador *********************************
use core\ConfigGlobal;
use core\ViewTwig;
use function core\is_true;
use entradas\model\EntradaLista;
use expedientes\model\ExpedienteLista;
use usuarios\model\entity\Usuario;

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

/* Necesario para cargar solo una vez las paginas css y js. (_css_default.html.twig) 
 * En concreto hay un problema con bootstrap.js y popper.js
 */
$peticion_ajax = 0;
if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
{
    // handle request as AJAX
    $peticion_ajax = 1;
}

$username = $_SESSION['session_auth']['username'];
$oUsuario = new Usuario(ConfigGlobal::mi_id_usuario());
$username = empty($oUsuario->getNom_usuario())? $username : $oUsuario->getNom_usuario();

$Qtabs = (string) \filter_input(INPUT_POST, 'tabs');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

$a_pills = [];
//Diferentes filtros:
// Expedientes:

$oExpedienteLista = new ExpedienteLista();
$filtro = 'borrador_propio';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 1;
    $text = _("personal"); // _("borrador (propio)");
    $explicacion = _("Expedientes de trabajo propio");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active, 'explicacion' => $explicacion];
$a_pills[$num_orden] = $pill;

// borrador_oficina = 2
$filtro = 'borrador_oficina';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 2;
    $text = _("consejo local"); //_("borrador (oficina)");
    $explicacion = _("Expedientes para revisión del consejo local");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active, 'explicacion' => $explicacion];
$a_pills[$num_orden] = $pill;

    
// archivados = 9
$filtro = 'archivados';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 9;
    $text = _("archivados");
    $explicacion = _("Expedientes de la propia oficina archivados, una vez finalizados todos los pasos");
    // No hace falta el número:
    //$oExpedienteLista->setFiltro($filtro);
    //$num = $oExpedienteLista->getNumero();
    $num = '';
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active, 'explicacion' => $explicacion];
$a_pills[$num_orden] = $pill;


// entradas = 12
$filtro = 'en_aceptado';
	$active = ($filtro == $Qfiltro)? 'active' : '';
	$aQuery = [ 'filtro' => $filtro ];
	$pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query($aQuery));
	$num_orden = 12;
	$text = _("correo de entrada"); //_("entradas");
	$explicacion = _("Gestionar el correo de entrada");
	$oEntradaLista = new EntradaLista();
	$oEntradaLista->setFiltro($filtro);
	$num = $oEntradaLista->getNumero();
	$pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active, 'explicacion' => $explicacion];
$a_pills[$num_orden] = $pill;

// entradas = 13
$filtro = 'en_encargado';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query($aQuery));
    $num_orden = 13;
    $text = _("correo encargado"); // _("entradas encargadas");
    $explicacion = _("Ver el correo de entrada encargado a alguien");
    $oEntradaLista = new EntradaLista();
    $oEntradaLista->setFiltro($filtro);
    $num = $oEntradaLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active, 'explicacion' => $explicacion];
$a_pills[$num_orden] = $pill;

// buscar = 20
$filtro = 'en_buscar';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/busquedas/controller/buscar_escrito.php?'.http_build_query($aQuery));
    $num_orden = 20;
    $text = _("archivo de escritos");
    $explicacion = _("Buscar Entradas, escritos, etc. registrados en secetaría");
    $num = '';
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active, 'explicacion' => $explicacion];
$a_pills[$num_orden] = $pill;

// entradas de la semana = 21
$filtro = 'entradas_semana';
	$active = ($filtro == $Qfiltro)? 'active' : '';
	$aQuery = [ 'filtro' => $filtro ];
	$pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query($aQuery));
	$num_orden = 21;
	$text = _("entradas");
	$explicacion = _("Correo de dl y cr de los últimos 15 días");
	$num = '';
	$pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active, 'explicacion' => $explicacion];
$a_pills[$num_orden] = $pill;

// buscar = 22
$filtro = 'permanentes_cr';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/busquedas/controller/lista_permanentes.php?'.http_build_query($aQuery));
    $num_orden = 22;
    $text = _("permanentes de cr");
    $explicacion = _("Escritos de cr permanentes");
    $num = '';
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active, 'explicacion' => $explicacion];
$a_pills[$num_orden] = $pill;

// pendientes = 30
$filtro = 'pendientes';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro,
                'periodo' => 'hoy',
            ];
    $pag_lst = web\Hash::link('apps/pendientes/controller/pendiente_tabla.php?'.http_build_query($aQuery));
    $pagina_inicio = $pag_lst; 
    $num_orden = 30;
    $text = _("pendientes");
    $explicacion = _("Gestionar pendientes del registro y/o la oficina");
    $num = '';
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active, 'explicacion' => $explicacion];
$a_pills[$num_orden] = $pill;

// documentos = 40
$filtro = 'documentos';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro,
            ];
    $pag_lst = web\Hash::link('apps/documentos/controller/documentos_lista.php?'.http_build_query($aQuery));
    $num_orden = 40;
    $text = _("documentos");
    $explicacion = _("Introducir y gestionar documentos externos a la base de datos de Tramity");
    $num = '';
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active, 'explicacion' => $explicacion];
$a_pills[$num_orden] = $pill;

// ordenar:
ksort($a_pills);

$pagina_profile = web\Hash::link('apps/usuarios/controller/personal.php?'.http_build_query([]));
$pagina_etiquetas = web\Hash::link('apps/etiquetas/controller/etiqueta_lista.php?'.http_build_query([]));
$url_ajax = web\Hash::link('apps/usuarios/controller/usuario_update.php');

$mi_idioma = ConfigGlobal::mi_Idioma_short();
$a_campos = [
    'role_actual' => ConfigGlobal::role_actual(),
    'username' => $username,
    'mi_idioma' => $mi_idioma,
    'error_fecha' => $_SESSION['oConfig']->getPlazoError(),
    'pagina_profile' => $pagina_profile,
    'pagina_etiquetas' => $pagina_etiquetas,
    // para tabs
    'a_pills' => $a_pills,
    'vista' => 'ctr',
    'filtro' => $filtro,
    'a_roles' => $_SESSION['session_auth']['a_roles'],
    'peticion_ajax' => $peticion_ajax,
    'pagina_inicio' => $pagina_inicio,
    // problemas con https
    'url_ajax' => $url_ajax,
];
$oView = new ViewTwig('usuarios/controller');

if (empty($Qtabs)) {
    echo $oView->renderizar('usuario_home.html.twig',$a_campos);
} else {
    echo $oView->renderizar('usuario_tabs.html.twig',$a_campos);
}