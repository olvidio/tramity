CREATE TABLE nombre_del_esquema.lugares_grupos
(
    id_grupo    SERIAL PRIMARY KEY,
    descripcion text NOT NULL,
    miembros    integer[]
);

ALTER TABLE nombre_del_esquema.lugares_grupos
    OWNER TO tramity;