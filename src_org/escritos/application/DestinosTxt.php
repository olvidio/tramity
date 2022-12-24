<?php

namespace escritos\application;

use escritos\domain\repositories\EscritoRepository;
use lugares\domain\repositories\LugarRepository;

class DestinosTxt
{

    public function __invoke(int $id_escrito)
    {
        $mensaje = '';
        $escritosRepository = new EscritoRepository();
        $oEscrito = $escritosRepository->findById($id_escrito);
        if ($oEscrito === null) {
            $mensaje = _("No encuentro");
        }
        $a_miembros = $oEscrito->getDestinosIds();
        $LugarRepository = new LugarRepository();
        $aLugares = $LugarRepository->getArrayLugares();
        $destinos_txt = '';
        foreach ($a_miembros as $id_lugar) {
            if (empty($aLugares[$id_lugar])) {
                continue;
            }
            $destinos_txt .= empty($destinos_txt) ? '' : "\n";
            $destinos_txt .= $aLugares[$id_lugar];
        }

        if (empty($mensaje)) {
            $jsondata['success'] = true;
            $jsondata['destinos'] = $destinos_txt;
        } else {
            $jsondata['success'] = false;
            $jsondata['mensaje'] = $mensaje;
        }

        return $jsondata;
    }
}