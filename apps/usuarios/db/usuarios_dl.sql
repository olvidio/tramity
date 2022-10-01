
CREATE TABLE public.x_oficinas (
		id_oficina SERIAL PRIMARY KEY,
		sigla text NOT NULL,
		orden smallint
);
ALTER TABLE public.x_oficinas OWNER TO tramity;

CREATE TABLE public.cargos_grupos (
    id_grupo SERIAL PRIMARY KEY,
    id_cargo_ref integer NOT NULL,
    descripcion text NOT NULL,
    miembros integer[]
);

CREATE UNIQUE INDEX cargos_grupos_id_cargo_ref_ukey ON public.cargos_grupos (id_cargo_ref);
ALTER TABLE public.cargos_grupos OWNER TO tramity;
