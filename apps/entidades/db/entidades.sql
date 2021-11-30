CREATE TABLE public.entidades (
    id_entidad SERIAL PRIMARY KEY,
    nombre text,
    schema text,
	tipo smallint,
	anulado boolean
);

ALTER TABLE public.entidades OWNER TO tramity;

CREATE UNIQUE INDEX IF NOT EXISTS entidades_nombre_udx ON public.entidades ((lower(nombre)));
CREATE UNIQUE INDEX IF NOT EXISTS entidades_schema_udx ON public.entidades ((lower(schema)));
CREATE INDEX IF NOT EXISTS entidades_anulado_idx ON public.entidades (anulado);
