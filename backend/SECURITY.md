# 🔒 Configuración de Seguridad - PawConnect

## Checklist OWASP Implementado

### ✅ 1. CSRF Protection

**Estado:** Activo  
**Implementación:** Laravel Sanctum middleware `ValidateCsrfToken`

- Todas las rutas web requieren token CSRF
- Configurado en `config/sanctum.php` → `middleware.validate_csrf_token`
- Las rutas API usan autenticación por token (Sanctum)

### ✅ 2. Headers de Seguridad HTTP

**Estado:** Implementado  
**Middleware:** `App\Http\Middleware\SecurityHeaders`

Headers configurados:

| Header | Valor | Propósito |
|--------|-------|-----------|
| `X-Frame-Options` | `SAMEORIGIN` | Prevenir clickjacking |
| `X-Content-Type-Options` | `nosniff` | Prevenir MIME sniffing |
| `X-XSS-Protection` | `1; mode=block` | Habilitar filtro XSS |
| `Strict-Transport-Security` | `max-age=31536000` | Forzar HTTPS (producción) |
| `Content-Security-Policy` | Restrictivo | Prevenir XSS e inyección de código |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Proteger información de referencia |
| `Permissions-Policy` | Geolocation limitado | Controlar APIs del navegador |

**CSP Configurado:**
```
default-src 'self';
script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com;
style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;
img-src 'self' data: https: blob:;
connect-src 'self' https://api.stripe.com wss://localhost:8080;
```

### ✅ 3. Rate Limiting

**Estado:** Configurado  
**Implementación:** Laravel throttle middleware

- **Rutas API:** 60 peticiones por minuto por usuario/IP
- Configurado en `bootstrap/app.php` → `$middleware->throttleApi()`
- Retorna HTTP 429 (Too Many Requests) cuando se excede el límite

**Personalización:**
```php
// En RouteServiceProvider o bootstrap/app.php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

### ✅ 4. Expiración de Tokens Sanctum

**Estado:** Configurado  
**Valor:** 60 días (87,600 minutos)

Configurado en `config/sanctum.php`:
```php
'expiration' => env('SANCTUM_EXPIRATION', 87600), // 60 días
```

**Estrategia:**
- Los tokens API expiran automáticamente después de 60 días
- Obliga a los clientes móviles a renovar autenticación periódicamente
- Reduce el riesgo de tokens comprometidos
- Variable de entorno `SANCTUM_EXPIRATION` permite ajustar en producción

**Renovación:**
```php
// Los clientes deben implementar refresh token o re-login
POST /api/v1/auth/login
{
  "email": "user@example.com",
  "password": "password"
}
```

### ✅ 5. APP_DEBUG en Producción

**Estado:** Documentado  
**Configuración requerida:**

```env
# .env de producción
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_PRODUCTION_KEY_HERE
```

**Importante:**
- ⚠️ **NUNCA** establecer `APP_DEBUG=true` en producción
- Con debug activo, Laravel expone:
  - Stack traces completos
  - Configuración de base de datos
  - Variables de entorno sensibles
  - Rutas y estructura del código

**Verificación:**
```bash
# Verificar que APP_DEBUG está en false
php artisan config:cache
php artisan tinker
>>> config('app.debug');
=> false
```

## Variables de Entorno de Seguridad

### Desarrollo (.env)
```env
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:...

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:8000,127.0.0.1:8000
SANCTUM_EXPIRATION=87600

# Database
DB_DATABASE=pawconnect
DB_USERNAME=pawconnect
DB_PASSWORD=pawconnect

# Stripe (Test)
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

### Producción (.env.production)
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:RANDOM_SECURE_KEY_GENERATED_BY_ARTISAN

# URL
APP_URL=https://pawconnect.com

# Sanctum
SANCTUM_STATEFUL_DOMAINS=pawconnect.com,app.pawconnect.com
SANCTUM_EXPIRATION=43200  # 30 días en producción (más estricto)

# Database
DB_HOST=production-db-host
DB_DATABASE=pawconnect_prod
DB_USERNAME=pawconnect_prod_user
DB_PASSWORD=SECURE_RANDOM_PASSWORD_HERE

# Stripe (Live)
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...

# Session
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Cache
CACHE_DRIVER=redis

# Queue
QUEUE_CONNECTION=redis
```

## Tests de Seguridad

Ejecutar tests de seguridad:
```bash
php artisan test --filter=Security
```

## Recomendaciones Adicionales

### 1. HTTPS Obligatorio
```nginx
# Nginx - Redirigir HTTP a HTTPS
server {
    listen 80;
    server_name pawconnect.com;
    return 301 https://$server_name$request_uri;
}
```

### 2. Actualización de Dependencias
```bash
# Verificar vulnerabilidades conocidas
composer audit

# Actualizar paquetes de seguridad
composer update --with-dependencies
```

### 3. Logs de Seguridad
```bash
# Monitorear intentos de login fallidos
tail -f storage/logs/laravel.log | grep "Failed login"

# Monitorear rate limit excedido
tail -f storage/logs/laravel.log | grep "429"
```

### 4. Validación de Entrada
- ✅ Todos los endpoints usan Form Requests
- ✅ Validación de tipos de datos
- ✅ Sanitización de HTML (strip_tags)
- ✅ Validación de archivos subidos

### 5. Protección de Rutas Sensibles
```php
// Rutas admin protegidas con middleware
Route::middleware(['auth:sanctum', 'is_admin'])->prefix('admin')->group(...);
```

### 6. Secrets y Credenciales
- ❌ **NUNCA** commitear archivos `.env`
- ✅ Usar variables de entorno para API keys
- ✅ Rotar secrets regularmente
- ✅ Diferentes credenciales por entorno

## Auditoría de Seguridad

### Checklist Pre-Deploy

- [ ] `APP_DEBUG=false` en producción
- [ ] `APP_ENV=production`
- [ ] HTTPS configurado con certificado válido
- [ ] Firewall configurado (solo puertos 80, 443, 22)
- [ ] Base de datos no accesible públicamente
- [ ] Credenciales de Stripe en modo live
- [ ] Rate limiting testeado
- [ ] Backups automáticos configurados
- [ ] Monitoreo de logs activo
- [ ] Redis protegido con contraseña
- [ ] SSH con autenticación por clave (no password)

### Contacto de Seguridad

Para reportar vulnerabilidades de seguridad:
📧 security@pawconnect.com

**Por favor NO abrir issues públicos para problemas de seguridad.**
