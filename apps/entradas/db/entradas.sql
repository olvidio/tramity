CREATE TABLE nombre_del_esquema.entradas
(
    id_entrada            SERIAL PRIMARY KEY,
    id_entrada_compartida integer,
    modo_entrada          integer NOT NULL,
    json_prot_origen      jsonb,
    asunto_entrada        text    NOT NULL,
    json_prot_ref         jsonb,
    ponente               smallint,
    resto_oficinas        integer[],
    asunto                text,
    f_entrada             date,
    detalle               text,
    categoria             smallint,
    visibilidad           smallint,
    f_contestar           date,
    bypass                boolean,
    estado                smallint,
    anulado               text,
    encargado             integer,
    json_visto            jsonb
);

ALTER TABLE nombre_del_esquema.entradas
    OWNER TO tramity;

CREATE INDEX IF NOT EXISTS entradas_f_entrada_idx ON nombre_del_esquema.entradas (f_entrada);
CREATE INDEX IF NOT EXISTS entradas_f_contestar_idx ON nombre_del_esquema.entradas (f_contestar);
CREATE INDEX IF NOT EXISTS entradas_asunto_e_idx ON nombre_del_esquema.entradas ((lower(asunto_entrada)));
CREATE INDEX IF NOT EXISTS entradas_asunto_idx ON nombre_del_esquema.entradas ((lower(asunto)));
CREATE INDEX IF NOT EXISTS entradas_estado_idx ON nombre_del_esquema.entradas (estado);

CREATE INDEX IF NOT EXISTS entradas_origen_idx ON nombre_del_esquema.entradas USING GIN (json_prot_origen jsonb_path_ops);
CREATE INDEX IF NOT EXISTS entradas_ref_idx ON nombre_del_esquema.entradas USING GIN (json_prot_ref jsonb_path_ops);
CREATE INDEX IF NOT EXISTS entradas_visto_idx ON nombre_del_esquema.entradas USING GIN (json_visto jsonb_path_ops);

ALTER TABLE nombre_del_esquema.entradas
    ADD CONSTRAINT fk_entradas_compartidas FOREIGN KEY (id_entrada_compartida) REFERENCES public.entradas_compartidas (id_entrada_compartida) ON DELETE CASCADE;


--- docs
CREATE TABLE nombre_del_esquema.entrada_doc
(
    id_entrada integer PRIMARY KEY,
    tipo_doc   smallint,
    f_doc      date NOT NULL
);

ALTER TABLE nombre_del_esquema.entrada_doc
    OWNER TO tramity;

CREATE INDEX IF NOT EXISTS entrada_doc_f_doc_idx ON nombre_del_esquema.entrada_doc (f_doc);
CREATE INDEX IF NOT EXISTS entrada_doc_tipo_doc_idx ON nombre_del_esquema.entrada_doc (tipo_doc);
ALTER TABLE nombre_del_esquema.entrada_doc
    ADD CONSTRAINT entrada_doc_fk_ent FOREIGN KEY (id_entrada) REFERENCES nombre_del_esquema.entradas (id_entrada) ON DELETE CASCADE;

CREATE TABLE nombre_del_esquema.entrada_doc_json
(
    id_doc SERIAL PRIMARY KEY,
    txt    json
);

ALTER TABLE nombre_del_esquema.entrada_doc_json
    OWNER TO tramity;

CREATE TABLE nombre_del_esquema.entrada_doc_bytea
(
    id_doc SERIAL PRIMARY KEY,
    txt    bytea
);

ALTER TABLE nombre_del_esquema.entrada_doc_bytea
    OWNER TO tramity;

CREATE TABLE nombre_del_esquema.entrada_doc_txt
(
    id_doc SERIAL PRIMARY KEY,
    txt    text
);

ALTER TABLE nombre_del_esquema.entrada_doc_txt
    OWNER TO tramity;

--- adjuntos

CREATE TABLE nombre_del_esquema.entrada_adjuntos
(
    id_item    SERIAL PRIMARY KEY,
    id_entrada integer NOT NULL,
    nom        text,
    adjunto    bytea   NOT NULL
);

ALTER TABLE nombre_del_esquema.entrada_adjuntos
    OWNER TO tramity;

CREATE INDEX IF NOT EXISTS entrada_adjuntos_id_entrada_idx ON nombre_del_esquema.entrada_adjuntos (id_entrada);
ALTER TABLE nombre_del_esquema.entrada_adjuntos
    ADD CONSTRAINT entrada_adjuntos_fk_ent FOREIGN KEY (id_entrada) REFERENCES nombre_del_esquema.entradas (id_entrada) ON DELETE CASCADE;
