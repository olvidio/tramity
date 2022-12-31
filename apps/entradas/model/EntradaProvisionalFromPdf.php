<?php

namespace entradas\model;

class EntradaProvisionalFromPdf
{

    private string $file_pdf;

    public function __construct($file_pdf)
    {
        $this->file_pdf = $file_pdf;
    }


    private function leer_pdf()
    {
// Parse PDF file and build necessary objects.
$parser = new Parser();
$pdf = $parser->parseFile('/path/to/document.pdf');

$text = $pdf->getText();
echo $text;
    }

    public function crear_entrada_provisional()
    {
       $a_pdf = $this->leer_pdf();

    }
}
