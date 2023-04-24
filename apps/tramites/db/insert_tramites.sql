--- Para la tabla x_tramites del ctr
INSERT INTO nombre_del_esquema.x_tramites (id_tramite, tramite, orden, breve)
VALUES (2, 'ordinarios', 20, 'ord.');

--- empezar a contar en 10.
SELECT pg_catalog.setval('nombre_del_esquema.x_tramites_id_tramite_seq', 10, true);
-- Para la tabla tramite_cargo de la dl
INSERT INTO nombre_del_esquema.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)
VALUES (1, 2, 10, 1, 1);
INSERT INTO nombre_del_esquema.tramite_cargo (id_item, id_tramite, orden_tramite, id_cargo, multiple)
VALUES (2, 2, 30, 2, 1);