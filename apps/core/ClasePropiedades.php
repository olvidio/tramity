<?php

namespace core;
abstract class ClasePropiedades
{
    /**
     * oDbl de ClasePropiedades
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de ClasePropiedades
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * ErrorTxt de ClasePropiedades
     *
     * @var string
     */
    protected $sErrorTxt;
    /* MÉTODOS GET y SET --------------------------------------------------------*/


    /**
     * Recupera l'atribut iid_schema
     *
     * @return integer iid_schema
     */
    function getId_schema()
    {
        if (!isset($this->iid_schema) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_schema;
    }

    /**
     *
     *
     * @param integer iid_schema='' optional
     */
    function setId_schema($iid_schema = '')
    {
        $this->iid_schema = $iid_schema;
    }

    /**
     * Recupera l'atribut oDbl de ClasePropiedades
     *
     * @return object oDbl
     */
    public function getoDbl()
    {
        return $this->oDbl;
    }

    /**
     * El faig public per quan s'ha de copiar dades d'un esquema a un altre.
     *
     * @param object oDbl
     */
    public function setoDbl($oDbl)
    {
        $this->oDbl = $oDbl;
    }

    /**
     * Recupera l'atribut sNomTabla de ClasePropiedades
     *
     * @return string sNomTabla
     */
    public function getNomTabla()
    {
        return $this->sNomTabla;
    }

    public function __get($nombre)
    {
        $metodo = 'get' . ucfirst($nombre);
        if (method_exists($this, $metodo)) {
            return $this->$metodo();
        }
    }

    public function __set($nombre, $valor)
    {
        $metodo = 'set' . ucfirst($nombre);
        if (method_exists($this, $metodo)) {
            $this->$metodo($valor);
        }
    }

    /**
     * sErrorTxt
     * @return string
     */
    public function getErrorTxt()
    {
        return $this->sErrorTxt;
    }

    /**
     * sErrorTxt
     * @param string $sErrorTxt
     * @return ClasePropiedades
     */
    public function setErrorTxt($sErrorTxt)
    {
        $this->sErrorTxt = $sErrorTxt;
        return $this;
    }

    /**
     * @param string sNomTabla
     */
    protected function setNomTabla($sNomTabla)
    {
        $this->sNomTabla = $sNomTabla;
    }

}