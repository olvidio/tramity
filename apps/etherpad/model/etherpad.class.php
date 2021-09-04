<?php
namespace etherpad\model;

use core\ConfigGlobal;

/**
 * INFO EN:
 * 
 * https://etherpad.org/doc/v1.7.0/
 * 
*/

class Etherpad  extends Client {
    
    // Tipos de id
    const ID_ADJUNTO     = 'adjunto';
    const ID_DOCUMENTO   = 'documento';
    const ID_ENTRADA     = 'entrada';
    const ID_ESCRITO     = 'escrito';
    const ID_EXPEDIENTE  = 'expediente';
    const ID_PLANTILLA   = 'plantilla';
    
    /**
     * Se encuentra en el servidor etherpad en;
     * tramity:/opt/etherpad/etherpad-lite/APIKEY.txt
     * 
     * @var string|null
     */ 
    private $apikey = '255a27fbe84ca4f15720a75ed58c603f2f325146eda850741bec357b0942e546';
    private $apikey_dlb = '7114153c4b981f57380f3bdb65444daed5e15efca3ec54ffa48f66270f927b50';

    /**
     * @var string|null
     */
    private $url = null;

    private $id_usuario = null;
    private $nom_usuario = null;

    private $id_escrito = null;
    private $text = null;
    private $multiple = FALSE;
    
    
    public function setId ($tipo_id,$id) {
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
        }
        
        $this->id_escrito = $prefix.$id;
        
        return $this->id_escrito;
    }
    
    /**
     */
    public function __construct($url='',$id_usuario='') {
        if (empty($url)) {
            $url = $_SESSION['oConfig']->getServerEtherpad();
        }
        //$this->url = 'http://127.0.0.1:9001';
        $this->url = $url;
        
        if (empty($id_usuario)) {
            $id_usuario = ConfigGlobal::mi_id_usuario();
        }
        $nom_usuario = ConfigGlobal::mi_usuario(); 
        
        $this->setId_usuario($id_usuario);
        $this->setNom_usuario($nom_usuario);
    
        // depende si es en el portatil o en la dl.
        if (ConfigGlobal::SERVIDOR == 'tramity.local') {
            $apikey = $this->apikey;
        } else {
            $apikey = $this->apikey_dlb;
        }
        
        parent::__construct($apikey, $this->url);
    }
    
   /**
    * devuelve el escrito en html.
    * 
    * @return string html
    */ 
    private function cleanHtml() {
        $contenido = $this->getHHTML();
        
        $dom = new \DOMDocument;
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
        foreach ( $domNodeList as $domElement ) {
            // mirar si es: class="comment
            $class = $domElement->getAttribute('class');
            if (strstr($class, 'comment')) {
                $domNodeListChilds = $domElement->childNodes;
                foreach ( $domNodeListChilds as $domElementChild ) {
                    // borrar los child: <sup></sup>
                    $nodeName = $domElementChild->nodeName;
                    if ($nodeName == 'sup') {
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
        
        $xpath = new \DOMXPath($dom);
        /* Quitar los <td> con display:none:
         * <td class="regex-delete" name="payload" style="display:none;">{"payload":[["</td>
         * <td class="regex-delete" name="delimCell" id="" style="display:none;">","</td>
         * <td class="regex-delete" name="bracketAndcomma" style="display:none;">"]],"tblId":"1","tblClass":"data-tables", "tblProperties":{"borderWidth":"1","cellAttrs":[],"width":"100","rowAttrs":{},"colAttrs":[],"authors":{}}}</td>
         */
        // Selects tags to be processed.
        $tags_list = $xpath->query("//td");
        foreach($tags_list as $tag) {
            $class = $tag->getAttribute('name');
            if (strstr($class, 'payload') || strstr($class, 'delimCell') || strstr($class, 'bracketAndcomma')) {
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
        foreach($tags_list as $tag) {
            // Checks and deletes tags with empty content.
            if(  in_array($tag->tagName, ['p','a','strong','blockquote']) ){
                if( $tag->nodeValue == "" ){
                    $tag->parentNode->removeChild($tag);
                }
            }
        }
        
        //$xpath = new \DOMXPath($dom);
        // Quitar los atributos style
        $tags_list = $xpath->query("//table|//tr|//td");
        foreach($tags_list as $tag) {
            $tag->removeAttribute('style');
            $tag->removeAttribute('name');
            $tag->removeAttribute('class');
        }
        
        // Quitar los atributos label
        $tags_list = $xpath->query("//label");
        foreach($tags_list as $tag) {
            $tag->parentNode->removeChild($tag);
        }
        
        // lista de los tagg 'body'
        $bodies = $dom->getElementsByTagName('body');
        
        // cojo el primero de la lista: sólo debería haber uno.
        $body = $bodies->item(0);
        //$txt = $body->C14N(); //innerhtml convierte <br> a <br></br>. Se usa lo de abajo:
        $txt = $body->ownerDocument->saveHTML( $body );
        $txt2 = substr($txt, 6); // Quitar el tag <body> inicial
        $txt3 = substr($txt2, 0, -7); // Quitar el tag </body> final
        // eliminar dobles lineas: <br><br>
        //$txt4 = str_replace("<br><br>", "<br>", $txt3);
        $txt4 = str_replace("</p><br>", "</p>", $txt3);
        $txt5 = str_replace("</table><br>", "</table>", $txt4);
        
        //<br value="tblBreak">
        $txt6 = str_replace("<br value=\"tblBreak\">", "", $txt5);
        $txt7 = str_replace("</tbody></table><table><tbody>", "", $txt6);

        return $txt7;
    }
    
    /**
     * Quitar todos los estilos etc de las tablas
     * 
     */
    private function quitarAtributosTabla($html) {
        $dom = new \DOMDocument;
        /* la '@' sirve para evita los errores:  Warning: DOMDocument::loadHTML()
         *
         * loadHTML expects valid markup, i’m afraid most page’s arn’t.
         * You can alter the code to suppress markup errors:-
         *      $file = @$doc->loadHTML($remote);
         */
        @$dom->loadHTML($html);
        
        $xpath = new \DOMXPath($dom);
        // Quitar los atributos style
        $tags_list = $xpath->query("//table|//tr|//td");
        foreach($tags_list as $tag) {
            $tag->removeAttribute('style');
            $tag->removeAttribute('name');
            $tag->removeAttribute('class');
            /*
            $class = $tag->getAttribute('name');
            if (strstr($class, 'undefined')) {
                $tag->removeAttribute('class');
            }
            */
        }
        
        // Quitar los atributos label
        $tags_list = $xpath->query("//label");
        foreach($tags_list as $tag) {
            $tag->parentNode->removeChild($tag);
        }
        
        // save html
        //$txt = $dom->saveHTML();
        
        // lista de los tagg 'body'
        $bodies = $dom->getElementsByTagName('body');
        
        // cojo el primero de la lista: sólo debería haber uno.
        $body = $bodies->item(0);
        //$txt = $body->C14N(); //innerhtml convierte <br> a <br></br>. Se usa lo de abajo:
        $txt = $body->ownerDocument->saveHTML( $body );
        $txt2 = substr($txt, 6); // Quitar el tag <body> inicial
        $txt3 = substr($txt2, 0, -7); // Quitar el tag </body> final
        
        $txt4 = str_replace("</tbody></table><table><tbody>", "", $txt3);
        
        return $txt4;
    }
    
   /**
    * devuelve el escrito en html.
    * 
    * @return string html
    */ 
    public function generarHtml() {
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
    * devuelve el escrito en formato PDF.
    * 
    * @param array $a_header ['left', 'center', 'right']
    * @return \Mpdf\Mpdf
    */ 
    public function generarPDF($a_header=[],$fecha='') {
        $stylesheet = "<style>
                TABLE { border: 1px solid black; border-collapse: collapse; }
                TD { padding: 2mm; border: 1px solid black; vertical-align: middle;}
                TD.header { padding: 1mm; border: 0px; vertical-align: bottom;}
                 </style>
                ";
        
        
        $txt = $this->cleanHtml();
        $txt2 = str_replace("<tbody>", "", $txt);
        $html = str_replace("</tbody>", "", $txt2);
        
        //$html = $this->quitarAtributosTabla($html1);
        
        // convert to PDF
        require_once(ConfigGlobal::$dir_libs.'/vendor/autoload.php');
        
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
        
        /*
        $header = array (
                'L' => array (
                    'content' => $a_header['left'],
                    'font-size' => 10,
                    'font-style' => 'B',
                    'font-family' => 'serif',
                    'color'=>'#000000'
                ),
                'C' => array (
                    'content' => $a_header['center'],
                    'content' => '',
                    'font-size' => 10,
                    'font-style' => 'B',
                    'font-family' => 'serif',
                    'color'=>'#000000'
                ),
                'R' => array (
                    'content' => $a_header['right'],
                    'font-size' => 10,
                    'font-style' => 'B',
                    'font-family' => 'serif',
                    'color'=>'#000000'
                ),
                'line' => 1,
        );
        */
        
        $footer = '{PAGENO}/{nbpg}';
        
        
        if (!empty($fecha)) {
            $html .= '<div id="fecha" style="margin-top: 2em; margin-right:  0em; text-align: right; " >';
            $html .= $fecha;
            $html .= '</div>';
        }
        
        try {
            //$mpdf=new mPDF('','A4','','',10,10,10,10,6,3);
            $config = [ 'mode' => 'utf-8',
                        'format' => 'A4-P',
                        'margin_header' => 10,
                        'margin_top' => 40,
                
            ];
            $mpdf = new \Mpdf\Mpdf($config);
            //$mpdf->simpleTables = true;
            //$mpdf->packTableData = true;
            //$mpdf->keep_table_proportions = TRUE;
            //$mpdf->shrink_tables_to_fit=1;
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->list_indent_first_level = 0;	// 1 or 0 - whether to indent the first level of a list
            //$mpdf->WriteHTML('<h1>Hello world!</h1><p>Més què d\'air. Ñanyo.</p>');
            
            if (!empty($header_html)) {
                $mpdf->SetHTMLHeader($header_html);
            }
            //$mpdf->SetHeader($header, 'O');
            $mpdf->SetHTMLFooter($footer);
            
            $mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS);
            $mpdf->WriteHTML($html);
        
            // Other code
            return $mpdf;
        } catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch
            // Process the exception, log, print etc.
            echo $e->getMessage();
        }
    }
    
    public function getHHtml() {
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

    public function getAuthorId() {
        // Crear usuario dani (7)
        $authorID = '';
        $rta = $this->createAuthorIfNotExistsFor($this->id_usuario,$this->nom_usuario);
        if ($rta->getCode() == 0) {
            $data = $rta->getData();
            /* returns: * {code: 0, message:"ok", data: {authorID: "a.s8oes9dhwrvt0zif"}} */
            $authorID = $data['authorID'];
        } else {
            $this->mostrar_error($rta);
        }
        return $authorID; 
    }
    
   public function getGroupId() {
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
   
   public function getPadID() {
       if (empty($this->id_escrito)) {
           die (_("Debe indicar el id con SetId")); 
       }
       // obtener o crear el pad
       $PadID = $this->getId_pad();
       // Conceder permisos (crear sesión)
       $this->addPerm();
       
       return $PadID;
   }
   
    public function getId_pad() {
        $groupID = $this->getGroupId();
        $padId = $groupID."$".$this->id_escrito;

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
    
   //returns the text of a pad
   public function getTexto($padID) {
       $rta = $this->getText($padID);
        if ($rta->getCode() == 0) {
            $data = $rta->getData();
            /* returns: {code: 0, message:"ok", data: {text:"Welcome Text"}}
             * {code: 1, message:"padID does not exist", data: null}  
             */
            $texto = $data['text'];
            return $texto;
        } else {
            $this->mostrar_error($rta);
        }
   }
   
   
   
   
   // Crear o abrir Pad
   public function crearPad() {
        $groupID = $this->getGroupId();
        $padName = $this->id_escrito;
        $padId = $groupID."$".$this->id_escrito;
        $text = $this->text; // para el caso de clonar
        $text = empty($this->text)? null : $this->text;

        $rta = $this->createGroupPad($groupID, $padName, $text);
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
   
   
   public function addPerm() {
        $groupID = $this->getGroupId();
        $authorID = $this->getAuthorId();
        $validUntil = date("U", strtotime("+1 hours")); // (1h)
        return $this->crearSession($groupID,$authorID,$validUntil);
       
   }
   
   
   public function eliminarPad() {
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
   
   /* Crear session (link usuario-grupo)
    * creates a new session. validUntil is an unix timestamp in seconds
    */
   public function crearSession($groupID, $authorID, $validUntil) {
        // comprobar si ya está la session:
        /* Example returns:
         * 
         * {code: 0,"message":"ok","data":{"s.oxf2ras6lvhv2132":{"groupID":"g.s8oes9dhwrvt0zif","authorID":"a.akf8finncvomlqva","validUntil":2312905480}}}
         * {code: 1, message:"authorID does not exist", data: null}
         */ 
        $lista = $this->listSessionsOfAuthor($authorID);
        if ($lista->getCode() == 0) {
            $data = $lista->getData();
            foreach ($data as $sessionID => $aData) {
                $group = $aData['groupID'];
                $author = $aData['authorID'];
                $valid = $aData['validUntil'];
                if ($group == $groupID && $author == $authorID ) {
                    $ahora = date("U");
                    if ($valid > $ahora) {
                        // Además hay que asegurar que está también la cookie
                        if(isset($_COOKIE["sessionID"])){
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
       
        // Si no está borrón y cuenta neuva.
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
    
    /*
    Sessions can be created between a group and an author. This allows an author to access more than one group. 
    The sessionID will be set as a cookie to the client and is valid until a certain date. The session cookie 
    can also contain multiple comma-seperated sessionIDs, allowing a user to edit pads in different groups at 
    the same time. Only users with a valid session for this group, can access group pads. You can create a 
    session after you authenticated the user at your web application, to give them access to the pads. 
    You should save the sessionID of this session and delete it after the user logged out. 
    */
    
    public function addSessionCookie($sessionID) {
        $authorID = $this->getAuthorId();
        
        //$sessionID = (!isset($_COOKIE["sessionID"]))? "" : $_COOKIE["sessionID"];
        // sessiones abiertas:
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
           $lista_sesiones =  implode(',', $a_sesiones);
        } else {
            // Da igual, porque borro todo. En alguna instalación da el error:
            //  *Error: wrong parameters sessionID does not exist**
            //$this->mostrar_error($rta);
        }
        // añadir la session actual:
        $lista_sesiones .= empty($lista_sesiones)? $sessionID : ",$sessionID";
        //$lista_sesiones = "$a";
        //setcookie("sessionID", $lista_sesiones, time() + (86400 * 30), "/; SameSite=Lax"); // 86400 = 1 day
        // para php >= 7.3
        // 86400 = 1 day
        // 3600 = 1 hora
        if ($this->multiple === FALSE) { // espero a mandar las cookies al final.
            // Coger el nombre del dominio para que sirva para tramity.red.local y etherpad.red.local
            //const SERVIDOR = 'tramity.red.local';
            $regs = [];
            $host = ConfigGlobal::SERVIDOR;
            preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $host, $regs);
            $domain = $regs['domain'];
            
            setcookie("sessionID", $lista_sesiones, [
                                    //'expires' => time() + (86400 * 30),
                                    'expires' => time() + (3600),
                                    'path' => '/',
                                    'sameSite' => 'Strict',
                                    'Secure' => 'Secure',
                                    'domain' => $domain,
                                ]);
        }
    }
    
    private function deleteAllSessions($authorID) {
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
    
    private function borrarSesion($sessionID) {
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
    /*----------------------------------------------------------------------------------------*/
    
    /**
     * @return string $url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getId_usuario()
    {
        return $this->id_usuario;
    }

    /**
     * @return mixed
     */
    public function getNom_usuario()
    {
        return $this->nom_usuario;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param mixed $id_usuario
     */
    public function setId_usuario($id_usuario)
    {
        $this->id_usuario = $id_usuario;
    }

    /**
     * @param mixed $nom_usuario
     */
    public function setNom_usuario($nom_usuario)
    {
        $this->nom_usuario = $nom_usuario;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
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


}