<?php
namespace ethercalc\model;

use core\ConfigGlobal;

/**
 * INFO EN:
 * 
 * https://etherpad.org/doc/v1.7.0/
 * 
*/

class Ethercalc {
    
    // Tipos de id
    const ID_ENTRADA       = 'entrada';
    const ID_ESCRITO       = 'escrito';
    const ID_EXPEDIENTE    = 'expediente';
    

    /**
     * @var string|null
     */
    private $url = null;

    private $id_usuario = null;
    private $nom_usuario = null;

    private $id_escrito = null;
    
    
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
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }
        
        $this->id_escrito = $prefix.$id;
        
        return $this->id_escrito;
    }
    
    /**
     */
    public function __construct($url='',$id_usuario='') {
        if (empty($url)) {
            $url = $_SESSION['oConfig']->getServerEthercalc();
        }
        $this->url = $url;
        
        if (empty($id_usuario)) {
            $id_usuario = ConfigGlobal::mi_id_usuario();
        }
        $nom_usuario = ConfigGlobal::mi_usuario(); 
        
        $this->setId_usuario($id_usuario);
        $this->setNom_usuario($nom_usuario);
    
        
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
        
        try {
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
            return $data['html'];
        } else {
            $this->mostrar_error($rta);
        }
    }

   
   public function getPadID() {
       if (empty($this->id_escrito)) {
           die (_("Debe indicar el id con SetId")); 
       }
       // obtener o crear el pad
       $PadID = $this->getId_pad();
       
       return $PadID;
   }
   
    public function getId_pad() {
        return 'calc'.$this->id_escrito;
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
}