--- Para la tabla x_tramites de la dl
INSERT INTO public.x_tramites (id_tramite, tramite, orden, breve)
VALUES (2, 'ordinarios', 20, 'ord.');
--- empezar a contar en 10.
SELECT pg_catalog.setval('public.x_tramites_id_tramite_seq', 10, true);