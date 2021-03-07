<?php
namespace migration\model;


// Arxivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

class Migration { 
    
    private $oDBT;
    
    /* CONSTRUCTOR ------------------------------ */
    function __construct() {
        $this->oDBT = $GLOBALS['oDBT'];
        
        // CREATE SCHEMA IF NOT EXISTS reg
        // GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA reg TO tramity;
        // GRANT USAGE ON SCHEMA reg TO tramity;
        //ALTER SCHEMA reg OWNER TO tramity;
        // REASSIGN OWNED BY dani TO tramity;
    }
    
    private function getId_local ($dl='dlb') {
        $sql = "SELECT id_lugar FROM lugares WHERE sigla = '$dl'";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($this->oDBT->query($sql) as $row) {
            $id = $row['id_lugar'];
        }
        return $id;
    }
    /*------------------------- METODES ESCRITOS-APROBACIONES ----------------------------- */
    
    public function copiar_aprobaciones() {
        // aprobaciones: id_salida 	id_reg 	f_aprobacion 	f_salida 	id_modo_envio
        // escritos: id_escrito	json_prot_local json_prot_destino json_prot_ref id_grupos destinos entradilla
        //      asunto detalle creador resto_oficinas comentarios f_aprobacion f_escrito f_contestar categoria visibilidad
        //      accion modo_envio f_salida ok tipo_doc anulado
        
        // añadir columna
        $sql = "ALTER TABLE escritos ADD COLUMN IF NOT EXISTS id_reg integer ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $sql = "ALTER TABLE escritos ADD COLUMN IF NOT EXISTS id_salida integer ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // SALILDAS
        $id_lugar_dlb = $this->getId_local();
        $sql = "INSERT INTO escritos (json_prot_local, asunto, detalle, f_aprobacion, f_escrito, categoria, visibilidad, accion, 
                modo_envio, f_salida, ok, id_reg, id_salida)
            (SELECT json_build_object('any', substring(prot_any::text from '..$'), 'mas', null, 'num', prot_num, 'lugar', $id_lugar_dlb) AS json_prot_local,
            e.asunto, e.detalle, a.f_aprobacion, e.f_doc, 2, CASE reservado WHEN 't' THEN 3 ELSE 1 END, 2, 1, a.f_salida, 3, a.id_reg, a.id_salida
            FROM reg.escritos e JOIN reg.aprobaciones a USING (id_reg) ); ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
    }
    
    public function destinos_aprobaciones() {
        
        // DESTINOS
        // para poner el id_lugar nuevo
        $sql = "ALTER TABLE reg.destinos ADD COLUMN IF NOT EXISTS id_lugar_new integer ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $sql = "UPDATE reg.destinos e SET id_lugar_new = 
                (SELECT id_lugar_new FROM reg.lugares l WHERE e.id_lugar = l.id_lugar) ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        $sql = "UPDATE escritos es SET (json_prot_destino) =
                (
                select to_json(array (
                    SELECT json_build_object('any', substring(prot_any::text from '..$'), 'mas', mas, 'num', prot_num, 'lugar', id_lugar_new) AS json_prot_destino
                    FROM reg.destinos d
                     WHERE d.id_lugar_new IS NOT NULL
                    AND es.id_salida = d.id_salida AND es.id_reg = d.id_reg )
                    )
                )";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        
        // copiar la descripcion para el caso de destino multiple
        $sql = "ALTER TABLE escritos ADD COLUMN IF NOT EXISTS descripcion text";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $sql = "UPDATE escritos x SET descripcion = sub.descripcion
                FROM (SELECT m.descripcion, e.id_escrito
                    FROM escritos e, reg.destino_multiple m 
                    WHERE e.id_reg = m.id_reg
                    ) AS sub
                WHERE x.id_escrito = sub.id_escrito";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
    }
        
    public function oficinas_aprobaciones() {
        // id_item 	id_reg 	id_e_s 	id_oficina 	responsable 	asunto_of 	cancilleria
        // cambio las oficinas por el cargo del director de la oficina
        // ponente
        $sql = "UPDATE escritos es SET (creador) =
                (SELECT c.id_cargo 
                 FROM reg.oficinas of JOIN reg.aprobaciones a ON (a.id_salida = of.id_e_s AND a.id_reg = of.id_reg),
                 reg.x_oficinas x, aux_cargos c
                 WHERE of.responsable = 't' AND x.id_oficina = of.id_oficina AND x.id_oficina_new IS NOT NULL
                AND of.cancilleria = 'f' AND es.id_reg = of.id_reg AND es.id_salida = of.id_e_s 
                AND c.id_oficina = x.id_oficina_new AND director = TRUE
                )";
                
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
       
        // resto
        $sql = "UPDATE escritos es SET (resto_oficinas) =
                (
                SELECT array( SELECT c.id_cargo 
                FROM reg.oficinas of JOIN reg.aprobaciones a ON (a.id_salida = of.id_e_s AND a.id_reg = of.id_reg),
                 reg.x_oficinas x, aux_cargos c
                 WHERE of.responsable = 'f' AND x.id_oficina = of.id_oficina AND x.id_oficina_new IS NOT NULL
                AND of.cancilleria = 'f' AND es.id_reg = of.id_reg AND es.id_salida = of.id_e_s
                AND c.id_oficina = x.id_oficina_new AND director = TRUE )
                )";
                
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
    }

    public function referencias_aprobaciones() {
        //id_ref 	id_reg 	id_lugar 	prot_num 	prot_any 	mas 	cancilleria id_lugar_new
        
        $sql = "UPDATE escritos es SET (json_prot_ref) =
                (
                select to_json(array (
                    SELECT json_build_object('any', substring(prot_any::text from '..$'), 'mas', mas, 'num', prot_num, 'lugar', id_lugar_new) AS json_prot_ref
                    FROM reg.referencias ref
                     WHERE ref.id_lugar_new IS NOT NULL
                    AND ref.cancilleria = 'f' AND es.id_reg = ref.id_reg )
                    )
                )";
                
                
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
    }
    
    
    
    /*------------------------- METODES ENTRADAS ----------------------------- */
    
    public function docs_entradas() {
        // id_escrito 	id_reg 	dl_ctr 	tipo 	num 	escrito 	nom 	extension 	activo
        // id_doc txt
        // id_item 	id_entrada 	nom 	adjunto
        

        // Añado campo para distinguir dl_ctr hasta que duplique la entrada
        $sql = "ALTER TABLE entrada_adjuntos ADD COLUMN IF NOT EXISTS dl_ctr integer ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $sql = "ALTER TABLE entrada_adjuntos ADD COLUMN IF NOT EXISTS id_reg integer ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        
        // copiar los adjuntos para dl y ctr
        $sql = "INSERT INTO entrada_adjuntos (id_entrada, adjunto, nom, dl_ctr, id_reg) 
                (SELECT e.id_entrada, d.escrito, (CASE tipo WHEN 1 THEN 'escrito' ELSE 'anexo_'||num END)||'.'||extension, dl_ctr, d.id_reg
                FROM reg.documentos d, entradas e  
                WHERE e.id_reg = d.id_reg AND activo='t'
                ) ";
        
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // duplicar entrada para el caso de los ctr:
        $sql = "INSERT INTO entradas ( modo_entrada, json_prot_origen, asunto_entrada, json_prot_ref, ponente, resto_oficinas,
                        asunto, f_entrada, detalle, categoria, visibilidad, f_contestar, bypass, estado, anulado, id_reg, id_entrada_old)
                (
                SELECT DISTINCT e.modo_entrada, e.json_prot_origen, e.asunto_entrada, e.json_prot_ref, e.ponente, e.resto_oficinas,
                        e.asunto, e.f_entrada, e.detalle, e.categoria, e.visibilidad, e.f_contestar, TRUE, e.estado, e.anulado, e.id_reg, e.id_entrada_old
                FROM entradas e, entrada_adjuntos ad 
                WHERE e.id_entrada = ad.id_entrada AND ad.dl_ctr=2 AND e.anulado IS NULL
                ) ";
        
        
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
    
        // cambiar el id_entrada por el nuevo (caso ctr)
        $sql= "UPDATE entrada_adjuntos ad SET id_entrada = sub.id_entrada FROM
                (SELECT DISTINCT e.id_entrada, e.id_reg FROM entradas e, entrada_adjuntos ad2
                    WHERE e.bypass=TRUE AND e.anulado IS NULL AND e.id_reg = ad2.id_reg AND ad2.dl_ctr=2
                    ) AS sub WHERE ad.dl_ctr=2 AND sub.id_reg=ad.id_reg;
               ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        
        // Poner bypass=FALSE para los de dl
        $sql= "UPDATE entradas e SET bypass = FALSE  FROM
                (SELECT DISTINCT e2.id_entrada, e2.id_reg, e2.asunto FROM entradas e2, entrada_adjuntos ad2
                 WHERE e2.anulado IS NULL AND e2.id_entrada = ad2.id_entrada AND ad2.dl_ctr=1
                 ) AS sub WHERE e.id_entrada = sub.id_entrada;
               ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        
    }
    
    public function bypass_entradas() {
        //id_item 	id_entrada 	descripcion 	json_prot_destino 	id_grupos 	destinos 	f_salida
        //id_salida 	id_reg 	descripcion 	tipo_ctr 	tipo_labor
        
        $sql = "INSERT INTO entradas_bypass (id_entrada, descripcion) 
                (SELECT e.id_entrada, m.descripcion
                FROM entradas e, reg.destino_multiple m 
                WHERE e.id_reg = m.id_reg
                ) ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        
        // la fecha:
        $sql = "UPDATE entradas_bypass en SET (f_salida) =
                (SELECT a.f_salida
                FROM entradas e, reg.destino_multiple m, reg.aprobaciones a 
                WHERE e.id_reg = m.id_reg AND m.id_salida=a.id_salida
                    AND en.id_entrada = e.id_entrada
                ) ";
        
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        
        
    }
    
    public function permanentes_entradas() {
        //id_reg 	activo
        // categoria permanente = 3
        $sql = "UPDATE entradas en SET categoria = 3
                FROM reg.cr_num_bajo cr WHERE cr.id_reg = en.id_reg AND cr.activo='t' ";
        
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        
    }
    
    public function oficinas_entradas() {
        // id_item 	id_reg 	id_e_s 	id_oficina 	responsable 	asunto_of 	cancilleria
        
        // ponente
        $sql = "UPDATE entradas en SET (ponente) =
                (SELECT x.id_oficina_new 
                 FROM reg.oficinas of JOIN reg.entradas e ON (e.id_entrada = of.id_e_s AND e.id_reg = of.id_reg),
                 reg.x_oficinas x
                 WHERE of.responsable = 't' AND x.id_oficina = of.id_oficina AND x.id_oficina_new IS NOT NULL
                AND of.cancilleria = 'f' AND en.id_reg = of.id_reg AND en.id_entrada_old = of.id_e_s
                )";
                
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
       
        // resto
        $sql = "UPDATE entradas en SET (resto_oficinas) =
                (
                SELECT array( SELECT x.id_oficina_new 
                FROM reg.oficinas of JOIN reg.entradas e ON (e.id_entrada = of.id_e_s AND e.id_reg = of.id_reg),
                 reg.x_oficinas x
                 WHERE of.responsable = 'f' AND x.id_oficina = of.id_oficina AND x.id_oficina_new IS NOT NULL
                AND of.cancilleria = 'f' AND en.id_reg = of.id_reg AND en.id_entrada_old = of.id_e_s)
                )";
                
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
    }

    public function referencias_entradas() {
        // para poner el id_lugar nuevo
        $sql = "ALTER TABLE reg.referencias ADD COLUMN IF NOT EXISTS id_lugar_new integer ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $sql = "UPDATE reg.referencias ref SET id_lugar_new = 
                (SELECT id_lugar_new FROM reg.lugares l WHERE ref.id_lugar = l.id_lugar) ";
        
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        
        
        //id_ref 	id_reg 	id_lugar 	prot_num 	prot_any 	mas 	cancilleria id_lugar_new
        
        $sql = "UPDATE entradas en SET (json_prot_ref) =
                (
                select to_json(array (
                    SELECT json_build_object('any', substring(prot_any::text from '..$'), 'mas', mas, 'num', prot_num, 'lugar', id_lugar_new) AS json_prot_ref
                    FROM reg.referencias ref
                     WHERE ref.id_lugar_new IS NOT NULL
                    AND ref.cancilleria = 'f' AND en.id_reg = ref.id_reg )
                    )
                )";
                
                
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
    }

    public function completar_entradas() {
       // id_reg 	f_doc 	asunto 	escrito entrada aprobacion  anulado reservado detalle distribucion_cr
        
        $sql = "UPDATE entradas e SET (asunto, detalle, anulado, bypass, categoria, estado, visibilidad) =
                (SELECT asunto, detalle, anulado, distribucion_cr, 2, 5, CASE reservado WHEN 't' THEN 3 ELSE 1 END
                FROM reg.escritos re 
                WHERE re.id_reg = e.id_reg)";
                
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
    }

    public function copiar_entradas() {
        // añadir columna
        $sql = "ALTER TABLE entradas ADD COLUMN IF NOT EXISTS id_reg integer ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $sql = "ALTER TABLE entradas ADD COLUMN IF NOT EXISTS id_entrada_old integer ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // para poner el id_lugar nuevo
        $sql = "ALTER TABLE reg.entradas ADD COLUMN IF NOT EXISTS id_lugar_new integer ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $sql = "UPDATE reg.entradas e SET id_lugar_new = 
                (SELECT id_lugar_new FROM reg.lugares l WHERE e.id_lugar = l.id_lugar) ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        // pasar l'any a dues xifres:
        // json_prot_origen => {"any": 20, "mas": null, "num": 15, "lugar": 58} 
        $sql = "INSERT INTO entradas (modo_entrada, json_prot_origen, f_entrada, id_reg, asunto_entrada, id_entrada_old)
                (SELECT 5, json_build_object('any', substring(prot_any::text from '..$'), 'mas', mas, 'num', prot_num, 'lugar', id_lugar_new) AS json_prot_origen,
                f_entrada, id_reg, 'importado', id_entrada
                FROM reg.entradas WHERE id_lugar_new IS NOT NULL); ";
                
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }

        
        
    }
    
    public function crear_equivalencias_oficinas() {
        // añadir columna
        $sql = "ALTER TABLE reg.x_oficinas ADD COLUMN IF NOT EXISTS id_oficina_new integer ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        // relacionar
        $sql = "UPDATE reg.x_oficinas SET id_oficina_new = public.x_oficinas.id_oficina
                    FROM public.x_oficinas WHERE reg.x_oficinas.sigla = public.x_oficinas.sigla ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        
        // mirar si queda alguna oficina suelta
        $sql = "SELECT * FROM reg.x_oficinas WHERE id_oficina_new IS NULL";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $msg = '';
        foreach ($this->oDBT->query($sql) as $row) {
            $msg .= "sigla: ".$row['sigla']."<br>";
        }
        
        if (!empty($msg)) {
            echo "Revisar la correspondencia para las oficinas:<br>";
            echo $msg;
        }
        
    }
    
    public function crear_equivalencias_lugares() {
        // añadir columna
        $sql = "ALTER TABLE reg.lugares ADD COLUMN IF NOT EXISTS id_lugar_new integer ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        
        // relacionar
        $sql = "UPDATE reg.lugares SET id_lugar_new = public.lugares.id_lugar
                    FROM public.lugares WHERE reg.lugares.sigla = public.lugares.sigla ";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        
        // mirar si queda alguna oficina suelta
        $sql = "SELECT * FROM reg.lugares WHERE id_lugar_new IS NULL";
        if ($this->oDBT->query($sql) === FALSE) {
            $sClauError = 'migartion';
            $_SESSION['oGestorErrores']->addErrorAppLastError($this->oDBT, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        $msg = '';
        foreach ($this->oDBT->query($sql) as $row) {
            $msg .= "sigla: ".$row['sigla']."<br>";
        }
        
        if (!empty($msg)) {
            echo "Revisar la correspondencia para los lugares:<br>";
            echo $msg;
        }
        
    }
    
}