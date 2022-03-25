--- by pass
-- OJO; Para los indices array integer ---
CREATE EXTENSION IF NOT EXISTS intarray;
CREATE TABLE public.entradas_bypass (
	id_item SERIAL PRIMARY KEY,
    id_entrada integer NOT NULL,
    descripcion text NOT NULL,
    json_prot_destino jsonb,
    id_grupos integer[],
    destinos integer[],
    f_salida date,
    CONSTRAINT fk_entrada
      FOREIGN KEY(id_entrada) 
	  REFERENCES public.entradas(id_entrada) ON DELETE CASCADE
);

ALTER TABLE public.entradas_bypass OWNER TO tramity;

CREATE UNIQUE INDEX IF NOT EXISTS entradas_bypass_id_entrada_idx ON public.entradas_bypass (id_entrada);
CREATE INDEX IF NOT EXISTS entradas_bypass_destinos_idx ON public.entradas_bypass USING GIN (destinos gin__int_ops);
CREATE INDEX IF NOT EXISTS entradas_bypass_id_grupos_idx ON public.entradas_bypass USING GIN (id_grupos gin__int_ops);