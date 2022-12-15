<?php

namespace entradas\model;

use core\ConfigDB;
use core\DBConnection;
use entradas\domain\repositories\EntradaDocDBRepository;
use web\StringLocal;

class EntradaEntidadDocRepository extends EntradaDocDBRepository
{

    /**
     * Constructor de la classe.
     * Se l'hi ha de dir a quin esquema s'ha de conectar.
     *
     * @param string $entidad . Nombre de la nombre_entidad donde hay que crear la entradaDocDB.
     */
    function __construct(int $id_entrada, string $entidad)
    {
        // El nombre del esquema es en minúsculas porque si se accede via nombre del
        // servidor, éste está en minúscula (agdmontagut.tramity.local)
        // http://www.ietf.org/rfc/rfc2616.txt: Field names are case-insensitive.
        $schema = strtolower($entidad);
        // también lo normalizo:
        $schema = StringLocal::toRFC952($schema);

        $oConfigDB = new ConfigDB('tramity');
        $config = $oConfigDB->getEsquema($schema);
        $oConexion = new DBConnection($config);
        $oDbl = $oConexion->getPDO();

        $this->iid_entrada = $id_entrada;

        $this->setoDbl($oDbl);
        $this->setNomTabla('entrada_doc');
    }

}