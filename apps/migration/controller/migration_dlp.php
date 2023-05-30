<?php

use migration\model\ImportarBonita;
use migration\model\MigrationDlp;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

/*
1.- crear equivalencias oficinas, lugares, 
2.- 

poner entrdas
 	id_entrada 	id_reg 	f_entrada 	id_lugar 	prot_num 	prot_any 	mas 	f_doc_entrada
 	
 	
    id_entrada 	modo_entrada 	json_prot_origen 	f_entrada 	
    
    asunto_entrada 	json_prot_ref 	ponente 	resto_oficinas 	asunto 	detalle 	categoria 	visibilidad 	f_contestar 	bypass 	estado 	anulado
*/


// Equivalencias
$Q_que = (string)filter_input(INPUT_POST, 'que');

switch ($Q_que) {
    case 'aprobaciones_oficinas':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->oficinas_aprobaciones();
        echo "OK aprobaciones oficinas";
        break;
    case 'entradas_docs':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->docs_entradas();
        echo "OK entradas docs";
        break;
    case 'entradas_bypass':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->bypass_entradas();
        echo "OK entradas bypass";
        break;
    case 'entradas_permanentes':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->permanentes_entradas();
        echo "OK entradas permanentes";
        break;
    case 'entradas_of':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->oficinas_entradas();
        echo "OK entradas oficinas";
        break;
    case 'entradas2':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->completar_entradas();
        echo "OK entradas 2";
        break;
    case 'oficinas':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->crear_equivalencias_oficinas();
        echo "OK oficinas";
        break;
// --------------------
    case 'pasar_a_dlp_anexos':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->pasar_a_dlp_anexos();
        echo "OK pasado a dlp";
        break;
    case 'pasar_a_dlp':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->pasar_a_dlp();
        echo "OK pasado a dlp";
        break;
    case 'escritos_cancilleria':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->escritos_cancilleria();
        echo "OK modificar asunto escritos cancillería";
        break;
    case 'aprobaciones_ref':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->referencias_aprobaciones();
        echo "OK aprobaciones referencias";
        break;
    case 'aprobaciones_anexos':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->aprobaciones_anexos();
        echo "OK entradas anexos";
        break;
    case 'aprobaciones_destinos':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->destinos_aprobaciones();
        echo "OK aprobaciones destinos";
        break;
    case 'aprobaciones':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->copiar_aprobaciones();
        echo "OK aprobaciones";
        break;
    case 'entradas_cancilleria':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->entradas_cancilleria();
        echo "OK  modificar asunto entradas cancillería";
        break;
    case 'entradas_ref':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->entradas_ref();
        echo "OK entradas anexos";
        break;
    case 'entradas_anexos':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->entradas_anexos();
        echo "OK entradas anexos";
        break;
    case 'entradas':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->copiar_entradas();
        echo "OK entradas";
        break;
    case 'lugares_a_prod':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->pasar_lugares_a_produccion();
        echo "OK lugares a producción";
        break;
    case 'lugares':
        $oMigrationDlp = new MigrationDlp();
        $oMigrationDlp->crear_equivalencias_lugares();
        echo "OK lugares";
        break;
    case 'crear_tablas':
        $oImportarBonita = new ImportarBonita();
        $oImportarBonita->crear_inicio();
        echo "OK lugares";
        break;
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}