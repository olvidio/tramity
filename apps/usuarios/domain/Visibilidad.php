<?php

namespace usuarios\domain;

use core\ConfigGlobal;
use usuarios\domain\entity\Cargo;
use usuarios\domain\repositories\CargoRepository;

class Visibilidad
{


    /* CONST -------------------------------------------------------------- */

    // visibilidad
    public const V_TODOS = 1;  // cualquiera
    public const V_PERSONAL = 2;  // oficina y directores
    public const V_DIRECTORES = 3;  // sólo directores
    public const V_RESERVADO = 4;  // sólo directores, añade no ver a los directores de otras oficinas no implicadas
    public const V_RESERVADO_VCD = 5;  // sólo vcd + quien señale

    // visibilidad_ctr
    public const V_CTR_TODOS = 1; // cualquiera
    public const V_CTR_DTOR = 7; // d
    public const V_CTR_DTOR_SACD = 8; // d y sacd


    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    public function getArrayCondVisibilidad(): array
    {
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            // visibilidad:
            $a_visibilidad[] = self::V_CTR_TODOS;
            $id_cargo = ConfigGlobal::role_id_cargo();
            $CargoRepository = new CargoRepository();
            $oCargo = $CargoRepository->findById($id_cargo);
            if ($oCargo === null) {
                exit(_("No Existe!!!"));
            }
            if ($oCargo->isDirector()) {
                $a_visibilidad[] = self::V_CTR_DTOR;
                $a_visibilidad[] = self::V_CTR_DTOR_SACD;
            }
            if ($oCargo->isSacd()) {
                $a_visibilidad[] = self::V_CTR_DTOR_SACD;
            }
        } else {
            $a_visibilidad = [];
        }
        return $a_visibilidad;
    }

    public function getArrayVisibilidad($limitar_por_usuario = FALSE): array
    {
        $a_visibilidad = [];
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            $a_visibilidad = $this->getArrayVisibilidadCtr($limitar_por_usuario);
        }
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            $a_visibilidad = $this->getArrayVisibilidadDl();
        }

        return $a_visibilidad;
    }

    public function getArrayVisibilidadCtr($limitar_por_usuario = FALSE): array
    {
        $a_visibilidad[self::V_CTR_TODOS] = _("todos");
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            if ($limitar_por_usuario) {
                if (ConfigGlobal::soy_dtor()) {
                    $a_visibilidad[self::V_CTR_DTOR] = _("d");
                    $a_visibilidad[self::V_CTR_DTOR_SACD] = _("d y sacd");
                }
                if (ConfigGlobal::soy_sacd()) {
                    $a_visibilidad[self::V_CTR_DTOR_SACD] = _("d y sacd");
                }
            } else {
                $a_visibilidad[self::V_CTR_DTOR] = _("d");
                $a_visibilidad[self::V_CTR_DTOR_SACD] = _("d y sacd");
            }
        } else {
            $a_visibilidad[self::V_CTR_DTOR] = _("d");
            $a_visibilidad[self::V_CTR_DTOR_SACD] = _("d y sacd");
        }
        return $a_visibilidad;
    }

    public function getArrayVisibilidadDl(): array
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
    