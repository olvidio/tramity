<?php

namespace expedientes\model;

use convertirdocumentos\model\DocConverter;
use documentos\model\Documento;
use entradas\model\entity\EntradaAdjunto;
use entradas\model\Entrada;
use envios\model\Enviar;
use escritos\model\entity\EscritoAdjunto;
use escritos\model\Escrito;
use escritos\model\TextoDelEscrito;

class VerAntecedentes
{

    private string $path_temp;

    public function __construct($path_temp)
    {
        $this->path_temp = $path_temp;
    }

    /**
     * @param array $a_id_adjuntos
     * @param string $propietario
     * @return array
     */
    private function extractAdjuntos(array $a_id_adjuntos, string $propietario): array
    {
        $aFiles = [];
        foreach ($a_id_adjuntos as $item => $adjunto_filename) {
            if ($propietario === 'entrada') {
                $oAdjunto = new EntradaAdjunto($item);
                $nombre_fichero = $oAdjunto->getNom();
            } elseif ($propietario === 'escrito') {
                $oAdjunto = new EscritoAdjunto($item);
                $nombre_fichero = $oAdjunto->getNom();
            }
            $doc = $oAdjunto->getAdjunto();
            $path_parts = pathinfo($nombre_fichero);
            $file_extension = $path_parts['extension'];
            $file_name = $path_parts['filename'];
            $nombre_fichero_pdf = $this->path_temp . $file_name . '.pdf';
            if ($file_extension !== 'pdf') {
                $nombre_fichero_pdf = $this->convertirDocEnPdf($nombre_fichero, $doc);
            } else {
                file_put_contents($nombre_fichero_pdf, $doc);
            }
            $aFiles[] = $nombre_fichero_pdf;
        }
        return $aFiles;
    }

    private function convertirDocEnPdf($nombre_fichero, $doc): string
    {
        $oDocConverter = new DocConverter();
        $oDocConverter->setNombreFicheroOriginalConExtension($nombre_fichero);
        $oDocConverter->setDocIn($doc);
        $oDocConverter->convert('pdf', FALSE);
        return $oDocConverter->getNombreFicheroNuevoConExtension();
    }

    public function verEnPdf($aAntecedentes): array
    {
        $aFiles = [];
        foreach ($aAntecedentes as $antecedente) {
            $id = $antecedente['id'];
            $tipo = $antecedente['tipo'];
            switch ($tipo) {
                case 'entrada':
                    $oEnviarEntrada = new Enviar($id, 'entrada');
                    $File = $oEnviarEntrada->getPdf();

                    $file_content = $File['content'];
                    $file_name = $File['name'];
                    $file_name_con_extension = $File['ext'];

                    $filename_local_con_extension = $this->path_temp . $file_name_con_extension;
                    // con los espacios hay problemas, no bastan las comillas
                    $filename_local_sin_espacios_con_extension = str_replace(' ', '_', $filename_local_con_extension);
                    file_put_contents($filename_local_sin_espacios_con_extension, $file_content);

                    $aFiles[] = $filename_local_sin_espacios_con_extension;

                    // Buscar si tiene adjuntos:
                    $oEntrada = new Entrada($id);
                    $a_id_adjuntos = $oEntrada->getArrayIdAdjuntos();
                    $aFiles_adj = $this->extractAdjuntos($a_id_adjuntos, 'entrada');
                    if (!empty($aFiles_adj)) {
                        $aFiles = array_merge($aFiles, $aFiles_adj);
                    }
                    break;
                case 'escrito':
                    $oEnviarEscrito = new Enviar($id, 'escrito');
                    $File = $oEnviarEscrito->getPdf();

                    $file_content = $File['content'];
                    $file_name = $File['name'];
                    $file_name_con_extension = $File['ext'];

                    $filename_local_con_extension = $this->path_temp . $file_name_con_extension;
                    // con los espacios hay problemas, no bastan las comillas
                    $filename_local_sin_espacios_con_extension = str_replace(' ', '_', $filename_local_con_extension);
                    file_put_contents($filename_local_sin_espacios_con_extension, $file_content);

                    $aFiles[] = $filename_local_sin_espacios_con_extension;

                    // Buscar si tiene adjuntos:
                    $oEscrito = new Escrito($id);
                    $a_id_adjuntos = $oEscrito->getArrayIdAdjuntos();
                    $aFiles_adj = $this->extractAdjuntos($a_id_adjuntos, 'escrito');
                    if (!empty($aFiles_adj)) {
                        $aFiles = array_merge($aFiles, $aFiles_adj);
                    }
                    break;
                case 'documento':
                    $oDocumento = new Documento($id);
                    $tipo_doc = $oDocumento->getTipo_doc();
                    $nom = $oDocumento->getNom();
                    switch ($tipo_doc) {
                        case TextoDelEscrito::TIPO_UPLOAD:
                            $doc = $oDocumento->getDocumento();
                            $nombre_fichero = $oDocumento->getNombre_fichero();
                            $path_parts = pathinfo($nombre_fichero);
                            //$base_name = $path_parts['basename'];
                            $file_extension = $path_parts['extension'];
                            $file_name = $path_parts['filename'];
                            $nombre_fichero_pdf = $this->path_temp . $file_name . '.pdf';
                            if ($file_extension !== 'pdf') {
                                $nombre_fichero_pdf = $this->convertirDocEnPdf($nombre_fichero, $doc);
                            } else {
                                file_put_contents($nombre_fichero_pdf, $doc);
                            }
                            $aFiles[] = $nombre_fichero_pdf;
                            break;
                        default:
                            $oTextoDelEscrito = new TextoDelEscrito($tipo_doc,TextoDelEscrito::ID_DOCUMENTO, $id);

                            // propiamente no tiene cabeceras ni fecha salida
                            $a_header = ['left' => 'doc',
                                'center' => '',
                                'right' => 'algo',
                            ];
                            $f_salida = '';
                            $oTextoDelEscrito->addHeaders($a_header, $f_salida);
                            // formato pdf:
                            $file_name = $this->path_temp . $nom;
                            $filename_local_con_extension = $file_name . '.pdf';
                            $file_content = $oTextoDelEscrito->getContentFormatPDF();

                            // con los espacios hay problemas, no bastan las comillas
                            $filename_local_sin_espacios_con_extension = str_replace(' ', '_', $filename_local_con_extension);
                            file_put_contents($filename_local_sin_espacios_con_extension, $file_content);

                            $aFiles[] = $filename_local_sin_espacios_con_extension;
                            break;
                    }
                    break;
                case 'expediente':
                    $oExpediente = new Expediente($id);
                    $aAntecedentes = $oExpediente->getJson_antecedentes(TRUE);

                    $VerAntecedentes = new self($this->path_temp);
                    $aFiles_exp = $VerAntecedentes->verEnPdf($aAntecedentes);
                    if (!empty($aFiles_exp)) {
                        $aFiles = array_merge($aFiles, $aFiles_exp);
                    }
                    break;
                default:
                    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_switch);
            }
        }
        return $aFiles;
    }
}