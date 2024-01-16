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
    private string $file_extension_original = '';
    private string $nombreFicheroOriginalConExtension = '';
    private string $nombreFicheroOriginalSinExtension = '';
    private string $nombreFicheroNuevoConExtension = '';
    private string $nombreFicheroNuevoSinExtension = '';

    public function convertOdt2(string $file_odt, string $nuevo_tipo): string
    {
        $path_parts = pathinfo($file_odt);
        $extension_original = $path_parts['extension'];
        $nombreFicheroOriginalSinExtension = $path_parts['filename'];

        $path_temp = '/tmp/';
        // Hay que poner el LC_ALL para asegurar acentos etc.
        $file_escaped = escapeshellarg($file_odt);
        $file_escaped = str_replace(' ', '\ ', $file_escaped);
        //$command = escapeshellcmd("LC_ALL=es_ES.UTF-8 libreoffice -env:UserInstallation=file:///tmp/test --headless --convert-to $nuevo_tipo --outdir /tmp $file_escaped 2>&1");
        $command = "LC_ALL=es_ES.UTF-8 libreoffice -env:UserInstallation=file:///tmp/test --headless --convert-to $nuevo_tipo --outdir /tmp $file_escaped 2>&1";
        exec($command, $output,  $retval);

        return $path_temp . $nombreFicheroOriginalSinExtension . '.' . $nuevo_tipo;
    }

    public function convert($nuevo_tipo, $borrarTemporales = TRUE)
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
        $command = escapeshellcmd("LC_ALL=es_ES.UTF-8 libreoffice -env:UserInstallation=file:///tmp/test --headless --convert-to $nuevo_tipo --outdir $path_temp $filename_original_sin_espacios 2>&1");

        exec($command, $output,  $retval);

        if (empty($this->nombreFicheroNuevoSinExtension)) {
            $filename_nuevo_sin_extension = $filename_local_sin_espacios_sin_extension;
        } else {
            $filename_nuevo_sin_extension = $path_temp . $this->nombreFicheroNuevoSinExtension;
        }
        $filename_nuevo_sin_espacios_sin_extension = str_replace(' ', '_', $filename_nuevo_sin_extension);
        $filename_nuevo_con_extension = $filename_nuevo_sin_espacios_sin_extension . '.'. $nuevo_tipo;
        $doc_converted = file_get_contents($filename_nuevo_con_extension);

        //Look Out for BOM
        $bom = pack('H*','EFBBBF');
        $doc_converted = preg_replace("/^$bom/", '', $doc_converted);

        // borrar los ficheros temporales
        unlink($filename_original_sin_espacios);
        if ($borrarTemporales) {
            unlink($filename_nuevo_con_extension);
        } else {
            $this->nombreFicheroNuevoConExtension = $filename_nuevo_con_extension;
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

    public function getNombreFicheroNuevoConExtension(): string
    {
        return $this->nombreFicheroNuevoConExtension;
    }

    public function setDocIn(string $doc): void
    {
        $this->documento = $doc;
    }

}