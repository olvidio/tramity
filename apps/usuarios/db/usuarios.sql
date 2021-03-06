

CREATE TABLE aux_usuarios (
    id_usuario SERIAL PRIMARY KEY,
    usuario character varying(20) NOT NULL,
    id_cargo integer NOT NULL,
    password bytea,
    email text,
    nom_usuario text
);
COMMENT ON COLUMN aux_usuarios.id_cargo IS 'corresponde al cargo por defecto o preferido';
CREATE INDEX ON aux_usuarios ((lower(usuario)));
ALTER TABLE aux_usuarios OWNER TO tramity;

INSERT INTO aux_usuarios (usuario, id_cargo, password) VALUES ('default', 1, '');
--- admin, pwd: admin
INSERT INTO aux_usuarios (usuario, id_cargo, password) VALUES ( 'admin', 1, '\x37656362643933383430636566393563323666633463333331356139613965623732346434333137393634303034623539663834663861363466356564343161313464333935616236646134646366663762353633353363393466316266306338643363636335646638616136313564633735373663656532663534653736343734316461656538323135383766666132383235353537316231636534353762623466356437656236643433333965303737656561636334643763343334663531306464363331616331626430636135626431633132323131623565333635643036373038623563656431323632653035356236363036613764613937633736316265383064636461353336393165373033643961303530613465383332353763336164333262643965346132323632346461396164656566383032626336383965656164333731');
--- dani, pwd: system
INSERT INTO aux_usuarios (usuario, id_cargo, password) VALUES ('dani', 1, '\x36376163623761626432333432306134326263626261626139666264633439336330633738326635333930656261336432333633643838383264356362376631656465366331623364666234333237653761396431643832373264613430363830663361633836303166666132356636386236653233323438363061353332616434333335613564653563303730303038326530393933663661306566613966363639633264333161343463376530373730393432633739336166316230323638336133306538333061386338396231393766616134663939363139656537623062326533373032303061383838303664636132383132336462333165323131303536366365663933663436333766623338343861313233646631643139326333663931633436316366393936643466656533336363346435333062643762383030646265356231');


CREATE TABLE aux_cargos (
    id_cargo SERIAL PRIMARY KEY,
    id_ambito integer NOT NULL,
    cargo character varying(20) NOT NULL,
    descripcion text,
    id_oficina integer NOT NULL,
    director boolean NOT NULL DEFAULT 't',
    id_usuario integer,
    id_suplente integer
);

COMMENT ON COLUMN aux_cargos.id_cargo IS 'corresponde al cargo';
COMMENT ON COLUMN aux_cargos.id_ambito IS 'corresponde al tipo de instalacion: 1=cg, 2=cr, 3=dl, 4=ctr';
COMMENT ON COLUMN aux_cargos.director IS 'director u oficial';
ALTER TABLE aux_cargos OWNER TO tramity;

INSERT INTO aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (1, 3, 'ponente', 'ponente', 0, 'f');
INSERT INTO aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (2, 3, 'oficiales', 'del ponente', 0, 'f');
INSERT INTO aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (3, 3, 'varias', 'varias' , 0, 'f');
INSERT INTO aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (4, 3, 'todos_d', 'todos d', 0, 'f');
INSERT INTO aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (5, 3, 'vºbº vcd', 'listo para reunión', 0, 'f');
INSERT INTO aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (6, 3, 'secretaria', 'secretaria', 0, 'f');
INSERT INTO aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (7, 3, 'reunion', 'reunion', 0, 'f');
--- empezar a contar en 10.
SELECT pg_catalog.setval('public.aux_cargos_id_cargo_seq', 10, true);


CREATE TABLE x_oficinas (
		id_oficina SERIAL PRIMARY KEY,
		sigla text NOT NULL,
		orden smallint
);
ALTER TABLE x_oficinas OWNER TO tramity;


CREATE TABLE usuario_preferencias (
    id_item SERIAL PRIMARY KEY,
	id_usuario integer NOT NULL,
	tipo text NOT NULL,
	preferencia text
);

CREATE UNIQUE INDEX "usuario_preferencias_id_usuario_tipo_ukey" ON usuario_preferencias (id_usuario, tipo);
ALTER TABLE usuario_preferencias OWNER TO tramity;

CREATE TABLE public.cargos_grupos (
    id_grupo SERIAL PRIMARY KEY,
    id_cargo_ref integer NOT NULL,
    descripcion text NOT NULL,
    miembros integer[]
);

CREATE UNIQUE INDEX "cargos_grupos_id_cargo_ref_ukey" ON cargos_grupos (id_cargo_ref);
ALTER TABLE public.cargos_grupos OWNER TO tramity;

