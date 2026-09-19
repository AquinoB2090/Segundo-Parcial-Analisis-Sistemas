# Requerimientos del Sistema de Citas Medicas

## 1. Gestion de Citas Medicas

| Codigo | Tipo | Requerimiento |
| --- | --- | --- |
| RQF-01 | Funcional | El sistema debe permitir crear una cita medica indicando paciente, doctor, fecha, hora de inicio/fin y motivo. |
| RQF-03 | Funcional | El sistema debe impedir la doble reserva: no puede existir mas de una cita activa para el mismo doctor en horarios que se solapan. |
| RQF-04 | Funcional | El sistema debe permitir reprogramar una cita arrastrandola en el calendario, sincronizando el cambio con la base de datos via API. |
| RQF-05 | Funcional | El sistema debe permitir cancelar una cita cambiando su estado, sin eliminar el registro historico. |
| RQF-08 | Funcional | El sistema debe validar los datos de entrada: campos obligatorios, formato de fecha/hora, existencia del paciente y del doctor antes de persistir. |
| RQNF-07 | No funcional | La validacion de disponibilidad de horario debe ejecutarse en el servidor, no unicamente en el cliente. |

## 2. Calendario e Interfaz de Usuario

| Codigo | Tipo | Requerimiento |
| --- | --- | --- |
| RQF-02 | Funcional | El sistema debe mostrar las citas en un calendario interactivo usando FullCalendar, con al menos las vistas de mes y semana. |
| RQF-04 | Funcional | El sistema debe permitir reprogramar una cita mediante drag & drop en el calendario. |
| RQF-09 | Funcional | El sistema debe mostrar el detalle de una cita al hacer clic sobre el evento correspondiente en el calendario. |
| RQF-10 | Funcional | El sistema debe representar visualmente el estado de cada cita mediante color: pendiente, confirmada, cancelada y atendida. |
| RQNF-06 | No funcional | La interfaz del calendario debe ser usable en resoluciones de escritorio y tablet como minimo. |

## 3. Filtros y Consultas

| Codigo | Tipo | Requerimiento |
| --- | --- | --- |
| RQF-06 | Funcional | El sistema debe permitir filtrar/listar las citas por doctor y por rango de fechas. |
| RQF-07 | Funcional | El sistema debe exponer operaciones de lectura para doctores y pacientes. |

## 4. API REST

| Codigo | Tipo | Requerimiento |
| --- | --- | --- |
| RQF-07 | Funcional | El sistema debe exponer una API REST con operaciones CRUD sobre citas y operaciones de lectura para doctores y pacientes. |
| RQNF-03 | No funcional | La API debe responder en formato JSON y usar codigos HTTP correctos: 200/201 para exito, 400 para datos invalidos, 404 para no encontrado y 409 para conflicto de horario. |

### Endpoints sugeridos

| Metodo | Ruta | Proposito |
| --- | --- | --- |
| GET | `/api/citas` | Lista citas. Admite filtros `doctor_id`, `paciente_id`, `desde`, `hasta`. Usado para pintar el calendario. |
| POST | `/api/citas` | Crea una cita nueva. Responde `409` si hay conflicto de horario con el mismo doctor. |
| GET | `/api/citas/{id}` | Devuelve el detalle de una cita. |
| PUT | `/api/citas/{id}` | Reprograma la cita. Usado por el evento drag & drop del calendario. |
| PATCH | `/api/citas/{id}/estado` | Cambia el estado de la cita: confirmar, cancelar o atender. |
| GET | `/api/doctores` | Lista doctores disponibles, usada para el formulario de creacion y los filtros. |
| GET | `/api/pacientes` | Lista pacientes registrados, usada para el formulario de creacion. |

## 5. Base de Datos e Infraestructura

| Codigo | Tipo | Requerimiento |
| --- | --- | --- |
| RQNF-01 | No funcional | La base de datos MySQL debe ejecutarse en un contenedor Docker con persistencia mediante volumen. |
| RQNF-02 | No funcional | El entorno de base de datos debe poder levantarse con un solo comando: `docker compose up`. |

## 6. Arquitectura del Codigo

| Codigo | Tipo | Requerimiento |
| --- | --- | --- |
| RQNF-04 | No funcional | El codigo debe organizarse por capas: presentacion/calendario, API, logica de negocio y acceso a datos. |

## 7. Trazabilidad y Evidencia

| Codigo | Tipo | Requerimiento |
| --- | --- | --- |
| RQNF-05 | No funcional | El repositorio debe mantener trazabilidad Git: minimo 4 ramas por feature, commits descriptivos, Pull Request y merge a `main` documentados. |
| RQNF-08 | No funcional | Toda evidencia, como capturas, comandos, respuestas de API e historial Git, debe quedar documentada en el PR o en un archivo de evidencia. |

## 8. Criterios de Entrega a Tener en Cuenta

| Categoria | Criterio |
| --- | --- |
| API REST | La API REST debe estar funcional, cubriendo el CRUD de citas, validaciones de datos y codigos HTTP correctos. |
| Docker y MySQL | El proyecto debe incluir Docker Compose, esquema MySQL con persistencia mediante volumen y datos semilla. |
| Backlog y trazabilidad | El backlog debe estar aplicado y debe existir trazabilidad de commits y Pull Requests usando los ID de requerimientos `RQF` y `RQNF`. |
