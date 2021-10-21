<?php
namespace documentos\model;

use core\ConfigGlobal;
use documentos\model\entity\EtiquetaDocumento;
use documentos\model\entity\GestorEtiquetaDocumento;
use etiquetas\model\entity\Etiqueta;
use etiquetas\model\entity\GestorEtiqueta;
use documentos\model\entity\DocumentoDB;
use expedientes\model\GestorExpediente;

class Documento Extends DocumentoDB {
    
    /* CONST -------------------------------------------------------------- */
    
    // tipo doc  OJO también se usa para loas adjuntos.
    /*
    1 -> etherpad
	2 -> ethercalc
    3 -> otros
    */
    const DOC_ETHERPAD     = 1;
    const DOC_ETHERCALC    = 2;
    const DOC_UPLOAD       = 3;
    
    // visibilidad 
    /*
    - Personal
    - Oficina
    */
    const V_PERSONAL     = 1;
    const V_OFICINA      = 2;
    
    public function getArrayVisibilidad() {
        return [
            self::V_OFICINA => _("oficina"),
            self::V_PERSONAL => _("personal"),
        ];
    }
    
    public function getArrayTipos() {
        return [
            self::DOC_ETHERPAD => _("etherpad"),
            self::DOC_ETHERCALC => _("etheclac"),
            self::DOC_UPLOAD => _("incrustado"),
        ];
    }
    
    
    /**
     * Comprueba si se puede elimnar el documento: Que no esté como antecedente en algún
     *     expediente.
     *
     * @param integer $id
     * @return string mensaje de error.
     */
    public function comprobarEliminar($id){
        $gesExpedientes = new GestorExpediente();
        $error_txt = '';
        $tipo = 'documento';
        $a_id_expedientes = $gesExpedientes->getIdExpedientesConAntecedente($id, $tipo);
        if (!empty($a_id_expedientes)) {
            $error_txt .= _("Este documento está como antecedente");
        }
        
        return $error_txt;
    }
    
    public function getEtiquetasVisiblesArray($id_cargo='') {
        $cEtiquetas = $this->getEtiquetasVisibles($id_cargo);
        $a_etiquetas = [];
        foreach ($cEtiquetas as $oEtiqueta) {
            $a_etiquetas[] = $oEtiqueta->getId_etiqueta();
        }
        return $a_etiquetas;
    }
    
    public function getEtiquetasVisiblesTxt($id_cargo='') {
        $cEtiquetas = $this->getEtiquetasVisibles($id_cargo);
        $str_etiquetas = '';
        foreach ($cEtiquetas as $oEtiqueta) {
            $str_etiquetas .= empty($str_etiquetas)? '' : ', ';
            $str_etiquetas .= $oEtiqueta->getNom_etiqueta();
        }
        return $str_etiquetas;
    }
    
    public function getEtiquetasVisibles($id_cargo='') {
        if (empty($id_cargo)) {
            $id_cargo = ConfigGlobal::role_id_cargo();
        }
        $gesEtiquetas = new GestorEtiqueta();
        $cMisEtiquetas = $gesEtiquetas->getMisEtiquetas($id_cargo);
        $a_mis_etiquetas = [];
        foreach($cMisEtiquetas as $oEtiqueta) {
            $a_mis_etiquetas[] = $oEtiqueta->getId_etiqueta();
        }
        $gesEtiquetasDocumento = new GestorEtiquetaDocumento();
        $aWhere = [ 'id_doc' => $this->iid_doc ];
        $cEtiquetasExp = $gesEtiquetasDocumento->getEtiquetasDocumento($aWhere);
        $cEtiquetas = [];
        foreach ($cEtiquetasExp as $oEtiquetaExp) {
            $id_etiqueta = $oEtiquetaExp->getId_etiqueta();
            if (in_array($id_etiqueta, $a_mis_etiquetas)) {
                $cEtiquetas[] = new Etiqueta($id_etiqueta);
            }
        }
        
        return $cEtiquetas;
    }
    
    public function getEtiquetas() {
        $gesEtiquetasDocumento = new GestorEtiquetaDocumento();
        $aWhere = [ 'id_doc' => $this->iid_doc ];
        $cEtiquetasExp = $gesEtiquetasDocumento->getEtiquetasDocumento($aWhere);
        $cEtiquetas = [];
        foreach ($cEtiquetasExp as $oEtiquetaExp) {
            $id_etiqueta = $oEtiquetaExp->getId_etiqueta();
            $cEtiquetas[] = new Etiqueta($id_etiqueta);
        }
        
        return $cEtiquetas;
    }
    
    public function setEtiquetas($aEtiquetas){
        $this->delEtiquetas();
        $a_filter_etiquetas = array_filter($aEtiquetas); // Quita los elementos vacíos y nulos.
        foreach ($a_filter_etiquetas as $id_etiqueta) {
            $EtiquetaDocumento = new EtiquetaDocumento(['id_doc' => $this->iid_doc, 'id_etiqueta' => $id_etiqueta]);
            $EtiquetaDocumento->DBGuardar();
        }
    }
    
    public function delEtiquetas(){
        $gesEtiquetasDocumento = new GestorEtiquetaDocumento();
        if ($gesEtiquetasDocumento->deleteEtiquetasDocumento($this->iid_doc) === FALSE) {
            return FALSE;
        }
        return TRUE;
    }
    
    
}