INTRO
=====


El servidor Davical es un servidor de calendarios que se instala a parte. Desde cualquier cliente de calendarios se puede acceder con el protocolo caldav.

El servicor tiene una página de administración (https://davical.red.local) para adminstrar los usuarios, grupos, recursos y permisos entre ellos.

Para poder acceder desde una aplicación de calendarios, habria que crear un nuevo usuario y darle  los permisos correspondientes. Los usuarios creados desde tramity, tienen todos el mismo password, distinto del del usuario, para que el progarama pueda acceder siempre.


Para acceder a una colleccion
------------------------------

	http://davical.red.local/caldav.php/oficina_agd/registro/
	

Este módulo tiene una clase: caldavclient.class, que permite acceder y realizar las peticiones desde PHP.

Además se requieren otras clases para:
- crear usuarios y grupos desde el php al hacer los cambios en tramity
- crear los calendarios.

La distribución de recursos es la siguiente: Cada oficina tiene dos calendarios: uno de la oficina y otro de registro. Es una cuestión de permisos. El calendario de registro solo puede crear y modificar (excepto marcar como terminado) la oficina de 'secretaria'. El calendario de la oficina, tienen permiso todos los usuarios de la oficina.

Los usuarios pertenecen a grupos, y los grupos son los que tienen los permisos para acceder a los calendarios.
El usuario 'secretaria' tiene permiso para todos los calendarios tipo registro.

La nomenclatura antigua (una instalación para una única dl) era:

	usuario:	of2sm
	grupo:		grupo_vsm
	recurso:	oficina_vsm
	(colecciones del recurso):
			 /oficina_vsm/oficina
			 /oficina_vsm/registro


Ahora, se permite hacer una instalación que sea multi-centros, o multi-dl. Por tanto hay que cambiar las nomenclaturas. Además, para los centros no tiene sentido hablar de oficinas, ni parece que convenga tener dos colecciones. Con una basta.

para los centro pordria ser:

	usuario:	agdmontagut_sd
	grupo:		agdmontagut_grupo
	recurso:	agdmontagut_oficina
	(colecciones del recurso):
			 /agdmontagut_oficina/oficina

y para las dl:

	usuario:	dlb_of2sm
	grupo:		dlb_grupo_vsm
	recurso:	dlb_oficina_vsm
	(colecciones del recurso):
			 /dlb_oficina_vsm/oficina
			 /dlb_oficina_vsm/registro


pasar los viejos a la nueva nomenclatura:
-----------------------------------------


 UPDATE usr SET username = REGEXP_REPLACE(username, '(oficina_)(.*)', 'dlb_\1\2') WHERE username::text ~ 'oficina_';
 UPDATE usr SET username = REGEXP_REPLACE(username, '(grupo_)(.*)', 'dlb_\1\2') WHERE username::text ~ 'grupo_';
 UPDATE usr SET username = REGEXP_REPLACE(username, '(.*)', 'dlb_\1') WHERE username::text !~ '(grupo|oficina)';

 UPDATE calendar_item SET uid = REGEXP_REPLACE(uid, '(.*)_oficina_(.*)', '\1_dlb_oficina_\2') WHERE uid::text ~ '_oficina_';

 UPDATE caldav_data SET dav_name = REGEXP_REPLACE(dav_name, 'oficina_dlb_(.*)', 'dlb_oficina_\1') WHERE dav_name::text ~ 'oficina_dlb';

 UPDATE caldav_data SET caldav_data = REGEXP_REPLACE(caldav_data,'UID:(.*?)@(.*)_oficina_(.*?)\\r','UID:\1@\2_dlb_oficina_\3')
   
UPDATE collection SET parent_container = REGEXP_REPLACE(parent_container, 'oficina_dlb(.*)', 'dlb_oficina\1') WHERE parent_container::text ~ 'oficina_dlb';
UPDATE collection SET dav_name = REGEXP_REPLACE(dav_name, 'oficina_dlb(.*)', 'dlb_oficina\1') WHERE dav_name::text ~ 'oficina_dlb';


                    



