<?php
namespace usuarios\model\entity;

use core\ClasePropiedades;
use core\DatosCampo;
use core\Set;
use PDO;
use PDOException;
use function core\is_true;


/**
 * Fichero con la Clase que accede a la tabla aux_cargos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 30/11/2022
 */
/**
 * Clase que implementa la entidad aux_cargos
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 30/11/2022
 */
class Cargo Extends ClasePropiedades {

    public const AMBITO_CG = 1;
    public const AMBITO_CR = 2;
    public const AMBITO_DL = 3;
    public const AMBITO_CTR = 4;

    public const CARGO_PONENTE = 1;
    public const CARGO_OFICIALES = 2;
    public const CARGO_VARIAS = 3;
    public const CARGO_TODOS_DIR = 4;
    public const CARGO_VB_VCD = 5;
    public const CARGO_DISTRIBUIR = 6;
    public const CARGO_REUNION = 7;

    public const OFICINA_ESQUEMA = -10;
	/* ATRIBUTOS ----------------------------------------------------------------- */


	/**
	 * aPrimary_key de Cargo
	 *
	 * @var array
	 */
	 private array $aPrimary_key;

	/**
	 * bLoaded de Cargo
	 *
	 * @var bool
	 */
	 private bool $bLoaded = FALSE;


	/**
	 * Id_cargo de Cargo
	 *
	 * @var int
	 */
	 private int $iid_cargo;
	/**
	 * Id_ambito de Cargo
	 *
	 * @var int
	 */
	 private int $iid_ambito;
	/**
	 * Cargo de Cargo
	 *
	 * @var string
	 */
	 private string $scargo = '';
	/**
	 * Descripcion de Cargo
	 *
	 * @var string|null
	 */
	 private ?string $sdescripcion = null;
	/**
	 * Id_oficina de Cargo
	 *
	 * @var int
	 */
	 private int $iid_oficina;
	/**
	 * Director de Cargo
	 *
	 * @var bool
	 */
	 private bool $bdirector;
	/**
	 * Id_usuario de Cargo
	 *
	 * @var int|null
	 */
	 private ?int $iid_usuario = null;
	/**
	 * Id_suplente de Cargo
	 *
	 * @var int|null
	 */
	 private ?int $iid_suplente = null;
	/**
	 * Sacd de Cargo
	 *
	 * @var bool
	 */
	 private bool $bsacd;
	/* ATRIBUTOS QUE NO SON CAMPOS------------------------------------------------- */

	/* CONSTRUCTOR -------------------------------------------------------------- */

	/**
	 * @param integer|null  $iid_cargo
	 */
	public function __construct(int $iid_cargo = null)
	{
		$oDbl = $GLOBALS['oDBT'];
		if ($iid_cargo !== null)
		{
			$this->iid_cargo = $iid_cargo;
			$this->aPrimary_key = array('iid_cargo' => $this->iid_cargo);
		}
		$this->setoDbl($oDbl);
		$this->setNomTabla('aux_cargos');
	}

	/* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

	/**
	 * Si no existe el registro, hace un insert, si existe, se hace el update.
	 */
	public function DBGuardar(): bool
	{
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		if ($this->DBCargar('guardar') === FALSE)
		{
		    $bInsert=TRUE;
		} else {
		    $bInsert=FALSE;
		}
		$aDades=array();
		$aDades['id_ambito'] = $this->iid_ambito;
		$aDades['cargo'] = $this->scargo;
		$aDades['descripcion'] = $this->sdescripcion;
		$aDades['id_oficina'] = $this->iid_oficina;
		$aDades['director'] = $this->bdirector;
		$aDades['id_usuario'] = $this->iid_usuario;
		$aDades['id_suplente'] = $this->iid_suplente;
		$aDades['sacd'] = $this->bsacd;
		array_walk($aDades, 'core\poner_null');
		//para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
		if ( is_true($aDades['director']) ) { $aDades['director']='true'; } else { $aDades['director']='false'; }
		if ( is_true($aDades['sacd']) ) { $aDades['sacd']='true'; } else { $aDades['sacd']='false'; }

		if ($bInsert === FALSE) {
			//UPDATE
			$update="
					id_ambito                = :id_ambito,
					cargo                    = :cargo,
					descripcion              = :descripcion,
					id_oficina               = :id_oficina,
					director                 = :director,
					id_usuario               = :id_usuario,
					id_suplente              = :id_suplente,
					sacd                     = :sacd";
			if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_cargo='$this->iid_cargo'")) === FALSE) {
				$sClaveError = 'Cargo.update.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
				return FALSE;
			}
				
            try {
                $oDblSt->execute($aDades);
            }
            catch ( PDOException $e) {
                $err_txt=$e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'Cargo.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
		} else {
			// INSERT
			$campos="(id_cargo,id_ambito,cargo,descripcion,id_oficina,director,id_usuario,id_suplente,sacd)";
			$valores="(:id_cargo,:id_ambito,:cargo,:descripcion,:id_oficina,:director,:id_usuario,:id_suplente,:sacd)";		
			if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
				$sClaveError = 'Cargo.insertar.prepare';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
				return FALSE;
			}
            try {
                $oDblSt->execute($aDades);
            }
            catch ( PDOException $e) {
                $err_txt=$e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'Cargo.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
			}
		}
		$this->setAllAtributes($aDades);
		return TRUE;
	}

	/**
	 * Carga los campos de la base de datos como ATRIBUTOS de la clase.
	 */
	public function DBCargar($que=null): bool
	{
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		if (isset($this->iid_cargo)) {
			if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_cargo='$this->iid_cargo'")) === FALSE) {
				$sClaveError = 'Cargo.cargar';
				$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
				return FALSE;
			}
			$aDades = $oDblSt->fetch(PDO::FETCH_ASSOC);
			
            // Para evitar posteriores cargas
            $this->bLoaded = TRUE;
			switch ($que) {
				case 'tot':
                    $this->setAllAtributes($aDades);
					break;
				case 'guardar':
					if (!$oDblSt->rowCount()){
					    return FALSE;
					}
					break;
                default:
					// En el caso de no existir esta fila, $aDades = FALSE:
					if ($aDades === FALSE) {
						return FALSE;
					}
					$this->setAllAtributes($aDades);
			}
			return TRUE;
		}
        return FALSE;
	}

	public function DBEliminar(): bool
	{
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_cargo='$this->iid_cargo'")) === FALSE) {
			$sClaveError = 'Cargo.eliminar';
			$_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
			return FALSE;
		}
		return TRUE;
	}
	
	/* OTOS MÉTODOS  ----------------------------------------------------------*/
	/* MÉTODOS PRIVADOS ----------------------------------------------------------*/

	/**
	 * Establece el valor de todos los atributos
	 *
	 * @param array $aDades
	 */
	private function setAllAtributes(array $aDades): void
	{
		if (array_key_exists('id_cargo',$aDades))
		{
			$this->setId_cargo($aDades['id_cargo']);
		}
		if (array_key_exists('id_ambito',$aDades))
		{
			$this->setId_ambito($aDades['id_ambito']);
		}
		if (array_key_exists('cargo',$aDades))
		{
			$this->setCargo($aDades['cargo']);
		}
		if (array_key_exists('descripcion',$aDades))
		{
			$this->setDescripcion($aDades['descripcion']);
		}
		if (array_key_exists('id_oficina',$aDades))
		{
			$this->setId_oficina($aDades['id_oficina']);
		}
		if (array_key_exists('director',$aDades))
		{
			$this->setDirector($aDades['director']);
		}
		if (array_key_exists('id_usuario',$aDades))
		{
			$this->setId_usuario($aDades['id_usuario']);
		}
		if (array_key_exists('id_suplente',$aDades))
		{
			$this->setId_suplente($aDades['id_suplente']);
		}
		if (array_key_exists('sacd',$aDades))
		{
			$this->setSacd($aDades['sacd']);
		}
	}
	/* MÉTODOS GET y SET --------------------------------------------------------*/


	/**
	 * Recupera las claves primarias de Cargo en un array
	 *
	 * @return array aPrimary_key
	 */
	public function getPrimary_key(): array
	{
		if (!isset($this->aPrimary_key )) {
			$this->aPrimary_key = array('id_cargo' => $this->iid_cargo);
		}
		return $this->aPrimary_key;
	}
	/**
	 * Establece las claves primarias de Cargo en un array
	 *
	 */
	public function setPrimary_key(array $aPrimaryKey): void
	{
		$this->aPrimary_key = $aPrimaryKey;
	}
	

	/**
	 *
	 * @return int $iid_cargo
	 */
	public function getId_cargo(): int
	{
		if (!isset($this->iid_cargo) && !$this->bLoaded) {
			$this->DBCargar();
		}
		return $this->iid_cargo;
	}
	/**
	 *
	 * @param int $iid_cargo
	 */
	public function setId_cargo(int $iid_cargo): void
	{
		$this->iid_cargo = $iid_cargo;
	}
	/**
	 *
	 * @return int $iid_ambito
	 */
	public function getId_ambito(): int
	{
		if (!isset($this->iid_ambito) && !$this->bLoaded) {
			$this->DBCargar();
		}
		return $this->iid_ambito;
	}
	/**
	 *
	 * @param int $iid_ambito
	 */
	public function setId_ambito( int $iid_ambito): void
	{
		$this->iid_ambito = $iid_ambito;
	}
	/**
	 *
	 * @return string $scargo
	 */
	public function getCargo(): string
	{
		if (!isset($this->scargo) && !$this->bLoaded) {
			$this->DBCargar();
		}
		return $this->scargo;
	}
	/**
	 *
	 * @param string $scargo
	 */
	public function setCargo( string $scargo): void
	{
		$this->scargo = $scargo;
	}
	/**
	 *
	 * @return string|null $sdescripcion
	 */
	public function getDescripcion(): ?string
	{
		if (!isset($this->sdescripcion) && !$this->bLoaded) {
			$this->DBCargar();
		}
		return $this->sdescripcion;
	}
	/**
	 *
	 * @param string|null $sdescripcion
	 */
	public function setDescripcion( ?string $sdescripcion = null): void
	{
		$this->sdescripcion = $sdescripcion;
	}
	/**
	 *
	 * @return int $iid_oficina
	 */
	public function getId_oficina(): int
	{
		if (!isset($this->iid_oficina) && !$this->bLoaded) {
			$this->DBCargar();
		}
		return $this->iid_oficina;
	}
	/**
	 *
	 * @param int $iid_oficina
	 */
	public function setId_oficina( int $iid_oficina): void
	{
		$this->iid_oficina = $iid_oficina;
	}
	/**
	 *
	 * @return bool $bdirector
	 */
	public function getDirector(): bool
	{
		if (!isset($this->bdirector) && !$this->bLoaded) {
			$this->DBCargar();
		}
		return $this->bdirector;
	}
	/**
	 *
	 * @param bool $bdirector
	 */
	public function setDirector( bool $bdirector): void
	{
		$this->bdirector = $bdirector;
	}
	/**
	 *
	 * @return int|null $iid_usuario
	 */
	public function getId_usuario(): ?int
	{
		if (!isset($this->iid_usuario) && !$this->bLoaded) {
			$this->DBCargar();
		}
		return $this->iid_usuario;
	}
	/**
	 *
	 * @param int|null $iid_usuario
	 */
	public function setId_usuario( ?int $iid_usuario = null): void
	{
		$this->iid_usuario = $iid_usuario;
	}
	/**
	 *
	 * @return int|null $iid_suplente
	 */
	public function getId_suplente(): ?int
	{
		if (!isset($this->iid_suplente) && !$this->bLoaded) {
			$this->DBCargar();
		}
		return $this->iid_suplente;
	}
	/**
	 *
	 * @param int|null $iid_suplente
	 */
	public function setId_suplente( ?int $iid_suplente = null): void
	{
		$this->iid_suplente = $iid_suplente;
	}
	/**
	 *
	 * @return bool $bsacd
	 */
	public function getSacd(): bool
	{
		if (!isset($this->bsacd) && !$this->bLoaded) {
			$this->DBCargar();
		}
		return $this->bsacd;
	}
	/**
	 *
	 * @param bool $bsacd
	 */
	public function setSacd( bool $bsacd): void
	{
		$this->bsacd = $bsacd;
	}
	/* MÉTODOS GET y SET DE ATRIBUTOS QUE NO SON CAMPOS -----------------------------*/

	/**
	 * Devuelve una colección de objetos del tipo DatosCampo
	 */
	public function getDatosCampos(): array
	{
		$oCargoSet = new Set();

		$oCargoSet->add($this->getDatosId_ambito());
		$oCargoSet->add($this->getDatosCargo());
		$oCargoSet->add($this->getDatosDescripcion());
		$oCargoSet->add($this->getDatosId_oficina());
		$oCargoSet->add($this->getDatosDirector());
		$oCargoSet->add($this->getDatosId_usuario());
		$oCargoSet->add($this->getDatosId_suplente());
		$oCargoSet->add($this->getDatosSacd());
		return $oCargoSet->getTot();
	}



	/**
	 *
	 * @return DatosCampo
	 */
	public function getDatosId_ambito(): DatosCampo
	{
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'id_ambito'));
		$oDatosCampo->setEtiqueta(_("id_ambito"));
		return $oDatosCampo;
	}
	/**
	 *
	 * @return DatosCampo
	 */
	public function getDatosCargo(): DatosCampo
	{
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'cargo'));
		$oDatosCampo->setEtiqueta(_("cargo"));
		return $oDatosCampo;
	}
	/**
	 *
	 * @return DatosCampo
	 */
	public function getDatosDescripcion(): DatosCampo
	{
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'descripcion'));
		$oDatosCampo->setEtiqueta(_("descripcion"));
		return $oDatosCampo;
	}
	/**
	 *
	 * @return DatosCampo
	 */
	public function getDatosId_oficina(): DatosCampo
	{
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'id_oficina'));
		$oDatosCampo->setEtiqueta(_("id_oficina"));
		return $oDatosCampo;
	}
	/**
	 *
	 * @return DatosCampo
	 */
	public function getDatosDirector(): DatosCampo
	{
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'director'));
		$oDatosCampo->setEtiqueta(_("director"));
		return $oDatosCampo;
	}
	/**
	 *
	 * @return DatosCampo
	 */
	public function getDatosId_usuario(): DatosCampo
	{
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'id_usuario'));
		$oDatosCampo->setEtiqueta(_("id_usuario"));
		return $oDatosCampo;
	}
	/**
	 *
	 * @return DatosCampo
	 */
	public function getDatosId_suplente(): DatosCampo
	{
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'id_suplente'));
		$oDatosCampo->setEtiqueta(_("id_suplente"));
		return $oDatosCampo;
	}
	/**
	 *
	 * @return DatosCampo
	 */
	public function getDatosSacd(): DatosCampo
	{
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new DatosCampo(array('nom_tabla'=>$nom_tabla,'nom_camp'=>'sacd'));
		$oDatosCampo->setEtiqueta(_("sacd"));
		return $oDatosCampo;
	}
}
