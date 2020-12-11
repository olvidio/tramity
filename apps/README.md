TRAMITY
=======

cargos
------

Toda la aplicación está centrada en los cargos: son los cargos los que firman. Cada cargo se asigna a un usuario. También puede asignarse a un único suplente.

Los usuarios que tienen asignados más de un cargo, sea como titular o como suplente, pueden cambiar de cargo por un desplegable.

No existe diferencia entre un usuario que actua propiamente o como suplente. Pueden actuar a la vez. La única comprobación es una alerta que avisa al entrar el usuario titular de que existe un suplente activo.

Para poder definir bien los trámites, se añaden unos cargos especiales (que no tienen ni oficina ni titular):

	1  ponente 
	2  oficiales (del ponente)
	3  varias
	4  todos_d
	5  vºbº vcd (listo para reunión)
	6  distribuir (scdl distribuir)
	7  convocar reunion

Se definen las oficinas como manera de agrupar los cargos. En algunos casos se puede compartir la información entre miembros de una misma oficina. El director siempre puede ver todos los expedientes de los cargos de su oficina.

lugares
-------

Con este nombre se designa a los ctr, dl y cr a los que se pueden enviar escritos. Para cada uno está prvisto que pueda tener una dirección de email donde enviar los escritos. Además se añade la opción para enviar como pdf o xml (en el caso que tengan una aplicación similar -o ésta misma- se envian los campos por separado para ser introducidos automáticamente en la aplicación). También se puede tener la clave pública para enviar cifrado.

Grupos de lugares: Se pueden hacer grupos de lugares y funcinan como listas de correo. Se ha des-estimado hacer los grupos en función de los parámetros de definicion del centro: tipo de centro o tipo de labor, por la gran variabilidad...

TRAMITES
========

Los trámites se definen como una secuencia de cargos que deben ir firmando. Existen los cargos: 'oficiales', 'varias' y 'todos_d' en donde se pueden añadir o quitar para cada expediente concreto.

 Al ir completando las firmas para cada fase del trámite, se va cambiado el estado del expediente, de manera que se puedan hacer listados de los expedientes aplicado diversos filtros a la hora de buscar:

Filtros en la generación de expedientes:
---------------------------------------

Definir un campo (estado), que indique el punto del itinerario en que se encuentra:

* Borrador (antes de circular, mientras lo trabaja el ponenente)
* Circulando (está pasando a firmas)
* Fijar reunión
* Acabado (una vez firmado -aprobado o rechazado-, antes de enviar escritos...)
* Terminado (una vez hechas todas las "acciones": enviar escritos...)
* Copias (para marcar que es copia de otro, y para que salga en la selección de copias).

    ESTADO_BORRADOR          = 1;
    ESTADO_CIRCULANDO        = 2;
    ESTADO_FIJAR_REUNION     = 3;
    ESTADO_ACABADO           = 4;
    ESTADO_TERMINADO         = 5;
    ESTADO_COPIAS            = 6;


(Oficinas)

1.- Borrador(propio)

$filtro = 'borrador_propio';

	estado = Expediente::ESTADO_BORRADOR;
	ponente = ConfigGlobal::mi_id_cargo();

2.- borrador (oficina)

$filtro = 'borrador_oficina';

	estado = Expediente::ESTADO_BORRADOR;
	ponente = ( director: todos los de la oficina, resto: mi_cargo )


3.- para firmar

$filtro = 'firmar';

expediente:

	estado = Expediente::ESTADO_CIRCULANDO;
	
firma:

	id_cargo = ConfigGlobal::mi_id_cargo();
	tipo = Firma::TIPO_VOTO;
	valor = 'IS NULL'; Firma::V_VISTO; Firma::V_A_ESPERA;

4.- para reunión

$filtro = 'reunion';

expediente:

	estado = Expediente::ESTADO_FIJAR_REUNION;
	f_reunion = 'IS NOT NULL';

firma:

	id_cargo = ConfigGlobal::mi_id_cargo();
	tipo = Firma::TIPO_VOTO;
	valor = 'IS NULL'; Firma::V_VISTO; Firma::V_A_ESPERA;
	
	
5.- circulando
 
$filtro = 'circulando';
	
	estado = Expediente::ESTADO_CIRCULANDO;
	ponente = (director: todos los dla oficina, resto: id_cargo);

6.- reunión día

$filtro = 'seg_reunion'

(todos). Los que falta firmar en otro color. 

	estado = Expediente::ESTADO_FIJAR_REUNION;
	f_reunion = 'IS NOT NULL';


7.- acabados

$filtro = 'acabados'

	'estado' = Expediente::ESTADO_ACABADO;
	'ok'     = 't';	// marcados por scdl con ok.
	'ponente' = ConfigGlobal::mi_id_cargo();  // solo los propios:

8.- archivados

$filtro = 'archivados';

	'estado' = Expediente::ESTADO_TERMINADO;
	>>>>TODOS????????

9.- copipas

$filtro = 'copias';

	'estado' = Expediente::ESTADO_COPIAS;
	>>>>TODOS????????

	
(Secretaria)

1.- fijar_reunión

$filtro = 'fijar_reunion'

	'estado' = Expediente::ESTADO_FIJAR_REUNION;
    'f_reunion' = 'IS NULL';

2.- seguimiento reuinión

$filtro = 'seg_reunion';

	'estado' = Expediente::ESTADO_FIJAR_REUNION;
	'f_reunion' = 'IS NOT NULL';

3.- distribuir

$filtro = 'distribuir';

	'estado' = Expediente::ESTADO_ACABADO;
	'ok' = 'f'; // todavia sin marcar por scdl con ok.
	
4.- enviar ((SOLO ESCRITOS (No expedientes) ))

$filtro = 'enviar';

	'accion' => Escrito::ACCION_ESCRITO,
	'ok' => Escrito::OK_OFICINA,
	'f_salida' => 'IS NULL', 'HOY'
	
5.- permanentes

$filtro = 'permanentes';

6.- pendientes

$filtro = 'pendientes';



Entradas
--------

Definir un campo (estado) para las entradas, que indique el punto del itinerario en que se encuentra:

* ingresado [introducir] (valor inicial, hasta el ok del vcd. Lo hace secretaría, o automáticamente si viene por xml)
* admitido (una vez se da el ok del vcd)
* asignado (secretaría añade la información: ponente, fechas etc.)
* aceptado (ok del scdl)
* oficinas (cada oficina ve las que le corresponden)
	 
    ESTADO_INGRESADO         = 1;
    ESTADO_ADMITIDO          = 2;
    ESTADO_ASIGNADO          = 3;
    ESTADO_ACEPTADO          = 4;
    ESTADO_OFICINAS          = 5; (No se usa, en la práctica es el Aceptado)

(secretaria)
7.- E: introducir

$filtro = 'en_ingresado';
	
	$aWhere['estado'] = Entrada::ESTADO_INGRESADO;

(secretaria)
8.- E: asignar

$filtro = 'en_admitido';

	$aWhere['estado'] = Entrada::ESTADO_ASIGNADO;

(secretaria + scdl)
9.- E: aceptar

$filtro = 'en_asignado';

	$aWhere['estado'] = Entrada::ESTADO_ASIGNADO;

(secretaria)
10.- distribución cr

$filtro = 'bypass';

(usuarios + vcd)
10.- E: admitir

$filtro = 'en_ingresado';

	$aWhere['estado'] = Entrada::ESTADO_INGRESADO;

(usuarios)
11.- entradas

$filtro = 'en_aceptado';

	$aWhere['ponente'] = ConfigGlobal::mi_id_cargo();
	$aWhere['estado'] = Entrada::ESTADO_ACEPTADO;


