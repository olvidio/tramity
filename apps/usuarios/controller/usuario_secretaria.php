<?php
// INICIO Cabecera global de URL de controlador *********************************
use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\EntradaLista;
use expedientes\model\ExpedienteLista;
use expedientes\model\EscritoLista;
use expedientes\model\Escrito;

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
//oficinas adicionales (suplencias..)
/*
if ($username == 'scdl') {
    $a_roles_posibles = [ 'scdl', 'secretaria'];
}
*/

$Qtabs = (string) \filter_input(INPUT_POST, 'tabs');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

$a_pills = [];
//Diferentes filtros:
// Expedientes:
$oExpedienteLista = new ExpedienteLista();

// Sólo para scdl
if (ConfigGlobal::mi_usuario_cargo() === 'scdl') {
    // fijar reunión = 1;
    $filtro = 'fijar_reunion';
        $active = ($filtro == $Qfiltro)? 'active' : '';
        $aQuery = [ 'filtro' => $filtro ];
        $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
        $num_orden = 1;
        $text = _("fijar reunión");
        $oExpedienteLista->setFiltro($filtro);
        $num = $oExpedienteLista->getNumero();
        $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
    $a_pills[$num_orden] = $pill;

    // seguimiento = 2;
    $filtro = 'seg_reunion';
        $active = ($filtro == $Qfiltro)? 'active' : '';
        $aQuery = [ 'filtro' => $filtro ];
        $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
        $num_orden = 2;
        $text = _("seguimiento reunion");
        $oExpedienteLista->setFiltro($filtro);
        $num = $oExpedienteLista->getNumero();
        $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
    $a_pills[$num_orden] = $pill;
}

// firmar = 2;
$filtro = 'seguimiento';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 7;
    $text = _("seguimiento");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// reunion = 3;
$filtro = 'distribuir';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 3;
    $text = _("distribuir");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// circular = 4;
// se envian escritos, no expedientes
$filtro = 'enviar';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro, 'modo' => 'mod' ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/escrito_lista.php?'.http_build_query($aQuery));
    $num_orden = 4;
    $text = _("enviar");
    $oEscritoLista = new EscritoLista();
    $oEscritoLista->setFiltro($filtro);
    $num = $oEscritoLista->getNumeroEnviar();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// permanantes = 5;
$filtro = 'permanentes';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 5;
    $text = _("permanentes");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// pendientes = 6;
$filtro = 'pendientes';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 6;
    $text = _("pendientes");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// introducir entradas
$filtro = 'en_ingresado';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query($aQuery));
    $num_orden = 7;
    $text = _("E: introducir");
    $oEntradaLista = new EntradaLista();
    $oEntradaLista->setFiltro($filtro);
    $num = $oEntradaLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// asignar entradas
$filtro = 'en_admitido';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query($aQuery));
    $num_orden = 8;
    $text = _("E: asignar");
    $oEntradaLista = new EntradaLista();
    $oEntradaLista->setFiltro($filtro);
    $num = $oEntradaLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// Solo el scdl
$perm_a = $_SESSION['oConfig']->getPerm_aceptar();
if (ConfigGlobal::mi_usuario_cargo() === 'scdl' OR $perm_a) {
    // aceptar entradas
    $filtro = 'en_asignado';
        $active = ($filtro == $Qfiltro)? 'active' : '';
        $aQuery = [ 'filtro' => $filtro ];
        $pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query($aQuery));
        $num_orden = 9;
        $text = _("E: aceptar");
        $oEntradaLista = new EntradaLista();
        $oEntradaLista->setFiltro($filtro);
        $num = $oEntradaLista->getNumero();
        $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
    $a_pills[$num_orden] = $pill;
}
// distribución cr
$filtro = 'bypass';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query($aQuery));
    $num_orden = 10;
    $text = _("ditribución cr");
    $oEntradaLista = new EntradaLista();
    $oEntradaLista->setFiltro($filtro);
    $num = $oEntradaLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// buscar = 13;
$filtro = 'buscar';
$active = ($filtro == $Qfiltro)? 'active' : '';
$aQuery = [ 'filtro' => $filtro ];
$pag_lst = web\Hash::link('apps/busquedas/controller/buscar_escrito.php?'.http_build_query($aQuery));
$num_orden = 13;
$text = _("archivo de escritos");
$num = '';
$pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// preferencias del scdl o suplente
if (ConfigGlobal::mi_usuario_cargo() === 'scdl') {
    $filtro = 'pref';
        $active = ($filtro == $Qfiltro)? 'active' : '';
        $aQuery = [ 'filtro' => $filtro ];
        $pag_lst = web\Hash::link('apps/config/controller/parametros_scdl.php?'.http_build_query($aQuery));
        $num_orden = 90;
        $text = _("pref");
        $oEntradaLista = new EntradaLista();
        $oEntradaLista->setFiltro($filtro);
        $num = 0;
        $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
    $a_pills[$num_orden] = $pill;
}

// pendientes = 16;
$filtro = 'pendientes';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro,
                'periodo' => 'hoy',
            ];
    $pag_lst = web\Hash::link('apps/pendientes/controller/pendiente_tabla.php?'.http_build_query($aQuery));
    $num_orden = 16;
    $text = _("pendientes");
    $num = '';
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// salida manual = 20;
$filtro = 'salida_manual';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro,
                'accion' => Escrito::ACCION_ESCRITO,
            ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/salida_escrito.php?'.http_build_query($aQuery));
    $num_orden = 20;
    $text = _("salida manual");
    $num = '';
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;
// ordenar:
ksort($a_pills);

$pagina_profile = web\Hash::link('apps/usuarios/controller/personal.php?'.http_build_query([]));
$pagina_etiquetas = web\Hash::link('apps/etiquetas/controller/etiqueta_lista.php?'.http_build_query([]));

$mi_idioma = ConfigGlobal::mi_Idioma_short();
$a_campos = [
    'oficina' => 'Secretaría',
    'username' => $username,
    'mi_idioma' => $mi_idioma,
    'error_fecha' => $_SESSION['oConfig']->getPlazoError(),
    'pagina_profile' => $pagina_profile,
    'pagina_etiquetas' => $pagina_etiquetas,
    // para tabs
    'a_pills' => $a_pills,
    'vista' => 'scdl',
    'filtro' => $filtro,
    'role_actual' => ConfigGlobal::role_actual(),
    'a_roles' => $_SESSION['session_auth']['a_roles'],
    'peticion_ajax' => $peticion_ajax,
];
$oView = new ViewTwig('usuarios/controller');

if (empty($Qtabs)) {
    echo $oView->renderizar('usuario_home.html.twig',$a_campos);
} else {
    echo $oView->renderizar('usuario_tabs.html.twig',$a_campos);
}
