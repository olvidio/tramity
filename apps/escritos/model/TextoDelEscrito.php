<?php

namespace escritos\model;

use escritos\model\entity\EscritoDB;
use etherpad\model\Etherpad;
use synology\model\SynoText;

class TextoDelEscrito implements TextoDelEscritoInterface
{
    // tipos de archivo
    public const TIPO_ETHERPAD = 1;
    public const TIPO_ETHERCALC = 2;
    public const TIPO_UPLOAD = 3;
    public const TIPO_SYNOTEXT = 4;
    public const TIPO_SYNOCALC = 5;

    // Tipos de id
    public const ID_COMPARTIDO = 'compartido';
    public const ID_ADJUNTO = 'adjunto';
    public const ID_DOCUMENTO = 'documento';
    public const ID_ENTRADA = 'entrada';
    public const ID_ESCRITO = 'escrito';
    public const ID_EXPEDIENTE = 'expediente';
    public const ID_PLANTILLA = 'plantilla';

    private Etherpad|SynoText $documentoDeTexto;

    public function __construct(int $tipo_doc,string $tipo_id,int  $id_escrito)
    {
        switch ($tipo_doc) {
            case self::TIPO_ETHERPAD:
                $this->documentoDeTexto = new Etherpad();
                $this->documentoDeTexto->setId($tipo_id, $id_escrito);

                break;
            case self::TIPO_SYNOTEXT:
                $this->documentoDeTexto = new SynoText();
                $this->documentoDeTexto->setId($tipo_id, $id_escrito);
                break;
            default:
                $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                exit ($err_switch);
        }
    }

    public function setId($tipo_id, $id_escrito, $sigla = ''): string
    {
        return $this->documentoDeTexto->setId($tipo_id, $id_escrito, $sigla);
    }


    public function copyTo($newId_escrito): void
    {
        $this->documentoDeTexto->copyTo($newId_escrito);
    }

    public function eliminar(): void
    {
        $this->documentoDeTexto->eliminar();
    }

    public function setHtml($html): void
    {
        $this->documentoDeTexto->setHTML($html);
    }

    public function generarHtml(): string
    {
        return $this->documentoDeTexto->generarHtml();
    }

    public function getHtmlSinLimpiar(): string
    {
        return $this->documentoDeTexto->getHtmlSinLimpiar();
    }

    public function crearTexto(): void
    {
        $this->documentoDeTexto->crearTexto();
    }

    public function setTextContent($text_content): void
    {
        $this->documentoDeTexto->setTextContent($text_content);
    }

    public function generarMD(): string
    {
        return $this->documentoDeTexto->generarMD();
    }

    public function addHeaders(array $a_header = [], string $fecha = ''): void
    {
        $this->documentoDeTexto->addHeaders($a_header, $fecha);
    }

    public function getContentFormatODT(): string
    {
        return $this->documentoDeTexto->getContentFormatODT();
    }

    public function getContentFormatPDF(): string
    {
        return $this->documentoDeTexto->getContentFormatPDF();
    }

    public function getContentFormatDOCX(): string
    {
        return $this->documentoDeTexto->getContentFormatDOCX();
    }

    public function getJsonEditorUrl(): array
    {
        return $this->documentoDeTexto->getJsonEditorUrl();
    }

    public function getServerUrl(): string
    {
        return $this->documentoDeTexto->getServerUrl();
    }

    public function setMultiple($multiple): void
    {
        $this->documentoDeTexto->setMultiple($multiple);
    }

    public static function getArrayTipos()
    {
        return [
            self::TIPO_ETHERPAD => _("etherpad"),
            self::TIPO_ETHERCALC => _("etheclac"),
            self::TIPO_UPLOAD => _("incrustado"),
            self::TIPO_SYNOTEXT => _("synology text"),
            self::TIPO_SYNOCALC => _("synology calc"),
        ];
    }
}