CREATE TABLE IF NOT EXISTS x_config (
    parametro text NOT NULL,
    valor text
    );
        
ALTER TABLE x_config OWNER TO tramity;

ALTER TABLE x_config ADD PRIMARY KEY (parametro);

INSERT INTO x_config (parametro, valor) VALUES ('ambito',3);
INSERT INTO x_config (parametro, valor) VALUES ('sigla', 'dlb');
INSERT INTO x_config (parametro, valor) VALUES ('idioma_default', 'es_ES.UTF-8');
INSERT INTO x_config (parametro, valor) VALUES ('server_etherpad', 'http://tramity.local:9001');
INSERT INTO x_config (parametro, valor) VALUES ('plazo_urgente', 1);
INSERT INTO x_config (parametro, valor) VALUES ('plazo_rapido', 7);
INSERT INTO x_config (parametro, valor) VALUES ('plazo_desconocido', 56);
INSERT INTO x_config (parametro, valor) VALUES ('plazo_normal', 14);
INSERT INTO x_config (parametro, valor) VALUES ('plazo_error', 21);