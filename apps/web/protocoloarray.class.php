<?php
namespace web;

use lugares\model\entity\Lugar;

class ProtocoloArray Extends Protocolo {
	/* ATRIBUTS ----------------------------------------------------------------- */

	/**
	 * sSeleccionados del Desplegable
	 *
	 * @var string
	 */
	 private $sSeleccionados;

	/**
	 * sNomConjunto del Desplegable
	 *
	 * @var string 
	 */
	 private $sNomConjunto;
	/**
	 * sAccionConjunto del Desplegable
	 *
	 * @var string 
	 */
	 private $sAccionConjunto;
	/**
	 * para añadir (o no) la palabra 'ref' delante del texto.
	 * 
	 * bRef del Desplegable
	 *
	 * @var boolean 
	 */
	 private $bRef=FALSE;

	 /**
	  * Para añadir (o no) una opción más en blanco.
	  * 
	  * @var boolean
	  */
	 private $bAdd = TRUE;


	/* CONSTRUCTOR -------------------------------------------------------------- */

	/**
	 * Constructor de la classe.
	 *
	 */
	function __construct($id,$Opciones,$Nom) {
	    if (isset($id) && $id !== '') { $this->sSeleccionados = $id; }
	    if (isset($Opciones) && $Opciones !== '') { $this->oOpciones = $Opciones; }
	    if (isset($Nom) && $Nom !== '') { $this->sNomConjunto = $Nom; }
		
	}

	/* METODES PUBLICS ----------------------------------------------------------*/
	
	public function ArrayListaTxtBr($id_lugar_dst_org) {
	    $aRef['dst_org'] = '';
	    $aRef['local'] = '';
	    $aSeleccionados = '';
	    if (is_array($this->sSeleccionados)) {
	        $aSeleccionados = $this->sSeleccionados;
	    }
	    
	    $ref = ($this->bRef)? 'ref. ' : '';
	    if (!empty($aSeleccionados)) {
	        foreach ($aSeleccionados as $oProt) {
	        	$oProt = json_decode(json_encode($oProt));
	        	if (!property_exists($oProt, 'lugar')) { continue; }
			    $lugar = $oProt->lugar;
			    $prot_num = $oProt->num;
			    $prot_any = $oProt->any;
			    $prot_mas = $oProt->mas;
			    
                $oLugar = new Lugar($lugar);
                $nom_lugar = $oLugar->getSigla();
			    $txt = "$nom_lugar";
			    if (!empty($prot_num)) {
			        $txt .= " ${prot_num}/${prot_any}";
			    }
			    $txt .= !empty($prot_mas)? ", ${prot_mas}" : '';
			    
                if ($lugar == $id_lugar_dst_org) {
                    $aRef['dst_org'] .= !empty($aRef['dst_org'])? "<br>" : '';
                    $aRef['dst_org'] .= $ref.$txt;
                } else {
                    $aRef['local'] .= !empty($aRef['local'])? "<br>" : '';
                    $aRef['local'] .= $ref.$txt;
                }
	        }
	    }
	    return $aRef;
	}

	public function ListaTxtBr($id_lugar='') {
	    $aSeleccionados = '';
	    if (is_array($this->sSeleccionados)) {
	        $aSeleccionados = $this->sSeleccionados;
	    }
	    
	    $sLista = "";
	    $ref = ($this->bRef)? 'ref. ' : '';
	    if (!empty($aSeleccionados)) {
	        foreach ($aSeleccionados as $oProt) {
	        	$oProt = json_decode(json_encode($oProt));
	        	if (!property_exists($oProt, 'lugar')) { continue; }
			    $lugar = $oProt->lugar;
			    $prot_num = $oProt->num;
			    $prot_any = $oProt->any;
			    $prot_mas = $oProt->mas;
			    
			    if (!empty($lugar)) {
			        $oLugar = new Lugar($lugar);
			        $nom_lugar = $oLugar->getSigla();
			    }
			    // para sacar solo un destino
			    if (!empty($id_lugar) && $lugar != $id_lugar) {
			        continue;
			    }
			    
			    $txt = "$nom_lugar";
			    if (!empty($prot_num)) {
			        $txt .= " ${prot_num}/${prot_any}";
			    }
			    $txt .= !empty($prot_mas)? ", ${prot_mas}" : '';
			    
			    $sLista .= !empty($sLista)? "<br>" : '';
			    $sLista .= $ref.$txt;
	        }
	    }
	    return $sLista;
	}
	public function ListaTxt() {
	    $aSeleccionados = '';
	    if (is_array($this->sSeleccionados)) {
	        $aSeleccionados = $this->sSeleccionados;
	    }
	    
	    $ref = ($this->bRef)? 'ref. ' : '';
	    $sLista = "<div class=\"row\" >";
	    if (!empty($aSeleccionados)) {
	        foreach ($aSeleccionados as $oProt) {
	        	$oProt = json_decode(json_encode($oProt));
	        	if (!property_exists($oProt, 'lugar')) { continue; }
			    $lugar = $oProt->lugar;
			    $prot_num = $oProt->num;
			    $prot_any = $oProt->any;
			    $prot_mas = $oProt->mas;
			    
			    if (!empty($lugar)) {
			        $oLugar = new Lugar($lugar);
			        $nom_lugar = $oLugar->getSigla();
			    }
			    
			    $txt = "$nom_lugar";
			    if (!empty($prot_num)) {
			        $txt .= " ${prot_num}/${prot_any}";
			    }
			    $txt .= !empty($prot_mas)? ", ${prot_mas}" : '';
			    
			    $sLista .= $ref.$txt."<br>";
	        }
	    }
	    $sLista .= "</div>";
	    
	    return $sLista;
	}
	
	/**
	 *
	 * Esta función sirve para hacer el echo en html de un input tipo select.
	 * Dentro de una tabla.
	 *
	 * @retrun html <select>...</select> 	
	 */
	public function ListaSelects() {
		$aSeleccionados = '';
		if (is_array($this->sSeleccionados)) {
    		$aSeleccionados = $this->sSeleccionados;
		}

		$fnjs_comprobar = 'fnjs_comprobar_'. $this->sNomConjunto;
		
		$span = $this->sNomConjunto."_span";
		$n=0;
		$sLista = "<div id=\"$span\" class=\"row\" >";
		if (!empty($aSeleccionados)) {
			foreach ($aSeleccionados as $oProt) {
	        	$oProt = json_decode(json_encode($oProt));
	        	if (!property_exists($oProt, 'lugar')) { continue; }
				$this->ilugar = empty($oProt->lugar)? '' : $oProt->lugar;
			    $this->iprot_num = empty($oProt->num)? '' : $oProt->num;
			    $this->iprot_any = empty($oProt->any)? '' : $oProt->any;
			    $this->sprot_mas = empty($oProt->mas)? '' : $oProt->mas;
			    
				$this->sNombre = $this->sNomConjunto."[$n]";
			    
				$this->sOpcion_sel = $this->ilugar;
				$this->sAction="$fnjs_comprobar('".$this->sNombre."',$n);";

				$sLista .= $this->ver_desplegable();
				$n++;
			}
		}
		$sLista .= "</div>";
		
		// para que me salga una opción más en blanco
		if ($this->bAdd) {
            $this->ilugar = '';
            $this->iprot_num = '';
            $this->iprot_any = '';
            $this->sprot_mas = '';
            $this->sNombre = $this->sNomConjunto."_mas";
            $this->sAction = $this->sAccionConjunto;
            $this->sOpcion_sel = '';
            
            $sLista .= "<div class=\"row\">";
            $sLista .= $this->ver_desplegable();
            $sLista .= "</div>";
            $sLista .= "<input type=hidden name='".$this->sNomConjunto."_num' id='".$this->sNomConjunto."_num' value=$n>";
		}
		
		return $sLista;
	}

	/**
	 *
	 * Esta función sirve para hacer el echo en html de un input tipo select.
	 * Dentro de una tabla.
	 * 
	 * El tabindex se empieza 39 números antes del que seponga al desplegable, para poder tener 10.
	 * por tnato la diferencia entre el anterior al desplegable y éste debe ser de 40.
	 *
	 * @retrun string para javascript. 	
	 */
	public function ListaSelectsJs() {

		$fnjs_comprobar = 'fnjs_comprobar_'. $this->sNomConjunto;
		$fnjs_next = 'fnjs_focus_num_'. $this->sNomConjunto;

		$id_nom = $this->sNomConjunto;
	    $id_prot_num = "prot_num_" . $id_nom;
	    $id_prot_any = "prot_any_" . $id_nom;
	    $id_prot_mas = "prot_mas_" . $id_nom;
	    $id_row = "row_" . $id_nom;
	    
		$mas = $this->sNomConjunto."_mas";
		$num = $this->sNomConjunto."_num";
		$span = $this->sNomConjunto."_span";
		
		$tab_def = isset($this->iTabIndex)? $this->iTabIndex : 40;
		$tab = $tab_def - 39;
		
		$txt_js = "\n\t\t\tvar num=$('#$num');";
		$txt_js .= "\n\t\t\tvar id_mas=$('#$mas').val();";
		$txt_js .= "\n\t\t\tvar n=Number(num.val());";
		$txt_js .= "\n\t\t\tvar txt;";
		$txt_js .= "\n\t\t\tvar tab=$tab+4*n;";
		$txt_js .= "\n\t\t\tvar tab1=$tab+4*n+1;";
		$txt_js .= "\n\t\t\tvar tab2=$tab+4*n+2;";
		$txt_js .= "\n\t\t\tvar tab3=$tab+4*n+3;";

		$txt_js .= "\n\t\t\ttxt = '<div class=\'row\' id=".$id_row."['+n+'] >';";
		$txt_js .= "\n\t\t\ttxt += '<div class=\'col col-4\'>';";
		$txt_js .= "\n\t\t\ttxt +='<select tabindex=";
		$txt_js .= "'+tab+' id=".$id_nom."['+n+'] name=".$id_nom."['+n+'] class=\'form-control\' onChange=$fnjs_comprobar(\'".$id_nom."\','+n+');>';";
		$txt_js .= "\n\t\t\t";
		$txt_js .= 'txt += "'.addslashes($this->options()).'";';
		$txt_js .= "\n\t\t\ttxt += '</select>';";
		$txt_js .= "\n\t\t\ttxt += '</div>';";
		
		$txt_js .= "\n\t\t\ttxt += '<div class=\'col\'>';";
		$txt_js .= "\n\t\t\ttxt += '<input tabindex=";
		$txt_js .= "'+tab1+' id=".$id_prot_num."['+n+'] name=".$id_prot_num."['+n+'] class=\'form-control\' onChange=fnjs_proto(\'#".$id_prot_num."['+n+']\',\'#".$id_prot_any."['+n+']\');>';";
		$txt_js .= "\n\t\t\ttxt += '</div>';";
		$txt_js .= "\n\t\t\ttxt += ' / '";
		$txt_js .= "\n\t\t\ttxt += '<div class=\'col\'>';";
		$txt_js .= "\n\t\t\ttxt += '<input tabindex=";
		$txt_js .= "'+tab2+' id=".$id_prot_any."['+n+'] name=".$id_prot_any."['+n+'] class=\'form-control\' >';";
		$txt_js .= "\n\t\t\ttxt += '</div>';";
		$txt_js .= "\n\t\t\ttxt += ' "._("más...")." '";
		$txt_js .= "\n\t\t\ttxt += '<div class=\'col col-4\'>';";
		$txt_js .= "\n\t\t\ttxt += '<input tabindex=";
		$txt_js .= "'+tab3+' id=".$id_prot_mas."['+n+'] name=".$id_prot_mas."['+n+'] class=\'form-control\' >';";
		$txt_js .= "\n\t\t\ttxt += '</div>';";
		$txt_js .= "\n\t\t\ttxt += '</div>';";
		
		$txt_js .= "\n\t\t\t// antes del desplegable de añadir";
		$txt_js .= "\n\t\t\t$('#$span').append(txt);";	
		$txt_js .= "\n\t\t\t// selecciono el valor del desplegable";
		$txt_js .= "\n\t\t\tvar nom='#".$id_nom."\\\\['+n+'\\\\]';";
		$txt_js .= "\n\t\t\t$(nom).val(id_mas);";
		$txt_js .= "\n\t\t\tn1=n+1;";
		$txt_js .= "\n\t\t\tnum.val(n1);";
		$txt_js .= "\n\t\t\t$('#$mas').val('');";
		$txt_js .= "\n\t\t\t$fnjs_next"."('".$id_nom."',n);";
		$txt_js .= "\n";
			
		return $txt_js;
	}

	/**
	 *
	 * Esta función sirve para hacer el echo en html de un input tipo select.
	 * Dentro de una tabla.
	 *
	 * @retrun string para javascript. 	
	 */
	public function ComprobarSelectJs() {

		$fnjs_comprobar = 'fnjs_comprobar_'. $this->sNomConjunto;
		$fnjs_next = 'fnjs_focus_num_'. $this->sNomConjunto;
		
		$txt_js = "\n$fnjs_comprobar = function (nom,n) {";
		$txt_js .= "\n\t".'var id_row="#row_"+nom+"\\\\["+n+"\\\\]";';
		$txt_js .= "\n\t".'var id="#"+nom+"\\\\["+n+"\\\\]";';
		$txt_js .= "\n\t".'var valor=$(id).val();';
		$txt_js .= "\n\tif (!valor) {";
		$txt_js .= "\n\t\t".'$(id_row).hide();';
		$txt_js .= "\n\t} else {";
		$txt_js .= "\n\t\t$fnjs_next"."(nom,n);";
		$txt_js .= "\n\t}";
		$txt_js .= "\n}";
		$txt_js .= "\n";
		
		$txt_js .= "\n$fnjs_next = function (nom,n) {";
		$txt_js .= "\n\t".'var id_prot_num = "#prot_num_"+nom+"\\\\["+n+"\\\\]";';
		$txt_js .= "\n\t".'$(id_prot_num).focus();';
		$txt_js .= "\n}";
		$txt_js .= "\n";
		
		return $txt_js;
	}

	/* METODES  ----------------------------------------------------------*/
	
	public function getArray_sel() {
	    return (array) $this->sSeleccionados;
	}

	public function setArray_sel($seleccionados=[]) {
	    $this->sSeleccionados = $seleccionados;
	}

	public function setRef($bRef) {
		 $this->bRef = $bRef;
	}

	public function setNomConjunto($sNomConjunto) {
		 $this->sNomConjunto = $sNomConjunto;
	}

	public function setAccionConjunto($sAccionConjunto) {
		$this->sAccionConjunto = $sAccionConjunto;
	}
    /**
     * @return boolean
     */
    public function isAdd()
    {
        return $this->bAdd;
    }

    /**
     * @param boolean $bAdd
     */
    public function setAdd($bAdd)
    {
        $this->bAdd = $bAdd;
    }

}
