<?php
// INICIO Cabecera global de URL de controlador *********************************
use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\EntradaLista;
use escritos\model\Escrito;
use escritos\model\EscritoLista;
use expedientes\model\ExpedienteLista;
use usuarios\model\entity\Usuario;

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
    && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // handle request as AJAX
    $peticion_ajax = 1;
}

$username = $_SESSION['session_auth']['username'];
$oUsuario = new Usuario(ConfigGlobal::mi_id_usuario());
$username = empty($oUsuario->getNom_usuario()) ? $username : $oUsuario->getNom_usuario();

$Qtabs = (string)\filter_input(INPUT_POST, 'tabs');
$Qfiltro = (string)\filter_input(INPUT_POST, 'filtro');

$a_pills = [];
//Diferentes filtros:
// Expedientes:
$oExpedienteLista = new ExpedienteLista();

// Sólo para scdl o suplente
$aPosiblesCargos = $_SESSION['session_auth']['aPosiblesCargos'];
$id_cargo = array_search('scdl', $aPosiblesCargos);
if (!empty($id_cargo)) {
    // fijar reunión = 1;
    $filtro = 'fijar_reunion';
    $active = ($filtro == $Qfiltro) ? 'active' : '';
    $aQuery = ['filtro' => $filtro];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?' . http_build_query($aQuery));
    $num_orden = 1;
    $text = _("fijar reunión");
    $explicacion = _("asignar a cada expediente la fecha de la reunión (scdl)");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = ['orden' => $num_orden,
        'text' => $text,
        'pag_lst' => $pag_lst,
        'num' => $num,
        'active' => $active,
        'class' => 'btn-expediente',
        'explicacion' => $explicacion,
        'ver_orden' => TRUE,
    ];
    $a_pills[$num_orden] = $pill;

    // seguimiento = 2
    $filtro = 'seg_reunion';
    $active = ($filtro == $Qfiltro) ? 'active' : '';
    $aQuery = ['filtro' => $filtro];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?' . http_build_query($aQuery));
    $num_orden = 2;
    $text = _("seguimiento reunion");
    $explicacion = _("control de quiénes faltan por firmar cada expediente (scdl)");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = ['orden' => $num_orden,
        'text' => $text,
        'pag_lst' => $pag_lst,
        'num' => $num,
        'active' => $active,
        'class' => 'btn-expediente',
        'explicacion' => $explicacion,
        'ver_orden' => TRUE,
    ];
    $a_pills[$num_orden] = $pill;
}

// firmar = 2
$filtro = 'seguimiento';
$active = ($filtro == $Qfiltro) ? 'active' : '';
$aQuery = ['filtro' => $filtro];
$pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?' . http_build_query($aQuery));
$num_orden = 7;
$text = _("seguimiento");
$explicacion = '';
$oExpedienteLista->setFiltro($filtro);
$num = $oExpedienteLista->getNumero();
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => $num,
    'active' => $active,
    'class' => 'btn-expediente',
    'explicacion' => $explicacion,
    'ver_orden' => TRUE,
];
$a_pills[$num_orden] = $pill;

// reunion = 3
// Solo el scdl
$perm_distribuir = $_SESSION['oConfig']->getPerm_distribuir();
if (ConfigGlobal::mi_usuario_cargo() === 'scdl' || $perm_distribuir) {
    $filtro = 'distribuir';
    $active = ($filtro == $Qfiltro) ? 'active' : '';
    $aQuery = ['filtro' => $filtro];
    $pag_lst = web\Hash::link('apps/expedientes/controller/expediente_lista.php?' . http_build_query($aQuery));
    $num_orden = 3;
    $text = _("distribuir");
    $explicacion = _("Pasar a cada oficina los expedientes que ya han circulado (scdl)");
    $oExpedienteLista->setFiltro($filtro);
    $num = $oExpedienteLista->getNumero();
    $pill = ['orden' => $num_orden,
        'text' => $text,
        'pag_lst' => $pag_lst,
        'num' => $num,
        'active' => $active,
        'class' => 'btn-expediente',
        'explicacion' => $explicacion,
        'ver_orden' => TRUE,
    ];
    $a_pills[$num_orden] = $pill;
}

// circular = 4
// se envian escritos, no expedientes
$filtro = 'enviar';
$active = ($filtro == $Qfiltro) ? 'active' : '';
$aQuery = ['filtro' => $filtro, 'modo' => 'mod'];
$pag_lst = web\Hash::link('apps/escritos/controller/escrito_lista.php?' . http_build_query($aQuery));
$num_orden = 4;
$text = _("enviar");
$explicacion = _("Enviar escritos a los destinos correspondientes (secretaría)");
$oEscritoLista = new EscritoLista();
$oEscritoLista->setFiltro($filtro);
$num = $oEscritoLista->getNumeroEnviar();
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => $num,
    'active' => $active,
    'class' => 'btn-expediente',
    'explicacion' => $explicacion,
    'ver_orden' => TRUE,
];
$a_pills[$num_orden] = $pill;

// introducir entradas
$filtro = 'en_ingresado';
$active = ($filtro == $Qfiltro) ? 'active' : '';
$aQuery = ['filtro' => $filtro];
$pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?' . http_build_query($aQuery));
$num_orden = 7;
$text = _("E: introducir");
$explicacion = _("Introducir nuevas entradas para el vcd (Secretaría)");
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
    'ver_orden' => TRUE,
];
$a_pills[$num_orden] = $pill;

// asignar entradas
$filtro = 'en_admitido';
$active = ($filtro == $Qfiltro) ? 'active' : '';
$aQuery = ['filtro' => $filtro];
$pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?' . http_build_query($aQuery));
$num_orden = 8;
$text = _("E: asignar");
$explicacion = _("Completar el registro de las nuevas entradas (Secretaría)");
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
    'ver_orden' => TRUE,
];
$a_pills[$num_orden] = $pill;

// Solo el scdl
$perm_aceptar = $_SESSION['oConfig']->getPerm_aceptar();
if (ConfigGlobal::mi_usuario_cargo() === 'scdl' || $perm_aceptar) {
    // aceptar entradas
    $filtro = 'en_asignado';
    $active = ($filtro == $Qfiltro) ? 'active' : '';
    $aQuery = ['filtro' => $filtro];
    $pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?' . http_build_query($aQuery));
    $num_orden = 9;
    $text = _("E: aceptar");
    $explicacion = _("Confirmar y completar el registro de las nuevas entradas (scdl)");
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
        'ver_orden' => TRUE,
    ];
    $a_pills[$num_orden] = $pill;
}
// distribución cr
$filtro = 'bypass';
$active = ($filtro == $Qfiltro) ? 'active' : '';
$aQuery = ['filtro' => $filtro];
$pag_lst = web\Hash::link('apps/entradas/controller/entrada_lista.php?' . http_build_query($aQuery));
$num_orden = 10;
$text = _("distribución cr");
$explicacion = _("Enviar escritos de cr a ctr (Secretaría)");
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
    'ver_orden' => TRUE,
];
$a_pills[$num_orden] = $pill;

// buscar = 13
$filtro = 'en_buscar';
$active = ($filtro == $Qfiltro) ? 'active' : '';
$aQuery = ['filtro' => $filtro];
$pag_lst = web\Hash::link('apps/busquedas/controller/buscar_escrito.php?' . http_build_query($aQuery));
$num_orden = 13;
$text = _("archivo de escritos");
$explicacion = _("Modificaciones en Entradas, escritos, etc. (Secretaría)");
$num = '';
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => $num,
    'active' => $active,
    'class' => 'btn-entrada',
    'explicacion' => $explicacion,
    'ver_orden' => TRUE,
];
$a_pills[$num_orden] = $pill;

// preferencias del scdl o suplente
if (ConfigGlobal::mi_usuario_cargo() === 'scdl') {
    $filtro = 'pref';
    $active = ($filtro == $Qfiltro) ? 'active' : '';
    $aQuery = ['filtro' => $filtro];
    $pag_lst = web\Hash::link('apps/config/controller/parametros_scdl.php?' . http_build_query($aQuery));
    $num_orden = 90;
    $text = _("pref");
    $explicacion = _("Dar permisos a of de Secretaría para tareas del scdl (scdl)");
    $oEntradaLista = new EntradaLista();
    $oEntradaLista->setFiltro($filtro);
    $num = 0;
    $pill = ['orden' => $num_orden,
        'text' => $text,
        'pag_lst' => $pag_lst,
        'num' => $num,
        'active' => $active,
        'class' => 'btn-pendiente',
        'explicacion' => $explicacion,
        'ver_orden' => TRUE,
    ];
    $a_pills[$num_orden] = $pill;
}

// pendientes = 16
$filtro = 'pendientes';
$active = ($filtro == $Qfiltro) ? 'active' : '';
$aQuery = ['filtro' => $filtro,
    'periodo' => 'hoy',
];
$pag_lst = web\Hash::link('apps/pendientes/controller/pendiente_tabla.php?' . http_build_query($aQuery));
$pagina_inicio = $pag_lst;
$num_orden = 16;
$text = _("pendientes");
$explicacion = _("Gestión de los pendientes (Secretaría)");
$num = '';
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => $num,
    'active' => $active,
    'class' => 'btn-pendiente',
    'explicacion' => $explicacion,
    'ver_orden' => TRUE,
];
$a_pills[$num_orden] = $pill;

// salida manual = 20
$filtro = 'salida_manual';
$active = ($filtro == $Qfiltro) ? 'active' : '';
$aQuery = ['filtro' => $filtro,
    'accion' => Escrito::ACCION_ESCRITO,
];
$pag_lst = web\Hash::link('apps/escritos/controller/salida_escrito.php?' . http_build_query($aQuery));
$num_orden = 20;
$text = _("salida manual");
$explicacion = _("Registro y envío de escritos sin circular expediente (Secretaría)");
$num = '';
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => $num,
    'active' => $active,
    'class' => 'btn-pendiente',
    'explicacion' => $explicacion,
    'ver_orden' => TRUE,
];
$a_pills[$num_orden] = $pill;

// mantenimiento = 21
$filtro = 'mantenimiento';
$active = ($filtro == $Qfiltro) ? 'active' : '';
$aQuery = ['filtro' => $filtro,
];
$pag_lst = web\Hash::link('apps/escritos/controller/mantenimiento.php?' . http_build_query($aQuery));
$num_orden = 21;
$text = _("mantenimiento");
$explicacion = _("Operaciones varias (Secretaría)");
$num = '';
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => $num,
    'active' => $active,
    'class' => 'btn-pendiente',
    'explicacion' => $explicacion,
    'ver_orden' => TRUE,
];
$a_pills[$num_orden] = $pill;

// plantillas = 30
$filtro = 'plantillas';
$active = ($filtro == $Qfiltro) ? 'active' : '';
$aQuery = ['filtro' => $filtro,
    'accion' => Escrito::ACCION_PLANTILLA,
];
$pag_lst = web\Hash::link('apps/plantillas/controller/plantilla_lista.php?' . http_build_query($aQuery));
$num_orden = 30;
$text = _("plantillas");
$explicacion = _("Modelos jurídicos para diferentes trámites (Secretaría)");
$num = '';
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => $num,
    'active' => $active,
    'class' => 'btn-pendiente',
    'explicacion' => $explicacion,
    'ver_orden' => TRUE,
];
$a_pills[$num_orden] = $pill;

// imprimir = 40
$filtro = 'imprimir';
$active = ($filtro == $Qfiltro) ? 'active' : '';
$aQuery = ['filtro' => $filtro,
];
$pag_lst = web\Hash::link('apps/busquedas/controller/imprimir_que.php?' . http_build_query($aQuery));
$num_orden = 40;
$text = _("imprimir");
$explicacion = _("Imprimir el registro (Secretaría)");
$num = '';
$pill = ['orden' => $num_orden,
    'text' => $text,
    'pag_lst' => $pag_lst,
    'num' => $num,
    'active' => $active,
    'class' => 'btn-pendiente',
    'explicacion' => $explicacion,
    'ver_orden' => TRUE,
];
$a_pills[$num_orden] = $pill;

// ordenar:
ksort($a_pills);

$pagina_profile = web\Hash::link('apps/usuarios/controller/personal.php?' . http_build_query([]));
$pagina_etiquetas = web\Hash::link('apps/etiquetas/controller/etiqueta_lista.php?' . http_build_query([]));
$url_ajax = web\Hash::link('apps/usuarios/controller/usuario_update.php');

$mi_idioma = ConfigGlobal::mi_Idioma_short();
$entidad = ConfigGlobal::getEsquema();
$a_campos = [
    'entidad' => $entidad,
    'role_actual' => ConfigGlobal::role_actual(),
    'username' => $username,
    'mi_idioma' => $mi_idioma,
    'error_fecha' => $_SESSION['oConfig']->getPlazoError(),
    'pagina_profile' => $pagina_profile,
    'pagina_etiquetas' => $pagina_etiquetas,
    // para tabs
    'a_pills' => $a_pills,
    'vista' => 'secretaria',
    'filtro' => $filtro,
    'a_roles' => $_SESSION['session_auth']['a_roles'],
    'peticion_ajax' => $peticion_ajax,
    'pagina_inicio' => $pagina_inicio,
    // problemas con https
    'url_ajax' => $url_ajax,
];
$oView = new ViewTwig('usuarios/controller');

if (empty($Qtabs)) {
    echo $oView->renderizar('usuario_home.html.twig', $a_campos);
} else {
    echo $oView->renderizar('usuario_tabs.html.twig', $a_campos);
}