<?php

// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************
// FIN de  Cabecera global de URL de controlador ********************************

?>
<span class="link" onclick="fnjs_update_div('#main','apps/davical/controller/proves.php?que=crear_usuario')" >
 	1: Crear Usuario
</span>
<br>
<span class="link" onclick="fnjs_update_div('#main','apps/davical/controller/proves.php?que=crear_usuario&user_no=1002')" >
 	2: Modicficar Usuario (1002)
</span>
<br>
Oficina
<hr>
<span class="link" onclick="fnjs_update_div('#main','apps/davical/controller/proves.php?que=crear_resource')" >
 	3: Crear Resuorce
</span>
<br>
<span class="link" onclick="fnjs_update_div('#main','apps/davical/controller/proves.php?que=crear_resource&user_no=1005')" >
 	4: Modificar Resuorce (1005)
</span>
<br>
<span class="link" onclick="fnjs_update_div('#main','apps/davical/controller/proves.php?que=crear_coleccion&user_no=1005')" >
 	5: Crear Coleccion para oficina_vsm (1005)
</span>
<br>
<span class="link" onclick="fnjs_update_div('#main','apps/davical/controller/proves.php?que=crear_oficina&oficina=vsg')" >
 	6: Oficina Tot (vsg)
</span>
<br>
<span class="link" onclick="fnjs_update_div('#main','apps/davical/controller/proves.php?que=add_user&oficina=vsg&user=of1sg')" >
 	7: Add user (of1sg) to Oficina Tot (vsg)
</span>
<br>
<hr>

<span class="link" onclick="fnjs_update_div('#main','apps/davical/controller/proves.php?que=eliminar_usuario')" >
 	10: Eliminar Usuario
</span>
<br>
<span class="link" onclick="fnjs_update_div('#main','apps/davical/controller/proves.php?que=eliminar_oficina')" >
 	11: Eliminar oficina
</span>
<br>