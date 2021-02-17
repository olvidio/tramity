<?php
namespace usuarios\model;
use core\ConfigGlobal;
use entradas\model\Entrada;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorOficina;
use usuarios\model\entity\GestorCargo;

class PermRegistro {
    
    /* CONST LOS MISMOS QUE EN Entradas 
     * // visibilidad
     * const V_TODOS           = 1;  // cualquiera
     * const V_PERSONAL        = 2;  // oficina y directores
     * const V_RESERVADO       = 3;  // sólo directores
     * const V_RESERVADO_VCD   = 4;  // sólo vcd + quien señale
     */
    
    /* valores de permiso:
     0: para no ver nada.
     1: ver.
     2: escribir.
     4: cambiar la visibilidad. (dtor of responsable.)
     */
    
    const PERM_NADA     = 0;
    const PERM_VER      = 1;
    const PERM_MODIFICAR = 2;
    const PERM_CAMBIAR  = 4;
    
    
    private $array_registro_perm = [];
    
    private function init() {
        $todos = [];
        $personal = [];
        $reservado = [];
        $vcd = [];
        // director de la oficna principal.
        $todos['dtor_pral'] = [
                    'asunto'    => 1,
                    'detalle'   => 2,
                    'escrito'   => 1,
                    'cambio'    => 4,
        ];
        $personal['dtor_pral'] = [
                    'asunto'    => 1,
                    'detalle'   => 2,
                    'escrito'   => 1,
                    'cambio'    => 4,
        ];
        $reservado['dtor_pral'] = [
                    'asunto'    => 1,
                    'detalle'   => 2,
                    'escrito'   => 1,
                    'cambio'    => 4,
        ];
        $vcd['dtor_pral'] = [
                    'asunto'    => 2,
                    'detalle'   => 2,
                    'escrito'   => 2,
                    'cambio'    => 4,
        ];
        // directores de las oficinas implicadas.
        $todos['dtor_imp'] = [
                    'asunto'    => 1,
                    'detalle'   => 1,
                    'escrito'   => 1,
                    'cambio'    => 0,
        ];
        $personal['dtor_imp'] = [
                    'asunto'    => 1,
                    'detalle'   => 1,
                    'escrito'   => 1,
                    'cambio'    => 0,
        ];
        $reservado['dtor_imp'] = [
                    'asunto'    => 1,
                    'detalle'   => 1,
                    'escrito'   => 1,
                    'cambio'    => 0,
        ];
        $vcd['dtor_imp'] = [
                    'asunto'    => 1,
                    'detalle'   => 1,
                    'escrito'   => 1,
                    'cambio'    => 0,
        ];
        // resto de directores.
        $todos['dtor'] = [
                    'asunto'    => 1,
                    'detalle'   => 1,
                    'escrito'   => 1,
                    'cambio'    => 0,
        ];
        $personal['dtor'] = [
                    'asunto'    => 1,
                    'detalle'   => 1,
                    'escrito'   => 1,
                    'cambio'    => 0,
        ];
        $reservado['dtor'] = [
                    'asunto'    => 1,
                    'detalle'   => 1,
                    'escrito'   => 1,
                    'cambio'    => 0,
        ];
        $vcd['dtor'] = [
                    'asunto'    => 0,
                    'detalle'   => 0,
                    'escrito'   => 0,
                    'cambio'    => 0,
        ];
        // secretario de la dl.
        $todos['secretario'] = [
                    'asunto'    => 2,
                    'detalle'   => 2,
                    'escrito'   => 2,
                    'cambio'    => 4,
        ];
        $personal['secretario'] = [
                    'asunto'    => 2,
                    'detalle'   => 2,
                    'escrito'   => 2,
                    'cambio'    => 4,
        ];
        $reservado['secretario'] = [
                    'asunto'    => 2,
                    'detalle'   => 2,
                    'escrito'   => 2,
                    'cambio'    => 4,
        ];
        $vcd['secretario'] = [
                    'asunto'    => 0,
                    'detalle'   => 0,
                    'escrito'   => 0,
                    'cambio'    => 0,
        ];
        // oficiales de secretaría.
        $todos['of_scl'] = [
                    'asunto'    => 2,
                    'detalle'   => 2,
                    'escrito'   => 2,
                    'cambio'    => 4,
        ];
        $personal['of_scl'] = [
                    'asunto'    => 2,
                    'detalle'   => 2,
                    'escrito'   => 2,
                    'cambio'    => 0,
        ];
        $reservado['of_scl'] = [
                    'asunto'    => 0,
                    'detalle'   => 0,
                    'escrito'   => 0,
                    'cambio'    => 0,
        ];
        $vcd['of_scl'] = [
                    'asunto'    => 0,
                    'detalle'   => 0,
                    'escrito'   => 0,
                    'cambio'    => 0,
        ];
        // oficiales de la oficina principal.
        $todos['of_pral'] = [
                    'asunto'    => 1,
                    'detalle'   => 2,
                    'escrito'   => 1,
                    'cambio'    => 0,
        ];
        $personal['of_pral'] = [
                    'asunto'    => 1,
                    'detalle'   => 2,
                    'escrito'   => 1,
                    'cambio'    => 0,
        ];
        $reservado['of_pral'] = [
                    'asunto'    => 0,
                    'detalle'   => 0,
                    'escrito'   => 0,
                    'cambio'    => 0,
        ];
        $vcd['of_pral'] = [
                    'asunto'    => 0,
                    'detalle'   => 0,
                    'escrito'   => 0,
                    'cambio'    => 0,
        ];
        // oficiales de las oficinas implicadas.
        $todos['of_imp'] = [
                    'asunto'    => 1,
                    'detalle'   => 1,
                    'escrito'   => 1,
                    'cambio'    => 0,
        ];
        $personal['of_imp'] = [
                    'asunto'    => 1,
                    'detalle'   => 1,
                    'escrito'   => 1,
                    'cambio'    => 0,
        ];
        $reservado['of_imp'] = [
                    'asunto'    => 0,
                    'detalle'   => 0,
                    'escrito'   => 0,
                    'cambio'    => 0,
        ];
        $vcd['of_imp'] = [
                    'asunto'    => 0,
                    'detalle'   => 0,
                    'escrito'   => 0,
                    'cambio'    => 0,
        ];
        // resto de oficiales.
        $todos['of'] = [
                    'asunto'    => 1,
                    'detalle'   => 1,
                    'escrito'   => 1,
                    'cambio'    => 0,
        ];
        $personal['of'] = [
                    'asunto'    => 1,
                    'detalle'   => 0,
                    'escrito'   => 0,
                    'cambio'    => 0,
        ];
        $reservado['of'] = [
                    'asunto'    => 0,
                    'detalle'   => 0,
                    'escrito'   => 0,
                    'cambio'    => 0,
        ];
        $vcd['of'] = [
                    'asunto'    => 0,
                    'detalle'   => 0,
                    'escrito'   => 0,
                    'cambio'    => 0,
        ];

        $this->array_registro_perm[Entrada::V_TODOS] = $todos;
        $this->array_registro_perm[Entrada::V_PERSONAL] = $personal;
        $this->array_registro_perm[Entrada::V_RESERVADO] = $reservado;
        $this->array_registro_perm[Entrada::V_RESERVADO_VCD] = $vcd;
        
        return $this->array_registro_perm;
    }
    
    public function __construct() {
        $this->init();
    }
    
    /**
     * Función para buscar el permiso para ver el asunto, detalle o escrito 
     * de una entrada o escrito o pendiente según quien sea yo.
     * 
     * @param object $oEntrada|$oEscrito|$oPendiente|oExpediente
     * @param string  $que (aunto|detalle|escrito|cambio)
     * @return number
     */
    function permiso_detalle($objeto,$que) {
        $role_actual = $_SESSION['session_auth']['role_actual'];
        
        $gesCargos = new GestorCargo();
        $cCargos = $gesCargos->getCargos(['cargo' => $role_actual]);
        if (!empty($cCargos[0])) {
            $id_oficina_role = $cCargos[0]->getId_oficina();
        } else {
            $id_oficina_role = 0;
        }
        
        $id_cargo = ConfigGlobal::mi_id_cargo();
        $oCargo = new Cargo($id_cargo);
        $soy_dtor = $oCargo->getDirector();
        $id_oficina_cargo = $oCargo->getId_oficina();
        
        /* Al final da igual si es entrada o escrito. Tienen los mismos métodos
        $clase = get_class($objeto);
        if (strpos($clase, 'entrada') !== FALSE) {
            $visibilidad = $objeto->getVisibilidad();
            $id_ponente = $objeto->getPonente();
            $a_resto_cargos = $objeto->getResto_oficinas();
        }
        */
        
        $visibilidad = $objeto->getVisibilidad();
        $id_ponente = $objeto->getPonente();
        $resto_cargos = $objeto->getResto_oficinas();
        // pasar cargos a oficinas:
        $oCargoP = new Cargo($id_ponente);
        $id_oficina_ponente = $oCargoP->getId_oficina();
        $a_oficinas = [];
        foreach ($resto_cargos as $id_cargo) {
            $oCargo = new Cargo($id_cargo);
            $a_oficinas[] = $oCargo->getId_oficina();
        }
        
        
        $soy = empty($soy_dtor)? 'of' : 'dtor';
        if (array_search($id_oficina_role, $a_oficinas)) {
            $soy = empty($soy_dtor)? 'of_imp' : 'dtor_imp';
        }
        if ($id_oficina_cargo == $id_ponente) {
            $soy = empty($soy_dtor)? 'of_pral' : 'dtor_pral';
        }
        if ($role_actual == 'secretaria') {
            $soy = empty($soy_dtor)? 'of_scl' : 'secretario';
        }
        
        if (empty($this->array_registro_perm[$visibilidad][$soy][$que]) {
            echo "visibilidad: $visibilidad, soy: $soy, que: $que<br>";
        }
        $permiso = $this->array_registro_perm[$visibilidad][$soy][$que];
        return $permiso;
    }
    
}