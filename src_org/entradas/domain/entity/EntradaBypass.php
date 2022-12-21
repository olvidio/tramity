<?php

namespace entradas\domain\entity;

use lugares\domain\repositories\GrupoRepository;
use lugares\domain\repositories\LugarRepository;
use stdClass;
use web\DateTimeLocal;
use web\NullDateTimeLocal;
use function core\is_true;

/**
 * Clase que implementa la entidad entradas_bypass
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 12/12/2022
 */
class EntradaBypass extends Entrada
{

    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * Id_item de EntradaBypass
     *
     * @var int
     */
    private int $iid_item;
    /**
     * Id_entrada de EntradaBypass
     *
     * @var int
     */
    protected int $iid_entrada;
    /**
     * Descripción de EntradaBypass
     *
     * @var string
     */
    private string $sdescripcion;
    /**
     * Json_prot_destino de EntradaBypass
     *
     * @var array|stdClass|null
     */
    private array|stdClass|null $json_prot_destino = null;
    /**
     * Id_grupos de EntradaBypass
     *
     * @var array|null
     */
    private array|null $a_id_grupos = null;
    /**
     * Destinos de EntradaBypass
     *
     * @var array|null
     */
    private array|null $a_destinos = null;
    /**
     * F_salida de EntradaBypass
     *
     * @var DateTimeLocal|null
     */
    private DateTimeLocal|null $df_salida = null;

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    public function getDestinosByPass(): array
    {
        $a_grupos = $this->getId_grupos();

        $aMiembros = [];
        $destinos_txt = '';

        if (!empty($a_grupos)) {
            $destinos_txt = $this->getDescripcion();
            //(según los grupos seleccionados)
            $a_miembros_g = [];
            $GrupoRepository = new GrupoRepository();
            foreach ($a_grupos as $id_grupo) {
                $oGrupo = $GrupoRepository->findById($id_grupo);
                if ($oGrupo !== null) {
                    $a_miembros_g[] = $oGrupo->getMiembros();
                }
                //$aMiembros = array_merge($aMiembros, $a_miembros_g);
            }
            $aMiembros = array_merge([], ...$a_miembros_g);
            $aMiembros = array_unique($aMiembros);
            /* TODO
            $this->setDestinos($aMiembros);
            if ($this->DBGuardar() === FALSE) {
                $error_txt = $this->getErrorTxt();
                exit ($error_txt);
            }
            */
        } else {
            //(según individuales)
            $LugarRepository = new LugarRepository();
            $a_json_prot_dst = $this->getJson_prot_destino();
            foreach ($a_json_prot_dst as $json_prot_dst) {
                $aMiembros[] = $json_prot_dst->id_lugar;
                $oLugar = $LugarRepository->findById($json_prot_dst->id_lugar);
                if ($oLugar === null) {
                    $destinos_txt .= _("No encuentro el Lugar");
                } else {
                    $destinos_txt .= empty($destinos_txt) ? '' : ', ';
                    $destinos_txt .= $oLugar->getNombre();
                }
            }
        }

        return ['miembros' => $aMiembros, 'txt' => $destinos_txt];
    }

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDatos
     * @return EntradaBypass
     */
    public function setAllAttributes(array $aDatos): EntradaBypass
    {
        if (array_key_exists('id_item', $aDatos)) {
            $this->setId_item($aDatos['id_item']);
        }
        if (array_key_exists('id_entrada', $aDatos)) {
            $this->setId_entrada($aDatos['id_entrada']);
        }
        if (array_key_exists('descripcion', $aDatos)) {
            $this->setDescripcion($aDatos['descripcion']);
        }
        if (array_key_exists('json_prot_destino', $aDatos)) {
            $this->setJson_prot_destino($aDatos['json_prot_destino']);
        }
        if (array_key_exists('id_grupos', $aDatos)) {
            $this->setId_grupos($aDatos['id_grupos']);
        }
        if (array_key_exists('destinos', $aDatos)) {
            $this->setDestinos($aDatos['destinos']);
        }
        if (array_key_exists('f_salida', $aDatos)) {
            $this->setF_salida($aDatos['f_salida']);
        }
        // Añado los de entradas
        if (array_key_exists('modo_entrada', $aDatos)) {
            $this->setModo_entrada($aDatos['modo_entrada']);
        }
        if (array_key_exists('json_prot_origen', $aDatos)) {
            $this->setJson_prot_origen($aDatos['json_prot_origen']);
        }
        if (array_key_exists('asunto_entrada', $aDatos)) {
            $this->setAsunto_entrada($aDatos['asunto_entrada']);
        }
        if (array_key_exists('json_prot_ref', $aDatos)) {
            $this->setJson_prot_ref($aDatos['json_prot_ref']);
        }
        if (array_key_exists('ponente', $aDatos)) {
            $this->setPonente($aDatos['ponente']);
        }
        if (array_key_exists('resto_oficinas', $aDatos)) {
            $this->setResto_oficinas($aDatos['resto_oficinas']);
        }
        if (array_key_exists('asunto', $aDatos)) {
            $this->setAsunto($aDatos['asunto']);
        }
        if (array_key_exists('f_entrada', $aDatos)) {
            $this->setF_entrada($aDatos['f_entrada']);
        }
        if (array_key_exists('detalle', $aDatos)) {
            $this->setDetalle($aDatos['detalle']);
        }
        if (array_key_exists('categoria', $aDatos)) {
            $this->setCategoria($aDatos['categoria']);
        }
        if (array_key_exists('visibilidad', $aDatos)) {
            $this->setVisibilidad($aDatos['visibilidad']);
        }
        if (array_key_exists('f_contestar', $aDatos)) {
            $this->setF_contestar($aDatos['f_contestar']);
        }
        if (array_key_exists('bypass', $aDatos)) {
            $this->setBypass(is_true($aDatos['bypass']));
        }
        if (array_key_exists('estado', $aDatos)) {
            $this->setEstado($aDatos['estado']);
        }
        if (array_key_exists('anulado', $aDatos)) {
            $this->setAnulado($aDatos['anulado']);
        }
        if (array_key_exists('encargado', $aDatos)) {
            $this->setEncargado($aDatos['encargado']);
        }
        if (array_key_exists('json_visto', $aDatos)) {
            $this->setJson_visto($aDatos['json_visto']);
        }
        if (array_key_exists('id_entrada_compartida', $aDatos)) {
            $this->setId_entrada_compartida($aDatos['id_entrada_compartida']);
        }
        return $this;
    }

    /**
     *
     * @return int $iid_item
     */
    public function getId_item(): int
    {
        return $this->iid_item;
    }

    /**
     *
     * @param int $iid_item
     */
    public function setId_item(int $iid_item): void
    {
        $this->iid_item = $iid_item;
    }

    /**
     *
     * @return int $iid_entrada
     */
    public function getId_entrada(): int
    {
        return $this->iid_entrada;
    }

    /**
     *
     * @param int $iid_entrada
     */
    public function setId_entrada(int $iid_entrada): void
    {
        $this->iid_entrada = $iid_entrada;
    }

    /**
     *
     * @return string $sdescripcion
     */
    public function getDescripcion(): string
    {
        return $this->sdescripcion;
    }

    /**
     *
     * @param string $sdescripcion
     */
    public function setDescripcion(string $sdescripcion): void
    {
        $this->sdescripcion = $sdescripcion;
    }

    /**
     *
     * @return array|stdClass|null $json_prot_destino
     */
    public function getJson_prot_destino(): array|stdClass|null
    {
        return $this->json_prot_destino;
    }

    /**
     *
     * @param stdClass|array|null $json_prot_destino
     */
    public function setJson_prot_destino(stdClass|array|null $json_prot_destino = null): void
    {
        $this->json_prot_destino = $json_prot_destino;
    }

    /**
     *
     * @return array|null $a_id_grupos
     */
    public function getId_grupos(): array|null
    {
        return $this->a_id_grupos;
    }

    /**
     *
     * @param array|null $a_id_grupos
     */
    public function setId_grupos(array $a_id_grupos = null): void
    {
        $this->a_id_grupos = $a_id_grupos;
    }

    /**
     *
     * @return array|null $a_destinos
     */
    public function getDestinos(): array|null
    {
        return $this->a_destinos;
    }

    /**
     *
     * @param array|null $a_destinos
     */
    public function setDestinos(array $a_destinos = null): void
    {
        $this->a_destinos = $a_destinos;
    }

    /**
     *
     * @return DateTimeLocal|NullDateTimeLocal|null $df_salida
     */
    public function getF_salida(): DateTimeLocal|NullDateTimeLocal|null
    {
        return $this->df_salida ?? new NullDateTimeLocal;
    }

    /**
     *
     * @param DateTimeLocal|null $df_salida
     */
    public function setF_salida(DateTimeLocal|null $df_salida = null): void
    {
        $this->df_salida = $df_salida;
    }
}