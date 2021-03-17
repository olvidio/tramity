<?php

// INICIO Cabecera global de URL de controlador *********************************
use busquedas\model\Buscar;
use busquedas\model\VerTabla;
use core\ViewTwig;
use function core\any_2;
use entradas\model\GestorEntrada;
use lugares\model\entity\GestorLugar;
use usuarios\model\entity\GestorOficina;

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

$Qtipo_lista = (string) \filter_input(INPUT_POST, 'tipo_lista');
/*
if (empty($Qtipo_lista)) {
    $Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
    if (!empty($Qasunto)) {
        $Qtipo_lista = 4;
    } else {
        $Qtipo_lista = 7;
    }
}
*/
 $Qprot_num = '';
 $Qprot_any = '';
 $Qasunto = '';
    
$titulo = '';
$lista = '';
$oTabla = '';
switch ($Qtipo_lista) {
    case 54:
        // por asunto
        break;
    case 'any':
     // por año
        // Busco el id_lugar de cr.
        $gesLugares = new GestorLugar();
        $id_cr = $gesLugares->getId_cr();
        $Qany = (integer) \filter_input(INPUT_POST, 'any');
       
        $any2 = any_2($Qany);
        
        $oBuscar = new Buscar();
        $oBuscar->setId_lugar($id_cr);
        $oBuscar->setProt_any($any2);
        $aCollection = $oBuscar->getCollection($Qtipo_lista);
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCollection($cCollection);
        }
        break;
    case 'oficina':
     // por oficina
        // Busco el id_lugar de cr.
        $gesLugares = new GestorLugar();
        $id_cr = $gesLugares->getId_cr();
        $Qid_oficina = (integer) \filter_input(INPUT_POST, 'id_oficina');
        
        $oBuscar = new Buscar();
        $oBuscar->setId_lugar($id_cr);
        $oBuscar->setPonente($Qid_oficina);
        
        $aCollection = $oBuscar->getCollection($Qtipo_lista);
        foreach ($aCollection as $key => $cCollection) {
            $oTabla = new VerTabla();
            $oTabla->setKey($key);
            $oTabla->setCollection($cCollection);
        }
        break;
    case 'proto': // un protocolo concreto:
        // Busco el id_lugar de cr.
        $gesLugares = new GestorLugar();
        $id_cr = $gesLugares->getId_cr();

        $Qprot_num = (integer) \filter_input(INPUT_POST, 'prot_num');
        $Qprot_any = (string) \filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.
        $Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
        
        $Qprot_any = core\any_2($Qprot_any);
        
        $oBuscar = new Buscar();
        $oBuscar->setId_lugar($id_cr);
        
        $flag = 0;
        if (!empty($Qasunto)) {
            $oBuscar->setAsunto($Qasunto);
            $flag = 1;
        }
        if (!empty($Qprot_num)) {
            $oBuscar->setProt_num($Qprot_num);
            $flag = 1;
        } else {
            $Qprot_num = '';
        }
        if ($Qprot_any != '') { // para aceptar el 00
            $oBuscar->setProt_any($Qprot_any);
            $flag = 1;
        }
        if ($flag == 0) {
            $lista = _("Debe indicar el protocolo o el asunto");
        } else {
            $aCollection = $oBuscar->getCollection($Qtipo_lista);
            foreach ($aCollection as $key => $cCollection) {
                $oTabla = new VerTabla();
                $oTabla->setKey($key);
                $oTabla->setCollection($cCollection);
            }
        }
        break;
    case 'lst_oficinas':
        //oficinas posibles:
        $gesOficinas = new GestorOficina();
        $cOficinas = $gesOficinas->getArrayOficinas();
        
        $titulo = _("AVISOS DE CR DE NÚMERO BAJO: por oficinas");
        $lista = '';
        foreach ($cOficinas as $id_oficina => $sigla) {
            $ira = "apps/busquedas/controller/lista_permanentes.php?tipo_lista=oficina&id_oficina=$id_oficina";
            
            $lista .= "<button onclick=\"fnjs_update_div('#main','$ira')\" type=\"button\" class=\"btn btn-primary btn-block col-2\">";
            $lista .= sprintf(_("oficina %s"),$sigla);
            $lista .= "</button>";
        }
        break;
    case 'lst_years':
    default:
        //anys posibles:
        $gesEntradas = new GestorEntrada();
        $a_anys = $gesEntradas->posiblesYear();
        
        $a_any_min=current($a_anys);
        $any_min=$a_any_min;
        end($a_anys);
        $a_any_max=current($a_anys);
        $any_max=$a_any_max;
        reset($a_anys);
        
        $decena_min=floor($any_min/10)*10;
        $decena_max=floor($any_max/10)*10;
        
        $titulo = _("AVISOS DE CR DE NÚMERO BAJO: por años");
        foreach ($a_anys as $any) {
            $ira="apps/busquedas/controller/lista_permanentes.php?tipo_lista=any&any=$any";
            $btn = "<button onclick=\"fnjs_update_div('#main','$ira')\" type=\"button\" class=\"btn btn-outline-primary \" >";
            $btn .= sprintf(_("año %s"),$any);
            $btn .= "</button>";
            $lista_any[$any] = $btn;
        }
        $lista = '';
        for ($fila=0;$fila<10;$fila++) {
            $lista .= '<div class="row">';
            for ($decena=$decena_min;$decena<=$decena_max;$decena=$decena+10){
                $any_lista=(int) $decena+$fila;
                if (!empty($lista_any[$any_lista])) {
                    $txt = $lista_any[$any_lista] ;
                } else {
                    $btn = "<button type=\"button\" class=\"btn btn-outline-secondary\" disabled >";
                    $btn .= sprintf(_("año %s"),$any_lista);
                    $btn .= "</button>";
                    $txt = $btn;
                }
                $lista .= $txt;
            }
            $lista .= '</div>';
        }
}

        


$a_campos = [
    //'oHash' => $oHash,
    'prot_num' => $Qprot_num,
    'prot_any' => $Qprot_any,
    'asunto' => $Qasunto,
    'tipo_lista' => $Qtipo_lista,
    'titulo' => $titulo,
    'lista' => $lista,
    'oTabla' => $oTabla,
];

$oView = new ViewTwig('busquedas/controller');
echo $oView->renderizar('lista_permanentes.html.twig',$a_campos);