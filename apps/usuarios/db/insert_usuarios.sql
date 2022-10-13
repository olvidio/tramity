--- Para la tabla aux_cargos de un ctr
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (1, 4, 'ponente', 'ponente', 0, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (4, 4, 'todos', 'todos', 0, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (7, 4, 'reunion', 'reunion', 0, 'f', 'f');
--- empezar a contar en 10.
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (10, 4, 'd', 'director', 0, 't', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (11, 4, 'sd', 'subdirector', 0, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (12, 4, 'scl', 'secretario', 0, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (13, 4, 'sacd', 'sacerdote', 0, 'f', 't');
SELECT pg_catalog.setval('public.aux_cargos_id_cargo_seq', 14, true);
