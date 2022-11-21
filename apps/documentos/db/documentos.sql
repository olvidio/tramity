CREATE TABLE nombre_del_esquema.documentos
(
    id_doc         SERIAL PRIMARY KEY,
    nom            text,
    nombre_fichero text,
    creador        smallint,
    visibilidad    smallint,
    f_upload       date,
    tipo_doc       smallint,
    documento      bytea
);


ALTER TABLE nombre_del_esquema.documentos OWNER TO tramity;

CREATE INDEX IF NOT EXISTS documentos_f_upload_idx ON nombre_del_esquema.documentos (f_upload);
CREATE INDEX IF NOT EXISTS documentos_nom_idx ON nombre_del_esquema.documentos (nom);
CREATE INDEX IF NOT EXISTS documentos_creador_idx ON nombre_del_esquema.documentos (creador);
CREATE INDEX IF NOT EXISTS documentos_visibilidad_idx ON nombre_del_esquema.documentos (visibilidad);


CREATE TABLE nombre_del_esquema.etiquetas_documento
(
    id_etiqueta integer not null,
    id_doc      integer not null,
    PRIMARY KEY (id_etiqueta, id_doc)
);

ALTER TABLE nombre_del_esquema.etiquetas_documento OWNER TO tramity;

ALTER TABLE nombre_del_esquema.etiquetas_documento
    ADD CONSTRAINT etiquetas_documento_fk_exp FOREIGN KEY (id_doc) REFERENCES nombre_del_esquema.documentos (id_doc) ON DELETE CASCADE;
ALTER TABLE nombre_del_esquema.etiquetas_documento
    ADD CONSTRAINT etiquetas_documento_fk_eti FOREIGN KEY (id_etiqueta) REFERENCES nombre_del_esquema.etiquetas (id_etiqueta) ON DELETE CASCADE;