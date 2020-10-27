<?php
namespace core;

/**
 * Condicion
 *
 * Classe per a gestionar les condicions de cerca a la Base de Dades
 *
 * @package delegación
 * @subpackage model
 * @author 
 * @version 1.0
 * @created 22/9/2010
 */
class Condicion {
	/* ATRIBUTS ----------------------------------------------------------------- */


	/* CONSTRUCTOR -------------------------------------------------------------- */

	function __construct() {
		// constructor buit
	}

	/* METODES PUBLICS -----------------------------------------------------------*/
	public function getCondicion($campo,$operador,$valor) {
	   if (isset($operador) && $operador !='') {
			switch($operador) {
				case '!=':
					$sCondi = "$campo != :$campo";
					break;
				case 'IS NOT NULL':
				case 'IS NULL':
					$sCondi = "$campo $operador";
					break;
				case 'OR':
					$sCondi = '';
					$aVal = explode(',',$valor);
					foreach ($aVal as $val) {
						$sCondi .= empty($sCondi)? "$campo = $val" : " OR $campo = $val";
					}
					$sCondi = "($sCondi)";
					break;
				case 'BETWEEN':
					$val1 = strtok($valor,',');
					$val2 = strtok(',');
					$sCondi = "$campo >= $val1 AND $campo <= $val2";
					break;
				case '!~':
				    $sCondi = "$campo::text !~ :$campo";
				    break;
				case '!~*':
				    $sCondi = "$campo::text !~* :$campo";
				    break;
				case '~':
				    $sCondi = "$campo::text ~ :$campo";
				    break;
				case '~*':
				    $sCondi = "$campo::text ~* :$campo";
				    break;
				case '~INV':
					$sCondi = ":$campo::text ~ $campo";
					break;
				case 'sin_acentos':
					$sCondi = "public.sin_acentos($campo::text)  ~* public.sin_acentos(:$campo::text)";
					break;
				case '&':
					$sCondi = "($campo & :$campo) = :$campo";
					break;
				case 'ANY':
					/* Uso: pasar un array de postgres, que el php trata com una variable string:
					 * $a_id_dir = array (1,3,7,90);
					 * $v = "{".implode(', ',$aid_dir)."}";
					 * $aWhere['id_direccion'] = $v;
            		 * $aOperador['id_direccion'] = 'ANY';
					 */
					$sCondi = "$campo = ANY (:$campo)";
					break;
				case 'IN':
				case 'NOT IN':
					/* no funciona, por lo menos con los integer, lo toma como string. */
					/* Se hace como el BETWEEN */
					$sCondi = "$campo $operador ($valor)";
					break;
				case 'TXT':
				    $sCondi = "$valor";
				    break;
				default:
					$sCondi = "$campo $operador :$campo";
			}
		} else {
			$sCondi = "$campo = :$campo";
		}
		return $sCondi;
	}

}