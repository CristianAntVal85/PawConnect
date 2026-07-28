# 🔐 Sanctum y 📖 Scribe - Configuración Completa

## 📊 Comparación Rápida

| Característica | **Sanctum** | **Scribe** |
|---|---|---|
| **Propósito** | Autenticación API | Documentación API |
| **Tipo** | Sistema de seguridad | Herramienta de documentación |
| **BD** | Crea tabla `personal_access_tokens` | Sin BD (solo archivos) |
| **Configuración** | `config/sanctum.php` | `config/scribe.php` |
| **Salida** | Tokens para apps móviles/SPAs | Página HTML en `/docs` |
| **Cuándo usar** | Proteger endpoints | Documentar API para devs |

---

## 🔐 **SANCTUM - Autenticación con Tokens**

### ¿Para qué sirve?

Sanctum permite que:
- Apps móviles se autentiquen sin cookies
- SPAs externas accedan a tu API de forma segura
- Cada usuario tenga tokens únicos y expiración

### ¿Dónde está la configuración?

```
backend/
├── config/
│   └── sanctum.php              ← AQUÍ
├── database/migrations/
│   └── 2026_07_27_*.._create_personal_access_tokens_table.php
└── app/Models/
    └── User.php                 ← Debe tener HasApiTokens
```

### Archivos publicados

```bash
# Publicar configuración (ya hecho):
./artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Resulta en:
# ✅ config/sanctum.php
# ✅ database/migrations/*personal_access_tokens*
```

### Configuración clave en `.env.local`

```env
SANCTUM_STATEFUL_DOMAINS=localhost:8000,127.0.0.1:8000
```

Ya está en tu `.env.local` ✅

### Cómo funciona en código

```php
// 1️⃣ Modelo User debe tener trait:
use Laravel\Sanctum\HasApiTokens;

class User extends Model {
    use HasApiTokens;
}

// 2️⃣ Generar token en login:
$token = $user->createToken('app-token')->plainTextToken;
// Respuesta: "1|abc123xyz..."

// 3️⃣ Cliente lo usa en Header:
Authorization: Bearer 1|abc123xyz...

// 4️⃣ Proteger ruta:
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
```

### Estados en BD

**Tabla `personal_access_tokens`:**
```
id | tokenable_type | tokenable_id | name | token | abilities | last_used_at | expires_at | created_at | updated_at
```

---

## 📖 **SCRIBE - Documentación Automática**

### ¿Para qué sirve?

- Genera documentación HTML interactiva desde tus rutas
- Crea colección Postman automáticamente
- Documenta parámetros, responses, ejemplos
- Accesible en http://localhost:8000/docs

### ¿Dónde está la configuración?

```
backend/
├── config/
│   └── scribe.php               ← AQUÍ
├── storage/
│   └── scribe/                  ← Cache generado
├── resources/
│   └── docs/                    ← Documentación fuente
└── public/
    └── docs/                    ← Salida HTML (después de generar)
```

### Archivos publicados

```bash
# Publicar configuración:
./artisan vendor:publish --tag=scribe-config

# Resultado:
# ✅ config/scribe.php
```

### Configuración en `config/scribe.php`

```php
return [
    // Título de documentación
    'title' => config('app.name').' API Documentation',
    // ✅ Resultado: "PawConnect API Documentation"

    // Descripción
    'description' => 'API REST para conectar dueños de mascotas...',
    // ✅ Ya personalizada

    // Rutas a documentar
    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/*'],  // Solo rutas /api/*
            ],
        ],
    ],

    // Base URL de API
    'base_url' => config('app.url'),
    // ✅ Usa http://localhost:8000
];
```

### Generar documentación

```bash
# Crear documentación estática
./artisan scribe:generate

# Resultado:
# ✅ resources/docs/            (fuente)
# ✅ public/docs/               (HTML)
# ✅ storage/app-shortcut-urls.json

# Acceder en navegador:
# http://localhost:8000/docs
```

### Documentar un endpoint

Scribe **escanea automáticamente** tus rutas, pero puedes usar docstrings:

```php
<?php

namespace App\Http\Controllers;

/**
 * @group Usuarios
 * Gestión de usuarios del sistema
 */
class UserController extends Controller
{
    /**
     * Listar usuarios
     *
     * Devuelve todos los usuarios paginados.
     * Requiere autenticación.
     *
     * @authenticated
     * @param int $per_page Usuarios por página (default: 15)
     * @response 200 {
     *   "data": [
     *     {"id": 1, "name": "Juan", "email": "juan@example.com"}
     *   ],
     *   "links": {"first": "...", "last": "..."}
     * }
     */
    public function index()
    {
        return User::paginate(15);
    }
}
```

---

## ✅ Estado Actual en tu Proyecto

| Componente | Estado | Ubicación |
|---|---|---|
| **Sanctum instalado** | ✅ | `vendor/laravel/sanctum` |
| **Sanctum publicado** | ✅ | `config/sanctum.php` |
| **Tabla de tokens creada** | ✅ | `personal_access_tokens` (en BD) |
| **SANCTUM_STATEFUL_DOMAINS** | ✅ | `.env.local` |
| **Scribe instalado** | ✅ | `vendor/knuckleswtf/scribe` |
| **Scribe publicado** | ✅ | `config/scribe.php` |
| **Documentación generada** | ⏳ | Ejecutar: `./artisan scribe:generate` |

---

## 🚀 Próximos Pasos

### 1. Preparar User model para Sanctum

```bash
# Editar backend/app/Models/User.php
```

```php
<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens;  // ← AÑADIR ESTE TRAIT
    
    protected $fillable = ['name', 'email', 'password'];
}
```

### 2. Crear endpoint de login

```bash
./artisan make:controller AuthController
```

```php
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    if (Auth::attempt($request->only('email', 'password'))) {
        $token = auth()->user()->createToken('app-token')->plainTextToken;
        
        return response()->json([
            'token' => $token,
            'user' => auth()->user()
        ]);
    }

    return response()->json(['error' => 'Credenciales inválidas'], 401);
}
```

### 3. Generar documentación

```bash
./artisan scribe:generate

# Accede en: http://localhost:8000/docs
```

---

## 📋 Comandos Útiles

```bash
# Ver tabla de tokens
docker compose exec mysql mysql -u pawconnect -ppawconnect pawconnect -e "SELECT * FROM personal_access_tokens\G"

# Regenerar documentación
./artisan scribe:generate --force

# Ver diferencias en config de Scribe
./artisan scribe:config:diff

# Crear token manualmente (tinker)
./artisan tinker
> $user = User::first()
> $token = $user->createToken('test')->plainTextToken
```

---

## 🔒 Seguridad

**Sanctum protege automáticamente:**
- ✅ CSRF para cookies
- ✅ SameSite=Strict por defecto
- ✅ Tokens con expiración configurable
- ✅ Rate limiting integrable

**En producción cambiar:**
```env
APP_ENV=production
APP_DEBUG=false
SANCTUM_STATEFUL_DOMAINS=tu-dominio.com
```

---

## 📚 Recursos

- [Sanctum Docs](https://laravel.com/docs/11.x/sanctum)
- [Scribe Docs](https://scribe.knuckles.wtf/laravel)
