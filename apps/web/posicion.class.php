<?php
namespace web;
use core;

class Posicion {
	
	/**
	 * id_div de Posicion
	 *
	 * @var string
	 */
	private $sid_div;
	/**
	 * Url de Posicion
	 *
	 * @var string
	 */
	private $surl;
	/**
	 * Bloque de Posicion
	 *
	 * @var string
	 */
	private $sbloque;
	/**
	 * parametros de Posicion
	 *
	 * @var array
	 */
	private $aParametros = array ();
	/**
	 * stack de Posicion
	 *
	 * @var integer
	 */
	private $stack;
	/**
	 * constructor de Posicion.
	 * Sirve para indicar si estoy dentro de _construct()
	 *
	 * @var bool
	 */
	private $constructor = false;

	/* CONSTRUCTOR ------------------------------ */
	function __construct($php_self='',$vars=array()) {
		$this->constructor = true;
		$this->surl = $php_self;
		$this->sbloque = '#main';
		$this->setParametros($vars);
		$this->constructor = false;
	}
	
	/**
	 * coloca el cursor de posicion en la última posicion.
	 *
	 */
	private function goEnd() {
	    if (!is_array($_SESSION['position'])) { return; }
		$aPosition = end($_SESSION['position']);
		$this->stack = key($_SESSION['position']);
		$this->surl = $aPosition['url'];
		$this->sbloque = $aPosition['bloque'];
		$this->aParametros = $aPosition['parametros'];
	}
	/**
	 * coloca el cursor de posicion n posiciones atras.
	 * para n=0, no se usa el valor en $_SESSION['position'], sino el actual.
	 *
	 * @var integer $n número de posiciones a retroceder.
	 */
	public function go($n=0) {
	    if ($n == 0 || !is_array($_SESSION['position'])) { return; }

		$aPosition = end($_SESSION['position']);
		for ($i=0; $i < $n; $i++) {
			$aPosition = prev($_SESSION['position']);
			// si ha llegado al final, se devuelve la primera:
			if ($aPosition === FALSE) {
			    $aPosition = reset($_SESSION['position']);
			    break;
			}
		}
		$this->stack = key($_SESSION['position']);
		$this->surl = $aPosition['url'];
		$this->sbloque = $aPosition['bloque'];
		$this->aParametros = $aPosition['parametros'];
	}
	public function getStack($n=0){
		end($_SESSION['position']);
		for ($i=0; $i < $n; $i++) {
			$aPosition = prev($_SESSION['position']);
		}
		$this->stack = key($_SESSION['position']);
		return $this->stack;
	}
	/**
	 * coloca el cursor de posicion en stack.
	 *
	 * @var integer $stack indice del array $_SESSION['position']);
	 */
	public function goStack($stack='*') { //pongo '*' para distinguirlo del 0.
		if (isset($_SESSION['position'][$stack])) {
			$aPosition = $_SESSION['position'][$stack];
			$this->stack = $aPosition['stack'];
			$this->surl = $aPosition['url'];
			$this->sbloque = $aPosition['bloque'];
			$this->aParametros = $aPosition['parametros'];
			return true;
		} else {
			return false;
		}
	}

	public function olvidar($stack='*') {
		if ($stack != '*') { //pongo '*' para distinguirlo del 0.
			// hasta el final
			array_splice($_SESSION['position'], $stack+1);
		} elseif (isset($this->stack)) { // borrar el actual
			array_splice($_SESSION['position'], $this->stack);
		}
	}
	
	/*
	 * @param $parar Para el incremento de la pila. por defecto 0. El 1 sirve par el caso de actualizar una misma página.
	 */
	public function recordar($parar=0) {
		//echo "<script>history.pushState({state:'new'},'New State','?new');</script>";
		// evitar que sea muy grande
		$this->limitar(20);
		// poner en parametros el stack
		if (empty($this->stack)) { //OJO si es el primero tiene valor 0.
			if (isset($_SESSION['position']) && is_array($_SESSION['position'])) { //para la primera
				end($_SESSION['position']);
				if (empty($parar)) {
					$stack = key($_SESSION['position']) + 1;
				}else {
					$stack = key($_SESSION['position']);
				}
			} else {
				$stack = 0;
			}
		} else {
			if (empty($parar)) {
				$stack = $this->stack + 1;
			} else {
				$stack = $this->stack;
			}
		}
		$this->setParametro('stack', $stack);
		$aPosition = array('url'=>$this->surl,'bloque'=>$this->sbloque,'parametros'=>$this->aParametros,'stack'=>$stack);
		$_SESSION['position'][$stack] = $aPosition;
	}

	private function guardar() {
		if (!isset($this->stack)) { //OJO si es el primero tiene valor 0. (no usar empty)
			if (is_array($_SESSION['position'])) {
				end($_SESSION['position']);
				$stack = key($_SESSION['position']);
			} else {
				$stack = 0;
			}
		} else {
			$stack = $this->stack;
		}
		$_SESSION['position'][$stack] = array('url'=>$this->surl,'bloque'=>$this->sbloque,'parametros'=>$this->aParametros,'stack'=>$stack);
	}

	public function go_atras($n=0) {
		$this->go($n);
		// puede ser que no haya donde volver
		if (empty($this->surl)) {
			return '';
		}
		$id_div = $this->getId_div();
		$id_div = empty($id_div)? 'go_atras' : $id_div;
		
		$url = $this->surl;
		$aParam = $this->aParametros;
		if (!empty($aParam['bloque'])) {
		    $this->sbloque = '#'.$aParam['bloque'];
		}
		$sparametros = Hash::add_hash($aParam,$url);
		$bloque = $this->sbloque;

		$html = '<div id="'.$id_div.'" style="display: none;">';
		$html .= '	<form id="go">';
		$html .= '	url: <input name="url" type="text" value="' . $url .'" size=70><br>';
		$html .= '	parametros: <input name="parametros" type="text" value="' . $sparametros . '" size=70><br>';
		$html .= '	bloque: <input name="id_div" type="text" value="' . $bloque . '" size=70>';
		$html .= '</form>';
		$html .= '</div>';
		// vuelvo el cursor al final.
		$this->goEnd();
		return $html;
	}
	
	public function js_atras($n=0) {
		$this->go($n);
		// puede ser que no haya donde volver
		if (empty($this->surl)) {
			return '';
		}
		$id_div = $this->getId_div();
		$id_div = empty($id_div)? 'js_atras' : $id_div; //por si hay dos (ir_atras)

		$url = $this->surl;
		$aParam = $this->aParametros;
		$sparametros = Hash::add_hash($aParam,$url);
		$bloque = $this->sbloque;

		// el div inicial lo pone fnjs_mostrar_atras, porque intenta aprovechar si ya existe uno
		//  $html = '<div id="'.$id_div.'" style="display: none;">';
		$html = '<form id="go">';
		$html .= '	<input name="url" type="text" value="' . $url .'" size=70><br>';
		$html .= '	<input name="parametros" type="text" value="' . $sparametros . '" size=70><br>';
		$html .= '	<input name="id_div" type="text" value="' . $bloque . '" size=70>';
		$html .= '</form>';
		
		// vuelvo el cursor al final.
		$this->goEnd();
		return "fnjs_mostrar_atras('#$id_div','$html');";
	}
	
	/**
	 * retorna un div, con formulario para enviar datos a Posicion
	 * En index.php hay un div:
	 * <div id="left_slide" class="left-slide">
	 *		<span class=handle onClick="fnjs_ir_a('#ir_atras');"></span>
	 * 
	 * @param integer $n número de posiciones atras. Normalmente 1
	 * @return string Html= div (display_none)
	 */
	public function mostrar_left_slide($n=0) {
		$this->go($n);
		// puede ser que no haya donde volver
		if (empty($this->surl)) {
			return '';
		}
		$id_div = $this->getId_div();
		$id_div = empty($id_div)? 'ir_atras' : $id_div;
		
		$url = $this->surl;
		$aParam = $this->aParametros;
		if (!empty($aParam['bloque'])) {
		    $this->sbloque = '#'.$aParam['bloque'];
		}
		$sparametros = Hash::add_hash($aParam,$url);
		$bloque = $this->sbloque;
		
		$html = '<div id="'.$id_div.'" style="display: none;">';
		$html .= '	<form id="go">';
		$html .= '	url: <input name="url" type="text" value="' . $url .'" size=70><br>';
		$html .= '	parametros: <input name="parametros" type="text" value="' . $sparametros . '" size=70><br>';
		$html .= '	bloque: <input name="id_div" type="text" value="' . $bloque . '" size=70>';
		$html .= '</form>';
		$html .= '</div>';

		// vuelvo el cursor al final.
		$this->goEnd();
		return $html;
	}

	/**
	 * retorna una imagen de flecha, con formulario para enviar datos a Posicion
	 * onClick -> activa fnjs_ir_a(id_div)
	 * 
	 * @param integer $n número de posiciones atras. Normalmente 1
	 * @return string Html= div (display_none) + img(arrow)
	 */
	public function mostrar_back_arrow($n=0) {
		$this->go($n);
		// puede ser que no haya donde volver
		if (empty($this->surl)) {
			return '';
		}
		$id_div = $this->getId_div();
		$id_div = empty($id_div)? 'ir_atras2' : $id_div;
		
		$url = $this->surl;
		$aParam = $this->aParametros;
		$sparametros = Hash::add_hash($aParam,$url);
		$bloque = $this->sbloque;
		
		$html = '<div id="'.$id_div.'" style="display: none;">';
		$html .= '<form id="go">';
		$html .= '	<input name="url" type="hidden" value="' . $url .'" size=70>';
		$html .= '	<input name="parametros" type="hidden" value="' . $sparametros . '" size=70>';
		$html .= '	<input name="id_div" type="hidden" value="' . $bloque . '" size=70>';
		$html .= '</form>';
		$html .= '</div>';
		$html .= "<img onclick=fnjs_ir_a('#$id_div') src=".core\ConfigGlobal::getWeb_icons().'/flechas/left.gif border=0 height=40>';
		
		// vuelvo el cursor al final.
		$this->goEnd();
		return $html;
	}

	private function limitar($n=10) {
		// Cuando hay el doble, borro $n.
		if (isset($_SESSION['position'])) { // No sé poruqe no deja poner todo junto
			if (is_array($_SESSION['position']) && (count($_SESSION['position']) > 2*$n)) {
				$eee = 'a borrra!!';
				array_splice($_SESSION['position'], -$n); // negativo empieza por el final.
				// hay que cambiar el indice stack
				end($_SESSION['position']);
				$stack = key($_SESSION['position']);
				$this->stack = $stack;
				//con los stack dentro de parammmmm
			}
		}
	}

	/**
	 * estableix el valor de l'atribut id_div de Posicion
	 *
	 * @param string id_div
	 */
	function setId_div($id_div) {
		$this->sid_div = $id_div;
	}
	/**
	 * Recupera l'atribut id_div de Posicion
	 *
	 * @return string id_div
	 */
	function getId_div() {
		if (empty($this->sid_div)) {
			return '';
		}
		return $this->sid_div;
	}
	/**
	 * estableix el valor de l'atribut url de Posicion
	 *
	 * @param string url
	 */
	function setUrl($url) {
		$this->surl = $url;
	}
	/**
	 * Recupera l'atribut url de Posicion
	 *
	 * @return string url
	 */
	function getUrl() {
		return $this->surl;
	}
	/**
	 * estableix el valor de l'atribut bloque de Posicion
	 *
	 * @param string sbloque
	 */
	function setBloque($bloque) {
		$this->sbloque = $bloque;
	}
	/**
	 * Recupera l'atribut bloque de Posicion
	 *
	 * @return string sbloque
	 */
	function getBloque() {
		return $this->sbloque;
	}

	/**
	 * estableix el valor d'un parametre de Posicion 
	 *
	 * @param mixed nomParametre
	 * @param mixed valor
	 */
	public function addParametro($nomParametre,$valor,$n=0) {
		if (!empty($_SESSION['position'])) {
			$this->go($n);
			$this->setParametro($nomParametre,$valor);
			$this->guardar();
			$this->goEnd();
		}
	}	
	/**
	 * estableix el valor d'un parametre de Posicion 
	 *
	 * @param mixed nomParametre
	 * @param mixed valor
	 */
	public function setParametro($nomParametre,$valor) {
		$this->aParametros[$nomParametre] = $valor;
	}
	/**
	 * estableix el valor de tots els atributs parametros de Posicion que se li passen en un array
	 *
	 * @param array aVars
	 */
	public function setParametros($aVars,$n=0) {
		// Si es del constructor no guardo los cambios aqui.
		if ($this->constructor) {
			foreach ($aVars as $key=>$value) {
				$this->aParametros[$key] = $value;
			}
		} else {
			if (!empty($_SESSION['position'])) {
				$this->go($n);
				foreach ($aVars as $key=>$value) {
					$this->aParametros[$key] = $value;
				}
				$this->guardar();
				$this->goEnd();
			}
		}
	}
	/**
	 * recupera el valor del parametre
	 *
	 * @param string nomParametre
	 */
	public function getParametro($nomParametre,$n=0) {
		if ($n == 0) {
			if (!isset($this->aParametros[$nomParametre])) {
				$valParametre = '';
			} else {
				$valParametre = empty($this->aParametros[$nomParametre])? '' : $this->aParametros[$nomParametre];
			}
		} else {
			$this->go($n);
			if (!isset($this->aParametros[$nomParametre])) {
				$valParametre = '';
			} else {
				$valParametre = empty($this->aParametros[$nomParametre])? '' : $this->aParametros[$nomParametre];
			}
			$this->goEnd();
		}
		return $valParametre;
	}

	/**
	*
	* Esta función sirve para ir a una página. Típico al acabar un procedimiento.
	*
	* versión Ajax.
	*
	* la variable $go_to puede contener sólo el nombre de la página o también el <div> 
	* donde se quiere la página. (separado por un '|'): pagina|div. ¡OJO!, cuando se pasa
	* una consulta con concatenaciones (||) es un lio. 
	*
	* busca la página en el directorio actual. Para usar una referencia absoluta a una página,
	* el $go_to deberia empezar por '#'.
	*/
	public function ir_a($go_to) {
		if ($go_to=='atras') { return self::atras(); }
		$this->setId_div('ir_a');
		$url='';
		$parametros='';
		$frame='';
		$go=strtok($go_to,"@");
		$pag_sin_param = '';
		$pagina=urldecode($go_to);
		$pagina=strtok($pagina,"|");
		$frame = strtok("|");
		//echo "frame: $frame<br>";

		$_error_txt = "pagina1: $pagina<br>";
		// separo la url de los parametros
		$p=strpos($pagina,'?');
		if ($p !== false ) {
			$pag_sin_param=substr($pagina,0,$p);
			$parametros=substr($pagina,$p+1);
			$_error_txt .= "pag sin param: $pag_sin_param<br>";
			$_error_txt .= "param: $parametros<br>";
		} else {
			$pag_sin_param=$pagina;
		}
		$posi=strpos($parametros,"condicion=");
		if ($posi===false) {
			//$cond=substr($parametros,$posi);
		} else {
			$cond1=substr($parametros,$posi+10);
			//para asegurar que no tiene barras \
			$cond1=stripslashes($cond1);	
			$_error_txt .= "cond1: $cond1<br>";
			$cond2=urlencode($cond1);
			$_error_txt .= "cond2: $cond2<br>";
			$parametros=str_replace($cond1,$cond2,$parametros);
		}
		
		$pagina=$pag_sin_param; // quito la doble barra
		if (strpos($pagina,core\ConfigGlobal::getWeb()) !== false ) { // Si es una referencia absoluta
			$url=$pagina;
		} else {
			$_error_txt .= "pagina2: $pagina<br>";
			$dire=getcwd();
			$_error_txt .= "dire: $dire<br>";
			$path= str_replace (core\ConfigGlobal::$directorio, "", $dire);
			$_error_txt .= "path: $path<br>";
			if (substr($path,-1)!='/'){$path.='/';} // me aseguro de que acabe en "/"
			$_error_txt .= "path2: $path<br>";
			$pagina=str_replace ($path,"",$pagina); //si la dirección ya es absoluta, la quito
			$_error_txt .= "pagina3: $pagina<br>";
			//echo "directorio: $dire, path: $path<br>";
			//echo "pagina: $pagina<br>";
			if (substr($pagina,0,1)=='#'){$pagina=substr($pagina,1); $path="";}
			if (substr($pagina,0,2)=='./'){$pagina=substr($pagina,1);}
			if (substr($pagina,0,3)=='../') { //quito un directorio de $path
				$path = preg_replace('/\w+\/?$/', '', $path);
				$pagina=substr($pagina,3);
			}
			$_error_txt .= "pagina4: $pagina<br>";
			//echo "pagina2: $pagina<br>";
			$url=core\ConfigGlobal::getWeb().$path.$pagina;
		}
		/*
		if (core\ConfigGlobal::mi_id_usuario() == 443) {
			echo "hola dani<br>";
			echo $_error_txt;
		}
		*/
		if (empty($frame)) $frame="#main";

		// passarlo a array para usar la funcion add_hash
		$aParam = array();
		foreach (explode('&',$parametros) as $param) {
			$aa = explode('=',$param);
			$aParam[$aa[0]] = isset($aa[1])? $aa[1] : ''; //ojo con el empty y el 0.
		}
		$parametros = Hash::add_hash($aParam,$url);

		$html = '<form id="go">';
		$html .= '	url: <input name="url" type="text" value="' . $url .'" size=70><br>';
		$html .= '	parametros: <input name="parametros" type="text" value="' . $parametros . '" size=70><br>';
		$html .= '	bloque: <input name="id_div" type="text" value="' . $frame . '" size=70>';
		$html .= '</form>';
		?>
		<script>fnjs_mostrar_atras('#ir_a','<?= $html ?>');</script>
		<?php
	}

	/**
	*
	* Esta función sirve para ir a una página. desde un link, a traves de java: location.
	* El parámetro $form sirve para indicar si se pone una direccion absoluta (http:...) o una relativa al $web (es para el caso del action de un formulario).
	*
	*/
	public function link_a($go_to,$form='') {
		$go=strtok($go_to,"@");
		$pag_sin_param = '';
		$pagina=urldecode($go_to);
		$pagina=strtok($pagina,"|");
		$frame = strtok("|");

		$pagina=str_replace (core\ConfigGlobal::getWeb(),"",$pagina); //si la dirección ya es absoluta, la quito
		$dire=getcwd();
		$path= str_replace (core\ConfigGlobal::$directorio, "", $dire);
		if (substr($path,-1)!='/'){$path.='/';} // me aseguro de que acabe en "/"
		$pagina=str_replace ($path,"",$pagina); //si la dirección ya es absoluta, la quito
		//echo "directorio: $dire, path: $path<br>";
		//echo "pagina: $pagina<br>";
		if (substr($pagina,0,1)=='#'){$pagina=substr($pagina,1); $path="";}
		if (substr($pagina,0,2)=='./'){$pagina=substr($pagina,1);}
		if (substr($pagina,0,3)=='../') { //quito un directorio de $path
			$path = preg_replace('/\w+\/?$/', '', $path);
			$pagina=substr($pagina,3);
		}
		//echo "pagina2: $pagina<br>";
		// separo la url de los parametros
		if ($p=strpos($pagina,'?') ) { //"%3F" es "?" cuando está encode) 
			$pag=substr($pagina,0,$p);
			// no arrastro el goto, no sé porque estba aqui.
			$parametros=substr($pagina,$p+1);
			//$parametros=substr($pagina,$p+1)."&go_to=$go_to";
			//echo "param: $parametros<br>";
		} else {
			$pag=$pagina;
			//$parametros="go_to=$go_to";
		}
		$posi=strpos($parametros,"condicion=");
		if ($posi===false) {
			//$cond=substr($parametros,$posi);
		} else {
			$cond1=substr($parametros,$posi+10);
			//para asegurar que no tiene barras \
			$cond1=stripslashes($cond1);	
			//echo "cond1: $cond1<br>";
			$cond2=urlencode($cond1);
			//echo "cond2: $cond2<br>";
			$parametros=str_replace($cond1,$cond2,$parametros);
		}
		$url=core\ConfigGlobal::getWeb().$path.$pag;
		return "'".$url."?".$parametros."'";
	}
}
