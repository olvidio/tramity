CREATE TABLE nombre_del_esquema.x_tramites
(
    id_tramite SERIAL PRIMARY KEY,
    tramite    text NOT NULL,
    orden      smallint,
    breve      varchar(10),
    activo     boolean               NOT NULL DEFAULT 't'
);


ALTER TABLE nombre_del_esquema.x_tramites OWNER TO tramity;

CREATE TABLE nombre_del_esquema.tramite_cargo
(
    id_item       SERIAL PRIMARY KEY,
    id_tramite    integer  NOT NULL,
    orden_tramite smallint NOT NULL,
    id_cargo      integer  NOT NULL,
    multiple      smallint
);

ALTER TABLE nombre_del_esquema.tramite_cargo OWNER TO tramity;

CREATE INDEX tramite_cargo_id_tramite_idx ON nombre_del_esquema.tramite_cargo (id_tramite);
CREATE INDEX tramite_cargo_id_cargo_idx ON nombre_del_esquema.tramite_cargo (id_cargo);
ALTER TABLE nombre_del_esquema.tramite_cargo
    ADD CONSTRAINT tramite_cargo_orden_tramite_ukey UNIQUE (id_tramite, orden_tramite);


CREATE TABLE nombre_del_esquema.expediente_firmas
(
    id_item          SERIAL PRIMARY KEY,
    id_expediente    integer  NOT NULL,
    id_tramite       integer  NOT NULL REFERENCES nombre_del_esquema.x_tramites (id_tramite) ON DELETE CASCADE,
    id_cargo_creador integer  NOT NULL REFERENCES nombre_del_esquema.aux_cargos (id_cargo) ON DELETE CASCADE,
    cargo_tipo       integer  NOT NULL,
    id_cargo         integer  NOT NULL REFERENCES nombre_del_esquema.aux_cargos (id_cargo) ON DELETE CASCADE,
    id_usuario       integer REFERENCES nombre_del_esquema.aux_usuarios (id_usuario) ON DELETE CASCADE,
    orden_tramite    smallint NOT NULL,
    orden_oficina    smallint,
    tipo             smallint NOT NULL,
    valor            smallint,
    observ_creador   text,
    observ           text,
    f_valor          timestamp
);


ALTER TABLE nombre_del_esquema.expediente_firmas OWNER TO tramity;

CREATE INDEX expediente_firmas_id_expediente_idx ON nombre_del_esquema.expediente_firmas (id_expediente);
CREATE INDEX expediente_firmas_id_tramite_idx ON nombre_del_esquema.expediente_firmas (id_tramite);
CREATE INDEX expediente_firmas_id_cargo_idx ON nombre_del_esquema.expediente_firmas (id_cargo);
CREATE INDEX expediente_firmas_tipo_idx ON nombre_del_esquema.expediente_firmas (tipo);

ALTER TABLE nombre_del_esquema.expediente_firmas
    ADD CONSTRAINT exp_tramite_firmas_ukey UNIQUE (id_expediente, id_cargo, tipo, id_tramite, orden_tramite,
                                                   orden_oficina);
--- porque seguramente creo la tabla de expedientes después
ALTER TABLE nombre_del_esquema.expediente_firmas
    ADD CONSTRAINT exp_tramite_firmas_fk FOREIGN KEY (id_expediente) REFERENCES nombre_del_esquema.expedientes (id_expediente) ON DELETE CASCADE;