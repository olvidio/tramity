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


    public function convert()
    {
        $path_temp = '/tmp/';
        $filename_local = $path_temp . $this->base_name;
        file_put_contents($filename_local, $this->documento);
        $command = "libreoffice -env:UserInstallation=file:///tmp/test --headless --convert-to pdf --outdir $path_temp $filename_local  2>&1";

        exec($command, $output,  $retval);
        //echo "Returned with status $retval and output:\n";

        $filename_pdf = $path_temp . $this->file_name . '.pdf';
        $doc_converted = file_get_contents($filename_pdf);
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

    public function setDocIn(string $doc): void
    {
        $this->documento = $doc;
    }

}