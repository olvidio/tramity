<?php

use core\ViewTwig;

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
$a_campos = [
];

$oView = new ViewTwig('migration/controller');
echo $oView->renderizar('migration_index.html.twig', $a_campos);
	