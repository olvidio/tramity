CREATE TABLE public.documentos (
    id_doc SERIAL PRIMARY KEY,
    nom text,
    nombre_fichero text,
    creador smallint,
	visibilidad smallint,
    f_upload date,
	tipo_doc smallint,
	documento bytea
);


ALTER TABLE public.documentos OWNER TO tramity;

CREATE INDEX IF NOT EXISTS documentos_f_upload_idx ON public.documentos (f_upload);
CREATE INDEX IF NOT EXISTS documentos_nom_idx ON public.documentos (nom);
CREATE INDEX IF NOT EXISTS documentos_creador_idx ON public.documentos (creador);
CREATE INDEX IF NOT EXISTS documentos_visibilidad_idx ON public.documentos (visibilidad);


CREATE TABLE public.etiquetas_documento (
	id_etiqueta  integer not null,
 	id_doc integer not null,
 	PRIMARY KEY (id_etiqueta, id_doc)
 );
 	
ALTER TABLE public.etiquetas_documento OWNER TO tramity;

ALTER TABLE public.etiquetas_documento ADD CONSTRAINT etiquetas_documento_fk_exp FOREIGN KEY (id_doc) REFERENCES public.documentos (id_doc) ON DELETE CASCADE;
ALTER TABLE public.etiquetas_documento ADD CONSTRAINT etiquetas_documento_fk_eti FOREIGN KEY (id_etiqueta) REFERENCES public.etiquetas (id_etiqueta) ON DELETE CASCADE;