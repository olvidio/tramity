<?php

namespace core;

use PDO;

abstract class ClaseRepository
{
    /**
     * oDbl de ClasePropiedades
     *
     * @var PDO
     */
    protected PDO $oDbl;
    /**
     * NomTabla de ClasePropiedades
     *
     * @var string
     */
    protected string $sNomTabla = '';
    /**
     * ErrorTxt de ClasePropiedades
     *
     * @var string
     */
    protected string $sErrorTxt;

    protected int $iid_schema;

    /**
     *
     *
     * @param integer $iid_schema
     */
    public function setId_schema(int $iid_schema): void
    {
        $this->iid_schema = $iid_schema;
    }

    /**
     * Recupera el atributo oDbl de ClaseRepository
     *
     * @return PDO $oDbl
     */
    public function getoDbl(): PDO
    {
        return $this->oDbl;
    }

    /**
     * El faig public per quan s'ha de copiar dades d'un esquema a un altre.
     *
     * @param PDO $oDbl
     */
    public function setoDbl(PDO $oDbl): void
    {
        $this->oDbl = $oDbl;
    }

    /**
     *
     * @return string $sNomTabla
     */
    public function getNomTabla(): string
    {
        return $this->sNomTabla;
    }

    /**
     * @param string $sNomTabla
     */
    public function setNomTabla(string $sNomTabla): void
    {
        $this->sNomTabla = $sNomTabla;
    }

    /**
     * sErrorTxt
     * @return string
     */
    public function getErrorTxt(): string
    {
        return $this->sErrorTxt;
    }

    /*
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
    */

    /**
     * Recupera el atributo iid_schema
     *
     * @return integer $iid_schema
     */
    protected function getId_schema(): int
    {
        return $this->iid_schema;
    }

    /**
     * sErrorTxt
     * @param string $sErrorTxt
     * @return ClaseRepository
     */
    protected function setErrorTxt(string $sErrorTxt): static
    {
        $this->sErrorTxt = $sErrorTxt;
        return $this;
    }

    /**
     * Serveix per juntar en un conjunt una serie de col·leccions separades
     *
     * @param array $a_Clases nom de les classes
     * @param string $namespace nom del namespace
     * @param array $aWhere associatiu amb els valors de les variables amb les quals farem la query
     * @param array $aOperators aOperators associate amb els valors dels operadors que cal aplicar a cada variable
     */
    protected function getConjunt(array $a_Clases, string $namespace, array $aWhere, array $aOperators)
    {
        $cClassesTot = [];

        $paraOrdenar = '';
        if (isset($aWhere['_ordre']) && $aWhere['_ordre'] !== '') {
            $paraOrdenar = $aWhere['_ordre'];
            unset($aWhere['_ordre']);
        }
        foreach ($a_Clases as $aClasse) {
            $Classe = $aClasse['clase'];
            $get = $aClasse['get'];

            $a_ord[$Classe] = array();
            $a_ord_cond[$Classe] = array();
            $Gestor = $namespace . '\Gestor' . $Classe;
            $oGesClasse = new $Gestor;
            $cClasses = $oGesClasse->$get($aWhere, $aOperators);
            if (is_array($cClasses)) {
                $cClassesTot = array_merge($cClassesTot, $cClasses);
            }
        }

        //ordenar
        if (!empty($paraOrdenar)) {
            $a_ordre = explode(',', $paraOrdenar);
            foreach ($cClassesTot as $key_c => $oClass) {
                $get = '';
                foreach ($a_ordre as $key_o => $ordre) {
                    //comprobar que en $ordre está sólo el campo. Puede tener parametros: ASC, DESC
                    $aa_ordre = explode(' ', $ordre);
                    $ordreCamp = $aa_ordre[0];
                    $get = 'get' . ucfirst($ordreCamp);
                    $a_ord[$key_o][$key_c] = strtolower($oClass->$get());
                    $a_ord_cond[$key_o] = SORT_ASC;
                    if (count($aa_ordre) > 1 && $aa_ordre[1] === 'DESC') {
                        $a_ord_cond[$key_o] = SORT_DESC;
                    }
                }
            }
            $multisort_args = [];
            foreach ($a_ordre as $key_o => $ordre) {
                if (!empty($a_ord[$key_o])) {
                    $multisort_args[] = $a_ord[$key_o];
                    $multisort_args[] = $a_ord_cond[$key_o];
                    $multisort_args[] = SORT_STRING;
                }
            }
            $multisort_args[] = &$cClassesTot;   // finally add the source array, by reference
            call_user_func_array("array_multisort", $multisort_args);
        }
        return $cClassesTot;
    }


}