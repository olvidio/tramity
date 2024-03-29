<?php

namespace busquedas\model;

use config\model\entity\ConfigSchema;
use core\ConverterDate;
use DateInterval;
use entradas\model\entity\GestorEntradaBypass;
use entradas\model\entity\GestorEntradaCompartida;
use entradas\model\entity\GestorEntradaDB;
use entradas\model\Entrada;
use entradas\model\GestorEntrada;
use escritos\model\GestorEscrito;
use etiquetas\model\entity\GestorEtiquetaEntrada;
use lugares\model\entity\GestorLugar;
use usuarios\model\Categoria;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use web\DateTimeLocal;
use web\NullDateTimeLocal;


class Buscar
{
    /**
     * Id_sigla
     *
     * @var integer
     */
    private $id_sigla;

    /**
     * Id_lugar
     *
     * @var integer
     */
    private $id_lugar;

    /**
     * Prot_num
     *
     * @var integer
     */
    private $prot_num;

    /**
     * Prot_any
     *
     * @var string (puede ser '04')
     */
    private $prot_any;

    /**
     * Prot_mas
     *
     * @var string
     */
    private $prot_mas;

    /**
     *
     * @var string
     */
    private $asunto;
    /**
     *
     * @var integer
     */
    private $ponente;
    /**
     *
     */
    private $aCargos;

    /**
     *
     * @var integer
     */
    private $oficina;

    /**
     *
     * @var string
     */
    private $antiguedad;
    /**
     *
     * @var integer
     */
    private $origen_id_lugar;

    /**
     *
     * @var integer
     */
    private $dest_id_lugar;

    /**
     *
     * @var integer
     */
    private $local_id_lugar;

    /**
     *
     * @var DateTimeLocal
     */
    private $df_min;
    /**
     *
     * @var DateTimeLocal
     */
    private $df_max;

    /**
     * @var boolean
     */
    private $bByPass = FALSE;
    /**
     *
     * @var string
     */
    private $opcion;
    /**
     *
     * @var integer
     */
    private $accion;
    /**
     *
     * @var boolean
     */
    private $ref;

    private $a_etiquetas;
    private $andOr;


    public function getCollection($opcion, $mas = '')
    {
        /* Siempre, obligatorio tener:
         *  - f_entrada not null para las entradas
         *  - f_aprobacion not null para los escritos 
         */
        $this->opcion = $opcion;
        switch ($opcion) {
            // permanentes de cr
            case 'proto':
                // En los centros, no busco en entradas, sino en entradas_compartidas y
                // veo si el centro está en los destinos.
                if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR
                    || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO)
                {
                    $aWhereEntrada = [];
                    $aOperadorEntrada = [];
                    // por asunto
                    if (!empty($this->asunto)) {
                        // en este caso el operador es 'sin_acentos'
                        $aWhereEntrada['asunto_entrada'] = $this->asunto;
                        $aOperadorEntrada['asunto_entrada'] = 'sin_acentos';
                    }

                    $aWhereEntrada['categoria'] = Categoria::CAT_PERMANENTE;
                    $aProt_origen = ['id_lugar' => $this->id_lugar,
                        'num' => $this->prot_num,
                        'any' => $this->prot_any,
                        'mas' => $this->prot_mas,
                    ];
                    $gesLugares = new GestorLugar();
                    $id_sigla_local = $gesLugares->getId_sigla_local();
                    $id_destino = $id_sigla_local;

                    $aWhereEntrada['_ordre'] = 'f_entrada';
                    $gesEntradasCompartidas = new GestorEntradaCompartida();
                    $cEntradas = $gesEntradasCompartidas->getEntradasByProtOrigenDestino($aProt_origen, $id_destino, $aWhereEntrada, $aOperadorEntrada);
                    $aCollections['entradas_compartidas'] = $cEntradas;
                } else {
                    // por asunto
                    if (!empty($this->asunto)) {
                        // en este caso el operador es 'sin_acentos'
                        $aWhereEntrada['asunto_detalle'] = $this->asunto;
                    }

                    $aWhereEntrada['estado'] = Entrada::ESTADO_ACEPTADO;
                    $aOperadorEntrada['estado'] = '>=';
                    $aWhereEntrada['categoria'] = Categoria::CAT_PERMANENTE;
                    $aWhereEntrada['_ordre'] = 'f_entrada';
                    $aOperadorEntrada['f_entrada'] = 'IS NOT NULL';
                    $aProt_origen = ['id_lugar' => $this->id_lugar,
                        'num' => $this->prot_num,
                        'any' => $this->prot_any,
                        'mas' => $this->prot_mas,
                    ];

                    $aWhereEntrada['f_entrada'] = 'x';
                    $gesEntradas = new GestorEntradaDB();
                    $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen, $aWhereEntrada, $aOperadorEntrada);
                    $aCollections['entradas'] = $cEntradas;
                }
                return $aCollections;
            case 'any':
                // por año
                // En los centros, no busco en entradas, sino en entradas_compartidas y
                // veo si el centro está en los destinos.
                if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR
                    || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO)
                {
                    $aWhereEntrada = [];
                    $aOperadorEntrada = [];
                    $aWhereEntrada['categoria'] = Categoria::CAT_PERMANENTE;
                    $aProt_origen = ['id_lugar' => $this->id_lugar,
                        'num' => $this->prot_num,
                        'any' => $this->prot_any,
                        'mas' => $this->prot_mas,
                    ];
                    $gesLugares = new GestorLugar();
                    $id_sigla_local = $gesLugares->getId_sigla_local();
                    $id_destino = $id_sigla_local;

                    $aWhereEntrada['_ordre'] = 'f_entrada';
                    $gesEntradasCompartidas = new GestorEntradaCompartida();
                    $cEntradas = $gesEntradasCompartidas->getEntradasByProtOrigenDestino($aProt_origen, $id_destino, $aWhereEntrada, $aOperadorEntrada);
                    $aCollections['entradas_compartidas'] = $cEntradas;
                } else {
                    $aWhereEntrada['estado'] = Entrada::ESTADO_ACEPTADO;
                    $aOperadorEntrada['estado'] = '>=';
                    $aWhereEntrada['categoria'] = Categoria::CAT_PERMANENTE;
                    $aProt_origen = ['id_lugar' => $this->id_lugar,
                        'num' => $this->prot_num,
                        'any' => $this->prot_any,
                        'mas' => $this->prot_mas,
                    ];

                    $aWhereEntrada['_ordre'] = 'f_entrada';
                    $gesEntradas = new GestorEntradaDB();
                    $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen, $aWhereEntrada, $aOperadorEntrada);
                    $aCollections['entradas'] = $cEntradas;
                }
                return $aCollections;
            case 'lst_todos':
                // En los centros, no busco en entradas, sino en entradas_compartidas y
                // veo si el centro está en los destinos.
                if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR
                    || $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR_CORREO)
                {
                    $aWhereEntrada = [];
                    $aOperadorEntrada = [];
                    $aWhereEntrada['categoria'] = Categoria::CAT_PERMANENTE;
                    $aProt_origen = ['id_lugar' => $this->id_lugar,
                        'num' => $this->prot_num,
                        'any' => $this->prot_any,
                        'mas' => $this->prot_mas,
                    ];
                    $gesLugares = new GestorLugar();
                    $id_sigla_local = $gesLugares->getId_sigla_local();
                    $id_destino = $id_sigla_local;

                    $aWhereEntrada['_ordre'] = 'f_entrada DESC';
                    $gesEntradasCompartidas = new GestorEntradaCompartida();
                    $cEntradas = $gesEntradasCompartidas->getEntradasByProtOrigenDestino($aProt_origen, $id_destino, $aWhereEntrada, $aOperadorEntrada);
                    $aCollections['entradas_compartidas'] = $cEntradas;
                }
                return $aCollections;
            case 'oficina':
                $aWhereEntrada['estado'] = Entrada::ESTADO_ACEPTADO;
                $aOperadorEntrada['estado'] = '>=';
                $aWhereEntrada['categoria'] = Categoria::CAT_PERMANENTE;
                $aWhereEntrada['ponente'] = $this->ponente;
                $aProt_origen = ['id_lugar' => $this->id_lugar,
                    'num' => $this->prot_num,
                    'any' => $this->prot_any,
                    'mas' => $this->prot_mas,
                ];

                $aWhereEntrada['_ordre'] = 'f_entrada';
                $gesEntradas = new GestorEntradaDB();
                $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen, $aWhereEntrada, $aOperadorEntrada);
                $aCollections['entradas'] = $cEntradas;
                return $aCollections;
            case 8: // por etiquetas
                $gesEtiquetasEntrada = new GestorEtiquetaEntrada();
                $a_Id_entradas = $gesEtiquetasEntrada->getArrayEntradas($this->a_etiquetas, $this->andOr);
                $cEntradas = [];
                foreach ($a_Id_entradas as $id_entrada) {
                    $oEntrada = new Entrada($id_entrada);
                    $cEntradas[] = $oEntrada;
                }

                $aCollections['entradas'] = $cEntradas;

                return $aCollections;
            case 71: // un protocolo concreto también en ref:

                $aProt_ref = ['id_lugar' => $this->id_lugar,
                    'num' => $this->prot_num,
                    'any' => $this->prot_any,
                    'mas' => $this->prot_mas,
                ];
                // Entradas: origen_prot.
                $aWhereEntrada['f_entrada'] = 'x';
                $aOperadorEntrada['f_entrada'] = 'IS NOT NULL';
                $aWhereEntrada['estado'] = Entrada::ESTADO_ACEPTADO;
                $aOperadorEntrada['estado'] = '>=';
                $aWhereEntrada['_ordre'] = 'f_entrada';
                $gesEntradas = new GestorEntradaDB();
                $cEntradas = $gesEntradas->getEntradasByRefDB($aProt_ref, $aWhereEntrada, $aOperadorEntrada);
                $aCollections['entradas_ref'] = $cEntradas;

                // Escritos (salidas):
                $aWhereEscrito['f_aprobacion'] = 'x';
                $aOperadorEscrito['f_aprobacion'] = 'IS NOT NULL';
                $aWhereEscrito['_ordre'] = 'f_aprobacion';
                $gesEscritos = new GestorEscrito();
                $cEscritos = $gesEscritos->getEscritosByRefDB($aProt_ref, $aWhereEscrito, $aOperadorEscrito);
                $aCollections['escritos_ref'] = $cEscritos;

            case 7: // un protocolo concreto:
                // Entradas: origen_prot.
                $aWhereEntrada['f_entrada'] = 'x';
                $aOperadorEntrada['f_entrada'] = 'IS NOT NULL';
                $aProt_origen = ['id_lugar' => $this->id_lugar,
                    'num' => $this->prot_num,
                    'any' => $this->prot_any,
                    'mas' => $this->prot_mas,
                ];

                $aWhereEntrada['estado'] = Entrada::ESTADO_ACEPTADO;
                $aOperadorEntrada['estado'] = '>=';
                $aWhereEntrada['_ordre'] = 'f_entrada';
                $gesEntradas = new GestorEntradaDB();
                $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen, $aWhereEntrada, $aOperadorEntrada);
                $aCollections['entradas'] = $cEntradas;

                // Escritos (salidas):
                //si el lugar es la dl (o de Cancillería), hay que buscar en el protocolo local
                $oConfigSchema = new ConfigSchema('id_lugar_cancilleria');
                $id_cancilleria = (int)$oConfigSchema->getValor();

                $oConfigSchema = new ConfigSchema('id_lugar_uden');
                $id_uden = (int)$oConfigSchema->getValor();

                if ($this->id_lugar === $this->id_sigla || $this->id_lugar === $id_cancilleria || $this->id_lugar === $id_uden) {
                    $aWhereEscrito['f_aprobacion'] = 'x';
                    $aOperadorEscrito['f_aprobacion'] = 'IS NOT NULL';
                    $aProt_destino = ['id_lugar' => $this->id_lugar,
                        'num' => $this->prot_num,
                        'any' => $this->prot_any,
                        'mas' => $this->prot_mas,
                    ];
                    $aWhereEscrito['_ordre'] = 'f_aprobacion';
                    $gesEscritos = new GestorEscrito();
                    $cEscritos = $gesEscritos->getEscritosByProtLocalDB($aProt_destino, $aWhereEscrito, $aOperadorEscrito);
                    $aCollections['escritos'] = $cEscritos;
                } else {
                    $aWhereEscrito['f_aprobacion'] = 'x';
                    $aOperadorEscrito['f_aprobacion'] = 'IS NOT NULL';
                    $aProt_destino = ['id_lugar' => $this->id_lugar,
                        'num' => $this->prot_num,
                        'any' => $this->prot_any,
                        'mas' => $this->prot_mas,
                    ];
                    $aWhereEscrito['_ordre'] = 'f_aprobacion';
                    $gesEscritos = new GestorEscrito();
                    $cEscritos = $gesEscritos->getEscritosByProtDestinoDB($aProt_destino, $aWhereEscrito, $aOperadorEscrito);
                    $aCollections['escritos'] = $cEscritos;
                }

                return $aCollections;
            case 1:    // Listado de los últimos
                $Q_antiguedad = (string)filter_input(INPUT_POST, 'antiguedad');
                $Q_origen_id_lugar = (integer)filter_input(INPUT_POST, 'origen_id_lugar');
                $aWhereEntrada = [];
                $aOperadorEntrada = [];
                $aWhereEscrito = [];
                $aOperadorEscrito = [];

                $aWhereEntrada['estado'] = Entrada::ESTADO_ACEPTADO;
                $aOperadorEntrada['estado'] = '>=';
                if (!empty($Q_antiguedad)) {
                    switch ($Q_antiguedad) {
                        case "1m":
                            $limite = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, date("d"), date("Y")));
                            break;
                        case "3m":
                            $limite = date("Y-m-d", mktime(0, 0, 0, date("m") - 3, date("d"), date("Y")));
                            break;
                        case "6m":
                            $limite = date("Y-m-d", mktime(0, 0, 0, date("m") - 6, date("d"), date("Y")));
                            break;
                        case "1a":
                            $limite = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y") - 1));
                            break;
                        case "2a":
                            $limite = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y") - 2));
                            break;
                        case "aa":
                            // más de 2 años.
                            $limite = '1928-10-02';
                            break;
                        default:
                            $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                            exit ($err_switch);
                    }
                    $aWhereEntrada['f_entrada'] = $limite;
                    $aWhereEntrada['_ordre'] = 'f_entrada';
                    $aOperadorEntrada['f_entrada'] = '>';
                    $aWhereEscrito['f_aprobacion'] = $limite;
                    $aWhereEscrito['_ordre'] = 'f_aprobacion';
                    $aOperadorEscrito['f_aprobacion'] = '>';
                } else {
                    $aWhereEntrada['f_entrada'] = 'x';
                    $aOperadorEntrada['f_entrada'] = 'IS NOT NULL';
                    $aWhereEscrito['f_aprobacion'] = 'x';
                    $aOperadorEscrito['f_aprobacion'] = 'IS NOT NULL';
                }

                if (!empty($Q_origen_id_lugar)) {
                    // Caso especial de querer ver los escritos de la dl. No se consulta en las entradas, sino salidas.
                    // se omiten los de distribución de cr.
                    if ($Q_origen_id_lugar === $this->local_id_lugar) {
                        $this->setF_min($limite, FALSE);
                        $cEscritos = $this->buscarEscritos();
                        $aCollections['escritos'] = $cEscritos;
                    } else {
                        $aWhereEntrada['_ordre'] = 'f_entrada';
                        $gesEntradas = new GestorEntradaDB();
                        $cEntradas = $gesEntradas->getEntradasByLugarDB($Q_origen_id_lugar, $aWhereEntrada, $aOperadorEntrada);
                        $aCollections['entradas'] = $cEntradas;
                    }
                } else {
                    $aWhereEntrada['_ordre'] = 'f_entrada';
                    $gesEntradas = new GestorEntrada();
                    $cEntradas = $gesEntradas->getEntradas($aWhereEntrada, $aOperadorEntrada);
                    $aCollections['entradas'] = $cEntradas;
                }
                return $aCollections;
            case 2:
                // buscar en entradas (sólo entradas 17-3-2021)
                $cEntradas = $this->buscarEntradas();
                $aCollections['entradas'] = $cEntradas;
                $cEscritos = $this->buscarEscritos();
                $aCollections['escritos'] = $cEscritos;
                return $aCollections;
            case 3:
                // buscar por destino
                $aCollections = [];

                // para ver los recibidos en dl
                if (!empty($this->local_id_lugar) && $this->getId_sigla() == $this->local_id_lugar) {
                    $cEntradas = $this->buscarEntradas();
                    $aCollections['entradas'] = $cEntradas;
                } else {
                    // otros lugares
                    if (!empty($this->dest_id_lugar)) {
                        $cEscritos = $this->buscarEscritos();
                        $aCollections['escritos'] = $cEscritos;
                        // añadir los de cr a ctr (bypass)
                        $cEntradasBypass = $this->buscarEntradasBypass();
                        $aCollections['entradas_bypass'] = $cEntradasBypass;
                    }
                }

                return $aCollections;
            case 9:
                // buscar por origen
                $aCollections = [];

                // para ver los enviados por dl
                if (!empty($this->origen_id_lugar) && $this->origen_id_lugar === $this->local_id_lugar) {
                    $cEscritos = $this->buscarEscritos();
                    $aCollections['escritos'] = $cEscritos;
                } else {
                    // otros lugares
                    if (!empty($this->origen_id_lugar)) {
                        $cEntradas = $this->buscarEntradas();
                        $aCollections['entradas'] = $cEntradas;
                    }
                }

                return $aCollections;
            case 41: // case "dl":
            case 6: // buscar en escritos: modelo jurídico (plantilla)
                $cEscritos = $this->buscarEscritos();
                $aCollections['escritos'] = $cEscritos;
                return $aCollections;
            case 42: //case "de":
            case 43: //case "de cr a dl":
            case 44: //case "de cr a ctr":
            case 5: // como 2 pero solo buscar en entradas (sólo entradas 17-3-2021)
                $cEntradas = $this->buscarEntradas();
                $aCollections['entradas'] = $cEntradas;
                return $aCollections;
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }

    }

    /**
     * Si df_valor es string, y convert=true se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es false, df_valor debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param DateTimeLocal|string df_min='' optional.
     * @param boolean convert=true optional. Si es false, df_valor debe ser un string en formato ISO (Y-m-d).
     */
    function setF_min($df_min = '', $convert = true)
    {
        if ($convert === true && !empty($df_min)) {
            $oConverter = new ConverterDate('date', $df_min);
            $this->df_min = $oConverter->toPg();
        } else {
            $this->df_min = $df_min;
        }
    }

    private function buscarEscritos()
    {
        $aWhere = [];
        $aOperador = [];
        $gesEscritos = new GestorEscrito();
        // buscar en origen, destino, o ambos. + periodo + oficina
        // las fechas.
        $f_min = '';
        $f_max = '';
        $oF_min = $this->getF_min();
        $f_min = $oF_min->getIso();
        $oF_max = $this->getF_max();
        $f_max = $oF_max->getIso();

        $aWhere['_ordre'] = 'f_aprobacion';
        if (!empty($this->accion)) {
            $aWhere['accion'] = $this->accion;
        }
        // No permitir todos, porque puede tardar mucho.
        if (empty($f_min)) {
            $oHoy = new DateTimeLocal();
            $oHoy->sub(new DateInterval('P1Y'));
            $f_min = $oHoy->getIso();
        }
        if (empty($f_max)) {
            $oHoy = new DateTimeLocal();
            $f_max = $oHoy->getIso();
        }
        if (!empty($f_min) && !empty($f_max)) {
            $aWhere ['f_aprobacion'] = "'$f_min','$f_max'";
            $aOperador ['f_aprobacion'] = 'BETWEEN';
        } else {
            $aWhere['f_aprobacion'] = 'x';
            $aOperador['f_aprobacion'] = 'IS NOT NULL';
        }

        if (!empty($this->asunto)) {
            // en este caso el operador es 'sin_acentos'
            $aWhere['asunto_detalle'] = $this->asunto;
        }

        $cEscritos = [];
        if (!empty($this->oficina)) {
            // Cargos correspondientes a la oficina:
            $gesCargos = new GestorCargo();
            $a_cargos_oficina = $gesCargos->getArrayCargosOficina($this->oficina);
            $a_cargos = [];
            foreach (array_keys($a_cargos_oficina) as $id_cargo) {
                $a_cargos[] = $id_cargo;
            }
            if (!empty($a_cargos)) {
                $cEscritosPonente = [];
                $cEscritosResto = [];
                // dos busquedas:
                $aWhere['creador'] = implode(',', $a_cargos);
                $aOperador['creador'] = 'IN';
                // A Quien se envia el escrito (escritos)
                if (!empty($this->dest_id_lugar)) {
                    $cEscritosPonenteJson = $gesEscritos->getEscritosByLugarDB($this->dest_id_lugar, $aWhere, $aOperador);
                    // añadir los envios a grupos:
                    $cEscritosPonenteGrupos = $gesEscritos->getEscritosByLugarDeGrupo($this->dest_id_lugar, $aWhere, $aOperador);
                    $cEscritosPonente = array_merge($cEscritosPonenteJson, $cEscritosPonenteGrupos);
                } elseif (!empty($this->local_id_lugar)) {
                    $cEscritosPonente = $gesEscritos->getEscritosByLocal($this->local_id_lugar, $aWhere, $aOperador);
                } else {
                    $cEscritosPonente = $gesEscritos->getEscritos($aWhere, $aOperador);
                }
                unset($aWhere['creador']);
                unset($aOperador['creador']);

                $aWhere['resto_oficinas'] = '{' . implode(', ', $a_cargos) . '}';
                $aOperador['resto_oficinas'] = 'OVERLAP';
                // A quien envía el escrito (escritos)
                if (!empty($this->dest_id_lugar)) {
                    $cEscritosRestoJson = $gesEscritos->getEscritosByLugarDB($this->dest_id_lugar, $aWhere, $aOperador);
                    // añadir los envíos a grupos:
                    $cEscritosRestoGrupos = $gesEscritos->getEscritosByLugarDeGrupo($this->dest_id_lugar, $aWhere, $aOperador);
                    $cEscritosResto = array_merge($cEscritosRestoJson, $cEscritosRestoGrupos);
                } elseif (!empty($this->local_id_lugar)) {
                    $cEscritosResto = $gesEscritos->getEscritosByLocal($this->local_id_lugar, $aWhere, $aOperador);
                } else {
                    $cEscritosResto = $gesEscritos->getEscritos($aWhere, $aOperador);
                }

                $cEscritos = array_merge($cEscritosPonente, $cEscritosResto);
            } else {
                // para que no salga nada pongo
                unset($aWhere['creador']);
            }
        } else {
            // A quien se envía el escrito (escritos)
            if (!empty($this->dest_id_lugar)) {
                $cEscritosJson = $gesEscritos->getEscritosByLugarDB($this->dest_id_lugar, $aWhere, $aOperador);
                // añadir los envíos a grupos:
                $cEscritosGrupos = $gesEscritos->getEscritosByLugarDeGrupo($this->dest_id_lugar, $aWhere, $aOperador);
                $cEscritos = array_merge($cEscritosJson, $cEscritosGrupos);
            } elseif (!empty($this->local_id_lugar)) {
                $cEscritos = $gesEscritos->getEscritosByLocal($this->local_id_lugar, $aWhere, $aOperador);
            } else {
                $cEscritos = $gesEscritos->getEscritos($aWhere, $aOperador);
            }
        }
        return $cEscritos;
    }

    /**
     * Recupera l'atribut df_min
     *
     * @return DateTimeLocal|NullDateTimeLocal df_min
     */
    function getF_min()
    {
        if (empty($this->df_min)) {
            return new NullDateTimeLocal();
        }
        return (new ConverterDate('date', $this->df_min))->fromPg();
    }

    /**
     * Recupera l'atribut df_max
     *
     * @return DateTimeLocal|NullDateTimeLocal df_max
     */
    function getF_max()
    {
        if (empty($this->df_max)) {
            return new NullDateTimeLocal();
        }
        return (new ConverterDate('date', $this->df_max))->fromPg();
    }

    private function buscarEntradas()
    {
        $aWhere = [];
        $aOperador = [];
        $gesEntradas = new GestorEntrada();
        // buscar en origen, destino, o ambos. + periodo + oficina
        // las fechas.
        $f_min = '';
        $f_max = '';
        $oF_min = $this->getF_min();
        $f_min = $oF_min->getIso();
        $oF_max = $this->getF_max();
        $f_max = $oF_max->getIso();

        $aWhere['estado'] = Entrada::ESTADO_ACEPTADO;
        $aOperador['estado'] = '>=';
        $aWhere['_ordre'] = 'f_entrada';
        if (!empty($this->opcion) && $this->opcion == 5) {
            $aWhere['_ordre'] = 'f_entrada DESC';
        }
        // No permitir todos, porque puede tardar mucho.
        if (empty($f_min)) {
            $oHoy = new DateTimeLocal();
            $oHoy->sub(new DateInterval('P1Y'));
            $f_min = $oHoy->getIso();
        }
        if (empty($f_max)) {
            $oHoy = new DateTimeLocal();
            $f_max = $oHoy->getIso();
        }
        if (!empty($f_min) && !empty($f_max)) {
            $aWhere ['f_entrada'] = "'$f_min','$f_max'";
            $aOperador ['f_entrada'] = 'BETWEEN';
        } else {
            $aWhere['f_entrada'] = 'x';
            $aOperador['f_entrada'] = 'IS NOT NULL';
        }

        if (!empty($this->asunto)) {
            // en este caso el operador es 'sin_acentos'
            $aWhere['asunto_detalle'] = $this->asunto;
        }

        if ($this->bByPass) {
            $aWhere['bypass'] = 't';
        } else {
            $aWhere['bypass'] = 'f';
        }

        if (!empty($this->oficina)) {
            // Entradas es por oficinas, escritos por cargos:
            // dos busquedas:
            $aWhere['ponente'] = $this->oficina;
            // Quien envia el escrito (entradas)
            if (!empty($this->origen_id_lugar)) {
                $cEntradasPonente = $gesEntradas->getEntradasByLugarDB($this->origen_id_lugar, $aWhere, $aOperador);
            } else {
                $cEntradasPonente = $gesEntradas->getEntradas($aWhere, $aOperador);
            }
            unset($aWhere['ponente']);
            unset($aOperador['ponente']);

            $aWhere['resto_oficinas'] = '{' . $this->oficina . '}';
            $aOperador['resto_oficinas'] = 'OVERLAP';
            // Quien envia el escrito (entradas)
            if (!empty($this->origen_id_lugar)) {
                $cEntradasResto = $gesEntradas->getEntradasByLugarDB($this->origen_id_lugar, $aWhere, $aOperador);
            } else {
                $cEntradasResto = $gesEntradas->getEntradas($aWhere, $aOperador);
            }

            $cEntradas = array_merge($cEntradasPonente, $cEntradasResto);
        } else {
            // Quien envia el escrito (entradas)
            if (!empty($this->origen_id_lugar)) {
                $cEntradas = $gesEntradas->getEntradasByLugarDB($this->origen_id_lugar, $aWhere, $aOperador);
            } else {
                $cEntradas = $gesEntradas->getEntradas($aWhere, $aOperador);
            }
        }
        return $cEntradas;
    }

    /**
     * @return number
     */
    public function getId_sigla()
    {
        return $this->id_sigla;
    }

    /**
     * @param number $id_sigla
     */
    public function setId_sigla($id_sigla)
    {
        $this->id_sigla = $id_sigla;
    }

    private function buscarEntradasBypass()
    {
        $aWhere = [];
        $aOperador = [];
        $gesEntradas = new GestorEntradaBypass();
        // buscar en origen, destino, o ambos. + periodo + oficina
        // las fechas.
        $f_min = '';
        $f_max = '';
        $oF_min = $this->getF_min();
        $f_min = $oF_min->getIso();
        $oF_max = $this->getF_max();
        $f_max = $oF_max->getIso();

        $aWhere['estado'] = Entrada::ESTADO_ACEPTADO;
        $aOperador['estado'] = '>=';
        $aWhere['_ordre'] = 'f_entrada';
        if (!empty($this->opcion) && $this->opcion == 5) {
            $aWhere['_ordre'] = 'f_entrada DESC';
        }
        // No permitir todos, porque puede tardar mucho.
        if (empty($f_min)) {
            $oHoy = new DateTimeLocal();
            $oHoy->sub(new DateInterval('P1Y'));
            $f_min = $oHoy->getIso();
        }
        if (empty($f_max)) {
            $oHoy = new DateTimeLocal();
            $f_max = $oHoy->getIso();
        }
        if (!empty($f_min) && !empty($f_max)) {
            $aWhere ['f_entrada'] = "'$f_min','$f_max'";
            $aOperador ['f_entrada'] = 'BETWEEN';
        } else {
            $aWhere['f_entrada'] = 'x';
            $aOperador['f_entrada'] = 'IS NOT NULL';
        }

        if (!empty($this->asunto)) {
            // en este caso el operador es 'sin_acentos'
            $aWhere['asunto_detalle'] = $this->asunto;
        }

        if (!empty($this->oficina)) {
            // Entradas es por oficinas, escritos por cargos:
            // dos búsquedas:
            $aWhere['ponente'] = $this->oficina;
            // Quien envía el escrito (entradas)
            if (!empty($this->origen_id_lugar)) {
                exit("Función no implementada!!!");
            } else {
                $cEntradasPonente = $gesEntradas->getEntradasBypass($aWhere, $aOperador);
            }
            unset($aWhere['ponente']);
            unset($aOperador['ponente']);

            $aWhere['resto_oficinas'] = '{' . $this->oficina . '}';
            $aOperador['resto_oficinas'] = 'OVERLAP';
            // Destino del Bypass (entradas)
            if (!empty($this->dest_id_lugar)) {
                $cEntradasResto = $gesEntradas->getEntradasBypassByDestino($this->dest_id_lugar, $aWhere, $aOperador);
            } else {
                $cEntradasResto = $gesEntradas->getEntradasBypass($aWhere, $aOperador);
            }

            $cEntradas = array_merge($cEntradasPonente, $cEntradasResto);
        } else {
            // Destino del Bypass (entradas)
            if (!empty($this->dest_id_lugar)) {
                $cEntradas = $gesEntradas->getEntradasBypassByDestino($this->dest_id_lugar, $aWhere, $aOperador);
            } else {
                $cEntradas = $gesEntradas->getEntradasBypass($aWhere, $aOperador);
            }
        }
        return $cEntradas;
    }

    /**
     * @return number
     */
    public function getId_lugar()
    {
        return $this->id_lugar;
    }

    /**
     * @param number $id_lugar
     */
    public function setId_lugar($id_lugar)
    {
        $this->id_lugar = $id_lugar;
    }

    /**
     * @return number
     */
    public function getProt_num()
    {
        return $this->prot_num;
    }

    /**
     * @param number $prot_num
     */
    public function setProt_num($prot_num)
    {
        $this->prot_num = $prot_num;
    }

    /**
     * @return string
     */
    public function getProt_any()
    {
        return $this->prot_any;
    }

    /**
     * @param string prot_any
     */
    public function setProt_any($prot_any): void
    {
        $this->prot_any = $prot_any;
    }

    /**
     * @param string $asunto
     */
    public function setAsunto($asunto)
    {
        $this->asunto = $asunto;
    }

    /**
     * @param number $ponente
     */
    public function setPonente($ponente)
    {
        $this->ponente = $ponente;
    }

    /**
     * @param mixed $aCargos
     */
    public function setACargos($aCargos)
    {
        $this->aCargos = $aCargos;
    }

    /**
     * @param integer $oficina
     */
    public function setOficina($oficina)
    {
        $this->oficina = $oficina;
    }

    /**
     * @param string $antiguedad
     */
    public function setAntiguedad($antiguedad)
    {
        $this->antiguedad = $antiguedad;
    }

    /**
     * @param number $origen_id_lugar
     */
    public function setOrigen_id_lugar($origen_id_lugar)
    {
        $this->origen_id_lugar = $origen_id_lugar;
    }

    /**
     * @param number $dest_id_lugar
     */
    public function setDest_id_lugar($dest_id_lugar)
    {
        $this->dest_id_lugar = $dest_id_lugar;
    }

    /**
     * @param number $local_id_lugar
     */
    public function setLocal_id_lugar($local_id_lugar)
    {
        $this->local_id_lugar = $local_id_lugar;
    }

    /**
     * @param boolean $bByPass
     */
    public function setByPass($ByPass)
    {
        $this->bByPass = $ByPass;
    }

    /**
     * Si df_valor es string, y convert=true se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es false, df_valor debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param DateTimeLocal|string df_max='' optional.
     * @param boolean convert=true optional. Si es false, df_valor debe ser un string en formato ISO (Y-m-d).
     */
    function setF_max($df_max = '', $convert = true)
    {
        if ($convert === true && !empty($df_max)) {
            $oConverter = new ConverterDate('date', $df_max);
            $this->df_max = $oConverter->toPg();
        } else {
            $this->df_max = $df_max;
        }
    }

    /**
     * @return number
     */
    public function getAccion()
    {
        return $this->accion;
    }

    /**
     * @param number $accion
     */
    public function setAccion($accion)
    {
        $this->accion = $accion;
    }

    /**
     * @return boolean
     */
    public function isRef()
    {
        return $this->ref;
    }

    /**
     * @param boolean $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * @return mixed
     */
    public function getEtiquetas()
    {
        return $this->a_etiquetas;
    }

    /**
     * @param mixed $a_etiquetas
     */
    public function setEtiquetas($a_etiquetas)
    {
        $this->a_etiquetas = $a_etiquetas;
    }

    /**
     * @return mixed
     */
    public function getAndOr()
    {
        return $this->andOr;
    }

    /**
     * @param mixed $andOr
     */
    public function setAndOr($andOr)
    {
        $this->andOr = $andOr;
    }


}