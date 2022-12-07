<?php

namespace web;

use core\ConfigGlobal;

class Hash
{

    /**
     * aValoresCamposNo de Seguridad
     *
     * array con los campos y valores a no tener en quenta para el hash.
     * para ponerlos otra vez en la query despues de calcular los hash
     *
     * @var array
     */
    static private $aValoresCamposNo = array();
    /**
     * Direccion Url
     *
     * @var string
     */
    private $sUrl;
    /**
     * campos_chk de Seguridad
     * Lista de campos separados por '!'.
     * campos de si o no.
     *
     * @var string
     */
    private $sCamposChk;
    /**
     * camposForm de Seguridad
     * Lista de campos separados por '!'.
     * campos que se pasan con el formulario.
     *
     * @var string
     */
    private $sCamposForm;
    /**
     * acamposHidden de Seguridad
     * Array de campos campos hidden y sus valores.
     *
     * @var array
     */
    private $aCamposHidden;
    /**
     * para poder tener un id distinto para un mismo nombre de campo hidden
     */
    private $prefix;
    /**
     * camposNo de Seguridad
     * Lista de campos separados por '!'.
     * campos a no tener en quenta para el hash.
     *
     * @var string
     */
    private $sCamposNo;

    /* CONSTRUCTOR ------------------------------ */

    public function __construct()
    {
        // constructor vuit
    }

    /* MÉTODOS PÚBLICOS -----------------------------------------------------------*/

    /**
     * Añade el hash al final del string que se le pasa como url.
     *
     * @param string $sUrl_full
     * @return string
     */
    static function link($sUrl_full)
    {
        //$sUrl_org = $sUrl_full;
        $sUrl_full = self::ordenarParam($sUrl_full);
        $rta = self::md($sUrl_full);
        $sUrlHash = $rta['hash'];
        $horig = $rta['orig'];

        $aParam = array();
        $aParam['h'] = $sUrlHash;
        if (ConfigGlobal::is_debug_mode()) {
            $aParam['horig'] = $horig;
        }
        if (is_array($aParam)) {
            array_walk($aParam, 'core\poner_empty_on_null');
        }
        if (strpos($sUrl_full, '?') === false) {
            $sUrl_full .= '?' . http_build_query($aParam);
        } else {
            $sUrl_full .= '&' . http_build_query($aParam);
        }
        return $sUrl_full;
    }

    private static function ordenarParam($sUrl)
    {
        if (($aParam = parse_url($sUrl)) === false) { // la url puede contener ip en vez de nombre
            $matches = [];
            $regex = "^(//([^/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?";
            preg_match("@$regex@", $sUrl, $matches);
            $aParam['path'] = $matches[3];
            $aParam['query'] = !empty($matches[5]) ? $matches[5] : '';

        }
        $sPath = !empty($aParam['path']) ? self::FullPath($aParam['path']) : self::FullPath('');
        if (!empty($aParam['query'])) {
            $aQuery = self::string2array($aParam['query']);
            $aQuerySorted = self::ordenarArrayParam($aQuery);
            $sUrl = $sPath . '?' . http_build_query($aQuerySorted);
        } else {
            $sUrl = $sPath;
        }
        return $sUrl;
    }

    private static function FullPath($sPath)
    {
        $sPath = (substr($sPath, 0, 1) == '/') ? $sPath : '/' . $sPath;
        if (strpos($sPath, $_SERVER["SERVER_NAME"]) === false) {
            if (strpos($sPath, ConfigGlobal::$web_path) === false) {
                $sPath = ConfigGlobal::getWeb() . $sPath;
            } else {
                $sPath = ConfigGlobal::getWeb() . ConfigGlobal::getWebPort() . $sPath;
            }
        }
        return $sPath;
    }

    /**
     * Convierte una cadena http query en un array.
     *
     * @param string $sParam
     * @param string $and separador '&'
     * @return string[]
     */
    private static function string2array($sParam, $and = '&')
    {
        $aParam = array();
        if (!empty($sParam)) { //si no hay no hace falta ordenar nada.
            $sParam = urldecode($sParam);
            $aParejas = explode($and, $sParam);
            foreach ($aParejas as $pareja) {
                $alist = explode('=', $pareja);
                $campo = $alist[0];
                // ojo con los ceros. esto no funciona:
                //$valor = empty($alist[1])? '' : $alist[1];
                $valor = !isset($alist[1]) ? '' : $alist[1];
                $aParam[$campo] = $valor;
            }
        }
        return $aParam;
    }

    /**
     * Devuelve los parametros preparados para calcular el hash.
     *
     * Ordena los parametros para que al calcular el hash  estén siempre en el mismo orden.
     * Quita los parametros que no se deben incluir en el hash.
     * Elimina los valores de los parametros si hnov=1.
     *
     * @param array $aParam
     * @return array
     */
    private static function ordenarArrayParam($aParam)
    {
        if (!empty($aParam)) {
            $aPOST = $aParam;
            // campos que se deben quitar del hash; separados por !.
            $hno = empty($aPOST['hno']) ? '' : $aPOST['hno'];
            if (!empty($hno)) {
                $a_campos_no = explode('!', $hno);
                foreach ($a_campos_no as $campo) {
                    if (isset($aPOST[$campo])) {
                        self::$aValoresCamposNo[$campo] = $aPOST[$campo];
                        unset($aPOST[$campo]);
                    }
                }
            }
            // Indica que los campos deben estar sin valores en el hash;
            $hnov = empty($aPOST['hnov']) ? '' : $aPOST['hnov'];
            if ($hnov == 1) { // borro posibles los valores de los campos
                foreach ($aPOST as $camp => $valor) {
                    $aPOST[$camp] = '';
                }
            }
            //var_dump($aPOST);
            unset($aPOST['PHPSESSID']);
            unset($aPOST['atras']);
            unset($aPOST['h']);
            unset($aPOST['hc']);
            unset($aPOST['horig']);
            unset($aPOST['hh']);
            unset($aPOST['hhc']);
            unset($aPOST['hhorig']);
            unset($aPOST['hno']);
            unset($aPOST['hchk']);
            unset($aPOST['hnov']);
            ksort($aPOST);
            if (is_array($aPOST)) {
                array_walk($aPOST, 'core\poner_empty_on_null');
            }
            return $aPOST;
        } else {
            return array();
        }
    }

    /**
     * Calcula el hash(md5) del string que se le pasa. Se añade el id_session y algún carácter más.
     * Por lo que sólo sirve para la misma session.
     *
     * @param string $str
     * @return string[]  'orig' => string original decoded y trimed
     *                   'hash' => el md5
     */
    private static function md($str)
    {
        $rta = [];
        $str = rawurldecode(trim($str));
        $rta['orig'] = $str;
        $rta['hash'] = md5($str . session_id() . "a+a+");
        return $rta;
    }

    /**
     * Sólo la usa web\Posicion.
     *   => elimino hnov. (si existiera). Se cuenta todos los valores de los campos.
     *   => añado hpos (viene de web\Posicion y no un formulario normal)
     *          para indicar al receptor que el hash se calcula con la url incluida.
     *
     * @param array $aParam
     * @param string $url
     * @return string
     */
    public static function add_hash($aParam, $url)
    {
        if (!is_array($aParam)) {
            $sParam = $aParam;
            $aParam = self::string2array($sParam);
        }
        //parece que sólo la usa web\Posicion => elimino hnov. y añado hpos
        unset($aParam['hnov']);
        $aParam['hpos'] = 1;
        $aParamSorted = self::ordenarArrayParam($aParam);
        $sPath = self::FullPath($url);
        $sUrl_full = $sPath;
        if (!empty($aParamSorted)) $sUrl_full .= '?' . http_build_query($aParamSorted);

        $rta = self::md($sUrl_full);
        $h2 = $rta['hash'];
        $horig = $rta['orig'];

        $aParam['h'] = $h2;
        // después de calcular el hash, añado los campos que no afectan
        $hno = '';
        foreach (self::$aValoresCamposNo as $campo => $value) {
            $aParam[$campo] = $value;
            $hno .= '!' . $campo;
        }
        if (!empty($hno)) {
            $aParam['hno'] = $hno;
        }
        if (ConfigGlobal::is_debug_mode()) {
            //$query .= '&horig='.$horig;
            $aParam['horig'] = $horig;
        }
        if (is_array($aParam)) {
            array_walk($aParam, 'core\poner_empty_on_null');
        }
        $query = http_build_query($aParam);
        return $query;
    }

    /**
     * Para validar los parametros enviados via POST. Recalcula el hash y lo compara con el
     * que se pasa a trasvés del POST.
     *
     * @param array $aPOST (normalmente $_POST)
     * @return string empty si es correcto, echo error en caso contrario.
     */
    public function validatePost($aPOST)
    {
        if (isset($aPOST['h'])) {
            $salta = 0;
            $h1 = $aPOST['h'];
            // hash de los campos hidden
            $hh = empty($aPOST['hh']) ? '' : $aPOST['hh'];
            // campos hidden. (separados por !).
            $hhc = empty($aPOST['hhc']) ? '' : $aPOST['hhc'];
            // campos del formulario. (separados por '!').
            //$hc = empty($aPOST['hc'])? '' : $aPOST['hc'];
            // campos que se deben quitar del hash. (separados por '!').
            $hno = empty($aPOST['hno']) ? '' : $aPOST['hno'];
            if (!empty($hno)) {
                $a_campos_no = explode('!', $hno);
                foreach ($a_campos_no as $campo) {
                    unset($aPOST[$campo]);
                }
            }
            $hchk = empty($aPOST['hchk']) ? '' : $aPOST['hchk'];
            //En el caso de comprobar_campos.php añado tres que hay que borrar
            // Para sf
            if ($_SERVER["REQUEST_URI"] == ConfigGlobal::getWebPath() . '/apps/core/comprobar_campos.php') {
                unset($aPOST['cc_tabla']);
                unset($aPOST['cc_obj']);
                unset($aPOST['cc_pau']);
            }

            //si es hnov es 1, es para no tener en cuenta los valores de los parametros en el hash.
            $hnov = empty($aPOST['hnov']) ? '' : $aPOST['hnov'];
            $hpos = empty($aPOST['hpos']) ? '' : $aPOST['hpos'];

            if (ConfigGlobal::is_debug_mode()) {
                // Url original de la que se ha ehecho el hash. Para comparar con la actual.
                $horig = empty($aPOST['horig']) ? '' : $aPOST['horig'];
                $hhorig = empty($aPOST['hhorig']) ? '' : $aPOST['hhorig'];
                /*
                $horig = empty($aPOST['horig'])? '' : rawurldecode($aPOST['horig']);
                $hhorig = empty($aPOST['hhorig'])? '' : rawurldecode($aPOST['hhorig']);
                */
            }
            // Si es un form, paso la lista de campos posibles.
            if (isset($aPOST['hh'])) {
                unset($aPOST['PHPSESSID']);
                unset($aPOST['atras']);
                unset($aPOST['h']);
                //unset($aPOST['hc']);
                unset($aPOST['horig']);
                unset($aPOST['hh']);
                unset($aPOST['hhc']);
                unset($aPOST['hhorig']);
                unset($aPOST['hno']);
                unset($aPOST['hchk']);
                unset($aPOST['hnov']);

                // Que los campos hidden sean los mismos y con los mismos valores.
                //lista de campos hidden
                $a_campos = explode('!', $hhc);
                $aCampos = array();
                foreach ($a_campos as $campo) {
                    if (empty($campo)) continue;
                    // no puedo usar empty por los valores '0'
                    if (isset($aPOST[$campo])) {
                        //$aCampos[$campo] = rawurldecode($aPOST[$campo]);
                        $aCampos[$campo] = $aPOST[$campo];
                    } else {
                        $aCampos[$campo] = '';
                    }
                }
                $h2 = self::getHashArray($aCampos)['hash'];
                $sUrl = self::getHashArray($aCampos)['orig'];

                if ($hh !== $h2) {
                    $salta = 1;
                    if (ConfigGlobal::is_debug_mode()) {
                        $salta_txt = _("llegan campos hidden modificados");
                        $horig = $hhorig;
                    }
                } else {
                    // Que los campos checkbox sean los mismos sin tener en cuenta los mismos valores.
                    //lista de campos chck
                    $a_campos = explode('!', $hchk);
                    foreach ($a_campos as $campo) {
                        if (empty($campo)) continue;
                        $aPOST[$campo] = '';
                    }

                    // Si vengo por web\Posicion, el hash es de toda la url.
                    // Con los formularios, como en algunos casos se cambia el action, sólo compruebo los campos.
                    if ($hpos == 1) {
                        $aParamSorted = self::ordenarArrayParam($aPOST);
                        $sUrl = $this->realFullUrl();
                        if (!empty($aParamSorted)) {
                            $sUrl .= '?' . http_build_query($aParamSorted);
                        }
                        $rta = self::md($sUrl);
                    } else {
                        // El hash de los campos Form, Tiene que ser sin valores, pues cuando se ha
                        //  generado, los campos estaban vacíos.
                        $rta = self::getHashArray($aPOST, 1);
                    }
                    $h2 = $rta['hash'];
                    $sUrl = $rta['orig'];
                    if ($h1 !== $h2) {
                        $salta = 1;
                        if (ConfigGlobal::is_debug_mode()) {
                            $salta_txt = _("llegan distinto número o nombre de los campos que se dice que se envian");
                        }
                    }
                }
            } else {
                unset($aPOST['PHPSESSID']);
                unset($aPOST['atras']);
                unset($aPOST['h']);
                unset($aPOST['horig']);
                unset($aPOST['hh']);
                unset($aPOST['hhc']);
                unset($aPOST['hhorig']);
                // campos que se deben quitar del hash. (separados por '!').
                $hno = empty($aPOST['hno']) ? '' : $aPOST['hno'];
                if (!empty($hno)) {
                    $a_campos_no = explode('!', $hno);
                    foreach ($a_campos_no as $campo) {
                        unset($aPOST[$campo]);
                    }
                }
                unset($aPOST['hno']);
                unset($aPOST['hchk']);
                unset($aPOST['hnov']);
                ksort($aPOST);

                if ($hnov == 1) { // borro posibles los valores de los campos
                    foreach ($aPOST as $camp => $valor) {
                        $aPOST[$camp] = '';
                    }
                }

                $sUrl = $this->realFullUrl();
                if (!empty($aPOST)) {
                    if (is_array($aPOST)) {
                        array_walk($aPOST, 'core\poner_empty_on_null');
                    }
                    $sUrl .= '?' . http_build_query($aPOST);
                }

                //echo "ccc: $sUrl<br>";
                $rta = self::md($sUrl);
                $h2 = $rta['hash'];
                //$h2orig = $rta['orig'];

                if ($h1 !== $h2) {
                    $salta = 1;
                    $salta_txt = _("url modificada");
                }
            }
            if ($salta == 1) {
                if (ConfigGlobal::is_debug_mode()) {
                    $salta_txt .= '<br>';
                    $salta_txt .= 'script: ' . $_SERVER['SCRIPT_NAME'] . '<br>';
                    $salta_txt .= "url (h1) emisor  : $horig<br>";
                    $salta_txt .= "url (h2) receptor: $sUrl<br>";
                    //$salta_txt .= "h1: $h1; h2: $h2;  ".var_dump($h1===$h2);
                    echo $salta_txt;
                    $err = _("problema general de seguridad") . "\n";
                    $_SESSION['oGestorErrores']->addErrorSec($err, $salta_txt, __LINE__, __FILE__);
                    exit();
                } else {
                    $salta_txt = $_SERVER["PHP_SELF"];
                    $err = _("problema general de seguridad") . "\n";
                    $err .= _("para ver más detalles activa el mode debug en core\confgiGlobal");
                    $_SESSION['oGestorErrores']->addErrorSec($err, $salta_txt, __LINE__, __FILE__);
                    // Para salir de la sesión.
//					session_start();
                    // Destruir todas las variables de sesión.
                    $_SESSION = array();
                    //$GLOBALS = array(); # error en php8.1
                    // Si se desea destruir la sesión completamente, borre también la cookie de sesión.
                    // Nota: ¡Esto destruirá la sesión, y no la información de la sesión!
                    if (ini_get("session.use_cookies")) {
                        $params = session_get_cookie_params();
                        setcookie(session_name(), '',
                            ['expires' => time() - 42000,
                                'path' => $params["path"],
                                'domain' => $params["domain"],
                                'secure' => $params["secure"],
                                'httponly' => $params["httponly"],
                                'sameSite' => 'Strict',
                            ]
                        );
                    }
                    // Finalmente, destruir la sesión.
                    session_regenerate_id();
                    session_destroy();
                    $pagina_exit = "/" . ConfigGlobal::WEBDIR . "/index.php";
                    header("Location: $pagina_exit");
                    die();
                }
            }
        } else {
            //evito los scripts y si va por command line (no existe REQUEST URI)
            $salta = 0;
            if (!isset($_SERVER["REQUEST_URI"])) {
                $salta = 1;
            } else {
                if (strpos($_SERVER["REQUEST_URI"], '/index.php') !== false) {
                    $salta = 1;
                }
                if (strpos($_SERVER["REQUEST_URI"], 'udm4') !== false) {
                    $salta = 1;
                }
            }
            if ($salta == 0) {
                echo "<div>" . _("página con seguridad desactivada") . "</div>";
            }
        }
    }

    /* METODES GET AND SETTERS  -----------------------------------------------------------*/

    /**
     * @param array $aCampos
     * @param string $valor 'sin_valor' Para no tener en cuenta los valores de los campos en el hash
     * @return array  'hash' =>
     *                 'orig' =>
     */
    private static function getHashArray($aCampos, $sin_valor = 0)
    {
        $aCampos['hnov'] = $sin_valor;
        $aParamSorted = self::ordenarArrayParam($aCampos);
        $sUrl_full = '';
        if (!empty($aParamSorted)) $sUrl_full = http_build_query($aParamSorted, '', '&');
        $sUrl_full = str_replace('%21', '!', $sUrl_full);

        $rta = self::md($sUrl_full);
        return $rta;
    }

    private function realFullUrl()
    {
        if (empty($_SERVER["SERVER_PORT"])) {
            $port = '';
        } else {
            $port = ':' . $_SERVER["SERVER_PORT"];
        }

        $sUrl = '//' . $_SERVER["SERVER_NAME"] . $port . $_SERVER["SCRIPT_NAME"];

        return $sUrl;
    }

    /**
     * Devuelve el html para poner dentro de un form, con los campos necesarios para
     * enviar y calcular el hash.
     * Se incluyen los campos hidden que se hayan definido en la clase Hash
     * Se incluyen los campos de comprobacion (h, hc, horig, hh, hhc, hhorig, hno, hchkk, hnov)
     *     h: el hash de los campos del form (hc) logicamente sin valor, pues se puede cambiar en el form.
     *     hh: el hash de los campos Hidden (hhc) con sus valores, que no se pueden cambiar.
     *
     * @return string html
     */
    public function getCamposHtml()
    {
        $this->addHiddenToForm();

        $CamposFormSorted = $this->ordenarQuery($this->sCamposForm);
        $rta = self::md($CamposFormSorted);
        $HashCamposForm = $rta['hash'];
        $HashCamposFormOrig = $rta['orig'];

        $CamposHidden = $this->array2stringCamposHidden();
        $aCamposHidden = $this->getArrayCamposHidden();
        $CamposNo = $this->sCamposNo;
        $CamposChk = $this->sCamposChk;

        $aCamposNo = array();
        if (!empty($CamposNo)) {
            $aCamposNo = explode('!', $CamposNo);
        }
        $aCampos = array();
        foreach ($aCamposHidden as $campo => $valor) {
            //los camposNo, valor = ''.
            if (!empty($CamposNo) && in_array($campo, $aCamposNo)) {
                $aCampos[$campo] = '';
            } else {
                $aCampos[$campo] = $valor;
            }
        }

        $rta = self::getHashArray($aCampos);
        $CamposHidden_hash = $rta['hash'];
        $CamposHidden_horig = $rta['orig'];

        $html = "<input type=\"Hidden\" name=\"h\" value=\"$HashCamposForm\" >\n";
        if (ConfigGlobal::is_debug_mode()) {
            $html .= "<input type=\"Hidden\" name=\"horig\" value=\"$HashCamposFormOrig\" >\n";
        }
        if (!empty($CamposNo)) {
            $html .= "<input type=\"Hidden\" name=\"hno\" value=\"$CamposNo\">\n";
        }
        if (!empty($CamposChk)) {
            $html .= "<input type=\"Hidden\" name=\"hchk\" value=\"$CamposChk\">\n";
        }
        $html .= "<input type=\"Hidden\" name=\"hhc\" value=\"$CamposHidden\" >\n";
        $html .= "<input type=\"Hidden\" name=\"hh\" value=\"$CamposHidden_hash\" >\n";
        if (ConfigGlobal::is_debug_mode()) {
            $html .= "<input type=\"Hidden\" name=\"hhorig\" value=\"$CamposHidden_horig\" >\n";
        }
        $html .= $this->getCamposHiddenHtml();
        return $html;
    }

    /**
     * Afagir a la llista de camps Form, els camps hidden.
     *
     */
    private function addHiddenToForm()
    {
        $sCamposForm = $this->array2stringCamposHidden();
        $this->sCamposForm .= empty($this->sCamposForm) ? $sCamposForm : '!' . $sCamposForm;
    }

    /**
     * Devuelve los camposHidden del array en un srting separados por '!'
     *
     * @return string
     */
    private function array2stringCamposHidden()
    {
        $sCamposHidden = '';
        $aCamposHidden = $this->aCamposHidden;
        if (!empty($aCamposHidden)) {
            foreach ($aCamposHidden as $campo => $valor) {
                $sCamposHidden .= empty($sCamposHidden) ? $campo : '!' . $campo;
            }
        } else { //hay que pasar algo para que lo identifique como formulario y no un link.
            $this->setArrayCamposHidden(array('hola' => 'adios'));
            $sCamposHidden = $this->array2stringCamposHidden();
        }
        return $sCamposHidden;
    }

    /**
     * estableix el valor del atribut ArrayCamposHidden
     *
     * @param array aCamposHidden
     */
    public function setArrayCamposHidden($aCamposHidden)
    {
        $this->aCamposHidden = $aCamposHidden;
    }

    /**
     * Ordenar, para asegurar que es el mismo orden al crearlo que al comprobar
     * No tiene en cuenta los valores
     * me salto los camposNo.
     * Añado los campos Chk (si el valor es null, no se pasan).
     *
     * @param string $sCampos (separados por '!')
     * @return string[]
     */
    private function ordenarQuery($sCampos)
    {
        $a_campos = explode('!', $sCampos);
        $CamposNo = $this->sCamposNo;
        $aCamposNo = array();
        if (!empty($CamposNo)) {
            $aCamposNo = explode('!', $CamposNo);
        }
        $CamposChk = $this->sCamposChk;
        $aCamposChk = array();
        if (!empty($CamposChk)) {
            $aCamposChk = explode('!', $CamposChk);
            $a_campos = array_merge($a_campos, $aCamposChk);
        }

        $sQuery = '';
        sort($a_campos);
        $aCampos = array();
        foreach ($a_campos as $campo) {
            if (!empty($CamposNo) && in_array($campo, $aCamposNo)) continue; //me salto los camposNo.
            // ???????????me salto los campos vacios que no sean chk.
            if (empty($campo) && !in_array($campo, $aCamposChk)) continue; //me salto los campos vacios que no sean chk.
            //if (empty($campo)) continue;
            $sQuery .= empty($sQuery) ? $campo . '=' : '&' . $campo . '=';
            $aCampos[$campo] = '';
        }

        if (is_array($aCampos)) {
            array_walk($aCampos, 'core\poner_empty_on_null');
        }
        $sQuery = http_build_query($aCampos);

        return $sQuery;
    }

    /**
     * recupera el valor del atribut ArrayCamposHidden
     *
     */
    public function getArrayCamposHidden()
    {
        return $this->aCamposHidden;
    }

    /**
     * Devuelve el html de los campos hidden con su valor
     *
     * @return string html
     */
    private function getCamposHiddenHtml()
    {
        $prefix = empty($this->prefix) ? '' : $this->prefix . '_';
        $aCamposHidden = $this->aCamposHidden;
        $sCamposHiddenHtml = '';
        foreach ($aCamposHidden as $campo => $valor) {
            if (is_array($valor)) {
                $i = 0;
                foreach ($valor as $val) {
                    $nom = $campo . "[$i]";
                    $sCamposHiddenHtml .= "<input type=\"hidden\" id=\"$prefix$nom\" name=\"$nom\" value=\"$val\">\n";
                    $i++;
                }

            } else {
                $sCamposHiddenHtml .= "<input type=\"hidden\" id=\"$prefix$campo\" name=\"$campo\" value=\"$valor\">\n";
            }
        }
        return $sCamposHiddenHtml;
    }

    /**
     * Calcula el hash de los campos para añadir en los link.
     * Genera la url completa: url + camposHidden + hash
     *    campos hidden con sus calores
     *    camposNo no se tienen en cuenta. (se pueden añadir al final en las funciones javascript)
     *    camposForm sin valores. (se deben añadir al final en las funciones javascript)
     *
     * Devuelve la url + camposHidden + los parámetros h.
     */
    public function linkConVal()
    {
        $this->addHiddenToForm();
        $sQuery = $this->sCamposForm;
        $CamposFormSorted = $this->ordenarQuery($this->sCamposForm);
        $rta = self::md($CamposFormSorted);
        $HashCamposForm = $rta['hash'];
        $HashCamposFormOrig = $rta['orig'];

        $CamposHidden = $this->array2stringCamposHidden();
        $aCamposHidden = $this->getArrayCamposHidden();
        $CamposNo = $this->sCamposNo;
        $CamposChk = $this->sCamposChk;

        $aCamposNo = array();
        if (!empty($CamposNo)) {
            $aCamposNo = explode('!', $CamposNo);
        }
        $aCampos = array();
        foreach ($aCamposHidden as $campo => $valor) {
            //los camposNo, valor = ''.
            if (!empty($CamposNo) && in_array($campo, $aCamposNo)) {
                $aCampos[$campo] = '';
            } else {
                $aCampos[$campo] = $valor;
            }
        }

        $rta = self::getHashArray($aCampos);
        $CamposHidden_hash = $rta['hash'];
        $CamposHidden_orig = $rta['orig'];

        $url = $this->sUrl;
        $sUrl_full = self::FullPath($url);

        $sQuery = '?';
        $sQuery .= "h=$HashCamposForm";
        if (!empty($CamposNo)) {
            $sQuery .= "&hno=$CamposNo";
        }
        if (!empty($CamposChk)) {
            $sQuery .= "&hchk=$CamposChk";
        }
        $sQuery .= "&hhc=$CamposHidden";
        $sQuery .= "&hh=$CamposHidden_hash";
        if (ConfigGlobal::is_debug_mode()) {
            // OJO. Si paso los parametros normalmente, la lista de campos orig
            //    se interpreta como campos a añadir.
            //$sQuery .= "&horig=$HashCamposFormOrig";
            //$sQuery .= "&hhorig=$CamposHidden_orig";
        }

        $sQuery .= '&' . $this->array2queryCamposHidden();

        return $sUrl_full . $sQuery;
    }
    /* MÉTODOS PRIVADOS -----------------------------------------------------------*/

    /**
     * Devuelve los camposHidden del array en un srting para una url
     *
     * @return string
     */
    private function array2queryCamposHidden()
    {
        $sCamposHidden = '';
        $aCamposHidden = $this->aCamposHidden;
        if (!empty($aCamposHidden)) {
            foreach ($aCamposHidden as $campo => $valor) {
                $sCamposHidden .= empty($sCamposHidden) ? "$campo=$valor" : '&' . "$campo=$valor";
            }
        } else { //hay que pasar algo para que lo identifique como formulario y no un link.
            $this->setArrayCamposHidden(array('hola' => 'adios'));
            $sCamposHidden = $this->array2stringCamposHidden();
        }
        return $sCamposHidden;
    }

    /**
     * Calcula el hash de los campos del form sin valores
     * para añadir en los link
     *
     * Devuelve una cadena para añadir a la url con los parámetros: hnov=1, h, horig
     *    hnov sirve para indicar al receptor que el hash se ha hecho sin los valores.
     *
     * @return string
     */
    public function linkSinVal()
    {
        $sQuery = $this->sCamposForm;
        $CamposFormSorted = $this->ordenarQuery($this->sCamposForm);

        $url = $this->sUrl;
        $sUrl_full = self::FullPath($url);
        if (!empty($CamposFormSorted)) {
            $sUrl_full .= '?' . $CamposFormSorted;
        }

        $rta = self::md($sUrl_full);
        $HashCamposForm = $rta['hash'];
        $HashCamposFormOrig = $rta['orig'];

        if (!empty($sQuery)) {
            $query = '&hnov=1&h=' . $HashCamposForm;
        } else {
            $query = '?hnov=1&h=' . $HashCamposForm;
        }
        if (ConfigGlobal::is_debug_mode()) {
            $query .= '&horig=' . rawurlencode($HashCamposFormOrig);
        }
        return $query;
    }

    /**
     * Devuelve los campos en una cadena para usar en llamadas ajax
     *
     * return string
     */
    public function getParamAjax()
    {
        $sUrl = $this->getUrl();
        $this->addHiddenToForm();

        $CamposFormSorted = $this->ordenarQuery($this->sCamposForm);
        $rta = self::md($CamposFormSorted);
        $HashCamposForm = $rta['hash'];
        $HashCamposFormOrig = $rta['orig'];

        $CamposHidden = $this->array2stringCamposHidden();
        $aCamposHidden = $this->getArrayCamposHidden();
        $CamposNo = $this->sCamposNo;
        $CamposChk = $this->sCamposChk;

        $aCamposNo = array();
        if (!empty($CamposNo)) {
            $aCamposNo = explode('!', $CamposNo);
        }
        $aCamposH = array();
        foreach ($aCamposHidden as $campo => $valor) {
            //los camposNo, valor = ''.
            if (!empty($CamposNo) && in_array($campo, $aCamposNo)) {
                $aCamposH[$campo] = '';
            } else {
                $aCamposH[$campo] = $valor;
            }
        }
        $rtaH = self::getHashArray($aCamposH);
        $hh = $rtaH['hash'];
        $sparam = $rtaH['orig'];

        $h = $HashCamposForm;
        $hhc = $CamposHidden;

        return "h=$h&hh=$hh&hhc=$hhc&" . $sparam;

    }

    /**
     * recupera el valor del atribut Url
     *
     */
    public function getUrl()
    {
        return $this->sUrl;
    }

    /**
     * Devuelve los campos en una cadena para usar en llamadas ajax
     *
     * return string $json_param .= "$parametro: '$valor'";
     */
    public function getParamAjaxEnArray()
    {
        $sUrl = $this->getUrl();
        $this->addHiddenToForm();

        $CamposFormSorted = $this->ordenarQuery($this->sCamposForm);
        $rta = self::md($CamposFormSorted);
        $HashCamposForm = $rta['hash'];
        $HashCamposFormOrig = $rta['orig'];

        $CamposHidden = $this->array2stringCamposHidden();
        $aCamposHidden = $this->getArrayCamposHidden();
        $CamposNo = $this->sCamposNo;
        $CamposChk = $this->sCamposChk;

        $aCamposNo = array();
        if (!empty($CamposNo)) {
            $aCamposNo = explode('!', $CamposNo);
        }
        $aCamposH = array();
        foreach ($aCamposHidden as $campo => $valor) {
            //los camposNo, valor = ''.
            if (!empty($CamposNo) && in_array($campo, $aCamposNo)) {
                $aCamposH[$campo] = '';
            } else {
                $aCamposH[$campo] = $valor;
            }
        }
        $rtaH = self::getHashArray($aCamposH);
        $hh = $rtaH['hash'];
        $sparam = $rtaH['orig'];

        $h = $HashCamposForm;
        $hhc = $CamposHidden;

        // poner los param en formato " var: 'valor', "
        $aParams = explode('&', $sparam);
        $json_param = "h:'$h', hh:'$hh', hhc:'$hhc'";
        foreach ($aParams as $Param) {
            $parametro = strtok($Param, '=');
            $valor = strtok('=');
            $json_param .= ', ';
            $json_param .= "$parametro: '$valor'";
        }

        return $json_param;
    }

    /**
     * estableix el valor del atribut Url
     *
     * @param string sUrl
     */
    public function setUrl($sUrl)
    {
        $this->sUrl = $sUrl;
    }

    /**
     * estableix el valor del atribut CamposChk
     *
     * @param string sCamposChk
     */
    public function setCamposChk($sCamposChk)
    {
        $this->sCamposChk = $sCamposChk;
    }

    /**
     * recupera el valor del atribut CamposChk
     *
     */
    public function getCamposChk()
    {
        return $this->sCamposChk;
    }

    /**
     * estableix el valor del atribut CamposForm
     *
     * @param string sCamposForm
     */
    public function setCamposForm($sCamposForm)
    {
        $this->sCamposForm = $sCamposForm;
    }

    /**
     * recupera el valor del atribut CamposForm
     *
     */
    public function getCamposForm()
    {
        return $this->sCamposForm;
    }

    /**
     * estableix el valor del atribut CamposNo
     *
     * @param string sCamposNo
     */
    public function setCamposNo($sCamposNo)
    {
        $this->sCamposNo = $sCamposNo;
    }

    /**
     * recupera el valor del atribut CamposNo
     *
     */
    public function getCamposNo()
    {
        return $this->sCamposNo;
    }

    /**
     * @return mixed
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param mixed $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

}
