<?php

use web\Hash;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// FIN de  Cabecera global de URL de controlador ********************************


$oHash = new Hash();
$oHash->setcamposForm('db!tabla!clase!clase_plural!grupo!aplicacion');

?>
<form name="frm" action="apps/devel/controller/factory.php">
    <?= $oHash->getCamposHtml(); ?>
    Base de dades: <input type="text" name="db" value="tramity"><br>
    Nom de la taula: <input type="text" name="tabla" value=''><br>
    Nom de la Clase: <input type="text" name="clase" value=""><br>
    Plural de Nom de la Clase: <input type="text" name="clase_plural" value=""><br>
    app: <input type="text" name="grupo" value="usuarios"> (actividades,personas,ubis)<br>
    Nom de la Aplicacion: <input type="text" name="aplicacion" value="tramity"> (registro,...)<br>
    <br>
    <input TYPE="button" VALUE="<?= ucfirst(_("guardar cambios")); ?>" onclick="fnjs_enviar_formulario(this.form)">
</form>
