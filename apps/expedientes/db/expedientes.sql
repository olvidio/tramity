CREATE TABLE public.expedientes (
    id_expediente SERIAL PRIMARY KEY,
    id_tramite integer NOT NULL,
    ponente smallint,
    resto_oficinas integer[],
    asunto text,
    entradilla text,
    comentarios text,
    prioridad smallint NOT NULL,
    json_antecedentes jsonb,
    json_acciones jsonb,
	f_contestar date,
    estado smallint NOT NULL DEFAULT 1,
    f_ini_circulacion date,
    f_reunion timestamp without time zone,
    f_aprobacion date,
	vida smallint,
    json_preparar jsonb,
    firmas_oficina integer[],
	visibilidad smallint
);

ALTER TABLE public.expedientes OWNER TO tramity;

CREATE INDEX IF NOT EXISTS expedientes_asunto_idx ON public.expedientes ((lower(asunto)));
CREATE INDEX IF NOT EXISTS expedientes_estado_idx ON public.expedientes (estado);
CREATE INDEX IF NOT EXISTS expedientes_f_aprobacion ON public.expedientes (f_aprobacion);
CREATE INDEX IF NOT EXISTS expedientes_f_contestar_idx ON public.expedientes (f_contestar);
CREATE INDEX IF NOT EXISTS expedientes_f_reunion ON public.expedientes (f_reunion);
CREATE INDEX IF NOT EXISTS expedientes_ponente_idx ON public.expedientes (ponente);

CREATE INDEX IF NOT EXISTS expedientes_preparar_idx ON public.expedientes USING GIN (json_preparar jsonb_path_ops);
CREATE INDEX IF NOT EXISTS expedientes_antecedentes_idx ON public.expedientes USING GIN (json_antecedentes jsonb_path_ops);

--- acciones
CREATE TABLE public.acciones (
    id_item SERIAL PRIMARY KEY,
    id_expediente integer NOT NULL,
    tipo_accion smallint NOT NULL,
    id_escrito integer NOT NULL
);

ALTER TABLE public.acciones OWNER TO tramity;

CREATE INDEX IF NOT EXISTS acciones_id_expediente ON public.acciones (id_expediente);
CREATE INDEX IF NOT EXISTS acciones_id_escrito ON public.acciones (id_escrito);
CREATE INDEX IF NOT EXISTS acciones_tipo_accion ON public.acciones (tipo_accion);

ALTER TABLE public.acciones ADD CONSTRAINT acciones_fk_exp FOREIGN KEY (id_expediente) REFERENCES public.expedientes (id_expediente) ON DELETE CASCADE;
ALTER TABLE public.acciones ADD CONSTRAINT acciones_fk_esc FOREIGN KEY (id_escrito) REFERENCES public.escritos (id_escrito) ON DELETE CASCADE;