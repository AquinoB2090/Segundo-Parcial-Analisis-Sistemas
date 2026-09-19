# Backlog Aplicado

Esta entrega prepara Laravel local, MySQL en Docker, esquema y datos semilla.
Referenciar un requisito funcional indica soporte de datos, no que esten
implementados sus endpoints o pantallas.

## Trabajo de Esta Rama

Rama: `feature/docker-mysql-schema`.

| Paso | Categoria | ID relacionados | Estado | Evidencia |
| --- | --- | --- | --- | --- |
| Laravel 12 y dependencias locales | Entorno | RQNF-04 | Hecho | `d3f2df5` |
| MySQL, volumen, puerto y healthcheck | Docker | RQNF-01, RQNF-02 | Hecho | `ece2260` |
| Configuracion MySQL y autoload en Windows | Entorno y Docker | RQNF-02 | Hecho | `0bb5348` |
| Migraciones, relaciones, indices y semillas | Datos | RQF-01, RQF-05, RQF-08, RQNF-01 | Hecho como base de datos | `5c47e4c` |
| Guia, evidencia y plantilla de PR | Documentacion | RQNF-05, RQNF-08 | Hecho en esta entrega | Commit `docs(RQNF-05,RQNF-08)` |

## Pendientes del Sistema

| Categoria | ID | Trabajo pendiente |
| --- | --- | --- |
| API REST | RQF-01, RQF-07, RQF-08, RQNF-03 | CRUD de citas, lecturas de doctores/pacientes, validaciones y JSON con 200/201, 400, 404 y 409. |
| Disponibilidad | RQF-03, RQNF-07 | Validar solapamientos en el servidor, incluyendo solicitudes concurrentes. |
| Reprogramacion | RQF-04 | Actualizar fecha/hora por API y sincronizar drag & drop. |
| Estados e historial | RQF-05 | Cambiar estado y cancelar sin eliminar registros. |
| Consultas | RQF-06 | Filtrar por doctor, paciente y fechas. |
| Calendario | RQF-02, RQF-09, RQF-10, RQNF-06 | FullCalendar, mes/semana, detalles, colores, escritorio y tablet. |
| Arquitectura | RQNF-04 | Completar presentacion, controladores API y logica de negocio. |
| Git | RQNF-05 | Completar ramas por funcionalidad, PR y merge documentados. |
| Evidencia | RQNF-08 | Agregar respuestas de API y capturas cuando existan esas funcionalidades. |

## Trazabilidad

Cada commit y PR debe mencionar los ID afectados y su validacion. Ramas
propuestas para el trabajo restante:

- `feature/api-rest-citas`: endpoints y codigos HTTP.
- `feature/disponibilidad-citas`: reglas de horario y concurrencia.
- `feature/calendario-citas`: calendario e interaccion.
- `feature/evidencia-integracion`: evidencia de integracion.

Estas ramas son propuestas, no un registro de creacion. Esta entrega tiene
commits locales en la rama actual; su push, PR y merge siguen pendientes.
