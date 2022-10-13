CREATE TABLE public.lugares_grupos
(
    id_grupo    SERIAL PRIMARY KEY,
    descripcion text NOT NULL,
    miembros    integer[]
);

ALTER TABLE public.lugares_grupos OWNER TO tramity;