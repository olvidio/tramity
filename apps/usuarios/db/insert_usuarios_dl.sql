--- Para la tabla aux_cargos de la dl
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (1, 3, 'ponente', 'ponente', 0, 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (2, 3, 'oficiales', 'del ponente', 0, 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (3, 3, 'varias', 'varias' , 0, 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (4, 3, 'todos_d', 'todos d', 0, 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (5, 3, 'vºbº vcd', 'listo para reunión', 0, 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (6, 3, 'secretaria', 'secretaria', 0, 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director) VALUES (7, 3, 'reunion', 'reunion', 0, 'f');
--- empezar a contar en 10.
SELECT pg_catalog.setval('public.aux_cargos_id_cargo_seq', 10, true);