<?php

namespace escritos\model;

interface TextoDelEscritoInterface
{
    public function getJsonEditorUrl(): array;

    public function getServerUrl(): string;

    /*
     * crea el pad (en etherpad), o el documento en synology
     * si existe $text_content, se añade el texto.
     */
    public function crearTexto(): void;

    public function eliminar();

    public function copyTo($newId_escrito);

    public function setId($tipo_id, $id_escrito, $sigla = ''): string;

    public function setTextContent($text_content): void;

    public function setHtml($html);

    public function getHtmlSinLimpiar(): string;

    public function generarHtml(): string;

    public function generarMD(): string;

    public function addHeaders(array $a_header = [], string $fecha = ''): void;

    /**
     * devuelve  el texto en formato ODT
     */
    public function getContentFormatODT(): string;

    /**
     * devuelve  el texto en formato PDF
     */
    public function getContentFormatPDF(): string;

    public function getContentFormatDOCX(): string;


    public function setMultiple($multiple): void;
}