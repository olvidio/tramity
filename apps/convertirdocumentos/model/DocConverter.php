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
    private $file_extension_original = '';
    private $nombreFicheroOriginalConExtension = '';
    private $nombreFicheroOriginalSinExtension = '';
    private $nombreFicheroNuevoSinExtension = '';


    public function convert($borrarTemporales = TRUE)
    {
        $path_parts = pathinfo($this->nombreFicheroOriginalConExtension);
        $this->file_extension_original = $path_parts['extension'];
        $this->nombreFicheroOriginalSinExtension = $path_parts['filename'];

        $path_temp = '/tmp/';
        $filename_local_sin_extension = $path_temp . $this->nombreFicheroOriginalSinExtension;
        // con los espacios hay problemas, no bastan las comillas
        $filename_local_sin_espacios_sin_extension = str_replace(' ', '_', $filename_local_sin_extension);
        $filename_original_sin_espacios = $filename_local_sin_espacios_sin_extension . '.' .$this->file_extension_original;

        file_put_contents($filename_original_sin_espacios, $this->documento);
        $command = escapeshellcmd("libreoffice -env:UserInstallation=file:///tmp/test --headless --convert-to pdf --outdir $path_temp $filename_original_sin_espacios 2>&1");

        exec($command, $output,  $retval);

        if (empty($this->nombreFicheroNuevoSinExtension)) {
            $filename_nuevo_sin_extension = $path_temp . $this->nombreFicheroOriginalSinExtension;
        } else {
            $filename_nuevo_sin_extension = $path_temp . $this->nombreFicheroNuevoSinExtension;
        }
        $filename_nuevo_sin_espacios_sin_extension = str_replace(' ', '_', $filename_nuevo_sin_extension);
        $filename_pdf = $filename_nuevo_sin_espacios_sin_extension . '.pdf';
        $doc_converted = file_get_contents($filename_pdf);

        // borrar los ficheros temporales
        unlink($filename_local_sin_espacios_sin_extension);
        if ($borrarTemporales) {
            unlink($filename_pdf);
        } else {
            $this->nombreFicheroNuevoSinExtension = $filename_pdf;
        }
        return $doc_converted;
    }

    /**
     * @param string $nombreFicheroOriginalConExtension
     */
    public function setNombreFicheroOriginalConExtension(string $nombreFicheroOriginalConExtension): void
    {
        $this->nombreFicheroOriginalConExtension = $nombreFicheroOriginalConExtension;
    }

    /**
     * @param string $file_extension_original
     */
    public function setFileExtensionOriginal(string $file_extension_original): void
    {
        $this->file_extension_original = $file_extension_original;
    }

    public function setNombreFicheroNuevoSinExtension(string $nombreFicheroNuevoSinExtension): void
    {
        $this->nombreFicheroNuevoSinExtension = $nombreFicheroNuevoSinExtension;
    }

    public function getNombreFicheroNuevoSinExtension(): string
    {
        return $this->nombreFicheroNuevoSinExtension;
    }

    public function setDocIn(string $doc): void
    {
        $this->documento = $doc;
    }

}