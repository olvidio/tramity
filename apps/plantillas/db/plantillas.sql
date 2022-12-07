CREATE TABLE nombre_del_esquema.plantillas
(
    id_plantilla SERIAL PRIMARY KEY,
    nombre       text NOT NULL
);


ALTER TABLE nombre_del_esquema.plantillas
    OWNER TO tramity;

