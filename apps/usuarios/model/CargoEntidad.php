<?php

namespace usuarios\model;

use core\ConfigDB;
use core\DBConnection;
use usuarios\model\entity\Cargo;
use web\StringLocal;


class CargoEntidad extends Cargo
{


    /* PROPIEDADES -------------------------------------------------------------- */


    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor
     * Hay que decirle a que esquema se tiene que conectar.
     *
     * @param string $entidad . Nombre de la nombre_entidad donde hay que crear la entrada.
     */
    function __construct(string $entidad)
    {
        parent::__construct();
        // El nombre del esquema es en minúsculas porque si se accede via nombre del
        // servidor, éste está en minúscula (agdmontagut.tramity.local)
        // http://www.ietf.org/rfc/rfc2616.txt: Field names are case-insensitive.
        // también lo normalizo:
        $schema = StringLocal::toRFC952($entidad);

        $oConfigDB = new ConfigDB('tramity'); //de la database común
        $config = $oConfigDB->getEsquema($schema);
        $oConexion = new DBConnection($config);
        $oDbl = $oConexion->getPDO();

        $this->setoDbl($oDbl);
        $this->setNomTabla('aux_cargos');
    }

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/


}