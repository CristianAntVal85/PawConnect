# ❓ FAQ: Preguntas Frecuentes sobre Autenticación

**Respuestas rápidas. Para detalles técnicos, consulta [ARQUITECTURA_AUTENTICACION.md](./ARQUITECTURA_AUTENTICACION.md)**

---

## 🤔 Conceptos Generales

### P1: ¿Por qué existen controllers en `Api/` y en `Web/`?

**R:** Porque son clientes diferentes con necesidades diferentes.

| Aspecto | Web | API |
|---|---|---|
| Cliente | Navegador (Blade) | Móvil, SPA |
| Respuesta | Redirige a página | Devuelve JSON |
| Autenticación | Sesión HTTP | Token Sanctum |

→ Ver detalles en [ARQUITECTURA § 2. Tipos de Clientes](./ARQUITECTURA_AUTENTICACION.md#2-tipos-de-clientes)

---

### P2: ¿Las rutas deben tener `name()`?

**R:** **SÍ.** Para generar URLs dinámicamente:

```php
// ✅ BIEN
<form action="{{ route('login.store') }}">

// ❌ MAL  
<form action="/login">
```

Si cambias `/login` → `/signin`, solo cambias en `routes/`, todo lo demás funciona.

→ Ver detalles en [ARQUITECTURA § 5. Decisiones Arquitectónicas](./ARQUITECTURA_AUTENTICACION.md#5-decisiones-arquitectónicas)

---

### P3: ¿Por qué los formularios estaban con `action="#"`?

**R:** Placeholder temporal durante desarrollo. Se completó `login.blade.php` pero se olvidó `register.blade.php` y `forgot-password.blade.php`.

**Ahora están arreglados:**
```blade
<form action="{{ route('register.store') }}">  ✅
<form action="{{ route('password.email') }}">  ✅
<form action="{{ route('login.store') }}">     ✅
```

---

### P4: ¿LoginController duplica código?

**R:** **No, ya no.** Antes duplicaba validaciones. Ahora usa `AuthService` compartido.

```php
// ANTES (duplicado)
Web/LoginController::store()      ← validaba credenciales aquí
Api/AuthController::login()       ← y validaba aquí también ❌

// AHORA (compartido)
Web/LoginController::store()   ─┐
Web/RegisterController::store()─┼→ AuthService::validateCredentials()
Api/AuthController::login()    ─┘
```

→ Ver arquitectura completa en [ARQUITECTURA § 3. AuthService](./ARQUITECTURA_AUTENTICACION.md#3-authservice-lógica-compartida)

---

## 🛠 Implementación Técnica

### P5: ¿Qué cambió en las rutas?

**API (`routes/api.php`):**
```php
// Ahora con names
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('/register', [...])       ->name('register');    // api.v1.auth.register
    Route::post('/login', [...])          ->name('login');       // api.v1.auth.login
    Route::post('/forgot-password', [...])  ->name('forgot-password');
    Route::post('/reset-password', [...])   ->name('reset-password');
});
```

**Web (`routes/web.php`):**
```php
Route::get('/login', [...])      ->name('login');
Route::post('/login', [...])     ->name('login.store');

Route::get('/register', [...])      ->name('register');
Route::post('/register', [...])     ->name('register.store');

Route::post('/forgot-password', [...]) ->name('password.email');
```

---

### P6: ¿Qué hace AuthService?

**R:** Centraliza lógica compartida entre Web y API:

```php
class AuthService {
    // Validar credenciales (Web + API)
    validateCredentials(email, password) → User
    
    // Crear usuario (Web + API)
    createUser(data) → User
    
    // Solo Web
    authenticateWeb(user, remember)
    
    // Solo API
    revokeSameName(user)
}
```

---

### P7: ¿Qué controllers están involucrados?

**Web (Sesiones HTTP):**
- `Web/LoginController@store` - POST /login
- `Web/RegisterController@store` - POST /register  
- `Web/ForgotPasswordController@store` - POST /forgot-password

**API (Tokens Sanctum):**
- `Api/AuthController@login` - POST /api/v1/auth/login
- `Api/AuthController@register` - POST /api/v1/auth/register
- `Api/AuthController@forgotPassword` - POST /api/v1/auth/forgot-password
- `Api/AuthController@resetPassword` - POST /api/v1/auth/reset-password

---

## 🧪 Testing

### P8: ¿Cómo testeo Web vs API?

**Web:**
```php
$this->post(route('login.store'), $credentials);
$this->assertAuthenticatedAs($user);  // Verifica sesión
```

**API:**
```php
$this->postJson(route('api.v1.auth.login'), $credentials);
$response->assertJsonStructure(['data' => ['token']]);
```

→ Ver ejemplos completos en [ARQUITECTURA § 7. Testing](./ARQUITECTURA_AUTENTICACION.md#7-testing)

---

## 🔒 Seguridad

### P9: ¿Qué garantiza la seguridad?

**Web:**
- ✅ Cookie HttpOnly (JS no accede)
- ✅ CSRF token automático
- ✅ Session regeneration

**API:**
- ✅ Bearer token (no en cookie)
- ✅ Validación por Sanctum
- ✅ Hash de contraseñas

→ Ver detalles en [ARQUITECTURA § 8. Seguridad](./ARQUITECTURA_AUTENTICACION.md#8-seguridad)

---

## 📚 Documentación Relacionada

| Documento | Para Qué |
|---|---|
| **ARQUITECTURA_AUTENTICACION.md** | Entender el sistema completo (8 secciones) |
| **CAMBIOS_AUTENTICACION_RESUMO.md** | Empezar rápido (ejemplos de uso) |
| **FAQ.md** | Preguntas frecuentes (este documento) |

---

## 💡 Caso de Uso: De Frontend a Backend

**Escenario:** Usuario registra cuenta desde el navegador

```
1. Usuario llena form en register.blade.php
   ↓
2. POST /register → Web/RegisterController@store
   ↓
3. RegisterController valida y delega en AuthService::createUser()
   ↓
4. AuthService crea usuario en BD
   ↓
5. RegisterController autentica: Auth::login($user)
   ↓
6. Redirect /dashboard con sesión HTTP
```

**Escenario 2:** App móvil registra cuenta

```
1. App envía JSON: {name, email, password}
   ↓
2. POST /api/v1/auth/register → Api/AuthController@register
   ↓
3. AuthController delega en AuthService::createUser()
   ↓
4. AuthService crea usuario en BD
   ↓
5. AuthController genera Sanctum token
   ↓
6. Devuelve JSON: {token, user}
   ↓
7. App guarda token en localStorage
```

Ambos usan `AuthService` → **Sin duplicación** ✅

---

## ✨ TL;DR (Resumido)

- Web y API coexisten porque el navegador y las apps móviles son distintos
- Routes deben tener `name()` para generar URLs dinámicamente  
- `action="#"` era un placeholder olvidado
- `AuthService` elimina la duplicación de código
- Consulta ARQUITECTURA_AUTENTICACION.md para entender el sistema completo
