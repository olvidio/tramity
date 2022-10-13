--- Para la tabla x_oficinas de la dl
INSERT INTO public.x_oficinas(id_oficina, sigla, orden)
VALUES (1, 'vcd', 10);
INSERT INTO public.x_oficinas(id_oficina, sigla, orden)
VALUES (2, 'sd', 20);
INSERT INTO public.x_oficinas(id_oficina, sigla, orden)
VALUES (3, 'vcsd', 30);
INSERT INTO public.x_oficinas(id_oficina, sigla, orden)
VALUES (4, 'scdl', 40);
INSERT INTO public.x_oficinas(id_oficina, sigla, orden)
VALUES (5, 'vsm', 50);
INSERT INTO public.x_oficinas(id_oficina, sigla, orden)
VALUES (6, 'vsg', 60);
INSERT INTO public.x_oficinas(id_oficina, sigla, orden)
VALUES (7, 'vsr', 70);
INSERT INTO public.x_oficinas(id_oficina, sigla, orden)
VALUES (8, 'agd', 80);
INSERT INTO public.x_oficinas(id_oficina, sigla, orden)
VALUES (9, 'vest', 90);
INSERT INTO public.x_oficinas(id_oficina, sigla, orden)
VALUES (10, 'adl', 100);
INSERT INTO public.x_oficinas(id_oficina, sigla, orden)
VALUES (11, 'des', 110);
INSERT INTO public.x_oficinas(id_oficina, sigla, orden)
VALUES (12, 'ofsvsm', 120);
INSERT INTO public.x_oficinas(id_oficina, sigla, orden)
VALUES (13, 'aop', 130);
INSERT INTO public.x_oficinas(id_oficina, sigla, orden)
VALUES (14, 'soi', 140);
INSERT INTO public.x_oficinas(id_oficina, sigla, orden)
VALUES (15, 'ocs', 150);
--- empezar a contar en 20.
SELECT pg_catalog.setval('public.x_oficinas_id_oficina_seq', 20, true);
---
--- Para la tabla aux_cargos de la dl
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (1, 3, 'ponente', 'ponente', 0, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (2, 3, 'oficiales', 'del ponente', 0, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (3, 3, 'varias', 'varias', 0, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (4, 3, 'todos_d', 'todos d', 0, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (5, 3, 'vºbº vcd', 'listo para reunión', 0, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (6, 3, 'secretaria', 'secretaria', 0, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (7, 3, 'reunion', 'reunion', 0, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (8, 3, 'distribuir', 'scdl secretaria', 0, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (9, 3, 'convocar_reunion', 'scdl reunion', 0, 'f', 'f');
--- cargos tipicos 
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (10, 3, 'vsg', 'Vocal de San Gabriel', 6, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (11, 3, 'vsm', 'Vocal de San Miguel', 5, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (12, 3, 'vsr', 'Vocal de San Rafael', 7, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (13, 3, 'vstgr', 'Vicedecano stgr', 9, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (14, 3, 'vcd', 'Vicario de la Delegación', 1, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (15, 3, 'scdl', 'scdl', 4, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (16, 3, 'sd', 'Subdirector de la Delegación', 2, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (17, 3, 'soi', 'Oficina de soi', 14, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (18, 3, 'adl', 'Administrador', 10, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (19, 3, 'aop', 'Oficina aop', 13, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (20, 3, 'dagd', 'dagd', 8, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (21, 3, 'dre', 'Director espritiual', 11, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (22, 3, 'ocs', 'Causas de los santos', 15, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (23, 3, 'vcsd', 'Vicario Secretario', 3, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (24, 3, 'vest', 'Vocal de estudios', 9, 'f', 'f');
--- oficiales
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (25, 3, 'of1scdl', 'Oficial de Secretaria', 4, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (26, 3, 'of1adl', 'Oficial de adl', 10, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (27, 3, 'of1agd', 'Oficial de Agregados', 8, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (28, 3, 'of1aop', 'Oficial de aop', 13, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (29, 3, 'of1est', 'Oficial de estudios', 9, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (30, 3, 'of1ocs', 'Oficial de ocs', 15, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (31, 3, 'of1sg', 'Oficial san Gabriel', 6, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (32, 3, 'of1sm', 'Oficial de ofs vsm', 5, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (33, 3, 'of1soi', 'Oficial de soi', 14, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (34, 3, 'of1sr', 'Oficial san Rafael', 7, 'f', 'f');
INSERT INTO public.aux_cargos (id_cargo, id_ambito, cargo, descripcion, id_oficina, director, sacd)
VALUES (35, 3, 'ofsvsm', 'Oficial Suplente de vsm', 12, 'f', 'f');
--- empezar a contar en 50.
SELECT pg_catalog.setval('public.aux_cargos_id_cargo_seq', 50, true);
---
--- Para la tabla cargos_grupos de la dl
INSERT INTO public.cargos_grupos (id_grupo, id_cargo_ref, descripcion, miembros)
VALUES (1, 9, 'todos los d', '{18,20,21,23,24,10,11,12}');
--- empezar a contar en 2.
SELECT pg_catalog.setval('public.cargos_grupos_id_grupo_seq', 2, true);

