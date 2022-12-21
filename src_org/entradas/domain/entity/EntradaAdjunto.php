<?php

namespace entradas\domain\entity;
/**
 * Clase que implementa la entidad entrada_adjuntos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 14/12/2022
 */
class EntradaAdjunto {

	/* ATRIBUTOS ----------------------------------------------------------------- */

	/**
	 * Id_item de EntradaAdjunto
	 *
	 * @var int
	 */
	 private int $iid_item;
	/**
	 * Id_entrada de EntradaAdjunto
	 *
	 * @var int
	 */
	 private int $iid_entrada;
	/**
	 * Nom de EntradaAdjunto
	 *
	 * @var string|null
	 */
	 private string|null $snom = null;
	/**
	 * Adjunto de EntradaAdjunto
	 *
	 * @var string
	 */
	 private string $sadjunto;

	/* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

	/**
	 * Establece el valor de todos los atributos
	 *
	 * @param array $aDatos
	 * @return EntradaAdjunto
	 */
	public function setAllAttributes(array $aDatos): EntradaAdjunto
	{
		if (array_key_exists('id_item',$aDatos))
		{
			$this->setId_item($aDatos['id_item']);
		}
		if (array_key_exists('id_entrada',$aDatos))
		{
			$this->setId_entrada($aDatos['id_entrada']);
		}
		if (array_key_exists('nom',$aDatos))
		{
			$this->setNom($aDatos['nom']);
		}
		if (array_key_exists('adjunto',$aDatos))
		{
			$this->setAdjunto($aDatos['adjunto']);
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
	 * @return string|null $snom
	 */
	public function getNom(): ?string
	{
		return $this->snom;
	}
	/**
	 *
	 * @param string|null $snom
	 */
	public function setNom(?string $snom = null): void
	{
		$this->snom = $snom;
	}
	/**
	 *
	 * @return string $sadjunto
	 */
	public function getAdjunto(): string
	{
		return $this->sadjunto;
	}
	/**
	 *
	 * @param string $sadjunto
	 */
	public function setAdjunto(string $sadjunto): void
	{
		$this->sadjunto = $sadjunto;
	}
}