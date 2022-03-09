<?php

use entradas\model\entity\EntradaAdjunto;
use entradas\model\entity\GestorEntradaAdjuntoOrg;
use expedientes\model\entity\EscritoAdjunto;
use expedientes\model\entity\GestorEscritoAdjuntoOrg;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string) \filter_input(INPUT_POST, 'que');

if ($Qque == 'escritos') {
	// Adjuntos de escritos:
	$gesAdjuntosEscritoOrg = new GestorEscritoAdjuntoOrg();
	$cEscritoAdjuntosOrg = $gesAdjuntosEscritoOrg->getEscritoAdjuntos();

	foreach($cEscritoAdjuntosOrg as $oEscritoAdjuntoOrg) {
		$doc = '';
		$doc_encoded = '';
		$id_item = $oEscritoAdjuntoOrg->getId_item();
		$id_escrito = $oEscritoAdjuntoOrg->getId_escrito();
		$nom = $oEscritoAdjuntoOrg->getNom();
		$tipo_doc = $oEscritoAdjuntoOrg->getTipo_doc();
		$res_adjunto = $oEscritoAdjuntoOrg->getAdjuntoResource();
		
		if (!empty($res_adjunto)) {
			rewind($res_adjunto);
			$doc_encoded = stream_get_contents($res_adjunto);
			if ( base64_encode(base64_decode($doc_encoded, true)) === $doc_encoded){
				// $data is valid
				$doc = base64_decode($doc_encoded);
			} else {
				// $data is NOT valid
				if (is_resource($doc_encoded)) {
					$doc_encoded = stream_get_contents($doc_encoded);
				}
				if (strpos($doc_encoded,'Resource') !== FALSE) {
					// error?
					continue;
				}
				$doc = $doc_encoded;
			}
			// grabar el nuevo:
			$oEscritoAdjunto = new EscritoAdjunto($id_item);
			$oEscritoAdjunto->setId_escrito($id_escrito);
			$oEscritoAdjunto->setNom($nom);
			$oEscritoAdjunto->setAdjunto($doc);
			$oEscritoAdjunto->setTipo_doc($tipo_doc);
			$oEscritoAdjunto->DBGuardar();
		}
			
	}
}
if ($Qque == 'entradas') {
	// Adjuntos de entradas:
	$gesAdjuntosEntradasOrg = new GestorEntradaAdjuntoOrg();
	// Dividir en partes para que no colapse: memory ... bytes exhausted
	//$cEntradaAdjuntosOrg = $gesAdjuntosEntradasOrg->getEntradasAdjunto();
	
	$cantidad = 500;
	$anterior = 0;
	$aWhere = [ '_ordre' => 'id_item',
			'_limit' => $cantidad,
			'_offset' => $anterior,
	];
	$cEntradaAdjuntosOrg = $gesAdjuntosEntradasOrg->getEntradasAdjunto($aWhere);
	$num_filas = count($cEntradaAdjuntosOrg);
	while ($num_filas > 0) {
		$anterior += $num_filas;
		
		foreach($cEntradaAdjuntosOrg as $oEntradaAdjuntoOrg) {
			$id_item = $oEntradaAdjuntoOrg->getId_item();
			$id_entrada = $oEntradaAdjuntoOrg->getId_entrada();
			$nom = $oEntradaAdjuntoOrg->getNom();
			$res_adjunto = $oEntradaAdjuntoOrg->getAdjuntoResource();
			
			if (!empty($res_adjunto)) {
				rewind($res_adjunto);
				$doc_encoded = stream_get_contents($res_adjunto);
				if ( base64_encode(base64_decode($doc_encoded, true)) === $doc_encoded){
					// $data is valid
					$doc = base64_decode($doc_encoded);
				} else {
					// $data is NOT valid
					$doc = $doc_encoded;
				}
				// grabar el nuevo:
				$oEntradaAdjunto = new EntradaAdjunto($id_item);
				$oEntradaAdjunto->setId_entrada($id_entrada);
				$oEntradaAdjunto->setNom($nom);
				$oEntradaAdjunto->setAdjunto($doc);
				$oEntradaAdjunto->DBGuardar();
			}
				
		}
		
		$aWhere = [ '_ordre' => 'id_item',
				'_limit' => $cantidad,
				'_offset' => $anterior,
		];
		$cEntradaAdjuntosOrg = $gesAdjuntosEntradasOrg->getEntradasAdjunto($aWhere);
		$num_filas = count($cEntradaAdjuntosOrg);
	}
}
