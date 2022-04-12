<?php

use documentos\model\Documento;
use documentos\model\entity\GestorDocumentoOrg;
use entradas\model\entity\EntradaAdjunto;
use entradas\model\entity\GestorEntradaAdjuntoOrg;
use escritos\model\entity\EscritoAdjunto;
use escritos\model\entity\GestorEscritoAdjuntoOrg;

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
	// Dividir en partes para que no colapse: memory ... bytes exhausted
	
	$cantidad = 5;
	$anterior = 0;
	$aWhere = [ 'tipo_doc' => Documento::DOC_UPLOAD,  
			'_ordre' => 'id_item',
			'_limit' => $cantidad,
			'_offset' => $anterior,
	];
	$cEscritoAdjuntosOrg = $gesAdjuntosEscritoOrg->getEscritoAdjuntos($aWhere);
	$num_filas = count($cEscritoAdjuntosOrg);
	while ($num_filas > 0) {
		$anterior += $num_filas;
		
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
					$doc = $doc_encoded;
				}
				// grabar el nuevo:
				$oEscritoAdjunto = new EscritoAdjunto($id_item);
				$oEscritoAdjunto->DBCarregar();
				$oEscritoAdjunto->setId_escrito($id_escrito);
				$oEscritoAdjunto->setNom($nom);
				$oEscritoAdjunto->setAdjunto($doc);
				$oEscritoAdjunto->setTipo_doc($tipo_doc);
				$oEscritoAdjunto->DBGuardar();
			}
		}
		
		$aWhere = [ '_ordre' => 'id_item',
				'_limit' => $cantidad,
				'_offset' => $anterior,
		];
		$cEscritoAdjuntosOrg = $gesAdjuntosEscritoOrg->getEscritoAdjuntos($aWhere);
		$num_filas = count($cEscritoAdjuntosOrg);
	}
}

if ($Qque == 'entradas') {
	// Adjuntos de entradas:
	$gesAdjuntosEntradasOrg = new GestorEntradaAdjuntoOrg();
	// Dividir en partes para que no colapse: memory ... bytes exhausted
	//$cEntradaAdjuntosOrg = $gesAdjuntosEntradasOrg->getEntradasAdjunto();
	
	$cantidad = 100;
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
				$oEntradaAdjunto->DBCarregar();
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

if ($Qque == 'documentos') {
	// Adjuntos de entradas:
	$gesDocumentosOrg = new GestorDocumentoOrg();
	// Dividir en partes para que no colapse: memory ... bytes exhausted
	//$cDocumentoAdjuntosOrg = $gesAdjuntosDocumentosOrg->getDocumentosAdjunto();
	
	$cantidad = 5;
	$anterior = 0;
	$aWhere = [ '_ordre' => 'id_doc',
			'_limit' => $cantidad,
			'_offset' => $anterior,
	];
	$cDocumentoOrg = $gesDocumentosOrg->getDocumentosOrg($aWhere);
	$num_filas = count($cDocumentoOrg);
	while ($num_filas > 0) {
		$anterior += $num_filas;
		
		foreach($cDocumentoOrg as $oDocumentoOrg) {
			$id_doc = $oDocumentoOrg->getId_doc();
			$nom = $oDocumentoOrg->getNom();
			$nombre_fichero = $oDocumentoOrg->getNombre_fichero();
			$creador = $oDocumentoOrg->getCreador('');
			$visibilidad = $oDocumentoOrg->getVisibilidad('');
			$tipo_doc = $oDocumentoOrg->getTipo_doc('');
			$f_upload = $oDocumentoOrg->getF_upload('');
			$res_documento = $oDocumentoOrg->getDocumentoResource();
			
			if (!empty($res_documento)) {
				rewind($res_documento);
				$doc_encoded = stream_get_contents($res_documento);
				if ( base64_encode(base64_decode($doc_encoded, true)) === $doc_encoded){
					$doc = base64_decode($doc_encoded);
				} else {
					// $data is NOT valid'
					$doc = $doc_encoded;
				}
				
				// grabar el nuevo:
				$oDocumento = new Documento($id_doc);
				$oDocumento->DBCarregar();
				$oDocumento->setNom($nom);
				$oDocumento->setNombre_fichero($nombre_fichero);
				$oDocumento->setCreador($creador);
				$oDocumento->setVisibilidad($visibilidad);
				$oDocumento->setTipo_doc($tipo_doc);
				$oDocumento->setF_upload($f_upload);
				$oDocumento->setDocumento($doc);
				$oDocumento->DBGuardar();
			}
				
		}
		
		$aWhere = [ '_ordre' => 'id_doc',
				'_limit' => $cantidad,
				'_offset' => $anterior,
		];
		$cDocumentosOrg = $gesDocumentosOrg->getDocumentosOrg($aWhere);
		$num_filas = count($cDocumentosOrg);
	}
}
