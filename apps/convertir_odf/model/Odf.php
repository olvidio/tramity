<?php

namespace convertir_odf\model;

/*

odf-php a library to read and write ods files from php.

This library has been forked from eyeOS project and licended under the LGPL3
terms available at: http://www.gnu.org/licenses/lgpl-3.0.txt (relicenced
with permission of the copyright holders)

Copyright: Juan Lao Tebar (juanlao@eyeos.org) and Jose Carlos Norte (jose@eyeos.org) - 2008 

https://sourceforge.net/projects/ods-php/

*/

use core\ServerConf;
use ZipArchive;

class Odf
{
    var $fonts;
    var $styles;
    var $sheets;
    var $lastElement;
    var $fods;
    var $currentSheet;
    var $currentRow;
    var $currentCell;
    var $lastRowAtt;
    var $repeat;

    public const DIR_COMPONENTES = "/apps/convertir_odf/model/ODF/";

    private function getDirComponentes(): string
    {
        return ServerConf::getDir() . self::DIR_COMPONENTES;
    }

    function __construct()
    {
        $this->styles = array();
        $this->fonts = array();
        $this->sheets = array();
        $this->currentRow = 0;
        $this->currentSheet = 0;
        $this->currentCell = 0;
        $this->repeat = 0;
    }

    public function parse($data): void
    {
        $xml_parser = xml_parser_create();
        xml_set_object($xml_parser, $this);
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler($xml_parser, "characterData");
        xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, false);

        xml_parse($xml_parser, $data, strlen($data));

        xml_parser_free($xml_parser);
    }


    private function startElement($parser, $tagName, $attrs)
    {
        $cTagName = strtolower($tagName);
        if ($cTagName === 'style:font-face') {
            //$this->fonts[$attrs['STYLE:NAME']] = $attrs;
        } elseif ($cTagName === 'style:style') {
            //$this->lastElement = $attrs['STYLE:NAME'];
            $this->styles[$this->lastElement]['attrs'] = $attrs;
        } elseif ($cTagName === 'style:table-column-properties' || $cTagName === 'style:table-row-properties'
            || $cTagName === 'style:table-properties' || $cTagName === 'style:text-properties') {
            $this->styles[$this->lastElement]['styles'][$cTagName] = $attrs;
        } elseif ($cTagName === 'table:table-cell') {
            $this->lastElement = $cTagName;
            $this->sheets[$this->currentSheet]['rows'][$this->currentRow][$this->currentCell]['attrs'] = $attrs;
            if (isset($attrs['TABLE:NUMBER-COLUMNS-REPEATED'])) {
                $times = (int)$attrs['TABLE:NUMBER-COLUMNS-REPEATED'];
                $times--;
                for ($i = 1; $i <= $times; $i++) {
                    $cnum = $this->currentCell + $i;
                    $this->sheets[$this->currentSheet]['rows'][$this->currentRow][$cnum]['attrs'] = $attrs;
                }
                $this->currentCell += $times;
                $this->repeat = $times;
            }
            if (isset($this->lastRowAtt['TABLE:NUMBER-ROWS-REPEATED'])) {
                $times = (int)$this->lastRowAtt['TABLE:NUMBER-ROWS-REPEATED'];
                $times--;
                for ($i = 1; $i <= $times; $i++) {
                    $cnum = $this->currentRow + $i;
                    $this->sheets[$this->currentSheet]['rows'][$cnum][$i - 1]['attrs'] = $attrs;
                }
                $this->currentRow += $times;
            }
        } elseif ($cTagName === 'table:table-row') {
            $this->lastRowAtt = $attrs;
        }
    }

    private function endElement($parser, $tagName)
    {
        $cTagName = strtolower($tagName);
        if ($cTagName === 'table:table') {
            $this->currentSheet++;
            $this->currentRow = 0;
        } elseif ($cTagName === 'table:table-row') {
            $this->currentRow++;
            $this->currentCell = 0;
        } elseif ($cTagName === 'table:table-cell') {
            $this->currentCell++;
            $this->repeat = 0;
        }
    }

    private function characterData($parser, $data)
    {
        if ($this->lastElement === 'table:table-cell') {
            $this->sheets[$this->currentSheet]['rows'][$this->currentRow][$this->currentCell]['value'] = $data;
            if ($this->repeat > 0) {
                for ($i = 0; $i < $this->repeat; $i++) {
                    $cnum = $this->currentCell - ($i + 1);
                    $this->sheets[$this->currentSheet]['rows'][$this->currentRow][$cnum]['value'] = $data;
                }
            }
        }
    }

    private function getMeta($lang)
    {
        $myDate = date('Y-m-j\TH:i:s');
        $meta = '<?xml version="1.0" encoding="UTF-8"?>
		<office:document-meta xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:ooo="http://openoffice.org/2004/office" xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" office:version="1.2">
			<office:meta>
				<meta:generator>odf-php</meta:generator>
				<meta:creation-date>' . $myDate . '</meta:creation-date>
				<dc:date>' . $myDate . '</dc:date>
				<dc:language>' . $lang . '</dc:language>
				<meta:editing-cycles>2</meta:editing-cycles>
				<meta:editing-duration>PT15S</meta:editing-duration>
				<meta:user-defined meta:name="Info 1"/>
				<meta:user-defined meta:name="Info 2"/>
				<meta:user-defined meta:name="Info 3"/>
				<meta:user-defined meta:name="Info 4"/>
			</office:meta>
		</office:document-meta>';
        return $meta;
    }

    private function getStyle($estilo_propio)
    {
        if ($estilo_propio) {
            if ($rta = file_get_contents($estilo_propio)) {
                return $rta;
            }
        }
    }

    private function getSettings($settings_propio)
    {
        if ($settings_propio) {
            if ($rta = file_get_contents($settings_propio)) {
                return $rta;
            }
        }
    }

    private function getManifest($doc_type)
    {
        switch ($doc_type) {
            case "text":
                $rta_1 = '<manifest:file-entry manifest:full-path="/" manifest:version="1.2" manifest:media-type="application/vnd.oasis.opendocument.text"/>';
                break;
            case "spreadsheet":
                $rta_1 = '<manifest:file-entry manifest:full-path="/" manifest:version="1.2" manifest:media-type="application/vnd.oasis.opendocument.spreadsheet"/>';
                break;
        }
        $rta = '<?xml version="1.0" encoding="UTF-8"?>
<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0" manifest:version="1.2" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0">';
        $rta .= $rta_1;
        $rta .= ' <manifest:file-entry manifest:full-path="Configurations2/" manifest:media-type="application/vnd.sun.xml.ui.configuration"/>
 <manifest:file-entry manifest:full-path="manifest.rdf" manifest:media-type="application/rdf+xml"/>
 <manifest:file-entry manifest:full-path="meta.xml" manifest:media-type="text/xml"/>
 <manifest:file-entry manifest:full-path="styles.xml" manifest:media-type="text/xml"/>
 <manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>
 <manifest:file-entry manifest:full-path="settings.xml" manifest:media-type="text/xml"/>
 <manifest:file-entry manifest:full-path="Thumbnails/thumbnail.png" manifest:media-type="image/png"/>
</manifest:manifest>';

        return $rta;
    }

    private function getManifestRdf()
    {
        $rta = '<?xml version="1.0" encoding="utf-8"?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
  <rdf:Description rdf:about="styles.xml">
    <rdf:type rdf:resource="http://docs.oasis-open.org/ns/office/1.2/meta/odf#StylesFile"/>
  </rdf:Description>
  <rdf:Description rdf:about="">
    <ns0:hasPart xmlns:ns0="http://docs.oasis-open.org/ns/office/1.2/meta/pkg#" rdf:resource="styles.xml"/>
  </rdf:Description>
  <rdf:Description rdf:about="content.xml">
    <rdf:type rdf:resource="http://docs.oasis-open.org/ns/office/1.2/meta/odf#ContentFile"/>
  </rdf:Description>
  <rdf:Description rdf:about="">
    <ns0:hasPart xmlns:ns0="http://docs.oasis-open.org/ns/office/1.2/meta/pkg#" rdf:resource="content.xml"/>
  </rdf:Description>
  <rdf:Description rdf:about="">
    <rdf:type rdf:resource="http://docs.oasis-open.org/ns/office/1.2/meta/pkg#Document"/>
  </rdf:Description>
</rdf:RDF>';
        return $rta;
    }

    public function addCell($sheet, $row, $cell, $value, $type)
    {
        $this->sheets[$sheet]['rows'][$row][$cell]['attrs'] = array('OFFICE:VALUE-TYPE' => $type, 'OFFICE:VALUE' => $value);
        $this->sheets[$sheet]['rows'][$row][$cell]['value'] = $value;
    }

    public function editCell($sheet, $row, $cell, $value)
    {
        $this->sheets[$sheet]['rows'][$row][$cell]['attrs']['OFFICE:VALUE'] = $value;
        $this->sheets[$sheet]['rows'][$row][$cell]['value'] = $value;
    }

    public function parseOds($file)
    {
        $tmp = $this->get_tmp_dir();
        copy($file, $tmp . '/' . basename($file));
        $path = $tmp . '/' . basename($file);
        $uid = uniqid('', true);
        if (!mkdir($concurrentDirectory = $tmp . '/' . $uid) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
        shell_exec('unzip ' . escapeshellarg($path) . ' -d ' . escapeshellarg($tmp . '/' . $uid));
        $this->parse(file_get_contents($tmp . '/' . $uid . '/content.xml'));
        return $this;
    }

    public function saveOds($file, $txt_html, $conv_style, $doc_type)
    {
        if ($conv_style) {
            if ($doc_type === 'text') {
                $estilo_propio = $this->getDirComponentes() . $conv_style . "_styles_txt.xml";
            } else {
                $estilo_propio = $this->getDirComponentes() . $conv_style . "_styles.xml";
            }
            $settings_propio = $this->getDirComponentes() . $conv_style . "_settings.xml";
            if (!file_exists($estilo_propio)) {
                $estilo_propio = $this->getDirComponentes() . "/styles.xml";
            }
            if (!file_exists($settings_propio)) {
                $settings_propio = $this->getDirComponentes() . "/settings.xml";
            }
        } else {
            $estilo_propio = $this->getDirComponentes() . "/styles.xml";
            $settings_propio = $this->getDirComponentes() . "/settings.xml";
        }
        $charset = ini_get('default_charset');
        ini_set('default_charset', 'UTF-8');

        $zip = new \ZipArchive;
        if ($zip->open($file, ZIPARCHIVE::CREATE) !== TRUE) {
            exit("cannot open <$file>\n");
        }
        switch ($doc_type) {
            case "text":
                $zip->addFromString('mimetype', 'application/vnd.oasis.opendocument.text');
                break;
            case "spreadsheet":
                $zip->addFromString('mimetype', 'application/vnd.oasis.opendocument.spreadsheet');
                break;
        }
        // si uso xsltproc el texto ya tiene las cabeceras.
        $zip->addFromString("content.xml", $txt_html);
        $zip->addFromString("meta.xml", $this->getMeta('es-ES'));
        $zip->addFromString("styles.xml", $this->getStyle($estilo_propio));
        $zip->addFromString("settings.xml", $this->getSettings($settings_propio));
        $zip->addFromString("manifest.rdf", $this->getManifestRdf());
        $zip->addEmptyDir("META-INF");
        $zip->addFromString("META-INF/manifest.xml", $this->getManifest($doc_type));
        $zip->addEmptyDir("Configurations2");
        $zip->addEmptyDir("Configurations2/accelerator");
        $zip->addEmptyDir("Configurations2/images");
        $zip->addEmptyDir("Configurations2/popupmenu");
        $zip->addEmptyDir("Configurations2/statusbar");
        $zip->addEmptyDir("Configurations2/floater");
        $zip->addEmptyDir("Configurations2/menubar");
        $zip->addEmptyDir("Configurations2/progressbar");
        $zip->addEmptyDir("Configurations2/toolbar");
        $zip->close();

        ini_set('default_charset', $charset);
    }

    public function newOds()
    {
        $file_content = $this->getDirComponentes() . '/content_ini.xml';
        $content = file_get_contents($file_content);

        $this->parse($content);
        return $this;
    }

    private function get_tmp_dir()
    {
        $path = '';
        if (!function_exists('sys_get_temp_dir')) {
            $path = $this->try_get_temp_dir();
        } else {
            $path = sys_get_temp_dir();
            if (is_dir($path)) {
                return $path;
            }

            $path = $this->try_get_temp_dir();
        }
        return $path;
    }

    private function try_get_temp_dir(): false|string
    {
        // Try to get from environment variable
        if (!empty($_ENV['TMP'])) {
            $path = realpath($_ENV['TMP']);
        } else if (!empty($_ENV['TMPDIR'])) {
            $path = realpath($_ENV['TMPDIR']);
        } else if (!empty($_ENV['TEMP'])) {
            $path = realpath($_ENV['TEMP']);
        } // Detect by creating a temporary file
        else {
            // Try to use system's temporary directory
            // as random name shouldn't exist
            $temp_file = tempnam(md5(uniqid(mt_rand(), TRUE)), '');
            if ($temp_file) {
                $temp_dir = realpath(dirname($temp_file));
                unlink($temp_file);
                $path = $temp_dir;
            } else {
                return "/tmp";
            }
        }
        return $path;
    }

}
