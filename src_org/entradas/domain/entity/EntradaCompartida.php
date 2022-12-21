<?php

namespace entradas\domain\entity;
	use web\DateTimeLocal;
	use web\NullDateTimeLocal;
	use stdClass;
/**
 * Clase que implementa la entidad entradas_compartidas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class EntradaCompartida {

	/* ATRIBUTOS ----------------------------------------------------------------- */

	/**
	 * Id_entrada_compartida de EntradaCompartida
	 *
	 * @var int
	 */
	 private int $iid_entrada_compartida;
	/**
	 * Descripcion de EntradaCompartida
	 *
	 * @var string
	 */
	 private string $sdescripcion;
	/**
	 * Json_prot_destino de EntradaCompartida
	 *
	 * @var array|stdClass|null
	 */
	 private array|stdClass|null $json_prot_destino = null;
	/**
	 * Destinos de EntradaCompartida
	 *
	 * @var array|null
	 */
	 private array|null $a_destinos = null;
	/**
	 * F_documento de EntradaCompartida
	 *
	 * @var DateTimeLocal|null
	 */
	 private DateTimeLocal|null $df_documento = null;
	/**
	 * Json_prot_origen de EntradaCompartida
	 *
	 * @var array|stdClass|null
	 */
	 private array|stdClass|null $json_prot_origen = null;
	/**
	 * Json_prot_ref de EntradaCompartida
	 *
	 * @var array|stdClass|null
	 */
	 private array|stdClass|null $json_prot_ref = null;
	/**
	 * Categoria de EntradaCompartida
	 *
	 * @var int|null
	 */
	 private int|null $icategoria = null;
	/**
	 * Asunto_entrada de EntradaCompartida
	 *
	 * @var string
	 */
	 private string $sasunto_entrada;
	/**
	 * F_entrada de EntradaCompartida
	 *
	 * @var DateTimeLocal|null
	 */
	 private DateTimeLocal|null $df_entrada = null;
	/**
	 * Anulado de EntradaCompartida
	 *
	 * @var string|null
	 */
	 private string|null $sanulado = null;

	/* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

	/**
	 * Establece el valor de todos los atributos
	 *
	 * @param array $aDatos
	 * @return EntradaCompartida
	 */
	public function setAllAttributes(array $aDatos): EntradaCompartida
	{
		if (array_key_exists('id_entrada_compartida',$aDatos))
		{
			$this->setId_entrada_compartida($aDatos['id_entrada_compartida']);
		}
		if (array_key_exists('descripcion',$aDatos))
		{
			$this->setDescripcion($aDatos['descripcion']);
		}
		if (array_key_exists('json_prot_destino',$aDatos))
		{
			$this->setJson_prot_destino($aDatos['json_prot_destino']);
		}
		if (array_key_exists('destinos',$aDatos))
		{
			$this->setDestinos($aDatos['destinos']);
		}
		if (array_key_exists('f_documento',$aDatos))
		{
			$this->setF_documento($aDatos['f_documento']);
		}
		if (array_key_exists('json_prot_origen',$aDatos))
		{
			$this->setJson_prot_origen($aDatos['json_prot_origen']);
		}
		if (array_key_exists('json_prot_ref',$aDatos))
		{
			$this->setJson_prot_ref($aDatos['json_prot_ref']);
		}
		if (array_key_exists('categoria',$aDatos))
		{
			$this->setCategoria($aDatos['categoria']);
		}
		if (array_key_exists('asunto_entrada',$aDatos))
		{
			$this->setAsunto_entrada($aDatos['asunto_entrada']);
		}
		if (array_key_exists('f_entrada',$aDatos))
		{
			$this->setF_entrada($aDatos['f_entrada']);
		}
		if (array_key_exists('anulado',$aDatos))
		{
			$this->setAnulado($aDatos['anulado']);
		}
		return $this;
	}
	/**
	 *
	 * @return int $iid_entrada_compartida
	 */
	public function getId_entrada_compartida(): int
	{
		return $this->iid_entrada_compartida;
	}
	/**
	 *
	 * @param int $iid_entrada_compartida
	 */
	public function setId_entrada_compartida(int $iid_entrada_compartida): void
	{
		$this->iid_entrada_compartida = $iid_entrada_compartida;
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
	public function setDestinos(array $a_destinos= null): void
	{
        $this->a_destinos = $a_destinos;
	}
	/**
	 *
	 * @return DateTimeLocal|NullDateTimeLocal|null $df_documento
	 */
	public function getF_documento(): DateTimeLocal|NullDateTimeLocal|null
	{
        return $this->df_documento?? new NullDateTimeLocal;
	}
	/**
	 * 
	 * @param DateTimeLocal|null $df_documento
	 */
	public function setF_documento(DateTimeLocal|null $df_documento = null): void
	{
        $this->df_documento = $df_documento;
	}
	/**
	 *
	 * @return array|stdClass|null $json_prot_origen
	 */
	public function getJson_prot_origen(): array|stdClass|null
	{
		return $this->json_prot_origen;
	}
	/**
	 * 
	 * @param stdClass|array|null $json_prot_origen
	 */
	public function setJson_prot_origen(stdClass|array|null $json_prot_origen = null): void
	{
        $this->json_prot_origen = $json_prot_origen;
	}
	/**
	 *
	 * @return array|stdClass|null $json_prot_ref
	 */
	public function getJson_prot_ref(): array|stdClass|null
	{
		return $this->json_prot_ref;
	}
	/**
	 * 
	 * @param stdClass|array|null $json_prot_ref
	 */
	public function setJson_prot_ref(stdClass|array|null $json_prot_ref = null): void
	{
        $this->json_prot_ref = $json_prot_ref;
	}
	/**
	 *
	 * @return int|null $icategoria
	 */
	public function getCategoria(): ?int
	{
		return $this->icategoria;
	}
	/**
	 *
	 * @param int|null $icategoria
	 */
	public function setCategoria(?int $icategoria = null): void
	{
		$this->icategoria = $icategoria;
	}
	/**
	 *
	 * @return string $sasunto_entrada
	 */
	public function getAsunto_entrada(): string
	{
		return $this->sasunto_entrada;
	}
	/**
	 *
	 * @param string $sasunto_entrada
	 */
	public function setAsunto_entrada(string $sasunto_entrada): void
	{
		$this->sasunto_entrada = $sasunto_entrada;
	}
	/**
	 *
	 * @return DateTimeLocal|NullDateTimeLocal|null $df_entrada
	 */
	public function getF_entrada(): DateTimeLocal|NullDateTimeLocal|null
	{
        return $this->df_entrada?? new NullDateTimeLocal;
	}
	/**
	 * 
	 * @param DateTimeLocal|null $df_entrada
	 */
	public function setF_entrada(DateTimeLocal|null $df_entrada = null): void
	{
        $this->df_entrada = $df_entrada;
	}
	/**
	 *
	 * @return string|null $sanulado
	 */
	public function getAnulado(): ?string
	{
		return $this->sanulado;
	}
	/**
	 *
	 * @param string|null $sanulado
	 */
	public function setAnulado(?string $sanulado = null): void
	{
		$this->sanulado = $sanulado;
	}
}