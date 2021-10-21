<?php
namespace web;

class Desplegable {
	protected $aPrimary_key;
	protected $sNombre;
	protected $sid;
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
	protected $sdisabled;

	/* CONSTRUCTOR ------------------------------ */
	function __construct($sNombre='',$oOpciones='',$sOpcion_sel='',$bBlanco='') {
		if (is_array($sNombre)) { //le puedo pasar los parámetros que quiera por el array
			$this->aPrimary_key = $sNombre;
			foreach($sNombre as $nom_id=>$val_id) {
			    if($val_id !== '') { $this->$nom_id = $val_id; }
			}
		} else {
		    if (isset($sNombre) && $sNombre !== '') { $this->sNombre = $sNombre; }
		    if (isset($oOpciones) && $oOpciones !== '') { $this->oOpciones = $oOpciones; }
		    if (isset($sOpcion_sel) && $sOpcion_sel !== '') { $this->sOpcion_sel = $sOpcion_sel; }
		    if (isset($bBlanco) && $bBlanco !== '') { $this->bBlanco = $bBlanco; }
		}
	}

	public function radio($num_col='') {
	    $num_col = empty($num_col)? 3 : $num_col;
	    $col = "col-$num_col";
	    if (!empty($this->sOpcion_sel)) {
            if (is_array($this->sOpcion_sel)) { $a_sel = $this->sOpcion_sel; }
            if (is_string($this->sOpcion_sel)) { $a_sel = explode(",", $this->sOpcion_sel); }
	    } else {
            $a_sel = [];
	    }
        $sHtml = '';
        foreach($this->oOpciones as $key=>$val) {
            $id = $this->sNombre.'_'.$key;
            $name = $this->sNombre;
            if (in_array($key, $a_sel)) { $sel = 'checked'; } else { $sel = ''; }
            
            $sHtml .= "<div class=\"$col form-check form-check-inline\">";
            $sHtml .= "<input class=\"form-check-input\" type=\"radio\" name=\"$name\" id=\"$id\" value=\"$key\" $sel />";
		    $sHtml .= "<label class=\"form-check-label\" for=\"$id\">$val</label>";
            $sHtml .= '</div>';
		}
		
		return $sHtml;
	}

	public function checkbox($num_col='') {
	    $num_col = empty($num_col)? 3 : $num_col;
	    $col = "col-$num_col";
	    if (!empty($this->sOpcion_sel)) {
            if (is_array($this->sOpcion_sel)) { $a_sel = $this->sOpcion_sel; }
            if (is_string($this->sOpcion_sel)) { $a_sel = explode(",", $this->sOpcion_sel); }
	    } else {
            $a_sel = [];
	    }
        $sHtml = '';
        foreach($this->oOpciones as $key=>$val) {
            $id = $this->sNombre.'_'.$key;
            $name = $this->sNombre."[]";
            if (in_array($key, $a_sel)) { $sel = 'checked'; } else { $sel = ''; }
            
            $sHtml .= "<div class=\"$col form-check form-check-inline\">";
            if (empty($this->sAction)) {
                $sHtml .= "<input class=\"form-check-input\" type=\"checkbox\" name=\"$name\" id=\"$id\" value=\"$key\" $sel />";
            }else {
                $sHtml .= "<input class=\"form-check-input\" type=\"checkbox\" name=\"$name\" id=\"$id\" value=\"$key\" $sel onChange=\"$this->sAction\" />";
            }
		    $sHtml .= "<label class=\"form-check-label\" for=\"$id\">$val</label>";
            $sHtml .= '</div>';
		}
		
		return $sHtml;
	}

	public function desplegable() {
	    $id = empty($this->sid)? $this->sNombre : $this->sid;
        $disabled = $this->sdisabled;
		$multiple = empty($this->bMultiple)? '' : 'multiple';
		$tab_index = empty($this->iTabIndex)? '' : 'tabindex="'.$this->iTabIndex.'"';
		$size = empty($this->iSize)? '' : 'size="'.$this->iSize.'"';
		$clase = empty($this->sClase)? '' : 'class="'.$this->sClase.'"';
		if (empty($this->sAction)) {
			$sHtml = "<select $multiple $tab_index id=\"$id\" name=\"$this->sNombre\" $clase $size $disabled>";
		} else {
			$sHtml = "<select $multiple $tab_index id=\"$id\" name=\"$this->sNombre\" $clase $size $disabled onChange=\"$this->sAction\" >";
		}
		$sHtml .= $this->options();
		$sHtml .= '</select>';
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
				if (!empty($this->aOpcion_no) && is_array($this->aOpcion_no) && in_array($row[0], $this->aOpcion_no)) { continue; }
				$txt .= "<option value=\"$row[0]\" $sel>$row[$a]</option>";
			}
		} else if (is_array($this->oOpciones)) {
			reset($this->oOpciones);
			foreach($this->oOpciones as $key=>$val) {
				if ((string)$key === (string)$this->sOpcion_sel) { $sel = 'selected'; } else { $sel = ''; }
				if (!empty($this->aOpcion_no) && in_array($key, $this->aOpcion_no)) { continue; }
				$txt .= "<option value=\"$key\" $sel>$val</option>";
			}
		} else {
			$msg_err = _("tiene que ser un array") .": ".__FILE__.": line ". __LINE__;
			exit ($msg_err);
		}
		return $txt;
	}

	public function setNombre($sNombre) {
		$this->sNombre = $sNombre;
	}
	public function setId($sid) {
		$this->sid = $sid;
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
	public function setDisabled($disabled=FALSE) {
	    if ($disabled) {
	       $this->sdisabled = 'disabled';
	    } else {
	       $this->sdisabled = '';
	    }
	}
}

