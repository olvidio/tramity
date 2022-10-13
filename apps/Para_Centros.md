Cambios introducidos para que el programa funcione para los centros
===================================================================

A. controladores y vistas
-------------------------

En la definición de se incluye el ámbito (ctr, dl). Este parámetro está accesible en:

$_SESSION['oConfig']->getAmbito()

valores en Cargo:

Cargo::AMBITO_CG = 1;
Cargo::AMBITO_CR = 2;
Cargo::AMBITO_DL = 3; //"dl"
Cargo::AMBITO_CTR = 4;

En los controladores:

if ($_SESSION['oConfig']->getAmbito() == Cargo::AMBITO_CTR) {

En las vistas, al incio de los campos, uso la varliable 'vista_dl' (true/false) para indicar las cosas que hay que ver
sólo en el ambito de la dl.

{% if vista_dl %}

B. Nomenclatura etherpad
------------------------

Añado el nombre del centro o la sigla de la dl (separando con un asterisco) antes del prefijo y el id del escrito:

$this->id_escrito = $nom_ctr."*".$prefix.$id;