<?php

namespace etiquetas\domain\entity;
	use function core\is_true;
/**
 * Clase que implementa la entidad etiquetas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 13/12/2022
 */
class Etiqueta {

	/* ATRIBUTOS ----------------------------------------------------------------- */

	/**
	 * Id_etiqueta de Etiqueta
	 *
	 * @var int
	 */
	 private int $iid_etiqueta;
	/**
	 * Nom_etiqueta de Etiqueta
	 *
	 * @var string
	 */
	 private string $snom_etiqueta;
	/**
	 * Id_cargo de Etiqueta
	 *
	 * @var int|null
	 */
	 private int|null $iid_cargo = null;
	/**
	 * Oficina de Etiqueta
	 *
	 * @var bool|null
	 */
	 private bool|null $boficina = null;

	/* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

	/**
	 * Establece el valor de todos los atributos
	 *
	 * @param array $aDatos
	 * @return Etiqueta
	 */
	public function setAllAttributes(array $aDatos): Etiqueta
	{
		if (array_key_exists('id_etiqueta',$aDatos))
		{
			$this->setId_etiqueta($aDatos['id_etiqueta']);
		}
		if (array_key_exists('nom_etiqueta',$aDatos))
		{
			$this->setNom_etiqueta($aDatos['nom_etiqueta']);
		}
		if (array_key_exists('id_cargo',$aDatos))
		{
			$this->setId_cargo($aDatos['id_cargo']);
		}
		if (array_key_exists('oficina',$aDatos))
		{
			$this->setOficina(is_true($aDatos['oficina']));
		}
		return $this;
	}
	/**
	 *
	 * @return int $iid_etiqueta
	 */
	public function getId_etiqueta(): int
	{
		return $this->iid_etiqueta;
	}
	/**
	 *
	 * @param int $iid_etiqueta
	 */
	public function setId_etiqueta(int $iid_etiqueta): void
	{
		$this->iid_etiqueta = $iid_etiqueta;
	}
	/**
	 *
	 * @return string $snom_etiqueta
	 */
	public function getNom_etiqueta(): string
	{
		return $this->snom_etiqueta;
	}
	/**
	 *
	 * @param string $snom_etiqueta
	 */
	public function setNom_etiqueta(string $snom_etiqueta): void
	{
		$this->snom_etiqueta = $snom_etiqueta;
	}
	/**
	 *
	 * @return int|null $iid_cargo
	 */
	public function getId_cargo(): ?int
	{
		return $this->iid_cargo;
	}
	/**
	 *
	 * @param int|null $iid_cargo
	 */
	public function setId_cargo(?int $iid_cargo = null): void
	{
		$this->iid_cargo = $iid_cargo;
	}
	/**
	 *
	 * @return bool|null $boficina
	 */
	public function isOficina(): ?bool
	{
		return $this->boficina;
	}
	/**
	 *
	 * @param bool|null $boficina
	 */
	public function setOficina(?bool $boficina = null): void
	{
		$this->boficina = $boficina;
	}
}