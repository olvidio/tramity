<?php

namespace envios\model;

use documentos\model\Documento;
use entradas\model\entity\EntradaAdjunto;
use entradas\model\entity\EntradaBypass;
use entradas\model\entity\EntradaCompartida;
use entradas\model\Entrada;
use escritos\model\entity\EscritoAdjunto;
use escritos\model\Escrito;
use etherpad\model\Etherpad;
use lugares\model\entity\GestorLugar;
use lugares\model\entity\Grupo;
use lugares\model\entity\Lugar;
use Mpdf\MpdfException;
use oasis_as4\model\As4;
use oasis_as4\model\As4CollaborationInfo;
use PHPMailer\PHPMailer\Exception;
use SplFileInfo;
use stdClass;
use usuarios\model\Categoria;
use usuarios\model\entity\Cargo;
use web\Protocolo;
use function core\is_true;


class Enviar
{
    private Escrito $oEscrito;
    /**
     * @var Entrada|EntradaCompartida
     */
    private $oEntrada;
    /**
     * @var EntradaBypass|Entrada
     */
    private $oEntradaBypass;
    private Etherpad $oEtherpad;
    private bool $bLoaded = FALSE;
    private bool $is_Bypass;
    private int $iid;
    private string $tipo;
    private string $sigla_destino = '';
    private string $f_salida;

    private string $asunto;
    private string $filename = '';
    private string $filename_ext;
    private string $contentFile;
    private array $a_adjuntos;
    private array $a_rta = [];

    private string $accion;
    /**
     * @var array|false|string|string[]|null
     */
    private string|array|null|false $filename_iso;

    public function __construct($id, $tipo)
    {
        $this->setId($id);
        $this->setTipo($tipo);

        if ($this->tipo === 'escrito') {
            $this->oEscrito = new Escrito($this->iid);
        }
        if ($this->tipo === 'entrada') {
            // Los centros no tienen bypass
            if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
                $this->oEntradaBypass = new Entrada($this->iid);
            } else {
                $this->oEntradaBypass = new EntradaBypass($this->iid);
            }
            $this->is_Bypass = $this->oEntradaBypass->getBypass();
        }
    }

    public function setId($id): void
    {
        $this->iid = $id;
        $this->bLoaded = FALSE;
    }

    public function setTipo($tipo): void
    {
        $this->tipo = $tipo;
        $this->bLoaded = FALSE;
    }

    /**
     * para descargar en local
     *
     * @param bool $is_compartida
     * @return array
     * @throws MpdfException
     */
    public function getPdf(bool $is_compartida = FALSE): array
    {
        $this->getDocumento($is_compartida);

        $a_header = [];
        if ($this->tipo === 'escrito') {
            $a_header = ['left' => $this->oEscrito->cabeceraIzquierda(),
                'center' => '',
                'right' => $this->oEscrito->cabeceraDerecha(),
            ];
        }

        if ($this->tipo === 'entrada') {
            // Puede que no sea bypass. Se usa para descargar la entrada en local.
            if (is_true($this->is_Bypass) && $_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
                $a_header = ['left' => $this->oEntradaBypass->cabeceraDistribucion_cr(),
                    'center' => '',
                    'right' => $this->oEntradaBypass->cabeceraDerecha(),
                ];
            } else {
                $a_header = ['left' => $this->oEntrada->cabeceraIzquierda(),
                    'center' => '',
                    'right' => $this->oEntrada->cabeceraDerecha(),
                ];
            }
        }
        // formato pdf:
        $this->filename_ext = $this->filename . '.pdf';
        $omPdf = $this->oEtherpad->generarPDF($a_header, $this->f_salida);
        $this->contentFile = $omPdf->Output($this->filename_ext, 'S');

        return ['content' => $this->contentFile,
            'name' => $this->filename,
            'ext' => $this->filename_ext,
        ];
    }

    private function getDocumento(bool $is_compartida = FALSE): void
    {
        if ($this->tipo === 'entrada') {
            if ($is_compartida) {
                $this->getDatosEntradaCompartida();
            } else {
                // Puede que no sea bypass. Se usa para descargar la entrada en local.
                if (is_true($this->is_Bypass)) {
                    $this->getDatosEntradaByPass();
                } else {
                    $this->getDatosEntrada();
                }
            }
        }
        if ($this->tipo === 'escrito') {
            $this->getDatosEscrito();
        }
    }

    private function getDatosEntradaCompartida(): void
    {
        $this->oEntrada = new EntradaCompartida($this->iid);
        $this->f_salida = $this->oEntrada->getF_documento()->getFromLocal('.');
        $this->asunto = $this->oEntrada->getAsunto_entrada();

        $json_prot_origen = $this->oEntrada->getJson_prot_origen();
        if (count(get_object_vars($json_prot_origen)) === 0) {
            exit (_("No hay más"));
        }

        $this->filename = $this->oEntrada->getNombreEscrito($this->sigla_destino);

        $this->oEtherpad = new Etherpad();
        $this->oEtherpad->setId(Etherpad::ID_COMPARTIDO, $this->iid);
    }

    private function getDatosEntradaByPass(): void
    {
        $this->f_salida = $this->oEntradaBypass->getF_documento()->getFromLocal('.');
        $this->asunto = empty($this->oEntradaBypass->getAsunto()) ? $this->oEntradaBypass->getAsunto_entrada() : $this->oEntradaBypass->getAsunto();

        $json_prot_origen = $this->oEntradaBypass->getJson_prot_origen();
        if (count(get_object_vars($json_prot_origen)) === 0) {
            exit (_("No hay más"));
        }

        $this->filename = $this->oEntradaBypass->getNombreEscrito($this->sigla_destino);

        $this->oEtherpad = new Etherpad();
        $this->oEtherpad->setId(Etherpad::ID_ENTRADA, $this->iid);

        // Attachments
        $this->a_adjuntos = [];
        $a_id_adjuntos = $this->oEntradaBypass->getArrayIdAdjuntos();
        foreach ($a_id_adjuntos as $item => $adjunto_filename) {
            $oEntradaAdjunto = new EntradaAdjunto($item);
            $escrito_txt = $oEntradaAdjunto->getAdjunto();
            $this->a_adjuntos[$adjunto_filename] = $escrito_txt;
        }
    }

    private function getDatosEntrada(): void
    {
        $this->oEntrada = new Entrada($this->iid);
        $this->f_salida = $this->oEntrada->getF_documento()->getFromLocal('.');
        $this->asunto = empty($this->oEntrada->getAsunto()) ? $this->oEntrada->getAsunto_entrada() : $this->oEntrada->getAsunto();

        $json_prot_origen = $this->oEntrada->getJson_prot_origen();
        if (count(get_object_vars($json_prot_origen)) === 0) {
            exit (_("No hay más"));
        }

        $this->filename = $this->oEntrada->getNombreEscrito($this->sigla_destino);

        $this->oEtherpad = new Etherpad();
        $this->oEtherpad->setId(Etherpad::ID_ENTRADA, $this->iid);

        // Attachments
        $this->a_adjuntos = [];
        $a_id_adjuntos = $this->oEntrada->getArrayIdAdjuntos();
        foreach ($a_id_adjuntos as $item => $adjunto_filename) {
            $oEntradaAdjunto = new EntradaAdjunto($item);
            $escrito_txt = $oEntradaAdjunto->getAdjunto();
            $this->a_adjuntos[$adjunto_filename] = $escrito_txt;
        }
    }

    /**
     * Obtiene los datos de:
     *    asunto, f_salida, adjuntos, Etherpad.
     */
    private function getDatosEscrito(): void
    {
        // para no tener que repetir cuando hay multiples destinos
        if ($this->bLoaded === FALSE) {
            $json_prot_local = $this->oEscrito->getJson_prot_local();
            // En el caso de los ctr, se envía directamente sin los pasos
            // de circular por secretaria, y al llegar aquí todavía no se ha generado el
            // número de protocolo.
            if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR && empty((array)$json_prot_local)) {
                $this->oEscrito->generarProtocolo();
                if ($this->oEscrito->DBCargar() === FALSE) {
                    $err_cargar = sprintf(_("OJO! no existe el escrito a enviar en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_cargar);
                }
            }
            // f_salida
            $this->f_salida = $this->oEscrito->getF_escrito()->getFromLocal('.');
            $this->asunto = $this->oEscrito->getAsunto();
            // Attachments
            $this->a_adjuntos = [];
            $a_id_adjuntos = $this->oEscrito->getArrayIdAdjuntos();
            foreach ($a_id_adjuntos as $item => $adjunto_filename) {
                $oEscritoAdjunto = new EscritoAdjunto($item);
                $tipo_doc = $oEscritoAdjunto->getTipo_doc();
                switch ($tipo_doc) {
                    case Documento::DOC_UPLOAD:
                        if ($oEscritoAdjunto->getAdjunto() === FALSE) {
                            $err_adjunto = sprintf(_("No se puede enviar el adjunto \"%s\""), $adjunto_filename);
                            exit ($err_adjunto);
                        }
                        $escrito_txt = $oEscritoAdjunto->getAdjunto();
                        $this->a_adjuntos[$adjunto_filename] = $escrito_txt;
                        break;
                    case Documento::DOC_ETHERPAD:
                        $id_adjunto = $oEscritoAdjunto->getId_item();
                        $oEtherpadAdj = new Etherpad();
                        $oEtherpadAdj->setId(Etherpad::ID_ADJUNTO, $id_adjunto);
                        $escrito_txt = $oEtherpadAdj->generarPDF();
                        $this->a_adjuntos[$adjunto_filename] = $escrito_txt;
                        break;
                    default:
                        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                        exit ($err_switch);
                }
            }

            // etherpad
            $this->oEtherpad = new Etherpad();
            $this->oEtherpad->setId(Etherpad::ID_ESCRITO, $this->iid);
            $this->bLoaded = TRUE;
        }
        $this->filename = $this->oEscrito->getNombreEscrito($this->sigla_destino);
    }

    public function enviar(): array
    {
        // Inicialmente se consiguen todos los destinos, y para cada uno
        // se mira el modo de envío y se envía.
        // Para el caso de enviar a grupos en RDP hay que hacer una excepción.
        // si existe el campo autorización, sirve para discriminar.
        if ($this->soyGrupoRDP()) {
            return $this->enviar_rdp();
        }

        $aDestinos = $this->getDestinatarios();
        $sigla = $_SESSION['oConfig']->getSigla();

        $num_enviados = 0;
        $a_lista_dst_as4 = [];
        $a_lista_auth_rdp = [];
        $flag_AS4 = FALSE;
        $flag_rdp = FALSE;
        $flag_rdp_en_cola = FALSE;
        $autorizacion_dl = '';
        foreach ($aDestinos as $id_lugar) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);

            $oLugar = new Lugar($id_lugar);
            $this->sigla_destino = $oLugar->getSigla();

            $modo_envio = $oLugar->getModo_envio();
            switch ($modo_envio) {
                case Lugar::MODO_RDP:
                    $autorizacion_dl = '0-' . $sigla . '|';
                    $autorizacion = $oLugar->getAutorizacion();
                    // si la acción es compartir, se usa el mismo escrito para un conjunto de ctr.
                    // aquí genero el array de autorizaciones:
                    if ($this->accion === As4CollaborationInfo::ACCION_COMPARTIR) {
                        $flag_rdp = TRUE;
                        if (!in_array($autorizacion, $a_lista_auth_rdp, true)) {
                            $a_lista_auth_rdp[] = $autorizacion;
                        }
                    } else {
                        $flag_rdp_en_cola = TRUE;
                        $a_lista_auth_rdp[] = $autorizacion;
                    }
                    break;
                case Lugar::MODO_PDF:
                    $email = $oLugar->getE_mail();
                    $err_mail = $this->enviarPdf($id_lugar, $email);
                    break;
                case Lugar::MODO_AS4;
                    $plataforma = $oLugar->getPlataforma();
                    // si la acción es compartir, se envía el mismo escrito a un conjunto de ctr.
                    // aquí genero el array de plataformas destino:
                    if ($this->accion === As4CollaborationInfo::ACCION_COMPARTIR
                        || $this->accion === As4CollaborationInfo::ACCION_REEMPLAZAR) {
                        $flag_AS4 = TRUE;
                        if (!in_array($plataforma, $a_lista_dst_as4, true)) {
                            $a_lista_dst_as4[] = $plataforma;
                        }
                    } else {
                        $err_mail = $this->enviarAS4($id_lugar, $plataforma, $this->accion);
                    }
                    break;
                default:
                    $err_mail = _("No hay modo de envío para este destino");
            }
            if (empty($err_mail)) {
                $num_enviados++;
            }
        }

        // si es compartir, enviar en bloque
        // por permisos
        if ($flag_rdp_en_cola) {
            $autorizacion_lst = $autorizacion_dl . implode('|', $a_lista_auth_rdp);
            $err_mail = $this->enviarRdp($autorizacion_lst,true);
        }
        if ($flag_rdp) {
            if ($this->accion === As4CollaborationInfo::ACCION_COMPARTIR) {
                $autorizacion_lst = $autorizacion_dl . implode('|', $a_lista_auth_rdp);
                $err_mail = $this->enviarRdp($autorizacion_lst,false);
            }
        }
        // por plataformas.
        if ($flag_AS4) {
            if ($this->accion === As4CollaborationInfo::ACCION_COMPARTIR
                || $this->accion === As4CollaborationInfo::ACCION_REEMPLAZAR) {
                foreach ($a_lista_dst_as4 as $plataforma) {
                    // Finalmente los destinos se añaden en el payload. No se tienen en cuenta a la hora de enviar.
                    // Al recoger, se mira si están en la plataforma y se les añade.
                    $err_mail = $this->enviarAS4Compartido($plataforma);
                }
            }
        }

        // by default
        $this->a_rta['success'] = TRUE;
        $this->a_rta['mensaje'] = $err_mail;
        $this->a_rta['marcar'] = TRUE;
        if (empty($aDestinos)) {
            $err_mail = _("No hay destinos para este escrito") . ':<br>';
            $err_mail .= empty($this->filename) ? '' : $this->filename;
            $this->a_rta['success'] = FALSE;
            $this->a_rta['mensaje'] = $err_mail;
            $this->a_rta['marcar'] = FALSE;
            return $this->a_rta;
        }
        if (!empty($err_mail)) {
            $err_mail = _("mail no válido para") . ':<br>' . $err_mail;
            $this->a_rta['success'] = FALSE;
            $this->a_rta['mensaje'] = $err_mail;
            $this->a_rta['marcar'] = FALSE;
            if ($num_enviados > 1) {
                $this->a_rta['marcar'] = TRUE;
            }
        }
        return $this->a_rta;
    }

    public function enviar_rdp(): array
    {
        $sigla = $_SESSION['oConfig']->getSigla();
        $num_enviados = 0;
        $a_lista_auth_rdp = [];
        $autorizacion_dl = '';

        if ($this->tipo === 'entrada') {
            $id_grupos = $this->oEntradaBypass->getId_grupos();
        }
        if ($this->tipo === 'escrito') {
            $id_grupos = $this->oEscrito->getId_grupos();
        }
        if (!empty($id_grupos)) {
            foreach ($id_grupos as $id_grupo) {
                $autorizacion_dl = '0-' . $sigla . '|';
                $oGrupo = new Grupo($id_grupo);
                $autorizacion = $oGrupo->getAutorizacion();
                // aquí genero el array de autorizaciones:
                if (!in_array($autorizacion, $a_lista_auth_rdp, true)) {
                    $a_lista_auth_rdp[] = $autorizacion;
                }
                $num_enviados++;
            }
        }

        $autorizacion_lst = $autorizacion_dl . implode('|', $a_lista_auth_rdp);
        $err_mail = $this->enviarRdp($autorizacion_lst, false);

        // by default
        $this->a_rta['success'] = TRUE;
        $this->a_rta['mensaje'] = $err_mail;
        $this->a_rta['marcar'] = TRUE;
        if (!empty($err_mail)) {
            $err_mail = _("mail no válido para") . ':<br>' . $err_mail;
            $this->a_rta['success'] = FALSE;
            $this->a_rta['mensaje'] = $err_mail;
            $this->a_rta['marcar'] = FALSE;
            if ($num_enviados > 1) {
                $this->a_rta['marcar'] = TRUE;
            }
        }
        return $this->a_rta;
    }

    private function soyGrupoRDP()
    {
        $id_grupos = '';
        $soyGrupoRDP = FALSE;
        if ($this->tipo === 'entrada') {
            $id_grupos = $this->oEntradaBypass->getId_grupos();
        }
        if ($this->tipo === 'escrito') {
            $id_grupos = $this->oEscrito->getId_grupos();
        }
        if (!empty($id_grupos)) {
            foreach ($id_grupos as $id_grupo) {
                $oGrupo = new Grupo($id_grupo);
                $auth = $oGrupo->getAutorizacion();
                if ($auth !== NULL) {
                    $soyGrupoRDP = TRUE;
                }
            }
        }
        return $soyGrupoRDP;
    }

    private function getDestinatarios()
    {
        if ($this->tipo === 'entrada') {
            $this->accion = As4CollaborationInfo::ACCION_COMPARTIR;
            $aDestinos = $this->oEntradaBypass->getDestinosByPass();
            return $aDestinos['miembros'];
        }
        if ($this->tipo === 'escrito') {
            $id_grupos = $this->oEscrito->getId_grupos();
            if (!empty($id_grupos)) {
                $this->accion = As4CollaborationInfo::ACCION_COMPARTIR;
            } else {
                $this->accion = As4CollaborationInfo::ACCION_NUEVO;
            }
            return $this->oEscrito->getDestinosIds();
        }
        return [];
    }

    private function enviarRdp($autorizacion_lst,bool $varios)
    {
        $DIR_CORREO = '/home/correodlp';
        $err_mail = '';
        $fecha_hora = date('ymd');

        // generar un nuevo content, con la cabecera al ctr concreto.
        $this->getDocumento();

        if ($this->tipo === 'escrito') {
            // nombre del archivo
            $filename = $this->oEscrito->getNombreEscrito($this->sigla_destino);
            $asunto = $this->oEscrito->getAsunto();
        }

        if ($this->tipo === 'entrada') {
            $filename = $this->oEntradaBypass->getNombreEscrito('');
            $asunto = $this->oEntradaBypass->getAsunto();
        }
        // si va a más de un centro (no grupos) cambio el nombre del fichero, y en vez del nombre del primer (o último) centro
        // pongo "varios"
        if ($varios) {
            $filename = preg_replace('/(.*)\(.*\)(.*)/', '$1(varios)$2', $filename);
        }

        $filename_utf8 = $fecha_hora . '-' . $filename . '-' . trim($asunto);
        $this->filename = $filename_utf8;
        $this->filename_iso = mb_convert_encoding($filename_utf8, 'ISO-8859-1', 'UTF-8');
        // escribir en el directorio para bonita
        $a_header = $this->getHeader();
        $omPdf = $this->oEtherpad->generarPDF($a_header, $this->f_salida);

        $filename_ext = $this->filename . '.pdf';
        $filename_iso_ext = $this->filename_iso . '.pdf';
        $full_filename_iso = $DIR_CORREO . '/' . $filename_iso_ext;
        $omPdf->Output($full_filename_iso, 'F');

        $oWin = new FicherosPSWin($DIR_CORREO);
        $oWin->inicializar();

        //anotar lineas en ps1 (power shell de windows)
        $oWin->permisos($filename_ext, $autorizacion_lst);
        $oWin->mover($filename_ext);

        // adjuntos:
        $a = 0;
        foreach ($this->a_adjuntos as $adjunto_filename => $escrito_txt) {
            $a++;
            $info = new SplFileInfo($adjunto_filename);
            $extension = $info->getExtension();

            $adjunto_filename_num = 'adj_'.$a.".$extension";
            $adjunto_filename_iso = mb_convert_encoding($adjunto_filename_num, 'ISO-8859-1', 'UTF-8');
            $filename_ext = $this->filename . '-' . $adjunto_filename_num;
            $filename_iso_ext = $this->filename_iso . '-' . $adjunto_filename_iso;
            $full_filename_iso = $DIR_CORREO . '/' . $filename_iso_ext;
            file_put_contents($full_filename_iso, $escrito_txt);
            //anotar lineas en ps1 (power shell de windows)
            $oWin->permisos($filename_ext, $autorizacion_lst);
            $oWin->mover($filename_ext);
        }
        $oWin->add_pause();

        return $err_mail;
    }

    private function enviarPdf($id_lugar, $email)
    {
        $err_mail = '';

        $message = $_SESSION['oConfig']->getBodyMail();
        $message = empty($message) ? _("Ver archivos adjuntos") : $message;

        $oMail = new TramityMail(TRUE); //passing 'true' enables exceptions
        // Activo codificación utf-8
        $oMail->CharSet = 'UTF-8';

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $oMail->addBCC($email);
            // generar el mail, Uno para cada destino (para poder poner bien la cabecera) en cco (bcc):
            try {
                // generar un nuevo content, con la cabecera al ctr concreto.
                $this->getDocumento();

                // formato pdf:
                // cabeceras fuera del if loaded, para cambiarlas para cada ctr del grupo
                $a_header = $this->getHeader($id_lugar);
                $this->filename_ext = $this->filename . '.pdf';
                $omPdf = $this->oEtherpad->generarPDF($a_header, $this->f_salida);
                $this->contentFile = $omPdf->Output($this->filename_ext, 'S');

                $subject = "$this->filename ($this->asunto)";
                // Attachments
                ////$oMail->addAttachment($File, $filename);    // Optional name
                $oMail->addStringAttachment($this->contentFile, $this->filename_ext);    // Optional name
                ////$oMail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
                ////$oMail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

                // adjuntos:
                foreach ($this->a_adjuntos as $adjunto_filename => $escrito_txt) {
                    $oMail->addStringAttachment($escrito_txt, $adjunto_filename);    // Optional name
                }

                // Content
                $oMail->isHTML(true);                                  // Set email format to HTML
                $oMail->Subject = $subject;
                $oMail->Body = $message;
                ////$oMail->AltBody = 'This is the body in plain text for non-HTML mail clients';

                $oMail->send();
                $this->a_rta['success'] = TRUE;
                $this->a_rta['mensaje'] = 'Message has been sent<br>';
                $this->a_rta['marcar'] = TRUE;
            } catch (Exception $e) {
                $err_mail .= empty($err_mail) ? '' : '<br>';
                $err_mail .= "Message could not be sent. Mailer Error: $oMail->ErrorInfo";
            }
        } else {
            $oLugar = new Lugar($id_lugar);

            $err_mail .= empty($err_mail) ? '' : '<br>';
            $err_mail .= $oLugar->getNombre() . "($email)";
        }
        return $err_mail;
    }

    private function getHeader($id_lugar = ''): array
    {
        $a_header = [];
        if ($this->tipo === 'entrada') {
            // Puede que no sea bypass. Se uasa para descargar la entrada en local.
            if (is_true($this->is_Bypass)) {
                $a_header = ['left' => $this->oEntradaBypass->cabeceraIzquierda(),
                    'center' => '',
                    'right' => $this->oEntradaBypass->cabeceraDerecha(),
                ];
            } else {
                $a_header = ['left' => $this->oEntrada->cabeceraIzquierda(),
                    'center' => '',
                    'right' => $this->oEntrada->cabeceraDerecha(),
                ];
            }
        }
        if ($this->tipo === 'escrito') {
            $a_header = ['left' => $this->oEscrito->cabeceraIzquierda($id_lugar),
                'center' => '',
                'right' => $this->oEscrito->cabeceraDerecha(),
            ];
        }

        return $a_header;
    }

    private function enviarAS4(int $id_lugar, string $plataforma, string $accion): string
    {
        $err_mail = '';
        $this->getDocumento();

        if ($this->tipo === 'escrito') {
            // Si la categoría es 'sin numerar', no hay protocolo local.
            // fabrico uno con sólo el lugar:
            if ($this->oEscrito->getCategoria() === Categoria::CAT_E12) {
                // Busco el id_lugar de la dl.
                $gesLugares = new GestorLugar();
                $id_siga_local = $gesLugares->getId_sigla_local();
                $json_prot_org = new stdClass;
                $json_prot_org->id_lugar = $id_siga_local;
                $json_prot_org->num = '';
                $json_prot_org->any = '';
                $json_prot_org->mas = '';
            } else {
                $json_prot_org = $this->oEscrito->getJson_prot_local();
            }
            // Miro si en json_prot_dst hay el id_lugar
            // y aporta más datos del protocolo
            $a_json_prot_dst = $this->oEscrito->getJson_prot_destino(FALSE);
        }
        if ($this->tipo === 'entrada') {
            $json_prot_org = $this->oEntradaBypass->getJson_prot_origen();
            $a_json_prot_dst = $this->oEntradaBypass->getJson_prot_destino(FALSE);
        }

        $json_prot_dst = new stdClass();
        foreach ($a_json_prot_dst as $json_prot_dst) {
            if (!property_exists($json_prot_dst, 'id_lugar')) {
                continue;
            }
            $id_dst = (int)$json_prot_dst->id_lugar;
            if ($id_dst === $id_lugar) {
                break;
            }
        }
        // Puede ser que el que el id_lugar no esté en json_prot_dst,
        // por que sea un grupo...
        if (empty((array)$json_prot_dst)) {
            $oProtDst = new Protocolo($id_lugar, '', '', '');
            $json_prot_dst = $oProtDst->getProt();
        }

        // generar el xml
        $oAS4 = new As4();
        $oAS4->setPlataforma_Destino($plataforma);
        $oAS4->setAccion($accion);
        $oAS4->setTipo_escrito($this->tipo);
        $oAS4->setJson_prot_org($json_prot_org);
        $oAS4->setJson_prot_dst($json_prot_dst);
        if ($this->tipo === 'escrito') {
            $oAS4->setEscrito($this->oEscrito);
        }
        if ($this->tipo === 'entrada') {
            $oAS4->setEscrito($this->oEntradaBypass);
        }

        // nombre del archivo
        $this->filename = $this->oEscrito->getNombreEscrito($this->sigla_destino);

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

    private function enviarAS4Compartido($plataforma): string
    {
        $err_mail = '';
        $this->getDocumento();

        if ($this->tipo === 'escrito') {
            // Si la categoría es 'sin numerar', no hay protocolo local.
            // fabrico uno con sólo el lugar:
            if ($this->oEscrito->getCategoria() === Categoria::CAT_E12) {
                // Busco el id_lugar de la dl.
                $gesLugares = new GestorLugar();
                $id_siga_local = $gesLugares->getId_sigla_local();
                $json_prot_org = new stdClass;
                $json_prot_org->id_lugar = $id_siga_local;
                $json_prot_org->num = '';
                $json_prot_org->any = '';
                $json_prot_org->mas = '';
            } else {
                $json_prot_org = $this->oEscrito->getJson_prot_local();
            }
        }

        if ($this->tipo === 'entrada') {
            $json_prot_org = $this->oEntradaBypass->getJson_prot_origen();
            // cambio el nombre del fichero. No hace falta añadir la sigla destino si es compartido.
            switch ($this->accion) {
                case As4CollaborationInfo::ACCION_COMPARTIR:
                case As4CollaborationInfo::ACCION_REEMPLAZAR:
                case As4CollaborationInfo::ACCION_ORDEN_ANULAR:
                    $parentesi = $this->accion;
                    break;
                default:
                    $parentesi = '';
            }
            $this->filename = $this->oEntradaBypass->getNombreEscrito($parentesi);
        }

        // Los destinos se añaden en el payload. No se tienen en cuenta a la hora de enviar.
        // Al recoger, se mira si están en la plataforma y se les añade.

        // generar el xml
        $oAS4 = new As4();
        $oAS4->setPlataforma_Destino($plataforma);
        $oAS4->setAccion($this->accion);
        $oAS4->setTipo_escrito($this->tipo);
        $oAS4->setJson_prot_org($json_prot_org);
        if ($this->tipo === 'escrito') {
            $oAS4->setEscrito($this->oEscrito);
        }
        if ($this->tipo === 'entrada') {
            $oAS4->setEscrito($this->oEntradaBypass);
        }

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

}