<?php
namespace busquedas\model;

use PDO;
use web\Lista;

class VerTabla {
    
    /**
     * Id_sigla
     *
     * @var integer
     */
    private $id_sigla;

    /**
     * Id_lugar
     *
     * @var integer
     */
    private $id_lugar;

    /**
     * Prot_num
     *
     * @var integer
     */
    private $prot_num;

    /**
     * Prot_any
     *
     * @var integer
     */
    private $prot_any;


    
    /**
     * @return number
     */
    public function getId_sigla()
    {
        return $this->id_sigla;
    }

    /**
     * @return number
     */
    public function getId_lugar()
    {
        return $this->id_lugar;
    }

    /**
     * @return number
     */
    public function getProt_num()
    {
        return $this->prot_num;
    }

    /**
     * @return number
     */
    public function getProt_any()
    {
        return $this->prot_any;
    }

    /**
     * @param number $id_sigla
     */
    public function setId_sigla($id_sigla)
    {
        $this->id_sigla = $id_sigla;
    }

    /**
     * @param number $id_lugar
     */
    public function setId_lugar($id_lugar)
    {
        $this->id_lugar = $id_lugar;
    }

    /**
     * @param number $prot_num
     */
    public function setProt_num($prot_num)
    {
        $this->prot_num = $prot_num;
    }

    /**
     * @param number $prot_any
     */
    public function setProt_any($prot_any)
    {
        $this->prot_any = $prot_any;
    }

    
    public function mostrarTabla() {
        if ($this->id_sigla == $this->id_sigla) {
            return $this->tabla_entradas($donde, $sql, $orden);
        } else {
            return $this->tabla_salidas($donde, $sql, $orden);
        }
    }
    // ---------------------------------- tablas ----------------------------

    public function tabla_entradas($donde,$sql,$orden,$txt_titulo="",$atras="") {
        $oDbl = $GLOBALS['oDBT'];
        // entradas
        $e_s="e";

        $go_to="registro_tabla.php?tabla=entradas&donde=".urlencode($donde)."&sql=".urlencode($sql);
        if ($orden && $sql) $sql .= "ORDER BY " .$orden;
        if ($orden && $donde) $donde .= "ORDER BY " .$orden;

        if ($GLOBALS['oPerm']->have_perm("scl")) { 
            $a_botones=array( array( 'txt' => _('modificar'), 'click' =>"fnjs_modificar(\"#seleccionados_e\")" ) ,
                        array( 'txt' => _('eliminar'), 'click' =>"fnjs_borrar(\"#seleccionados_e\")" ) 
                        );
        }

        $a_botones[]=array( 'txt' => _('asunto oficina'), 'click' =>"fnjs_modificar_of(\"#seleccionados_e\")" ) ;
        $a_botones[]=array( 'txt' => _('detalle'), 'click' =>"fnjs_modificar_det(\"#seleccionados_e\")" ) ;

        $a_cabeceras=array( array('name'=>ucfirst(_("protocolo")),'formatter'=>'clickFormatter'),
                            ucfirst(_("origen")),
                            ucfirst(_("ref.")),
                            array('name'=>ucfirst(_("asunto")),'formatter'=>'clickFormatter2'),
                            ucfirst(_("ofic.")),
                            array('name'=>ucfirst(_("fecha doc.")),'class'=>'fecha'),
                            array('name'=>ucfirst(_("fecha entrada")),'class'=>'fecha')
                            );
        
        if (!empty($donde)) $donde="AND ".$donde;
        if (empty($sql)) {	
        $sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
                en.id_entrada,en.f_entrada,en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,en.mas,
                u.sigla,en.f_doc_entrada
                FROM escritos es LEFT JOIN entradas en USING (id_reg), lugares u
                WHERE en.id_lugar=u.id_lugar $donde
                ";
        }
        //echo "query: $sql<br>";
        $a_valores = array();
        $i=0;
        if (($oDblSt = $oDbl->query($sql)) !== false) {
        foreach ($oDblSt as $row) {
            $i++;
            $id_reg=$row["id_reg"];
            $prot_num=$row["prot_num"];
            $prot_any=any_2($row["prot_any"]);
            $asunto=$row["asunto"];
            $f_doc_entrada=date_any_2($row["f_doc_entrada"]);
            $anulado=$row["anulado"];
            $reservado=$row["reservado"];
            $detalle=$row["detalle"];
            $distribucion_cr=$row["distribucion_cr"];
            
            $protocolo="dlb ".$prot_num."/".$prot_any;
            
            $perm_asunto=permiso_detalle($id_reg,$reservado,"a");
            $perm_detalle=permiso_detalle($id_reg,$reservado,"d");

            $id_entrada=$row["id_entrada"];
            $f_entrada=date_any_2($row["f_entrada"]);
            $origen_sigla=$row["sigla"];
            $origen_prot_num=$row["o_prot_num"];
            $origen_prot_any=any_2($row["o_prot_any"]);
            $origen_mas=$row["mas"];
            
            $pagina_mod="scdl/registro/registro_modificar.php?id_reg=$id_reg&e_s=$e_s";
            $pagina="scdl/registro/asunto_of.php?nuevo=2&id_reg=$id_reg&e_s=$e_s&atras=$atras";


            $origen=$origen_sigla." ".$origen_prot_num."/".$origen_prot_any;
            if (!empty($origen_mas)) $origen .= " (".$origen_mas.")" ;
            
            // referencias
            $referencias=buscar_ref($id_reg,"f");
            
            // permisos para el asunto
            if ($perm_asunto==0) $asunto=_("reservado");
            // oficinas
            $oficinas = buscar_oficinas($id_reg,$id_entrada,"f");
            // asunto oficina, lo añado al asunto entre parentesis.
            $asunto_of= buscar_asunto_of($id_reg,$id_entrada,"f");
            if (!empty($asunto_of)) $asunto.=" (".$asunto_of.").";

            // permisos para el detalle
            if ($perm_detalle==0) $detalle=_("reservado");
            if ($reservado=="t" && $perm_asunto>1) $asunto=_("RESERVADO")." $asunto";
            if ($detalle && $perm_detalle>1 && $perm_asunto) $asunto.=" [".$detalle."].";

            if (!empty($anulado)) $asunto=_("ANULADO")." ($anulado) $asunto";
            
            if ($distribucion_cr=='t') {
                $sql_1= "SELECT m.descripcion
                    FROM destino_multiple m 
                    WHERE m.id_reg=$id_reg
                    ";
                //echo "query: $sql<br>";
                $oDblSt_query_1=$oDbl->query($sql_1);
                $descripcion=$oDblSt_query_1->fetchColumn();
                $asunto.=" <font style='color: Green;'>"._("dl y")." $descripcion</font>";
            }

            $a_valores[$i]['sel']="$id_reg#$e_s";
            //$a_valores[$i][1]=$protocolo;
            if ( $GLOBALS['oPerm']->have_perm("scdl")) {
                $a_valores[$i][1]=array( 'ira'=>$pagina_mod, 'valor'=>$protocolo);
            } else {
                $a_valores[$i][1]=$protocolo;
            }
            $a_valores[$i][2]=$origen;
            $a_valores[$i][3]=$referencias;

            $a_valores[$i][4]= array( 'ira2'=>$pagina, 'valor'=>$asunto);

            //$a_valores[$i][4]=$asunto;
            $a_valores[$i][5]=$oficinas;
            $a_valores[$i][6]=$f_doc_entrada;
            $a_valores[$i][7]=$f_entrada;
        }
        }
        /* ---------------------------------- html --------------------------------------- */
        if (empty($txt_titulo)) $txt_titulo= _("escritos recibidos en la Delegación");
        $txt="<h2 class=subtitulo>$txt_titulo</h2>";
        if ($i==0) {
            $txt.=_("no hay");
        } else {
            $txt.="<form id='seleccionados_e' name='seleccionados_e' action='' method='post'>
                <input type='hidden' name='permiso' value='3'>
                <input type='Hidden' name='go_to' value='$go_to' >
                <input type='Hidden' name='atras' value='$atras' >
                <input type='Hidden' name='mod' value='' >";
            $oTabla = new Lista();
            $oTabla->setId_tabla('func_reg_entradas');
            $oTabla->setCabeceras($a_cabeceras);
            $oTabla->setBotones($a_botones);
            $oTabla->setDatos($a_valores);
            $txt.=$oTabla->mostrar_tabla();
            $txt.="</form><br>";
        }
        return $txt;
    }


    public function tabla_salidas($donde,$sql,$orden,$txt_titulo="",$atras="") {
        $oDbl = $GLOBALS['oDBT'];
        // salidas
        $e_s="s";

        $go_to="registro_tabla.php?tabla=salidas&donde=".urlencode($donde)."&sql=".urlencode($sql);
        if ($orden && $sql) $sql .= "ORDER BY " .$orden;
        if ($orden && $donde) $donde .= "ORDER BY " .$orden;

        if ($GLOBALS['oPerm']->have_perm("scl")) { 
            $a_botones=array( array( 'txt' => _('modificar'), 'click' =>"fnjs_modificar(\"#seleccionados_s\")" ) ,
                        array( 'txt' => _('eliminar'), 'click' =>"fnjs_borrar(\"#seleccionados_s\")" ) 
                        );
        }

        $a_botones[]=array( 'txt' => _('asunto oficina'), 'click' =>"fnjs_modificar_of(\"#seleccionados_s\")" ) ;
        $a_botones[]=array( 'txt' => _('detalle'), 'click' =>"fnjs_modificar_det(\"#seleccionados_s\")" ) ;
                
        $a_cabeceras=array( array('name'=>ucfirst(_("protocolo")),'formatter'=>'clickFormatter'), ucfirst(_("destinos")),  ucfirst(_("ref.")), 
                array('name'=>ucfirst(_("asunto")),'formatter'=>'clickFormatter2'),
                   ucfirst(_("ofic.")),
                array('name'=>ucfirst(_("fecha doc.")),'class'=>'fecha'),
                array('name'=>ucfirst(_("aprobado")),'class'=>'fecha'),
                ucfirst(_("enviado")) // no puede ser class fecha, porque a veces se añade el modo de envio.
                   );
        
        if (!empty($donde)) $donde="AND ".$donde;
        //echo "sql 1: $sql<br>";
        if (empty($sql)) {
            $sql= "SELECT es.id_reg,es.prot_num,es.prot_any,es.asunto,es.f_doc,ap.id_modo_envio, x.modo_envio,es.anulado,es.reservado,es.detalle,es.distribucion_cr,
                    ap.id_salida,ap.f_aprobacion,ap.f_salida,m.descripcion
                    FROM escritos es, aprobaciones ap LEFT JOIN destino_multiple m USING (id_salida), x_modo_envio x
                    WHERE es.id_reg=ap.id_reg AND x.id_modo_envio=ap.id_modo_envio $donde
                    ";
        }
        //echo "sql: $sql<br>";
        
        $i=0;
        foreach ($oDbl->query($sql) as $row) {
            $i++;
            $id_reg=$row["id_reg"];
            $prot_num=$row["prot_num"];
            $prot_any=any_2($row["prot_any"]);
            $asunto=$row["asunto"];
            $anulado=$row["anulado"];
            $reservado=$row["reservado"];
            $detalle=$row["detalle"];
            $distribucion_cr=$row["distribucion_cr"];
            $f_doc=date_any_2($row["f_doc"]);
            $protocolo="dlb ".$prot_num."/".$prot_any;
            
            $perm_asunto=permiso_detalle($id_reg,$reservado,"a");
            $perm_detalle=permiso_detalle($id_reg,$reservado,"d");
            
            if ($distribucion_cr=='t') {
                $sql_1= "SELECT en.id_lugar as o_lugar,en.prot_num as o_prot_num,en.prot_any as o_prot_any,u.sigla
                FROM entradas en LEFT JOIN lugares u USING (id_lugar) 
                WHERE en.id_reg=$id_reg
                ";
                //echo "query: $sql<br>";
                $oEntrada=$oDbl->query($sql_1)->fetch(PDO::FETCH_OBJ);
                $origen_prot_num=$oEntrada->o_prot_num;
                $origen_prot_any=any_2($oEntrada->o_prot_any);
                $origen_sigla=$oEntrada->sigla;
                $protocolo=$origen_sigla." ".$origen_prot_num."/".$origen_prot_any;
            }
            
            $id_salida=$row["id_salida"];
            $f_aprobacion=date_any_2($row["f_aprobacion"]);
            $f_salida=date_any_2($row["f_salida"]);
            if ($row["id_modo_envio"]) $f_salida.=" (".$row["modo_envio"].")";
            
            $descripcion=$row["descripcion"];
            
            $pagina_mod="scdl/registro/registro_modificar.php?id_reg=$id_reg&e_s=$e_s";
            $pagina="scdl/registro/asunto_of.php?nuevo=2&id_reg=$id_reg&e_s=$e_s&atras=$atras";
            
            // destinos
            if (empty($descripcion)) {
                $destinos=buscar_destinos($id_reg);
            } else {
                $destinos=$descripcion;
            }
            // referencias
            $referencias=buscar_ref($id_reg,"f");
            
            // permisos para el asunto
            if ($perm_asunto==0) $asunto=_("reservado");
            // oficinas
            $oficinas = buscar_oficinas($id_reg,$id_salida,"f");
            // asunto oficina, lo añado al asunto entre parentesis.
            $asunto_of= buscar_asunto_of($id_reg,$id_salida,"f");
            if (!empty($asunto_of)) $asunto.=" (".$asunto_of.").";
            // permisos para el detalle
            if ($perm_detalle==0) $detalle=_("reservado");
            if ($reservado=="t" && $perm_asunto>1 ) $asunto=_("RESERVADO")." $asunto";
            if ($detalle && $perm_detalle>1 && $perm_asunto) $asunto.=" [".$detalle."].";
            if (!empty($anulado)) $asunto=_("ANULADO")." ($anulado) $asunto";

            $a_valores[$i]['sel']="$id_reg#$e_s";
            //$a_valores[$i][1]=$protocolo;
            if ( $GLOBALS['oPerm']->have_perm("scdl")) {
                $a_valores[$i][1]=array( 'ira'=>$pagina_mod, 'valor'=>$protocolo);
            } else {
                $a_valores[$i][1]=$protocolo;
            }
            $a_valores[$i][2]=$destinos;
            $a_valores[$i][3]=$referencias;
            //$a_valores[$i][4]=$asunto;
            $a_valores[$i][4]= array( 'ira2'=>$pagina, 'valor'=>$asunto);
            $a_valores[$i][5]=$oficinas;
            $a_valores[$i][6]=$f_doc;
            $a_valores[$i][7]=$f_aprobacion;
            $a_valores[$i][8]=$f_salida;
        }
        /* ---------------------------------- html --------------------------------------- */
        if (empty($txt_titulo)) $txt_titulo=_("escritos aprobados en la Delegación");
        $txt="<h2 class=subtitulo>$txt_titulo</h2>";
        if ($i==0) {
            $txt.=_("no hay");
        } else {
            $txt.="<form id='seleccionados_s' name='seleccionados_s' action='' method='post'>
                <input type='hidden' name='permiso' value='3'>
                <input type='Hidden' name='go_to' value='$go_to' >
                <input type='Hidden' name='atras' value='$atras' >
                <input type='Hidden' name='mod' value='' >";
            $oTabla = new Lista();
            $oTabla->setId_tabla('func_reg_salidas');
            $oTabla->setCabeceras($a_cabeceras);
            $oTabla->setBotones($a_botones);
            $oTabla->setDatos($a_valores);
            $txt.=$oTabla->mostrar_tabla();
            $txt.="</form><br>";
        }
        return $txt;
    }

}