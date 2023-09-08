<?php

namespace entradas\model;

use core\ClaseGestor;
use core\Set;
use entradas\model\entity\EntradaDB;
use escritos\model\entity\EscritoDB;
use expedientes\model\entity\ExpedienteDB;
use web\Protocolo;
use function core\is_true;


class Convertir extends ClaseGestor
{

    /**
     * Constructor de la classe.
     *
     */
    function __construct($nom_tabla)
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla($nom_tabla);
    }

    public function expedientes()
    {
        $cantidad = 100;
        $anterior = 0;
        $aWhere = ['_ordre' => 'id_expediente',
            '_limit' => $cantidad,
            '_offset' => $anterior,
        ];

        $cExpedientes = $this->getAllExpedientes($aWhere);
        $num_filas = count($cExpedientes);
        while ($num_filas > 0) {
            $anterior += $num_filas;

            foreach ($cExpedientes as $oExpediente) {
                // antecedentes
                $aAntecedente_db = $oExpediente->getJson_antecedentes(TRUE);
                if (!empty($aAntecedente_db)) {
                    $aAntecedentes = [];
                    foreach ($aAntecedente_db as $a_antecedente) {
                        $id = (int)$a_antecedente['id'];
                        $tipo = (string)$a_antecedente['tipo'];

                        if (!empty($id)) {
                            $aAntecedentes[] = ['id' => $id, 'tipo' => $tipo];
                        }
                    }
                    $oExpediente->setJson_antecedentes($aAntecedentes);
                }

                // también json_preparar
                $aPreparar_db = $oExpediente->getJson_preparar(TRUE);
                if (!empty($aPreparar_db)) {
                    $aPreparar = [];
                    foreach ($aPreparar_db as $a_preparar) {
                        $id = (int)$a_preparar['id'];
                        $visto = (string)is_true($a_preparar['visto']) ? TRUE : FALSE;

                        if (!empty($id)) {
                            $aPreparar[] = ['id' => $id, 'visto' => $visto];
                        }
                    }
                    $oExpediente->setJson_preparar($aPreparar);
                }
                $oExpediente->DBGuardar();

            }

            $aWhere = ['_ordre' => 'id_expediente',
                '_limit' => $cantidad,
                '_offset' => $anterior,
            ];
            $cExpedientes = $this->getAllExpedientes($aWhere);
            $num_filas = count($cExpedientes);
        }
    }


    public function getAllExpedientes($aWhere)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oExpedienteDBSet = new Set();

        $sOrdre = '';
        $sLimit = '';
        $sOffset = '';
        if (isset($aWhere['_ordre']) && $aWhere['_ordre'] !== '') {
            $sOrdre = ' ORDER BY ' . $aWhere['_ordre'];
        }
        if (isset($aWhere['_ordre'])) {
            unset($aWhere['_ordre']);
        }
        if (isset($aWhere['_limit']) && $aWhere['_limit'] !== '') {
            $sLimit = ' LIMIT ' . $aWhere['_limit'];
        }
        if (isset($aWhere['_limit'])) {
            unset($aWhere['_limit']);
        }
        if (isset($aWhere['_offset']) && $aWhere['_offset'] != '') {
            $sOffset = ' OFFSET ' . $aWhere['_offset'];
        }
        if (isset($aWhere['_offset'])) {
            unset($aWhere['_offset']);
        }

        $sCondi = "WHERE  json_preparar != '[]' OR json_antecedentes != '[]'";
        $sQry = "SELECT * FROM $nom_tabla $sCondi " . $sOrdre . $sLimit . $sOffset;

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute()) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $a_pkey = array('id_expediente' => $aDades['id_expediente']);
            $oExpediente = new ExpedienteDB($a_pkey);
            $oExpedienteDBSet->add($oExpediente);
        }
        return $oExpedienteDBSet->getTot();
    }


    public function destino()
    {
        $cantidad = 100;
        $anterior = 0;
        $aWhere = ['_ordre' => 'id_escrito',
            '_limit' => $cantidad,
            '_offset' => $anterior,
        ];

        $cEscritos = $this->getAllEscritos($aWhere);
        $num_filas = count($cEscritos);
        while ($num_filas > 0) {
            $anterior += $num_filas;

            foreach ($cEscritos as $oEscrito) {
                $aProt_ref_db = $oEscrito->getJson_prot_ref(TRUE);
                if (!empty($aProt_ref_db)) {
                    $aProtRef = [];
                    foreach ($aProt_ref_db as $a_prot_ref) {
                        $id_lugar = $a_prot_ref['id_lugar'];
                        $prot_num = $a_prot_ref['num'];
                        $prot_any = $a_prot_ref['any'];
                        $prot_mas = $a_prot_ref['mas'];

                        if (!empty($id_lugar)) {
                            $oProtRef = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                            $aProtRef[] = $oProtRef->getProt();
                        }
                    }
                    $oEscrito->setJson_prot_ref($aProtRef);
                }

                // tambien json_prot_destino
                $aProt_dst_db = $oEscrito->getJson_prot_destino(TRUE);
                if (!empty($aProt_dst_db)) {
                    $aProtDst = [];
                    foreach ($aProt_dst_db as $a_prot_dst) {
                        $id_lugar = $a_prot_dst['id_lugar'];
                        $prot_num = $a_prot_dst['num'];
                        $prot_any = $a_prot_dst['any'];
                        $prot_mas = $a_prot_dst['mas'];

                        if (!empty($id_lugar)) {
                            $oProtDst = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                            $aProtDst[] = $oProtDst->getProt();
                        }
                    }
                    $oEscrito->setJson_prot_destino($aProtDst);
                }

                // tambien json_prot_local
                $prot_local = $oEscrito->getJson_prot_local(TRUE);

                if (!empty($prot_local['id_lugar'])) {
                    $id_lugar = $prot_local['id_lugar'];
                    $prot_num = $prot_local['num'];
                    $prot_any = $prot_local['any'];
                    $prot_mas = $prot_local['mas'];

                    $oProtLocal = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                    $prot_local = $oProtLocal->getProt();

                    $oEscrito->setJson_prot_local($prot_local);
                }

                $oEscrito->DBGuardar();

            }

            $aWhere = ['_ordre' => 'id_escrito',
                '_limit' => $cantidad,
                '_offset' => $anterior,
            ];
            $cEscritos = $this->getAllEscritos($aWhere);
            $num_filas = count($cEscritos);
        }
    }


    public function getAllEscritos($aWhere)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEscritoDBSet = new Set();

        $sOrdre = '';
        $sLimit = '';
        $sOffset = '';
        if (isset($aWhere['_ordre']) && $aWhere['_ordre'] !== '') {
            $sOrdre = ' ORDER BY ' . $aWhere['_ordre'];
        }
        if (isset($aWhere['_ordre'])) {
            unset($aWhere['_ordre']);
        }
        if (isset($aWhere['_limit']) && $aWhere['_limit'] !== '') {
            $sLimit = ' LIMIT ' . $aWhere['_limit'];
        }
        if (isset($aWhere['_limit'])) {
            unset($aWhere['_limit']);
        }
        if (isset($aWhere['_offset']) && $aWhere['_offset'] != '') {
            $sOffset = ' OFFSET ' . $aWhere['_offset'];
        }
        if (isset($aWhere['_offset'])) {
            unset($aWhere['_offset']);
        }

        $sQry = "SELECT * FROM $nom_tabla " . $sOrdre . $sLimit . $sOffset;

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute()) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $a_pkey = array('id_escrito' => $aDades['id_escrito']);
            $oEscrito = new EscritoDB($a_pkey);
            $oEscritoDBSet->add($oEscrito);
        }
        return $oEscritoDBSet->getTot();
    }

    public function ref()
    {
        $cantidad = 100;
        $anterior = 0;
        $aWhere = ['_ordre' => 'id_entrada',
            '_limit' => $cantidad,
            '_offset' => $anterior,
        ];

        $cEntradas = $this->getAllEntradas($aWhere);
        $num_filas = count($cEntradas);
        while ($num_filas > 0) {
            $anterior += $num_filas;

            foreach ($cEntradas as $oEntrada) {
                $aProt_ref_db = $oEntrada->getJson_prot_ref(TRUE);

                if (!empty($aProt_ref_db)) {
                    $aProtRef = [];
                    foreach ($aProt_ref_db as $a_prot_ref) {
                        $id_lugar = $a_prot_ref['id_lugar'];
                        $prot_num = $a_prot_ref['num'];
                        $prot_any = $a_prot_ref['any'];
                        $prot_mas = $a_prot_ref['mas'];

                        if (!empty($id_lugar)) {
                            $oProtRef = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                            $aProtRef[] = $oProtRef->getProt();
                        }
                    }
                    $oEntrada->setJson_prot_ref($aProtRef);
                }

                // tambien origen:
                $prot_origen = $oEntrada->getJson_prot_origen(TRUE);

                $id_lugar = $prot_origen['id_lugar'];
                $prot_num = $prot_origen['num'];
                $prot_any = $prot_origen['any'];
                $prot_mas = $prot_origen['mas'];

                if (!empty($id_lugar)) {
                    $oProtOrigen = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                    $prot_origen = $oProtOrigen->getProt();
                }
                $oEntrada->setJson_prot_origen($prot_origen);

                $oEntrada->DBGuardar();

            }

            $aWhere = ['_ordre' => 'id_entrada',
                '_limit' => $cantidad,
                '_offset' => $anterior,
            ];
            $cEntradas = $this->getAllEntradas($aWhere);
            $num_filas = count($cEntradas);
        }
    }


    public function getAllEntradas($aWhere)
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oEntradaDBSet = new Set();

        $sOrdre = '';
        $sLimit = '';
        $sOffset = '';
        if (isset($aWhere['_ordre']) && $aWhere['_ordre'] !== '') {
            $sOrdre = ' ORDER BY ' . $aWhere['_ordre'];
        }
        if (isset($aWhere['_ordre'])) {
            unset($aWhere['_ordre']);
        }
        if (isset($aWhere['_limit']) && $aWhere['_limit'] !== '') {
            $sLimit = ' LIMIT ' . $aWhere['_limit'];
        }
        if (isset($aWhere['_limit'])) {
            unset($aWhere['_limit']);
        }
        if (isset($aWhere['_offset']) && $aWhere['_offset'] != '') {
            $sOffset = ' OFFSET ' . $aWhere['_offset'];
        }
        if (isset($aWhere['_offset'])) {
            unset($aWhere['_offset']);
        }

        $sQry = "SELECT * FROM $nom_tabla " . $sOrdre . $sLimit . $sOffset;

        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute()) === FALSE) {
            $sClauError = 'GestorEntradaDB.llistar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDades) {
            $oEntradaDB = new EntradaDB($aDades['id_entrada']);
            $oEntradaDBSet->add($oEntradaDB);
        }
        return $oEntradaDBSet->getTot();
    }

}