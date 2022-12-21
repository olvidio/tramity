<?php

namespace envios\model;

use documentos\domain\entity\Documento;
use entradas\domain\entity\Entrada;
use entradas\domain\entity\EntradaBypass;
use entradas\domain\entity\EntradaCompartida;
use entradas\domain\entity\EntradaRepository;
use entradas\domain\repositories\EntradaAdjuntoRepository;
use entradas\domain\repositories\EntradaBypassRepository;
use escritos\domain\entity\Escrito;
use escritos\domain\repositories\EscritoAdjuntoRepository;
use escritos\domain\repositories\EscritoRepository;
use etherpad\model\Etherpad;
use lugares\domain\entity\Lugar;
use lugares\domain\repositories\LugarRepository;
use Mpdf\MpdfException;
use oasis_as4\model\As4;
use oasis_as4\model\As4CollaborationInfo;
use PHPMailer\PHPMailer\Exception;
use stdClass;
use usuarios\domain\Categoria;
use usuarios\domain\entity\Cargo;
use web\Protocolo;
use function core\is_true;


class Enviar
{
    private Escrito $oEscrito;
    /**
     * @var Entrada|EntradaCompartida
     */
    private EntradaCompartida|Entrada $oEntrada;
    /**
     * @var Entrada|EntradaBypass
     */
    private EntradaBypass|Entrada $oEntradaBypass;
    private Etherpad $oEtherpad;
    private bool $bLoaded = FALSE;
    private bool $is_Bypass;
    private int $iid;
    private string $tipo;
    private string $sigla_destino = '';
    private string $f_salida;

    private string $asunto;
    private string $filename;
    private string $filename_ext;
    private string $contentFile;
    private array $a_adjuntos;
    private array $a_rta = [];

    private string $accion;

    public function __construct(int $id, string $tipo)
    {
        $this->setId($id);
        $this->setTipo($tipo);

        if ($this->tipo === 'escrito') {
            $escritoRepository = new EscritoRepository();
            $this->oEscrito = $escritoRepository->findById($this->iid);
        }
        if ($this->tipo === 'entrada') {
            // Los centros no tienen bypass
            if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
                $EntradaRepository = new EntradaRepository();
                $this->oEntradaBypass = $EntradaRepository->findById($this->iid);
            } else {
                $entradaBypassRepository = new EntradaBypassRepository();
                $this->oEntradaBypass = $entradaBypassRepository->findById($this->iid);
            }
            $this->is_Bypass = $this->oEntradaBypass->isBypass();
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
        $entradaAdjuntoRepository = new EntradaAdjuntoRepository();
        foreach ($a_id_adjuntos as $item => $adjunto_filename) {
            $oEntradaAdjunto = $entradaAdjuntoRepository->findById($item);
            $escrito_txt = $oEntradaAdjunto->getAdjunto();
            $this->a_adjuntos[$adjunto_filename] = $escrito_txt;
        }
    }

    private function getDatosEntrada(): void
    {
        $EntradaRepository = new EntradaRepository();
        $oEntrada = $EntradaRepository->findById($this->iid);
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
        $entradaAdjuntoRepository = new EntradaAdjuntoRepository();
        foreach ($a_id_adjuntos as $item => $adjunto_filename) {
            $oEntradaAdjunto = $entradaAdjuntoRepository->findById($item);
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
                if ($this->oEscrito === null) {
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
            $escritoAdjuntoRepository = new EscritoAdjuntoRepository();
            foreach ($a_id_adjuntos as $item => $adjunto_filename) {
                $oEscritoAdjunto = $escritoAdjuntoRepository->findById($item);
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
        $aDestinos = $this->getDestinatarios();

        $num_enviados = 0;
        $a_lista_dst_as4 = [];
        $LugarRepository = new LugarRepository();
        foreach ($aDestinos as $id_lugar) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);

            $oLugar = $LugarRepository->findById($id_lugar);
            $this->sigla_destino = $oLugar->getSigla();

            $modo_envio = $oLugar->getModo_envio();
            switch ($modo_envio) {
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

        // si es compartir, enviar en bloque por plataformas.
        if ($this->accion === As4CollaborationInfo::ACCION_COMPARTIR
            || $this->accion === As4CollaborationInfo::ACCION_REEMPLAZAR) {
            foreach ($a_lista_dst_as4 as $plataforma) {
                // Finalmente los destinos se añaden en el payload. No se tienen en cuenta a la hora de enviar.
                // Al recoger, se mira si están en la plataforma y se les añade.
                $err_mail = $this->enviarAS4Compartido($plataforma);
            }
        }


        if (empty($aDestinos)) {
            $err_mail = _("No hay destinos para este escrito") . ':<br>' . $this->filename;
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
    }

    private function enviarPdf($id_lugar, $email)
    {
        $err_mail = '';

        $message = $_SESSION['oConfig']->getBodyMail();
        $message = empty($message) ? _("Ver archivos adjuntos") : $message;

        $oMail = new TramityMail(TRUE); //passing 'true' enables exceptions
        // Activo condificacción utf-8
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
                $LugarRepository = new LugarRepository();
                $id_siga_local = $LugarRepository->getId_sigla_local();
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
                $LugarRepository = new LugarRepository();
                $id_siga_local = $LugarRepository->getId_sigla_local();
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