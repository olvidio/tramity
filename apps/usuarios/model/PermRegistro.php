<?php

namespace usuarios\model;

use core\ConfigGlobal;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;

class PermRegistro
{

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

    public const PERM_NADA = 0;
    public const PERM_VER = 1;
    public const PERM_MODIFICAR = 2;
    public const PERM_CAMBIAR = 4;


    private array $array_registro_perm = [];

    public function __construct()
    {
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            $this->init();
        } else {
            $this->init_ctr();
        }
    }

    private function init_ctr(): void
    {
        $todos = [];
        $director = [];
        $director_sacd = [];

        // director.
        $todos['dtor'] = [
            'asunto' => 2,
            'detalle' => 2,
            'escrito' => 2,
            'cambio' => 4,
        ];
        $director['dtor'] = [
            'asunto' => 2,
            'detalle' => 2,
            'escrito' => 2,
            'cambio' => 4,
        ];
        $director_sacd['dtor'] = [
            'asunto' => 2,
            'detalle' => 2,
            'escrito' => 2,
            'cambio' => 4,
        ];
        // sacd
        $todos['sacd'] = [
            'asunto' => 1,
            'detalle' => 1,
            'escrito' => 1,
            'cambio' => 0,
        ];
        $director['sacd'] = [
            'asunto' => 1,
            'detalle' => 1,
            'escrito' => 1,
            'cambio' => 0,
        ];
        $director_sacd['sacd'] = [
            'asunto' => 1,
            'detalle' => 1,
            'escrito' => 1,
            'cambio' => 0,
        ];
        // resto.
        $todos['resto'] = [
            'asunto' => 2,
            'detalle' => 2,
            'escrito' => 2,
            'cambio' => 0,
        ];
        $director['resto'] = [
            'asunto' => 0,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];
        $director_sacd['resto'] = [
            'asunto' => 0,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];

        $this->array_registro_perm[Visibilidad::V_CTR_TODOS] = $todos;
        $this->array_registro_perm[Visibilidad::V_CTR_DTOR] = $director;
        $this->array_registro_perm[Visibilidad::V_CTR_DTOR_SACD] = $director_sacd;

    }

    private function init(): void
    {
        $todos = [];
        $personal = [];
        $reservado = [];
        $vcd = [];
        // director de la oficina principal.
        $todos['dtor_pral'] = [
            'asunto' => 1,
            'detalle' => 2,
            'escrito' => 1,
            'cambio' => 4,
        ];
        $personal['dtor_pral'] = [
            'asunto' => 1,
            'detalle' => 2,
            'escrito' => 1,
            'cambio' => 4,
        ];
        $directores['dtor_pral'] = [
            'asunto' => 1,
            'detalle' => 2,
            'escrito' => 1,
            'cambio' => 4,
        ];
        $reservado['dtor_pral'] = [
            'asunto' => 1,
            'detalle' => 2,
            'escrito' => 1,
            'cambio' => 4,
        ];
        $vcd['dtor_pral'] = [
            'asunto' => 2,
            'detalle' => 2,
            'escrito' => 2,
            'cambio' => 4,
        ];
        // directores de las oficinas implicadas.
        $todos['dtor_imp'] = [
            'asunto' => 1,
            'detalle' => 1,
            'escrito' => 1,
            'cambio' => 0,
        ];
        $personal['dtor_imp'] = [
            'asunto' => 1,
            'detalle' => 1,
            'escrito' => 1,
            'cambio' => 0,
        ];
        $directores['dtor_imp'] = [
            'asunto' => 1,
            'detalle' => 1,
            'escrito' => 1,
            'cambio' => 0,
        ];
        $reservado['dtor_imp'] = [
            'asunto' => 1,
            'detalle' => 1,
            'escrito' => 1,
            'cambio' => 0,
        ];
        $vcd['dtor_imp'] = [
            'asunto' => 1,
            'detalle' => 1,
            'escrito' => 1,
            'cambio' => 0,
        ];
        // resto de directores.
        $todos['dtor'] = [
            'asunto' => 1,
            'detalle' => 1,
            'escrito' => 1,
            'cambio' => 0,
        ];
        $personal['dtor'] = [
            'asunto' => 1,
            'detalle' => 1,
            'escrito' => 1,
            'cambio' => 0,
        ];
        $directores['dtor'] = [
            'asunto' => 1,
            'detalle' => 1,
            'escrito' => 1,
            'cambio' => 0,
        ];
        $reservado['dtor'] = [
            'asunto' => 1,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];
        $vcd['dtor'] = [
            'asunto' => 0,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];
        // secretario de la dl.
        $todos['secretario'] = [
            'asunto' => 2,
            'detalle' => 2,
            'escrito' => 2,
            'cambio' => 4,
        ];
        $personal['secretario'] = [
            'asunto' => 2,
            'detalle' => 2,
            'escrito' => 2,
            'cambio' => 4,
        ];
        $directores['secretario'] = [
            'asunto' => 2,
            'detalle' => 2,
            'escrito' => 2,
            'cambio' => 4,
        ];
        $reservado['secretario'] = [
            'asunto' => 2,
            'detalle' => 2,
            'escrito' => 2,
            'cambio' => 4,
        ];
        $vcd['secretario'] = [
            'asunto' => 2,
            'detalle' => 2,
            'escrito' => 2,
            'cambio' => 4,
        ];
        // oficiales de secretaría.
        $todos['of_scl'] = [
            'asunto' => 2,
            'detalle' => 2,
            'escrito' => 2,
            'cambio' => 4,
        ];
        $personal['of_scl'] = [
            'asunto' => 2,
            'detalle' => 2,
            'escrito' => 2,
            'cambio' => 4,
        ];
        $directores['of_scl'] = [
            'asunto' => 0,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];
        $reservado['of_scl'] = [
            'asunto' => 0,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];
        $vcd['of_scl'] = [
            'asunto' => 0,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];
        // oficiales de la oficina principal.
        $todos['of_pral'] = [
            'asunto' => 1,
            'detalle' => 2,
            'escrito' => 1,
            'cambio' => 0,
        ];
        $personal['of_pral'] = [
            'asunto' => 1,
            'detalle' => 2,
            'escrito' => 1,
            'cambio' => 0,
        ];
        $directores['of_pral'] = [
            'asunto' => 0,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];
        $reservado['of_pral'] = [
            'asunto' => 0,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];
        $vcd['of_pral'] = [
            'asunto' => 0,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];
        // oficiales de las oficinas implicadas.
        $todos['of_imp'] = [
            'asunto' => 1,
            'detalle' => 1,
            'escrito' => 1,
            'cambio' => 0,
        ];
        $personal['of_imp'] = [
            'asunto' => 1,
            'detalle' => 1,
            'escrito' => 1,
            'cambio' => 0,
        ];
        $directores['of_imp'] = [
            'asunto' => 0,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];
        $reservado['of_imp'] = [
            'asunto' => 0,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];
        $vcd['of_imp'] = [
            'asunto' => 0,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];
        // resto de oficiales.
        $todos['of'] = [
            'asunto' => 1,
            'detalle' => 1,
            'escrito' => 1,
            'cambio' => 0,
        ];
        $personal['of'] = [
            'asunto' => 1,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];
        $directores['of'] = [
            'asunto' => 0,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];
        $reservado['of'] = [
            'asunto' => 0,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];
        $vcd['of'] = [
            'asunto' => 0,
            'detalle' => 0,
            'escrito' => 0,
            'cambio' => 0,
        ];

        $this->array_registro_perm[Visibilidad::V_TODOS] = $todos;
        $this->array_registro_perm[Visibilidad::V_PERSONAL] = $personal;
        $this->array_registro_perm[Visibilidad::V_DIRECTORES] = $directores;
        $this->array_registro_perm[Visibilidad::V_RESERVADO] = $reservado;
        $this->array_registro_perm[Visibilidad::V_RESERVADO_VCD] = $vcd;

    }

    public function isVisibleDtor(int $visibilidad): bool
    {
        $rta = FALSE;
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            $soy_dtor = ConfigGlobal::soy_dtor();
            if (($visibilidad === Visibilidad::V_DIRECTORES ||
                    $visibilidad === Visibilidad::V_RESERVADO ||
                    $visibilidad === Visibilidad::V_RESERVADO_VCD
                )
                && $soy_dtor === FALSE) {
                $rta = FALSE;
            } else {
                $rta = TRUE;
            }
        }

        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR
            || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO) {
            $soy_dtor = ConfigGlobal::soy_dtor();
            $soy_sacd = ConfigGlobal::soy_sacd();
            if ($visibilidad === Visibilidad::V_CTR_DTOR && $soy_dtor) {
                $rta = TRUE;
            }
            if ($visibilidad === Visibilidad::V_CTR_DTOR_SACD && ($soy_dtor || $soy_sacd)) {
                $rta = TRUE;
            }
            if ($visibilidad === Visibilidad::V_CTR_TODOS) {
                $rta = TRUE;
            }
        }

        return $rta;
    }


    /**
     * Función para buscar el permiso para ver el asunto, detalle o escrito
     * de una entrada o escrito o pendiente según quien sea yo.
     *
     * @param object $objeto (oEntrada|oEscrito|oPendiente|oExpediente)
     * @param string $que (asunto|detalle|escrito|cambio)
     * @return integer
     */
    public function permiso_detalle($objeto, string $que): int
    {

        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            $rta = $this->permiso_detalle_dl($objeto, $que);
        } else {
            $rta = $this->permiso_detalle_ctr($objeto, $que);
        }

        return $rta;
    }


    /**
     * Para el ámbito CTR:
     * Función para buscar el permiso para ver el asunto, detalle o escrito
     * de una entrada o escrito o pendiente según quien sea yo.
     *
     * @param  object $objeto (oEscrito|oPendiente|oExpediente|oEntrada)
     * @param string $que (asunto|detalle|escrito|cambio)
     * @return integer
     */
    private function permiso_detalle_ctr($objeto, string $que): int
    {

        $id_cargo_role = ConfigGlobal::role_id_cargo();
        $oCargo = new Cargo($id_cargo_role);
        $soy_dtor = $oCargo->getDirector();
        $soy_sacd = $oCargo->getSacd();

        $visibilidad = $objeto->getVisibilidad();
        $visibilidad = empty($visibilidad) ? Visibilidad::V_TODOS : $visibilidad;


        $soy = empty($soy_dtor) ? 'resto' : 'dtor';
        $soy = empty($soy_sacd) ? $soy : 'sacd';

        if (!isset($this->array_registro_perm[$visibilidad][$soy][$que])) {
            echo "NO encuentro permiso para: visibilidad: $visibilidad, soy: $soy, que: $que<br>";
        }

        return $this->array_registro_perm[$visibilidad][$soy][$que];
    }

    /**
     * Para el ámbito DL:
     * Función para buscar el permiso para ver el asunto, detalle o escrito
     * de una entrada o escrito o pendiente según quien sea yo.
     *
     * @param object $object (oEntrada|oEscrito|oPendiente|oExpediente)
     * @param string $que (asunto|detalle|escrito|cambio)
     * @return integer
     */
    private function permiso_detalle_dl($objeto, string $que): int
    {
        $role_actual = ConfigGlobal::role_actual();
        $id_oficina_pral = '';
        $id_oficina_role = '';
        // El role de secretaria no tiene oficina
        if ($role_actual === 'secretaria') {
            // mira el usuario actual, no el role.
            $soy_dtor = ConfigGlobal::soy_dtor();
        } else {
            $id_oficina_role = ConfigGlobal::role_id_oficina();
            $id_cargo_role = ConfigGlobal::role_id_cargo();
            $oCargo = new Cargo($id_cargo_role);
            if ($oCargo->DBCargar()) {
                // Asegurar que existe el cargo
                $soy_dtor = $oCargo->getDirector();
            }
        }


        $visibilidad = $objeto->getVisibilidad();
        $visibilidad = empty($visibilidad) ? Visibilidad::V_TODOS : $visibilidad;
        // Entradas es por oficinas, Escritos por cargos
        $classname = get_class($objeto);
        $clase = substr($classname, strrpos($classname, '\\') + 1);

        $a_oficinas = [];
        if ($clase === 'Entrada' || $clase === 'Pendiente') {
            $id_oficina_pral = $objeto->getPonente();
            $a_oficinas = $objeto->getResto_oficinas();
        }

        if ($clase === 'Escrito' || $clase === 'Expediente') {
            // Sólo afecta a los que tengan fecha de aprobación:
            if (empty($objeto->getF_aprobacion()->getIso())) {
                return self::PERM_MODIFICAR;
            }

            $resto_cargos = $objeto->getResto_oficinas();
            // pasar cargos a oficinas:
            $id_ponente = $objeto->getPonente();
            $oCargoP = new Cargo($id_ponente);
            if ($oCargoP->DBCargar()) {
                // asegurar que existe el cargo
                $id_oficina_pral = $oCargoP->getId_oficina();
            }
            foreach ($resto_cargos as $id_cargo) {
                $oCargo = new Cargo($id_cargo);
                if ($oCargo->DBCargar()) {
                    // asegurar que existe el cargo
                    $a_oficinas[] = $oCargo->getId_oficina();
                }
            }
        }

        $soy = empty($soy_dtor) ? 'of' : 'dtor';
        switch ($role_actual) {
            case 'secretaria':
                $soy = empty($soy_dtor) ? 'of_scl' : 'secretario';
                break;
            case 'vcd':
                $soy = 'dtor_pral';
                break;
            default:
                if (in_array($id_oficina_role, $a_oficinas, true)) {
                    $soy = empty($soy_dtor) ? 'of_imp' : 'dtor_imp';
                }
                if ($id_oficina_role === $id_oficina_pral) {
                    $soy = empty($soy_dtor) ? 'of_pral' : 'dtor_pral';
                }
        }
        // para el sd, como vcd excepto si la oficina es vcd.
        // Lo pongo fuera de switch para aprovechar el default.
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL && $role_actual === 'sd') {
            $gesCargo = new GestorCargo();
            $cCargos = $gesCargo->getCargos(['cargo' => 'vcd']);
            $id_oficina_vcd = $cCargos[0]->getId_oficina();
            if ($id_oficina_pral !== $id_oficina_vcd) {
                $soy = 'dtor_pral';
            }
        }

        if (!isset($this->array_registro_perm[$visibilidad][$soy][$que])) {
            echo "NO encuentro permiso para: visibilidad: $visibilidad, soy: $soy, que: $que<br>";
        }

        return $this->array_registro_perm[$visibilidad][$soy][$que];
    }

}