<?php

namespace entradas\model;


use core\ConfigGlobal;
use entradas\model\entity\EntradaDocDB;
use lugares\model\entity\GestorLugar;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;
use usuarios\model\Categoria;
use web\DateTimeLocal;
use web\Protocolo;

require_once(ConfigGlobal::$dir_libs . '/vendor/autoload.php');

class EntradaProvisionalFromPdf
{

    private string $content_pdf;
    private string $page_frist;
    private string $page_last;
    private int $num_pages;

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
    }

    public function crear_entrada_provisional(string $asunto)
    {
        $this->leer_pdf();
        $text = $this->page_frist;

        $origen = '';
        $oFecha = '';
        $destino = '';
        $a_ref = [];
        $a_referencias = [];
        $a_ref_prot = [];

        $a_txt = explode("\n", $text);
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
                $pattern = "/^\s*([^\*\p{N}]+)*((\*|\s)+\d+\/\d{2})*(\*|\s)+(((\*\P{N}\*)|\P{N})+)(\s+\d+\/\d{2})*\s*$/u";
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
                    $pattern = '/^(\s*(\P{N}+)\s*-\s*([^\s\p{N}]+)(\s+\d+\/\d{2})*\s*)*\s*(\P{N}+)\s*-\s*(\P{N}+)(\s+\d+\/\d{2})*\s*$/u';
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
                    $pattern = '/^\s*(\P{N}+)-(\P{N}+)(\s+\d+\/\d{2})*\s*$/u';
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
                        $pattern = '/^\s*(((\*\P{N}\*)|\P{N})*\s*)(\d+\/\d{2})\s*$/i';
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
                    $pattern = '/(ref\.?)\s+(\P{N}+)-(\P{N}+)(\s+\d+\/\d{2})*\s*(ref\.?)\s+(\P{N}+)-(\P{N}+)(\s+\d+\/\d{2})*\s*$/ui';
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

        if ($this->num_pages > 0) {
            $text = $this->page_last;
            $a_txt = explode("\n", $text);
            $num_lineas = count($a_txt);
            $l = 0;
            foreach ($a_txt as $line) {
                $l++;
                $tramo_inicio = ($l - 5 < 0);
                $tramo_fin = ($num_lineas - $l < 2);
                if (empty($line) && !$tramo_inicio && !$tramo_fin) {
                    continue;
                }
                // Para obtener la fecha.
                if ($tramo_fin) {
                    $oFecha = $this->extractFecha($line);
                }
            }
        }


        $oEntrada = new Entrada();
        $oEntrada->setAsunto_entrada($asunto);
        $oEntrada->setModo_entrada(Entrada::MODO_PROVISIONAL);
        // Por el momento solamente etherpad
        $oEntrada->setTipo_documento(EntradaDocDB::TIPO_ETHERPAD);

        if (!empty($origen)) {
            // al buscar sin acentos se usan las expresiones regulares.
            // Para los centros tipo: sss+Barcelona, hay que escapar:
            $origen = str_replace('+', '\+', $origen);
            $gesLugares = new GestorLugar();
            $cLugares = $gesLugares->getLugares(['sigla' => "^$origen\$"], ['sigla' => 'sin_acentos']);
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

    /**
     * @param string $filtro
     */
    public function setFiltro(string $filtro): void
    {
        $this->filtro = $filtro;
    }
}
