<?php
namespace documentos\model;

use documentos\model\entity\GestorDocumentoDB;

class GestorDocumento Extends GestorDocumentoDB {
    
    
    /**
     * retorna l'array d'objectes de tipus Documento
     *
     * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
     * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
     * @return array Una col·lecció d'objectes de tipus Expediente
     */
    function getDocumentos($aWhere=array(),$aOperators=array()) {
        return parent::getDocumentosDB($aWhere,$aOperators,TRUE);    
    }
}