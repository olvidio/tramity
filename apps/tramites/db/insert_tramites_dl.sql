--- Para la tabla x_tramites de la dl
INSERT INTO public.x_tramites (id_tramite, tramite, orden, breve) VALUES (1, 'de trámite (los "E-12")', 10, 'E12');
INSERT INTO public.x_tramites (id_tramite, tramite, orden, breve) VALUES (2, 'ordinarios', 20, 'ord.');
INSERT INTO public.x_tramites (id_tramite, tramite, orden, breve) VALUES (3, 'de despacho', 30, 'desp.');
INSERT INTO public.x_tramites (id_tramite, tramite, orden, breve) VALUES (4, 'extraordinarios', 40, 'extra.');
INSERT INTO public.x_tramites (id_tramite, tramite, orden, breve) VALUES (5, 'voto consultivo', 50, 'cons.');
INSERT INTO public.x_tramites (id_tramite, tramite, orden, breve) VALUES (6, 'voto deliberativo', 60, 'delib.');
INSERT INTO public.x_tramites (id_tramite, tramite, orden, breve) VALUES (7, 'comisión de trabajo', 70, 'c. t.');
--- empezar a contar en 10.
SELECT pg_catalog.setval('public.x_tramites_id_tramite_seq', 10, true);
---
--- Para la tabla tramite_cargo de la dl
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (1, 1, 5, 2, 0);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (2, 1, 10, 1, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (3, 1, 20, 3, 2);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (4, 1, 30, 8, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (5, 2, 5, 2, 0);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (6, 2, 10, 1, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (7, 2, 20, 3, 2);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (8, 2, 30, 15, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (9, 2, 40, 8, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (10, 4, 10, 1, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (11, 4, 20, 3, 0);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (12, 4, 30, 15, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (13, 4, 40, 16, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (14, 4, 50, 5, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (15, 4, 60, 9, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (16, 4, 70, 4, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (17, 4, 80, 14, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (18, 4, 90, 8, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (19, 3, 5, 2, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (20, 3, 10, 1, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (21, 3, 20, 3, 0);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (22, 3, 30, 15, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (23, 3, 40, 16, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (24, 3, 50, 14, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (25, 3, 60, 8, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (26, 5, 5, 2, 0);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (27, 5, 10, 1, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (28, 5, 15, 3, 0);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (29, 5, 20, 15, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (31, 5, 30, 23, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (32, 5, 40, 16, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (33, 5, 50, 14, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (34, 5, 60, 8, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (35, 6, 5, 2, 0);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (36, 6, 10, 1, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (37, 6, 15, 3, 0);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (38, 6, 20, 15, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (39, 6, 30, 16, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (40, 6, 40, 5, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (41, 6, 50, 9, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (42, 6, 60, 4, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (43, 6, 70, 14, 1);
INSERT INTO public.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)  VALUES (44, 6, 80, 8, 1);
--- empezar a contar en 30.
SELECT pg_catalog.setval('public.tramite_cargo_id_item_seq', 45, true);

