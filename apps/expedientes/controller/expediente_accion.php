<?php
use core\ConfigGlobal;
use core\ViewTwig;
use expedientes\model\Expediente;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use web\Hash;
use entradas\model\Entrada;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************



$Qid_expediente = (integer) \filter_input(INPUT_POST, 'id_expediente');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
$Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');

// Añado la opcion de poder crear un eexpediente desde entradas
if ($Qfiltro == 'en_aceptado') {
    $oEntrada = new Entrada($Qid_entrada);
    $asunto = $oEntrada->getAsunto();
    
    $url_cancel = 'apps/entradas/controller/entrada_lista.php';
    $pagina_cancel = Hash::link($url_cancel.'?'.http_build_query(['filtro' => $Qfiltro]));
} else {
    if (empty($Qid_expediente) && $Qfiltro != 'en_aceptado') {
        exit ("Error, no existe el expediente");
    }

    $oExpediente = new Expediente();
    $oExpediente->setId_expediente($Qid_expediente);
    $estado = $oExpediente->getEstado();
    $asunto = $oExpediente->getAsunto();
    $id_ponente = $oExpediente->getPonente();

    $oCargo = new Cargo($id_ponente);
    $oficina_ponente = $oCargo->getId_oficina();

    $url_cancel = 'apps/expedientes/controller/expediente_lista.php';
    $pagina_cancel = Hash::link($url_cancel.'?'.http_build_query(['filtro' => $Qfiltro]));
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

$a_botones = [];
switch($Qfiltro) {
    case 'en_aceptado':
        //if ($estado == Expediente::ESTADO_BORRADOR) {
        // los de la oficina
        $a_botones[0] = ['accion' => 'en_add_expediente',
                        'txt'    => _("añadir a un expediente"),
                        'tipo'    => 'modal',
                    ];
        $a_botones[1] = ['accion' => 'en_expediente',
                        'txt'    => _("crear un nuevo expediente"),
                        'tipo'    => '',
                    ];
        break;
    case 'borrador_oficina':
    case 'borrador_propio':
        //if ($estado == Expediente::ESTADO_BORRADOR) {
        // los de la oficina
        if ($oficina_ponente == ConfigGlobal::role_id_oficina()) {
            $a_botones[0] = ['accion' => 'exp_eliminar',
                            'txt'    => _("eliminar"),
                        ];
        }
        break;
    case 'firmar':
    case 'circulando':
        // los de la oficina
        if ($oficina_ponente == ConfigGlobal::role_id_oficina()) {
            $a_botones[0] = ['accion' => 'exp_a_borrador',
                            'txt'    => _("mover a borrador"),
                        ];
            $a_botones[1] = ['accion' => 'exp_cp_borrador',
                            'txt'    => _("copiar a borrador"),
                        ];
        }
        // para todos
        $a_botones[2] = ['accion' => 'exp_cp_copias',
                        'txt'    => _("copiar a copias"),
                    ];
        break;
    case 'reunion':
    case 'seg_reunion':
        // los de la oficina
        if ($oficina_ponente == ConfigGlobal::role_id_oficina()) {
            $a_botones[1] = ['accion' => 'exp_cp_borrador',
                            'txt'    => _("copiar a borrador"),
                        ];
        }
        // para todos
        $a_botones[2] = ['accion' => 'exp_cp_copias',
                        'txt'    => _("copiar a copias"),
                    ];
        break;
    case 'acabados':
    case 'acabados_encargados':
    case 'copias':
        $a_botones[0] = ['accion' => 'exp_a_borrador',
                        'txt'    => _("mover a borrador"),
                    ];
        $a_botones[1] = ['accion' => 'exp_cp_borrador',
                        'txt'    => _("copiar a borrador"),
                    ];
        break;
    case 'archivados':
        $a_botones[0] = ['accion' => 'exp_a_borrador',
                        'txt'    => _("mover a borrador"),
                    ];
        $a_botones[1] = ['accion' => 'exp_cp_borrador',
                        'txt'    => _("copiar a borrador"),
                    ];
        $a_botones[2] = ['accion' => 'exp_cp_oficina',
                        'txt'    => _("copiar a oficina"),
                    ];
        
        break;
}
if (empty($a_botones)) {
    $a_botones[] = ['accion' => '',
                    'txt'    => _("no tiene permiso"),
                ];
}

$a_campos = [
    'id_entrada' => $Qid_entrada,
    'id_expediente' => $Qid_expediente,
    'filtro' => $Qfiltro,
    //'oHash' => $oHash,
    'asunto' => $asunto,
    'a_botones' => $a_botones,
    'pagina_cancel' => $pagina_cancel,
];

$oView = new ViewTwig('expedientes/controller');
echo $oView->renderizar('expediente_accion.html.twig',$a_campos);