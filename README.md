**TicketApp — API REST con Laravel**

Proyecto backend desarrollado en PHP (Laravel) para gestionar tickets, asignaciones de dispositivos y reportes de incidentes. El proyecto está preparado para ejecutarse con Docker y cuenta con integraciones para Sentry y Webhooks de Discord.

**Requisitos**:
- PHP 8.0+
- Composer
- Docker y Docker Compose (para la instalación recomendada)

**Contenido**
- **Instalación (Docker - recomendada)**
- **Instalación (Local)**
- **Variables de entorno**

**Autenticación (Tokens / Sanctum)**

Este proyecto usa Laravel Sanctum para autenticación por tokens. Pasos importantes después de clonar e instalar dependencias:

```bash
composer install
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate --seed
```

Para obtener un token, use los endpoints `POST /api/register` o `POST /api/login`. Incluya el token en requests autenticados con el header `Authorization: Bearer <token>`.

**Logout**

Endpoint: `POST /api/logout` (autenticado)

Ejemplo curl:

```bash
curl -X POST http://localhost:8000/api/logout \
	-H "Authorization: Bearer <token>" \
	-H "Accept: application/json"
```

El endpoint revoca el token actual del usuario.

1. Clonar el repositorio:

```bash
git clone <repo-url> ticketapp
cd ticketapp
```

2. Levantar la aplicación con Docker Compose:

```bash
docker-compose up --build -d
```

3. Ejecutar migraciones y seeders dentro del contenedor de aplicación:

```bash
docker-compose exec app php artisan migrate --seed --force
```

4. Generar llave de aplicación (si no se creó automáticamente):

```bash
docker-compose exec app php artisan key:generate
```

La API quedará disponible según la configuración de `docker-compose.yml` (por defecto `http://localhost:8000`).

---

**Instalación (Local - desarrollo)**

1. Clonar y entrar al proyecto:

```bash
git clone <repo-url> ticketapp
cd ticketapp
```

2. Instalar dependencias PHP:

```bash
composer install --no-interaction --prefer-dist
```

3. Instalar dependencias Node (si usa assets):

```bash
npm install
npm run build
```

4. Copiar el archivo de entorno y configurar variables:

```bash
cp .env.example .env
php artisan key:generate
```

5. Configurar la conexión a base de datos según `DB_CONNECTION` (el proyecto está pensado para `sqlsrv` pero puede usarse `sqlite` para pruebas locales). Luego ejecutar migraciones:

```bash
php artisan migrate --seed
```

6. Iniciar servidor local:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

---

**Variables de entorno relevantes**

- `APP_ENV` — entorno (local, production)
- `APP_KEY` — llave de la aplicación
- `DB_CONNECTION` — `sqlsrv` / `mysql` / `sqlite`
- `DB_HOST` `DB_PORT` `DB_DATABASE` `DB_USERNAME` `DB_PASSWORD`
- `SENTRY_DSN` — DSN de Sentry (opcional pero requerido para monitoreo)
- `DISCORD_WEBHOOK_URL` — Webhook de Discord para notificaciones
- `SANCTUM_STATEFUL_DOMAINS` / `SESSION_DOMAIN` — configuración de Sanctum si se usa

Colocar las variables en `.env` o usar Docker Secrets según su política de despliegue.

---

**Comandos útiles**

- Instalar dependencias: `composer install`
- Generar key: `php artisan key:generate`
- Ejecutar migraciones: `php artisan migrate --seed`
- Ejecutar tests: `php artisan test` o `vendor/bin/phpunit`
- Levantar servidor local: `php artisan serve`
- Construir assets: `npm run build`

---

**Integraciones**

Sentry: configurar `SENTRY_DSN` en `.env`. El proyecto está preparado para capturar excepciones y enviarlas a Sentry.

Discord: configurar `DISCORD_WEBHOOK_URL`. El sistema envía alertas automáticas para excepciones críticas y eventos de rate limit.

---

**Pruebas y validaciones**

Ejecutar la batería de pruebas:

```bash
php artisan test
```

Para validar que las notificaciones a Discord y la integración con Sentry funcionan, establecer las variables en el entorno y provocar un error controlado (o revisar los logs de error) según la documentación interna.

**Probar notificaciones (rápido)**

Si `APP_DEBUG=true` puede enviar un test a Discord/Sentry con el endpoint de test:

```bash
curl -X POST http://localhost:8000/api/debug/notify \
	-H "Content-Type: application/json" \
	-d '{"message":"Prueba de notificación desde local"}'
```

La respuesta indicará si el envío a Discord y Sentry se intentó correctamente. Asegúrese de tener `DISCORD_WEBHOOK_URL` y `SENTRY_LARAVEL_DSN` configurados en `.env`.

**Comando Artisan para ejecutar el flujo de integración**

He incluido un comando Artisan que ejecuta localmente el flujo completo y puede usarse cuando el entorno tiene soporte de base de datos o dentro de Docker.

```bash
php artisan run:integration-flow
```

El comando intentará: registrar un usuario, crear un dispositivo, crear un ticket, asignar el dispositivo, revocar el token y enviar una notificación de prueba a Discord/Sentry. Si el entorno no tiene el driver de base de datos (por ejemplo `pdo_sqlite`) o Docker no está disponible, el comando fallará con un mensaje que describe la razón.

---

**Postman / Colección**

Agregar en el repositorio una colección Postman o exportación OpenAPI para facilitar pruebas de API. (Si desea, puedo generar la colección basada en los endpoints existentes.)

---

**Contacto**

Mantenga la rama `main` limpia y use ramas de características para cambios. Para asistencia adicional puedo preparar la colección Postman y los comandos de CI/CD.

---

**API Endpoints**

Contenido y ejemplos de uso para los endpoints públicos y autenticados. Todos los requests deben usar `Content-Type: application/json`.

- **Autenticación**
	- Método: `POST /api/register`
		- Payload:

```json
{
	"name": "Usuario Ejemplo",
	"email": "usuario@ejemplo.com",
	"password": "secret123",
	"password_confirmation": "secret123"
}
```
		- Respuesta (201):

```json
{
	"data": {
		"id": 1,
		"name": "Usuario Ejemplo",
		"email": "usuario@ejemplo.com",
		"token": "<personal-access-token>"
	}
}
```

	- Método: `POST /api/login`
		- Payload:

```json
{
	"email": "usuario@ejemplo.com",
	"password": "secret123"
}
```
		- Respuesta (200):

```json
{
	"data": {
		"token": "<personal-access-token>"
	}
}
```

	- Autorización: incluir header `Authorization: Bearer <token>` en requests autenticados.

- **Tickets** (autenticado)
	- `GET /api/tickets` — Obtener lista de tickets
		- Query params opcionales: `status`, `page`, `per_page`
		- Respuesta (200): lista paginada de tickets.

	- `GET /api/tickets/{id}` — Obtener ticket por id
		- Respuesta (200): objeto ticket.

	- `POST /api/tickets` — Crear ticket
		- Payload ejemplo:

```json
{
	"title": "Problema con laptop",
	"description": "La batería no carga",
	"device_id": 3,
	"priority": "high"
}
```
		- Respuesta (201): ticket creado.

	- `PUT /api/tickets/{id}` — Actualizar ticket
		- Payload ejemplo (parcial):

```json
{
	"status": "closed",
	"assigned_to": 5
}
```
		- Respuesta (200): ticket actualizado.

	- `DELETE /api/tickets/{id}` — Eliminar ticket
		- Respuesta (204): sin contenido.

- **Dispositivos**
	- `GET /api/devices` — Listar dispositivos (autenticado)

	- `POST /api/devices/assign` — Asignar dispositivo a usuario (autenticado)
		- Payload:

```json
{
	"device_id": 3,
	"user_id": 10,
	"assigned_at": "2026-05-10T12:00:00Z",
	"notes": "Asignación temporal"
}
```
		- Respuesta (201): objeto `device_assignment`.

---

**Encabezados y Rate Limiting**

- Todos los endpoints devuelven respuestas JSON estructuradas bajo la clave `data` o `error`.
- Incluir `Accept: application/json` y `Content-Type: application/json` en requests.
- Rate limiting: la API usa `throttle:api`. Si el límite se excede la respuesta será HTTP 429 con cabeceras `Retry-After`.

---

**Ejemplos curl**

- Registrar usuario:

```bash
curl -X POST http://localhost:8000/api/register \
	-H "Content-Type: application/json" \
	-d '{"name":"Usuario","email":"u@e.com","password":"secret","password_confirmation":"secret"}'
```

- Obtener tickets (ejemplo con token):

```bash
curl -X GET http://localhost:8000/api/tickets \
	-H "Authorization: Bearer <token>" \
	-H "Accept: application/json"
```

---

Si desea, genero automáticamente una colección Postman u OpenAPI basada en estos endpoints.

