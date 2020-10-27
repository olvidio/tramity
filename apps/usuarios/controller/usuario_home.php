<?php
// INICIO Cabecera global de URL de controlador *********************************
use core\ConfigGlobal;
use core\ViewTwig;
use expedientes\model\ExpedienteLista;
use usuarios\model\entity\Usuario;
use entradas\model\EntradaLista;

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************

/* Necesario para cargar solo una vez las paginas css y js. (_css_default.html.twig) 
 * En concreto hay un problema con bootstrap.js y popper.js
 */
$peticion_ajax = 0;
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
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
// borrador = 1;
$filtro = 'borrador';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 1;
    $text = _("oficina (borrador)");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// firmar = 2;
$filtro = 'firmar';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 2;
    $text = _("para firmar");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// reunion = 3;
$filtro = 'reunion';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 3;
    $text = _("para reunión");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// circular = 4;
$filtro = 'circulando';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 4;
    $text = _("circulando");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// acabados = 5;
$filtro = 'acabados';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 5;
    $text = _("acabados");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// archivados = 6;
$filtro = 'archivados';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 6;
    $text = _("archivados");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;


// copias = 7;
$filtro = 'copias';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 7;
    $text = _("copias");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;


// Entradas:
// entradas = 8;
$filtro = 'entrada';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query($aQuery));
    $num_orden = 8;
    $text = _("entradas");
    $oEntradaLista = new EntradaLista();
    $oEntradaLista->setFiltro($filtro);
    $num = $oEntradaLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// escritos = 9;
// cr = 10;
// permanentes = 11;
// avisos = 12;
// pendientes = 13;

$pagina_profile = web\Hash::link('apps/usuarios/controller/personal.php?'.http_build_query([]));

$mi_idioma = ConfigGlobal::mi_Idioma_short();
$a_campos = [
    'oficina' => 'Home',
    'username' => $username,
    'mi_idioma' => $mi_idioma,
    'error_fecha' => $_SESSION['oConfig']->getPlazoError(),
    'pagina_profile' => $pagina_profile,
    // para tabs
    'a_pills' => $a_pills,
    'vista' => 'home',
    'filtro' => $filtro,
    'role_actual' => $_SESSION['session_auth']['role_actual'],
    'a_roles' => $_SESSION['session_auth']['a_roles'],
    'peticion_ajax' => $peticion_ajax,
];
$oView = new ViewTwig('usuarios/controller');

if (empty($Qtabs)) {
    echo $oView->renderizar('usuario_home.html.twig',$a_campos);
} else {
    echo $oView->renderizar('usuario_tabs.html.twig',$a_campos);
}
