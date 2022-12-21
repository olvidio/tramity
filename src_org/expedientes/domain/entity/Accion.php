<?php

namespace expedientes\domain\entity;
/**
 * Clase que implementa la entidad acciones
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 9/12/2022
 */
class Accion {

	/* ATRIBUTOS ----------------------------------------------------------------- */

	/**
	 * Id_item de Accion
	 *
	 * @var int
	 */
	 private int $iid_item;
	/**
	 * Id_expediente de Accion
	 *
	 * @var int
	 */
	 private int $iid_expediente;
	/**
	 * Tipo_accion de Accion
	 *
	 * @var int
	 */
	 private int $itipo_accion;
	/**
	 * Id_escrito de Accion
	 *
	 * @var int
	 */
	 private int $iid_escrito;

	/* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

	/**
	 * Establece el valor de todos los atributos
	 *
	 * @param array $aDatos
	 * return Accion
	 */
	public function setAllAttributes(array $aDatos): Accion
	{
		if (array_key_exists('id_item',$aDatos))
		{
			$this->setId_item($aDatos['id_item']);
		}
		if (array_key_exists('id_expediente',$aDatos))
		{
			$this->setId_expediente($aDatos['id_expediente']);
		}
		if (array_key_exists('tipo_accion',$aDatos))
		{
			$this->setTipo_accion($aDatos['tipo_accion']);
		}
		if (array_key_exists('id_escrito',$aDatos))
		{
			$this->setId_escrito($aDatos['id_escrito']);
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
	 * @return int $iid_expediente
	 */
	public function getId_expediente(): int
	{
		return $this->iid_expediente;
	}
	/**
	 *
	 * @param int $iid_expediente
	 */
	public function setId_expediente(int $iid_expediente): void
	{
		$this->iid_expediente = $iid_expediente;
	}
	/**
	 *
	 * @return int $itipo_accion
	 */
	public function getTipo_accion(): int
	{
		return $this->itipo_accion;
	}
	/**
	 *
	 * @param int $itipo_accion
	 */
	public function setTipo_accion(int $itipo_accion): void
	{
		$this->itipo_accion = $itipo_accion;
	}
	/**
	 *
	 * @return int $iid_escrito
	 */
	public function getId_escrito(): int
	{
		return $this->iid_escrito;
	}
	/**
	 *
	 * @param int $iid_escrito
	 */
	public function setId_escrito(int $iid_escrito): void
	{
		$this->iid_escrito = $iid_escrito;
	}
}