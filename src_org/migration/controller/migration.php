<?php

use migration\model\Migration;

// INICIO Cabecera global de URL de controlador *********************************

require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

/*
1.- crear equivalencias oficinas, lugares, 
2.- 

poner entrdas
 	id_entrada 	id_reg 	f_entrada 	id_lugar 	prot_num 	prot_any 	mas 	f_doc_entrada
 	
 	
    id_entrada 	modo_entrada 	json_prot_origen 	f_entrada 	
    
    asunto_entrada 	json_prot_ref 	ponente 	resto_oficinas 	asunto 	detalle 	categoria 	visibilidad 	oF_contestar 	bypass 	estado 	anulado
*/


// Equivalencias
$Q_que = (string)filter_input(INPUT_POST, 'que');

switch ($Q_que) {
    case 'aprobaciones_oficinas':
        $oMigration = new Migration();
        $oMigration->oficinas_aprobaciones();
        echo "OK aprobaciones oficinas";
        break;
    case 'aprobaciones_referencias':
        $oMigration = new Migration();
        $oMigration->referencias_aprobaciones();
        echo "OK aprobaciones referencias";
        break;
    case 'aprobaciones_destinos':
        $oMigration = new Migration();
        $oMigration->destinos_aprobaciones();
        echo "OK aprobaciones destinos";
        break;
    case 'aprobaciones':
        $oMigration = new Migration();
        $oMigration->copiar_aprobaciones();
        echo "OK aprobaciones";
        break;
    case 'entradas_docs':
        $oMigration = new Migration();
        $oMigration->docs_entradas();
        echo "OK entradas docs";
        break;
    case 'entradas_bypass':
        $oMigration = new Migration();
        $oMigration->bypass_entradas();
        echo "OK entradas bypass";
        break;
    case 'entradas_permanentes':
        $oMigration = new Migration();
        $oMigration->permanentes_entradas();
        echo "OK entradas permanentes";
        break;
    case 'entradas_ref':
        $oMigration = new Migration();
        $oMigration->referencias_entradas();
        echo "OK entradas referenias";
        break;
    case 'entradas_of':
        $oMigration = new Migration();
        $oMigration->oficinas_entradas();
        echo "OK entradas oficinas";
        break;
    case 'entradas2':
        $oMigration = new Migration();
        $oMigration->completar_entradas();
        echo "OK entradas 2";
        break;
    case 'entradas':
        $oMigration = new Migration();
        $oMigration->copiar_entradas();
        echo "OK entradas";
        break;
    case 'lugares':
        $oMigration = new Migration();
        $oMigration->crear_equivalencias_lugares();
        echo "OK lugares";
        break;
    case 'oficinas':
        $oMigration = new Migration();
        $oMigration->crear_equivalencias_oficinas();
        echo "OK oficinas";
        break;


}