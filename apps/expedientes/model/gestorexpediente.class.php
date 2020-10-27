<?php
namespace expedientes\model;


use expedientes\model\entity\GestorExpedienteDB;


class GestorExpediente Extends GestorExpedienteDB {
    
    
    /**
     * retorna l'array d'objectes de tipus expedienteDB
     *
     * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
     * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
     * @return array Una col·lecció d'objectes de tipus expedienteDB
     */
    function getExpedientes($aWhere=array(),$aOperators=array()) {
        return parent::getExpedientesDB($aWhere,$aOperators);    
    }
}