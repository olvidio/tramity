<?php
namespace usuarios\model;
use core\ConfigGlobal;
use entradas\model\Entrada;
use usuarios\model\entity\Cargo;

class PermRegistro {
    
    /* CONST LOS MISMOS QUE EN Entradas 
     * // visibilidad
     * const V_TODOS           = 1;  // cualquiera
     * const V_PERSONAL        = 2;  // oficina y directores
     * const V_DIRECTORES      = 3;  // sólo directores
     * const V_RESERVADO       = 4;  // sólo directores, añade no ver a los directores de otras oficinas no implicadas
     * const V_RESERVADO_VCD   = 5;  // sólo vcd
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
        // director de la oficina principal.
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
        $directores['dtor_pral'] = [
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
        $directores['dtor_imp'] = [
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
        $directores['dtor'] = [
                    'asunto'    => 1,
                    'detalle'   => 1,
                    'escrito'   => 1,
                    'cambio'    => 0,
        ];
        $reservado['dtor'] = [
                    'asunto'    => 1,
                    'detalle'   => 0,
                    'escrito'   => 0,
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
        $directores['secretario'] = [
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
                    'asunto'    => 2,
                    'detalle'   => 2,
                    'escrito'   => 2,
                    'cambio'    => 4,
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
                    'cambio'    => 4,
        ];
        $directores['of_scl'] = [
                    'asunto'    => 0,
                    'detalle'   => 0,
                    'escrito'   => 0,
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
        $directores['of_pral'] = [
                    'asunto'    => 0,
                    'detalle'   => 0,
                    'escrito'   => 0,
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
                    'detalle'   => 0,
                    'escrito'   => 0,
                    'cambio'    => 0,
        ];
        $directores['of_imp'] = [
                    'asunto'    => 0,
                    'detalle'   => 0,
                    'escrito'   => 0,
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
        $directores['of'] = [
                    'asunto'    => 0,
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
        $this->array_registro_perm[Entrada::V_DIRECTORES] = $directores;
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
        $role_actual = ConfigGlobal::role_actual();
        $id_oficina_pral = '';
        $id_oficina_role = '';
        // El role de secretaria no tiene oficina
        if ($role_actual == 'secretaria') {
            // miera el usuario actual, no el role.
            $soy_dtor = ConfigGlobal::soy_dtor();
        } else {
            $id_oficina_role = ConfigGlobal::role_id_oficina();
            $id_cargo_role = ConfigGlobal::role_id_cargo();
            $oCargo = new Cargo($id_cargo_role);
            $soy_dtor = $oCargo->getDirector();
        }
        
        
        $visibilidad = $objeto->getVisibilidad();
        $visibilidad = empty($visibilidad)? Entrada::V_TODOS : $visibilidad;
        // Entradas es por oficinas, Escritos por cargos
        $classname = get_class($objeto);
        $clase = substr($classname, strrpos($classname, '\\') + 1);
        
        $a_oficinas = [];
        if ($clase === 'Entrada' || $clase === 'Pendiente') {
            $id_oficina_pral = $objeto->getPonente();
            $a_oficinas = $objeto->getResto_oficinas();
        }

        if ($clase === 'Escrito') {
            // Sólo afecta a los que tengan fecha de aprobación:
            if (empty($objeto->getF_aprobacion()->getIso())) {
                return self::PERM_MODIFICAR; 
            } else {
                $id_ponente = $objeto->getPonente();
                $resto_cargos = $objeto->getResto_oficinas();
                // pasar cargos a oficinas:
                $oCargoP = new Cargo($id_ponente);
                $id_oficina_pral = $oCargoP->getId_oficina();
                foreach ($resto_cargos as $id_cargo) {
                    $oCargo = new Cargo($id_cargo);
                    $a_oficinas[] = $oCargo->getId_oficina();
                }
            }
        }
        
        $soy = empty($soy_dtor)? 'of' : 'dtor';
        switch ($role_actual) {
            case 'secretaria':
                $soy = empty($soy_dtor)? 'of_scl' : 'secretario';
                break;
            case 'vcd':
                $soy = 'dtor_pral';
                break;
            default:
                if (in_array($id_oficina_role, $a_oficinas)) {
                    $soy = empty($soy_dtor)? 'of_imp' : 'dtor_imp';
                }
                if ($id_oficina_role == $id_oficina_pral) {
                    $soy = empty($soy_dtor)? 'of_pral' : 'dtor_pral';
                }
        }
        
        if (!isset($this->array_registro_perm[$visibilidad][$soy][$que])) {
            echo "NO encuentro permiso para: visibilidad: $visibilidad, soy: $soy, que: $que<br>";
        }
        $permiso = $this->array_registro_perm[$visibilidad][$soy][$que];
        return $permiso;
    }
    
}