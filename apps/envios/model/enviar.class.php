<?php
namespace envios\model;

use PHPMailer\PHPMailer\Exception;
use documentos\model\Documento;
use entradas\model\Entrada;
use entradas\model\entity\EntradaAdjunto;
use entradas\model\entity\GestorEntradaBypass;
use etherpad\model\Etherpad;
use expedientes\model\Escrito;
use expedientes\model\entity\EscritoAdjunto;
use lugares\model\entity\Grupo;
use lugares\model\entity\Lugar;
use oasis_as4\model\As4;
use usuarios\model\entity\Cargo;
use web\Protocolo;


class Enviar {
    /**
     *
     * @var object
     */
    private $oEscrito;
    /**
     *
     * @var object
     */
    private $oEtherpad;
    /**
     *
     * @var boolean
     */
    private $bLoaded = FALSE;
    /**
     *
     * @var integer
     */
    private $iid;
    /**
     *
     * @var string
     */
    private $tipo;
    /**
     *
     * @var string
     */
    private $destinos_txt;
    /**
     *
     * @var \DateTime
     *
     */
    private $f_salida;
    
    private $asunto;
    private $filename;
    private $filename_ext;
    private $contentFile;
    private $a_adjuntos;
    
    private $cabecera_izq;
    private $cabecera_dcha;
    
    private $a_rta=[];
    
    public function __construct($id,$tipo) {
        $this->setId($id);
        $this->setTipo($tipo);
    }
    
    public function setId($id) {
        $this->iid = $id;
        $this->bLoaded = FALSE;
    }
    
    public function setTipo($tipo) {
        $this->tipo = $tipo;
        $this->bLoaded = FALSE;
    }
    
    private function getDestinatarios(){
        if ($this->tipo == 'entrada') {
            return $this->getDestinosByPass();
        }
        if ($this->tipo == 'escrito') {
            return $this->getDestinosEscrito();
        }
    }
    
    private function getDocumento($id_lugar='') {
        if ($this->tipo == 'entrada') {
            $this->getDatosEntrada();
        }
        if ($this->tipo == 'escrito') {
            $this->getDatosEscrito($id_lugar);
        }
    }
    
    private function getDestinosByPass() {
        $id_entrada = $this->iid;
        // a ver si ya está
        $gesEntradasBypass = new GestorEntradaBypass();
        $cEntradasBypass = $gesEntradasBypass->getEntradasBypass(['id_entrada' => $id_entrada]);
        if (!empty($cEntradasBypass)) {
            // solo debería haber una:
            $oEntradaBypass = $cEntradasBypass[0];
            
            $a_grupos = $oEntradaBypass->getId_grupos();
            $this->f_salida = $oEntradaBypass->getF_salida()->getFromLocal('.');
            
            $aMiembros = [];
            if (!empty($a_grupos)) {
                $destinos_txt = $oEntradaBypass->getDescripcion();
                //(segun los grupos seleccionados)
                foreach ($a_grupos as $id_grupo) {
                    $oGrupo = new Grupo($id_grupo);
                    $a_miembros_g = $oGrupo->getMiembros();
                    $aMiembros = array_merge($aMiembros, $a_miembros_g);
                }
                $aMiembros = array_unique($aMiembros);
                $oEntradaBypass->setDestinos($aMiembros);
                if ($oEntradaBypass->DBGuardar() === FALSE ) {
                    $error_txt = $oEntradaBypass->getErrorTxt();
                    exit ($error_txt);
                }
            } else {
                //(segun individuales)
                $destinos_txt = '';
                $a_json_prot_dst = $oEntradaBypass->getJson_prot_destino();
                foreach ($a_json_prot_dst as $json_prot_dst) {
                    $aMiembros[] = $json_prot_dst->lugar;
                    $oLugar = new Lugar($json_prot_dst->lugar);
                    $destinos_txt .= empty($destinos_txt)? '' : ', ';
                    $destinos_txt .= $oLugar->getNombre();
                }
            }
        }
        $this->destinos_txt = $destinos_txt;
        return $aMiembros;
    }
    
    private function getDestinosEscrito() {
        $id_escrito = $this->iid;
        $oEscrito = new Escrito($id_escrito);
        
        return $oEscrito->getDestinosIds();
    }
    
    private function getDatosEntrada() {
        $oEntrada = new Entrada($this->iid);
        $this->f_salida = $oEntrada->getF_documento()->getFromLocal('.');
        $this->asunto = $oEntrada->getAsunto();
        
        $a_header = [ 'left' => $oEntrada->cabeceraIzquierda(),
            'center' => '',
            'right' => $oEntrada->cabeceraDerecha(),
        ];

        $json_prot_origen = $oEntrada->getJson_prot_origen();
        if (count(get_object_vars($json_prot_origen)) == 0) {
            exit (_("No hay más"));
        }
        $oProtOrigen = new Protocolo();
        $oProtOrigen->setLugar($json_prot_origen->lugar);
        $oProtOrigen->setProt_num($json_prot_origen->num);
        $oProtOrigen->setProt_any($json_prot_origen->any);
        $oProtOrigen->setMas($json_prot_origen->mas);
        $this->filename = $this->renombrar($oProtOrigen->ver_txt());
        
        $oEtherpad = new Etherpad();
        $oEtherpad->setId (Etherpad::ID_ENTRADA,$this->iid);
        
        // formato pdf:
        $this->filename_ext = $this->filename.'.pdf';
        $omPdf = $oEtherpad->generarPDF($a_header,$this->f_salida);
        
        $this->contentFile = $omPdf->Output($this->filename_ext,'S');
        
        // Attachments
        $a_adjuntos = [];
        $a_id_adjuntos = $oEntrada->getArrayIdAdjuntos();
        foreach ($a_id_adjuntos as $item => $adjunto_filename) {
            $oEntradaAdjunto = new EntradaAdjunto($item);
            $escrito_txt = $oEntradaAdjunto->getAdjunto();
            $a_adjuntos[$adjunto_filename] = $escrito_txt;
        }
        $this->a_adjuntos = $a_adjuntos;
    }
    
    /**
     * genera el pdf del escrito.
     * Para las cabeceras hay que añadir las referencias
     * 
     * @param integer $id_lugar
     */
    private function getDatosEscrito($id_lugar) {
        // para no tener que repetir todo cuando hay multiples destinos
        if ($this->bLoaded === FALSE) {
            $this->oEscrito = new Escrito($this->iid);
            $json_prot_local = $this->oEscrito->getJson_prot_local();
			// En el caso de los ctr, se evnia directamente sin los pasos 
			// de cirular por secretaria, y al llegar aqui todavía no se ha generado el 
			// número de protocolo.
            if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR && empty((array)$json_prot_local)) {
				$this->oEscrito->generarProtocolo();
				$this->oEscrito->DBCarregar();
			}
            // f_salida
            $this->f_salida = $this->oEscrito->getF_escrito()->getFromLocal('.');
            $this->asunto = $this->oEscrito->getAsunto();
            // Attachments
            $a_adjuntos = [];
            $a_id_adjuntos = $this->oEscrito->getArrayIdAdjuntos();
            foreach ($a_id_adjuntos as $item => $adjunto_filename) {
                $oEscritoAdjunto = new EscritoAdjunto($item);
                $tipo_doc = $oEscritoAdjunto->getTipo_doc();
                switch ($tipo_doc) {
                    case Documento::DOC_UPLOAD:
                    	if ($escrito_txt = $oEscritoAdjunto->getAdjunto() === FALSE) {
                        	$err_adjunto = sprintf(_("No se puede enviar el adjunto \"%s\""), $adjunto_filename);
                        	exit ($err_adjunto);
                    	}
                        $a_adjuntos[$adjunto_filename] = $escrito_txt;
                        break;
                    case Documento::DOC_ETHERPAD:
                        $id_adjunto = $oEscritoAdjunto->getId_item();
                        $oEtherpadAdj = new Etherpad();
                        $oEtherpadAdj->setId (Etherpad::ID_ADJUNTO,$id_adjunto);
                        $escrito_txt = $oEtherpadAdj->generarPDF();
                        $a_adjuntos[$adjunto_filename] = $escrito_txt;
                        break;
                    default:
                        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                        exit ($err_switch);
                }
            }
            $this->a_adjuntos = $a_adjuntos;
            // nombre del archivo
            $this->filename = $this->oEscrito->getNombreEscrito();
            // etherpad
            $oEtherpad = new Etherpad();
            $oEtherpad->setId (Etherpad::ID_ESCRITO,$this->iid);
            $this->oEtherpad = $oEtherpad;
            $this->bLoaded = TRUE;
        }
        // cabeceras fuera del if loaded, para cambiarlas para cada ctr del grupo
        $this->cabecera_izq = $this->oEscrito->cabeceraIzquierda($id_lugar);
        $this->cabecera_dcha = $this->oEscrito->cabeceraDerecha();
        
        $a_header = [ 'left' => $this->cabecera_izq,
            'center' => '',
            'right' => $this->cabecera_dcha,
        ];
        
        // formato pdf:
        $this->filename_ext = $this->filename.'.pdf';
        $omPdf = $this->oEtherpad->generarPDF($a_header,$this->f_salida);
        
        $this->contentFile = $omPdf->Output($this->filename_ext,'S');
    }
    
    /**
     * para descargar en local
     *
     * @return array|string
     */
    public function getPdf() {
        $this->getDocumento();
        
        $filename = $this->filename;
        $filename_ext = $this->filename_ext;
        $contentFile = $this->contentFile;
        
        return ['content' => $contentFile,
            'name' => $filename,
            'ext' => $filename_ext,
        ];
    }
    
    public function enviar() {
    
        $aDestinos = $this->getDestinatarios();
        
        $num_enviados = 0;
        foreach ($aDestinos as $id_lugar) {
            ini_set( 'display_errors', 1 );
            error_reporting( E_ALL );
            
            $oLugar = new Lugar();
            $oLugar->setId_lugar($id_lugar);
            $oLugar->DBCarregar(); // obligar a recargar después de cambiar el id.

            $modo_envio = $oLugar->getModo_envio();
            switch ($modo_envio) {
                case Lugar::MODO_PDF:
                    $email = $oLugar->getE_mail();
                    $err_mail = $this->enviarPdf($id_lugar,$email);
                    break;
                case Lugar::MODO_AS4;
                	$plataforma = $oLugar->getPlataforma();
                    $err_mail = $this->enviarAS4($id_lugar,$plataforma);
                    break;
                default:
                    $err_mail =  _("No hay destinos metodo para este destino");
            }
            
        }
        
        if (empty($aDestinos)) {
            $err_mail = _("No hay destinos para este escrito").':<br>'.$this->filename;
            $this->a_rta['success'] = FALSE;
            $this->a_rta['mensaje'] = $err_mail;
            $this->a_rta['marcar'] = FALSE;
            return $this->a_rta;
        }
        if (!empty($err_mail)) {
            $err_mail = _("mail no válido para").':<br>'.$err_mail;
            $this->a_rta['success'] = FALSE;
            $this->a_rta['mensaje'] = $err_mail;
            $this->a_rta['marcar'] = FALSE;
            if ($num_enviados > 1) {
                $this->a_rta['marcar'] = TRUE;
            }
        }
        return $this->a_rta;
    }
    
    private function enviarAS4($id_lugar,$plataforma) {
        $err_mail = '';
        $this->getDocumento($id_lugar);
                
        $json_prot_org = $this->oEscrito->getJson_prot_local();
        // Miro si en json_prot_dst hay el id_lugar
        // y aporta más datos del protocolo
        $a_json_prot_dst = $this->oEscrito->getJson_prot_destino(FALSE);
        $json_prot_dst = new \stdClass();
        foreach ($a_json_prot_dst as $json_prot_dst) {
        	$id_dst = $json_prot_dst->lugar;
        	if ($id_dst == $id_lugar) {
        		break;
        	}
        }
        // Puede ser que el que el id_lugar no esté en json_prot_dst,
        // por que sea un grupo...
        if (empty((array) $json_prot_dst)) {
        	$oProtDst = new Protocolo($id_lugar, '', '', '');
        	$json_prot_dst = $oProtDst->getProt();
        }
	    
	    // parecido al email. debe estar en la definicion del ctr
        $accion = 'nuevo';
        // generar el xml
        $oAS4 = new As4();
        $oAS4->setPlataforma_Destino($plataforma);
        $oAS4->setAccion($accion);
        $oAS4->setJson_prot_org($json_prot_org);
        $oAS4->setJson_prot_dst($json_prot_dst);
        $oAS4->setEscrito($this->oEscrito);
        
        $err_mail .= $oAS4->writeOnDock($this->filename);
        
        if (empty($err_mail)) {
			$this->a_rta['success'] = TRUE;
			$this->a_rta['mensaje'] = 'AS4 Message has been sent<br>';
			$this->a_rta['marcar'] = TRUE;
        } else {
			$this->a_rta['success'] = FALSE;
			$this->a_rta['mensaje'] = 'ERROR AS4 Message has not been sent<br>';
			$this->a_rta['marcar'] = FALSE;
        }
        
        return $err_mail;
    }

    private function enviarPdf($id_lugar, $email) {
        $err_mail = '';

        $message = $_SESSION['oConfig']->getBodyMail();
        $message = empty($message)? _("Ver archivos adjuntos") : $message; 
        
        $oMail = new TramityMail(TRUE); //passing 'true' enables exceptions
        // Activo condificacción utf-8
        $oMail->CharSet = 'UTF-8';
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $oMail->addBCC($email);
            // generar el mail, Uno para cada destino (para poder poner bien la cabecera) en cco (bcc):
            try {
                // generar un nuevo content, con la cabecera al ctr concreto.
                $this->getDocumento($id_lugar);
                $filename = $this->filename;
                $filename_ext = $this->filename_ext;
                $a_adjuntos = $this->a_adjuntos;
                $contentFile = $this->contentFile;
                $subject = "$filename ($this->asunto)";
                // Attachments
                ////$oMail->addAttachment($File, $filename);    // Optional name
                $oMail->addStringAttachment($contentFile, $filename_ext);    // Optional name
                ////$oMail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
                ////$oMail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
                
                // adjuntos:
                foreach ($a_adjuntos as $adjunto_filename => $escrito_txt) {
                    $oMail->addStringAttachment($escrito_txt, $adjunto_filename);    // Optional name
                }
                
                // Content
                $oMail->isHTML(true);                                  // Set email format to HTML
                $oMail->Subject = $subject;
                $oMail->Body    = $message;
                ////$oMail->AltBody = 'This is the body in plain text for non-HTML mail clients';
                
                $oMail->send();
                $this->a_rta['success'] = TRUE;
                $this->a_rta['mensaje'] = 'Message has been sent<br>';
                $this->a_rta['marcar'] = TRUE;
            } catch (Exception $e) {
                $err_mail .= empty($err_mail)? '' : '<br>';
                $err_mail .= "Message could not be sent. Mailer Error: {$oMail->ErrorInfo}";
            }
        } else {
            $oLugar = new Lugar($id_lugar);
            
            $err_mail .= empty($err_mail)? '' : '<br>';
            $err_mail .= $oLugar->getNombre() ."($email)";
        }
        return $err_mail;
    }
    
    private function renombrar($string) {
        //cambiar '/' por '_':
        return str_replace('/', '_', $string);
    }
}