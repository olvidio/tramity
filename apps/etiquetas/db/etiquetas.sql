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

ALTER TABLE etiquetas_expediente ADD CONSTRAINT etiquetas_expediente_fk_exp FOREIGN KEY (id_expediente) REFERENCES expedientes (id_expediente) ON DELETE CASCADE;
ALTER TABLE etiquetas_expediente ADD CONSTRAINT etiquetas_expediente_fk_eti FOREIGN KEY (id_etiqueta) REFERENCES etiquetas (id_etiqueta) ON DELETE CASCADE;

