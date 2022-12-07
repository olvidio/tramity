CREATE TABLE nombre_del_esquema.etiquetas
(
    id_etiqueta  SERIAL PRIMARY KEY,
    nom_etiqueta text NOT NULL,
    id_cargo     integer,
    oficina      boolean DEFAULT 'f'
);

ALTER TABLE nombre_del_esquema.etiquetas
    OWNER TO tramity;

CREATE TABLE nombre_del_esquema.etiquetas_expediente
(
    id_etiqueta   integer,
    id_expediente integer,
    PRIMARY KEY (id_etiqueta, id_expediente)
);

ALTER TABLE nombre_del_esquema.etiquetas_expediente
    OWNER TO tramity;

ALTER TABLE nombre_del_esquema.etiquetas_expediente
    ADD CONSTRAINT etiquetas_expediente_fk_exp FOREIGN KEY (id_expediente) REFERENCES nombre_del_esquema.expedientes (id_expediente) ON DELETE CASCADE;
ALTER TABLE nombre_del_esquema.etiquetas_expediente
    ADD CONSTRAINT etiquetas_expediente_fk_eti FOREIGN KEY (id_etiqueta) REFERENCES nombre_del_esquema.etiquetas (id_etiqueta) ON DELETE CASCADE;

CREATE TABLE nombre_del_esquema.etiquetas_entrada
(
    id_etiqueta integer,
    id_entrada  integer,
    PRIMARY KEY (id_etiqueta, id_entrada)
);

ALTER TABLE nombre_del_esquema.etiquetas_entrada
    OWNER TO tramity;

ALTER TABLE nombre_del_esquema.etiquetas_entrada
    ADD CONSTRAINT etiquetas_entrada_fk_exp FOREIGN KEY (id_entrada) REFERENCES nombre_del_esquema.entradas (id_entrada) ON DELETE CASCADE;
ALTER TABLE nombre_del_esquema.etiquetas_entrada
    ADD CONSTRAINT etiquetas_entrada_fk_eti FOREIGN KEY (id_etiqueta) REFERENCES nombre_del_esquema.etiquetas (id_etiqueta) ON DELETE CASCADE;

