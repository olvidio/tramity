CREATE TABLE IF NOT EXISTS public.x_config (
    parametro text NOT NULL,
    valor text
    );
        
ALTER TABLE public.x_config OWNER TO tramity;

ALTER TABLE public.x_config ADD PRIMARY KEY (parametro);