<?php

namespace expedientes\model;

use core\ViewTwig;

class DialogoBusquedaCirculando
{
    private bool $resto_sel;
    private string $filtro;

    private string $chk_resto;
    private string $chk_propio;


    /**
     * @param bool $resto_sel
     * @param string $filtro
     */
    public function __construct(bool $resto_sel, string $filtro)
    {
        $this->resto_sel = $resto_sel;
        $this->filtro = $filtro;
    }


    public function generarCondicion()
    {
        if ($this->resto_sel === FALSE) {
            $this->chk_resto = '';
            $this->chk_propio = 'checked';
        } else {
            $this->chk_resto = 'checked';
            $this->chk_propio = '';
        }

        return ['success' => TRUE];
    }

    public function mostrarDialogo(): void
    {
        $a_campos = [
            'filtro' => $this->filtro,
            'chk_resto' => $this->chk_resto,
            'chk_propio' => $this->chk_propio,
        ];

        $oView = new ViewTwig('expedientes/controller');
        $oView->renderizar('expedientes_circulando_buscar.html.twig', $a_campos);
    }
}