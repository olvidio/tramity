<?php
namespace busquedas\model;

use core\ConfigGlobal;


class EscritoLista {
    /**
     * 
     * @var string
     */
    private $modo;
    /**
     * 
     * @var string
     */
    private $filtro;
    /**
     * 
     * @var integer
     */
    private $id_expediente;
    /**
     * 
     * @var array
     */
    private $aWhere;
    /**
     * 
     * @var array
     */
    private $aOperador;
    /**
     * 
     * @var array
     */
    private $a_expedientes_nuevos = [];
    
    
    switch ($opcion) {
    case 7: // un protocolo concreto:
        if (empty($mas)) {
            switch($lugar) {
                case $id_dl:
                    $donde=" es.prot_num='".$prot_num."'";
                    if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND es.prot_any='".$prot_any."'"; }
                    //echo tabla_entradas($donde,"","");
                    echo tabla_salidas($donde,"","");
                    break;
                case $id_cr:
                    $donde=" en.id_lugar='".$id_cr."' AND en.prot_num='".$prot_num."'";
                    if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND en.prot_any='".$prot_any."'"; }
                    echo tabla_entradas($donde,"","");
                    break;
                default:
                    $donde=" en.id_lugar='".$lugar."' AND en.prot_num='".$prot_num."'";
                    if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND en.prot_any='".$prot_any."'"; }
                    echo tabla_entradas($donde,"","");
                    break;
            }
            $pag_mas="scdl/registro/registro_tabla.php?lugar=$lugar&prot_num=$prot_num&prot_any=$prot_any&opcion=$opcion&mas=1";
            echo "<p><span class=link onclick=fnjs_update_div('#main','$pag_mas')>"._("Buscar otros escritos con esta referencia").".</span></p>";
            
        // Buscar otros escritos con esta referencia
            switch($lugar) {
                case $id_dl:
                    $donde=" es.prot_num='".$prot_num."'";
                    if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND es.prot_any='".$prot_any."'"; }
                    // Escritos aprobados en la dl
                    echo tabla_salidas($donde,"","");
                    // Escritos recibidos en la dl
                    echo tabla_entradas($donde,"","");
                    //contesta a un escrito de 'dlb' => buscar en ref.
                    $donde_ref="AND ref.prot_num='".$prot_num."'";
                    if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde_ref.="AND ref.prot_any='".$prot_any."'"; }
                    $sql_en= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
                        en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
                        u.sigla, en.f_doc_entrada
                        FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u, referencias ref
                        WHERE en.id_lugar=u.id_lugar AND ref.id_reg=es.id_reg AND ref.id_lugar=$id_dl $donde_ref
                        ";
                    $txt_titulo=_("escritos recibidos en la Delegación con esta referencia");
                    echo tabla_entradas("",$sql_en,"",$txt_titulo);
                    
                    $sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
                            ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
                            FROM escritos es, aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), referencias ref, x_modo_envio x
                            WHERE es.id_reg=ap.id_reg AND ref.id_reg=es.id_reg
                                AND ref.id_lugar=$id_dl AND x.id_modo_envio=ap.id_modo_envio
                                $donde_ref
                            ";
                                $txt_titulo=_("escritos aprobados en la Delegación con esta referencia");
                                echo tabla_salidas("",$sql_sal,"",$txt_titulo);
                                break;
                case $id_cr:
                    $donde=" en.id_lugar='".$id_cr."' AND en.prot_num='".$prot_num."'";
                    if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND en.prot_any='".$prot_any."'"; }
                    echo tabla_entradas($donde,"","");
                    // escritos aprobados enviados a cr.
                    $donde=" AND dest.prot_num='".$prot_num."'";
                    if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND dest.prot_any='".$prot_any."'"; }
                    $sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,
                            es.distribucion_cr, ap.id_salida,ap.f_aprobacion,ap.f_salida
                            FROM escritos es, aprobaciones ap LEFT JOIN destinos dest USING (id_salida), x_modo_envio x
                            WHERE es.distribucion_cr='f' AND es.id_reg=ap.id_reg
                                AND dest.id_lugar=$id_cr AND x.id_modo_envio=ap.id_modo_envio
                                $donde
                            ";
                                $txt_titulo=_("escritos aprobados enviados a cr");
                                echo tabla_salidas("",$sql_sal,"",$txt_titulo);
                                // escritos de cr enviados a ctr.
                                $donde=" AND en.prot_num='".$prot_num."'";
                                if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde.=" AND en.prot_any='".$prot_any."'"; }
                                $sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
                            ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
                            FROM escritos es, aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), entradas en, x_modo_envio x
                            WHERE es.distribucion_cr='t' AND es.id_reg=ap.id_reg AND en.id_reg=es.id_reg
                                AND en.id_lugar=$id_cr AND x.id_modo_envio=ap.id_modo_envio
                                $donde
                            ";
                                $txt_titulo=_("escritos de cr enviados a los ctr");
                                echo tabla_salidas("",$sql_sal,"",$txt_titulo);
                                //contesta a un escrito de 'dlb' => buscar en ref.
                                $donde_ref="AND ref.prot_num='".$prot_num."'";
                                if (!empty($prot_any)) { $prot_any=any_4($prot_any); $donde_ref.="AND ref.prot_any='".$prot_any."'"; }
                                $sql_en= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
                        en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
                        u.sigla, en.f_doc_entrada
                        FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u, referencias ref
                        WHERE en.id_lugar=u.id_lugar AND ref.id_reg=es.id_reg AND ref.id_lugar=$id_cr $donde_ref
                        ";
                                $txt_titulo=_("escritos recibidos en la Delegación con esta referencia");
                                echo tabla_entradas("",$sql_en,"",$txt_titulo);
                                $sql_sal= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
                            ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
                            FROM escritos es, aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), referencias ref, x_modo_envio x
                            WHERE es.id_reg=ap.id_reg AND ref.id_reg=es.id_reg
                                AND ref.id_lugar=$lugar AND x.id_modo_envio=ap.id_modo_envio
                                $donde_ref
                            ";
                                $txt_titulo=_("escritos aprobados en la Delegación con esta referencia");
                                echo tabla_salidas("",$sql_sal,"",$txt_titulo);
                                break;
            }
        break;
        
        
            
    
}