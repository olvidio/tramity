<?php
namespace busquedas\model;

use core\Converter;
use entradas\model\GestorEntrada;
use entradas\model\entity\GestorEntradaDB;
use expedientes\model\GestorEscrito;
use usuarios\model\entity\GestorCargo;
use web\DateTimeLocal;
use web\NullDateTimeLocal;
use entradas\model\Entrada;



class Buscar {
    /**
     * Id_sigla
     *
     * @var integer
     */
    private $id_sigla;

    /**
     * Id_cr
     *
     * @var integer
     */
    private $id_cr;

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
     * @var integer
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
    private $bByPass=FALSE;
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
    
    
    public function __construct() {
    }
    
    public function getCollection($opcion,$mas='') {
        /* Siempre, obligatorio tener:
         *  - f_entrada not null para las entradas
         *  - f_aprobacion not null para los escritos 
         */
        $this->opcion = $opcion;
        switch ($opcion) {
            // permanentes de cr
            case 'proto':
                // por asunto
                if (!empty($this->asunto)) {
                    // en este caso el operador es 'sin_acentos'
                    $aWhereEntrada['asunto_detalle'] = $this->asunto;
                }
                
                $aWhereEntrada['categoria'] = Entrada::CAT_PERMANATE;
                $aWhereEntrada['_ordre'] = 'f_entrada';
                $aOperadorEntrada['f_entrada'] = 'IS NOT NULL';
                $aProt_origen = [ 'lugar' => $this->id_lugar,
                                  'num' => $this->prot_num,
                                  'any' => $this->prot_any,
                                  'mas' => $this->prot_mas,
                            ];

                $aWhereEntrada['f_entrada'] = 'x';
                $gesEntradas = new GestorEntradaDB();
                $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen,$aWhereEntrada,$aOperadorEntrada);
                $aCollections['entradas'] = $cEntradas;
                return $aCollections;
                break;
            case 'any':
                // por año
                $aWhereEntrada['categoria'] = Entrada::CAT_PERMANATE;
                $aOperadorEntrada = [];
                $aProt_origen = [ 'lugar' => $this->id_lugar,
                                  'num' => $this->prot_num,
                                  'any' => $this->prot_any,
                                  'mas' => $this->prot_mas,
                            ];

                $aWhereEntrada['_ordre'] = 'f_entrada';
                $gesEntradas = new GestorEntradaDB();
                $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen,$aWhereEntrada,$aOperadorEntrada);
                $aCollections['entradas'] = $cEntradas;
                return $aCollections;
                break;
            case 'oficina':
                $aWhereEntrada['categoria'] = Entrada::CAT_PERMANATE;
                $aWhereEntrada['ponente'] = $this->ponente;
                $aOperadorEntrada = [];
                $aProt_origen = [ 'lugar' => $this->id_lugar,
                                  'num' => $this->prot_num,
                                  'any' => $this->prot_any,
                                  'mas' => $this->prot_mas,
                            ];
                
                $aWhereEntrada['_ordre'] = 'f_entrada';
                $gesEntradas = new GestorEntradaDB();
                $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen,$aWhereEntrada,$aOperadorEntrada);
                $aCollections['entradas'] = $cEntradas;
                return $aCollections;
                break;
            case 7: // un protocolo concreto:
                // Entradas: origen_prot.
                $aWhereEntrada['f_entrada'] = 'x';
                $aOperadorEntrada['f_entrada'] = 'IS NOT NULL';
                $aProt_origen = [ 'lugar' => $this->id_lugar,
                                  'num' => $this->prot_num,
                                  'any' => $this->prot_any,
                                  'mas' => $this->prot_mas,
                            ];

                $aWhereEntrada['_ordre'] = 'f_entrada';
                $gesEntradas = new GestorEntradaDB();
                $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen,$aWhereEntrada,$aOperadorEntrada);
                $aCollections['entradas'] = $cEntradas;
                            
                // Escritos (salidas):
                //si el lugar es la dl, hay que buscar en el protocolo local
                if ($this->id_lugar==$this->id_sigla) {
                    $aWhereEscrito['f_aprobacion'] = 'x';
                    $aOperadorEscrito['f_aprobacion'] = 'IS NOT NULL';
                    $aProt_destino = [ 'id_lugar' => $this->id_lugar,
                                      'num' => $this->prot_num,
                                      'any' => $this->prot_any,
                                      'mas' => $this->prot_mas,
                                ];
                    $aWhereEscrito['_ordre'] = 'f_aprobacion';
                    $gesEscritos = new GestorEscrito();
                    $cEscritos = $gesEscritos->getEscritosByProtLocalDB($aProt_destino,$aWhereEscrito,$aOperadorEscrito);
                    $aCollections['escritos'] = $cEscritos;
                } else {
                    $aWhereEscrito['f_aprobacion'] = 'x';
                    $aOperadorEscrito['f_aprobacion'] = 'IS NOT NULL';
                    $aProt_destino = [ 'id_lugar' => $this->id_lugar,
                                      'num' => $this->prot_num,
                                      'any' => $this->prot_any,
                                      'mas' => $this->prot_mas,
                                ];
                    $aWhereEscrito['_ordre'] = 'f_aprobacion';
                    $gesEscritos = new GestorEscrito();
                    $cEscritos = $gesEscritos->getEscritosByProtDestinoDB($aProt_destino,$aWhereEscrito,$aOperadorEscrito);
                    $aCollections['escritos'] = $cEscritos;
                }

                
                return $aCollections;
                break;
            case 1:	// Listado de los últimos
                $Qantiguedad = (string) \filter_input(INPUT_POST, 'antiguedad');
                $Qorigen_id_lugar = (integer) \filter_input(INPUT_POST, 'origen_id_lugar');
                $aWhereEntrada = [];
                $aOperadorEntrada = [];
                $aWhereEscrito = [];
                $aOperadorEscrito = [];
                
                if (!empty($Qantiguedad)) {
                    switch ($Qantiguedad) {
                        case "1m":
                            $limite = date("Y-m-d",mktime(0, 0, 0, date("m")-1, date("d"),date("Y")));
                            break;
                        case "3m":
                            $limite = date("Y-m-d",mktime(0, 0, 0, date("m")-3, date("d"),date("Y")));
                            break;
                        case "6m":
                            $limite = date("Y-m-d",mktime(0, 0, 0, date("m")-6, date("d"),date("Y")));
                            break;
                        case "1a":
                            $limite = date("Y-m-d",mktime(0, 0, 0, date("m"), date("d"),date("Y")-1));
                            break;
                        case "2a":
                            $limite = date("Y-m-d",mktime(0, 0, 0, date("m"),date("d"),date("Y")-2));
                            break;
                    }
                    $gesEntradas = new GestorEntrada();
                    $aWhereEntrada = [ 'f_entrada' => $limite, '_ordre' => 'f_entrada'];
                    $aOperadorEntrada = [ 'f_entrada' => '>'];
                    $aWhereEscrito = [ 'f_aprobacion' => $limite, '_ordre' => 'f_aprobacion'];
                    $aOperadorEscrito = [ 'f_aprobacion' => '>'];
                } else {
                    $aWhereEntrada['f_entrada'] = 'x';
                    $aOperadorEntrada['f_entrada'] = 'IS NOT NULL';
                    $aWhereEscrito['f_aprobacion'] = 'x';
                    $aOperadorEscrito['f_aprobacion'] = 'IS NOT NULL';
                }
                
                if (!empty($Qorigen_id_lugar)) {
                    // Caso especial de querer ver los escritos de la dl. No se consulta en las entradas, sino salidas.
                    // se omiten los de distribución de cr.
                    if ($Qorigen_id_lugar==$this->local_id_lugar) {
                        $id_lugar = $Qorigen_id_lugar;
                        $this->setF_min($limite,FALSE);
                        $cEscritos = $this->buscarEscritos();
                        $aCollections['escritos'] = $cEscritos;
                    } else {
                        $aWhereEntrada['_ordre'] = 'f_entrada';
                        $gesEntradas = new GestorEntradaDB();
                        $id_lugar = $Qorigen_id_lugar;
                        $cEntradas = $gesEntradas->getEntradasByLugarDB($id_lugar,$aWhereEntrada, $aOperadorEntrada);
                        $aCollections['entradas'] = $cEntradas;
                    }
                } else {
                    $aWhereEntrada['_ordre'] = 'f_entrada';
                    $gesEntradas = new GestorEntrada();
                    $cEntradas = $gesEntradas->getEntradas($aWhereEntrada, $aOperadorEntrada);
                    $aCollections['entradas'] = $cEntradas;
                }
                return $aCollections;
                break;
            case 2:
                // buscar en entradas (sólo entradas 17-3-2021)
                $cEntradas = $this->buscarEntradas();
                $aCollections['entradas'] = $cEntradas;
                $cEscritos = $this->buscarEscritos();
                $aCollections['escritos'] = $cEscritos;
                return $aCollections;
                break;
            case 3:
                // buscar en origen, destino o ambos
                $aCollections = [];
                
                $flag = 0;
                // para ver los recibidos en dl
                if ($this->getId_sigla() == $this->local_id_lugar) {
                    $cEntradas = $this->buscarEntradas();
                    $aCollections['entradas'] = $cEntradas;
                    $flag = 1;
                }
                // para ver los enviados por dl
                if (!empty($this->origen_id_lugar) && $this->origen_id_lugar == $this->local_id_lugar) {
                    $cEscritos = $this->buscarEscritos();
                    $aCollections['escritos'] = $cEscritos;
                    $flag = 1;
                }
                // otros lugares
                if ($flag == 0) {
                    if (!empty($this->origen_id_lugar)) {
                        $cEntradas = $this->buscarEntradas();
                        $aCollections['entradas'] = $cEntradas;
                    }
                    if (!empty($this->dest_id_lugar)) {
                        $cEscritos = $this->buscarEscritos();
                        $aCollections['escritos'] = $cEscritos;
                    }
                }
                
                return $aCollections;
            break;
            case 41:
                // case "dl":
                $cEscritos = $this->buscarEscritos();
                $aCollections['escritos'] = $cEscritos;
                return $aCollections;
            break;
            case 42:
                //case "de":
                $cEntradas = $this->buscarEntradas();
                $aCollections['entradas'] = $cEntradas;
                return $aCollections;
            break;
            case 43:
                //case "de cr a dl":
                $cEntradas = $this->buscarEntradas();
                $aCollections['entradas'] = $cEntradas;
                return $aCollections;
            break;
            case 44:
                //case "de cr a ctr":
                $cEntradas = $this->buscarEntradas();
                $aCollections['entradas'] = $cEntradas;
                return $aCollections;
            break;
            case 5:
                // como 2 pero solo buscar en entradas (sólo entradas 17-3-2021)
                $cEntradas = $this->buscarEntradas();
                $aCollections['entradas'] = $cEntradas;
                return $aCollections;
                break;
            case 6:
                // buscar en escritos: modelo jurídico (plantilla)
                $cEscritos = $this->buscarEscritos();
                $aCollections['escritos'] = $cEscritos;
                return $aCollections;
                break;
        }
    
    }

    private function buscarEscritos() {
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
        if (empty($f_max)) {
            $oHoy = new DateTimeLocal();
            $f_max = $oHoy->getIso();
        }
        if (!empty($f_min) && !empty($f_max)) {
            $aWhere ['f_aprobacion'] = "'$f_min','$f_max'";
            $aOperador ['f_aprobacion']  = 'BETWEEN';
            //$cond_ap="AND f_aprobacion >= '$f_min'";
        } else {
            $aWhere['f_aprobacion'] = 'x';
            $aOperador['f_aprobacion'] = 'IS NOT NULL';
        }
        
        if (!empty($this->asunto)) {
            // en este caso el operador es 'sin_acentos'
            $aWhere['asunto_detalle'] = $this->asunto;
        }

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
                $aWhere['creador'] = implode(',',$a_cargos);
                $aOperador['creador'] = 'IN';
                // A Quien se envia el escrito (escritos)
                if (!empty($this->dest_id_lugar)) {
                    $cEscritosPonente = $gesEscritos->getEscritosByLugarDB($this->dest_id_lugar,$aWhere,$aOperador);
                } elseif (!empty($this->local_id_lugar)) {
                    $cEscritosPonente = $gesEscritos->getEscritosByLocal($this->local_id_lugar,$aWhere,$aOperador);
                } else {
                    $cEscritosPonente = $gesEscritos->getEscritos($aWhere, $aOperador);
                }
                unset($aWhere['creador']);
                unset($aOperador['creador']);
                    
                $aWhere['resto_oficinas'] = '{'.implode(', ',$a_cargos).'}';
                $aOperador['resto_oficinas'] = 'OVERLAP';
                // A quien envia el escrito (escritos)
                if (!empty($this->dest_id_lugar)) {
                    $cEscritosResto = $gesEscritos->getEscritosByLugarDB($this->dest_id_lugar,$aWhere,$aOperador);
                } elseif (!empty($this->local_id_lugar)) {
                    $cEscritosResto = $gesEscritos->getEscritosByLocal($this->local_id_lugar,$aWhere,$aOperador);
                } else {
                    $cEscritosResto = $gesEscritos->getEscritos($aWhere, $aOperador);
                }
                
                $cEscritos  = array_merge($cEscritosPonente, $cEscritosResto);
            } else {
                // para que no salga nada pongo
                unset($aWhere['creador']);
            }
        } else {
            // A quien se envia el escrito (escritos)
            if (!empty($this->dest_id_lugar)) {
                $cEscritos = $gesEscritos->getEscritosByLugarDB($this->dest_id_lugar,$aWhere,$aOperador);
            } elseif (!empty($this->local_id_lugar)) {
                $cEscritos = $gesEscritos->getEscritosByLocal($this->local_id_lugar,$aWhere,$aOperador);
            } else {
                $cEscritos = $gesEscritos->getEscritos($aWhere, $aOperador);
            }
        }
        return $cEscritos;
    }
        
    private function buscarEntradas() {
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
        
        $aWhere['_ordre'] = 'f_entrada';
        if (!empty($this->opcion) && $this->opcion == 5) {
            $aWhere['_ordre'] = 'f_entrada DESC';
        }
        if (empty($f_max)) {
            $oHoy = new DateTimeLocal();
            $f_max = $oHoy->getIso();
        }
        if (!empty($f_min) && !empty($f_max)) {
            $aWhere ['f_entrada'] = "'$f_min','$f_max'";
            $aOperador ['f_entrada']  = 'BETWEEN';
            //$cond_ap="AND f_aprobacion >= '$f_min'";
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
                $cEntradasPonente = $gesEntradas->getEntradasByLugarDB($this->origen_id_lugar,$aWhere,$aOperador);
            } else {
                $cEntradasPonente = $gesEntradas->getEntradas($aWhere, $aOperador);
            }
            unset($aWhere['ponente']);
            unset($aOperador['ponente']);
                
            $aWhere['resto_oficinas'] = '{'.$this->oficina.'}';
            $aOperador['resto_oficinas'] = 'OVERLAP';
            // Quien envia el escrito (entradas)
            if (!empty($this->origen_id_lugar)) {
                $cEntradasResto = $gesEntradas->getEntradasByLugarDB($this->origen_id_lugar,$aWhere,$aOperador);
            } else {
                $cEntradasResto = $gesEntradas->getEntradas($aWhere, $aOperador);
            }
            
            $cEntradas  = array_merge($cEntradasPonente, $cEntradasResto);
        } else {
            // Quien envia el escrito (entradas)
            if (!empty($this->origen_id_lugar)) {
                $cEntradas = $gesEntradas->getEntradasByLugarDB($this->origen_id_lugar,$aWhere,$aOperador);
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
     * @return number
     */
    public function getId_lugar()
    {
        return $this->id_lugar;
    }

    /**
     * @return number
     */
    public function getProt_num()
    {
        return $this->prot_num;
    }

    /**
     * @return number
     */
    public function getProt_any()
    {
        return $this->prot_any;
    }

    /**
     * @param number $id_sigla
     */
    public function setId_sigla($id_sigla)
    {
        $this->id_sigla = $id_sigla;
    }

    /**
     * @param number $id_lugar
     */
    public function setId_lugar($id_lugar)
    {
        $this->id_lugar = $id_lugar;
    }

    /**
     * @param number $prot_num
     */
    public function setProt_num($prot_num)
    {
        $this->prot_num = $prot_num;
    }

    /**
     * @param number $prot_any
     */
    public function setProt_any($prot_any)
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
	 * Recupera l'atribut df_min
	 *
	 * @return DateTimeLocal df_min
	 */
	function getF_min() {
	    if (empty($this->df_min)) {
	        return new NullDateTimeLocal();
	    }
	    $oConverter = new Converter('date', $this->df_min);
	    return $oConverter->fromPg();
	}
	/**
	 * estableix el valor de l'atribut df_min
	 * Si df_valor es string, y convert=true se convierte usando el formato web\DateTimeLocal->getFormat().
	 * Si convert es false, df_valor debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
	 *
	 * @param date|string df_min='' optional.
	 * @param boolean convert=true optional. Si es false, df_valor debe ser un string en formato ISO (Y-m-d).
	 */
	function setF_min($df_min='',$convert=true) {
	    if ($convert === true && !empty($df_min)) {
	        $oConverter = new Converter('date', $df_min);
	        $this->df_min =$oConverter->toPg();
	    } else {
	        $this->df_min = $df_min;
	    }
	}
    
    /**
	 * Recupera l'atribut df_max
	 *
	 * @return DateTimeLocal df_max
	 */
	function getF_max() {
	    if (empty($this->df_max)) {
	        return new NullDateTimeLocal();
	    }
	    $oConverter = new Converter('date', $this->df_max);
	    return $oConverter->fromPg();
	}
	/**
	 * estableix el valor de l'atribut df_max
	 * Si df_valor es string, y convert=true se convierte usando el formato web\DateTimeLocal->getFormat().
	 * Si convert es false, df_valor debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
	 *
	 * @param date|string df_max='' optional.
	 * @param boolean convert=true optional. Si es false, df_valor debe ser un string en formato ISO (Y-m-d).
	 */
	function setF_max($df_max='',$convert=true) {
	    if ($convert === true && !empty($df_max)) {
	        $oConverter = new Converter('date', $df_max);
	        $this->df_max =$oConverter->toPg();
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

    
    
    
}