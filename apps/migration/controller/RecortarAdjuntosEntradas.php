<?php

// INICIO Cabecera global de URL de controlador *********************************

use core\ConfigGlobal;
use entradas\model\EntradaEntidadAdjunto;

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$oDbl = $GLOBALS['oDBT'];
$centro = 'dlb';

$sql0 = "SELECT id_item 
        FROM $centro.entrada_adjuntos 
        WHERE substr(nom, 0, 5) != 'mod_'
        ORDER BY id_item";
if (($oDblSt = $oDbl->Query($sql0)) === FALSE) {
    echo "Error de algún tipo..." . "<br>";
}

$num_adjuntos = $oDblSt->rowCount();

$dir_base = ConfigGlobal::directorio();

$tmpFilePath_1 = $dir_base . '/log/templates/example_1.xls';
$fp_1 = fopen($tmpFilePath_1, 'rb');
$contenido_doc1 = fread($fp_1, filesize($tmpFilePath_1));
$extension1 = 'xls';

$tmpFilePath_2 = $dir_base . '/log/templates/example_2.doc';
$fp_2 = fopen($tmpFilePath_2, 'rb');
$contenido_doc2 = fread($fp_2, filesize($tmpFilePath_2));
$extension2 = 'doc';

$tmpFilePath_3 = $dir_base . '/log/templates/example_3.odt';
$fp_3 = fopen($tmpFilePath_3, 'rb');
$contenido_doc3 = fread($fp_3, filesize($tmpFilePath_3));
$extension3 = 'odt';

$tmpFilePath_4 = $dir_base . '/log/templates/example_4.pdf';
$fp_4 = fopen($tmpFilePath_4, 'rb');
$contenido_doc4 = fread($fp_4, filesize($tmpFilePath_4));
$extension4 = 'pdf';

$tmpFilePath_5 = $dir_base . '/log/templates/example_5.doc';
$fp_5 = fopen($tmpFilePath_5, 'rb');
$contenido_doc5 = fread($fp_5, filesize($tmpFilePath_5));
$extension5 = 'doc';

//de 10 en 10
$init = 0;
$inc = 10;

for ($j = 0; $init < $num_adjuntos; $j++) {
    $sql = "SELECT id_item 
        FROM $centro.entrada_adjuntos 
        WHERE substr(nom, 0, 5) != 'mod_'
        ORDER BY id_item LIMIT $inc OFFSET $init";

    if (($oDblSt = $oDbl->Query($sql)) === FALSE) {
        echo "Error de algún tipo..." . "<br>";
    }

    $i = 0;
    foreach ($oDblSt as $row) {
        $i++;
        $id_item = $row['id_item'];
        $oEntradaAdjunto = new EntradaEntidadAdjunto($centro);
        $oEntradaAdjunto->setId_item($id_item);
        $oEntradaAdjunto->DBCargar();
        $nom_fichero = $oEntradaAdjunto->getNom();

        switch ($i) {
            case 1:
                $oEntradaAdjunto->setAdjunto($contenido_doc1);
                $extension = $extension1;
                break;
            case 2:
                $oEntradaAdjunto->setAdjunto($contenido_doc2);
                $extension = $extension2;
                break;
            case 3:
                $oEntradaAdjunto->setAdjunto($contenido_doc3);
                $extension = $extension3;
                break;
            case 4:
                $oEntradaAdjunto->setAdjunto($contenido_doc4);
                $extension = $extension4;
                break;
            default:
                $oEntradaAdjunto->setAdjunto($contenido_doc5);
                $extension = $extension5;
                break;
        }
        // cambiar el nombre: prefix = mod_, extensión:
        $nom_fichero = 'mod_' . preg_replace('/\..*$/', ".$extension", $nom_fichero);
        $oEntradaAdjunto->setNom($nom_fichero);

        if ($oEntradaAdjunto->DBGuardar() === FALSE) {
            echo "Error al guardar..." . "<br>";
        }

        echo "id_item: $id_item<br>";

        if ($i > 4) {
            $i = 0;
        }

    }
    $init = $init + $inc;
}