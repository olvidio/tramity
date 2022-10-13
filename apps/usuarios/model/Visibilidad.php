<?php

namespace usuarios\model;

use core\ConfigGlobal;
use usuarios\model\entity\Cargo;
use function core\is_true;

class Visibilidad
{


    /* CONST -------------------------------------------------------------- */

    // visibilidad
    const V_TODOS = 1;  // cualquiera
    const V_PERSONAL = 2;  // oficina y directores
    const V_DIRECTORES = 3;  // sólo directores
    const V_RESERVADO = 4;  // sólo directores, añade no ver a los directores de otras oficinas no implicadas
    const V_RESERVADO_VCD = 5;  // sólo vcd + quien señale

    // visibilidad_ctr
    const V_CTR_TODOS = 1; // cualquiera
    const V_CTR_DTOR = 7; // d
    const V_CTR_DTOR_SACD = 8; // d y sacd


    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    public function getArrayCondVisibilidad()
    {
        if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
            // visibilidad:
            $a_visibilidad[] = self::V_CTR_TODOS;
            $id_cargo = ConfigGlobal::role_id_cargo();
            $oCargo = new Cargo($id_cargo);
            $dtor = $oCargo->getDirector();
            $sacd = $oCargo->getSacd();
            if (is_true($dtor)) {
                $a_visibilidad[] = self::V_CTR_DTOR;
                $a_visibilidad[] = self::V_CTR_DTOR_SACD;
            }
            if (is_true($sacd)) {
                $a_visibilidad[] = self::V_CTR_DTOR_SACD;
            }
        } else {
            $a_visibilidad = [];
        }
        return $a_visibilidad;
    }

    public function getArrayVisibilidad()
    {
        $a_visibilidad = [];
        if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {
            $a_visibilidad = $this->getArrayVisibilidadCtr();
        }
        if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_DL) {
            $a_visibilidad = $this->getArrayVisibilidadDl();
        }

        return $a_visibilidad;
    }

    public function getArrayVisibilidadCtr()
    {
        return [
            self::V_CTR_TODOS => _("todos"),
            self::V_CTR_DTOR => _("d"),
            self::V_CTR_DTOR_SACD => _("d y sacd"),
        ];
    }

    public function getArrayVisibilidadDl()
    {
        return [
            self::V_TODOS => _("todos"),
            self::V_PERSONAL => _("personal"),
            self::V_DIRECTORES => _("directores"),
            self::V_RESERVADO => _("reservado"),
            self::V_RESERVADO_VCD => _("vcd"),
        ];
    }

}
    