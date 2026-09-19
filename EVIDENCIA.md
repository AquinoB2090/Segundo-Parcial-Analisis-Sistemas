# Evidencia del Entorno Laravel y Docker

Fecha: 2026-09-19. Rama: `feature/docker-mysql-schema`.

## Alcance y Versiones

PHP y Laravel corren en Windows; Docker ejecuta solamente MySQL. Se verificaron
arranque, conexion, migraciones, semillas, restricciones y persistencia.
La API REST y FullCalendar quedan pendientes.

| Componente | Version observada |
| --- | --- |
| PHP CLI | 8.2.12 |
| Laravel Framework | 12.69.2 |
| Composer | 2.8.12 |
| Node.js | 22.15.0 |
| Docker Engine | 28.4.0 |
| Docker Compose | 2.39.4-desktop.1 |
| MySQL | 8.4.11 |

Imagen: `mysql:8.4@sha256:85b9bf2e29cf836ecb8c2a15a935d4ba0c606631dff1dd79531a11983c638f2a`.

## Arranque y Datos

```sh
docker compose config --quiet
docker compose up -d --wait --wait-timeout 180
docker compose ps
php artisan --version
php artisan migrate --seed --no-interaction
php artisan migrate:status --no-interaction
php -d extension=intl artisan db:show --counts --no-interaction
```

Resultados: Compose valido, `citas-medicas-mysql-1` saludable y puerto
`127.0.0.1:3307->3306/tcp`. Laravel se conecto a `citas_medicas` como `citas_app`.
Se ejecutaron seis migraciones, tres internas y tres del dominio. Hay 3 doctores,
5 pacientes y 5 citas. MySQL reporto `utf8mb4_unicode_ci` y zona `+00:00`.

Incidencias resueltas: MySQL ignoro inicialmente un `.cnf` montado por permisos
de Windows; se sustituyo por opciones de arranque. Composer tardo demasiado
optimizando el autoload; con el autoload normal completo la instalacion.
`db:show` requirio cargar `intl` mediante `-d extension=intl`.

## Pruebas

```sh
php artisan test
```

Resultado en SQLite en memoria: **10 pruebas aprobadas, 41 aserciones**.
Incluye ocho pruebas de esquema/semillas y dos de la base de Laravel.

La misma suite se ejecuto en MySQL con una base aislada. Para reproducir en
PowerShell con las credenciales locales del ejemplo:

```powershell
docker compose exec -T mysql mysql --user=root --password=citas_local_root_password --execute="CREATE DATABASE IF NOT EXISTS citas_medicas_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; GRANT ALL PRIVILEGES ON citas_medicas_test.* TO 'citas_app'@'%';"
$env:DB_CONNECTION = 'mysql'
$env:DB_DATABASE = 'citas_medicas_test'
try {
    php artisan test
} finally {
    Remove-Item Env:DB_CONNECTION, Env:DB_DATABASE
}
```

Resultado en MySQL: **10 pruebas aprobadas, 41 aserciones**. `RefreshDatabase`
recrea tablas: usar la base de pruebas, nunca `citas_medicas`. Se verificaron
claves foraneas, restriccion de borrado de doctores/pacientes con historial,
estados permitidos, estado inicial y semillas sin duplicados ni reversion de
una cancelacion.

```sh
composer validate --strict
php vendor/bin/pint --dirty
npm run build
```

Resultados: manifiesto valido, formato aprobado y compilacion Vite exitosa.
`npm install` reporto cero vulnerabilidades al instalar.

## Persistencia

Se obtuvieron conteos y huella de las citas con:

```sql
SELECT
    (SELECT COUNT(*) FROM doctores) AS doctores,
    (SELECT COUNT(*) FROM pacientes) AS pacientes,
    (SELECT COUNT(*) FROM citas) AS citas;

SELECT SHA2(GROUP_CONCAT(CONCAT_WS('|',
    id, doctor_id, paciente_id, fecha, hora_inicio, hora_fin,
    motivo, estado, created_at, updated_at
) ORDER BY id), 256) AS huella_citas FROM citas;
```

Luego se elimino y recreo el contenedor conservando el volumen:

```sh
docker compose down
docker compose up -d --wait --wait-timeout 180
docker volume inspect citas-medicas_mysql_data
```

Sin ejecutar semillas ni migraciones de nuevo, se conservaron los conteos
`3 / 5 / 5` y esta huella identica antes y despues:

```text
a46ca866a4754fbf1232bdd3140b55fa10e762effbf0f7bd4b02417accfb09cd
```

El volumen `citas-medicas_mysql_data` utiliza el driver `local`.

## HTTP Local

Servidor iniciado con `php artisan serve --host=127.0.0.1 --port=8000 --no-reload`.

```powershell
Invoke-WebRequest -UseBasicParsing http://127.0.0.1:8000/ | Select-Object StatusCode
Invoke-WebRequest -UseBasicParsing http://127.0.0.1:8000/up | Select-Object StatusCode
```

Ambas rutas respondieron **200**. `/up` comprueba el arranque; la conexion a
MySQL se verifico aparte con migraciones y `db:show`. No se presentan estas
respuestas como evidencia de los endpoints de citas pendientes.

## Commits por Paso

| Commit | Paso | ID |
| --- | --- | --- |
| `d3f2df5` | Inicializar Laravel 12 local | RQNF-04 |
| `ece2260` | Docker Compose y conexion | RQNF-01, RQNF-02 |
| `0bb5348` | Reproducibilidad en Windows | RQNF-02 |
| `5c47e4c` | Esquema, modelos, semillas y pruebas | RQF-01, RQF-05, RQF-08, RQNF-01 |
| `docs(RQNF-05,RQNF-08)` | Esta documentacion y plantilla de PR | RQNF-05, RQNF-08 |

Consultar el hash de documentacion con
`git log --oneline --grep="docs(RQNF-05,RQNF-08)"`. Los ID funcionales en los
commits indican soporte de datos, no implementacion de la API. Esta entrega
no se ha publicado ni integrado a main; registrar el PR y merge cuando ocurran.
