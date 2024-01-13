<?php

namespace etherpad\model;

use convertir_odf\model\Odf;
use core\ConfigGlobal;

class Etherpad2ODF
{

    /**
     * devuelve el escrito en formato ODT.
     *
     * @param array $a_header ['left', 'center', 'right']
     * @return *.odt
     */
    public function generarODT($filename_ext, $txt, $a_header = [], $fecha = '')
    {
        $cabecera = '';
        if (!empty($a_header)) {
            $origen = '';
            if (!empty($a_header['right']) ) {
                $a_dcha = explode( '<br>', $a_header['right'] );
                $origen = $a_dcha[0];
                $ref = $a_dcha[1];
            }
            $cabecera = "<cabecera>";
            $cabecera .= $a_header['left'];
            $cabecera .= "<cabecera_end>";
            $cabecera .= $origen;
            if (!empty($ref)) {
                $cabecera .= "<ref>$ref</ref>";
            }
            $cabecera .= "</cabecera_end>";
            $cabecera .= "</cabecera>";
            $cabecera .= "<br>";
        }

        $txt2 = str_replace("<tbody>", "", $txt);
        $documento = $cabecera . str_replace("</tbody>", "", $txt2);

        if (!empty($fecha)) {
            $documento .= "<fecha>$fecha</fecha>";
        }

        $this->fixAmps($documento, 0);
        $doc_type = "text";

        $documento = html_entity_decode($documento, ENT_NOQUOTES, 'UTF-8');
        $documento = stripslashes($documento);
        $documento = '<meta content="text/html; charset=UTF-8" http-equiv="Content-Type"/>' . $documento;
        $documento_html = '<html><body>' . $documento . '</body></html>';
        //quitar los forms
        $documento_html = preg_replace('/<form.*>/', '', $documento_html);
        $documento_html = str_replace('/<\/form>/', '', $documento_html);

        $file_txt = "/tmp/$filename_ext.txt";
        $file_xml = "/tmp/$filename_ext.xml";

        if (!$handle = fopen($file_txt, 'wb+')) {
            echo "Cannot open file ($file_txt)";
            exit;
        }
        // Write $some content to our opened file.
        if (fwrite($handle, $documento_html) === FALSE) {
            echo "Cannot write to file ($file_txt)";
            exit;
        }
        fclose($handle);

        $xslt = "html2odftextTramity.xslt";
        $conv_style = "5";

        // para el bash. poner el nombre de fichero entre comillas simples, y escapar las posibles comillas del nombre.
        $file_txt_escaped = "'".str_replace("'","\'", $file_txt)."'";
        $file_xml_escaped = "'".str_replace("'","\'", $file_xml)."'";
        $cmd = "xsltproc --html " . ConfigGlobal::getDIR(). ODF::DIR_COMPONENTES . "xslt/$xslt $file_txt_escaped > $file_xml_escaped";
        $a_output = [];
        exec($cmd, $a_output, $return_var);
        if ($return_var !== 0) {
            echo "cmd: $cmd <br>error: $return_var<br>";
            print_r($a_output);
            exit();
        }
        $content_xml = file_get_contents($file_xml);

        $ODF = new Odf(); //create a new ods file
        $file_odt = "/tmp/$filename_ext";
        $ODF->saveOds($file_odt, $content_xml, $conv_style, $doc_type); //save the object to a ods file

        $ODF->newOds();

        return $file_odt;
        /*
        if (file_exists($file_odt)) {
            $file_size = (int)filesize($file_odt);
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");

            header('Content-type: application/vnd.oasis.opendocument.text');
            header("Content-Disposition: attachment; filename=\"$file_odt\"");
            header("Content-Length: $file_size");
            header('Content-Description: File Transfer');
            header("Content-Transfer-Encoding: binary ");
            //in case of more output buffers was opened.
            while (ob_get_level()) {
                ob_end_clean();
            }
            flush();
            readfile($file_odt);
            unlink($file_odt);
        }
        */

        unlink($file_txt);
        unlink($file_xml);

    }

    /**
     * Función para eliminar los "&" que no son html
     *
     * @param string $html
     * @param integer $offset
     */
    private function fixAmps(&$html, $offset)
    {
        $positionAmp = strpos($html, '&', $offset);
        $positionSemiColumn = strpos($html, ';', $positionAmp + 1);

        $string = substr($html, $positionAmp, $positionSemiColumn - $positionAmp + 1);

        if ($positionAmp !== false) { // If an '&' can be found.
            if ($positionSemiColumn === false) { // If no ';' can be found.
//            $html = substr_replace($html, '&amp;', $positionAmp, 1); // Replace straight away.
                $html = substr_replace($html, '', $positionAmp, 1); // Lo elimino
            } else if (preg_match('/&(#[0-9]+|[A-Z|a-z|0-9]+);/', $string) === 0) { // If a standard escape cannot be found.
                $html = substr_replace($html, '&amp;', $positionAmp, 1); // This mean we need to escapa the '&' sign.
                $this->fixAmps($html, $positionAmp + 5); // Recursive call from the new position.
            } else {
                $this->fixAmps($html, $positionAmp + 1); // Recursive call from the new position.
            }
        }
    }

}