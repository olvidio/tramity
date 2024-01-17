<?php

namespace etherpad\model;

use convertirdocumentos\model\DocConverter;
use core\ConfigGlobal;
use core\ServerConf;
use DOMDocument;
use DOMXPath;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use web\StringLocal;

/**
 * INFO EN:
 *
 * https://etherpad.org/doc/v1.7.0/
 *
 */
class Etherpad extends Client
{

    // Tipos de id
    public const ID_COMPARTIDO = 'compartido';
    public const ID_ADJUNTO = 'adjunto';
    public const ID_DOCUMENTO = 'documento';
    public const ID_ENTRADA = 'entrada';
    public const ID_ESCRITO = 'escrito';
    public const ID_EXPEDIENTE = 'expediente';
    public const ID_PLANTILLA = 'plantilla';

    private $id_usuario = null;
    private $nom_usuario = null;

    private $id_escrito = null;
    private $textContent = null;
    private $multiple = FALSE;

    /**
     */
    public function __construct($url = '', $id_usuario = '')
    {
        if (empty($url)) {
            $url = $_SESSION['oConfig']->getServerEtherpad();
        }
        $this->url = $url;

        if (empty($id_usuario)) {
            $id_usuario = ConfigGlobal::mi_id_usuario();
        }
        $nom_usuario = ConfigGlobal::mi_usuario();

        $this->setId_usuario($id_usuario);
        $this->setNom_usuario($nom_usuario);

        // depende si es en el portátil o en la dl.
        $this->api_version = $this->api_version_dlb;
        $this->apikey = $this->apikey_dlb;
        if (str_contains(ServerConf::getSERVIDOR(), 'docker')) {
            $this->apikey = $this->apikey_local;
            $this->api_version = $this->api_version_docker;
        }
        if (ServerConf::getSERVIDOR() === 'tramity.local') {
            $this->apikey = $this->apikey_local;
            $this->api_version = $this->api_version_dlb;
        }
        if (str_contains(ServerConf::getSERVIDOR(), 'bayona')) {
            $this->api_version = $this->api_version_dlp;
        }

        parent::__construct($this->apikey, $this->url);
    }

    public function setId($tipo_id, $id, $sigla = '')
    {
        // excepción para las entradas compartidas:
        if ($tipo_id === self::ID_COMPARTIDO) {
            $prefix = 'com';
            $this->id_escrito = $prefix . $id;
        } else {
            // Añado el nombre del centro. De forma normalizada, pues a saber que puede tener el nombre:
            if (empty($sigla)) {
                $sigla = $_SESSION['oConfig']->getSigla();
            }
            $nom_ctr = StringLocal::toRFC952($sigla);

            switch ($tipo_id) {
                case self::ID_ADJUNTO:
                    $prefix = 'adj';
                    break;
                case self::ID_DOCUMENTO:
                    $prefix = 'doc';
                    break;
                case self::ID_ENTRADA:
                    $prefix = 'ent';
                    break;
                case self::ID_ESCRITO:
                    $prefix = 'esc';
                    break;
                case self::ID_EXPEDIENTE:
                    $prefix = 'exp';
                    break;
                case self::ID_PLANTILLA:
                    $prefix = 'plt';
                    break;
                default:
                    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_switch);
            }
            $this->id_escrito = $nom_ctr . "*" . $prefix . $id;
        }
        return $this->id_escrito;
    }

    /**
     * devuelve el escrito en html.
     *
     * @return string html
     */
    public function generarHtml()
    {
        $html = '';
        $contenido = $this->cleanHtml();
        $html .= "<style>
                    p { margin-top:0; margin-bottom:0; }
                    table { width: 800px; border:1px solid black; }
                    td { padding: 2mm; border: 1px solid black; vertical-align: middle;}
                 </style>";

        $html .= '<div id="escrito" >';
        $html .= $contenido;
        $html .= '</div>';

        return $html;
    }

    /**
     * devuelve el escrito en html.
     *
     * @return string html
     */
    private function cleanHtml()
    {
        $contenido = $this->getHHtml();

        /*
        // otra forma canbiar <br>xxx<br> por <p>xxx</p>
        $pattern = "/(?:<p[^>]*>\s*)?([^<>\s][^<>]*)(?:<(?:br\s*\/?>\s*<br\s*\/?|\/p[^>]*)>(?:\s*\n)?|$)/";
        $replace = "<p>$1</p>\n";
        $contenido2 = preg_replace($pattern, $replace, $contenido);
*/

        $dom = new DOMDocument;
        /* la '@' sirve para evita los errores:  Warning: DOMDocument::loadHTML()
         *
         * loadHTML expects valid markup, i’m afraid most page’s arn’t.
         * You can alter the code to suppress markup errors:-
         *      $file = @$doc->loadHTML($remote);
         */
        @$dom->loadHTML($contenido);

        /* Quitar las marcas de comentarios:
         *
         * <span class="comment c-kvHZBxlaw3uVND0O"> donar permisos
         * <sup><a href="#c-kvHZBxlaw3uVND0O">*</a></sup></span>
         *
         */
        $domNodeList = $dom->getElementsByTagname('span');
        foreach ($domNodeList as $domElement) {
            // mirar si es: class="comment
            $class = $domElement->getAttribute('class');
            if (strpos($class, 'comment') !== false) {
                $domNodeListChilds = $domElement->childNodes;
                foreach ($domNodeListChilds as $domElementChild) {
                    // borrar los child: <sup></sup>
                    $nodeName = $domElementChild->nodeName;
                    if ($nodeName === 'sup') {
                        $domElement->removeChild($domElementChild);
                    }
                }
                // Move all span tag content to its parent node just before it.
                while ($domElement->hasChildNodes()) {
                    $child = $domElement->removeChild($domElement->firstChild);
                    $domElement->parentNode->insertBefore($child, $domElement);
                }
                // Remove the span tag.
                $domElement->parentNode->removeChild($domElement);
            }
        }
        $dom->normalizeDocument();

        $xpath = new DOMXPath($dom);
        /* Quitar los <td> con display:none:
         * <td class="regex-delete" name="payload" style="display:none;">{"payload":[["</td>
         * <td class="regex-delete" name="delimCell" id="" style="display:none;">","</td>
         * <td class="regex-delete" name="bracketAndcomma" style="display:none;">"]],"tblId":"1","tblClass":"data-tables", "tblProperties":{"borderWidth":"1","cellAttrs":[],"width":"100","rowAttrs":{},"colAttrs":[],"authors":{}}}</td>
         */
        // Selects tags to be processed.
        $tags_list = $xpath->query("//td");
        foreach ($tags_list as $tag) {
            $class = $tag->getAttribute('name');
            if (strpos($class, 'payload') !== false || strpos($class, 'delimCell') !== false || strpos($class, 'bracketAndcomma') !== false) {
                // Remove the td tag.
                $tag->parentNode->removeChild($tag);
            }
        }

        /*
         * Quitar los tag sin contenido. Tipico:
         *  <p style="text-ailgn:justify"></p>
         */
        // Selects tags to be processed.
        $tags_list = $xpath->query("//p|//br|//a|//strong|//img|//ul|//ol|//li|//em|//u|//s|//hr|//blockquote");
        foreach ($tags_list as $tag) {
            // Checks and deletes tags with empty content.
            if (in_array($tag->tagName, ['p', 'a', 'strong', 'blockquote']) && $tag->nodeValue == "") {
                $tag->parentNode->removeChild($tag);
            }
        }

        //$xpath = new \DOMXPath($dom);
        // Quitar los atributos style
        $tags_list = $xpath->query("//table|//tr|//td");
        foreach ($tags_list as $tag) {
            $tag->removeAttribute('style');
            $tag->removeAttribute('name');
            $tag->removeAttribute('class');
        }

        // Quitar los atributos label
        $tags_list = $xpath->query("//label");
        foreach ($tags_list as $tag) {
            $tag->parentNode->removeChild($tag);
        }

        // lista de los tagg 'body'
        $bodies = $dom->getElementsByTagName('body');

        // cojo el primero de la lista: sólo debería haber uno.
        $body = $bodies->item(0);
        //$txt = $body->C14N(); //innerhtml convierte <br> a <br></br>. Se usa lo de abajo:
        $txt = $body->ownerDocument->saveHTML($body);
        $txt2 = substr($txt, 6); // Quitar el tag <body> inicial
        $txt3 = substr($txt2, 0, -7); // Quitar el tag </body> final

        // Unificar posibles <br />  a <br> simple>
        $pattern = "/<br.*?\/?>/";
        $txt3_1 = preg_replace($pattern, "<br>", $txt3);
        //<br value="tblBreak">
        $txt3_2 = str_replace("<br value=\"tblBreak\">", "", $txt3_1);

        // añadir tag <br> al inicio
        $txt3_4 = '<br>' . $txt3_2;

        // Añadir <br> delante del texto entre una etiqueta distinta de <br> y el siguiente <br>
        $pattern = "/<.*?>(?<!<br>)([^<]+)(?=<br)/";
        $txt3_5 = preg_replace($pattern, "<br>$1", $txt3_4);

        // y después poner entre <p> el texto entre <br> (siempre que no esté ya encapsulado en algo '<xx>')
        $pattern = "/<br\ ?\/?>([^<].*?)<br\ ?\/?>/";
        $txt4 = preg_replace($pattern, "<p>$1</p><br>", $txt3_5);

        // También poner entre <p> el texto entre <br> y <p>
        $pattern = "/<br\ ?\/?>([^<].*?)<p\ ?\/?>/";
        $txt4_1 = preg_replace($pattern, "<p>$1</p><p>", $txt4);
        // eliminar párrafos vacíos: <p></p>
        $txt4_2 = str_replace("<p></p>", "", $txt4_1);

        // acabar bien los <ul>
        $pattern = "/(?<!<\/li>)<\/ul>/";
        $txt5 = preg_replace($pattern, "</li>$1", $txt4_2);


        // salto de página (4 o más ':' entre dos saltos de línea
        /* $txt7 = str_replace("/<br( *\/)?>:{4,}<br( *\/)?>/", "<div style=\"page-break-after: always;\"></div>", $txt6); */
        $txt6 = preg_replace("/:{4,}/", "<div class='salta_pag'></div>", $txt5);

        // eliminar dobles lineas: <br><br>
        //$txt3_5 = str_replace("<br><br>", "<br>", $txt3_4);
        // eliminar todos los <br>
        $txt7 = str_replace("<br>", "", $txt6);
        //$txt4 = str_replace("</p><br>", "</p>", $txt3_5);
        //$txt5 = str_replace("</table><br>", "</table>", $txt4);


        return str_replace("</tbody></table><table><tbody>", "", $txt7);
    }

    public function getHHtml()
    {
        $padId = $this->getPadID();

        // comprobar que no existe:
        // returns all pads of this group
        $rev = null;
        $rta = $this->getHTML($padId, $rev);
        $code = $rta->getCode();
        if ($code == 0) {
            $data = $rta->getData();
            /* Example returns:
             * {code: 0, message:"ok", data: {html:"Welcome Text<br>More Text"}}
             * {code: 1, message:"padID does not exist", data: null}
             */
            $html = $data['html'];
            return $html;
        } else {
            $this->mostrar_error($rta);
        }
    }

    public function getPadID()
    {
        if (empty($this->id_escrito)) {
            die (_("Debe indicar el id con setId"));
        }
        // obtener o crear el pad
        $PadID = $this->getId_pad();
        // Conceder permisos (crear sesión)
        $this->addPerm();

        return $PadID;
    }

    public function getId_pad()
    {
        $groupID = $this->getGroupId();
        $padId = $groupID . "$" . $this->id_escrito;

        // comprobar que no existe:
        // returns all pads of this group
        $rta = $this->listPads($groupID);
        $code = $rta->getCode();
        if ($code == 0) {
            $data = $rta->getData();
            /* Example returns:
             * {code: 0, message:"ok", data: {padIDs : ["g.s8oes9dhwrvt0zif$test", "g.s8oes9dhwrvt0zif$test2"]}
             * {code: 1, message:"groupID does not exist", data: null}
             */
            $padsOfGroup = $data['padIDs'];
            if (in_array($padId, $padsOfGroup)) {
                return $padId;
            } else {
                return $this->crearPad();
            }
        } elseif ($code == 1) {
            return $this->crearPad();
        } else {
            $this->mostrar_error($rta);
        }
    }

    public function getGroupId()
    {
        // Crear grupo id_escrito
        $groupID = '';
        $rta = $this->createGroupIfNotExistsFor($this->id_escrito);
        if ($rta->getCode() == 0) {
            $data = $rta->getData();
            /* returns: * {code: 0, message:"ok", data: {groupID: g.s8oes9dhwrvt0zif}} */
            $groupID = $data['groupID'];
        } else {
            $this->mostrar_error($rta);
        }
        return $groupID;
    }

    public function crearPad()
    {
        $groupID = $this->getGroupId();
        $padName = $this->id_escrito;
        $padId = $groupID . "$" . $this->id_escrito;
        // para el caso de clonar
        $text_clone = empty($this->textContent) ? null : $this->textContent;

        $rta = $this->createGroupPad($groupID, $padName, $text_clone);
        if ($rta->getCode() == 0) {
            /* returns: {code: 0, message:"ok", data: null}
             * {code: 1, message:"pad does already exist", data: null}
             * {code: 1, message:"groupID does not exist", data: null}
             */
            return $padId;
        } else {
            $this->mostrar_error($rta);
        }
    }

    public function addPerm()
    {
        $groupID = $this->getGroupId();
        $authorID = $this->getAuthorId();
        $validUntil = date("U", strtotime("+1 hours")); // (1h)
        return $this->crearSession($groupID, $authorID, $validUntil);

    }

    public function getAuthorId()
    {
        // Crear usuario dani (7)
        $authorID = '';
        $rta = $this->createAuthorIfNotExistsFor($this->id_usuario, $this->nom_usuario);
        if ($rta->getCode() == 0) {
            $data = $rta->getData();
            /* returns: * {code: 0, message:"ok", data: {authorID: "a.s8oes9dhwrvt0zif"}} */
            $authorID = $data['authorID'];
        } else {
            $this->mostrar_error($rta);
        }
        return $authorID;
    }

    public function crearSession($groupID, $authorID, $validUntil)
    {
        // comprobar si ya está la session:
        /* Example returns:
         *
         * {code: 0,"message":"ok","data":{"s.oxf2ras6lvhv2132":{"groupID":"g.s8oes9dhwrvt0zif","authorID":"a.akf8finncvomlqva","validUntil":2312905480}}}
         * {code: 1, message:"authorID does not exist", data: null}
         */
        $lista = $this->listSessionsOfAuthor($authorID);
        if ($lista->getCode() == 0) {
            $data = $lista->getData();
            // Cuando el usuario entra por primera vez da NULL
            if (!empty($data)) {
                foreach ($data as $sessionID => $aData) {
                    if ($aData === NULL) {
                        continue;
                    }
                    $group = $aData['groupID'];
                    $author = $aData['authorID'];
                    $valid = $aData['validUntil'];
                    if ($group == $groupID && $author == $authorID) {
                        $ahora = date("U");
                        // Además hay que asegurar que está también la cookie
                        if ($valid > $ahora && isset($_COOKIE["sessionID"])) {
                            return TRUE;
                        }
                    }
                }
            }
        } else {
            // Da igual, porque borro todo. En alguna instalación da el error:
            //  *Error: wrong parameters sessionID does not exist**
            //$this->mostrar_error($lista);
        }

        // Si no está borrón y cuenta nueva.
        $this->deleteAllSessions($authorID);

        /* Example returns:
         *
         * {code: 0, message:"ok", data: {sessionID: "s.s8oes9dhwrvt0zif"}}
         * {code: 1, message:"groupID doesn't exist", data: null}
         * {code: 1, message:"authorID doesn't exist", data: null}
         * {code: 1, message:"validUntil is in the past", data: null}
         */
        $rta = $this->createSession($groupID, $authorID, $validUntil);
        if ($rta->getCode() == 0) {
            $data = $rta->getData();
            $sessionID = $data['sessionID'];
            $this->addSessionCookie($sessionID);
        } else {
            $this->mostrar_error($rta);
        }
    }

    private function deleteAllSessions($authorID)
    {
        $rta = $this->listSessionsOfAuthor($authorID);
        if ($rta->getCode() == 0) {
            $data = $rta->getData();
            /* Example returns:
             * {"code":0,"message":"ok","data":{"s.oxf2ras6lvhv2132":{"groupID":"g.s8oes9dhwrvt0zif","authorID":"a.akf8finncvomlqva","validUntil":2312905480}}}
             * {code: 1, message:"authorID does not exist", data: null}
             */
            if (is_array($data)) {
                foreach (array_keys($data) as $session) {
                    $this->borrarSesion($session);
                }
            }
        } else {
            // Da igual, porque borro todo. En alguna instalación da el error:
            //  *Error: wrong parameters sessionID does not exist**
            //$this->mostrar_error($rta);
        }

    }

    //returns the text of a pad

    private function borrarSesion($sessionID)
    {
        $rta = $this->deleteSession($sessionID);
        if ($rta->getCode() == 0) {
            /*
             Example returns:

             {code: 0, message:"ok", data: null}
             {code: 1, message:"sessionID does not exist", data: null}
             */
        } else {
            $this->mostrar_error($rta);
        }
    }


    // Crear o abrir Pad

    public function addSessionCookie($sessionID)
    {
        $authorID = $this->getAuthorId();

        //$sessionID = (!isset($_COOKIE["sessionID"]))? "" : $_COOKIE["sessionID"];
        // sesiones abiertas:
        $lista_sesiones = '';
        $rta = $this->listSessionsOfAuthor($authorID);
        if ($rta->getCode() == 0) {
            $data = $rta->getData();
            /* Example returns:
             * {"code":0,"message":"ok","data":{"s.oxf2ras6lvhv2132":{"groupID":"g.s8oes9dhwrvt0zif","authorID":"a.akf8finncvomlqva","validUntil":2312905480}}}
             * {code: 1, message:"authorID does not exist", data: null}
             */
            $a_sesiones = [];
            foreach (array_keys($data) as $session) {
                // evitar duplicar sesión
                if ($session != $sessionID) {
                    $a_sesiones[] = $session;
                }
            }
            $lista_sesiones = implode(',', $a_sesiones);
        } else {
            // Da igual, porque borro todo. En alguna instalación da el error:
            //  *Error: wrong parameters sessionID does not exist**
            //$this->mostrar_error($rta);
        }
        // añadir la session actual:
        $lista_sesiones .= empty($lista_sesiones) ? $sessionID : ",$sessionID";
        //$lista_sesiones = "$a";
        //setcookie("sessionID", $lista_sesiones, time() + (86400 * 30), "/; SameSite=Lax"); // 86400 = 1 day
        // para php >= 7.3
        // 86400 = 1 day
        // 3600 = 1 hora
        if ($this->multiple === FALSE) { // espero a mandar las cookies al final.
            // Coger el nombre del dominio para que sirva para tramity.red.local y etherpad.red.local
            //const SERVIDOR = 'tramity.red.local';
            $regs = [];
            $host = ConfigGlobal::getSERVIDOR();
            preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $host, $regs);
            $domain = $regs['domain'];

            $secure = true;
            if (str_contains(ServerConf::getSERVIDOR(), 'docker')) {
                $secure = false;
            }
            setcookie("sessionID", $lista_sesiones, [
                'expires' => time() + 8000,
                'path' => '/',
                'Secure' => $secure, // solo si es https (en docker es http)
                'HttpOnly' => false, // OJO tiene que ser false, sino no abre las ventanas del pad
                'SameSite' => 'Strict',
                'Domain' => $domain
            ]);
        }
    }

    public function grabarMD($txt)
    {
        $padId = $this->getPadID();

        $rta = $this->setText($padId, $txt);
        /* Example returns:
         *  {code: 0, message:"ok", data: null}
         *  {code: 1, message:"padID does not exist", data: null}
         *  {code: 1, message:"text too long", data: null}
         */
        $code = $rta->getCode();
        if ($code == 1) {
            $this->mostrar_error($rta);
        }
    }

    /**
     * @param string $text_content
     */
    public function setTextContent($text_content)
    {
        $this->textContent = $text_content;
    }

    /* Crear session (link usuario-grupo)
     * creates a new session. validUntil is an unix timestamp in seconds
     */

    public function generarMD()
    {
        $padId = $this->getPadID();

        // comprobar que no existe:
        // returns all pads of this group
        $rev = null;
        $rta = $this->getText($padId, $rev);
        $code = $rta->getCode();
        if ($code == 0) {
            $data = $rta->getData();
            /* Example returns:
             * {code: 0, message:"ok", data: {text:"Welcome Text"}}
             * {code: 1, message:"padID does not exist", data: null}
             */
            $text = $data['text'];
            return $text;
        } else {
            $this->mostrar_error($rta);
        }
    }

    /*
    Sessions can be created between a group and an author. This allows an author to access more than one group. 
    The sessionID will be set as a cookie to the client and is valid until a certain date. The session cookie 
    can also contain multiple comma-seperated sessionIDs, allowing a user to edit pads in different groups at 
    the same time. Only users with a valid session for this group, can access group pads. You can create a 
    session after you authenticated the user at your web application, to give them access to the pads. 
    You should save the sessionID of this session and delete it after the user logged out. 
    */

    /**
     * devuelve la ruta del fichero odt creado a partir del Etherpad.
     *
     * @param array $a_header ['left', 'center', 'right']
     * @return string ruta del fichero odt que se ha creado
     */
    public function generarODT(string $filename_sin_ext, array $a_header = [], string $fecha = ''): string
    {
        $html = $this->cleanHtml();

        return (new Etherpad2ODF())->crearFicheroOdt($filename_sin_ext, $html, $a_header, $fecha);
    }

    /**
     * devuelve la ruta del fichero en formato PDF. USANDO EL LIBREOFFICE
     *
     * @param array $a_header ['left', 'center', 'right']
     * @return string $nombre_del_fichero
     */
    public function generarLOPDF(string $filename_sin_ext, array $a_header = [], string $fecha = '')
    {
        $file_odt = $this->generarODT($filename_sin_ext, $a_header, $fecha);

        $oDocConverter = new DocConverter();
        $file_pdf = $oDocConverter->convertOdt2($file_odt, 'pdf');

        return  $file_pdf;
    }


    /**
     * devuelve el escrito en formato PDF. Usando la librería MPDF
     *
     * @param array $a_header ['left', 'center', 'right']
     * @return Mpdf
     */
    public function generarMPDF(array $a_header = [], string $fecha = ''): Mpdf
    {
        $stylesheet = "<style>
                TABLE { border: 1px solid black; border-collapse: collapse; }
                TD { padding: 2mm; border: 1px solid black; vertical-align: middle;}
                TD.header { padding: 1mm; border: 0px; vertical-align: bottom;}
                 </style>
                ";


        $txt = $this->cleanHtml();
        $txt2 = str_replace("<tbody>", "", $txt);
        $html = str_replace("</tbody>", "", $txt2);

        // convert to PDF
        require_once(ConfigGlobal::dir_libs() . '/vendor/autoload.php');

        if (!empty($a_header)) {
            $header_html = '<table class="header" width="100%">';
            $header_html .= '<tr>';
            $header_html .= '<td class="header" width="33%">';
            $header_html .= $a_header['left'];
            $header_html .= '</td><td class="header" width="33%" align="center">';
            $header_html .= $a_header['center'];
            $header_html .= '</td><td class="header" width="33%" style="text-align: right;">';
            $header_html .= $a_header['right'];
            $header_html .= '</td></tr>';
            $header_html .= '</table>';
            $header_html .= '<hr>';
        } else {
            $header_html = '';
        }

        $footer = '{PAGENO}/{nbpg}';

        if (!empty($fecha)) {
            $html .= '<div id="fecha" style="margin-top: 2em; margin-right:  0em; text-align: right; " >';
            $html .= $fecha;
            $html .= '</div>';
        }

        try {
            $config = ['mode' => 'utf-8',
                'format' => 'A4-P',
                'margin_header' => 10,
                'margin_top' => 40,

            ];
            $mpdf = new Mpdf($config);
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->list_indent_first_level = 0;    // 1 or 0 - whether to indent the first level of a list

            if (!empty($header_html)) {
                $mpdf->SetHTMLHeader($header_html);
            }
            $mpdf->SetHTMLFooter($footer);

            $mpdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
            $mpdf->WriteHTML($html);

            // Other code
            return $mpdf;
        } catch (MpdfException $e) { // Note: safer fully qualified exception name used for catch
            // Process the exception, log, print etc.
            echo $e->getMessage();
        }
    }

    public function getTexto($padID)
    {
        $rta = $this->getText($padID);
        if ($rta->getCode() == 0) {
            $data = $rta->getData();
            /* returns: {code: 0, message:"ok", data: {text:"Welcome Text"}}
             * {code: 1, message:"padID does not exist", data: null}
             */
            return $data['text'];
        } else {
            $this->mostrar_error($rta);
        }
    }

    public function eliminarPad()
    {
        $padID = $this->getPadID();
        /*
         *Example returns:
         *
         * {code: 0, message:"ok", data: null}
         * {code: 1, message:"padID does not exist", data: null}
         */
        $rta = $this->deletePad($padID);
        if ($rta->getCode() == 1) {
            $this->mostrar_error($rta);
        }

    }

    /*----------------------------------------------------------------------------------------*/

    public function getApiVersion(): string
    {
        return $this->api_version;
    }

    /**
     * @return string $url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getId_usuario()
    {
        return $this->id_usuario;
    }

    /**
     * @param mixed $id_usuario
     */
    public function setId_usuario($id_usuario)
    {
        $this->id_usuario = $id_usuario;
    }

    /**
     * @return mixed
     */
    public function getNom_usuario()
    {
        return $this->nom_usuario;
    }

    /**
     * @param mixed $nom_usuario
     */
    public function setNom_usuario($nom_usuario)
    {
        $this->nom_usuario = $nom_usuario;
    }

    /**
     * @return boolean
     */
    public function getMultiple()
    {
        return $this->multiple;
    }

    /**
     * @param boolean $multiple
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;
    }

    /**
     * Quitar todos los estilos etc de las tablas
     *
     */
    private function quitarAtributosTabla($html)
    {
        $dom = new DOMDocument;
        /* la '@' sirve para evita los errores:  Warning: DOMDocument::loadHTML()
         *
         * loadHTML expects valid markup, i’m afraid most page’s arn’t.
         * You can alter the code to suppress markup errors:-
         *      $file = @$doc->loadHTML($remote);
         */
        @$dom->loadHTML($html);

        $xpath = new DOMXPath($dom);
        // Quitar los atributos style
        $tags_list = $xpath->query("//table|//tr|//td");
        foreach ($tags_list as $tag) {
            $tag->removeAttribute('style');
            $tag->removeAttribute('name');
            $tag->removeAttribute('class');
        }

        // Quitar los atributos label
        $tags_list = $xpath->query("//label");
        foreach ($tags_list as $tag) {
            $tag->parentNode->removeChild($tag);
        }

        // save html
        //$txt = $dom->saveHTML();

        // lista de los tagg 'body'
        $bodies = $dom->getElementsByTagName('body');

        // cojo el primero de la lista: sólo debería haber uno.
        $body = $bodies->item(0);
        //$txt = $body->C14N(); //innerhtml convierte <br> a <br></br>. Se usa lo de abajo:
        $txt = $body->ownerDocument->saveHTML($body);
        $txt2 = substr($txt, 6); // Quitar el tag <body> inicial
        $txt3 = substr($txt2, 0, -7); // Quitar el tag </body> final

        return str_replace("</tbody></table><table><tbody>", "", $txt3);
    }


}