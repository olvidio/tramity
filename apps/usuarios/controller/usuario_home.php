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
//$server_software = strtolower($_SERVER['SERVER_SOFTWARE']);
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
// borrador_propio = 1;
$filtro = 'borrador_propio';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 1;
    $text = _("borrador (propio)");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// borrador_oficina = 2;
$filtro = 'borrador_oficina';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 2;
    $text = _("borrador (oficina)");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// firmar = 3;
$filtro = 'firmar';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 3;
    $text = _("para firmar");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// reunion = 4;
$filtro = 'reunion';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 4;
    $text = _("para reunión");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// circular = 5;
$filtro = 'circulando';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 5;
    $text = _("circulando");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// acabados = 6;
$filtro = 'seg_reunion';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 6;
    $text = _("reunion día");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

if (is_true(ConfigGlobal::soy_dtor()) ) {
    // acabados = 7;
    $filtro = 'acabados';
        $active = ($filtro == $Qfiltro)? 'active' : '';
        $aQuery = [ 'filtro' => $filtro ];
        $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
        $num_orden = 7;
        $text = _("acabados");
        $oExpedienteLista->setFiltro($filtro);
        $num = $oExpedienteLista->getNumero();
        $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
    $a_pills[$num_orden] = $pill;
}
// acabados = 7.1;
$filtro = 'acabados_encargados';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 8;
    $text = _("acabados encargados");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;
    
// archivados = 9;
$filtro = 'archivados';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 9;
    $text = _("archivados");
    // No hace falta el número:
    //$oExpedienteLista->setFiltro($filtro);
    //$num = $oExpedienteLista->getNumero();
    $num = '';
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;


// copias = 10;
$filtro = 'copias';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?'.http_build_query($aQuery));
    $num_orden = 10;
    $text = _("copias");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;


// Entradas:
// Sólo para vcd
if (ConfigGlobal::mi_usuario_cargo() === 'vcd') {
    // entradas = 11;
    $filtro = 'en_ingresado';
        $active = ($filtro == $Qfiltro)? 'active' : '';
        $aQuery = [ 'filtro' => $filtro ];
        $pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query($aQuery));
        $num_orden = 11;
        $text = _("E: admitir");
        $oEntradaLista = new EntradaLista();
        $oEntradaLista->setFiltro($filtro);
        $num = $oEntradaLista->getNumero();
        $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
    $a_pills[$num_orden] = $pill;
}

// Sólo para dtor
if (is_true(ConfigGlobal::soy_dtor())) {
    // entradas = 12;
    $filtro = 'en_aceptado';
        $active = ($filtro == $Qfiltro)? 'active' : '';
        $aQuery = [ 'filtro' => $filtro ];
        $pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query($aQuery));
        $num_orden = 12;
        $text = _("entradas");
        $oEntradaLista = new EntradaLista();
        $oEntradaLista->setFiltro($filtro);
        $num = $oEntradaLista->getNumero();
        $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
    $a_pills[$num_orden] = $pill;
}

// entradas = 13;
$filtro = 'en_encargado';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query($aQuery));
    $num_orden = 13;
    $text = _("entradas encargadas");
    $oEntradaLista = new EntradaLista();
    $oEntradaLista->setFiltro($filtro);
    $num = $oEntradaLista->getNumero();
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// buscar = 20;
$filtro = 'en_buscar';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/busquedas/controller/buscar_escrito.php?'.http_build_query($aQuery));
    $num_orden = 20;
    $text = _("archivo de escritos");
    $num = '';
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

if (is_true(ConfigGlobal::soy_dtor())) {
    // escritos_cr = 21;
    $filtro = 'escritos_cr';
        $active = ($filtro == $Qfiltro)? 'active' : '';
        $aQuery = [ 'filtro' => $filtro ];
        $pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query($aQuery));
        $num_orden = 21;
        $text = _("escritos de cr");
        $num = '';
        $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
    $a_pills[$num_orden] = $pill;
}

// buscar = 22;
$filtro = 'permanentes_cr';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro ];
    $pag_lst = web\Hash::link('apps/busquedas/controller/lista_permanentes.php?'.http_build_query($aQuery));
    $num_orden = 22;
    $text = _("permanentes de cr");
    $num = '';
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// pendientes = 30;
$filtro = 'pendientes';
    $active = ($filtro == $Qfiltro)? 'active' : '';
    $aQuery = [ 'filtro' => $filtro,
                'periodo' => 'hoy',
            ];
    $pag_lst = web\Hash::link('apps/pendientes/controller/pendiente_tabla.php?'.http_build_query($aQuery));
    $num_orden = 30;
    $text = _("pendientes");
    $num = '';
    $pill = [ 'orden'=> $num_orden, 'text' => $text, 'pag_lst' => $pag_lst, 'num' => $num, 'active' => $active];
$a_pills[$num_orden] = $pill;

// ordenar:
ksort($a_pills);

$pagina_profile = web\Hash::link('apps/usuarios/controller/personal.php?'.http_build_query([]));
$pagina_etiquetas = web\Hash::link('apps/etiquetas/controller/etiqueta_lista.php?'.http_build_query([]));

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
    'vista' => 'home',
    'filtro' => $filtro,
    'a_roles' => $_SESSION['session_auth']['a_roles'],
    'peticion_ajax' => $peticion_ajax,
];
$oView = new ViewTwig('usuarios/controller');

if (empty($Qtabs)) {
    echo $oView->renderizar('usuario_home.html.twig',$a_campos);
} else {
    echo $oView->renderizar('usuario_tabs.html.twig',$a_campos);
}
