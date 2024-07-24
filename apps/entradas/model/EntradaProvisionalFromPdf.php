<?php

namespace entradas\model;


use core\ConfigGlobal;
use entradas\model\entity\EntradaDocDB;
use lugares\model\entity\GestorLugar;
use lugares\model\entity\Lugar;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;
use usuarios\model\Categoria;
use web\DateTimeLocal;
use web\Protocolo;
use function core\any_2;

require_once(ConfigGlobal::dir_libs() . '/vendor/autoload.php');

class EntradaProvisionalFromPdf
{

    private string $content_pdf;
    private string $page_frist;
    private string $page_last;
    private int $num_pages;
    private bool $rectorado;

    private string $filtro;

    public function __construct($content_pdf)
    {
        $this->content_pdf = $content_pdf;
    }

    /**
     * @param string $line
     * @return DateTimeLocal|null ;
     */
    private function extractFecha(string $line): ?DateTimeLocal
    {
        $oFecha = null;
        // Ciudad + fecha simple corta
        $pattern = '/^\s*(\P{N}*),?\s*(\d{1,2})([\/\-.])(\d{1,2})([\/\-.])(\d{2,4})\s*$/u';
        $coincide_fecha = preg_match($pattern, $line, $matches);
        if ($coincide_fecha === 1) {
            $ciudad = $matches[1];
            $dia = (int)trim($matches[2]);
            $mes = (int)trim($matches[4]);
            $any = (int)trim($matches[6]);

            $oFecha = new DateTimeLocal("$any-$mes-$dia");
        }
        if ($coincide_fecha !== 1) {
            // ciudad + fecha corta con meses en números romanos
            $pattern = '/^\s*(\P{N}*),?\s*(\d{1,2})([\/\-.])((I|V|X)*)([\/\-.])(\d{2,4})\s*$/u';
            $coincide_fecha = preg_match($pattern, $line, $matches);
            if ($coincide_fecha === 1) {
                $ciudad = $matches[1];
                $dia = (int)trim($matches[2]);
                $mes_roman = trim($matches[4]);
                $any = (int)trim($matches[7]);
                $a_num_romanos = ["I" => 1,
                    "II" => 2,
                    "III" => 3,
                    "IV" => 4,
                    "V" => 5,
                    "VI" => 6,
                    "VII" => 7,
                    "VIII" => 8,
                    "IX" => 9,
                    "X" => 10,
                    "XI" => 11,
                    "XII" => 12,
                ];
                $mes = $a_num_romanos[$mes_roman];
                $oFecha = new DateTimeLocal("$any-$mes-$dia");
            }
        }
        if ($coincide_fecha !== 1) {
            // ciudad + fecha con mes en texto
            $pattern = '/^\s*(\P{N}*),?\s*(\d{1,2})\s+de\s+(\P{N}*)\s+de\s+(\d{2,4})\s*$/u';
            $coincide_fecha = preg_match($pattern, $line, $matches);
            if ($coincide_fecha === 1) {
                $ciudad = $matches[1];
                $dia = (int)trim($matches[2]);
                $mes_txt = strtolower(trim($matches[3]));
                $any = (int)trim($matches[4]);
                $a_Meses = DateTimeLocal::Meses();
                $mes_numero = array_search($mes_txt, $a_Meses);
                // probar con otro idioma
                if (empty($mes_numero)) {
                    $a_Meses = DateTimeLocal::Meses_cat();
                    $mes_numero = array_search($mes_txt, $a_Meses);
                }
                if (empty($mes_numero)) {
                    $a_Meses = DateTimeLocal::Meses_es();
                    $mes_numero = array_search($mes_txt, $a_Meses);
                }
                if (empty($mes_numero) || empty ($dia) || empty($any)) {
                    return $oFecha;
                }
                $oFecha = new DateTimeLocal("$any-$mes_numero-$dia");
            }
        }
        return $oFecha;
    }


    private function leer_pdf()
    {
        // Sirve para que ponga '*' en vez de espacio cuando hay caracteres raros.
        // sucede en el caso de los acentos (se complica la expresión regular)
        $config = new Config();
        $config->setHorizontalOffset('*');

        $parser = new Parser([], $config);
        $pdf = $parser->parseContent($this->content_pdf);
        // extract text of the whole PDF
        //$text = $pdf->getText();
        // or extract the text of a specific page (in this case the first page)
        $this->page_frist = $pdf->getPages()[0]->getText();

        $num_pages = count($pdf->getPages()) - 1;
        $this->page_last = $pdf->getPages()[$num_pages]->getText();
        $this->num_pages = $num_pages;

        $this->rectorado = FALSE;
        $metaData = $pdf->getDetails();
        $producer = $metaData['Producer'];
        if (str_contains($producer, 'iText Group')) {
            // del IESE
            $this->rectorado = TRUE;
        }
    }

    public function crear_entrada_provisional(string $asunto)
    {
        $this->leer_pdf();

        $text = $this->page_frist;
        $a_txt = explode("\n", $text);

        if ($this->rectorado) {
            $cabeceras = $this->getOrigenDestinoRectorado($a_txt);
        } else {
            $cabeceras = $this->getOrigenDestino($a_txt);
        }

        $origen = $cabeceras['origen'];
        $origen_prot = $cabeceras['origen_prot'];
        $destino = $cabeceras['destino'];
        $destino_prot = $cabeceras['destino_prot'];
        $a_referencias = $cabeceras['a_referencias'];
        $a_ref = $cabeceras['a_ref'];
        $a_ref_prot = $cabeceras['a_ref_prot'];
        $oFecha = $cabeceras['oFecha'];
        $asunto = empty($cabeceras['asunto'])? $asunto : $cabeceras['asunto'];
        $detalle = empty($cabeceras['detalle'])? '' : $cabeceras['detalle'];

        if ($this->num_pages > 0) {
            $text = $this->page_last;
            $a_txt = explode("\n", $text);
            $num_lineas = count($a_txt);
            $l = 0;
            foreach ($a_txt as $line) {
                $l++;
                $tramo_inicio = ($l - 5 < 0);
                $tramo_fin = ($num_lineas - $l < 5);
                if (empty($line) && !$tramo_inicio && !$tramo_fin) {
                    continue;
                }
                // Para obtener la fecha.
                if (empty($oFecha) && $tramo_fin) {
                    $oFecha = $this->extractFecha($line);
                }
            }
        }


        $oEntrada = new Entrada();
        $oEntrada->setAsunto_entrada($asunto);
        $oEntrada->setAsunto($asunto);
        if (!empty($detalle)) {
            $oEntrada->setDetalle($detalle);
        }
        $oEntrada->setModo_entrada(Entrada::MODO_PROVISIONAL);
        // Por el momento solamente etherpad
        $oEntrada->setTipo_documento(EntradaDocDB::TIPO_ETHERPAD);

        if (!empty($origen)) {
            // al buscar sin acentos se usan las expresiones regulares.
            // Para los centros tipo: sss+Barcelona, hay que escapar:
            $origen = str_replace('+', '\+', $origen);
            $gesLugares = new GestorLugar();
            $origen_escapado = preg_quote($origen, NULL);
            $cLugares = $gesLugares->getLugares(['sigla' => "^$origen_escapado\$"], ['sigla' => 'sin_acentos']);
            if (empty($cLugares)) {
                //exit (_("No sé de dónde viene"));
            } else {
                $oLugar = $cLugares[0];
                $id_lugar = $oLugar->getId_lugar();

                if (!empty($origen_prot)) {
                    $a_origen = explode('/', $origen_prot);
                    $prot_num_origen = empty($a_origen[0]) ? '' : trim($a_origen[0]);
                    $prot_any_origen = empty($a_origen[1]) ? '' : trim($a_origen[1]);
                } else {
                    $prot_num_origen = '';
                    $prot_any_origen = '';
                }
                $oProtOrigen = new Protocolo($id_lugar, $prot_num_origen, $prot_any_origen, '');
                $oEntrada->setJson_prot_origen($oProtOrigen->getProt());
            }
        }

        // Si destino tiene número de protocolo se pone como referencia
        $aProtRef = [];
        if (!empty($destino) && !empty($destino_prot)) {
            // al buscar sin acentos se usan las expresiones regulares.
            // Para los centros tipo: sss+Barcelona, hay que escapar:
            $destino = str_replace('+', '\+', $destino);
            $gesLugares = new GestorLugar();
            $cLugares = $gesLugares->getLugares(['sigla' => "^$destino\$"], ['sigla' => 'sin_acentos']);
            if (empty($cLugares)) {
                //exit (_("No sé el destino"));
            } else {
                $oLugar = $cLugares[0];
                $id_lugar = $oLugar->getId_lugar();

                if (!empty($destino_prot)) {
                    $a_destino = explode('/', $destino_prot);
                    $prot_num_destino = empty($a_destino[0]) ? '' : trim($a_destino[0]);
                    $prot_any_destino = empty($a_destino[1]) ? '' : trim($a_destino[1]);
                } else {
                    $prot_num_destino = '';
                    $prot_any_destino = '';
                }
                $oProtDestino = new Protocolo($id_lugar, $prot_num_destino, $prot_any_destino, '');
                $aProtRef[] = $oProtDestino->getProt();
            }
        }

        if (!empty($a_ref)) {
            foreach ($a_referencias as $key => $ref) {
                $lugar_ref = $a_referencias[$key];
                $prot_ref = $a_ref_prot[$key];

                // al buscar sin acentos se usan las expresiones regulares.
                // Para los centros tipo: sss+Barcelona, hay que escapar:
                $lugar_ref = str_replace('+', '\+', $lugar_ref);
                $gesLugares = new GestorLugar();
                $cLugares = $gesLugares->getLugares(['sigla' => "^$lugar_ref\$"], ['sigla' => 'sin_acentos']);
                if (empty($cLugares)) {
                    //exit (_("No sé la referencia"));
                    $id_lugar = 0;
                } else {
                    $oLugar = $cLugares[0];
                    $id_lugar = $oLugar->getId_lugar();

                    $a_ref_n = explode('/', $prot_ref);
                    $prot_num_ref = trim($a_ref_n[0]);
                    $prot_any_ref = trim($a_ref_n[1]);
                    if (!empty($id_lugar)) {
                        $oProtRef = new Protocolo($id_lugar, $prot_num_ref, $prot_any_ref, '');
                        $aProtRef[] = $oProtRef->getProt();
                    }
                }
            }
        }
        $oEntrada->setJson_prot_ref($aProtRef);

        $oHoy = new DateTimeLocal();
        $oEntrada->setF_entrada($oHoy);

        $oEntrada->setCategoria(Categoria::CAT_NORMAL);

        if ($this->filtro === 'en_admitido') {
            // Si estoy en la pestaña de admitir:
            $oEntrada->setEstado(Entrada::ESTADO_ADMITIDO);
        } else {
            $oEntrada->setEstado(Entrada::ESTADO_INGRESADO);
        }

        if ($oEntrada->DBGuardar() === FALSE) {
            $error_txt = $oEntrada->getErrorTxt();
        }

        // una vez obtenido el id_entrada, puedo crear el doc
        if (!empty($oFecha) && $oFecha instanceof DateTimeLocal) {
            $f_iso = $oFecha->getIso();
            $oEntradaDocDB = new EntradaDocDB($oEntrada->getId_entrada());
            $oEntradaDocDB->setF_doc($f_iso, FALSE);
            $oEntradaDocDB->setTipo_doc($oEntrada->getTipo_documento());
            if ($oEntradaDocDB->DBGuardar() === FALSE) {
                return FALSE;
            }
        }

        return $oEntrada->getId_entrada();

    }


    private function getOrigenDestinoRectorado($a_txt)
    {
        $es_origen_canilleria = FALSE;

        $gesLugares = new GestorLugar();
        $id_local = $gesLugares->getId_sigla_local();
        $id_cancilleria = $gesLugares->getId_cancilleria();
        $id_uden = $gesLugares->getId_uden();

        $oLugar = new Lugar($id_local);
        $sigla_local = $oLugar->getSigla();
        $oLugar = new Lugar($id_cancilleria);
        $sigla_cancilleria = $oLugar->getSigla();
        $oLugar = new Lugar($id_uden);
        $sigla_uden = $oLugar->getSigla();

        $asunto= '';
        $detalle= '';
        $origen = '';
        $oFecha = '';
        $destino = '';
        $a_ref = [];
        $a_referencias = [];
        $a_ref_prot = [];

        $rectorado_txt = "";

        $num_lineas = count($a_txt);
        $l = 0;
        $linea_asunto = 0;
        $content_linea_asunto = '';
        foreach ($a_txt as $line) {
            $l++;
            // OJO con los espacios...
            $line = trim($line);
            if (empty($line)) {
                continue;
            }
            /*
            Asunto: las primeras lineas escritas hasta una liena en blanco
            interesa lo que está entre paréntesis (CANC. 334/24)  (REF. REG.2024/510)
            */
            if (empty($linea_asunto)) {
                $content_linea_asunto = $line;
                $linea_asunto = $l;
                continue;
            } else {
                // saber si son lineas contiguas (el return es una linea en blanco):
                if (($l - $linea_asunto) === 2 ) {
                    $content_linea_asunto .= $line;
                    $linea_asunto = $l;
                    continue;
                }
            }


            if (!empty($content_linea_asunto)) {
                //(CANC. 334/24)
                $pattern_can = "/^(.*)\s*\(CANC.\s*(\d*)\/(\d*)\)/u";
                //(REF. REG.2024/510)
                $pattern_reg = "/^(.*)\s*\(REF.\s*REG.\s*(\d*)\/(\d*)\)/u";

                $coincide = preg_match($pattern_can, $content_linea_asunto, $matches);
                if ($coincide === 1) {
                    $origen_ref = $sigla_cancilleria;
                    $asunto = empty($matches[1]) ? '' : $matches[1];
                    $ref_num_prot = empty($matches[2]) ? '' : (int) $matches[2];
                    $ref_any_prot = empty($matches[3]) ? '' : any_2($matches[3]);
                    $origen_ref_prot = "$ref_num_prot/$ref_any_prot";
                }
                $coincide = preg_match($pattern_reg, $content_linea_asunto, $matches);
                if ($coincide === 1) {
                    $origen_ref = $sigla_cancilleria;
                    $asunto = empty($matches[1]) ? '' : $matches[1];
                    $ref_any_prot = empty($matches[2]) ? '' : any_2($matches[2]);
                    $ref_num_prot = empty($matches[3]) ? '' : (int) $matches[3];
                    $origen_ref_prot = "$ref_num_prot/$ref_any_prot";
                }

                if (!empty($origen_ref)) {
                    $a_ref[] = 'ref';
                    $a_referencias[] = $origen_ref;
                    $a_ref_prot[] = $origen_ref_prot;
                }
                $origen_ref = '';
                $content_linea_asunto = '';
            }

            /*
            De: Rectorado	 n. ref.: 2024/06915
            De: IESE Business School n. ref.: 2024/04136
            */
            $pattern = "/^\s*De:(.*)ref\.: (\d{4})\/(\d*)(\*|\s)*$/u";
            $coincide = preg_match($pattern, $line, $matches);
            if ($coincide === 1) {
                $origen = $sigla_uden;
                $any_prot = empty($matches[2]) ? '' : any_2($matches[2]);
                $num_prot = empty($matches[3]) ? '' : (int) $matches[3];
                $origen_prot = "$num_prot/$any_prot";
            }

            /*
             * Si el origen es Cancillería, sobreescribo.
            De: Cancillería Pamplona	 n. ref.: 2024/06504
            De: Cancillería Barcelona  n. ref.: 2024/05922
            */
            $pattern = "/^\s*De:\s*Canciller.a\s+\w+.*ref\.: (\d{4})\/(\d*)(\*|\s)*$/u";
            $coincide = preg_match($pattern, $line, $matches);
            if ($coincide === 1) {
                $detalle = "n. Reg. $matches[1]/$matches[2]";
                $es_origen_canilleria = TRUE;
            }

            if ($es_origen_canilleria) {
                /*
                 A: IESE Business School s. ref.: 2024/04365
                 */
                $pattern = "/^\s*A:(.*)ref.:\s*(\d*)\/(\d*)\s*$/u";
                $coincide = preg_match($pattern, $line, $matches);
                if ($coincide === 1) {
                    $origen_ref = $sigla_uden;
                    $ref_num_prot = empty($matches[3]) ? '' : (int)$matches[3];
                    $ref_any_prot = empty($matches[2]) ? '' : any_2($matches[2]);
                    $origen_ref_prot = "$ref_num_prot/$ref_any_prot";
                }

                if (!empty($origen_ref)) {
                    $a_ref[] = 'ref';
                    $a_referencias[] = $origen_ref;
                    $a_ref_prot[] = $origen_ref_prot;
                }
                $origen_ref = '';
            }

            /*
            Pamplona, 17 de julio de 2024
            */
            // Para obtener la fecha.
            // ciudad + fecha con mes en texto
            $pattern = '/^\s*(\P{N}*),?\s*(\d{1,2})\s+de\s+(\P{N}*)\s+de\s+(\d{2,4})\s*$/u';
            $coincide_fecha = preg_match($pattern, $line, $matches);
            if ($coincide_fecha === 1) {
                $ciudad = $matches[1];
                $dia = (int)trim($matches[2]);
                $mes_txt = strtolower(trim($matches[3]));
                $any = (int)trim($matches[4]);
                $a_Meses = DateTimeLocal::Meses();
                $mes_numero = array_search($mes_txt, $a_Meses);
                // probar con otro idioma
                if (empty($mes_numero)) {
                    $a_Meses = DateTimeLocal::Meses_cat();
                    $mes_numero = array_search($mes_txt, $a_Meses);
                }
                if (empty($mes_numero)) {
                    $a_Meses = DateTimeLocal::Meses_es();
                    $mes_numero = array_search($mes_txt, $a_Meses);
                }
                if (empty($mes_numero) || empty ($dia) || empty($any)) {
                    return $oFecha;
                }
                $oFecha = new DateTimeLocal("$any-$mes_numero-$dia");
            }
        }

        if ($es_origen_canilleria) { //cambiar orden...
            $origen = array_shift($a_referencias);
            $origen_prot = array_shift($a_ref_prot);
            array_shift($a_ref); // para quitar también el primer valor
        }

        $cabeceras['origen'] = $origen;
        $cabeceras['origen_prot'] = $origen_prot;
        $cabeceras['destino'] = ''; //$destino;
        $cabeceras['destino_prot'] = ''; //$destino_prot;
        $cabeceras['a_referencias'] = $a_referencias;
        $cabeceras['a_ref'] = $a_ref;
        $cabeceras['a_ref_prot'] = $a_ref_prot;
        $cabeceras['oFecha'] = $oFecha;
        $cabeceras['asunto'] = $asunto;
        $cabeceras['detalle'] = $detalle;

        return $cabeceras;
    }


    private function getOrigenDestino($a_txt)
    {
        $origen = '';
        $origen_prot = '';
        $oFecha = '';
        $destino = '';
        $destino_prot = '';
        $a_ref = [];
        $a_referencias = [];
        $a_ref_prot = [];

        $num_lineas = count($a_txt);
        $l = 0;
        $linea_protocolo = 0;
        foreach ($a_txt as $line) {
            $l++;
            $tramo_inicio = ($l - 5 < 0);
            $tramo_fin = ($num_lineas - $l < 2);
            if ((!$tramo_inicio && !$tramo_fin) || empty($line) || ctype_space($line)) {
                continue;
            }

            if ($tramo_inicio && $linea_protocolo === 0) {
                // agdmontagut 12/22      dlb 3/22
                $pattern = "/^\s*([^\*\p{N}]+)*((\*|\s)+\d+\/\d{2})*(\*|\s)+(((\*\P{N}\*)|\P{N})+)(\s+\d+\/\d{2})*(\*|\s)*$/u";
                $coincide = preg_match($pattern, $line, $matches);
                if ($coincide === 1) {
                    // quitar los '*' si tiene
                    $destino = trim($matches[1]);
                    $destino_prot = empty($matches[2]) ? '' : $matches[2];
                    $destino_prot = str_replace('*', '', $destino_prot);

                    $origen = trim($matches[5]);
                    $origen = str_replace('*', '', $origen);
                    $origen_prot = empty($matches[8]) ? '' : $matches[8];
                    $origen_prot = str_replace('*', '', $origen_prot);

                    // si tiene un guión, puede ser de una región (Gal-dlb)
                    if (strpos($destino, '-')) {
                        $a_sigla = explode('-', $destino);
                        $origen1 = $a_sigla[0];
                        $destino1 = $a_sigla[1];
                        if ($destino1 !== 'sr' && $destino1 !== 'sr' && $origen1 !== 'sm' && $origen1 !== 'sr') {
                            $coincide = 0;
                        }
                    }
                }
                if ($coincide !== 1) {
                    // Ceb-r 3/22
                    //$pattern = '/^(\s*(\P{N}+)(\s+\d+\/\d{2})*\s*-\s*([^\s\p{N}]+)(\s+\d+\/\d{2})*\s*)*\s*(\P{N}+)(\s+\d+\/\d{2})*\s*-\s*(\P{N}+)(\s+\d+\/\d{2})*\s*$/u';
                    $pattern = '/^(\s*(\P{N}+)\s*-\s*([^\s\p{N}]+)(\s+\d+\/\d{2})*\s*)*\s*(\P{N}+)\s*-\s*(\P{N}+)(\s+\d+\/\d{2})*(\*|\s)*$/u';
                    $coincide = preg_match($pattern, $line, $matches);
                    if ($coincide === 1) {
                        // quitar los '*' si tiene
                        $origen_ref = trim($matches[2]);
                        $origen_ref = str_replace('*', '', $origen_ref);
                        $origen_ref_prot = empty($matches[4]) ? '' : $matches[4];
                        $origen_ref_prot = str_replace('*', '', $origen_ref_prot);

                        $destino_ref = trim($matches[3]);

                        if (!empty($origen_ref)) {
                            $a_ref[] = 'ref';
                            $a_referencias[] = $origen_ref;
                            $a_ref_prot[] = $origen_ref_prot;
                        }
                        //-------------------

                        $origen = trim($matches[5]);
                        $origen = str_replace('*', '', $origen);
                        $origen_prot = empty($matches[7]) ? '' : $matches[7];
                        $origen_prot = str_replace('*', '', $origen_prot);

                        $destino = trim($matches[6]);
                        $destino = str_replace('*', '', $destino);
                        if ($destino === 'r') {
                            $destino = 'cr';
                        }
                        $destino_prot = '';
                    }
                }
                if ($coincide !== 1) {
                    //Cam-dlb 8/23
                    $pattern = '/^\s*(\P{N}+)-(\P{N}+)(\s+\d+\/\d{2})*(\*|\s)*$/u';
                    $coincide = preg_match($pattern, $line, $matches);
                    if ($coincide === 1) {
                        $origen = trim($matches[1]);
                        $origen_prot = empty($matches[3]) ? '' : $matches[3];
                        $destino = trim($matches[2]);
                        if ($destino === 'r') {
                            $destino = 'cr';
                        }
                        $destino_prot = '';
                    }
                }
                // si no coincide, pero no está vacío, también salto de linea para que coja las referencias
                if ($coincide === 1 || !empty($line)) {
                    $linea_protocolo = $l;
                }

            } elseif ($tramo_inicio) {
                // si no hay protocolo origen, y en la linea siguiente a la uqe se encuentra el protocolo
                // solamente hay un número sin nombre lugar, puede ser de la linea anterior
                if (empty($origen_prot) && ($l === $linea_protocolo + 1)) {
                    // asegurar que no tenga 'ref' o 'cfr'
                    $pos = mb_stripos($line, 'ref');
                    if (empty($pos)) {
                        $pos = mb_stripos($line, 'cfr');
                    }
                    if (empty($pos)) {
                        $pattern = '/^\s*(((\*\P{N}\*)|\P{N})*\s*)(\d+\/\d{2})(\*|\s)*$/i';
                        $coincide = preg_match($pattern, $line, $matches);
                        if ($coincide === 1) {
                            // quitar los '*' si tiene
                            // Si en la segunda linea hay algo, lo que ha tomado de la primera línea como
                            //
                            // origen podría estar en la primera linea
                            if (empty($origen)) {
                                $origen = trim($matches[1]);
                                $origen = str_replace('*', '', $origen);
                            }
                            $origen_prot = empty($matches[4]) ? '' : $matches[4];
                            $origen_prot = str_replace('*', '', $origen_prot);
                        }
                    }
                }
                // ref
                $pattern = '/(ref\.?)\s+(\P{N}+)(\s+\d+\/\d{2})$/ui';
                $coincide = preg_match($pattern, $line, $matches);
                if ($coincide === 1) {
                    // si tiene un guión, puede ser de una región (Gal-dlb)
                    if (strpos($matches[2], '-')) {
                        $a_sigla = explode('-', $matches[2]);
                        $origen1 = $a_sigla[0];
                        $destino1 = $a_sigla[1];
                        if ($destino1 !== 'sr' && $destino1 !== 'sr' && $origen1 !== 'sm' && $origen1 !== 'sr') {
                            $coincide = 0;
                        }
                    }
                    // Sigue igual
                    if ($coincide === 1) {
                        $a_ref[] = $matches[1];
                        // quitar los '*' si tiene
                        $a_referencias[] = str_replace('*', '', $matches[2]);
                        $a_ref_prot[] = $matches[3];
                    }
                }
                if ($coincide !== 1) {
                    $pattern = '/(ref\.?)\s+(\P{N}+)-(\P{N}+)(\s+\d+\/\d{2})*\s*(ref\.?)\s+(\P{N}+)-(\P{N}+)(\s+\d+\/\d{2})*(\*|\s)*$/ui';
                    $coincide = preg_match($pattern, $line, $matches);
                    if ($coincide === 1) {
                        $a_ref[] = $matches[5];
                        $a_referencias[] = $matches[6];
                        $a_ref_prot[] = $matches[8];

                        $a_ref[] = $matches[1];
                        $a_referencias[] = $matches[2];
                        $a_ref_prot[] = $matches[4];
                    }
                }
                // cfr
                if ($coincide !== 1) {
                    $pattern = '/(cfr\.?)\s+(\P{N}+)(\s+\d+\/\d{2})$/ui';
                    $coincide = preg_match($pattern, $line, $matches);
                    if ($coincide === 1) {
                        // si tiene un guión, puede ser de una región (Gal-dlb)
                        if (strpos($matches[2], '-')) {
                            $a_sigla = explode('-', $destino);
                            $origen1 = $a_sigla[0];
                            $destino1 = $a_sigla[1];
                            if ($destino1 !== 'sr' && $destino1 !== 'sr' && $origen1 !== 'sm' && $origen1 !== 'sr') {
                                $coincide = 0;
                            }
                        }
                        // Sigue igual
                        if ($coincide === 1) {
                            $a_ref[] = $matches[1];
                            $a_referencias[] = $matches[2];
                            $a_ref_prot[] = $matches[3];
                        }
                    }
                }

            }

            // Para obtener la fecha.
            if ($tramo_fin) {
                $oFecha = $this->extractFecha($line);
            }
        }

        $cabeceras['origen'] = $origen;
        $cabeceras['origen_prot'] = $origen_prot;
        $cabeceras['destino'] = $destino;
        $cabeceras['destino_prot'] = $destino_prot;
        $cabeceras['a_referencias'] = $a_referencias;
        $cabeceras['a_ref'] = $a_ref;
        $cabeceras['a_ref_prot'] = $a_ref_prot;
        $cabeceras['oFecha'] = $oFecha;
        $cabeceras['asunto'] = '';
        $cabeceras['detalle'] = '';

        return $cabeceras;
    }


    /**
     * @param string $filtro
     */
    public function setFiltro(string $filtro): void
    {
        $this->filtro = $filtro;
    }
}
