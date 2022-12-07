<?php

namespace entradas\model;

use core\ConfigDB;
use core\DBConnection;
use entradas\model\entity\EntradaAdjunto;
use web\StringLocal;


class EntradaEntidadAdjunto extends EntradaAdjunto
{

    /**
     * Constructor de la classe.
     * Se l'hi ha de dir a quin esquema s'ha de conectar.
     *
     * @param string $entidad . Nombre de la nombre_entidad donde hay que crear la entradaAdjunto.
     */
    function __construct($entidad)
    {
        // El nombre del esquema es en minúsculas porque si se accede via nombre del
        // servidor, éste está en minúscula (agdmontagut.tramity.local)
        // http://www.ietf.org/rfc/rfc2616.txt: Field names are case-insensitive.
        $schema = strtolower($entidad);
        // tambien lo normalizo:
        $schema = StringLocal::toRFC952($schema);

        $oConfigDB = new ConfigDB('tramity'); //de la database comun
        $config = $oConfigDB->getEsquema($schema);
        $oConexion = new DBConnection($config);
        $oDbl = $oConexion->getPDO();

        $this->setoDbl($oDbl);
        $this->setNomTabla('entrada_adjuntos');
    }

}