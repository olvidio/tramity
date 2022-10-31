CREATE TABLE public.entidades
(
    id_entidad SERIAL PRIMARY KEY,
    nombre     text,
    schema     text,
    tipo       smallint,
    anulado    boolean
);

ALTER TABLE public.entidades OWNER TO tramity;

CREATE UNIQUE INDEX IF NOT EXISTS entidades_nombre_udx ON public.entidades ((lower (nombre)));
CREATE UNIQUE INDEX IF NOT EXISTS entidades_schema_udx ON public.entidades ((lower (schema)));
CREATE INDEX IF NOT EXISTS entidades_anulado_idx ON public.entidades (anulado);

ALTER SEQUENCE entidades_id_entidad_seq START WITH 100;

CREATE OR REPLACE FUNCTION public.idglobal(entidad text)
 RETURNS integer
 LANGUAGE plpgsql
 STABLE
AS $function$ DECLARE
                idauto int;
                n int;
                entidad ALIAS FOR $1;
        BEGIN
          SELECT id_entidad into n FROM public.entidades WHERE schema=entidad;
          EXECUTE format('SELECT last_value+1 FROM "%s".aux_usuarios_id_auto_seq', entidad) into idauto;
          RETURN n::text||idauto::text;
        END;
$function$;
