<?php
namespace web;

use function core\any_2;
use stdClass;
use lugares\model\entity\Lugar;

class Protocolo {
	protected $aPrimary_key;
	protected $sEtiqueta;
	protected $sNombre;
	protected $oOpciones;
	protected $sOpcion_sel;
	protected $aOpcion_no;
	protected $bBlanco;
	protected $valorBlanco;
	protected $sAction;
	protected $iSize;
	protected $bMultiple;
	protected $iTabIndex;
	protected $sClase;
    
	
	protected $ilugar;
	protected $iprot_num;
	protected $iprot_any;
	protected $sprot_mas;
	
	/* CONSTRUCTOR ------------------------------ */
	function __construct($ilugar='',$iprot_num='',$iprot_any='',$sprot_mas='') {
        if (isset($ilugar) && $ilugar !== '') $this->ilugar = $ilugar;
        if (isset($iprot_num) && $iprot_num !== '') $this->iprot_num = $iprot_num;
        if (isset($iprot_any) && $iprot_any !== '') {
            // asegurar que tenga dos cifras:
            $this->iprot_any = any_2($iprot_any);
        }
        if (isset($sprot_mas) && $sprot_mas !== '') $this->sprot_mas = $sprot_mas;
	}
	
	/**
	 * 
	 * @param stdClass $oProt
	 */
	public function setJson($oProt) {
	    // puede no tener protocolo.
	    if (count(get_object_vars($oProt)) == 0) {
            $this->ilugar = '';
            $this->iprot_num = '';
            $this->iprot_any = '';
            $this->sprot_mas = '';
	    } else {
            $this->ilugar = $oProt->lugar;
            $this->iprot_num = $oProt->num;
            $this->iprot_any = $oProt->any;
            $this->sprot_mas = $oProt->mas;
	    }
	}
	
	public function getProt() {
	    $oProt = new stdClass;
	    $oProt->lugar = $this->ilugar;
	    $oProt->num = $this->iprot_num;
	    $oProt->any = $this->iprot_any;
	    $oProt->mas = $this->sprot_mas;
	    
	    return $oProt;
	}
	
	public function ver_txt_mas() {
	    $prot_mas = empty($this->sprot_mas)? '' : $this->sprot_mas;
	    
	    return $prot_mas;
	}
	public function ver_txt_num() {
	    $lugar = empty($this->ilugar)? '' : $this->ilugar;
	    $nom_lugar = _("sin numerar (E12)");
	    if (!empty($lugar)) {
	        $oLugar = new Lugar($this->ilugar);
	        $nom_lugar = $oLugar->getSigla();
	    }
	    
	    $prot_num = empty($this->iprot_num)? '' : $this->iprot_num;
	    $prot_any = empty($this->iprot_any)? '' : $this->iprot_any;
	    
	    $txt = "$nom_lugar";
	    if (!empty($prot_num)) {
    	    $txt .= " ${prot_num}/${prot_any}";
	    }
	    return $txt;
	}

	/**
	 * Para generar el texto del protocolo.
	 * 
	 * @return string
	 */
	public function ver_txt() {
	    
	    $txt = $this->ver_txt_num();
        $txt .= !empty($this->ver_txt_mas())? ", ".$this->ver_txt_mas() : '';
	    
	    return $txt;
	}

	public function ver_desplegable() {
	    $lugar = empty($this->ilugar)? '' : $this->ilugar;
	    if (!empty($lugar)) $this->sOpcion_sel = $lugar;
	    
	    $prot_num = empty($this->iprot_num)? '' : $this->iprot_num;
	    $prot_any = empty($this->iprot_any)? '' : $this->iprot_any;
	    $prot_mas = empty($this->sprot_mas)? '' : $this->sprot_mas;
	    
	    $id_row = "row_" . $this->sNombre;
	    $id_prot_num = "prot_num_" . $this->sNombre;
	    $id_prot_any = "prot_any_" . $this->sNombre;
	    $id_prot_mas = "prot_mas_" . $this->sNombre;
	    
		$etiqueta = empty($this->sEtiqueta)? '' : $this->sEtiqueta;
		
		if (!empty($this->iTabIndex)) {
		    $tab_index =  'tabindex="'. $this->iTabIndex .'"';
		    $tab_index2 = 'tabindex="'. ($this->iTabIndex + 1) .'"';
		    $tab_index3 = 'tabindex="'. ($this->iTabIndex + 2) .'"';
		    $tab_index4 = 'tabindex="'. ($this->iTabIndex + 3) .'"';
		} else {
            $tab_index =  '';
		    $tab_index2 = '';
		    $tab_index3 = '';
		    $tab_index4 = '';
		}
		$size = empty($this->iSize)? '' : 'size="'.$this->iSize.'"';
		$clase = empty($this->sClase)? '' : 'class="'.$this->sClase.'"';
		
        $sHtml = "";
		if(!empty($etiqueta)) {
            $sHtml .= '<label for="prot_num" class="col-2 form-label">';
            $sHtml .= $etiqueta;
            $sHtml .= '</label>';
		}
		
		$clasname = get_class($this);
		if ($clasname == 'web\Protocolo') {
            $sHtml .= "<div class=\"col-10\">";
		}
		$sHtml .= "<div class=\"row\" id=\"$id_row\">";
		$sHtml .= "<div class=\"col col-4\">";
		if (empty($this->sAction)) {
		    $sHtml .= "<select $tab_index id=\"$this->sNombre\" name=\"$this->sNombre\" class=\"form-control\" $clase $size>";
		} else {
		    $sHtml .= "<select $tab_index id=\"$this->sNombre\" name=\"$this->sNombre\" class=\"form-control\" $clase $size onChange=\"$this->sAction\" >";
		}
		$sHtml .= $this->options();
		$sHtml .= '</select>';
        $sHtml .= '</div>';
		
		$sHtml .= "<div class=\"col\">
                    <input $tab_index2 type=\"text\" class=\"form-control\" id=\"$id_prot_num\" name=\"$id_prot_num\" value=\"$prot_num\" onchange=\"fnjs_proto('#$id_prot_num','#$id_prot_any')\">
                    </div>";
        $sHtml .= '/';
		$sHtml .= "<div class=\"col\">
                    <input $tab_index3 type=\"text\" class=\"form-control\" id=\"$id_prot_any\" name=\"$id_prot_any\" value=\"$prot_any\" >
                    </div>";
		
        $sHtml .= _("más...");
		$sHtml .= "<div class=\"col col-4\">
                    <input $tab_index4 type=\"text\" class=\"form-control\" id=\"$id_prot_mas\" name=\"$id_prot_mas\" value=\"$prot_mas\">
                    </div>";
        
        $sHtml .= '</div>';
		if ($clasname == 'web\Protocolo') {
            $sHtml .= '</div>';
		}
		
		return $sHtml;
	}
	
	public function options() {
		$txt = '';
		if (!empty($this->bBlanco)) {
			if (!empty($this->valorBlanco)) {
				$txt .= "<option value=\"$this->valorBlanco\"></option>";
			} else {
				$txt .= '<option></option>';
			}
		}
		if (is_object($this->oOpciones)) {
			$this->oOpciones->execute();
			foreach($this->oOpciones as $row) {
				if (!isset($row[1])) { $a = 0; } else { $a = 1; } // para el caso de sólo tener un valor.
				if ($row[0] == $this->sOpcion_sel) { $sel = 'selected'; } else { $sel = ''; }
				if (!empty($this->aOpcion_no) && is_array($this->aOpcion_no) && in_array($row[0], $this->aOpcion_no)) continue;
				$txt .= "<option value=\"$row[0]\" $sel>$row[$a]</option>";
			}
		} else if (is_array($this->oOpciones)) {
			reset($this->oOpciones);
			foreach($this->oOpciones as $key=>$val) {
				if ((string)$key === (string)$this->sOpcion_sel) { $sel = 'selected'; } else { $sel = ''; }
				if (!empty($this->aOpcion_no) && is_array($this->aOpcion_no) && in_array($row[0], $this->aOpcion_no)) continue;
				$txt .= "<option value=\"$key\" $sel>$val</option>";
			}
		} else {
			$msg_err = _("tiene que ser un array") .": ".__FILE__.": line ". __LINE__;
			exit ($msg_err);
		}
		return $txt;
	}

	public function setEtiqueta($sEtiqueta) {
		$this->sEtiqueta = $sEtiqueta;
	}
	public function setNombre($sNombre) {
		$this->sNombre = $sNombre;
	}
	public function setOpciones($aOpciones) {
		$this->oOpciones = $aOpciones;
	}
	public function getOpciones() {
		return $this->oOpciones;
	}
	public function setOpcion_sel($sOpcion_sel) {
		$this->sOpcion_sel = $sOpcion_sel;
	}
	public function setOpcion_no($aOpcion_no) {
		$this->aOpcion_no = $aOpcion_no;
	}
	public function setBlanco($bBlanco) {
		$this->bBlanco = $bBlanco;
	}
	public function setValBlanco($valorBlanco) {
		$this->valorBlanco = $valorBlanco;
	}
	public function setAction($sAction) {
		$this->sAction = $sAction;
	}
	public function setSize($iSize) {
		$this->iSize = $iSize;
	}
	public function setMultiple($bMultiple) {
		$this->bMultiple = $bMultiple;
	}
	public function setClase($sClase) {
		$this->sClase = $sClase;
	}
	public function setTabIndex($index) {
		$this->iTabIndex = $index;
	}
	public function setLugar($ilugar) {
		$this->ilugar = $ilugar;
	}
	public function setProt_num($iprot_num) {
		$this->iprot_num = $iprot_num;
	}
	public function setProt_any($iprot_any) {
		$this->iprot_any = $iprot_any;
	}
	public function setMas($sprot_mas) {
		$this->sprot_mas = $sprot_mas;
	}
}

