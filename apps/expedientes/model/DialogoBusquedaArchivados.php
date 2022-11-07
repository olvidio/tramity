<?php

namespace expedientes\model;

use core\ViewTwig;
use DateInterval;
use etiquetas\model\entity\GestorEtiqueta;
use etiquetas\model\entity\GestorEtiquetaExpediente;
use web\DateTimeLocal;
use web\DesplegableArray;

class DialogoBusquedaArchivados
{

    private string $asunto;
    private string $andOr;
    private array $a_etiquetas_filtered;
    private string $filtro;
    private string $periodo;

    private string $chk_and;
    private string $chk_or;
    private string $sel_mes = '';
    private string $sel_mes_6 = '';
    private string $sel_any_1 = '';
    private string $sel_any_2 = '';
    private string $sel_siempre = '';


    /**
     * @param string $asunto
     * @param string $andOr
     * @param array $a_etiquetas
     * @param string $filtro
     * @param string $periodo
     */
    public function __construct(string $asunto, string $andOr, array $a_etiquetas, string $filtro, string $periodo)
    {
        $this->asunto = $asunto;
        $this->andOr = $andOr;
        $this->filtro = $filtro;
        $this->periodo = $periodo;
        $this->a_etiquetas_filtered = array_filter($a_etiquetas);
    }


    public function generarCondicion()
    {
        $aWhereADD = [];
        $aOperadorADD = [];

        $gesEtiquetas = new GestorEtiqueta();
        $a_posibles_etiquetas = $gesEtiquetas->getArrayMisEtiquetas();
        $oArrayDesplEtiquetas = new DesplegableArray($this->a_etiquetas_filtered, $a_posibles_etiquetas, 'etiquetas');
        $oArrayDesplEtiquetas->setBlanco('t');
        $oArrayDesplEtiquetas->setAccionConjunto('fnjs_mas_etiquetas()');

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
            }
        }

        if (!empty($this->asunto)) {
            $aWhereADD['asunto'] = $this->asunto;
            $aOperadorADD['asunto'] = 'sin_acentos';
        }
        $periodoInterval = '';
        switch ($this->periodo) {
            case "mes":
                $this->sel_mes = 'selected';
                $periodoInterval = 'P1M';
                break;
            case "mes_6":
                $this->sel_mes_6 = 'selected';
                $periodoInterval = 'P6M';
                break;
            case "any_1":
                $this->sel_any_1 = 'selected';
                $periodoInterval = 'P1Y';
                break;
            case "any_2":
                $this->sel_any_2 = 'selected';
                $periodoInterval = 'P2Y';
                break;
            case "siempre":
                $this->sel_siempre = 'selected';
                break;
            default:
                // no hace falta, ya se borran todas los $sel_ antes del switch
        }
        if (!empty($this->periodo) && !empty($periodoInterval)) {
            $oFecha = new DateTimeLocal();
            $oFecha->sub(new DateInterval($periodoInterval));
            $aWhereADD['f_aprobacion'] = $oFecha->getIso();
            $aOperadorADD['f_aprobacion'] = '>';
        }


        return ['aWhereADD' => $aWhereADD, 'aOperadorADD' => $aOperadorADD];
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
            'oArrayDesplEtiquetas' => $oArrayDesplEtiquetas,
            'chk_and' => $this->chk_and,
            'chk_or' => $this->chk_or,
            'asunto' => $this->asunto,
            'sel_mes' => $this->sel_mes,
            'sel_mes_6' => $this->sel_mes_6,
            'sel_any_1' => $this->sel_any_1,
            'sel_any_2' => $this->sel_any_2,
            'sel_siempre' => $this->sel_siempre,
        ];

        $oView = new ViewTwig('expedientes/controller');
        $oView->renderizar('archivados_buscar.html.twig', $a_campos);
    }
}