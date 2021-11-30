--- Para la tabla aux_cargos de un ctr
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (1, 4, 'ponente', 'ponente', 0, 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (2, 4, 'oficiales', 'del ponente', 0, 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (3, 4, 'varias', 'varias' , 0, 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (4, 4, 'todos', 'todos', 0, 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (5, 4, 'vºbº d', 'listo para reunión', 0, 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (6, 4, 'secretaria', 'secretaria', 0, 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (7, 4, 'reunion', 'reunion', 0, 'f');
--- empezar a contar en 10.
SELECT pg_catalog.setval('public.aux_cargos_id_cargo_seq', 10, true);