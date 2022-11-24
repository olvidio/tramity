<?php

namespace entradas\model;

use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\entity\GestorEntradaBypass;
use usuarios\model\Categoria;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorOficina;
use usuarios\model\PermRegistro;
use usuarios\model\Visibilidad;
use web\Hash;
use web\Protocolo;
use web\ProtocoloArray;


class EntradaLista
{
    /**
     *
     * @var string
     */
    private string $filtro;
    /**
     *
     * @var integer
     */
    private int $id_entrada;
    /**
     *
     * @var array
     */
    private array $aWhere = [];
    /**
     *
     * @var array
     */
    private array $aOperador = [];
    /**
     *
     * @var array
     */
    private array $aWhereADD = [];
    /**
     *
     * @var array
     */
    private array $aOperadorADD = [];

    /*
     * filtros posibles: 
    'en_ingresado':
    'en_admitido':
    'en_asignado':
    'en_aceptado':
    'bypass'
    'permanentes'
    'avisos'
    'pendientes'
    */

    public function mostrarTabla(): void
    {
        $this->setCondicion();
        $pagina_nueva = '';
        $filtro = $this->getFiltro();

        $oCategoria = new Categoria();
        $a_categorias = $oCategoria->getArrayCategoria();
        $oVisibilidad = new Visibilidad();
        $a_visibilidad = $oVisibilidad->getArrayVisibilidad();

        $gesOficinas = new GestorOficina();
        $a_posibles_oficinas = $gesOficinas->getArrayOficinas();


        $ver_oficina = TRUE;
        $pagina_accion = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_accion.php';
        switch ($filtro) {
            case 'en_encargado':
                $encargado = $this->aWhereADD['encargado'];
                $pagina_mod = ConfigGlobal::getWeb() . '/apps/entradas/controller/entrada_ver.php';
                $pagina_nueva = '';
                break;
            case 'en_aceptado':
                $oficina = $this->aWhereADD['ponente'];
                //$pagina_accion =  ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_accion.php';
                $pagina_accion = ConfigGlobal::getWeb() . '/apps/expedientes/controller/expediente_accion.php';
                if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
                    $pagina_mod = ConfigGlobal::getWeb() . '/apps/entradas/controller/entrada_form_ctr.php';
                    $ver_oficina = FALSE;
                } else {
                    $pagina_mod = ConfigGlobal::getWeb() . '/apps/entradas/controller/entrada_ver.php';
                    $ver_oficina = TRUE;
                }
                $pagina_nueva = '';
                break;
            case 'en_ingresado':
                $ver_oficina = FALSE;
                $pagina_mod = ConfigGlobal::getWeb() . '/apps/entradas/controller/entrada_form.php';
                $pagina_nueva = Hash::link('apps/entradas/controller/entrada_form.php?' . http_build_query(['filtro' => $filtro]));
                if (ConfigGlobal::role_actual() === 'vcd') {
                    $aQuery = ['filtro' => $filtro, 'slide_mode' => 't'];
                    $pagina_nueva = Hash::link('apps/entradas/controller/entrada_lista.php?' . http_build_query($aQuery));
                }
                break;
            case 'en_admitido':
            case 'en_asignado':
            case 'entrada':
                $pagina_mod = ConfigGlobal::getWeb() . '/apps/entradas/controller/entrada_form.php';
                $pagina_nueva = Hash::link('apps/entradas/controller/entrada_form.php?' . http_build_query(['filtro' => $filtro]));
                break;
            case 'bypass':
                $pagina_mod = ConfigGlobal::getWeb() . '/apps/entradas/controller/entrada_bypass.php';
                break;
            default:
                $pagina_mod = ConfigGlobal::getWeb() . '/apps/entradas/controller/entrada_ver.php';
                $pagina_nueva = Hash::link('apps/entradas/controller/entrada_form.php?' . http_build_query(['filtro' => $filtro]));
        }

        $oProtOrigen = new Protocolo();
        $a_entradas = [];
        $id_entrada = '';
        if (!empty($this->aWhere)) {
            $oPermRegistro = new PermRegistro();
            $gesEntradas = new GestorEntrada();
            $cEntradas = $gesEntradas->getEntradas($this->aWhere, $this->aOperador);
            foreach ($cEntradas as $oEntrada) {
                $row = [];
                // mirar permisos...
                $visibilidad = $oEntrada->getVisibilidad();
                $visibilidad_txt = empty($a_visibilidad[$visibilidad]) ? '?' : $a_visibilidad[$visibilidad];

                $perm_ver_escrito = $oPermRegistro->permiso_detalle($oEntrada, 'escrito');
                $id_entrada = $oEntrada->getId_entrada();
                $row['id_entrada'] = $id_entrada;

                $a_cosas = ['id_entrada' => $id_entrada,
                    'filtro' => $filtro,
                    'slide_mode' => $this->slide_mode,
                ];
                if ($filtro === 'en_aceptado') {
                    $a_cosas['oficina'] = $oficina;
                }
                if ($filtro === 'en_encargado') {
                    $a_cosas['encargado'] = $encargado;
                }

                $id_entrada_compartida = $oEntrada->getId_entrada_compartida();
                if (!empty($id_entrada_compartida)) {
                    $compartida = 'true';
                } else {
                    $id_entrada_compartida = $id_entrada;
                    $compartida = 'false';
                }
                $link_accion = Hash::link($pagina_accion . '?' . http_build_query($a_cosas));
                $link_mod = Hash::link($pagina_mod . '?' . http_build_query($a_cosas));
                if ($perm_ver_escrito >= PermRegistro::PERM_VER) {
                    $row['link_ver'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_ver_entrada('$id_entrada_compartida',$compartida);\" >" . _("ver") . "</span>";
                    $row['link_accion'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_accion');\" >" . _("acción") . "</span>";
                }
                $row['link_mod'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_mod');\" >mod</span>";

                $oProtOrigen->setJson($oEntrada->getJson_prot_origen());
                $row['protocolo'] = $oProtOrigen->ver_txt();

                $json_ref = $oEntrada->getJson_prot_ref();
                $oArrayProtRef = new ProtocoloArray($json_ref, '', '');
                $oArrayProtRef->setRef(TRUE);
                $row['referencias'] = $oArrayProtRef->ListaTxtBr();

                $id_categoria = $oEntrada->getCategoria();
                $row['categoria'] = empty($a_categorias[$id_categoria]) ? '?' : $a_categorias[$id_categoria];
                $row['asunto'] = $oEntrada->getAsuntoDetalle();

                $id_of_ponente = $oEntrada->getPonente();
                $a_resto_oficinas = $oEntrada->getResto_oficinas();
                $of_ponente_txt = empty($a_posibles_oficinas[$id_of_ponente]) ? '?' : $a_posibles_oficinas[$id_of_ponente];
                $oficinas_txt = '';
                $oficinas_txt .= '<span class="text-danger">' . $of_ponente_txt . '</span>';
                foreach ($a_resto_oficinas as $id_oficina) {
                    $oficinas_txt .= empty($oficinas_txt) ? '' : ', ';
                    $oficinas_txt .= empty($a_posibles_oficinas[$id_oficina]) ? '?' : $a_posibles_oficinas[$id_oficina];
                }
                $row['oficinas'] = $oficinas_txt;

                $row['f_entrada'] = $oEntrada->getF_entrada()->getFromLocal();
                $row['f_contestar'] = $oEntrada->getF_contestar()->getFromLocal();

                // mirar si tienen escrito
                $row['f_escrito'] = $oEntrada->getF_documento()->getFromLocal();
                $row['visibilidad'] = $visibilidad_txt;
                // para ordenar. Si no añado id_entrada, sobre escribe.
                $f_entrada_iso = $oEntrada->getF_entrada()->getIso() . $id_entrada;
                $a_entradas[$f_entrada_iso] = $row;
            }
        }
        // ordenar por f_entrada_iso:
        krsort($a_entradas, SORT_STRING);

        $url_update = 'apps/entradas/controller/entrada_update.php';
        $server = ConfigGlobal::getWeb(); //http://tramity.local

        $a_cosas = ['filtro' => $filtro,
            'slide_mode' => $this->slide_mode,
        ];
        if ($filtro === 'en_aceptado') {
            $a_cosas['oficina'] = $oficina;
        }
        if ($filtro === 'en_encargado') {
            $a_cosas['encargado'] = $encargado;
        }
        $pagina_cancel = Hash::link('apps/entradas/controller/entrada_lista.php?' . http_build_query($a_cosas));

        $txt_btn_new = '';
        $btn_new = FALSE;
        $txt_btn_dock = '';
        $btn_dock = FALSE;
        $secretaria = FALSE;
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR && $this->filtro === 'en_aceptado') {
            $btn_dock = TRUE;
            if (ConfigGlobal::soy_dtor()) {
                $secretaria = TRUE;
            }
        }

        if (ConfigGlobal::role_actual() === 'secretaria') {
            $btn_dock = TRUE;
            $secretaria = TRUE;
            $btn_new = TRUE;
            $txt_btn_new = _("nueva entrada");
        }
        if (ConfigGlobal::role_actual() === 'vcd') {
            $btn_new = TRUE;
            $txt_btn_new = _("procesar");
            $btn_dock = TRUE;
        }
        if ($this->filtro === 'bypass') {
            $btn_new = FALSE;
        }

        $ver_accion = FALSE;
        if ($this->filtro === 'en_aceptado' || $this->filtro === 'en_encargado') {
            $ver_accion = TRUE;
        }

        $vista = ConfigGlobal::getVista();


        $txt_btn_dock = _("revisar dock");
        $pagina_cargar_dock = Hash::link('apps/entradas/controller/entrada_dock.php?' . http_build_query(['filtro' => $filtro]));


        $a_campos = [
            //'id_entrada' => $id_entrada,
            //'oHash' => $oHash,
            'a_entradas' => $a_entradas,
            'url_update' => $url_update,
            'pagina_nueva' => $pagina_nueva,
            'filtro' => $filtro,
            'server' => $server,
            'secretaria' => $secretaria,
            'btn_new' => $btn_new,
            'txt_btn_new' => $txt_btn_new,
            'pagina_cancel' => $pagina_cancel,
            'ver_accion' => $ver_accion,
            'ver_oficina' => $ver_oficina,
            //tabs_show
            'vista' => $vista,
            // as4
            'btn_dock' => $btn_dock,
            'txt_btn_dock' => $txt_btn_dock,
            'pagina_cargar_dock' => $pagina_cargar_dock,
        ];

        $oView = new ViewTwig('entradas/controller');
        if ($this->slide_mode === 't') {
            // poner la primera id_entrada, sino empieza por el final:
            $row = reset($a_entradas);
            $id_entrada = empty($row['id_entrada']) ? '' : $row['id_entrada'];
            include('apps/entradas/controller/entrada_ver_slide.php');
        } else {
            $oView->renderizar('entrada_lista.html.twig', $a_campos);
        }
    }

    /**
     *
     */
    private function setCondicion()
    {
        $aWhere = [];
        $aOperador = [];

        $aWhere['_ordre'] = 'f_entrada DESC';
        switch ($this->filtro) {
            case 'en_ingresado':
                $aWhere['estado'] = Entrada::ESTADO_INGRESADO;
                break;
            case 'en_admitido':
                $aWhere['estado'] = Entrada::ESTADO_ADMITIDO;
                break;
            case 'en_asignado':
                $aWhere['estado'] = Entrada::ESTADO_ASIGNADO;
                break;
            case 'en_encargado':
                $encargado = ConfigGlobal::role_id_cargo(); // valor por defecto
                if (!empty($this->aWhereADD['encargado'])) {
                    $encargado = $this->aWhereADD['encargado'];
                }
                $aWhere['estado'] = Entrada::ESTADO_ACEPTADO;
                $aWhere['encargado'] = $encargado;

                $oVisibilidad = new Visibilidad();
                $a_visibilidad = $oVisibilidad->getArrayCondVisibilidad();
                // No marcado como visto:
                $gesEntradas = new GestorEntrada();
                $cEntradas = $gesEntradas->getEntradasNoVistoDB($encargado, 'encargado', $a_visibilidad);
                $a_entradas_encargado = [];
                foreach ($cEntradas as $oEntrada) {
                    $id_entrada = $oEntrada->getId_entrada();
                    $a_entradas_encargado[] = $id_entrada;
                }
                if (!empty($a_entradas_encargado)) {
                    $aWhere['id_entrada'] = implode(',', $a_entradas_encargado);
                    $aOperador['id_entrada'] = 'IN';
                } else {
                    $aWhere['id_entrada'] = 1;
                }
                break;
            case 'en_aceptado':
                $oficina = 'propia'; // valor por defecto
                if (!empty($this->aWhereADD['ponente'])) {
                    $oficina = $this->aWhereADD['ponente'];
                }

                $a_entradas_ponente = [];
                if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
                    // visibilidad:
                    $oVisibilidad = new Visibilidad();
                    $a_visibilidad = $oVisibilidad->getArrayCondVisibilidad();

                    // No marcado como visto:
                    $gesEntradas = new GestorEntrada();
                    $cEntradas = $gesEntradas->getEntradasNoVistoDB('', 'centro', $a_visibilidad);
                    $a_entradas_ponente = [];
                    foreach ($cEntradas as $oEntrada) {
                        $id_entrada = $oEntrada->getId_entrada();
                        $a_entradas_ponente[] = $id_entrada;
                    }

                } else {
                    if ($oficina === 'propia') {
                        $id_oficina = ConfigGlobal::role_id_oficina();

                        // No marcado como visto:
                        $gesEntradas = new GestorEntrada();
                        $cEntradas = $gesEntradas->getEntradasNoVistoDB($id_oficina, 'ponente');
                        $a_entradas_ponente = [];
                        foreach ($cEntradas as $oEntrada) {
                            $id_entrada = $oEntrada->getId_entrada();
                            $a_entradas_ponente[] = $id_entrada;
                        }
                    }
                }

                //////// las oficina implicadas //////////////////////////////
                $a_entradas_resto = [];
                if ($oficina === 'resto') {
                    $id_oficina_role = ConfigGlobal::role_id_oficina();
                    if (!empty($id_oficina_role)) {
                        $id_oficina = ConfigGlobal::role_id_oficina();
                        $gesEntradas = new GestorEntrada();
                        $cEntradas = $gesEntradas->getEntradasNoVistoDB($id_oficina, 'resto');
                        foreach ($cEntradas as $oEntrada) {
                            $id_entrada = $oEntrada->getId_entrada();
                            $a_entradas_resto[] = $id_entrada;
                        }
                    }
                }

                // sumar los dos: nuevos + aclaraciones.
                $a_entradas_suma = array_merge($a_entradas_ponente, $a_entradas_resto);
                $aWhere = [];
                $aOperador = [];
                if (!empty($a_entradas_suma)) {
                    $aWhere['id_entrada'] = implode(',', $a_entradas_suma);
                    $aOperador['id_entrada'] = 'IN';
                }
                break;
            case 'bypass':
                // distribución cr
                $aWhere['bypass'] = 't';
                $aWhere['estado'] = Entrada::ESTADO_ACEPTADO;
                // que no estén enviados
                $aWhereBypass = ['f_salida' => 'x'];
                $aOperadorBypass = ['f_salida' => 'IS NULL'];
                $gesEntradaBypass = new GestorEntradaBypass();
                $cEntradasBypass = $gesEntradaBypass->getEntradasBypass($aWhereBypass, $aOperadorBypass);
                $a_bypass = [];
                foreach ($cEntradasBypass as $oEntradaBypass) {
                    $a_bypass[] = $oEntradaBypass->getId_entrada();
                }
                if (!empty($a_bypass)) {
                    $aWhere['id_entrada'] = implode(',', $a_bypass);
                    $aOperador['id_entrada'] = 'IN';
                } else {
                    // para que no salga nada pongo
                    $aWhere = [];
                }
                break;
            default:
                exit (_("No ha escogido ningún filtro"));
        }

        $this->aWhere = $aWhere;
        $this->aOperador = $aOperador;
    }

    /**
     * @return integer
     */
    public function getId_entrada(): int
    {
        return $this->id_entrada;
    }

    /**
     * @param integer $id_entrada
     */
    public function setId_entrada(int $id_entrada): void
    {
        $this->id_entrada = $id_entrada;
    }

    /**
     * @return string
     */
    public function getFiltro(): string
    {
        return $this->filtro;
    }

    /**
     * @param string $filtro
     */
    public function setFiltro(string $filtro): void
    {
        $this->filtro = $filtro;
    }

    public function getNumero(): int|string
    {
        $this->setCondicion();
        if (!empty($this->aWhere)) {
            $gesEntradas = new GestorEntrada();
            $cEntradas = $gesEntradas->getEntradas($this->aWhere, $this->aOperador);
            $num = count($cEntradas);
        } else {
            $num = '';
        }
        return $num;
    }

    /**
     * @param string $slide_mode
     */
    public function setSlide_mode($slide_mode)
    {
        $this->slide_mode = $slide_mode;
    }

    /**
     * @return array
     */
    public function getAWhereADD(): array
    {
        return $this->aWhereADD;
    }

    /**
     * @param array $aWhereADD
     */
    public function setAWhereADD(array $aWhereADD): void
    {
        $this->aWhereADD = $aWhereADD;
    }

    /**
     * @return array
     */
    public function getAOperadorADD(): array
    {
        return $this->aOperadorADD;
    }

    /**
     * @param array $aOperadorADD
     */
    public function setAOperadorADD(array $aOperadorADD): void
    {
        $this->aOperadorADD = $aOperadorADD;
    }

}