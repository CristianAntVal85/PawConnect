# 🎯 Resumen: Sanctum + Scribe

## ¿Qué son?

### 🔐 **Sanctum** = Autenticación API
```
Cliente móvil → POST /login
             ← Token: "1|abc123xyz..."
             → GET /user (con token en header)
             ← Datos del usuario autenticado
```

### 📖 **Scribe** = Documentación interactiva
```
http://localhost:8000/docs
  ├── Listado de endpoints
  ├── Parámetros de entrada
  ├── Ejemplos de respuesta
  └── Prueba directa (como Postman)
```

---

## ✅ Estado: TODO CONFIGURADO

| Componente | Archivo | Estado |
|---|---|---|
| **Sanctum Config** | `backend/config/sanctum.php` | ✅ Publicado |
| **Sanctum BD** | `personal_access_tokens` | ✅ Migrado |
| **User Traits** | `backend/app/Models/User.php` | ✅ `HasApiTokens` añadido |
| **Scribe Config** | `backend/config/scribe.php` | ✅ Publicado + personalizado |
| **Scribe Docs** | `resources/views/scribe/` | ✅ Generadas |
| **.env** | `.env.local` | ✅ `SANCTUM_STATEFUL_DOMAINS` configurado |

---

## 🚀 Usar Sanctum en tu App

### 1️⃣ Crear endpoint de Login

```php
// backend/routes/api.php

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|min:6'
    ]);

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $token = auth()->user()->createToken('app-token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user' => auth()->user()
    ]);
});

// Endpoint protegido
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
```

### 2️⃣ Cliente (Flutter, React, etc.)

```javascript
// Login
const response = await fetch('http://localhost:8000/api/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        email: 'user@example.com',
        password: 'password123'
    })
});

const { token } = await response.json();

// Usar token
const userData = await fetch('http://localhost:8000/api/user', {
    headers: { 'Authorization': `Bearer ${token}` }
});
```

---

## 📖 Usar Scribe en tu App

### 1️⃣ Documentar endpoint (opcional)

Scribe **escanea automáticamente** tus rutas, pero puedes mejorar con comments:

```php
<?php
namespace App\Http\Controllers;

/**
 * @group Usuarios
 * Gestión de usuarios
 */
class UserController extends Controller
{
    /**
     * Obtener usuario autenticado
     * 
     * @authenticated
     * @response 200 {"id": 1, "name": "Juan", "email": "juan@test.com"}
     */
    public function show(Request $request)
    {
        return $request->user();
    }
}
```

### 2️⃣ Regenerar documentación

```bash
./artisan scribe:generate --force
```

### 3️⃣ Acceder en navegador

```
http://localhost:8000/docs
```

---

## 🔑 Archivos de Configuración

### `config/sanctum.php`

```php
return [
    // Dominios que pueden acceder sin CSRF
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', '...')),
    
    // Expiración de tokens (525600 = 1 año)
    'expiration' => 525600,
    
    // Habilitar HTTPS en producción
    'middleware' => ['verify_csrf_token', 'throttle:api']
];
```

### `config/scribe.php`

```php
return [
    'title' => 'PawConnect API Documentation',
    'description' => 'API REST para conectar dueños de mascotas...',
    
    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/*'],  // Solo documentar /api/*
            ],
        ]
    ],
];
```

---

## 🛡️ Seguridad

**Sanctum protege automáticamente:**
- ✅ CSRF tokens
- ✅ Tokens con expiración
- ✅ SameSite=Strict
- ✅ Rate limiting integrable

**En producción:**
```env
APP_DEBUG=false
SANCTUM_STATEFUL_DOMAINS=tu-dominio.com
```

---

## 📚 Comandos Útiles

```bash
# Generar documentación
./artisan scribe:generate --force

# Ver diferencias de config
./artisan scribe:config:diff

# Crear token manualmente
./artisan tinker
> User::first()->createToken('test-token')->plainTextToken

# Ver tabla de tokens
docker compose exec mysql mysql -u pawconnect -ppawconnect pawconnect -e "SELECT * FROM personal_access_tokens\G"
```

---

## ✨ Próximos Pasos

1. ✅ Sanctum + Scribe configurados
2. ⏳ Crear migraciones base (Pet, Match, Message, etc.)
3. ⏳ Crear modelos y relaciones
4. ⏳ Crear endpoints API REST
5. ⏳ Documentación automática con Scribe

Ver [SANCTUM_SCRIBE.md](SANCTUM_SCRIBE.md) para guía completa.
