# Enosis eSports Racing Stats

Plataforma web privada para el equipo de simracing **Enosis eSports**. Permite a los pilotos del equipo consultar sus estadísticas personales de iRacing y al manager/equipo visualizar el rendimiento global, leaderboards y calendario de carreras.

---

## Contexto del Proyecto

Este proyecto es el **Trabajo Final del Ciclo Formativo de Grado Superior en Desarrollo de Aplicaciones Web (DAW)**. Simula un proyecto real de desarrollo Full-Stack aplicando metodologías, buenas prácticas y documentación técnica adecuada.

El equipo Enosis eSports compite activamente en **iRacing**, la plataforma de referencia mundial en simracing. La web consume la **iRacing Data API (OAuth2 oficial)** para obtener datos actualizados de calendarios, estadísticas de pilotos y resultados.

---

## Stack Tecnológico

| Capa | Tecnología |
|------|------------|
| Frontend | HTML5 + CSS3 + JavaScript vanilla + jQuery |
| Backend | PHP 8 + Laravel (arquitectura MVC) |
| Base de Datos | MySQL / MariaDB (gestionada con HeidiSQL) |
| API Externa | iRacing Data API (OAuth2) |
| Autenticación propia | Sistema de sesiones de Laravel + bcrypt |
| Control de versiones | Git + GitHub |
| Entorno de desarrollo | VS Code + XAMPP o Laragon |

---

## Roles de Usuario

El sistema tiene **3 roles** con permisos distintos:

### 🏎️ Piloto — sin cuenta iRacing vinculada (`role: driver, iracing_linked: false`)

- Se registra con **email y contraseña propios de la plataforma**.
- Puede ver el **calendario de carreras** de iRacing y contenido general de la web.
- **No puede ver** estadísticas personales de iRacing hasta que vincule su cuenta.

### 🏎️ Piloto — con cuenta iRacing vinculada (`role: driver, iracing_linked: true`)

- Una vez registrado, el piloto puede vincular su **cuenta de iRacing** desde su perfil mediante **OAuth2 de iRacing** (Authorization Code Grant).
- Al vincular, obtiene acceso a su **dashboard personal** con sus estadísticas de iRacing: iRating, Safety Rating (SR), victorias, podios, poles e historial de carreras.
- Sus tokens de iRacing (`access_token, refresh_token, iracing_customer_id`) se almacenan en la BD vinculados a su cuenta.
- Solo puede ver sus **propios datos.** No tiene acceso a los datos del resto del equipo.

### 👔 Manager (`role: manager`)
- Cuenta creada manualmente por el administrador (email + contraseña propios de la plataforma).
- Puede ver las **stats individuales de todos los pilotos** del equipo.
- Puede ver el **leaderboard completo** del equipo.
- Puede ver el **calendario de próximas carreras** de iRacing.
- Los datos se obtienen usando una cuenta de servidor del equipo (Password Limited Grant).

### ⚙️ Administrador (`role: admin`)
- Gestiona usuarios: crea cuentas de manager, aprueba pilotos.
- Vincula el perfil de cada piloto con su `iracing_customer_id`.
- Accede al panel de administración completo.

---

## Autenticación y OAuth2 de iRacing

### Registro e inicio de sesión del Piloto

1. El piloto se registra con email + contraseña en la web (sistema nativo de Laravel)
2. Inicia sesión → accede al calendario y contenido general
3. Desde su perfil, pulsa "Vincular cuenta de iRacing"
   → Aquí arranca el flujo OAuth2

### Vinculación con iRacing — Authorization Code Grant

```
Usuario pulsa "Vincular cuenta de iRacing"
→ Redirige a https://oauth.iracing.com/oauth2/authorize
    ?client_id=...
    &redirect_uri=https://tuWeb.com/auth/iracing/callback
    &response_type=code
    &scope=iracing.auth
    &state=valor_aleatorio_seguro

→ El piloto introduce sus credenciales en iRacing (nunca en nuestra web)

→ iRacing redirige a /auth/iracing/callback?code=ABC123&state=...

→ Laravel verifica el state, canjea el code por tokens:
  POST https://oauth.iracing.com/oauth2/token
  grant_type=authorization_code
  &client_id=...&client_secret=...&code=ABC123&redirect_uri=...

→ Se guarda en el usuario autenticado:
  iracing_customer_id, access_token, refresh_token, token_expires_at, iracing_linked=true

→ El piloto ahora tiene acceso a su dashboard con sus stats de iRacing
```

### Flujo del Servidor (Manager) — Password Limited Grant

```
Al arrancar / cuando expiran los tokens:
→ Laravel llama a POST /oauth2/token con grant_type=password_limited
→ Credenciales: client_id, client_secret (enmascarado), username y password (enmascarados)
→ Se almacenan los tokens del servidor en la BD o en caché
→ Se usan para consultar datos de todos los pilotos del equipo
```

### Enmascaramiento de contraseñas (obligatorio por la API)

iRacing requiere que tanto el `client_secret` como el `password` sean enmascarados antes de enviarlos. El algoritmo en PHP es:

```php
function maskSecret(string $secret, string $id): string {
    $normalizedId = strtolower(trim($id));
    $combined = $secret . $normalizedId;
    return base64_encode(hash('sha256', $combined, true));
}

// Uso:
$maskedPassword = maskSecret($password, $username);       // id = email del usuario iRacing
$maskedSecret   = maskSecret($clientSecret, $clientId);   // id = client_id
```

### Renovación de tokens

- El `access_token` caduca a los **600 segundos (10 minutos)**.
- El `refresh_token` caduca a los **3600 segundos (1 hora)**.
- Un **cron job de Laravel** (`app/Console/Kernel.php`) revisa y renueva los tokens automáticamente antes de que expiren usando Refresh Token Grant.

---

## Estructura de la Base de Datos

### Tabla `users`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT PK | Identificador interno |
| name | VARCHAR | Nombre del piloto o manager |
| email | VARCHAR UNIQUE | Email |
| password | VARCHAR | Contraseña bcrypt (Todos los usuarios se registran con email/password) |
| role | ENUM('driver','manager','admin') | Rol del usuario |
| iracing_customer_id | VARCHAR NULLABLE | ID del piloto en iRacing |
| access_token | TEXT NULLABLE | Token de acceso a la API de iRacing |
| refresh_token | TEXT NULLABLE | Token de refresco |
| token_expires_at | TIMESTAMP NULLABLE | Expiración del access_token |
| iracing_linked | BOOLEAN DEFAULT false | Si el piloto ha vinculado su cuenta de iRacing |
| created_at / updated_at | TIMESTAMP | Timestamps de Laravel |

### Tabla `driver_stats` (caché de datos de iRacing)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT PK | Identificador interno |
| user_id | FK → users.id | Piloto al que pertenecen |
| irating | INT | iRating actual |
| safety_rating | VARCHAR | Safety Rating (p.ej. "A 3.45") |
| wins | INT | Victorias totales |
| podiums | INT | Podios totales |
| races | INT | Carreras disputadas |
| poles | INT | Poles totales |
| favorite_category | VARCHAR | Categoría más disputada |
| last_synced_at | TIMESTAMP | Última sincronización con la API |
| created_at / updated_at | TIMESTAMP | |

### Tabla `race_results` (caché de historial de carreras)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | BIGINT PK | |
| user_id | FK → users.id | Piloto |
| subsession_id | VARCHAR | ID de la sesión en iRacing |
| series_name | VARCHAR | Nombre de la serie |
| track_name | VARCHAR | Nombre del circuito |
| finish_position | INT | Posición final |
| starting_position | INT | Posición de salida |
| incidents | INT | Puntos de incidentes |
| irating_change | INT | Variación de iRating en esa carrera |
| race_date | DATETIME | Fecha y hora de la carrera |

---

## Endpoints de la iRacing Data API utilizados

Base URL: `https://members-ng.iracing.com`

Todas las peticiones requieren el header:
```
Authorization: Bearer {access_token}
```

| Endpoint | Descripción |
|----------|-------------|
| `GET /data/member/info` | Datos básicos del piloto autenticado |
| `GET /data/member/profile?cust_id={id}` | Perfil público de un piloto por su ID |
| `GET /data/member/stats_by_category?cust_id={id}` | Stats del piloto por categoría |
| `GET /data/results/memberrecap?cust_id={id}` | Resumen de resultados del piloto |
| `GET /data/series/seasons?include_series=true` | Temporadas y series activas (calendario) |
| `GET /data/series/race_guide` | Guía de carreras próximas |

> ⚠️ La API de iRacing devuelve en algunos endpoints una URL firmada (S3) en lugar del JSON directamente. Hay que hacer una segunda petición GET a esa URL para obtener los datos reales.

---

## Estructura del Proyecto Laravel

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── LoginController.php        # Login managers/admin
│   │   │   └── IRacingOAuthController.php # OAuth flow para pilotos
│   │   ├── DriverController.php            # Perfil y stats del piloto
│   │   ├── ManagerController.php          # Vista manager: leaderboard, calendario
│   │   └── AdminController.php            # Panel de administración
│   └── Middleware/
│       ├── RoleMiddleware.php             # Comprueba el rol del usuario
│       └── RefreshIRacingToken.php        # Renueva tokens si están a punto de caducar
├── Models/
│   ├── User.php
│   ├── DriverStats.php
│   └── RaceResult.php
├── Services/
│   └── IRacingApiService.php              # Clase que encapsula todas las llamadas a la API
Console/
└── Kernel.php                             # Cron jobs: sincronización periódica con la API
resources/
└── views/
    ├── driver/
    │   └── profile.blade.php
    ├── manager/
    │   ├── dashboard.blade.php
    │   ├── leaderboard.blade.php
    │   └── calendar.blade.php
    └── admin/
        └── dashboard.blade.php
routes/
└── web.php                                # Definición de rutas y middlewares
```

---

## Variables de Entorno (.env)

```env
APP_NAME="Enosis eSports Racing Stats"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=enosis_racing
DB_USERNAME=root
DB_PASSWORD=

# iRacing OAuth2
IRACING_CLIENT_ID=
IRACING_CLIENT_SECRET=
IRACING_REDIRECT_URI=http://localhost/auth/iracing/callback

# Cuenta de servidor del equipo (Password Limited Grant para el manager)
IRACING_SERVER_USERNAME=
IRACING_SERVER_PASSWORD=
```

> ⚠️ Nunca subas el archivo `.env` al repositorio. Está incluido en `.gitignore` por defecto en Laravel.

---

## Instalación y Puesta en Marcha

```bash
# 1. Clonar el repositorio
git clone https://github.com/tu-usuario/enosis-racing-stats.git
cd enosis-racing-stats

# 2. Instalar dependencias PHP
composer install

# 3. Copiar el archivo de entorno y configurarlo
cp .env.example .env
# Editar .env con tus credenciales de BD y de iRacing

# 4. Generar clave de aplicación
php artisan key:generate

# 5. Ejecutar migraciones
php artisan migrate

# 6. (Opcional) Poblar la BD con datos de prueba
php artisan db:seed

# 7. Arrancar el servidor de desarrollo
php artisan serve
```

---

## Consideraciones para la IA que asista en este proyecto

- **Los pilotos se registran con email + contraseña** usando el sistema de autenticación nativo de Laravel. No hay login con iRacing directamente.
- **Vincular iRacing es un paso opcional posterior al registro,** desde el perfil del piloto. Solo al vincular se activa el dashboard con stats. El campo iracing_linked en la tabla users controla este acceso.
- **No uses autenticación Passport ni Sanctum.** Los pilotos y managers usan el sistema de sesiones nativo de Laravel.
- **El flujo OAuth de iRacing es solo para vincular,** no para autenticar. Se gestiona en IRacingOAuthController.php.
- **No uses autenticación Passport ni Sanctum** para los pilotos y managers usan el sistema de sesiones nativo de Laravel.
- **Toda llamada a la API de iRacing debe pasar por `IRacingApiService`**, nunca directamente desde un controlador.
- **Los datos de iRacing se cachean en BD** (`driver_stats`, `race_results`). No se consulta la API en tiempo real en cada request del usuario.
- **El enmascaramiento de contraseñas es obligatorio** antes de cualquier llamada al endpoint `/oauth2/token` de iRacing. Usar siempre la función `maskSecret()`.
- **Algunos endpoints de iRacing devuelven una URL S3 firmada**, no el JSON directamente. `IRacingApiService` debe manejar este patrón de doble petición.
- El frontend usa **jQuery para las peticiones AJAX** y manipulación del DOM. No usar fetch API ni frameworks de frontend.
- Las vistas son **Blade templates**. No usar componentes Vue, React ni Alpine.

---

## Estado del Proyecto

- [x] Documento de Requisitos (Fase 1)
- [ ] Diseño de base de datos y migraciones
- [ ] IRacingApiService
- [ ] Flujo OAuth pilotos
- [ ] Flujo Password Limited manager
- [ ] Vistas piloto
- [ ] Vistas manager
- [ ] Panel de administración
- [ ] Despliegue

---

## Equipo

Proyecto desarrollado por un miembro del equipo **Enosis eSports** como TFG del CFGS DAW.
