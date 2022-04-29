--- escritos locales
-- OJO; Para los indices array integer ---
CREATE EXTENSION IF NOT EXISTS intarray;
CREATE TABLE public.escritos (
    id_escrito SERIAL PRIMARY KEY,
    json_prot_local jsonb,
    json_prot_destino jsonb,
    json_prot_ref jsonb,
    id_grupos integer[],
    destinos integer[],
    asunto text NOT NULL,
	detalle text,
    creador smallint,
    resto_oficinas integer[],
    comentarios text,
    f_aprobacion date,
    f_escrito date,
	f_contestar date,
	categoria smallint,
	visibilidad smallint,
	visibilidad_dst smallint,
    accion smallint NOT NULL,
    modo_envio smallint NOT NULL,
    f_salida date,
	ok smallint,
	tipo_doc smallint,
	anulado boolean,
	descripcion text
);

ALTER TABLE public.escritos OWNER TO tramity;

CREATE INDEX IF NOT EXISTS escritos_asunto_idx ON public.escritos ((lower(asunto)));
CREATE INDEX IF NOT EXISTS escritos_f_aprobacion_idx ON public.escritos (f_aprobacion);
CREATE INDEX IF NOT EXISTS escritos_f_contestar_idx ON public.escritos (f_contestar);
CREATE INDEX IF NOT EXISTS escritos_f_escrito_idx ON public.escritos (f_escrito);
CREATE INDEX IF NOT EXISTS escritos_id_grupos_idx ON public.escritos USING GIN (id_grupos gin__int_ops);
CREATE INDEX IF NOT EXISTS excritos_destinos_idx ON public.escritos USING GIN (destinos gin__int_ops);

CREATE INDEX IF NOT EXISTS escritos_local_idx ON public.escritos USING GIN (json_prot_local jsonb_path_ops);
CREATE INDEX IF NOT EXISTS escritos_destino_idx ON public.escritos USING GIN (json_prot_destino jsonb_path_ops);
CREATE INDEX IF NOT EXISTS escritos_ref_idx ON public.escritos USING GIN (json_prot_ref jsonb_path_ops);

--- adjuntos
CREATE TABLE public.escrito_adjuntos (
	id_item SERIAL PRIMARY KEY,
    id_escrito integer NOT NULL,
    nom text,
    adjunto bytea,
	tipo_doc smallint
);

ALTER TABLE public.escrito_adjuntos OWNER TO tramity;

CREATE INDEX IF NOT EXISTS escrito_adjuntos_id_escrito_idx ON public.escrito_adjuntos (id_escrito);
ALTER TABLE public.escrito_adjuntos ADD CONSTRAINT escrito_adjuntos_fk_ent FOREIGN KEY (id_escrito) REFERENCES public.escritos (id_escrito) ON DELETE CASCADE;
