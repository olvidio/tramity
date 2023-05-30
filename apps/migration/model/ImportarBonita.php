<?php

namespace migration\model;

use PDOException;

class ImportarBonita
{


    private mixed $oDBT;

    /* CONSTRUCTOR ------------------------------ */
    public function __construct()
    {
        $this->oDBT = $GLOBALS['oDBT'];
    }

    public function crear_inicio(): void
    {

        $this->crear_schema();
        $this->crear_lugares();
        $this->crear_registro();
    }

    private function crear_schema(): void
    {

        $sql = "CREATE SCHEMA IF NOT EXISTS prodel;
         GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA prodel TO tramity;
         GRANT USAGE ON SCHEMA prodel TO tramity;
        ALTER SCHEMA prodel OWNER TO tramity;";
//         REASSIGN OWNED BY dani TO tramity;


        try {
            $this->oDBT->exec($sql);
        } catch (PDOException $e) {
            $err_txt = $e->errorInfo[2];
            $error = 'Error DB: ' . $err_txt ."\n<br>". 'linea: '. __LINE__ . ' en ' . __FILE__;
            exit($error);
        }
    }

    private function crear_lugares(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS prodel.lugares
            (
              persistenceid bigint NOT NULL,
              autorizacion character varying(255),
              codigo character varying(255),
              nombre character varying(30),
              persistenceversion bigint,
              tipo character varying(15),
              CONSTRAINT lugares_pkey PRIMARY KEY (persistenceid)
            )";

        try {
            $this->oDBT->exec($sql);
        } catch (PDOException $e) {
            $err_txt = $e->errorInfo[2];
            $error = 'Error DB: ' . $err_txt ."\n<br>". 'linea: '. __LINE__ . ' en ' . __FILE__;
            exit($error);
        }
    }

    private function crear_registro(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS prodel.registro
            (
              persistenceid bigint NOT NULL,
              anno character varying(4),
              annoregistro character varying(4),
              archivadoen character varying(30),
              asunto character varying(70),
              asuntooficinas character varying(70),
              departamentoponente character varying(10),
              destino character varying(1000),
              esanexo boolean,
              fechacreacion timestamp without time zone,
              fechamodificacion timestamp without time zone,
              fechatopecontestacion timestamp without time zone,
              idref bigint,
              nomanexo character varying(100),
              nombdocumento character varying(100),
              numprotocolo integer,
              numregistro integer,
              observaciones character varying(100),
              origen character varying(20),
              otrosdepartamentos character varying(255),
              permisooficial boolean,
              persistenceversion bigint,
              referencias character varying(255),
              requierecontestacion boolean,
              tipomime character varying(10),
              uuid character varying(255),
              CONSTRAINT registro_pkey PRIMARY KEY (persistenceid)
            )
            WITH (
              OIDS=FALSE
            );
            ALTER TABLE prodel.registro
              OWNER TO tramity;
            ";

        try {
            $this->oDBT->exec($sql);
        } catch (PDOException $e) {
            $err_txt = $e->errorInfo[2];
            $error = 'Error DB: ' . $err_txt ."\n<br>". 'linea: '. __LINE__ . ' en ' . __FILE__;
            exit($error);
        }

        // Index: protocolo,  registroind
        $sql_index = "CREATE INDEX protocolo
              ON prodel.registro
              USING btree
              (numprotocolo, anno);
                CREATE INDEX registroind ON prodel.registro
              USING btree
              (numregistro, annoregistro);
            ";

        try {
            $this->oDBT->exec($sql_index);
        } catch (PDOException $e) {
            $err_txt = $e->errorInfo[2];
            $error = 'Error DB: ' . $err_txt ."\n<br>". 'linea: '. __LINE__ . ' en ' . __FILE__;
            exit($error);
        }
    }


}