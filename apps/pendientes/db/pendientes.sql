CREATE TABLE public.pendientes
(
    id_pendiente  SERIAL PRIMARY KEY,
    asunto        text                  NOT NULL,
    status        text    DEFAULT 'NEEDS-ACTION'::text NOT NULL,
    f_acabado     date,
    f_plazo       date,
    ref_mas       text,
    observ        text,
    encargado     text,
    cancilleria   boolean DEFAULT false NOT NULL,
    visibilidad   integer,
    detalle       text,
    pendiente_con text,
    etiquetas     text,
    oficinas      text,
    id_oficina    integer               NOT NULL,
    rrule         text,
    f_inicio      date
);

COMMENT
ON TABLE public.pendientes IS 'Tabla temporal para guardar pendientes antes de saber el id_reg';

ALTER TABLE public.pendientes OWNER TO tramity;
