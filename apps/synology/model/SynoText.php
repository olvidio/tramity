<?php

namespace synology\model;

use convertirdocumentos\model\DocConverter;
use escritos\model\TextoDelEscrito;
use escritos\model\TextoDelEscritoInterface;
use Mpdf\Mpdf;
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
    private string $fecha;
    private array $a_header;

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

    private function generarPdf(): string
    {
        $this->downloadAsDocx();
        return $this->convert2Pdf();
    }

    public function generarHtml(): string
    {
        $this->downloadAsDocx();
        return $this->convert2Html();
    }

    private function uploadDocx($file)
    {
        $synologyClient = new FileStationClientTramity('host.docker.internal', 5000, 'http', 2);
        //$synologyOffice->activateDebug();
        $synologyClient->connect('dani', 'Mam00t!$Dam#24');

        $synologyClient->uploadFile($file, $this->filename);
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

        return $doc;
    }

    public function getContentFormatODT(): string
    {
        // TODO
    }

    private function convert2Pdf(): string
    {
        $oDocConverter = new DocConverter();
        $oDocConverter->setNombreFicheroOriginalConExtension($this->filename);
        $oDocConverter->setDocIn($this->ms_word);
        $doc = $oDocConverter->convert('pdf');

        return $doc;
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
        // Cero que no se puede. Hay que crear uno nuevo
        // crear un archivo docx. (a partir del html)
        // subirlo y pasarlo a odoc.

        $oDocConverter = new DocConverter();
        $oDocConverter->setNombreFicheroOriginalConExtension($this->filename);
        $oDocConverter->setDocIn($html);
        $docx = $oDocConverter->convert('docx');

        $this->uploadDocx($docx);
    }

    public function eliminar()
    {
        // TODO: Implement eliminar() method.
        $rta = $this->synologyDrive->delete($this->file_path);
    }

    public function copyTo($newId_escrito)
    {
        // TODO: Implement copyTo() method.
    }

    /**
     * Se usa para añadir las firmas
     * @return string
     */
    public function getHtmlSinLimpiar(): string
    {
        return $this->generarHtml();
    }

    public function crearTexto(): void
    {
        // TODO: Implement getPadID() method.
    }

    public function setTextContent($text_content): void
    {
        // TODO: Implement setTextContent() method.
        // parece que la manera será crearlo de cero
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
        $this->a_header = $a_header;
        $this->fecha = $fecha;
    }

    public function getContentFormatPDF(): string
    {
        // Para poner las cabeceras y la fecha, genero tres pdf y los unifico.
        // ESTO NO FUNCIONA porque cada pdf tiene sus márgenes y referencias de posición respecto a la página
        // y al unir se sobreponen, o se pone un salto de página.

        // Paso por el html como se hace en el etherpad.

        // cabeceras
        $cabeceraHtml = $this->cabeceraHtml();
        // escrito
        $docHtml = $this->generarHtml();
        // fecha
        $pieHtml = $this->pieHtml();

        $html = $cabeceraHtml . $docHtml . $pieHtml;

        $oDocConverter = new DocConverter();
        $oDocConverter->setNombreFicheroOriginalConExtension($this->filename);
        $oDocConverter->setDocIn($html);
        $pdf = $oDocConverter->convert('pdf');

        return $pdf;
    }

    public function getContentFormatDOCX(): string
    {
        // TODO: Implement getContentFormatDOCX() method.
    }

    private function cabeceraPdfFilename()
    {
        $filename_uniq = uniqid('convert_', true);
        $path_temp = '/tmp/';
        $file_cabecera = $path_temp . "$filename_uniq" . ".pdf";

        $html = $this->cabeceraHtml();

        $mpdf = new Mpdf();
        $mpdf->WriteHTML($html);
        $mpdf->Output($file_cabecera, \Mpdf\Output\Destination::FILE);

        return $file_cabecera;
    }

    private function cabeceraHtml(): string
    {
        if (!empty($this->a_header)) {
            $origen = '';
            $destino = '';
            if (!empty($this->a_header['left'])) {
                $a_izq = explode('<br>', $this->a_header['left']);
                $destino = $a_izq[0];
                $ref_izq = empty($a_izq[1]) ? '' : $a_izq[1];
            }
            if (!empty($this->a_header['right'])) {
                $a_dcha = explode('<br>', $this->a_header['right']);
                $origen = $a_dcha[0];
                $ref_dcha = empty($a_dcha[1]) ? '' : $a_dcha[1];
            }
            // linea 1
            $cabecera = "<table width='600px' >";
            $cabecera .= "<tr><td>";
            $cabecera .= $destino;
            $cabecera .= "</td><td style=\"text-align: right\">";
            $cabecera .= $origen;
            $cabecera .= "</td>";
            $cabecera .= "</tr>";
            // liena 2
            if (!empty($ref_izq) || !empty($ref_dcha)) {
                $cabecera .= "<tr>";
                if (!empty($ref_izq)) {
                    $cabecera .= $ref_izq;
                }
                if (!empty($ref_dcha)) {
                    $cabecera .= "<td></td>";
                    $cabecera .= "<td style=\"text-align: right\">";
                    $cabecera .= $ref_dcha;
                    $cabecera .= "</td>";
                }
                $cabecera .= "</tr>";
            }
            $cabecera .= "</table>";
            $cabecera .= "<br />";
        }
        return $cabecera;
    }

    private function piePdfFilename()
    {
        $filename_uniq = uniqid('convert_', true);
        $path_temp = '/tmp/';
        $file_pie = $path_temp . "$filename_uniq" . ".pdf";

        $html = $this->pieHtml();

        $mpdf = new Mpdf();
        $mpdf->WriteHTML($html);
        $mpdf->Output($file_pie, \Mpdf\Output\Destination::FILE);

        return $file_pie;
    }

    private function pieHtml(): string
    {
        $pie = '';
        if (!empty($this->fecha)) {
            $pie = "<table width='600px'>";
            $pie .= "<tr><td></td>";
            $pie .= "<td style=\"text-align: right\">";
            $pie .= $this->fecha;
            $pie .= "</td>";
            $pie .= "</tr></table>";
        }

        return $pie;
    }
}