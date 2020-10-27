
CREATE TABLE public.lugares (
    id_lugar SERIAL PRIMARY KEY,
    sigla text NOT NULL,
    dl character varying(6),
    region character varying(5),
    nombre character varying(35),
    tipo_ctr character varying(5),
    modo_envio smallint,
    pub_key bytea,
    e_mail text,
    anulado boolean DEFAULT false NOT NULL
);


ALTER TABLE public.lugares OWNER TO tramity;


CREATE INDEX sigla_lugares_key ON public.lugares USING btree (sigla);

CREATE TABLE public.lugares_grupos (
    id_grupo SERIAL PRIMARY KEY,
    descripcion text NOT NULL,
    miembros integer[]
);

ALTER TABLE public.lugares_grupos OWNER TO tramity;


