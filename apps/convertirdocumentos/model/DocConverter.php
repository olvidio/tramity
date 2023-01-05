<?php

namespace convertirdocumentos\model;

final class DocConverter
{

    /**
     * @var string
     */
    private $documento = '';

    /**
     * @var string
     */
    private $file_name = '';
    private $file_extension = '';
    private $base_name = '';


    public function convert($borrarTemporales = TRUE)
    {
        $path_temp = '/tmp/';
        $filename_local = $path_temp . $this->base_name;
        // con los espacios hay problemas, no bastan las comillas
        $filename_local_sin_espacios = str_replace(' ', '_', $filename_local);
        file_put_contents($filename_local_sin_espacios, $this->documento);
        //$command = escapeshellcmd("libreoffice -env:UserInstallation=file:///tmp/test --headless --convert-to pdf --outdir $path_temp \"$filename_local\"  2>&1");
        $command = escapeshellcmd("libreoffice -env:UserInstallation=file:///tmp/test --headless --convert-to pdf --outdir $path_temp $filename_local_sin_espacios 2>&1");

        exec($command, $output,  $retval);

        $filename_pdf = $filename_local_sin_espacios . '.pdf';
        $doc_converted = file_get_contents($filename_pdf);

        // borrar los ficheros temporales
        unlink($filename_local_sin_espacios);
        if ($borrarTemporales) {
            unlink($filename_pdf);
        } else {
            $this->file_name = $filename_pdf;
        }
        return $doc_converted;
    }

    /**
     * @param string $base_name
     */
    public function setBaseName(string $base_name): void
    {
        $this->base_name = $base_name;
    }

    /**
     * @param string $file_extension
     */
    public function setFileExtension(string $file_extension): void
    {
        $this->file_extension = $file_extension;
    }

    public function setFileName(string $file_name): void
    {
        $this->file_name = $file_name;
    }

    public function getFileName(): string
    {
        return $this->file_name;
    }

    public function setDocIn(string $doc): void
    {
        $this->documento = $doc;
    }

}