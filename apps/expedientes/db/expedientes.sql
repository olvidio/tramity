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
    etiquetas integer[],
	f_contestar date,
    estado smallint NOT NULL DEFAULT 1,
    f_ini_circulacion date,
    f_reunion date,
    f_aprobacion date,
	vida smallint,
	ok boolean,
    json_preparar jsonb,
    firmas_oficina integer[],
	visibilidad smallint
);

ALTER TABLE public.expedientes OWNER TO tramity;

CREATE INDEX IF NOT EXISTS expedientes_f_contestar_idx ON public.expedientes (f_contestar);
CREATE INDEX IF NOT EXISTS expedientes_f_reunion ON public.expedientes (f_reunion);
CREATE INDEX IF NOT EXISTS expedientes_f_aprobacion ON public.expedientes (f_aprobacion);
CREATE INDEX IF NOT EXISTS expedientes_asunto_idx ON public.expedientes ((lower(asunto)));
CREATE INDEX IF NOT EXISTS expedientes_etiquetas_idx ON public.expedientes (etiquetas);
CREATE INDEX IF NOT EXISTS expedientes_estado_idx ON public.expedientes (estado);

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

--- escritos locales
-- OJO; Para los indices array integer ---
CREATE EXTENSION intarray;
CREATE TABLE public.escritos (
    id_escrito SERIAL PRIMARY KEY,
    json_prot_local jsonb,
    json_prot_destino jsonb,
    json_prot_ref jsonb,
    id_grupos integer[],
    destinos integer[],
    entradilla text NOT NULL,
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
    accion smallint NOT NULL,
    modo_envio smallint NOT NULL,
    f_salida date,
	ok boolean,
	tipo_doc smallint,
	anulado boolean
);

ALTER TABLE public.escritos OWNER TO tramity;

CREATE INDEX IF NOT EXISTS escritos_f_aprobacion_idx ON public.escritos (f_aprobacion);
CREATE INDEX IF NOT EXISTS escritos_f_escrito_idx ON public.escritos (f_escrito);
CREATE INDEX IF NOT EXISTS escritos_f_contestar_idx ON public.escritos (f_contestar);
CREATE INDEX IF NOT EXISTS escritos_asunto_idx ON public.escritos ((lower(asunto)));
CREATE INDEX IF NOT EXISTS excritos_destinos_idx ON public.escritos USING GIN (destinos gin__int_ops);
CREATE INDEX IF NOT EXISTS escritos_id_grupos_idx ON public.escritos USING GIN (id_grupos gin__int_ops);


--- adjuntos
CREATE TABLE public.escrito_adjuntos (
	id_item SERIAL PRIMARY KEY,
    id_escrito integer NOT NULL,
    nom text,
    adjunto bytea NOT NULL
);

ALTER TABLE public.escrito_adjuntos OWNER TO tramity;

CREATE INDEX IF NOT EXISTS escrito_adjuntos_id_escrito_idx ON public.escrito_adjuntos (id_escrito);

