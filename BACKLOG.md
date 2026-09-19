# Backlog Aplicado

El proyecto incluye Laravel local, MySQL en Docker, esquema, semillas y la API
REST. El estado distingue la capacidad completa de los avances de backend.

## Trabajo de Esta Rama

Ramas documentadas: `feature/docker-mysql-schema`, `feature/api-rest-citas` y
`feature/validacion-conflictos-estados`.

| Paso | Categoria | ID relacionados | Estado | Evidencia |
| --- | --- | --- | --- | --- |
| Laravel 12 y dependencias locales | Entorno | RQNF-04 | Hecho | `d3f2df5` |
| MySQL, volumen, puerto y healthcheck | Docker | RQNF-01, RQNF-02 | Hecho | `ece2260` |
| Configuracion MySQL y autoload en Windows | Entorno y Docker | RQNF-02 | Hecho | `0bb5348` |
| Migraciones, relaciones, indices y semillas | Datos | RQF-01, RQF-05, RQF-08, RQNF-01 | Hecho como base de datos | `5c47e4c` |
| Guia, evidencia y plantilla de PR | Documentacion | RQNF-05, RQNF-08 | Hecho en esta entrega | Commit `docs(RQNF-05,RQNF-08)` |
| Siete endpoints, validaciones y recursos JSON | API REST | RQF-01, RQF-06, RQF-07, RQF-08, RQNF-03 | Hecho | `e84abcd` |
| Conflictos, reprogramacion y estados transaccionales | Negocio | RQF-03, RQF-04, RQF-05, RQNF-07 | Hecho en backend | `25773e7` |
| Pruebas HTTP en SQLite y MySQL | Calidad | RQF-01, RQF-03, RQF-07, RQF-08, RQNF-03 | Hecho | `1763961` |
| Contratos, ejemplos y evidencia de la API | Documentacion | RQNF-05, RQNF-08 | Hecho en esta entrega | Commit `docs(RQF-07,RQNF-08)` |
| Estados centralizados, mensajes en espanol y horas parciales | Validaciones | RQF-08, RQNF-03 | Hecho | `c62dd18` |
| Casos limite y cinco tipos de solapamiento | Calidad | RQF-03, RQF-08, RQNF-07 | Hecho | `9d0830b` |
| Matriz y evidencia de validaciones | Documentacion | RQNF-05, RQNF-08 | Hecho en esta entrega | Commit `docs(RQF-08,RQNF-08)` |

## Pendientes del Sistema

| Categoria | ID | Trabajo pendiente |
| --- | --- | --- |
| Calendario | RQF-02, RQF-09, RQF-10, RQNF-06 | FullCalendar, mes/semana, detalles, colores, escritorio y tablet. |
| Integracion visual | RQF-04 | Conectar el drag & drop de FullCalendar al `PUT` ya disponible. |
| Arquitectura | RQNF-04 | Completar la capa de presentacion; API, negocio y datos ya estan separados. |
| Git | RQNF-05 | Completar ramas por funcionalidad, PR y merge documentados. |
| Evidencia | RQNF-08 | Agregar capturas del calendario cuando exista esa funcionalidad. |

## Trazabilidad

Cada commit y PR debe mencionar los ID afectados y su validacion. Ramas
propuestas para el trabajo restante:

- `feature/calendario-citas`: calendario e interaccion.
- `feature/evidencia-integracion`: evidencia de integracion.

Las ramas restantes son propuestas, no un registro de creacion. Los commits de
validaciones estan locales en `feature/validacion-conflictos-estados`; su push,
PR y merge siguen pendientes.
