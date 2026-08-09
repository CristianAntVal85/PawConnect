# FASE 6.4 - Campana de Notificaciones en Navbar

**Fecha de inicio:** 2026-08-09  
**Fecha de finalización:** 2026-08-09  
**Estado:** ✅ Completada

## Resumen

Implementación del componente visual de campana de notificaciones en la barra de navegación principal. Los usuarios pueden ver el contador de notificaciones no leídas y acceder a un dropdown con las últimas notificaciones, con opción de marcar como leído.

## Arquitectura

### 1. Endpoints API

#### GET `/api/v1/notifications`

Obtiene todas las notificaciones no leídas del usuario autenticado con paginación.

**Parámetros:**
- `per_page` (opcional): Items por página (por defecto 20)
- `page` (opcional): Número de página (por defecto 1)

**Respuesta (200 OK):**
```json
{
  "message": "Notificaciones recuperadas exitosamente.",
  "data": {
    "data": [
      {
        "id": "uuid",
        "type": "App\\Notifications\\HealthReminderNotification",
        "data": "{...json...}",
        "read_at": null,
        "created_at": "2026-08-09 14:30:00"
      }
    ],
    "total": 5,
    "per_page": 20,
    "current_page": 1
  },
  "unread_count": 5
}
```

#### GET `/api/v1/notifications/unread-count`

Obtiene solo el contador de notificaciones no leídas (ligero).

**Respuesta (200 OK):**
```json
{
  "unread_count": 3
}
```

#### PUT `/api/v1/notifications/{notification}/mark-as-read`

Marca una notificación como leída.

**Respuesta (200 OK):**
```json
{
  "message": "Notificación marcada como leída.",
  "data": { ... }
}
```

### 2. Componente Blade: `notification-bell.blade.php`

**Ubicación:** `resources/views/components/notification-bell.blade.php`

**Características:**
- Campana SVG con badge dinámico mostrando contador
- Dropdown que se abre/cierra con Alpine.js
- Lista de notificaciones con scroll
- Soporte para diferentes tipos de notificaciones
- Actualización automática cada 30 segundos
- Formateo de fechas relativas (hace X minutos, hace X horas, etc.)
- Iconografía por tipo (🏥 para salud, 🔔 genérica)

**Propiedades:**
```blade
<x-notification-bell class="mr-2" />
```

**Script Alpine.js internos:**
- `loadNotifications()`: Carga las notificaciones y actualiza el contador
- `markAsRead()`: Marca una notificación como leída
- `formatDate()`: Formatea fechas en formato relativo

### 3. Controlador API: `NotificationController`

**Ubicación:** `app/Http/Controllers/Api/NotificationController.php`

**Métodos:**

```php
// GET /api/v1/notifications
public function index(Request $request): JsonResponse

// GET /api/v1/notifications/unread-count
public function unreadCount(Request $request): JsonResponse

// PUT /api/v1/notifications/{notificationId}/mark-as-read
public function markAsRead(Request $request, string $notificationId): JsonResponse
```

**Lógica:**
- Obtiene notificaciones del usuario autenticado
- Filtra por `read_at IS NULL` para no leídas
- Ordena por más reciente primero
- Maneja errores si notificación no pertenece al usuario

### 4. Vista Web: `notifications/index.blade.php`

**Ubicación:** `resources/views/notifications/index.blade.php`

**Características:**
- Página completa de notificaciones
- Filtros: Todas / Sin leer
- Lista detallada con acciones por notificación
- Link directo a historial sanitario (para notificaciones de salud)
- Botón para marcar como leída
- Fechas formateadas legiblemente

### 5. Controlador Web: `Web/NotificationController`

**Ubicación:** `app/Http/Controllers/Web/NotificationController.php`

**Método:**
```php
public function index(): View
```

Simplemente devuelve la vista `notifications.index` (todo el trabajo está en JS/Alpine).

### 6. Rutas

**API:**
```php
Route::get('/notifications', [NotificationController::class, 'index'])->name('api.v1.notifications.index');
Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('api.v1.notifications.unread-count');
Route::put('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('api.v1.notifications.mark-as-read');
```

**Web:**
```php
Route::get('/notifications', [NotificationController::class, 'index'])->name('web.notifications.index');
```

## Integración

### En el Layout Principal

El componente se incluye en `resources/views/layouts/app.blade.php`:

```blade
<x-notification-bell class="mr-2" />
```

Ubicación: Header, entre iconos y botones de perfil.

### Meta Token

Se agregó meta tag para autenticación:

```blade
@auth
<meta name="api-token" content="{{ auth()->user()->tokens()->first()?->plainTextToken ?? '' }}">
@endauth
```

El componente lee este token para hacer llamadas autenticadas a la API.

## Lógica de Funcionamiento

```
1. Componente carga → init()
2. loadNotifications() cada 30s
3. GET /api/v1/notifications/unread-count → actualiza badge
4. Si unreadCount > 0 → GET /api/v1/notifications → rellena dropdown
5. Usuario hace click → markAsRead()
6. PUT /api/v1/notifications/{id}/mark-as-read
7. Reload automático del dropdown
```

## Tipología de Notificaciones

### HealthReminderNotification
- Icono: 🏥
- Datos: `pet_name`, `type_label`, `next_due_date`, `days_until_due`, `message`
- Acción: Enlace directo a `/pets/{id}/health`

### Notificaciones Genéricas
- Icono: 🔔
- Soporte extensible para futuros tipos

## Tests

**Archivo:** `tests/Feature/NotificationTest.php`  
**Total de tests:** 9 (todos pasando)

### Tests del Endpoint (9)

1. ✅ Un usuario puede obtener sus notificaciones no leídas
2. ✅ Usuario no autenticado no puede acceder
3. ✅ La paginación funciona correctamente
4. ✅ Obtener contador de notificaciones no leídas
5. ✅ El contador no incluye leídas
6. ✅ Un usuario puede marcar notificación como leída
7. ✅ No puede marcar notificaciones de otros usuarios
8. ✅ Error al marcar inexistente
9. ✅ Las notificaciones de salud incluyen datos correctos

## Estadísticas

- **Archivos creados:** 4
  - 1 componente Blade
  - 1 vista web
  - 1 controlador API
  - 1 controlador web
- **Archivos modificados:** 2
  - routes/api.php (3 rutas)
  - routes/web.php (1 ruta)
  - resources/views/layouts/app.blade.php (integración)
- **Tests agregados:** 9
- **Total tests proyecto:** 76 (67 previos + 9 nuevos)
- **Duración de tests:** ~2 segundos

## Configuración

No requiere configuración especial. La campana funciona automáticamente una vez integrada en el layout:

1. Las notificaciones se generan automáticamente por el Scheduler (fase 6.3)
2. Se almacenan en tabla `notifications`
3. El componente las obtiene vía API
4. Se muestran en tiempo real con actualización cada 30s

## Flujo de Usuario

1. Usuario accede al dashboard
2. Ve campana en navbar
3. Si hay notificaciones no leídas, ve badge rojo con número
4. Hace clic en campana → abre dropdown
5. Ve lista de últimas notificaciones
6. Puede hacer clic en notificación para marcar como leída
7. O hacer clic en "Ver todas las notificaciones" → va a página completa
8. En página completa: ve todas, puede filtrar, y marcar como leídas

## Ventajas del Diseño

- **Ligero:** Endpoint `/unread-count` es muy rápido (solo COUNT)
- **Real-time:** Se actualiza cada 30 segundos (configurable)
- **Responsive:** Funciona en móvil y escritorio
- **Polimórfico:** Soporta múltiples tipos de notificaciones
- **Seguro:** Valida que usuario solo vea sus propias notificaciones
- **Escalable:** Paginación para miles de notificaciones

## Extensión Futura

Para agregar nuevos tipos de notificaciones:

1. Crear nueva clase `Notification` en `app/Notifications/`
2. Hacer que implemente interfaces requeridas
3. Actualizar componente Blade: agregar `<template x-if>` para tipo nuevo
4. Tests: agregar caso de prueba para nuevo tipo

## Verificación

```bash
# Ejecutar todos los tests
php artisan test

# Resultado esperado:
# Tests: 76 passed (267 assertions)

# Testear manualmente:
# 1. Crear un health record
# 2. Esperar a que se genere notificación (cronograma)
# 3. O crear notificación directamente en DB
# 4. Ver campana en navbar
# 5. Verificar que muestra en dropdown
# 6. Click para marcar como leída
# 7. Verificar que desaparece de lista
```

## Siguiente Paso

**Fase 6.5:** CRUD de productos (solo admin) para continuar con marketplace.
