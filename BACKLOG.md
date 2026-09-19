# Backlog Aplicado

El proyecto incluye Laravel local, MySQL en Docker, esquema, semillas y la API
REST. El estado distingue la capacidad completa de los avances de backend.

## Trabajo de Esta Rama

Ramas documentadas: `feature/docker-mysql-schema`, `feature/api-rest-citas`,
`feature/validacion-conflictos-estados` y `feature/fullcalendar-ui`.

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
| Base visual, dependencias y controles adaptables | Frontend | RQF-02, RQNF-06 | Hecho | `2639197` |
| FullCalendar, API, detalle, estados y drag & drop | Frontend | RQF-02, RQF-04, RQF-09, RQF-10 | Hecho | `f720ea0` |
| Pruebas de transformaciones, rango y pagina | Calidad | RQF-02, RQF-04, RQNF-06 | Hecho | `bb5d02a` |
| Guia y evidencia del calendario | Documentacion | RQNF-05, RQNF-08 | Hecho en esta entrega | Commit `docs(RQF-02,RQNF-08)` |

## Pendientes del Sistema

| Categoria | ID | Trabajo pendiente |
| --- | --- | --- |
| Git | RQNF-05 | Completar ramas por funcionalidad, PR y merge documentados. |
| Evidencia visual | RQNF-06, RQNF-08 | Capturar escritorio y tablet cuando el navegador integrado este disponible. |

## Trazabilidad

Cada commit y PR debe mencionar los ID afectados y su validacion. Ramas
propuestas para el trabajo restante:

- `feature/evidencia-integracion`: evidencia de integracion.

La rama restante es propuesta, no un registro de creacion. Los commits del
calendario estan locales en `feature/fullcalendar-ui`; su push, PR y merge
siguen pendientes.
