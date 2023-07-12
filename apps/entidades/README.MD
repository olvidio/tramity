Este módulo se ha introducido para poder unsar un mismo servidor para varias entidades simultáneamente.

cambios en postgresql
=====================

Hay que cambiar la estructura de la base de datos:

- En el esquema public, se dejan las tablas que afectan a la instalación del servidor y la administración del mismo:

```
             List of relations
 Schema |         Name         | Type  |  Owner  
--------+----------------------+-------+---------
 public | aux_usuarios         | table | tramity
 public | entidades            | table | tramity
 public | lugares              | table | tramity
 public | usuario_preferencias | table | tramity
 public | x_config             | table | tramity
 public | x_locales            | table | tramity
 public | x_timezones            | table | tramity
```

- para cada nombre_entidad se debe crear un esquema con el nombre de la nombre_entidad (normalizado: existe una función para
  normalizar, quitar espacios, pasar a minúscula etc.)

para una nombre_entidad tipo centro, las tablas son:

```
                 List of relations
   Schema    |         Name         | Type  |  Owner  
-------------+----------------------+-------+---------
 agdmontagut | acciones             | table | tramity
 agdmontagut | aux_cargos           | table | tramity
 agdmontagut | aux_usuarios         | table | tramity
 agdmontagut | documentos           | table | tramity
 agdmontagut | entrada_adjuntos     | table | tramity
 agdmontagut | entrada_doc          | table | tramity
 agdmontagut | entrada_doc_bytea    | table | tramity
 agdmontagut | entrada_doc_json     | table | tramity
 agdmontagut | entrada_doc_txt      | table | tramity
 agdmontagut | entradas             | table | tramity
 agdmontagut | escrito_adjuntos     | table | tramity
 agdmontagut | escritos             | table | tramity
 agdmontagut | etiquetas            | table | tramity
 agdmontagut | etiquetas_documento  | table | tramity
 agdmontagut | etiquetas_expediente | table | tramity
 agdmontagut | expediente_firmas    | table | tramity
 agdmontagut | expedientes          | table | tramity
 agdmontagut | pendientes           | table | tramity
 agdmontagut | plantillas           | table | tramity
 agdmontagut | tramite_cargo        | table | tramity
 agdmontagut | usuario_preferencias | table | tramity
 agdmontagut | x_config             | table | tramity
 agdmontagut | x_tramites           | table | tramity
```

Para una nombre_entidad tipo dl, se añaden las tablas:

```
                List of relations
 Schema |         Name         | Type  |  Owner  
--------+----------------------+-------+---------
 dlb    | cargos_grupos        | table | tramity
 dlb    | entradas_bypass      | table | tramity
 dlb    | lugares_grupos       | table | tramity
 dlb    | x_oficinas           | table | tramity
```

cambios en programa
===================

- El nombre del esquema se obtiene del nombre del host de la URL (agdmontagut.tramity.red.local)

- El nombre de los etherpad, pasa a contener el nombre de la nombre_entidad
- El nombre de los calendarios de pendientes, pasa a tener el nombre de la nombre_entidad.

- Los usuarios en davical, pasan de ser los cargos, a ser (nombre nombre_entidad).cargo

simplificación para los ctr
===========================

- en los escritos, el destino sólo puede ser la nombre_entidad superior (dl) y las referencias pueden ser: al propio ctr, a la
  dl, a cr.


 