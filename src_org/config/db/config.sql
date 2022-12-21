CREATE TABLE IF NOT EXISTS nombre_del_esquema.x_config
(
    parametro
        text
        NOT
            NULL,
    valor
        text
);

ALTER TABLE nombre_del_esquema.x_config
    OWNER TO tramity;

ALTER TABLE nombre_del_esquema.x_config
    ADD PRIMARY KEY (parametro);