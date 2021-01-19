<?php
namespace busquedas\model;

use core\Converter;
use entradas\model\GestorEntrada;
use entradas\model\entity\GestorEntradaDB;
use expedientes\model\GestorEscrito;
use usuarios\model\entity\GestorCargo;
use web\DateTimeLocal;
use web\NullDateTimeLocal;



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
     * @var DateTimeLocal
     */
    private $df_min;
    /**
     * 
     * @var DateTimeLocal
     */
    private $df_max;
    
    public function __construct() {
        $this->id_sigla = 23;
        $this->id_cr = 23;
    }
    
    public function getCollection($opcion,$mas) {
        switch ($opcion) {
            case 7: // un protocolo concreto:
                if (empty($mas)) {
                    switch($this->id_lugar) {
                            /*
                        case  $this->id_dl:
                            $donde=" es.prot_num='".$prot_num."'";
                            if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND es.prot_any='".$prot_any."'"; }
                            //echo tabla_entradas($donde,"","");
                            echo tabla_salidas($donde,"","");
                            break;
                        case $this->id_cr:
                            $donde=" en.id_lugar='".$id_cr."' AND en.prot_num='".$prot_num."'";
                            if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND en.prot_any='".$prot_any."'"; }
                            echo tabla_entradas($donde,"","");
                            break;
                            */
                        default:
                            // Entradas: origen_prot.
                            $aProt_origen = [ 'id_lugar' => $this->id_lugar,
                                              'num' => $this->prot_num,
                                              'any' => $this->prot_any,
                                              'mas' => $this->prot_mas,
                                        ];
                            $gesEntradas = new GestorEntradaDB();
                            $cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen);
                            return $cEntradas;
                            break;
                    }
                    //$pag_mas="scdl/registro/registro_tabla.php?lugar=$lugar&prot_num=$prot_num&prot_any=$prot_any&opcion=$opcion&mas=1";
                    //echo "<p><span class=link onclick=fnjs_update_div('#main','$pag_mas')>"._("Buscar otros escritos con esta referencia").".</span></p>";
                /*    
                // Buscar otros escritos con esta referencia
                    switch($lugar) {
                        case $id_dl:
                            $donde=" es.prot_num='".$prot_num."'";
                            if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND es.prot_any='".$prot_any."'"; }
                            // Escritos aprobados en la dl
                            echo tabla_salidas($donde,"","");
                            // Escritos recibidos en la dl
                            echo tabla_entradas($donde,"","");
                            //contesta a un escrito de 'dlb' => buscar en ref.
                            $donde_ref="AND ref.prot_num='".$prot_num."'";
                            if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde_ref.="AND ref.prot_any='".$prot_any."'"; }
                            $sql_en= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
                                en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
                                u.sigla, en.f_doc_entrada
                                FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u, referencias ref
                                WHERE en.id_lugar=u.id_lugar AND ref.id_reg=es.id_reg AND ref.id_lugar=$id_dl $donde_ref
                                ";
                            $txt_titulo=_("escritos recibidos en la Delegación con esta referencia");
                            echo tabla_entradas("",$sql_en,"",$txt_titulo);
                            
                            $sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
                                    ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
                                    FROM escritos es, aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), referencias ref, x_modo_envio x
                                    WHERE es.id_reg=ap.id_reg AND ref.id_reg=es.id_reg
                                        AND ref.id_lugar=$id_dl AND x.id_modo_envio=ap.id_modo_envio
                                        $donde_ref
                                    ";
                                        $txt_titulo=_("escritos aprobados en la Delegación con esta referencia");
                                        echo tabla_salidas("",$sql_sal,"",$txt_titulo);
                                        break;
                        case $id_cr:
                            $donde=" en.id_lugar='".$id_cr."' AND en.prot_num='".$prot_num."'";
                            if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND en.prot_any='".$prot_any."'"; }
                            echo tabla_entradas($donde,"","");
                            // escritos aprobados enviados a cr.
                            $donde=" AND dest.prot_num='".$prot_num."'";
                            if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND dest.prot_any='".$prot_any."'"; }
                            $sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,
                                    es.distribucion_cr, ap.id_salida,ap.f_aprobacion,ap.f_salida
                                    FROM escritos es, aprobaciones ap LEFT JOIN destinos dest USING (id_salida), x_modo_envio x
                                    WHERE es.distribucion_cr='f' AND es.id_reg=ap.id_reg
                                        AND dest.id_lugar=$id_cr AND x.id_modo_envio=ap.id_modo_envio
                                        $donde
                                    ";
                                        $txt_titulo=_("escritos aprobados enviados a cr");
                                        echo tabla_salidas("",$sql_sal,"",$txt_titulo);
                                        // escritos de cr enviados a ctr.
                                        $donde=" AND en.prot_num='".$prot_num."'";
                                        if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND en.prot_any='".$prot_any."'"; }
                                        $sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
                                    ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
                                    FROM escritos es, aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), entradas en, x_modo_envio x
                                    WHERE es.distribucion_cr='t' AND es.id_reg=ap.id_reg AND en.id_reg=es.id_reg
                                        AND en.id_lugar=$id_cr AND x.id_modo_envio=ap.id_modo_envio
                                        $donde
                                    ";
                                        $txt_titulo=_("escritos de cr enviados a los ctr");
                                        echo tabla_salidas("",$sql_sal,"",$txt_titulo);
                                        //contesta a un escrito de 'dlb' => buscar en ref.
                                        $donde_ref="AND ref.prot_num='".$prot_num."'";
                                        if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde_ref.="AND ref.prot_any='".$prot_any."'"; }
                                        $sql_en= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
                                en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
                                u.sigla, en.f_doc_entrada
                                FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u, referencias ref
                                WHERE en.id_lugar=u.id_lugar AND ref.id_reg=es.id_reg AND ref.id_lugar=$id_cr $donde_ref
                                ";
                                        $txt_titulo=_("escritos recibidos en la Delegación con esta referencia");
                                        echo tabla_entradas("",$sql_en,"",$txt_titulo);
                                        $sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
                                    ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
                                    FROM escritos es, aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), referencias ref, x_modo_envio x
                                    WHERE es.id_reg=ap.id_reg AND ref.id_reg=es.id_reg
                                        AND ref.id_lugar=$lugar AND x.id_modo_envio=ap.id_modo_envio
                                        $donde_ref
                                    ";
                                        $txt_titulo=_("escritos aprobados en la Delegación con esta referencia");
                                        echo tabla_salidas("",$sql_sal,"",$txt_titulo);
                                        break;
                    }
                */
                }
                break;
            case 1:	// Listado de los últimos
                $Qantiguedad = (string) \filter_input(INPUT_POST, 'antiguedad');
                $Qorigen_id_lugar = (integer) \filter_input(INPUT_POST, 'origen_id_lugar');
                $aWhereEntrada = [];
                $aOperadorEntrada = [];
                
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
                    if ($Qantiguedad=="aa") {
                        $aWhereEntrada = [ '_ordre' => 'f_entrada'];
                        $aOperadorEntrada = [];
                    }
                }
                
                if (!empty($Qorigen_id_lugar)) {
                    // Caso especial de querer ver los escritos de la dl. No se consulta en las entradas, sino salidas.
                    // se omiten los de distribución de cr.
                    // TODO
                    if ($Qorigen_id_lugar==$this->id_sigla) {
                            //echo tabla_salidas("",$sql_sal,"");
                    } else {
                        $gesEntradas = new GestorEntradaDB();
                        $id_lugar = $Qorigen_id_lugar;
                        $cEntradas = $gesEntradas->getEntradasByLugarDB($id_lugar,$aWhereEntrada, $aOperadorEntrada);
                    }
                } else {
                    $gesEntradas = new GestorEntrada();
                    $cEntradas = $gesEntradas->getEntradas($aWhereEntrada, $aOperadorEntrada);
                }
                $aCollections['entradas'] = $cEntradas;
                return $aCollections;
                break;
            case 2:
                $cEntradas = $this->buscarEntradas();
                $aCollections['entradas'] = $cEntradas;
                return $aCollections;
                break;
            case 3:
                // buscar en origen, destino o ambos
                
                $cEntradas = $this->buscarEntradas();
                $cEscritos = $this->buscarEscritos();
                
                $aCollections['entradas'] = $cEntradas;
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
        
        if (empty($f_max)) {
            $oHoy = new DateTimeLocal();
            $f_max = $oHoy->getIso();
        }
        if (!empty($f_min) && !empty($f_max)) {
            $aWhere ['f_entrada'] = "'$f_min','$f_max'";
            $aOperador ['f_entrada']  = 'BETWEEN';
            //$cond_ap="AND f_aprobacion >= '$f_min'";
        }
        
        if (!empty($this->asunto)) {
            $aWhere['asunto'] = $this->asunto;
            $aOperador['asunto'] = 'sin_acentos';
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
                // dos busquedas:
                $aWhere['creador'] = implode(',',$a_cargos);
                $aOperador['creador'] = 'IN';
                // A Quien se envia el escrito (escritos)
                if (!empty($this->dest_id_lugar)) {
                    $cEscritosPonente = $gesEscritos->getEscritosByLugarDB($this->dest_id_lugar,$aWhere,$aOperador);
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
                } else {
                    $cEscritos = $gesEscritos->getEscritos($aWhere, $aOperador);
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
        
        if (empty($f_max)) {
            $oHoy = new DateTimeLocal();
            $f_max = $oHoy->getIso();
        }
        if (!empty($f_min) && !empty($f_max)) {
            $aWhere ['f_entrada'] = "'$f_min','$f_max'";
            $aOperador ['f_entrada']  = 'BETWEEN';
            //$cond_ap="AND f_aprobacion >= '$f_min'";
        }
        
        if (!empty($this->asunto)) {
            $aWhere['asunto'] = $this->asunto;
            $aOperador['asunto'] = 'sin_acentos';
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
                // dos busquedas:
                $aWhere['ponente'] = implode(',',$a_cargos);
                $aOperador['ponente'] = 'IN';
                // Quien envia el escrito (entradas)
                if (!empty($this->origen_id_lugar)) {
                    $cEntradasPonente = $gesEntradas->getEntradasByLugarDB($this->origen_id_lugar,$aWhere,$aOperador);
                } else {
                    $cEntradasPonente = $gesEntradas->getEntradas($aWhere, $aOperador);
                }
                unset($aWhere['ponente']);
                unset($aOperador['ponente']);
                    
                $aWhere['resto_oficinas'] = '{'.implode(', ',$a_cargos).'}';
                $aOperador['resto_oficinas'] = 'OVERLAP';
                // Quien envia el escrito (entradas)
                if (!empty($this->origen_id_lugar)) {
                    $cEntradasResto = $gesEntradas->getEntradasByLugarDB($this->origen_id_lugar,$aWhere,$aOperador);
                } else {
                    $cEntradasResto = $gesEntradas->getEntradas($aWhere, $aOperador);
                }
                
                $cEntradas  = array_merge($cEntradasPonente, $cEntradasResto);
            } else {
                // para que no salga nada pongo
                unset($aWhere['ponente']);
            }
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
	 * Recupera l'atribut df_min
	 *
	 * @return DateTimeLocal df_min
	 */
	function getF_min() {
	    if (!isset($this->df_min) && !$this->bLoaded) {
	        $this->DBCarregar();
	    }
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
	    if (!isset($this->df_max) && !$this->bLoaded) {
	        $this->DBCarregar();
	    }
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
    
    
    
}