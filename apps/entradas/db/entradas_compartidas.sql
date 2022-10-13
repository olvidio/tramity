-- OJO; Para los indices array integer ---
CREATE
EXTENSION IF NOT EXISTS intarray;
CREATE TABLE public.entradas_compartidas
(
    id_entrada_compartida SERIAL PRIMARY KEY,
    descripcion           text NOT NULL,
    json_prot_destino     jsonb,
    destinos              integer[],
    f_documento           date,
    json_prot_origen      jsonb,
    json_prot_ref         jsonb,
    categoria             smallint,
    asunto_entrada        text NOT NULL,
    f_entrada             date,
    anulado               text
);

ALTER TABLE public.entradas_compartidas OWNER TO tramity;

CREATE UNIQUE INDEX IF NOT EXISTS entradas_compartidas_id_entrada_idx ON public.entradas_compartidas (id_entrada_compartida);
CREATE INDEX IF NOT EXISTS entradas_compartidas_destinos_idx ON public.entradas_compartidas USING GIN (destinos gin__int_ops);

CREATE INDEX IF NOT EXISTS entradas_compartidas_origen_idx ON public.entradas_compartidas USING GIN (json_prot_origen jsonb_path_ops);
CREATE INDEX IF NOT EXISTS entradas_compartidas_ref_idx ON public.entradas_compartidas USING GIN (json_prot_ref jsonb_path_ops);


--- adjuntos

CREATE TABLE public.entrada_compartida_adjuntos
(
    id_item               SERIAL PRIMARY KEY,
    id_entrada_compartida integer NOT NULL,
    nom                   text,
    adjunto               bytea   NOT NULL
);

ALTER TABLE public.entrada_compartida_adjuntos OWNER TO tramity;

CREATE INDEX IF NOT EXISTS entrada_compartida_adjuntos_id_entrada_idx ON public.entrada_compartida_adjuntos (id_entrada_compartida);
ALTER TABLE public.entrada_compartida_adjuntos
    ADD CONSTRAINT entrada_compartida_adjuntos_fk_ent FOREIGN KEY (id_entrada_compartida) REFERENCES public.entradas_compartidas (id_entrada_compartida) ON DELETE CASCADE;

