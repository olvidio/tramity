<?php

namespace tramites\infrastructure;

use core\ClaseRepository;
use core\Condicion;
use core\ConfigGlobal;
use core\ConverterDate;
use core\Set;
use expedientes\model\Expediente;
use PDO;
use PDOException;
use tramites\domain\entity\Firma;
use tramites\domain\repositories\FirmaRepositoryInterface;
use tramites\domain\repositories\TramiteCargoRepository;
use usuarios\domain\entity\Cargo;
use usuarios\domain\repositories\CargoRepository;
use usuarios\domain\repositories\UsuarioRepository;


/**
 * Clase que adapta la tabla expediente_firmas a la interfaz del repositorio
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 7/12/2022
 */
class PgFirmaRepository extends ClaseRepository implements FirmaRepositoryInterface
{
    public function __construct()
    {
        $oDbl = $GLOBALS['oDBT'];
        $this->setoDbl($oDbl);
        $this->setNomTabla('expediente_firmas');
    }

    /* -------------------- GESTOR BASE ---------------------------------------- */

    public function Eliminar(Firma $Firma): bool
    {
        $id_item = $Firma->getId_item();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_item = $id_item")) === FALSE) {
            $sClaveError = 'Firma.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /* -------------------- ENTIDAD --------------------------------------------- */

    public function getNewId_item()
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval('expediente_firmas_id_item_seq'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }

    public function faltaFirmarReunionExpediente(int $id_expediente): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $estado = Expediente::ESTADO_FIJAR_REUNION;
        //orden_tramite para las firmas de reunion (corresponde a 'todos_d' del tramite);
        $oExpediente = new Expediente($id_expediente);
        $id_tramite = $oExpediente->getId_tramite();
        $TramiteCargoRepository = new TramiteCargoRepository();
        $cTramiteCargo = $TramiteCargoRepository->getTramiteCargos(['id_tramite' => $id_tramite, 'id_cargo' => Cargo::CARGO_TODOS_DIR]);
        if (!empty($cTramiteCargo)) {
            $orden_tramite = $cTramiteCargo[0]->getOrden_tramite();
        } else {
            return FALSE;
        }

        $tipo_voto = Firma::TIPO_VOTO;
        $valor_ok = Firma::V_OK;
        $valor_no = Firma::V_NO;

        $sQuery = "SELECT f.*
                    FROM $nom_tabla f JOIN expedientes e USING (id_expediente)
                    WHERE e.id_expediente = $id_expediente AND e.estado = $estado AND e.f_reunion IS NOT NULL
                        AND f.orden_tramite = $orden_tramite
                        AND f.tipo = $tipo_voto
                        AND (f.valor IS NULL OR (f.valor != $valor_ok AND f.valor != $valor_no))
                    ";
        if ($oDbl->query($sQuery) === FALSE) {
            $sClauError = 'FirmaRepository.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $a_exp_faltan_firmas = [];
        foreach ($oDbl->query($sQuery) as $aFirma) {
            if (empty($aFirma)) {
                return FALSE;
            }
            $a_exp_faltan_firmas[] = $aFirma['id_cargo'];
        }
        return $a_exp_faltan_firmas;
    }

    public function getFirmasReunion(int $id_cargo): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $oFirmaSet = new Set();

        $estado = Expediente::ESTADO_FIJAR_REUNION;
        $tipo_voto = Firma::TIPO_VOTO;

        $sQuery = "SELECT DISTINCT f.*
                    FROM $nom_tabla f JOIN expedientes e USING (id_expediente)
                    WHERE e.estado = $estado AND e.f_reunion IS NOT NULL
                        AND f.id_cargo = $id_cargo
                        AND f.tipo = $tipo_voto
                    ";
        if (($oDblSt = $oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'FirmaRepository.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDblSt as $aDatos) {
            // para las fechas
            $aDatos['f_valor'] = (new ConverterDate('timestamp', $aDatos['f_valor']))->fromPg();
            $Firma = new Firma();
            $Firma->setAllAttributes($aDatos);
            $oFirmaSet->add($Firma);
        }
        return $oFirmaSet->getTot();
    }

    public function faltaFirmarReunion(): bool|array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $estado = Expediente::ESTADO_FIJAR_REUNION;
        $cargo_tipo = Cargo::CARGO_TODOS_DIR;
        $tipo_voto = Firma::TIPO_VOTO;
        $valor_ok = Firma::V_OK;
        $valor_no = Firma::V_NO;

        $sQuery = "SELECT DISTINCT f.id_expediente
                    FROM $nom_tabla f JOIN expedientes e USING (id_expediente)
                    WHERE e.estado = $estado AND e.f_reunion IS NOT NULL
                        AND f.cargo_tipo = $cargo_tipo
                        AND f.tipo = $tipo_voto
                        AND (f.valor IS NULL OR (f.valor != $valor_ok AND f.valor != $valor_no))
                    ";
        if ($oDbl->query($sQuery) === FALSE) {
            $sClauError = 'FirmaRepository.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $a_exp_faltan_firmas = [];
        foreach ($oDbl->query($sQuery) as $aFirma) {
            if (empty($aFirma)) {
                return FALSE;
            }
            $a_exp_faltan_firmas[] = $aFirma['id_expediente'];
        }
        return $a_exp_faltan_firmas;
    }

    public function getOrdenCargo(int $id_expediente, int $cargo_tipo): int|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $tipo_voto = Firma::TIPO_VOTO;
        $valor_ok = Firma::V_OK;
        $valor_no = Firma::V_NO;
        $valor_vb = Firma::V_D_VISTO_BUENO;

        $sQuery = "SELECT *
                    FROM $nom_tabla
                    WHERE id_expediente = $id_expediente AND cargo_tipo = $cargo_tipo AND tipo = $tipo_voto
                        AND (valor = $valor_ok OR valor = $valor_no OR valor = $valor_vb)
                    ORDER BY orden_tramite DESC, orden_oficina DESC LIMIT 1";
        if (($stmt = $oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'FirmaRepository.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $aFirma = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($aFirma)) {
            return FALSE;
        }
        $orden_tramite = $aFirma['orden_tramite'];

        return $orden_tramite;
    }

    public function getFirmasConforme(int $id_expediente): array
    {
        // cabio el nombre para los tipo_cargo:
        $a_cargos_especicales[] = Cargo::CARGO_DISTRIBUIR;
        $a_cargos_especicales[] = Cargo::CARGO_VB_VCD;
        $a_cargos_especicales[] = Cargo::CARGO_REUNION;

        $aWhere = ['id_expediente' => $id_expediente,
            '_ordre' => 'orden_tramite, f_valor'
        ];
        $cFirmas = $this->getFirmas($aWhere);
        $a_recorrido = [];
        $UsuarioRepository = new UsuarioRepository();
        foreach ($cFirmas as $oFirma) {
            $valor = $oFirma->getValor();
            $oFecha = $oFirma->getF_valor();
            $fecha = $oFecha->getFromLocal();
            $id_usuario = $oFirma->getId_usuario();

            $oUsuario = $UsuarioRepository->findById($id_usuario);
            $nom_usuario = $oUsuario->getNom_usuario();
            $cargo_tipo = $oFirma->getCargo_tipo();
            if (in_array($cargo_tipo, $a_cargos_especicales)) {
                continue;
            }
            if (!empty($valor) && ($valor === Firma::V_OK)) {
                $a_recorrido[$nom_usuario] = $fecha;
            }
        }

        return $a_recorrido;
    }

    /* -------------------- GESTOR EXTRA ---------------------------------------- */

    /**
     * devuelve una colección (array) de objetos de tipo Firma
     *
     * @param array $aWhere asociativo con los valores para cada campo de la BD.
     * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
     * @return array|FALSE Una colección de objetos de tipo Firma
     */
    public function getFirmas(array $aWhere = [], array $aOperators = []): array|false
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $FirmaSet = new Set();
        $oCondicion = new Condicion();
        $aCondicion = array();
        foreach ($aWhere as $camp => $val) {
            if ($camp === '_ordre') {
                continue;
            }
            if ($camp === '_limit') {
                continue;
            }
            $sOperador = $aOperators[$camp] ?? '';
            if ($a = $oCondicion->getCondicion($camp, $sOperador, $val)) {
                $aCondicion[] = $a;
            }
            // operadores que no requieren valores
            if ($sOperador === 'BETWEEN' || $sOperador === 'IS NULL' || $sOperador === 'IS NOT NULL' || $sOperador === 'OR') {
                unset($aWhere[$camp]);
            }
            if ($sOperador === 'IN' || $sOperador === 'NOT IN') {
                unset($aWhere[$camp]);
            }
            if ($sOperador === 'TXT') {
                unset($aWhere[$camp]);
            }
        }
        $sCondicion = implode(' AND ', $aCondicion);
        if ($sCondicion !== '') {
            $sCondicion = " WHERE " . $sCondicion;
        }
        $sOrdre = '';
        $sLimit = '';
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
        $sQry = "SELECT * FROM $nom_tabla " . $sCondicion . $sOrdre . $sLimit;
        if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
            $sClaveError = 'PgFirmaRepository.listar.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (($oDblSt->execute($aWhere)) === FALSE) {
            $sClaveError = 'PgFirmaRepository.listar.execute';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }

        $filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {
            // para las fechas
            $aDatos['f_valor'] = (new ConverterDate('timestamp', $aDatos['f_valor']))->fromPg();
            $Firma = new Firma();
            $Firma->setAllAttributes($aDatos);
            $FirmaSet->add($Firma);
        }
        return $FirmaSet->getTot();
    }

    /**
     * Busca la clase con id_item en la base de datos .
     */
    public function findById(int $id_item): ?Firma
    {
        $aDatos = $this->datosById($id_item);
        if (empty($aDatos)) {
            return null;
        }
        return (new Firma())->setAllAttributes($aDatos);
    }

    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     *
     * @param int $id_item
     * @return array|bool
     */
    public function datosById(int $id_item): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_item = $id_item")) === FALSE) {
            $sClaveError = 'Firma.getDatosById';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        $aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);
        // para las fechas
        if ($aDatos !== FALSE) {
            $aDatos['f_valor'] = (new ConverterDate('timestamp', $aDatos['f_valor']))->fromPg();
        }

        return $aDatos;
    }

    public function getRecorrido(int $id_expediente): array
    {
        // cambio el nombre para los tipo_cargo:
        $a_cargos_especiales[] = Cargo::CARGO_DISTRIBUIR;
        $a_cargos_especiales[] = Cargo::CARGO_VB_VCD;
        $a_cargos_especiales[] = Cargo::CARGO_REUNION;

        $CargoRepository = new CargoRepository();
        $aCargos = $CargoRepository->getArrayCargos(FALSE);
        $aWhere = ['id_expediente' => $id_expediente,
            '_ordre' => 'orden_tramite, f_valor'
        ];
        $cFirmas = $this->getFirmas($aWhere);
        $responder = FALSE;
        $comentarios = '';
        $a_recorrido = [];
        $oFirma = new Firma();
        $a_valores = $oFirma->getArrayValor('all');
        $UsuarioRepository = new UsuarioRepository();
        foreach ($cFirmas as $oFirma) {
            $a_rec = [];
            $tipo = $oFirma->getTipo();
            $valor = $oFirma->getValor();
            $oFvalor = $oFirma->getF_valor();
            $f_valor = empty($oFvalor) ? '' : $oFvalor->getFromLocalHora();
            if (!empty($valor) && ($valor !== Firma::V_VISTO)) {
                $id_usuario = $oFirma->getId_usuario();
                $oUsuario = $UsuarioRepository->findById($id_usuario);
                $nom_usuario = $oUsuario->getNom_usuario();
                $id_cargo = $oFirma->getId_cargo();
                $cargo_tipo = $oFirma->getCargo_tipo();
                if (in_array($cargo_tipo, $a_cargos_especiales, true)) {
                    $cargo = $aCargos[$cargo_tipo];
                } else {
                    $cargo = $aCargos[$id_cargo];
                }
                $voto = $a_valores[$valor];
                $observ = $oFirma->getObserv();
                $observ_ponente = $oFirma->getObserv_creador();
                if ($tipo === Firma::TIPO_VOTO) {
                    if (!empty($observ)) {
                        $comentarios .= empty($comentarios) ? '' : "<br>";
                        $comentarios .= "$cargo($nom_usuario) [$voto]: $observ";
                    }
                    switch ($valor) {
                        case Firma::V_NO:
                        case Firma::V_D_RECHAZADO:
                            $a_rec['class'] = "list-group-item-danger";
                            break;
                        case Firma::V_OK:
                        case Firma::V_D_OK:
                            $a_rec['class'] = "list-group-item-success";
                            break;
                        case Firma::V_ESPERA:
                        case Firma::V_D_ESPERA:
                        default:
                            $a_rec['class'] = "list-group-item-info";
                    }
                    $a_rec['valor'] = "$f_valor $cargo($nom_usuario) [$voto]";
                    $a_recorrido[] = $a_rec;
                }
                if ($tipo === Firma::TIPO_ACLARACION) {
                    $voto = '<span class="fw-bold">' . _("aclaración") . '</span>';
                    $comentarios .= empty($comentarios) ? '' : "<br>";
                    $comentarios .= "$cargo($voto): $observ";
                    if (!empty($observ_ponente)) {
                        $comentarios .= ' ' . '<span class="fw-bold">' . _("respuesta") . ": </span>";
                        $comentarios .= " $observ_ponente";
                    } else {
                        $responder = TRUE;
                    }
                }
            } else {
                if ($tipo === Firma::TIPO_VOTO) {
                    // lo marco como visto (sólo el mio). Si hay más de uno sólo debería ser el primero vacío
                    $oUsuario = $UsuarioRepository->findById(ConfigGlobal::mi_id_usuario());
                    $nom_usuario = $oUsuario->getNom_usuario();
                    $id_cargo = $oFirma->getId_cargo();
                    $cargo = $aCargos[$id_cargo];
                    $a_rec['class'] = "";
                    $a_rec['valor'] = $cargo;
                    if ($id_cargo === ConfigGlobal::role_id_cargo()) {
                        $orden_tramite_ref = $oFirma->getOrden_tramite();
                        // sólo el siguiente en orden tramite si están todos completos.
                        if ($this->isAnteriorOK($id_expediente, $orden_tramite_ref)) {
                            $oFirma->setValor(Firma::V_VISTO);
                            $oFirma->DBGuardar();

                            $voto = $a_valores[Firma::V_VISTO];
                            $a_rec['class'] = "list-group-item-info";
                            $a_rec['valor'] = "$f_valor $cargo($nom_usuario) [$voto]";
                        }
                    }
                    $a_recorrido[] = $a_rec;
                }
            }
        }

        return ['recorrido' => $a_recorrido,
            'comentarios' => $comentarios,
            'responder' => $responder,
        ];
    }

    public function isAnteriorOK(int $id_expediente, int $orden_tramite_ref): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        // posibles orden_tramite:
        $aOrdenTramite = $this->arrayOrdenTramite($id_expediente);
        krsort($aOrdenTramite);
        $flag = 0;
        $orden_anterior = 0;
        foreach (array_keys($aOrdenTramite) as $orden_tramite) {
            if ($flag === 1) {
                $orden_anterior = $orden_tramite;
                break;
            }
            if ($orden_tramite > $orden_tramite_ref) continue;
            if ($orden_tramite === $orden_tramite_ref) {
                $flag = 1;
            }
        }
        if ($flag === 1 && empty($orden_anterior)) {
            // No existe, el primero es el actual: ok
            return TRUE;
        }

        $tipo_voto = Firma::TIPO_VOTO;
        $sQuery = "SELECT *
                    FROM $nom_tabla
                    WHERE id_expediente = $id_expediente AND tipo = $tipo_voto AND orden_tramite = $orden_anterior
                    ";
        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'FirmaRepository.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // Contar que todos sean ok:
        foreach ($oDbl->query($sQuery) as $aDades) {
            $valor = $aDades['valor'];
            $id_cargo = $aDades['id_cargo'];
            $cargo_tipo = $aDades['cargo_tipo'];
            /*
             const TIPO_VOTO
             const TIPO_ACLARACION
             // valor
             /* const V_VISTO        = 1;  // leído, pensando
                const V_ESPERA       = 2;  // distinto a no leído
                const V_NO           = 3;  // voto negativo
                const V_OK           = 4;  // voto positivo
                const V_D_ESPERA       = 22;  // distinto a no leído
                const V_D_NO           = 23;  // voto negativo
                const V_D_OK           = 24;  // voto positivo
                const V_D_DILATA       = 25;  // sólo vcd
                const V_D_RECHAZADO    = 26;  // sólo vcd
                const V_D_VISTO_BUENO  = 27;  // sólo vcd VºBº
             */
            if (ConfigGlobal::role_actual() === 'vcd' && ConfigGlobal::role_id_cargo() === $id_cargo) {
                if ($valor === Firma::V_VISTO) {
                    return FALSE;
                }
                if ($cargo_tipo !== Cargo::CARGO_VB_VCD && $valor === Firma::V_D_ESPERA) {
                    return FALSE;
                }
            } else {
                if ($valor === Firma::V_NO ||
                    $valor === Firma::V_OK ||
                    $valor === Firma::V_D_NO ||
                    $valor === Firma::V_D_OK ||
                    $valor === Firma::V_D_VISTO_BUENO) {
                } else {
                    return FALSE;
                }
            }
        }
        return TRUE;
    }

    private function arrayOrdenTramite(int $id_expediente): array
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        // posibles orden_tramite:
        $sQuery = "SELECT DISTINCT orden_tramite, cargo_tipo
                    FROM $nom_tabla
                    WHERE id_expediente = $id_expediente
                    ORDER BY orden_tramite DESC";
        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'GestorFirma.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // el primero es el actual, el segundo (si existe) es el anterior.
        $aOrdenTramite = [];
        foreach ($oDbl->query($sQuery) as $aDades) {
            $orden_tramite = $aDades['orden_tramite'];
            $cargo_tipo = $aDades['cargo_tipo'];
            $aOrdenTramite[$orden_tramite] = $cargo_tipo;
        }
        return $aOrdenTramite;
    }

    /**
     * comprobar la última firma es el vº bº del vcd
     * pasarlo a scdl para fijar reunión
     *
     * @param integer $id_expediente
     * @return boolean
     */
    public function isParaReunion(int $id_expediente): bool
    {
        $oFirmaUltimaOk = $this->getUltimaOk($id_expediente);

        $valor = $oFirmaUltimaOk->getValor();
        $cargo_tipo = $oFirmaUltimaOk->getCargo_tipo();

        if ($cargo_tipo === Cargo::CARGO_VB_VCD &&
            ($valor === Firma::V_D_NO ||
                $valor === Firma::V_D_DILATA ||
                $valor === Firma::V_D_ESPERA ||
                $valor === Firma::V_D_RECHAZADO ||
                $valor === Firma::V_D_VISTO_BUENO)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function getUltimaOk(int $id_expediente): bool|Firma|null
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();

        $tipo_voto = Firma::TIPO_VOTO;

        $valors_posibles = Firma::V_OK;
        $valors_posibles .= ',' . Firma::V_NO;
        $valors_posibles .= ',' . Firma::V_D_OK;
        $valors_posibles .= ',' . Firma::V_D_NO;
        $valors_posibles .= ',' . Firma::V_D_DILATA;
        $valors_posibles .= ',' . Firma::V_D_RECHAZADO;
        $valors_posibles .= ',' . Firma::V_D_VISTO_BUENO;

        // posibles orden_tramite:
        $sQuery = "SELECT *
                    FROM $nom_tabla
                    WHERE id_expediente = $id_expediente AND tipo = $tipo_voto
                        AND valor IN ($valors_posibles)
                    ORDER BY orden_tramite DESC, orden_oficina DESC LIMIT 1";
        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'FirmaRepository.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // el primero es el actual, el segundo (si existe) es el anterior.
        foreach ($oDbl->query($sQuery) as $aDades) {
            $oFirma = $this->findById($aDades['id_item']);
        }
        return $oFirma;
    }

    public function hasTodasLasFirmas(int $id_expediente): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $tipo_voto = Firma::TIPO_VOTO;
        $valor_ok = Firma::V_OK;
        $valor_no = Firma::V_NO;
        $sQuery = "SELECT count(*)
                    FROM $nom_tabla
                    WHERE id_expediente = $id_expediente AND tipo = $tipo_voto AND (valor IS NULL OR (valor != $valor_ok AND valor != $valor_no))
                  ";
        if (($stmt = $oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'FirmaRepository.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $count = $stmt->fetchColumn();
        return $count <= 0;
    }

    public function isParaDistribuir(int $id_expediente): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        // El siguiente paso es distribuir, y ya han firmado todos:

        // Buscar el orden tramite de distribuir, y comprobar que todos los anteriores son ok.
        $cargo_tipo_distribuir = Cargo::CARGO_DISTRIBUIR;
        $sQuery = "SELECT *
                    FROM $nom_tabla
                    WHERE id_expediente = $id_expediente AND cargo_tipo = $cargo_tipo_distribuir AND valor IS NULL
                    ORDER BY orden_tramite, orden_oficina LIMIT 1";
        if (($stmt = $oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'FirmaRepository.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $aFirma = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($aFirma['orden_tramite'])) {
            $orden_tramite_secretaria = $aFirma['orden_tramite'];
            // mirar los anteriores:
            $aOrdenTramite = $this->arrayOrdenTramite($id_expediente);
            krsort($aOrdenTramite);
            foreach (array_keys($aOrdenTramite) as $orden_tramite) {
                if ($orden_tramite > $orden_tramite_secretaria) continue;
                if (!$this->isAnteriorOK($id_expediente, $orden_tramite)) {
                    // no está completo. devuelve FALSE
                    return FALSE;
                }
            }
        }
        return TRUE;
    }

    public function getPrimeraFirma(int $id_expediente): bool|Firma|null
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        // posibles orden_tramite:
        $sQuery = "SELECT *
                    FROM $nom_tabla
                    WHERE id_expediente = $id_expediente
                    ORDER BY orden_tramite, orden_oficina LIMIT 1";
        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'FirmaRepository.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // el primero es el actual, el segundo (si existe) es el anterior.
        foreach ($oDbl->query($sQuery) as $aDades) {
            $oFirma = $this->findById($aDades['id_item']);
        }
        return $oFirma;
    }

    public function copiarFirmas(int $id_expediente, int $id_tramite, int $id_tramite_old): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        // tipo voto:
        $tipo = Firma::TIPO_VOTO;
        $cargo_tipo = Cargo::CARGO_VARIAS;

        $sql_update = "UPDATE $nom_tabla new 
                            SET id_usuario=sub.id_usuario, 
                                valor=sub.valor,
                                observ_creador=sub.observ_creador,
                                observ=sub.observ,
                                f_valor=sub.f_valor
                FROM (
                    SELECT id_expediente, cargo_tipo, id_cargo, id_usuario, valor, observ_creador, observ, f_valor
                    FROM $nom_tabla old
                    WHERE old.id_expediente=$id_expediente AND old.id_tramite=$id_tramite_old AND old.valor IS NOT NULL AND old.tipo = $tipo
                     ) AS sub
                WHERE new.id_expediente=sub.id_expediente AND new.id_tramite=$id_tramite AND new.cargo_tipo=sub.cargo_tipo AND new.id_cargo=sub.id_cargo ;
        ";
        if (($oDbl->exec($sql_update)) === FALSE) {
            $sClauError = 'FirmaRepository.copiarFirmas1';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // Añadir los votos que se han ido añadiendo posteriormente, y no están en el tramite como tal (resto_oficinas).
        // cargo_tipo varias:
        $aWhere = [ //'valor' => 'x',
            'id_expediente' => $id_expediente,
            'id_tramite' => $id_tramite_old,
            'cargo_tipo' => $cargo_tipo,
        ];
        //$aOperador = [ 'valor' => 'IS NOT NULL'];
        $aOperador = [];
        $cFirmasVarias = $this->getFirmas($aWhere, $aOperador);
        // Buscar el orden-tramite de Varias:
        $TramiteCargoRepository = new TramiteCargoRepository();
        $cTramiteCargo = $TramiteCargoRepository->getTramiteCargos(['id_tramite' => $id_tramite, 'id_cargo' => Cargo::CARGO_VARIAS]);
        if (!empty($cTramiteCargo)) {
            $orden_tramite = $cTramiteCargo[0]->getOrden_tramite();
            $oExpediente = new Expediente($id_expediente);
            $a_resto_oficinas = $oExpediente->getResto_oficinas();
            foreach ($cFirmasVarias as $oFirma) {
                $id_cargo = $oFirma->getId_cargo();
                if (in_array($id_cargo, $a_resto_oficinas, true)) {
                    continue;
                }
                $oFirma->setId_tramite($id_tramite);
                $oFirma->setOrden_tramite($orden_tramite);
                $this->Guardar($oFirma);
            }

        } else {
            // No tiene 'varias' en el recorrido
        }

        // tipo aclaración:
        $tipo_a = Firma::TIPO_ACLARACION;
        // Busco las que tengo (valor is not null) y compruebo que existe la casilla para tipo=voto, y la inserto.
        $aWhere = ['valor' => 'x',
            'id_expediente' => $id_expediente,
            'id_tramite' => $id_tramite_old,
            'tipo' => $tipo_a,
        ];
        $aOperador = ['valor' => 'IS NOT NULL'];
        $cFirmas = $this->getFirmas($aWhere, $aOperador);
        foreach ($cFirmas as $oFirma) {
            $cargo_tipo = $oFirma->getCargo_tipo();
            $id_cargo = $oFirma->getId_cargo();
            // buscar la casilla nueva
            $aWhereVoto = [
                'id_expediente' => $id_expediente,
                'id_tramite' => $id_tramite_old,
                'tipo' => $tipo,
                'cargo_tipo' => $cargo_tipo,
                'id_cargo' => $id_cargo,
            ];
            $cFirmasVoto = $this->getFirmas($aWhereVoto);
            if (!empty($cFirmasVoto)) {
                // existe el voto, y puedo añadir la aclaración (cambio el id_tramite):
                $oFirma->setId_tramite($id_tramite);
                $this->Guardar($oFirma);
            }
        }
    }

    /**
     * Si no existe el registro, hace un insert, si existe, se hace el update.
     */
    public function Guardar(Firma $Firma): bool
    {
        $id_item = $Firma->getId_item();
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($id_item);

        $aDatos = [];
        $aDatos['id_expediente'] = $Firma->getId_expediente();
        $aDatos['id_tramite'] = $Firma->getId_tramite();
        $aDatos['id_cargo_creador'] = $Firma->getId_cargo_creador();
        $aDatos['cargo_tipo'] = $Firma->getCargo_tipo();
        $aDatos['id_cargo'] = $Firma->getId_cargo();
        $aDatos['id_usuario'] = $Firma->getId_usuario();
        $aDatos['orden_tramite'] = $Firma->getOrden_tramite();
        $aDatos['orden_oficina'] = $Firma->getOrden_oficina();
        $aDatos['tipo'] = $Firma->getTipo();
        $aDatos['valor'] = $Firma->getValor();
        $aDatos['observ_creador'] = $Firma->getObserv_creador();
        $aDatos['observ'] = $Firma->getObserv();
        // para las fechas
        $aDatos['f_valor'] = (new ConverterDate('timestamp', $Firma->getF_valor()))->toPg();

        array_walk($aDatos, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					id_expediente            = :id_expediente,
					id_tramite               = :id_tramite,
					id_cargo_creador         = :id_cargo_creador,
					cargo_tipo               = :cargo_tipo,
					id_cargo                 = :id_cargo,
					id_usuario               = :id_usuario,
					orden_tramite            = :orden_tramite,
					orden_oficina            = :orden_oficina,
					tipo                     = :tipo,
					valor                    = :valor,
					observ_creador           = :observ_creador,
					observ                   = :observ,
					f_valor                  = :f_valor";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_item = $id_item")) === FALSE) {
                $sClaveError = 'Firma.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'Firma.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $aDatos['id_item'] = $Firma->getId_item();
            $campos = "(id_item,id_expediente,id_tramite,id_cargo_creador,cargo_tipo,id_cargo,id_usuario,orden_tramite,orden_oficina,tipo,valor,observ_creador,observ,f_valor)";
            $valores = "(:id_item,:id_expediente,:id_tramite,:id_cargo_creador,:cargo_tipo,:id_cargo,:id_usuario,:orden_tramite,:orden_oficina,:tipo,:valor,:observ_creador,:observ,:f_valor)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'Firma.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDatos);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'Firma.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        return TRUE;
    }

    private function isNew(int $id_item): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_item = $id_item")) === FALSE) {
            $sClaveError = 'Firma.isNew';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (!$oDblSt->rowCount()) {
            return TRUE;
        }
        return FALSE;
    }

    public function borrarFirmas(int $id_expediente, int $id_tramite): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_expediente=$id_expediente AND id_tramite = $id_tramite")) === FALSE) {
            $sClauError = 'FirmaRepository.borrarFirmas';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

}