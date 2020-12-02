TRAMITES
========


Filtros en la genración de expedientes:
---------------------------------------

(Oficinas)

1.- Borrador
$filtro = 'borrador_propio';

	estado = Expediente::ESTADO_BORRADOR;
	ponente = ConfigGlobal::mi_id_cargo();

2.- 
$filtro = 'borrador_oficina';

	estado = Expediente::ESTADO_BORRADOR;
	ponente = ( director = todos, resto = mi_cargo )


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
	
	
5.- 
$filtro = 'circulando';
	
	estado = Expediente::ESTADO_CIRCULANDO;
	ponente = ConfigGlobal::mi_id_cargo();

6.-
$filtro = 'seg_reunion'

	estado = Expediente::ESTADO_FIJAR_REUNION;
	f_reunion = 'IS NOT NULL';


7.- 
$filtro = 'acabados'

	'estado' = Expediente::ESTADO_ACABADO;
	'ok'     = 't';	// marcados por scdl con ok.
    'ponente' = ConfigGlobal::mi_id_cargo();  // solo los propios:

8.- 
$filtro = 'archivados';

	'estado' = Expediente::ESTADO_TERMINADO;

9.- 
$filtro = 'copias';

	'estado' = Expediente::ESTADO_COPIAS;
	'f_aprobacion' = 'IS NOT NULL';

	
10.-
$filtro = 'entrada';

11.- buscar....


(Secretaria)

1.-
$filtro = 'fijar_reunion'

	'estado' = Expediente::ESTADO_FIJAR_REUNION;
    'f_reunion' = 'IS NULL';

2.-
$filtro = 'seg_reunion';

	'estado' = Expediente::ESTADO_FIJAR_REUNION;
	'f_reunion' = 'IS NOT NULL';

3.-
$filtro = 'distribuir';

	'estado' = Expediente::ESTADO_ACABADO;
	'ok' = 'f'; // todavia sin marcar por scdl con ok.
	
4.- SOLO ESCRITOS (No expedientes)
$filtro = 'enviar';
??????????


5.-
$filtro = 'permanentes';

6.-
$filtro = 'pendientes';



Entradas
--------

7.- introducir entradas
$filtro = 'introducir';

8.-
$filtro = 'entrada_todos';

9.- distribución cr
$filtro = 'bypass';
