<?php

use function core\any_2;
use entradas\model\GestorEntrada;
use usuarios\model\entity\Cargo;

// INICIO Cabecera global de URL de controlador *********************************


require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************


/**
* Conexión al servidor DaviCal para comprobar los pendientes
*
*/
//$oDBC = new PDO(ConfigGlobal::$str_conexio_cal);

$id_reg='';
$aviso_rango='';
$aviso_repe='';
$aviso_origen='';
$aviso_aprobado='';
$aviso_txt='';
$error_txt='';
$aviso_salto='';
$aviso_any='';
$asunto='';
$detalle='';
$visibilidad='';
$anulado='';
$oficinas_txt='';
$dest_id_lugar[0]='';
$datos='';
$id_pendiente = '';
$pendiente_txt = '';
$pendiente_uid = '';

$txt_err = '';
$Qque = (string) \filter_input(INPUT_POST, 'que');
$Qprot_num = (integer) \filter_input(INPUT_POST, 'prot_num');
$Qprot_any = (integer) \filter_input(INPUT_POST, 'prot_any');

$Qprot_any=any_2($Qprot_any);
// compruebo el año (actual o -1)
$any=date('y');
if ($Qprot_any != $any && $Qprot_any != $any-1) $aviso_any=1;

/*
// id_lugar de cr
$query_cr="SELECT id_lugar FROM lugares WHERE sigla='cr'";
$oDBRSt_x_cr=$oDBR->prepare($query_cr);
$oDBRSt_x_cr->execute();
$id_cr=$oDBRSt_x_cr->fetchColumn();
*/

switch ($Qque) {
	case "s1":
	case "e1":
	    /*
		// compruebo si está fuera del rango para la dl (o cr si es una salida)
		if ($Qque == "s1") { $rango_inf= $rango_inf_cr; } else { $rango_inf= $rango_inf_dl; }
		if ( $Qprot_num < $rango_inf || $Qprot_num > $rango_sup_dl) {
			$aviso_rango=1;
		}
		// compruebo si está repetido
		$query_repe="SELECT * FROM escritos WHERE prot_num=$Qprot_num AND prot_any=$Qprot_any";
		$oDBRSt_repetido=$oDBR->query($query_repe);
		if ($oDBRSt_repetido->rowCount()) { 
			$Qprot_num="";
			$aviso_repe=1;
		} else {
			// doy un aviso si el número de protocolo está x números ($error_prot) por encima del último.
			// valores por defecto según el número
			if ( $Qprot_num >= $rango_inf_cr && $Qprot_num < $rango_sup_cr) {
				// id_lugar de cr
				$query_cr="SELECT id_lugar FROM lugares WHERE sigla='cr'";
				$oDBRSt_x_cr=$oDBR->query($query_cr);
				$dest_id_lugar[0]=$oDBRSt_x_cr->fetchColumn();
				// doy un aviso si el número de protocolo está x números ($error_prot) por encima del último.
				$query_prot="SELECT prot_num FROM escritos WHERE prot_any=$Qprot_any AND prot_num < $rango_sup_cr ORDER BY id_reg DESC limit 1";
				$oDBRSt_q_ult=$oDBR->query($query_prot);
				if ($oDBRSt_q_ult->rowCount()) {
					$prot_ult=$oDBRSt_q_ult->fetchColumn();
					if ($Qprot_num > $prot_ult + $error_prot) {
						$aviso_salto=$prot_ult." (cr)";
					} 
				} else {
					$aviso_txt=sprintf(_("No existe ningún registro para el año %s."),$Qprot_any);
				}
			} else { // > $rango_sup_cr (caso dl)
				$dest_id_lugar[0]="";
				$query_prot="SELECT prot_num FROM escritos WHERE prot_any=$Qprot_any AND prot_num >= $rango_inf_dl ORDER BY id_reg DESC limit 1";
				$oDBRSt_q_ult=$oDBR->query($query_prot);
				if ($oDBRSt_q_ult->rowCount()) {
					$prot_ult=$oDBRSt_q_ult->fetchColumn();
					if ($Qprot_num > $prot_ult + $error_prot) {
						$aviso_salto=$prot_ult." (dl)";
					}
				} else {
					$aviso_txt=sprintf(_("No existe ningún registro para el año %s."),$Qprot_any);
				}
			}
		}
		*/
		break;
	case "e2":
	    
	    /*
		// de donde es:
		$sql="SELECT tipo_ctr FROM lugares WHERE id_lugar=".$_POST['id_lugar']."";
		$oDBRSt_q_origen=$oDBR->query($sql);
		$sigla=$oDBRSt_q_origen->fetchColumn();
		switch ($sigla) {
			case "cr":
			case "dl":
				// compruebo si está fuera del rango para cr o dl
				if ( $Qprot_num < 1 || $Qprot_num > $num_max_cr) {
					$aviso_rango=1;
				}
				break;
			default:
				// compruebo si está fuera del rango para ctr
				if ( $Qprot_num < 1 || $Qprot_num > $num_max_ctr) {
					$aviso_rango=1;
				}
		}
		// compruebo si ya está registrado
		$query_repe="SELECT * FROM entradas WHERE id_lugar=".$_POST['id_lugar']." AND prot_num=$Qprot_num AND prot_any=$Qprot_any";
		$oDBRSt_repetido=$oDBR->query($query_repe);
		if ($oDBRSt_repetido->rowCount()) { 
			$aviso_origen=1;
		} 
		*/
		break;
	case "e3":
	  /*
		// para buscar los valores de la referencia
		// compruebo si existe el escrito de referencia (sólo el primero, ordeno por anulado).

	   // Busco en entradas
	   $query_ref="SELECT d.id_reg,e.asunto,e.f_doc,e.anulado,e.detalle,e.reservado
				   FROM entradas d join escritos e USING (id_reg)
				  WHERE d.id_lugar=".$_POST['id_lugar']." AND d.prot_num=$Qprot_num AND d.prot_any=$Qprot_any
					ORDER BY e.anulado DESC	";
	   //echo "query: $query_ref<br>";
	   $oDBRSt_ref=$oDBR->query($query_ref);

	   // Si no està en entradas busco en destinos
	   if ($oDBRSt_ref->rowCount() == 0) {
		  $query_ref="SELECT d.id_reg,e.asunto,e.f_doc,e.anulado,e.detalle,e.reservado
					 FROM destinos d join escritos e USING (id_reg)
					WHERE d.id_lugar=".$_POST['id_lugar']." AND d.prot_num=$Qprot_num AND d.prot_any=$Qprot_any
					ORDER BY e.anulado DESC	";
		  //echo "query: $query_ref<br>";
		  $oDBRSt_ref=$oDBR->query($query_ref);
	   }
	  // Si la ref. es a un escrito de la dl:
	   $query_id_dl="SELECT sigla
					 FROM lugares
					 WHERE id_lugar=".$_POST['id_lugar']." AND sigla='".ConfigGlobal::$dele."' ";
	   //echo "query: $query_ref<br>";
	   $oDBRSt_id_dl=$oDBR->query($query_id_dl);

	  if (($oDBRSt_ref->rowCount() == 0) && ($oDBRSt_id_dl->rowCount() > 0)) {
		  $query_ref="SELECT e.id_reg,e.asunto,e.f_doc,e.anulado,e.detalle,e.reservado
					  FROM escritos e
					  WHERE e.prot_num=$Qprot_num AND e.prot_any=$Qprot_any";
		  //echo "query: $query_ref<br>";
		  $oDBRSt_ref=$oDBR->query($query_ref);
	   }

	   if ($oDBRSt_ref->rowCount() > 0) {
		  $oReferencia=$oDBRSt_ref->fetch(PDO::FETCH_OBJ);
		  $asunto=$oReferencia->asunto;
		  $detalle=$oReferencia->detalle;
		  $reservado=empty($oReferencia->reservado)? 'f': 't';
		  $f_doc=$oReferencia->f_doc;
		  $anulado=$oReferencia->anulado;
		  $id_reg =$oReferencia->id_reg;
		  $query_of="SELECT id_oficina
					 FROM oficinas
					 WHERE id_reg=$id_reg
					 GROUP BY id_oficina,responsable
					 ORDER BY responsable DESC";
		  $oficinas_txt='';
		  foreach ($oDBR->query($query_of) as $row_of) {
			 $oficinas_txt.=$row_of['id_oficina']." ";
		  }
	   }
	   */	
	   break;
	case "s2":
	    /*
		$donde="es.prot_num='".$Qprot_num."'";
		$donde.="AND es.prot_any='".$Qprot_any."'"; 
		// en entradas
		$nueva=3; //para el boton guardar
		// primero compruebo que no está ya aprobado:
		$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,
			ap.id_salida,ap.f_aprobacion,ap.f_salida,
			de.id_lugar as dest_lugar,de.prot_num as dest_prot_num,de.prot_any as dest_prot_any,de.mas as dest_mas,
			u.sigla
			FROM escritos es LEFT JOIN aprobaciones ap USING (id_reg), lugares u, destinos de
			WHERE ap.id_reg=de.id_reg AND ap.id_salida=de.id_salida AND de.id_lugar=u.id_lugar AND $donde
			";
		$oDBRSt_q=$oDBR->query($sql);
		if ($oDBRSt_q->rowCount()!=0) {
		   	$aviso_txt=sprintf(_("Ya existe una aprobación con este número: %s/%s."),$Qprot_num,$Qprot_any);
	   	} else {	
			$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,
				en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas as o_mas,
				u.sigla
				FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u
				WHERE en.id_lugar=u.id_lugar AND $donde
				";
			// echo "query: $sql<br>";
			$oDBRSt_q=$oDBR->query($sql);
			if ($oDBRSt_q->rowCount()==0) {
				$aviso_txt=sprintf(_("No existe ninguna entrada con este número: %s/%s."),$Qprot_num,$Qprot_any);
			} else {
				$row=$oDBRSt_q->fetch(PDO::FETCH_ASSOC);
				$id_reg=$row["id_reg"];
				$Qprot_num=$row["prot_num"];
				$Qprot_any=$row["prot_any"];
				$asunto=$row["asunto"];
				$anulado=$row["anulado"];
		    	$reservado=empty($row["reservado"])? 'f': 't';
				$detalle=$row["detalle"];
				$f_doc=$row["f_doc"];
						
				$id_entrada=$row["id_entrada"];
				$f_entrada=$row["f_entrada"];
				
				$dest_id_lugar[0]=$row["o_lugar"];
				$dest_prot_num[0]=$row["o_prot_num"];
				$dest_prot_any[0]=$row["o_prot_any"];
				$dest_mas[0]=$row["o_mas"];
				
				$datos=",
					\"id_reg\": \"$id_reg\",
					\"f_doc\": \"$f_doc\",
					\"id_entrada\": \"$id_entrada\",
					\"f_entrada\": \"$f_entrada\",
					\"dest_prot_num\": \"$dest_prot_num[0]\",
					\"dest_prot_any\": \"$dest_prot_any[0]\",
					\"dest_mas\": \"$dest_mas[0]\"
						";
				
				$array_oficinas=buscar_oficinas_id($id_reg,$id_entrada,"f");
				foreach ($array_oficinas as $value) {
						$oficinas_txt.= "$value ";
				}
				// Busco las referencias
				$referencias=buscar_ref($id_reg,"f","array");
				$r=0;
				foreach($referencias as $ref) {
					$r++;
					$datos.=",\"ref_id_lugar_$r\":\"".$ref["id_lugar"]."\",\"ref_num_$r\":\"".$ref["num"]."\",\"ref_any_$r\":\"".$ref["any"]."\"";
				}
				$datos.=",\"ref_r\": \"$r\"";
			}
		}
		*/
		break;
	case "s3":
	    /*
		// compruebo si existe el escrito de referencia (sólo el primero, ordeno por anulado).
		$query_dest="SELECT d.id_reg,e.asunto,e.anulado,e.reservado,e.detalle
					FROM entradas d join escritos e USING (id_reg)
					WHERE d.id_lugar=".$_POST['id_lugar']." AND d.prot_num=$Qprot_num AND d.prot_any=$Qprot_any
					ORDER BY e.anulado DESC	";
		//echo "query: $query_dest<br>";
		$oDBRSt_dest=$oDBR->query($query_dest);
		if ($oDBRSt_dest->rowCount()) { 
			$oEscrito=$oDBRSt_dest->fetch(PDO::FETCH_OBJ);
			$asunto=$oEscrito->asunto;
			$anulado=$oEscrito->anulado;
		    $reservado=empty($oReferencia->reservado)? 'f': 't';
			$detalle=$oEscrito->detalle;
			$id_reg =$oEscrito->id_reg;
			$query_of="SELECT id_oficina
						FROM oficinas
						WHERE id_reg=$id_reg
						GROUP BY id_oficina,responsable
						ORDER BY responsable DESC";
			$oDBRSt_of=$oDBR->query($query_of);
			$oficinas_txt="";
			foreach ($oDBRSt_of->fetchAll() as $row_of) {
				$oficinas_txt.=$row_of['id_oficina']." ";
			}
		}
		*/
		break;
	case "s4": //comprobado (modificar escrito registro)
        $Qid_lugar = (integer) \filter_input(INPUT_POST, 'id_lugar');
		// compruebo si existe el escrito de referencia (sólo el primero, ordeno por anulado).
		// en entradas:
	    $gesEntradas = new GestorEntrada();       //$aProt_orgigen = ['id_lugar', 'num', 'any', 'mas']
	    $aProt_origen = [ 'lugar' => $Qid_lugar,
                        'num' => $Qprot_num, 
                        'any' => $Qprot_any,
                        'mas' => '',
                    ];
		$cEntradas = $gesEntradas->getEntradasByProtOrigenDB($aProt_origen);
		
		foreach($cEntradas as $oEntrada) {
		    $id_entrada = $oEntrada->getId_entrada();
		    $id_reg = 'REN'.$id_entrada; // REN = Regitro Entrada
		    $id_of_ponente = $oEntrada->getPonente();
		    // para crear un pendiente, no pongo 'reservado'
		    $asunto = $oEntrada->getAsuntoDB();
		    $detalle = $oEntrada->getDetalle();
		    $visibilidad = $oEntrada->getVisibilidad();
		    // El estado de la enrtrada no tiene nada que ver con el del pendiente
		    // $oEntrada->getEstado();
		    $anulado = '';
		    $resto_oficinas = $oEntrada->getResto_oficinas();
		    $oficinas_txt = implode(' ', $resto_oficinas);
		}
		$jsondata['id_reg'] = $id_reg;
		break;
	case "s5":
	    /*
		$donde="es.prot_num='".$Qprot_num."' AND es.prot_any='".$Qprot_any."'"; 
		$dest_prot_num[0] = empty($dest_prot_num[0])? '' :$dest_prot_num[0];
		$dest_prot_any[0] = empty($dest_prot_any[0])? '' :$dest_prot_any[0];
		$dest_mas[0] = empty($dest_mas[0])? '' :$dest_mas[0];

		$nueva=4; //para el boton guardar
		$sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,
			ap.id_salida,ap.f_aprobacion,ap.f_salida,ap.id_modo_envio
			FROM escritos es LEFT JOIN aprobaciones ap USING (id_reg)
			WHERE $donde
			";
		//echo "query: $sql<br>";
		$oDBRSt_q=$oDBR->query($sql);
		if ($oDBRSt_q->rowCount()==0) { 
		   	$aviso_txt=sprintf(_("No existe ninguna aprobación con este número: %s/%s."),$Qprot_num,$Qprot_any); 
		} else {
			$row=$oDBRSt_q->fetch(PDO::FETCH_ASSOC);
			$id_reg=$row["id_reg"];
			$Qprot_num=$row["prot_num"];
			$Qprot_any=$row["prot_any"];
			$asunto=$row["asunto"];
			$anulado=$row["anulado"];
		    $reservado=empty($row["reservado"])? 'f': 't';
			$detalle=$row["detalle"];
			$f_doc=$row["f_doc"];
					
			$id_salida=$row["id_salida"];
			$f_aprobacion=$row["f_aprobacion"];
			// dani 14.XII -> 1/8/05
			$f_salida=$row["f_salida"];
			//if (empty($f_salida)) $f_salida=$hoy;
			
			$oDBRSt_q_des=$oDBR->query("SELECT descripcion FROM destino_multiple WHERE id_reg=$id_reg");
			$descripcion=$oDBRSt_q_des->fetchColumn();

			$id_modo_envio=$row["id_modo_envio"];
			
			if (empty($id_salida)) {
			  	$aviso_txt=sprintf(_("No existe ninguna aprobación con este número: %s/%s."),$Qprot_num,$Qprot_any);
				$aviso_aprobado=1;
		   	} else {
				$sql_destino= "SELECT de.id_lugar as dest_lugar,de.prot_num as dest_prot_num,
						de.prot_any as dest_prot_any,de.mas as dest_mas,u.sigla
					FROM lugares u, destinos de
					WHERE de.id_reg=$id_reg AND de.id_salida=$id_salida AND de.id_lugar=u.id_lugar
					";
				//echo "query: $sql_destino<br>";
				$oDBRSt_q_dest=$oDBR->query($sql_destino);
				if ($oDBRSt_q_dest->rowCount()!=0) {
					$row=$oDBRSt_q_dest->fetch(PDO::FETCH_ASSOC);
					$dest_id_lugar[0]=$row["dest_lugar"];
					$dest_prot_num[0]=$row["dest_prot_num"];
					$dest_prot_any[0]=$row["dest_prot_any"];
					$dest_mas[0]=$row["dest_mas"];
				}
				
				$array_oficinas=buscar_oficinas_id($id_reg,$id_salida,"f");
				if (is_array($array_oficinas)) {
					foreach ($array_oficinas as $value) {
							$oficinas_txt.= "$value ";
					}
				}
			
				// Compruebo que no tenga un pendiente asociado.
				$sql_pen="select * from calendar_item where uid ~ '^R$id_reg-' AND status != 'COMPLETED' AND status !='CANCELLED'";
				$oDBCSt_q_pen=$oDBC->query($sql_pen);
				if ($num_pen=$oDBCSt_q_pen->rowCount()) {
					if ($num_pen>1) {
						$pendiente_txt=_("Está relacionado con más de un pendiente");
					} else {
						$row=$oDBCSt_q_pen->fetch(PDO::FETCH_ASSOC);
						if ($row["rrule"]) {
							$pendiente_txt=_("Está relacionado con un pendiente periódico");
									$pen_asunto=$row["summary"];
									$pen_uid=$row["uid"];
									$ref=buscar_ref_uid($pen_uid,"txt");
									$pendiente_txt.="*$pen_asunto ($ref)";
									$pendiente_uid.="*$pen_uid";
						} else {
							$pendiente=1;
							$id_pendiente=$row["uid"];
						}
					}
				} else { // si no tiene pendiente, en el caso de cr puede que estemos contestando a un pendiente periódico.
						// Busco que las ref no coincidan con las de algún pendiente periódico
					if ($dest_id_lugar[0]==$id_cr && $dest_prot_num[0] ) {		
						$sql_id="SELECT en.id_reg FROM entradas en JOIN escritos es USING (id_reg)
								WHERE en.id_lugar=$id_cr AND en.prot_num=$dest_prot_num[0] AND en.prot_any=$dest_prot_any[0] AND es.anulado IS NULL
								";
						//echo "query: $sql_id<br>";
						$oDBRSt_q_id=$oDBR->query($sql_id);
						if ($oDBRSt_q_id->rowCount()) {
							$id_reg_cr=$oDBRSt_q_id->fetchColumn();
							$sql_pen_2="select * from calendar_item where uid ~ '^R$id_reg_cr-' AND status != 'COMPLETED' AND status !='CANCELLED'";
							$oDBCSt_q_pen_2=$oDBC->query($sql_pen_2);
							if ($oDBCSt_q_pen_2->rowCount()!=0) {
								$pp=0;
								$pendiente_txt= _("es posible que esté relacionado con estos pendientes periódicos").":   "; 
								foreach ($oDBCSt_q_pen_2->fetchAll() as $row) {
									$pp++; 			
									$pen_asunto=$row["summary"];
									$pen_uid=$row["uid"];
									$ref=buscar_ref_uid($pen_uid,"txt");
									$pendiente_txt.="*$pen_asunto ($ref)";
									$pendiente_uid.="*$pen_uid";
								}
							}
						}
					}
					// idem para las referencias.
					$sql_ref= "SELECT r.id_lugar as ref_lugar,r.prot_num as ref_prot_num,
							r.prot_any as ref_prot_any
							FROM referencias r
							WHERE r.id_reg=$id_reg AND r.id_lugar=$id_cr ";
					//echo "query: $sql_ref<br>";
					$oDBRSt_q_ref=$oDBR->query($sql_ref);
					if ($oDBRSt_q_ref->rowCount()!=0) {
						$pp_ref=0;
						foreach ($oDBRSt_q_ref->fetchAll() as $row) {
							$pp_ref++; 			
							$ref_id_lugar=$row["ref_lugar"];
							$ref_prot_num=$row["ref_prot_num"];
							$ref_prot_any=$row["ref_prot_any"];
							
							$sql_id="SELECT id_reg FROM entradas en LEFT JOIN escritos es USING (id_reg)
										WHERE en.id_lugar=$id_cr AND en.prot_num=$ref_prot_num AND en.prot_any=$ref_prot_any
											AND es.anulado is null
										";
							//$pendiente_txt.="query: $sql_id<br>";
							$oDBRSt_q_id=$oDBR->query($sql_id);
							if ($oDBRSt_q_id->rowCount()) {
								if (!empty($pendiente_txt)) $pendiente_txt.=" *"; // para que me separe los comentarios.
								$pendiente_txt.= _("es posible que esté relacionado con estos pendientes periódicos (por las ref.)").":   "; 
								$id_reg_cr=$oDBRSt_q_id->fetchColumn();
								$sql_pen_2="select * from calendar_item where uid ~ '^R$id_reg_cr-' AND status != 'COMPLETED' AND status !='CANCELLED'";
								$oDBCSt_q_pen_2=$oDBC->query($sql_pen_2);
								if ($oDBCSt_q_pen_2->rowCount()!=0) {
									$pp=0;
									foreach ($oDBCSt_q_pen_2->fetchAll() as $row) {
										$pp++; 			
										$pen_asunto=$row["summary"];
										$pen_uid=$row["uid"];
										$ref=buscar_ref_uid($pen_uid,"txt");
										$pendiente_txt.="*$pen_asunto ($ref) ";
										$pendiente_uid.="*$pen_uid";
									}
								}
							}
						}
					}
				}
				
				//$pendiente_txt=substr($pendiente_txt,2);
				$datos=",\"id_reg\": \"$id_reg\",\"f_doc\": \"$f_doc\",\"id_salida\": \"$id_salida\",\"f_salida\": \"$f_salida\",
					\"f_aprobacion\": \"$f_aprobacion\",
					\"descripcion\": \"".str_replace('"','\\"',$descripcion)."\",\"dest_prot_num\": \"$dest_prot_num[0]\",
					\"dest_prot_any\": \"$dest_prot_any[0]\",\"dest_mas\": \"$dest_mas[0]\",\"id_pendiente\": \"$id_pendiente\",
					\"id_modo_envio\": \"$id_modo_envio\",\"pendiente_uid\": \"$pendiente_uid\",
					\"pendiente_txt\": \"".str_replace('"','\\"',$pendiente_txt)."\"";
					
			}
			// Busco las referencias
			$referencias=buscar_ref($id_reg,"f","array");
			$r=0;
			foreach($referencias as $ref) {
				$r++;
				$datos.=",\"ref_id_lugar_$r\":\"".$ref['id_lugar']."\",\"ref_num_$r\":\"".$ref['num']."\",\"ref_any_$r\":\"".$ref['any']."\"";
			}
			$datos.=",\"ref_r\": \"$r\"";
			//print_r($referencias);

		}
		*/
		break;
	case "distribucion":
	    /*
		$nueva=4; //para el boton guardar
		$donde="AND en.prot_num='".$Qprot_num."'";
		if (!empty($Qprot_any)) { $donde.="AND en.prot_any='".$Qprot_any."'"; }
		// en entradas
		$sql= "SELECT es.id_reg,en.prot_num,en.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,
			ap.id_salida,ap.f_salida,ap.f_aprobacion,en.f_doc_entrada,en.id_lugar as o_lugar,u.sigla,m.descripcion,m.tipo_ctr,m.tipo_labor
			FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u, aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida)
			WHERE ap.id_reg=es.id_reg AND en.id_lugar=u.id_lugar AND u.sigla='cr' $donde
			ORDER BY en.f_entrada DESC
			";
		//echo "query: $sql<br>";
		$oDBRSt_q=$oDBR->query($sql);
		if ($oDBRSt_q->rowCount()==0) { $error_txt=sprintf(_("No existe ninguna entrada con este número: %s/%s."),$Qprot_num,$Qprot_any); }	
		$row=$oDBRSt_q->fetch(PDO::FETCH_ASSOC);
		$id_reg=$row['id_reg'];
		$Qprot_num=$row['prot_num'];
		$Qprot_any=$row['prot_any'];
		$asunto=$row['asunto'];
		$anulado=$row['anulado'];
		$reservado=$row['reservado'];
		$detalle=$row['detalle'];
		$f_doc=$row['f_doc'];
				
		$id_salida=$row['id_salida'];
		$f_salida=$row['f_salida'];
		
		$descripcion=$row['descripcion'];
		$tipo_ctr=$row['tipo_ctr'];
		$tipo_labor=$row['tipo_labor'];
		$f_aprobacion=$row['f_aprobacion'];

		$datos=",\"id_reg\": \"$id_reg\",\"f_doc\": \"$f_doc\",\"id_salida\": \"$id_salida\",\"f_salida\": \"$f_salida\",\"f_aprobacion\": \"$f_aprobacion\",
			\"descripcion\": \"".str_replace('"','\\"',$descripcion)."\",\"tipo_ctr\": \"$tipo_ctr\",\"tipo_labor\": \"$tipo_labor\"";
		break;
		*/
	case "anular":
	    /*
		$pendiente_txt='';
		$pendiente_txt_1='';
		$pendiente_txt_2='';
		$pendiente_caso='';
		$id_reg_nuevo='';

		$hoy=date("d/m/Y");
		$sql_pen="select * from calendar_item where uid ~ '^R".$_POST['id_reg']."-' AND status != 'COMPLETED' AND status != 'CANCELLED' ";
		//echo "query: $sql_pen<br>";
		$oDBCSt_q_pen=$oDBC->query($sql_pen);
		if ($oDBCSt_q_pen->rowCount()) { 
			$pendiente_txt=_("existen pendientes asociados a este escrito. Al eliminar o anular el escrito, el pendiente puede perder la referencia. Se aconseja actualizar el pendiente antes de eliminar el ecrito."); 
			// busco el nuevo id_reg (con el prot entrada del antiguo)
			$sql="SELECT prot_num,prot_any,id_lugar FROM entradas WHERE id_reg=".$_POST['id_reg']."";
			extract($oDBR->query($sql)->fetch(PDO::FETCH_ASSOC));
			$lugar=$oDBR->query("SELECT sigla FROM lugares WHERE id_lugar=$id_lugar")->fetchColumn();
			$sql="SELECT en.id_reg FROM entradas en JOIN escritos es USING (id_reg)
					WHERE en.prot_any=$Qprot_any AND en.prot_num=$Qprot_num AND en.id_lugar=$id_lugar
					AND es.anulado IS NULL AND en.id_reg!=".$_POST['id_reg']."
					";
			$oDBRSt_q=$oDBR->query($sql);
			$num_rows=$oDBRSt_q->rowCount();
			if (empty($num_rows)) {
				$pendiente_txt_1=sprintf(_("No existe ningun escrito más (y no anulado) con esta referencia: %s %s/%s."),$lugar,$Qprot_num,$Qprot_any);
				$pendiente_txt_2=_("¿Desea eliminar los pendientes asociados a este escrito?"); 
				$pendiente_caso=1;
			}
			if ($num_rows > 1) {
				$pendiente_txt_1=sprintf(_("Existe más de un escrito (no anulado) con esta referencia: %s %s/%s."),$lugar,$Qprot_num,$Qprot_any);
				$pendiente_caso=2;
			}
			if ($num_rows==1) {
				$id_reg_nuevo=$oDBRSt_q->fetchColumn();
				$pendiente_txt_1=sprintf(_("Existe una nueva versión de este escrito: %s %s/%s."),$lugar,$Qprot_num,$Qprot_any);
				$pendiente_txt_2=_("¿Desea asociar los pendientes al escrito vigente?"); 
				$pendiente_caso=3;
			}
		}	

		$datos=", \"pendiente_txt\": \"".str_replace('"','\\"',$pendiente_txt)."\"
		   		, \"pendiente_txt_1\": \"".str_replace('"','\\"',$pendiente_txt_1)."\"
				, \"pendiente_txt_2\": \"".str_replace('"','\\"',$pendiente_txt_2)."\"
				, \"pendiente_caso\": \"$pendiente_caso\"
				, \"id_reg_nuevo\": \"$id_reg_nuevo\"";
		*/
		break;
	case "can_e1":
	    /*
		// compruebo si está fuera del rango para la dl (o cr si es una salida)
		if ( $Qprot_num < 200 || $Qprot_num > 1500) {
			$aviso_rango=1;
		}
		// compruebo si está repetido
		$query_repe="SELECT * FROM cancilleria_escritos WHERE prot_num=$Qprot_num AND prot_any=$Qprot_any";
		$oDBRSt_repe=$oDBR->query($query_repe);
		if ($oDBRSt_repe->rowCount()) { 
			$Qprot_num="";
			$aviso_repe=1;
		} else {
			// valores por defecto según el número
			if ( $Qprot_num < 500 ) {
				$origen="cr"; $origen_num=$Qprot_num; $origen_any=$Qprot_any;
			} else {
				$origen="of";
			}
			$datos=", \"origen\": \"$origen\"";
		}
		*/
		break;
	case "can_e2":
	    /*
		// compruebo si está repetido
		$query_repe="SELECT * FROM cancilleria_escritos WHERE origen='".$_POST['id_lugar']."' AND origen_num=$Qprot_num AND origen_any=$Qprot_any";
		//echo "sql: $query_repe<br>";
		$oDBRSt_repe=$oDBR->query($query_repe);
		if ($oDBRSt_repe->rowCount()) { 
			$Qprot_num="";
			$aviso_repe=1;
		}
		*/
		break;
	case "can_e3":
	    /*
		// Busco de quien es la ref.
		$query_sigla="SELECT sigla
					FROM lugares
					WHERE id_lugar=".$_POST['id_lugar']." ";
		//echo "query sigla: $query_sigla<br>";
		$oDBRSt_q_sigla=$oDBR->query($query_sigla);
		$sigla=$oDBRSt_q_sigla->fetchColumn();
		switch ($sigla) {
			case "Cancillería":
				$query_ref="SELECT e.id_reg,e.asunto,e.f_doc,e.detalle
							FROM cancilleria_escritos e 
							WHERE e.prot_num=$Qprot_num AND e.prot_any=$Qprot_any";
				break;
			case "IESE":
				$query_ref="SELECT e.id_reg,e.asunto,e.f_doc,e.detalle 
							FROM cancilleria_escritos e 
							WHERE e.origen_num=$Qprot_num AND e.origen_any=$Qprot_any";
				break;
		}
		//echo "query: $query_ref<br>";
		$oDBRSt_ref=$oDBR->query($query_ref);
			
		if ($oDBRSt_ref->rowCount()) { 
		   $oReferencia=$oDBRSt_ref->fetch(PDO::FETCH_OBJ);
			  $asunto=$oReferencia->asunto;
			  $detalle=$oReferencia->detalle;
			  $f_doc=$oReferencia->f_doc;
			  $id_reg =$oReferencia->id_reg;
			$query_of="SELECT id_oficina
						FROM oficinas
						WHERE id_reg=$id_reg AND cancilleria='t'
						GROUP BY id_oficina,responsable
						ORDER BY responsable DESC";
			$oDBRSt_of=$oDBR->query($query_of);
			$oficinas_txt="";
			foreach ($oDBRSt_of->fetchAll() as $row_of) {
			    $oficinas_txt.= $row_of["id_oficina"]." ";
			}
		}
		*/
	break;

}

/*
if (!empty($id_reg)) {
	$perm_detalle=permiso_detalle($id_reg,$reservado);
	switch ($perm_detalle) {
		case 0:
			$asunto_r=_("reservado");
			$detalle_r=_("reservado");
			break;
		case 1:
			// no puede ver el detalle
			$detalle_r=_("reservado");
			break;
	}
}
*/

/*
echo "{ \"que\": \"".$_POST["que"]."\",
	 \"rango\": \"$aviso_rango\", 
	 \"repe\": \"$aviso_repe\",
	 \"registrado\": \"$aviso_origen\",
	 \"aprobado\": \"$aviso_aprobado\",
	 \"txt\": \"$aviso_txt\",
	 \"error\": \"$error_txt\",
	 \"salto\": \"$aviso_salto\",
	 \"any\":\"$aviso_any\",
	 \"asunto\": \"".str_replace('"','\\"',$asunto)."\",
	 \"detalle\": \"".str_replace('"','\\"',$detalle)."\",
	 \"reservado\": \"$reservado\",
	 \"asunto_r\": \"".str_replace('"','\\"',$asunto_r)."\",
	 \"detalle_r\": \"".str_replace('"','\\"',$detalle_r)."\",
	 \"anulado\":  \"".str_replace('"','\\"',$anulado)."\",
	 \"oficinas\": \"$oficinas_txt\",
	 \"destino\": \"$dest_id_lugar[0]\" $datos
	 }";
*/

$jsondata["que"] = $Qque;
$jsondata["rango"] = "$aviso_rango"; 
$jsondata["repe"] = "$aviso_repe";
$jsondata["registrado"] = "$aviso_origen";
$jsondata["aprobado"] = "$aviso_aprobado";
$jsondata["txt"] = "$aviso_txt";
$jsondata["error"] = "$error_txt";
$jsondata["salto"] = "$aviso_salto";
$jsondata["any"] ="$aviso_any";
$jsondata["id_of_ponente"] ="$id_of_ponente";
$jsondata["asunto"] = "".str_replace('"','\"',$asunto)."";
$jsondata["detalle"] = "".str_replace('"','\"',$detalle)."";
$jsondata["visibilidad"] = "$visibilidad";
$jsondata["anulado"] =  "".str_replace('"','\"',$anulado)."";
$jsondata["oficinas"] = "$oficinas_txt";
$jsondata["destino"] = "$dest_id_lugar[0]";

if (empty($txt_err)) {
    $jsondata['success'] = true;
    $jsondata['mensaje'] = 'ok';
} else {
    $jsondata['success'] = false;
    $jsondata['mensaje'] = $txt_err;
}

//Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
header('Content-type: application/json; charset=utf-8');
echo json_encode($jsondata);
exit();

