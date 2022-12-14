<?php

namespace documentos\domain\entity;

use core\ConfigGlobal;
use documentos\domain\repositories\EtiquetaDocumentoRepository;
use etiquetas\domain\repositories\EtiquetaRepository;
use expedientes\domain\repositories\ExpedienteRepository;

class Documento extends DocumentoDB
{

    /* CONST -------------------------------------------------------------- */

    // tipo doc  OJO también se usa para loas adjuntos.
    /*
    1 -> etherpad
	2 -> ethercalc
    3 -> otros
    */
    public const DOC_ETHERPAD = 1;
    public const DOC_ETHERCALC = 2;
    public const DOC_UPLOAD = 3;

    // visibilidad 
    /*
    - Personal
    - Oficina
    */
    public const V_PERSONAL = 1;
    public const V_OFICINA = 2;

    public function getArrayVisibilidad(): array
    {
        return [
            self::V_OFICINA => _("oficina"),
            self::V_PERSONAL => _("personal"),
        ];
    }

    public function getArrayTipos(): array
    {
        return [
            self::DOC_ETHERPAD => _("etherpad"),
            self::DOC_ETHERCALC => _("ethercalc"),
            self::DOC_UPLOAD => _("incrustado"),
        ];
    }


    /**
     * Comprueba si se puede eliminar el documento: Que no esté como antecedente en algún
     *     expediente.
     *
     * @param integer $id
     * @return string mensaje de error.
     */
    public function comprobarEliminar(int $id): string
    {
        $ExpedienteRepository = new ExpedienteRepository();
        $error_txt = '';
        $tipo = 'documento';
        $a_id_expedientes = $ExpedienteRepository->getIdExpedientesConAntecedente($id, $tipo);
        if (!empty($a_id_expedientes)) {
            $error_txt .= _("Este documento está como antecedente");
        }

        return $error_txt;
    }

    public function getEtiquetasVisiblesArray(int $id_cargo = null): array
    {
        $cEtiquetas = $this->getEtiquetasVisibles($id_cargo);
        $a_etiquetas = [];
        foreach ($cEtiquetas as $oEtiqueta) {
            $a_etiquetas[] = $oEtiqueta->getId_etiqueta();
        }
        return $a_etiquetas;
    }

    public function getEtiquetasVisibles(int $id_cargo = null): array
    {
        if ($id_cargo === null) {
            $id_cargo = ConfigGlobal::role_id_cargo();
        }
        $etiquetaRepository = new EtiquetaRepository();
        $cMisEtiquetas = $etiquetaRepository->getMisEtiquetas($id_cargo);
        $a_mis_etiquetas = [];
        foreach ($cMisEtiquetas as $oEtiqueta) {
            $a_mis_etiquetas[] = $oEtiqueta->getId_etiqueta();
        }
        $etiquetaDocumentoRepository = new EtiquetaDocumentoRepository();
        $aWhere = ['id_doc' => $this->iid_doc];
        $cEtiquetasExp = $etiquetaDocumentoRepository->getEtiquetasDocumento($aWhere);
        $cEtiquetas = [];
        foreach ($cEtiquetasExp as $oEtiquetaExp) {
            $id_etiqueta = $oEtiquetaExp->getId_etiqueta();
            if (in_array($id_etiqueta, $a_mis_etiquetas, true)) {
                $cEtiquetas[] = $etiquetaRepository->findById($id_etiqueta);
            }
        }

        return $cEtiquetas;
    }

    public function getEtiquetasVisiblesTxt(int $id_cargo = null): string
    {
        $cEtiquetas = $this->getEtiquetasVisibles($id_cargo);
        $str_etiquetas = '';
        foreach ($cEtiquetas as $oEtiqueta) {
            $str_etiquetas .= empty($str_etiquetas) ? '' : ', ';
            $str_etiquetas .= $oEtiqueta->getNom_etiqueta();
        }
        return $str_etiquetas;
    }

    public function getEtiquetas(): array
    {
        $etiquetaDocumentoRepository = new EtiquetaDocumentoRepository();
        $aWhere = ['id_doc' => $this->iid_doc];
        $cEtiquetasExp = $etiquetaDocumentoRepository->getEtiquetasDocumento($aWhere);
        $cEtiquetas = [];
        $etiquetaRepository = new EtiquetaRepository();
        foreach ($cEtiquetasExp as $oEtiquetaExp) {
            $id_etiqueta = $oEtiquetaExp->getId_etiqueta();
            $cEtiquetas[] = $etiquetaRepository->findById($id_etiqueta);
        }

        return $cEtiquetas;
    }

    public function setEtiquetas(array $aEtiquetas = []): void
    {
        $this->delEtiquetas();
        $a_filter_etiquetas = array_filter($aEtiquetas); // Quita los elementos vacíos y nulos.
        $etiquetaDocumentoRepository = new EtiquetaDocumentoRepository();
        foreach ($a_filter_etiquetas as $id_etiqueta) {
            $EtiquetaDocumento = new EtiquetaDocumento();
            $EtiquetaDocumento->setId_etiqueta($id_etiqueta);
            $EtiquetaDocumento->setId_doc($this->iid_doc);
            $etiquetaDocumentoRepository->Guardar($EtiquetaDocumento);
        }
    }

    public function delEtiquetas(): bool
    {
        $etiquetaDocumentoRepository = new EtiquetaDocumentoRepository();
        if ($etiquetaDocumentoRepository->deleteEtiquetasDocumento($this->iid_doc) === FALSE) {
            return FALSE;
        }
        return TRUE;
    }


}