CREATE TABLE public.etiquetas (
    id_etiqueta SERIAL PRIMARY KEY,
    nom_etiqueta text NOT NULL,
    id_cargo integer,
    oficina boolean DEFAULT 'f'
);

ALTER TABLE public.etiquetas OWNER TO tramity;

CREATE TABLE public.etiquetas_expediente (
    id_etiqueta integer,
    id_expediente integer,
    PRIMARY KEY (id_etiqueta, id_expediente)
);

ALTER TABLE public.etiquetas_expediente OWNER TO tramity;

ALTER TABLE public.etiquetas_expediente ADD CONSTRAINT etiquetas_expediente_fk_exp FOREIGN KEY (id_expediente) REFERENCES public.expedientes (id_expediente) ON DELETE CASCADE;
ALTER TABLE public.etiquetas_expediente ADD CONSTRAINT etiquetas_expediente_fk_eti FOREIGN KEY (id_etiqueta) REFERENCES public.etiquetas (id_etiqueta) ON DELETE CASCADE;

CREATE TABLE public.etiquetas_entrada (
    id_etiqueta integer,
    id_entrada integer,
    PRIMARY KEY (id_etiqueta, id_entrada)
);

ALTER TABLE public.etiquetas_entrada OWNER TO tramity;

ALTER TABLE public.etiquetas_entrada ADD CONSTRAINT etiquetas_entrada_fk_exp FOREIGN KEY (id_entrada) REFERENCES public.entradas (id_entrada) ON DELETE CASCADE;
ALTER TABLE public.etiquetas_entrada ADD CONSTRAINT etiquetas_entrada_fk_eti FOREIGN KEY (id_etiqueta) REFERENCES public.etiquetas (id_etiqueta) ON DELETE CASCADE;

