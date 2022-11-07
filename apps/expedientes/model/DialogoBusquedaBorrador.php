<?php

namespace expedientes\model;

use core\ViewTwig;
use etiquetas\model\entity\GestorEtiqueta;
use etiquetas\model\entity\GestorEtiquetaExpediente;
use web\DesplegableArray;

class DialogoBusquedaBorrador
{

    private int $prioridad_sel;
    private string $andOr;
    private array $a_etiquetas_filtered;
    private string $filtro;

    private string $chk_resto;
    private string $chk_espera;
    private string $chk_and;
    private string $chk_or;


    /**
     * @param int $prioridad_sel
     * @param string $andOr
     * @param array $a_etiquetas
     * @param string $filtro
     */
    public function __construct(int $prioridad_sel, string $andOr, array $a_etiquetas, string $filtro)
    {
        $this->prioridad_sel = $prioridad_sel;
        $this->andOr = $andOr;
        $this->filtro = $filtro;
        $this->a_etiquetas_filtered = array_filter($a_etiquetas);
    }


    public function generarCondicion()
    {
        $aWhereADD = [];
        $aOperadorADD = [];
        $aWhereADD['prioridad'] = Expediente::PRIORIDAD_ESPERA;
        if ($this->prioridad_sel === Expediente::PRIORIDAD_ESPERA) {
            $aOperadorADD['prioridad'] = '=';
            $this->chk_espera = 'checked';
            $this->chk_resto = '';
        } else {
            $aOperadorADD['prioridad'] = '!=';
            $this->chk_resto = 'checked';
            $this->chk_espera = '';
        }

        $this->chk_or = ($this->andOr === 'OR') ? 'checked' : '';
        // por defecto 'AND':
        $this->chk_and = (($this->andOr === 'AND') || empty($this->andOr)) ? 'checked' : '';

        if (!empty($this->a_etiquetas_filtered)) {
            $gesEtiquetasExpediente = new GestorEtiquetaExpediente();
            $cExpedientes = $gesEtiquetasExpediente->getArrayExpedientes($this->a_etiquetas_filtered, $this->andOr);
            if (!empty($cExpedientes)) {
                $aWhereADD['id_expediente'] = implode(',', $cExpedientes);
                $aOperadorADD['id_expediente'] = 'IN';
            } else {
                // No hay ninguno. No importa el resto de condiciones
                $msg = _("No hay ningún expediente con estas etiquetas");
                return ['success' => FALSE, 'message' => $msg];
            }
        }

        return ['success' => TRUE, 'aWhereADD' => $aWhereADD, 'aOperadorADD' => $aOperadorADD];
    }

    public function mostrarDialogo(): void
    {

        $gesEtiquetas = new GestorEtiqueta();
        $cEtiquetas = $gesEtiquetas->getMisEtiquetas();
        $a_posibles_etiquetas = [];
        foreach ($cEtiquetas as $oEtiqueta) {
            $id_etiqueta = $oEtiqueta->getId_etiqueta();
            $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
            $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
        }

        $oArrayDesplEtiquetas = new DesplegableArray($this->a_etiquetas_filtered, $a_posibles_etiquetas, 'etiquetas');
        $oArrayDesplEtiquetas->setBlanco('t');
        $oArrayDesplEtiquetas->setAccionConjunto('fnjs_mas_etiquetas()');

        $a_campos = [
            'filtro' => $this->filtro,
            'chk_resto' => $this->chk_resto,
            'chk_espera' => $this->chk_espera,
            'chk_and' => $this->chk_and,
            'chk_or' => $this->chk_or,
            'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,

        ];

        $oView = new ViewTwig('expedientes/controller');
        $oView->renderizar('expedientes_espera_buscar.html.twig', $a_campos);

    }
}