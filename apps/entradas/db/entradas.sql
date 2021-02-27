CREATE TABLE public.entradas (
    id_entrada SERIAL PRIMARY KEY,
    modo_entrada integer NOT NULL,
    json_prot_origen jsonb,
    asunto_entrada text NOT NULL,
    json_prot_ref jsonb,
    ponente smallint,
    resto_oficinas integer[],
    asunto text,
    f_entrada date,
	detalle text,
	categoria smallint,
	visibilidad smallint,
	f_contestar date,
	bypass boolean,
	estado smallint,
	anulado text
);

ALTER TABLE public.entradas OWNER TO tramity;

CREATE INDEX IF NOT EXISTS entradas_f_entrada_idx ON public.entradas (f_entrada);
CREATE INDEX IF NOT EXISTS entradas_f_contestar_idx ON public.entradas (f_contestar);
CREATE INDEX IF NOT EXISTS entradas_asunto_e_idx ON public.entradas ((lower(asunto_entrada)));
CREATE INDEX IF NOT EXISTS entradas_asunto_idx ON public.entradas ((lower(asunto)));
CREATE INDEX IF NOT EXISTS entradas_estado_idx ON public.entradas (estado);

--- docs
CREATE TABLE public.entrada_doc (
    id_entrada integer PRIMARY KEY,
    tipo_doc smallint,
    f_doc date NOT NULL
);

ALTER TABLE public.entrada_doc OWNER TO tramity;

CREATE INDEX IF NOT EXISTS entrada_doc_f_doc_idx ON public.entrada_doc (f_doc);
CREATE INDEX IF NOT EXISTS entrada_doc_tipo_doc_idx ON public.entrada_doc (tipo_doc);
ALTER TABLE entrada_doc ADD CONSTRAINT entrada_doc_fk_ent FOREIGN KEY (id_entrada) REFERENCES entradas (id_entrada) ON DELETE CASCADE;

CREATE TABLE public.entrada_doc_json (
    id_doc SERIAL PRIMARY KEY,
    txt json
);

ALTER TABLE public.entrada_doc_json OWNER TO tramity;

CREATE TABLE public.entrada_doc_bytea (
    id_doc SERIAL PRIMARY KEY,
    txt bytea
);

ALTER TABLE public.entrada_doc_bytea OWNER TO tramity;

CREATE TABLE public.entrada_doc_txt (
    id_doc SERIAL PRIMARY KEY,
    txt text
);

ALTER TABLE public.entrada_doc_txt OWNER TO tramity;

--- adjuntos

CREATE TABLE public.entrada_adjuntos (
	id_item SERIAL PRIMARY KEY,
    id_entrada integer NOT NULL,
    nom text,
    adjunto bytea NOT NULL
);

ALTER TABLE public.entrada_adjuntos OWNER TO tramity;

CREATE INDEX IF NOT EXISTS entrada_adjuntos_id_entrada_idx ON public.entrada_adjuntos (id_entrada);
ALTER TABLE entrada_adjuntos ADD CONSTRAINT entrada_adjuntos_fk_ent FOREIGN KEY (id_entrada) REFERENCES entradas (id_entrada) ON DELETE CASCADE;

--- by pass
-- OJO; Para los indices array integer ---
CREATE EXTENSION IF NOT EXISTS intarray;
CREATE TABLE public.entradas_bypass (
	id_item SERIAL PRIMARY KEY,
    id_entrada integer NOT NULL,
    descripcion text NOT NULL,
    json_prot_destino jsonb,
    id_grupos integer[],
    destinos integer[],
    f_salida date,
    CONSTRAINT fk_entrada
      FOREIGN KEY(id_entrada) 
	  REFERENCES entradas(id_entrada) ON DELETE CASCADE
);

--- ALTER TABLE "entradas_bypass" DROP CONSTRAINT "fk_entrada";
--- ALTER TABLE entradas_bypass ADD CONSTRAINT entradas_bypass_fk_ent FOREIGN KEY (id_entrada) REFERENCES entradas (id_entrada) ON DELETE CASCADE;

ALTER TABLE public.entradas_bypass OWNER TO tramity;

CREATE INDEX IF NOT EXISTS entradas_bypass_id_entrada_idx ON public.entradas_bypass (id_entrada);
CREATE INDEX IF NOT EXISTS entradas_bypass_destinos_idx ON public.entradas_bypass USING GIN (destinos gin__int_ops);
CREATE INDEX IF NOT EXISTS entradas_bypass_id_grupos_idx ON public.entradas_bypass USING GIN (id_grupos gin__int_ops);