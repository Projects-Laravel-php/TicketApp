# TicketApp

API REST construida con Laravel para la gestión de tickets e inventario/ asignación de dispositivos.

## Descripción

TicketApp es un backend Laravel que permite:
- registrar y autenticar usuarios con Laravel Sanctum
- crear, listar, ver, actualizar y eliminar tickets
- asignar dispositivos a tickets o usuarios
- proteger todos los endpoints autenticados
- manejar errores con respuestas JSON consistentes
- enviar alertas a Discord y reportes a Sentry

## Requisitos

- PHP 8.0+
- Composer
- Docker y Docker Compose (recomendado)
- Node.js / npm (solo si se usan assets front-end)

## Estructura principal

- `app/Http/Controllers/Api` - controladores de API
- `app/Services` - lógica de negocio
- `app/Models` - modelos Eloquent
- `routes/api.php` - rutas de API
- `app/Exceptions/Handler.php` - manejo global de excepciones

## Instalación con Docker (recomendada)

1. Clonar el repositorio:

```bash
git clone <repo-url> ticketapp
cd ticketapp
```

2. Levantar contenedores:

```bash
docker compose up -d --build
```

3. Generar la llave de la app:

```bash
docker compose exec app php artisan key:generate
```

4. Ejecutar migraciones y seeders:

```bash
docker compose exec app php artisan migrate --seed --force
```

5. Asegurarse de tener variables de entorno configuradas en `.env`.

## Instalación local

1. Clonar y entrar al proyecto:

```bash
git clone <repo-url> ticketapp
cd ticketapp
```

2. Instalar dependencias PHP:

```bash
composer install
```

3. Copiar `.env` y generar llave:

```bash
cp .env.example .env
php artisan key:generate
```

4. Configurar la base de datos en `.env`.

5. Ejecutar migraciones y seeders:

```bash
php artisan migrate --seed
```

6. Iniciar servidor local:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

## Variables de entorno importantes

- `APP_NAME`
- `APP_ENV`
- `APP_KEY`
- `APP_URL`
- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `DISCORD_WEBHOOK_URL`
- `SENTRY_LARAVEL_DSN`
- `SANCTUM_STATEFUL_DOMAINS`
- `SESSION_DOMAIN`

## Autenticación

TicketApp utiliza Laravel Sanctum para autenticación con tokens.

### Endpoints públicos

- `POST /api/register` - registro de usuario
- `POST /api/login` - inicio de sesión

### Endpoints protegidos

- `POST /api/logout`
- `GET /api/tickets`
- `GET /api/tickets/{id}`
- `POST /api/tickets`
- `PUT /api/tickets/{id}`
- `DELETE /api/tickets/{id}`
- `GET /api/devices`
- `POST /api/devices/assign`

### Uso del token

Enviar en cada request protegido:

```http
Authorization: Bearer <token>
Accept: application/json
```

## API principal

### Registrar usuario

`POST /api/register`

Payload:

```json
{
  "name": "Usuario Ejemplo",
  "email": "usuario@ejemplo.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

### Login

`POST /api/login`

Payload:

```json
{
  "email": "usuario@ejemplo.com",
  "password": "secret123"
}
```

Respuesta:

```json
{
  "success": true,
  "data": {
    "token": "<personal-access-token>"
  }
}
```

### Logout

`POST /api/logout`

Debe enviarse con autorización. Revoca el token actual.

### Tickets

Todos los tickets están asociados al usuario autenticado.

#### Listar tickets

`GET /api/tickets`

- Devuelve solo los tickets del usuario actual.
- No muestra tickets de otros usuarios.

#### Ver ticket

`GET /api/tickets/{id}`

- Devuelve el ticket solo si pertenece al usuario autenticado.
- Si el ticket existe pero no es del usuario, retorna error `403`.
- Si no existe, retorna error `404`.

#### Crear ticket

`POST /api/tickets`

Payload:

```json
{
  "title": "Falla de impresora",
  "description": "La impresora no responde",
  "device_id": 2,
  "priority": "high"
}
```

- El campo `user_id` se asigna automáticamente desde el token.
- No es posible crear tickets en nombre de otro usuario.

#### Actualizar ticket

`PUT /api/tickets/{id}`

Campos válidos:
- `title`
- `description`
- `status`
- `assigned_to`

- Solo se puede actualizar si el ticket pertenece al usuario autenticado.
- Si no está autorizado, retorna `403`.

#### Eliminar ticket

`DELETE /api/tickets/{id}`

- Solo el dueño del ticket puede eliminarlo.
- Si intenta borrar un ticket de otro usuario, retorna `403`.

### Dispositivos

#### Listar dispositivos

`GET /api/devices`

#### Asignar dispositivo

`POST /api/devices/assign`

Payload ejemplo:

```json
{
  "device_id": 1,
  "assigned_to": 2,
  "ticket_id": 5
}
```

- Crea una asignación de dispositivo según la lógica del servicio.
- El endpoint requiere autenticación.

## Manejo de errores

Las respuestas de error siempre devuelven JSON con estructura clara:

```json
{
  "success": false,
  "error": {
    "message": "Mensaje descriptivo",
    "errors": { /* opcional, validación */ }
  }
}
```

### Códigos principales

- `401 Unauthorized` - token faltante o inválido
- `403 Forbidden` - usuario autenticado no autorizado para el recurso
- `404 Not Found` - recurso no existe
- `422 Unprocessable Entity` - validación de datos falló
- `429 Too Many Requests` - límite de tasa excedido
- `500 Internal Server Error` - error del servidor

## Seguridad y permisos

- Solo el usuario autenticado puede acceder y administrar sus propios tickets.
- Intentos de acceso a tickets de otros usuarios reciben `403`.
- Los registros de usuario y login se mantienen públicos para obtener token.

## Notificaciones e integraciones

### Discord

- El proyecto puede enviar alertas a Discord vía `DISCORD_WEBHOOK_URL`.
- Se notifican errores críticos y eventos de rate limit.

### Sentry

- Se envían excepciones a Sentry cuando `SENTRY_LARAVEL_DSN` está configurado.
- Ideal para monitoreo de fallas en producción.

## Comandos útiles

```bash
composer install
php artisan key:generate
php artisan migrate --seed
php artisan test
php artisan serve
```

## Debug de notificaciones

Cuando `APP_DEBUG=true`, existe un endpoint de prueba:

`POST /api/debug/notify`

Ejemplo:

```bash
curl -X POST http://localhost:8000/api/debug/notify \
  -H "Content-Type: application/json" \
  -d '{"message":"Prueba de alerta"}'
```

## Notas finales

- La API está diseñada para respuesta JSON consistente.
- La lógica de negocio vive en `app/Services`.
- Los endpoints protegidos usan `auth:sanctum`.
- Los tickets pertenecen al usuario autenticado y no se puede sobrescribir `user_id` al crear tickets.
