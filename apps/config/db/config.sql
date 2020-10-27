CREATE TABLE IF NOT EXISTS x_config (
    parametro text NOT NULL,
    valor text
    );
        
        
ALTER TABLE x_config OWNER TO tramity;

ALTER TABLE x_config ADD PRIMARY KEY (parametro);


INSERT INTO x_config (parametro, valor) VALUES ('',3);
