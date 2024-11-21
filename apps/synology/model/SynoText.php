<?php

namespace synology\model;

use convertir_odf\model\Odf;
use convertirdocumentos\model\DocConverter;
use core\ConfigGlobal;
use envios\model\MIMETypeLocal;
use escritos\model\TextoDelEscrito;
use escritos\model\TextoDelEscritoInterface;
use web\StringLocal;

class SynoText implements TextoDelEscritoInterface
{
    private $file_path = null;
    private $dir_path = null;
    private $filename;

    private $synology;
    private $sigla;

    private $synologyDrive;
    private mixed $permanent_link;
    private string $ms_word;

    /**
     */
    public function __construct($api_version = 3)
    {
        $this->sigla = $_SESSION['oConfig']->getSigla();

        $this->synology = new FileStationClientTramity('host.docker.internal', 5000, 'http', $api_version);
        //$this->synology->activateDebug();
        $this->synology->connect('dani', 'Mam00t!$Dam#24');


        $this->synologyDrive = new SynologyDriveClientTramity('host.docker.internal', 5000, 'http', 3);
        //$this->synologyDrive->activateDebug();
        $this->synologyDrive->connect('dani', 'Mam00t!$Dam#24');
    }

    public function getJsonEditorUrl(): array
    {
        $server_url = $this->getServerUrl();
        // URL
        $this->getPermanentLink();
        //$url = 'http://localhost:5000/oo/r/' . $permanent_link;
        $url = $server_url . '/oo/r/' . $this->permanent_link;

        // Cookie
        $id = $this->synologyDrive->getSessionId();
        // viene con comillas '"'. Quitarlas.
        $id = str_replace('"', '', $id);
        setcookie("id", $id, time() + 1800, "/", "", false);

        return ['url' => $url, 'id' => $id];
    }

    public function setId($tipo_id, $id_escrito, $sigla = ''): string
    {
        // excepción para las entradas compartidas:
        if ($tipo_id === TextoDelEscrito::ID_COMPARTIDO) {
            $prefix = 'com';
            $this->dir_path = "/" . $prefix;
        } else {
            // Añado el nombre del centro. De forma normalizada, pues a saber que puede tener el nombre:
            if (!empty($sigla)) {
                $this->sigla = $sigla;
            }
            $nom_ctr = StringLocal::toRFC952($this->sigla);

            switch ($tipo_id) {
                case TextoDelEscrito::ID_ADJUNTO:
                    $prefix = 'adj';
                    break;
                case TextoDelEscrito::ID_DOCUMENTO:
                    $prefix = 'doc';
                    break;
                case TextoDelEscrito::ID_ENTRADA:
                    $prefix = 'ent';
                    break;
                case TextoDelEscrito::ID_ESCRITO:
                    $prefix = 'esc';
                    break;
                case TextoDelEscrito::ID_EXPEDIENTE:
                    $prefix = 'exp';
                    break;
                case TextoDelEscrito::ID_PLANTILLA:
                    $prefix = 'plt';
                    break;
                default:
                    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_switch);
            }
            $this->dir_path = "/" . $nom_ctr . "/" . $prefix;
        }
        $sufijo = 'odoc';
        $this->filename = $id_escrito . '.' . $sufijo;
        $this->file_path = $this->dir_path . "/" . $this->filename;

        return $this->file_path;
    }

    private function cretePath()
    {
        //$path = "/dlb/esc";

        $this->synology = new FileStationClientTramity('host.docker.internal', 5000, 'http', 3);
        //$this->synology->activateDebug();
        $this->synology->connect('dani', 'Mam00t!$Dam#24');

        // copiar de plantillas blank
        $path_template = '["' . "/dlb/templates/blank.odoc" . '"]';

        //$rta = $this->synology->CopyMove('["/dlb/templates/blank.odoc"]','"/dlb/esc"');
        $rta = $this->synology->CopyMove($path_template, $this->dir_path);
        if (empty($rta['taskid'])) {
            die(_("ERROROR"));
        }
        return $rta['taskid'];
    }


    private function renameFromPlantilla(string $name)
    {
        // from version 2
        $synology = new FileStationClientTramity('host.docker.internal', 5000, 'http', 2);
        $synology->connect('dani', 'Mam00t!$Dam#24');

        $a_path_org = "[" . '"' . $this->dir_path . '/blank.odoc' . '"' . "]";
        $a_name = "[" . '"' . $name . '"' . "]";
        $rta = $synology->rename($a_path_org, $a_name);
        return $rta;
    }

    public function generarHtml(): string
    {
        $this->downloadAsDocx();
        return $this->convert2Html();
    }

    public function downloadAsDocx()
    {
        $synologyOffice = new OfficeTramity('host.docker.internal', 5000, 'http', 3);
        //$synologyOffice->activateDebug();
        $synologyOffice->connect('dani', 'Mam00t!$Dam#24');

        // copiar de plantillas blank
        if (empty($this->permanent_link)) {
            $this->getPermanentLink();
        }
        $path_link = "link:" . $this->permanent_link;


        $this->ms_word = $synologyOffice->export($path_link);

        return $this->ms_word;

    }

    private function convert2Html(): string
    {

        $oDocConverter = new DocConverter();
        $oDocConverter->setNombreFicheroOriginalConExtension($this->filename);
        $oDocConverter->setDocIn($this->ms_word);
        $doc = $oDocConverter->convert('html');
        $nombre_fichero_pdf = $this->filename . '.html';
        $file_extension = 'html';

        return $doc;
    }

    public function getContentFormatODT(): string
    {
        // Para poner las cabeceras y la fecha, genero tres pdf y los unifico.
        // Para no pasar por el html com se hace en el etherpad.

        $pdf_all = '';
        // cabeceras
        $cabecera = '';
        if (!empty($a_header)) {
            $origen = '';
            $destino = '';
            if (!empty($a_header['left'])) {
                $a_izq = explode('<br>', $a_header['left']);
                $destino = $a_izq[0];
                $ref_izq = empty($a_izq[1]) ? '' : $a_izq[1];
            }
            if (!empty($a_header['right'])) {
                $a_dcha = explode('<br>', $a_header['right']);
                $origen = $a_dcha[0];
                $ref_dcha = empty($a_dcha[1]) ? '' : $a_dcha[1];
            }
            // linea 1
            $cabecera = "<cabecera>";
            $cabecera .= $destino;
            $cabecera .= "<cabecera_end>";
            $cabecera .= $origen;
            $cabecera .= "</cabecera_end>";
            $cabecera .= "</cabecera>";
            // liena 2
            if (!empty($ref_izq) || !empty($ref_dcha)) {
                $cabecera .= "<cabecera>";
                if (!empty($ref_izq)) {
                    $cabecera .= $ref_izq;
                }
                if (!empty($ref_dcha)) {
                    $cabecera .= "<cabecera_end>";
                    $cabecera .= $ref_dcha;
                    $cabecera .= "</cabecera_end>";
                }
                $cabecera .= "</cabecera>";
            }
            $cabecera .= "<separacion></separacion>";
        }

        $file_txt = "/tmp/$filename_sin_ext.txt";
        $file_xml = "/tmp/$filename_sin_ext.xml";
        // Utilizo la misma plantilla que para el ethrepad
        $xslt = "html2odftextTramity.xslt";
        $conv_style = "5";

        // para el bash. poner el nombre de fichero entre comillas simples, y escapar las posibles comillas del nombre.
        $file_txt_escaped = "'" . str_replace("'", "\'", $file_txt) . "'";
        $file_xml_escaped = "'" . str_replace("'", "\'", $file_xml) . "'";
        $cmd = "xsltproc --html " . ConfigGlobal::getDIR() . ODF::DIR_COMPONENTES . "xslt/$xslt $file_txt_escaped > $file_xml_escaped";
        $a_output = [];
        exec($cmd, $a_output, $return_var);
        $content_xml = file_get_contents($file_xml);

        // fecha
        if (!empty($fecha)) {
            $fecha_txt = "<fecha>$fecha</fecha>";
        }


        $a_pdf = $this->convert2Pdf();
        $filename_content = $a_pdf['nombre_fichero_pdf'];

        return $pdf_all;
    }

    public function convert2Pdf(): array
    {

        $oDocConverter = new DocConverter();
        $oDocConverter->setNombreFicheroOriginalConExtension($this->filename);
        $oDocConverter->setDocIn($this->ms_word);
        $doc = $oDocConverter->convert('pdf');
        $nombre_fichero_pdf = $this->filename . '.pdf';
        $file_extension = 'pdf';

        $a_datos_fichero = [
            'doc' => $doc,
            'nombre_fichero_pdf' => $nombre_fichero_pdf,
            'file_extension' => $file_extension,
        ];

        return $a_datos_fichero;
    }


    /**
     * @return array|void
     * @throws \Kazio73\SynologyApiClient\Client\SynologyException
     */
    private function getPermanentLink()
    {
        $rta = $this->synologyDrive->getInfo("team", $this->file_path);
        // si no existe lo creo
        if (empty($rta['name'])) {
            // no existe!
            $taskid = $this->cretePath();
            // comprobar que ha terminado

            // cambiar el nombre
            $rta2 = $this->renameFromPlantilla($this->filename);
            if (!empty($rta2['error_code'])) {
                exit ("Failed to rename it.");
            }
            // volver a consultar el link
            $rta = $this->synologyDrive->getInfo("team", $this->file_path);
        }

        $this->permanent_link = $rta['permanent_link'];

        return $this->permanent_link;
    }


    public function setHtml($html)
    {
        // TODO: Implement setHtml() method.
    }

    public function eliminar()
    {
        // TODO: Implement eliminar() method.
    }

    public function copyTo($newId_escrito)
    {
        // TODO: Implement copyTo() method.
    }

    public function getHtmlSinLimpiar(): string
    {
        // TODO: Implement getHHtml() method.
    }

    public function crearTexto(): void
    {
        // TODO: Implement getPadID() method.
    }

    public function setTextContent($text_content): void
    {
        // TODO: Implement setTextContent() method.
    }

    public function generarMD(): string
    {
        // TODO: Implement generarMD() method.
    }

    public function getServerUrl(): string
    {
        $server_url = 'http://dlb.tramity-dl.docker:5000';
        return $server_url;
    }

    public function setMultiple($multiple): void
    {
        // TODO: Implement setMultiple() method.
    }

    public function addHeaders(array $a_header = [], string $fecha = ''): void
    {
        // TODO: Implement addHeaders() method.
    }

    public function getContentFormatPDF(): string
    {
        // TODO: Implement getContentFormatPDF() method.
    }

    public function getContentFormatDOCX(): string
    {
        // TODO: Implement getContentFormatDOCX() method.
    }
}