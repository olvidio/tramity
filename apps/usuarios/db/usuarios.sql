CREATE TABLE nombre_del_esquema.aux_cargos
(
    id_cargo    SERIAL PRIMARY KEY,
    id_ambito   integer               NOT NULL,
    cargo       character varying(20) NOT NULL,
    descripcion text,
    id_oficina  integer               NOT NULL,
    director    boolean               NOT NULL DEFAULT 't',
    sacd        boolean               NOT NULL DEFAULT 'f',
    id_usuario  integer,
    id_suplente integer,
    activo      boolean               NOT NULL DEFAULT 't'
);

COMMENT
ON COLUMN nombre_del_esquema.aux_cargos.id_cargo IS 'corresponde al cargo';
COMMENT
ON COLUMN nombre_del_esquema.aux_cargos.id_ambito IS 'corresponde al tipo de instalacion: 1=cg, 2=cr, 3=dl, 4=ctr';
COMMENT
ON COLUMN nombre_del_esquema.aux_cargos.director IS 'director u oficial';
ALTER TABLE nombre_del_esquema.aux_cargos OWNER TO tramity;

CREATE TABLE nombre_del_esquema.aux_usuarios
(
    id_auto         SERIAL PRIMARY KEY,
    id_usuario         integer NOT NULL DEFAULT idglobal('nombre_del_esquema'::text),
    usuario            character varying(20) NOT NULL,
    id_cargo_preferido integer               NOT NULL,
    password           bytea,
    email              text,
    nom_usuario        text,
    activo             boolean               NOT NULL DEFAULT 't'
);
COMMENT
ON COLUMN nombre_del_esquema.aux_usuarios.id_cargo_preferido IS 'corresponde al cargo por defecto o preferido';
CREATE INDEX ON nombre_del_esquema.aux_usuarios ((lower (usuario)));
CREATE UNIQUE INDEX IF NOT EXISTS aux_usuarios_id_usuario_idx ON nombre_del_esquema.aux_usuarios (id_usuario);
ALTER TABLE nombre_del_esquema.aux_usuarios OWNER TO tramity;


INSERT INTO nombre_del_esquema.aux_usuarios (usuario, id_cargo_preferido, password, nom_usuario)
VALUES ('default', 0, '', 'NO BORRAR!');
--- admin, pwd: admin
INSERT INTO nombre_del_esquema.aux_usuarios (usuario, id_cargo_preferido, password, nom_usuario)
VALUES ('admin', 0,
        '\x37656362643933383430636566393563323666633463333331356139613965623732346434333137393634303034623539663834663861363466356564343161313464333935616236646134646366663762353633353363393466316266306338643363636335646638616136313564633735373663656532663534653736343734316461656538323135383766666132383235353537316231636534353762623466356437656236643433333965303737656561636334643763343334663531306464363331616331626430636135626431633132323131623565333635643036373038623563656431323632653035356236363036613764613937633736316265383064636461353336393165373033643961303530613465383332353763336164333262643965346132323632346461396164656566383032626336383965656164333731',
        'NO BORRAR!');
--- dani, pwd: system
INSERT INTO nombre_del_esquema.aux_usuarios (usuario, id_cargo_preferido, password, nom_usuario)
VALUES ('manager', 0,
        '\x36376163623761626432333432306134326263626261626139666264633439336330633738326635333930656261336432333633643838383264356362376631656465366331623364666234333237653761396431643832373264613430363830663361633836303166666132356636386236653233323438363061353332616434333335613564653563303730303038326530393933663661306566613966363639633264333161343463376530373730393432633739336166316230323638336133306538333061386338396231393766616134663939363139656537623062326533373032303061383838303664636132383132336462333165323131303536366365663933663436333766623338343861313233646631643139326333663931633436316366393936643466656533336363346435333062643762383030646265356231',
        'administrador de la dl');

CREATE TABLE nombre_del_esquema.usuario_preferencias
(
    id_item     SERIAL PRIMARY KEY,
    id_usuario  integer NOT NULL,
    tipo        text    NOT NULL,
    preferencia text
);

CREATE UNIQUE INDEX usuario_preferencias_id_usuario_tipo_ukey ON nombre_del_esquema.usuario_preferencias (id_usuario, tipo);
ALTER TABLE nombre_del_esquema.usuario_preferencias OWNER TO tramity;
