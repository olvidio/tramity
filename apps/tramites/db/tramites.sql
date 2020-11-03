
CREATE TABLE public.x_tramites (
    id_tramite SERIAL PRIMARY KEY,
    tramite text NOT NULL,
    orden smallint,
    breve varchar(10)
);


ALTER TABLE public.x_tramites OWNER TO tramity;

INSERT INTO x_tramites (tramite, orden, breve) VALUES ('de trámite (los "E-12")', 10, 'E12');
INSERT INTO x_tramites (tramite, orden, breve) VALUES ('ordinarios', 20, 'ord.');
INSERT INTO x_tramites (tramite, orden, breve) VALUES ('de despacho', 30, 'desp.');
INSERT INTO x_tramites (tramite, orden, breve) VALUES ('extraordinarios', 40, 'extra.');
INSERT INTO x_tramites (tramite, orden, breve) VALUES ('voto consultivo', 50, 'cons.');
INSERT INTO x_tramites (tramite, orden, breve) VALUES ('voto deliberativo', 60, 'delib.');
INSERT INTO x_tramites (tramite, orden, breve) VALUES ('comisión de trabajo', 70, 'c. t.');


CREATE TABLE public.tramite_cargo (
    id_item SERIAL PRIMARY KEY,
    id_tramite integer NOT NULL,
    orden_tramite smallint NOT NULL,
    id_cargo integer NOT NULL,
    multiple smallint
);

ALTER TABLE public.tramite_cargo OWNER TO tramity;

CREATE INDEX tramite_cargo_id_tramite_idx ON tramite_cargo (id_tramite);
CREATE INDEX tramite_cargo_id_cargo_idx ON tramite_cargo (id_cargo);
ALTER TABLE tramite_cargo ADD CONSTRAINT tramite_cargo_orden_tramite_ukey UNIQUE (id_tramite,orden_tramite);


CREATE TABLE public.expediente_firmas (
    id_item SERIAL PRIMARY KEY,
    id_expediente integer NOT NULL,
    id_tramite integer NOT NULL REFERENCES x_tramites(id_tramite) ON DELETE CASCADE,
    id_cargo_creador integer NOT NULL REFERENCES aux_cargos(id_cargo) ON DELETE CASCADE,
    id_cargo integer NOT NULL REFERENCES aux_cargos(id_cargo) ON DELETE CASCADE,
    orden_tramite smallint NOT NULL,
    orden_oficina smallint,
    tipo smallint NOT NULL,
    valor smallint,
    observ_creador text,
    observ text,
    f_valor timestamp
);


ALTER TABLE public.expediente_firmas OWNER TO tramity;

CREATE INDEX expediente_firmas_id_expediente_idx ON expediente_firmas (id_expediente);
CREATE INDEX expediente_firmas_id_tramite_idx ON expediente_firmas (id_tramite);
CREATE INDEX expediente_firmas_id_cargo_idx ON expediente_firmas (id_cargo);
CREATE INDEX expediente_firmas_tipo_idx ON expediente_firmas (tipo);

ALTER TABLE public.expediente_firmas ADD CONSTRAINT exp_tramite_firmas_ukey UNIQUE (id_expediente,id_cargo,tipo,orden_tramite);
--- porque seguramente creo la tabla de expedientes después
ALTER TABLE public.expediente_firmas ADD CONSTRAINT exp_tramite_firmas_fk FOREIGN KEY (id_expediente) REFERENCES expedientes (id_expediente) ON DELETE CASCADE;
