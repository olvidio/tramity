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
    const API_VERSION = '1.2.13';
    
    // Tipos de id
    const ID_ENTRADA       = 'entrada';
    const ID_ESCRITO       = 'escrito';
    const ID_EXPEDIENTE    = 'expediente';
    
    /**
     * Se encuentra en el servidor etherpad en;
     * tramity:/opt/etherpad/etherpad-lite/APIKEY.txt
     * 
     * @var string|null
     */ 
    private $apikey = '255a27fbe84ca4f15720a75ed58c603f2f325146eda850741bec357b0942e546';
    private $apikey_dlb = '8a7f816d90ddc5500a506b74244e26bbf24c7624a1701f95ff97f8917a6d043e';

    /**
     * @var string|null
     */
    private $url = null;

    private $id_usuario = null;
    private $nom_usuario = null;

    private $id_escrito = null;
    private $text = null;
    
    
    public function setId ($tipo_id,$id) {
        switch ($tipo_id) {
            case self::ID_ENTRADA:
                $prefix = 'ent';
                break;
            case self::ID_ESCRITO:
                $prefix = 'esc';
                break;
            case self::ID_EXPEDIENTE:
                $prefix = 'exp';
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
    * @param array $a_header ['left', 'center', 'right']
    * @return \Mpdf\Mpdf
    */ 
    public function generarHtml($a_header=[],$fecha='') {
        $html = '';
    
        $contenido = $this->getHHTML();
        
        
        $dom = new \DOMDocument;
        $dom->loadHTML($contenido);
        // lista de los tagg 'body'
        $bodies = $dom->getElementsByTagName('body');
        // cojo el primero de la lista: sólo debería haber uno.
        $body = $bodies->item(0);
        $txt = $body->C14N(); //innerhtml
        $txt2 = substr($txt, 6); // Quitar el tag <body> inicial
        $txt3 = substr($txt2, 0, -7); // Quitar el tag </body> final
        
        $html .= '<div id="escrito" >';
        $html .= $txt3;
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
        $html = '';
        
        // convert to PDF
        require_once(ConfigGlobal::$dir_libs.'/vendor/autoload.php');
        
        
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
        
        $footer = '{PAGENO}/{nbpg}';
        
        $contenido = $this->getHHTML();
        
        
        $dom = new \DOMDocument;
        $dom->loadHTML($contenido);
        // lista de los tagg 'body'
        $bodies = $dom->getElementsByTagName('body');
        // cojo el primero de la lista: sólo debería haber uno.
        $body = $bodies->item(0);
        $txt = $body->C14N(); //innerhtml
        $txt2 = substr($txt, 6); // Quitar el tag <body> inicial
        $txt3 = substr($txt2, 0, -7); // Quitar el tag </body> final
        
        $html .= '<div id="escrito" >';
        $html .= $txt3;
        $html .= '</div>';
        
        if (!empty($fecha)) {
            $html .= '<div id="fecha" style="margin-top: 2em; margin-right:  5em; text-align: right; " >';
            $html .= $fecha;
            $html .= '</div>';
        }
        //echo $html;
        //die();
        
        try {
            //$mpdf=new mPDF('','A4','','',10,10,10,10,6,3);
            $config = [ 'mode' => 'utf-8',
                        'format' => 'A4-P',
                        'margin_header' => 10,
                        'margin_top' => 40,
                
            ];
            $mpdf = new \Mpdf\Mpdf($config);
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->list_indent_first_level = 0;	// 1 or 0 - whether to indent the first level of a list
            //$mpdf->WriteHTML('<h1>Hello world!</h1><p>Més què d\'air. Ñanyo.</p>');
            //$mpdf->SetHTMLHeader($html_header);
            $mpdf->SetHeader($header, 'O');
            $mpdf->SetHTMLFooter($footer);
            $mpdf->WriteHTML($html);
        
            // Other code
            return $mpdf;
        } catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception name used for catch
            // Process the exception, log, print etc.
            echo $e->getMessage();
        }
    }
    
    /*
     Response Format
     #
     
     Responses are valid JSON in the following format:
     
     {
     "code": number,
     "message": string,
     "data": obj
     }
     
     code a return code
     0 everything ok
     1 wrong parameters
     2 internal error
     3 no such function
     4 no or wrong API Key
     message a status message. Its ok if everything is fine, else it contains an error message
     data the payload
     */
    
    private function mostrar_error($rta) {
        $a_codes = [
            0 => 'everything ok',
            1 => 'wrong parameters',
            2 => 'internal error',
            3 => 'no such function',
            4 => 'no or wrong API Key',
        ];
        $code = $rta->getCode();
        $message = $rta->getMessage();
        
        $html = "*Error: ". $a_codes[$code];
        $html .= "<br>";
        $html .= $message;
        $html .= "**<br>";
        
        echo $html;
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
   private function crearPad() {
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
       // ara per ara, primer esborrar:
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
           foreach ($data as $session => $a_info) {
               // evitar duplicar sesión
               if ($session != $sessionID) {
                   $a_sesiones[] = $session;
               }
           }
           $lista_sesiones =  implode(',', $a_sesiones);
        } else {
            $this->mostrar_error($rta);
        }
        // añadir la session actual:
        $lista_sesiones .= empty($lista_sesiones)? $sessionID : ",$sessionID";
        //$lista_sesiones = "$a";
        //setcookie("sessionID", $lista_sesiones, time() + (86400 * 30), "/; SameSite=Lax"); // 86400 = 1 day
        // para php >= 7.3
        // 86400 = 1 day
        setcookie("sessionID", $lista_sesiones, [
                                    'expires' => time() + (86400 * 30),
                                    'path' => '/',
                                    'sameSite' => 'Lax',
                                ]);
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
               foreach ($data as $session => $a_info) {
                   $this->borrarSesion($session);
               }
           }
        } else {
            $this->mostrar_error($rta);
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

}