<?php
use core\ViewTwig;
use function core\is_true;
use entradas\model\Entrada;
use entradas\model\entity\EntradaBypass;
use entradas\model\entity\EntradaDB;
use entradas\model\entity\GestorEntradaBypass;
use envios\model\Enviar;
use lugares\model\entity\GestorGrupo;
use web\DateTimeLocal;
use web\Protocolo;

// INICIO Cabecera global de URL de controlador *********************************
	require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
	require_once ("apps/core/global_object.inc");
// Crea los objectos por esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qque = (string) \filter_input(INPUT_POST, 'que');
$Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');

$Qorigen = (integer) \filter_input(INPUT_POST, 'origen');
$Qprot_num_origen = (integer) \filter_input(INPUT_POST, 'prot_num_origen');
$Qprot_any_origen = (integer) \filter_input(INPUT_POST, 'prot_any_origen');
$Qprot_mas_origen = (string) \filter_input(INPUT_POST, 'prot_mas_origen');

$Qasunto_e = (string) \filter_input(INPUT_POST, 'asunto_e');
$Qf_escrito = (string) \filter_input(INPUT_POST, 'f_escrito');
$Qasunto = (string) \filter_input(INPUT_POST, 'asunto');
$Qf_entrada = (string) \filter_input(INPUT_POST, 'f_entrada');

$Qdetalle = (string) \filter_input(INPUT_POST, 'detalle');
$Qponente = (integer) \filter_input(INPUT_POST, 'ponente');
$Qa_firmas = (array)  \filter_input(INPUT_POST, 'oficinas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$Qcategoria = (integer) \filter_input(INPUT_POST, 'categoria');
$Qvisibiliad = (integer) \filter_input(INPUT_POST, 'visibilidad');

$Qplazo = (string) \filter_input(INPUT_POST, 'plazo');
$Qf_plazo = (string) \filter_input(INPUT_POST, 'f_plazo');
$Qbypass = (string) \filter_input(INPUT_POST, 'bypass');
$QAdmitir = (string) \filter_input(INPUT_POST, 'admitir');

/* genero un vector con todas las referencias. Antes ya llegaba así, pero al quitar [] de los nombres, legan uno a uno.  */
$Qa_referencias = (array)  \filter_input(INPUT_POST, 'referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_prot_num_referencias = (array)  \filter_input(INPUT_POST, 'prot_num_referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_prot_any_referencias = (array)  \filter_input(INPUT_POST, 'prot_any_referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$Qa_prot_mas_referencias = (array)  \filter_input(INPUT_POST, 'prot_mas_referencias', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

$error_txt = '';
$jsondata = [];
switch($Qque) {
    case 'guardar_destinos':
        $gesEntradasBypass = new GestorEntradaBypass();
        $cEntradasBypass = $gesEntradasBypass->getEntradasBypass(['id_entrada' => $Qid_entrada]);
        if (count($cEntradasBypass) > 0) {
            // solo debería haber una:
            $oEntradaBypass = $cEntradasBypass[0];
            $oEntradaBypass->DBCarregar();
        } else {
            $oEntradaBypass = new EntradaBypass();
            $oEntradaBypass->setId_entrada($Qid_entrada);
        }
        //Qasunto.
        $oEntrada = new EntradaDB($Qid_entrada);
        $oEntrada->DBCarregar();
        $oEntrada->setAsunto($Qasunto);
        $oEntrada->DBGuardar();
        // destinos
        $Qgrupo_dst = (string) \filter_input(INPUT_POST, 'grupo_dst');
        $Qf_salida = (string) \filter_input(INPUT_POST, 'f_salida');
        
        // genero un vector con todos los grupos.
        $Qa_grupos = (array)  \filter_input(INPUT_POST, 'grupos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        /* genero un vector con todas las referencias. Antes ya llegaba así, pero al quitar [] de los nombres, legan uno a uno.  */
        $Qa_destinos = (array)  \filter_input(INPUT_POST, 'destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Qa_prot_num_destinos = (array)  \filter_input(INPUT_POST, 'prot_num_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Qa_prot_any_destinos = (array)  \filter_input(INPUT_POST, 'prot_any_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $Qa_prot_mas_destinos = (array)  \filter_input(INPUT_POST, 'prot_mas_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        
        // Si esta marcado como grupo de destinos, o destinos individuales.
        if (core\is_true($Qgrupo_dst)) {
            $descripcion = '';
            $gesGrupo = new GestorGrupo();
            $a_grupos = $gesGrupo->getArrayGrupos();
            foreach ($Qa_grupos as $id_grupo) {
                $descripcion .= empty($descripcion)? '' : ' + ';
                $descripcion .= $a_grupos[$id_grupo];
            }
            $oEntradaBypass->setId_grupos($Qa_grupos);
            $oEntradaBypass->setDescripcion($descripcion);
        } else {
            $aProtDst = [];
            foreach ($Qa_destinos as $key => $id_lugar) {
                $prot_num = $Qa_prot_num_destinos[$key];
                $prot_any = $Qa_prot_any_destinos[$key];
                $prot_mas = $Qa_prot_mas_destinos[$key];
                
                if (!empty($id_lugar)) {
                    $oProtDst = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                    $aProtDst[] = $oProtDst->getProt();
                }
            }
            $oEntradaBypass->setJson_prot_destino($aProtDst);
            $oEntradaBypass->setId_grupos();
            $oEntradaBypass->setDescripcion('x'); // no puede ser null.
        }
        $oEntradaBypass->setF_salida($Qf_salida);
        if ($oEntradaBypass->DBGuardar() === FALSE ) {
            $error_txt .= $oEntradaBypass->getErrorTxt();
        }
        
        if (!empty($error_txt)) {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = TRUE;
        }
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        break;
    case 'f_entrada':
        if ($Qf_entrada == 'hoy') {
            $oHoy = new DateTimeLocal();
            $Qf_entrada = $oHoy->getFromLocal();
        }
        $oEntrada = new Entrada($Qid_entrada);
        $oEntrada->DBCarregar();
        $oEntrada->setF_entrada($Qf_entrada);
        if (empty($Qf_entrada)) {
            $oEntrada->setEstado(Entrada::ESTADO_INGRESADO);
        } else {
            $oEntrada->setEstado(Entrada::ESTADO_ADMITIDO);
        }
        $oEntrada->DBGuardar();
        
        break;
    case 'upload_adjunto':
        
        if (empty($_FILES['adjuntos'])) {
            // Devolvemos un array asociativo con la clave error en formato JSON como respuesta
            echo json_encode(['error'=>'No hay ficheros para realizar upload.']);
            // Cancelamos el resto del script
            return;
        }
        $respuestas = [];
        $ficheros = $_FILES['adjuntos'];
        
        $a_error = $ficheros['error'];
        $a_names = $ficheros['name'];
        $a_tmp = $ficheros['tmp_name'];
        foreach ($a_names as $key => $name) {
            if ($a_error[$key] > 0) {
                $respuestas = [ "error" => $a_error[$key] ];
            } else {
                $path_parts = pathinfo($name);
                
                $nom=$path_parts['filename'];
                // puede no existir la extension
                $extension=empty($path_parts['extension'])? '' : $path_parts['extension'];

                $userfile= $a_tmp[$key];
                
                $fichero=file_get_contents($userfile);
                
            }
            $respuestas = ["ok" => "Ja está"];
            
            // Devolvemos el array asociativo en formato JSON como respuesta
        }
        echo json_encode($respuestas);
        
        break;
    case 'guardar':
        if (!empty($Qid_entrada)) {
            $oEntrada = new Entrada($Qid_entrada);
            $oEntrada->DBCarregar();
        } else {
            $oEntrada = new Entrada();
        }
        
        $oEntrada->setModo_entrada(Entrada::MODO_MANUAL);
        
        $oProtOrigen = new Protocolo($Qorigen, $Qprot_num_origen, $Qprot_any_origen, $Qprot_mas_origen);
        $oEntrada->setJson_prot_origen($oProtOrigen->getProt());
        
        $aProtRef = [];
        foreach ($Qa_referencias as $key => $id_lugar) {
            $prot_num = $Qa_prot_num_referencias[$key];
            $prot_any = $Qa_prot_any_referencias[$key];
            $prot_mas = $Qa_prot_mas_referencias[$key];
            
            if (!empty($id_lugar)) {
                $oProtRef = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                $aProtRef[] = $oProtRef->getProt();
            }
        }
        $oEntrada->setJson_prot_ref($aProtRef);
 
        $oEntrada->setAsunto_entrada($Qasunto_e);
        $oEntrada->setF_documento($Qf_escrito,TRUE);
        $oEntrada->setAsunto($Qasunto);
        $oEntrada->setF_entrada($Qf_entrada);

        $oEntrada->setDetalle($Qdetalle);
        $oEntrada->setPonente($Qponente);
        $oEntrada->setResto_oficinas($Qa_firmas);

        $oEntrada->setCategoria($Qcategoria);
        $oEntrada->setVisibilidad($Qvisibiliad);

        
        switch ($Qplazo) {
            case 'hoy':
                $oEntrada->setF_contestar('');
                break;
            case 'normal':
                $plazo_normal = $_SESSION['oConfig']->getPlazoNormal();
                $periodo = 'P'.$plazo_normal.'D';
                $oF = new DateTimeLocal();
                $oF->add(new DateInterval($periodo));
                $oEntrada->setF_contestar($oF);
                break;
            case 'rápido':
                $plazo_rapido = $_SESSION['oConfig']->getPlazoRapido();
                $periodo = 'P'.$plazo_rapido.'D';
                $oF = new DateTimeLocal();
                $oF->add(new DateInterval($periodo));
                $oEntrada->setF_contestar($oF);
                break;
            case 'urgente':
                $plazo_urgente = $_SESSION['oConfig']->getPlazoUrgente();
                $periodo = 'P'.$plazo_urgente.'D';
                $oF = new DateTimeLocal();
                $oF->add(new DateInterval($periodo));
                $oEntrada->setF_contestar($oF);
                break;
            case 'fecha':
                $oEntrada->setF_contestar($Qf_plazo);
                break;
        } 
            
        if (is_true($QAdmitir)) {
            // pasa directamente a asigado. Se supone que el admitido lo ha puesto el vcd.
            // en caso de ponerlo secretaria, al guardar pasa igualmente a asignado.
            $estado = Entrada::ESTADO_ASIGNADO;
        } else {
            $estado = Entrada::ESTADO_INGRESADO;
        }
        // si es el scdl, puede ser que pase a aceptado:
        if ($Qfiltro == 'en_asignado') {
            $estado = Entrada::ESTADO_ACEPTADO;
        }
        $oEntrada->setEstado($estado);
       
        $oEntrada->setBypass($Qbypass);
        if ($oEntrada->DBGuardar() === FALSE ) {
            $error_txt .= $oEntrada->getErrorTxt();
        } else {
            $id_entrada = $oEntrada->getId_entrada();
            //////// BY PASS //////
            if ($Qbypass && $Qid_entrada) {
                $gesEntradasBypass = new GestorEntradaBypass();
                $cEntradasBypass = $gesEntradasBypass->getEntradasBypass(['id_entrada' => $Qid_entrada]);
                if (count($cEntradasBypass) > 0) {
                    // solo debería haber una:
                    $oEntradaBypass = $cEntradasBypass[0];
                    $oEntradaBypass->DBCarregar();
                } else {
                    $oEntradaBypass = new EntradaBypass();
                    $oEntradaBypass->setId_entrada($Qid_entrada);
                }
                //Qasunto.
                $oEntrada = new EntradaDB($Qid_entrada);
                $oEntrada->DBCarregar();
                $oEntrada->setAsunto($Qasunto);
                $oEntrada->DBGuardar();
                // destinos
                $Qgrupo_dst = (string) \filter_input(INPUT_POST, 'grupo_dst');
                $Qf_salida = (string) \filter_input(INPUT_POST, 'f_salida');
                
                // genero un vector con todos los grupos.
                $Qa_grupos = (array)  \filter_input(INPUT_POST, 'grupos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                /* genero un vector con todas las referencias. Antes ya llegaba así, pero al quitar [] de los nombres, legan uno a uno.  */
                $Qa_destinos = (array)  \filter_input(INPUT_POST, 'destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $Qa_prot_num_destinos = (array)  \filter_input(INPUT_POST, 'prot_num_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $Qa_prot_any_destinos = (array)  \filter_input(INPUT_POST, 'prot_any_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $Qa_prot_mas_destinos = (array)  \filter_input(INPUT_POST, 'prot_mas_destinos', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                
                // Si esta marcado como grupo de destinos, o destinos individuales.
                if (core\is_true($Qgrupo_dst)) {
                    $descripcion = '';
                    $gesGrupo = new GestorGrupo();
                    $a_grupos = $gesGrupo->getArrayGrupos();
                    foreach ($Qa_grupos as $id_grupo) {
                        $descripcion .= empty($descripcion)? '' : ' + ';
                        $descripcion .= $a_grupos[$id_grupo];
                    }
                    $oEntradaBypass->setId_grupos($Qa_grupos);
                    $oEntradaBypass->setDescripcion($descripcion);
                } else {
                    $aProtDst = [];
                    foreach ($Qa_destinos as $key => $id_lugar) {
                        $prot_num = $Qa_prot_num_destinos[$key];
                        $prot_any = $Qa_prot_any_destinos[$key];
                        $prot_mas = $Qa_prot_mas_destinos[$key];
                        
                        if (!empty($id_lugar)) {
                            $oProtDst = new Protocolo($id_lugar, $prot_num, $prot_any, $prot_mas);
                            $aProtDst[] = $oProtDst->getProt();
                        }
                    }
                    $oEntradaBypass->setJson_prot_destino($aProtDst);
                    $oEntradaBypass->setId_grupos();
                    $oEntradaBypass->setDescripcion('x'); // no puede ser null.
                }
                if ($oEntradaBypass->DBGuardar() === FALSE ) {
                    $error_txt .= $oEntradaBypass->getErrorTxt();
                }
                
                if (!empty($error_txt)) {
                    $jsondata['success'] = FALSE;
                    $jsondata['mensaje'] = $error_txt;
                } else {
                    $jsondata['success'] = TRUE;
                }
            } else {
                // borrar si hubiera habido. ( o no?)
            }
        }
        
        
        if (!empty($error_txt)) {
            $jsondata['success'] = FALSE;
            $jsondata['mensaje'] = $error_txt;
        } else {
            $jsondata['success'] = TRUE;
            $jsondata['id_entrada'] = $id_entrada;
            $a_cosas = [ 'id_entrada' => $id_entrada, 'filtro' => $Qfiltro];
            $pagina_mod = web\Hash::link('apps/entradas/controller/entrada_form.php?'.http_build_query($a_cosas));
            $jsondata['pagina_mod'] = $pagina_mod;
        }
        //Aunque el content-type no sea un problema en la mayoría de casos, es recomendable especificarlo
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($jsondata);
        exit();
        
        /*
    case "1": //entradas
        $oDBR->beginTransaction(); // porque a veces al dar error en insertar entrada, ya habia insertado el escrito y no hay manera de localizarlo
        // 1º guardo escrito (devuelve el id_reg)
        $id_reg=ins_escrito($_POST['prot_num'],$_POST['prot_any'],$asunto,$_POST['f_doc'],'entrada',$_POST['anulado'],$_POST['reservado'],$detalle);
        // 2º guardo entrada (devuelve el id_entrada)
        $id_entrada=ins_entrada($id_reg,$_POST['f_entrada'],$_POST['origen_id_lugar'],$_POST['origen_num'],$_POST['origen_any'],$_POST['origen_mas'],$_POST['f_doc_entrada']);
        // 3º guardo referencias
        ins_ref($id_reg,$ref_id_lugar,$ref_prot_num,$ref_prot_any,$ref_mas);
        // 4º guardo oficinas implicadas
        ins_oficinas($_POST['oficinas'],$id_reg,$id_entrada);
        $oDBR->commit();
        // Si es una distribución de cr, también hago la aprobación (antes estaba despues del pendiente,
        // pero el array_shift me estropea el array de las oficinas; asi que es mejor hacer esto primero.
        if (!empty($_POST['lista_ids'])) {
            // 7º pongo true en aprobacion del escrito y marco el escrito como de distribucion cr
            $sql_insert="UPDATE escritos SET aprobacion='t',distribucion_cr='t' WHERE id_reg=$id_reg";
            //echo "sql 7: $sql_insert<br>";
            $oDBR->query($sql_insert);
            // 8º guardo la aprobacion (devuelve el id_salida): f_aprobacion=f_entrada
            $id_salida=ins_salida($id_reg,$_POST['f_entrada'],$_POST['f_salida'],$_POST['descripcion'],$_POST['tipo_ctr'],$_POST['tipo_labor'],$_POST['id_modo_envio']);
            // 9º guardo oficinas implicadas
            ins_oficinas($_POST['oficinas'],$id_reg,$id_salida);
            // 10º guardo los destinos
            $id_lugar=explode(",",$_POST['lista_ids']);
            ins_destinos($id_reg,$id_salida,$id_lugar,"","","");
        }
        // 5º Compruebo si hay que generar un pendiente
        if ($_POST['plazo']!="hoy" && empty($_POST['id_pen'])) { // si id_pen, ya se ha guardado
            switch ($_POST['plazo']) {
                case "muy_urgente":
                    $f_plazo= date('d/m/Y',mktime (0,0,0,date("m"),date("d")+$plazo_muy_urgente,date("Y")) );
                    break;
                case "urgente":
                    $f_plazo= date('d/m/Y',mktime (0,0,0,date("m"),date("d")+$plazo_urgente,date("Y")) );
                    break;
                case "normal":
                    $f_plazo= date('d/m/Y',mktime (0,0,0,date("m"),date("d")+$plazo_normal,date("Y")) );
                    break;
                case "fecha":
                    empty($_POST['f_plazo'])? $f_plazo="" : $f_plazo=$_POST['f_plazo'];
                    break;
            }
            // guardo pendiente
            $status="NEEDS-ACTION";
            $observ = empty($_POST['observ'])? '' : $_POST['observ'];
            $oficinas = empty($_POST['oficinas'])? '' : $_POST['oficinas'];
            $id_categoria = empty($_POST['id_categoria'])? '' : $_POST['id_categoria'];
            $encargado = empty($_POST['encargado'])? '' : $_POST['encargado'];
            $pendiente_con = empty($_POST['pendiente_con'])? '' : $_POST['pendiente_con'];
            // la primera oficina es la que determina el calendario
            $a_oficinas = explode(',',$oficinas);
            $id_oficina = $a_oficinas[0];
            ins_pendiente_of($id_oficina,$id_reg,$asunto,$status,"","",$f_plazo,$_POST['origen_mas'],$observ,$_POST['reservado'],$detalle,$id_categoria,$encargado,$pendiente_con,$oficinas,"");
        }
        // 6º si he guardado el pendiente antes que la entrada, hay que actualizar el pendiente con el id_reg
        if (!empty($_POST['id_pen'])) {
            guardar_pendiente($id_reg,$_POST['id_pen']);
        }
        break;
        */
        break;
}