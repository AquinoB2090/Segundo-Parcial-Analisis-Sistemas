# Sistema de Citas Medicas

Laravel 12 se ejecuta localmente con PHP. Docker Compose ejecuta solamente
MySQL 8.4, con volumen persistente y comprobacion de salud.

## Requisitos

- PHP 8.2+ y Composer 2, con las extensiones de Laravel y `pdo_mysql`.
- `pdo_sqlite` para pruebas; `zip` recomendada para Composer; `intl` para `db:show`.
- Node.js compatible con Vite 7 (20.19+ o 22.12+) y npm.
- Docker Desktop iniciado con contenedores Linux y Docker Compose v2.

Dependencias fijadas en `composer.lock` y `package-lock.json`.
Imagen MySQL fijada por digest en [compose.yaml](compose.yaml).

## Primera Instalacion

Desde la raiz del repositorio:

```sh
composer run setup
php artisan serve --host=127.0.0.1 --port=8000
```

`setup` instala dependencias, crea `.env` si no existe, genera `APP_KEY`,
levanta MySQL y espera su estado saludable, ejecuta migraciones y semillas,
e instala y compila recursos con npm. Es un comando de instalacion inicial:
si se repite, genera una nueva clave de aplicacion.

Aplicacion: <http://127.0.0.1:8000>. Salud: <http://127.0.0.1:8000/up>.
La pantalla inicial es la agenda medica con FullCalendar; la API REST esta
disponible bajo `/api`.

## Arranque Habitual

```sh
docker compose up -d --wait
php artisan serve --host=127.0.0.1 --port=8000
```

La base tambien puede levantarse en primer plano con `docker compose up`,
incluso sin `.env`, usando los valores locales predeterminados. Las migraciones
y semillas se ejecutan desde Laravel. Para recargar recursos durante el
desarrollo, usar `composer run dev` en lugar de `artisan serve`.

## Conexion y Persistencia

| Dato | Valor local predeterminado |
| --- | --- |
| Host desde Laravel | `127.0.0.1` |
| Puerto en el equipo / contenedor | `3307` / `3306` |
| Base de datos | `citas_medicas` |
| Usuario | `citas_app` |
| Contrasena local | `citas_local_password` |
| Volumen | `citas-medicas_mysql_data` |
| Zona horaria de Laravel | `America/Guatemala` |

Laravel y Compose comparten `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` y
`DB_PORT` de `.env`. `DB_HOST` debe ser `127.0.0.1` porque PHP corre en el equipo.
Las credenciales de ejemplo son para desarrollo y `.env` esta excluido de Git.
MySQL solo publica el puerto en loopback. El puerto 3307 evita interferir con
el servicio local existente en 3306.

Para cambiar de puerto, editar `DB_PORT`, ejecutar `docker compose up -d --wait`
y `php artisan config:clear`. Las variables de usuario y contrasena de MySQL
se aplican al inicializar un volumen nuevo; cambiar `.env` no cambia las
credenciales de una base existente.

```sh
docker compose ps
docker compose logs --tail 50 mysql
docker compose stop
docker compose start
docker compose down
```

`stop` y `down` conservan el volumen. `docker compose down -v` elimina los datos
y no forma parte del arranque habitual. Los ajustes de MySQL se pasan como
argumentos para evitar archivos `.cnf` ignorados por permisos en Windows.

## Esquema y Semillas

Las migraciones en `database/migrations` son la fuente del esquema; los modelos
y relaciones Eloquent estan en `app/Models`.

| Tabla | Campos principales |
| --- | --- |
| `doctores` | `id`, `nombre`, `especialidad`, `email` unico, `activo` |
| `pacientes` | `id`, `nombre`, `email` unico, `telefono`, `fecha_nacimiento` |
| `citas` | `id`, `paciente_id`, `doctor_id`, `fecha`, `hora_inicio`, `hora_fin`, `motivo`, `estado` |

Las tablas incluyen marcas de creacion y actualizacion. Las claves foraneas
impiden borrar doctores o pacientes con citas. Los estados son `pendiente`,
`confirmada`, `cancelada` y `atendida`, con `pendiente` como valor inicial.
Hay indices por doctor/horario, paciente/fecha y fecha.

Los indices apoyan la consulta de disponibilidad. La API valida el orden de las
horas y los solapamientos de forma transaccional en el servidor, como exigen
`RQF-03` y `RQNF-07`.

```sh
php artisan migrate --seed
php artisan migrate:status
php artisan db:seed
```

`CitasDemoSeeder` carga datos ficticios: 3 doctores, 5 pacientes y 5 citas entre
el 18 y el 23 de septiembre de 2026, con los cuatro estados. Repetirlo sobre los
mismos registros no duplica filas ni revierte cambios de estado. Las citas
semilla se identifican por doctor, paciente, fecha y hora inicial: si se
reprograman y se vuelve a sembrar, se recrea la cita de ejemplo original.
Las tablas de usuarios, sesiones, cache y trabajos son las internas de Laravel.

## API REST

Todas las respuestas son JSON. Los recursos exitosos usan la propiedad `data`;
los errores incluyen `message` y, para validaciones, `errors` por campo.

| Metodo | Ruta | Respuesta | Proposito |
| --- | --- | --- | --- |
| `GET` | `/api/citas` | `200` | Lista citas con doctor y paciente. |
| `POST` | `/api/citas` | `201`, `400`, `409` | Crea una cita. |
| `GET` | `/api/citas/{id}` | `200`, `404` | Devuelve el detalle. |
| `PUT` | `/api/citas/{id}` | `200`, `400`, `404`, `409` | Edita o reprograma campos enviados. |
| `PATCH` | `/api/citas/{id}/estado` | `200`, `400`, `404`, `409` | Cambia el estado. |
| `GET` | `/api/doctores` | `200` | Lista doctores activos. |
| `GET` | `/api/pacientes` | `200` | Lista pacientes. |

`GET /api/citas` admite `doctor_id`, `paciente_id`, `desde` y `hasta`; las
fechas usan `YYYY-MM-DD`. La creacion requiere `paciente_id`, `doctor_id`,
`fecha`, `hora_inicio`, `hora_fin` y `motivo`. Las horas usan `HH:mm`.

```json
{
  "paciente_id": 1,
  "doctor_id": 1,
  "fecha": "2026-09-28",
  "hora_inicio": "11:00",
  "hora_fin": "11:30",
  "motivo": "Consulta general"
}
```

La actualizacion `PUT` acepta uno o mas de esos campos, excepto `estado`.
El cambio de estado usa este cuerpo:

```json
{
  "estado": "confirmada"
}
```

Los estados permitidos son `pendiente`, `confirmada`, `cancelada` y `atendida`.
No existe borrado fisico: cancelar mediante `PATCH` conserva el historial y
cubre la baja logica del CRUD. Una cita cancelada deja libre su horario. Al
crear, reprogramar o reactivar, el servidor bloquea al doctor dentro de una
transaccion y responde `409` si otra cita activa se solapa. Los horarios que
terminan exactamente cuando comienza otro son validos.

### Validaciones

| Entrada | Reglas del servidor |
| --- | --- |
| `paciente_id` | Obligatorio al crear, entero y paciente existente. |
| `doctor_id` | Obligatorio al crear, entero y doctor existente y activo. |
| `fecha` | Obligatoria al crear y formato exacto `YYYY-MM-DD`. |
| `hora_inicio` | Obligatoria al crear y formato exacto `HH:mm`. |
| `hora_fin` | Obligatoria al crear, formato `HH:mm` y posterior al inicio. |
| `motivo` | Obligatorio al crear, texto y maximo 1000 caracteres. |
| `estado` | Uno de los cuatro estados admitidos. |
| `desde`, `hasta` | Formato `YYYY-MM-DD`; `hasta` no puede preceder a `desde`. |

En `PUT`, las horas enviadas se comparan tambien con la hora existente, por lo
que una actualizacion parcial no puede dejar inicio y fin iguales o invertidos.
Un cuerpo `PUT` vacio responde `400`. Las referencias inexistentes, doctores
inactivos, JSON malformado y valores fuera de formato tambien responden `400`
sin persistir cambios.

Ejemplo de error de validacion:

```json
{
  "message": "Los datos proporcionados no son validos.",
  "errors": {
    "doctor_id": ["El valor seleccionado para doctor no es valido."]
  }
}
```

## Calendario e Interfaz

La agenda usa FullCalendar 6 con vistas de mes y semana. Consulta las citas del
rango visible y aplica filtros por doctor y paciente. Los estados se distinguen
por color: amarillo para pendiente, verde para confirmada, azul para atendida y
rojo para cancelada.

Desde la interfaz se puede crear una cita, abrir su detalle, editar sus datos y
cambiar su estado. Las citas pendientes o confirmadas se pueden arrastrar o
redimensionar; el cambio se envia a `PUT /api/citas/{id}`. Si el servidor rechaza
la operacion, FullCalendar revierte el movimiento y muestra el error. Las citas
canceladas y atendidas permanecen bloqueadas contra arrastre.

El calendario carga automaticamente `desde` y `hasta` segun el rango de la
vista. En escritorio presenta filtros y leyenda en una sola franja; en tablet
los controles se reorganizan sin cambiar el tamano del calendario ni de sus
botones. Los dialogos usan controles nativos accesibles y los botones de icono
incluyen nombre para tecnologias de asistencia.

## Verificacion

```sh
composer validate --strict
docker compose config --quiet
php artisan test
php vendor/bin/pint --test
npm run test:frontend
npm run build
```

Las pruebas usan SQLite en memoria. La validacion adicional contra MySQL en
una base aislada esta descrita en [EVIDENCIA.md](EVIDENCIA.md).

En el PHP de XAMPP de este equipo, `zip` e `intl` existen pero no estan
habilitadas globalmente. Se pueden cargar solo para el comando necesario:

```powershell
php -d extension=zip C:\ProgramData\ComposerSetup\bin\composer.phar install --no-interaction
php -d extension=intl artisan db:show --counts
```

El autoload optimizado esta desactivado para desarrollo por su lentitud en
Windows; se puede generar al desplegar con `composer dump-autoload --optimize`.
El arranque no depende de Laravel Sail ni de la extension `pcntl`.

## Documentacion

- [Requerimientos por categoria](REQUERIMIENTOS.md).
- [Backlog y pendientes](BACKLOG.md).
- [Evidencia y commits](EVIDENCIA.md).
- [Plantilla de Pull Request](.github/pull_request_template.md).

Referencias: [instalacion de Laravel 12](https://laravel.com/docs/12.x/installation),
[requisitos PHP](https://laravel.com/docs/12.x/deployment#server-requirements),
[semillas](https://laravel.com/docs/12.x/seeding) e
[imagen oficial MySQL](https://hub.docker.com/_/mysql). Para el calendario:
[vistas](https://fullcalendar.io/docs/month-view),
[eventos JSON](https://fullcalendar.io/docs/events-json-feed) y
[drag & drop](https://fullcalendar.io/docs/event-dragging-resizing).
