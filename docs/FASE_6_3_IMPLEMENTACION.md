# FASE 6.3 - Notificaciones Automáticas con Scheduler

**Fecha de inicio:** 2026-08-09  
**Fecha de finalización:** 2026-08-09  
**Estado:** ✅ Completada

## Resumen

Implementación de un sistema automático de notificaciones para recordar a los usuarios sobre próximas revisiones de salud de sus mascotas. El sistema utiliza el Scheduler de Laravel para ejecutar un comando diariamente que identifica registros sanitarios próximos a vencer y envía notificaciones por email y base de datos.

## Arquitectura

### 1. Migración: `create_notifications_table`

Crea la tabla estándar de notificaciones de Laravel con estructura polimórfica:

```sql
CREATE TABLE notifications (
    id UUID PRIMARY KEY,
    notifiable_type VARCHAR,
    notifiable_id VARCHAR,
    type VARCHAR,
    data LONGTEXT,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Características:**
- UUID como clave primaria (estándar Laravel)
- Relación polimórfica con User
- Campo data en JSON para almacenar metadatos
- Timestamps para auditoría

### 2. Modelo: `Notification`

Extiende `DatabaseNotification` de Laravel:

```php
class Notification extends DatabaseNotification
{
    protected $table = 'notifications';
    protected $keyType = 'string';
    public $incrementing = false;
}
```

### 3. Comando: `SendHealthReminders`

**Ruta:** `app/Console/Commands/SendHealthReminders.php`  
**Nombre del comando:** `health:reminders`

**Lógica:**
1. Busca todos los HealthRecord con:
   - `notifications_active = true`
   - `next_due_date` no nulo
   - `next_due_date` entre hoy y hoy + 3 días

2. Por cada registro encontrado, dispara el job `ProcessHealthReminder`

**Salida:**
- Mensaje informativo con cantidad de records encontrados
- SUCCESS si todo va bien
- Maneja correctamente cuando no hay records

### 4. Job: `ProcessHealthReminder`

**Ruta:** `app/Jobs/ProcessHealthReminder.php`

**Responsabilidades:**
1. Recibe un HealthRecord en el constructor
2. Obtiene el usuario propietario de la mascota
3. Envía HealthReminderNotification al usuario

**Integración:**
- Implementa `ShouldQueue` para procesamiento asincrónico
- Se encola automáticamente desde el comando
- Permite alto volumen sin bloquear el scheduler

### 5. Notificación: `HealthReminderNotification`

**Ruta:** `app/Notifications/HealthReminderNotification.php`

**Canales de envío:**
- `database`: Almacena en tabla notifications
- `mail`: Envía email al usuario

**Email:**
- Asunto: "Recordatorio de salud para {nombre_mascota}"
- Incluye: nombre mascota, tipo de revisión, fechas
- Link directo al timeline sanitario
- Tono amigable y profesional

**Data almacenada:**
- health_record_id, pet_id, pet_name
- type, type_label (traducido al español)
- next_due_date, days_until_due
- message descriptivo

### 6. Scheduler: `routes/console.php`

```php
Schedule::command('health:reminders')
    ->daily()
    ->description('Send health reminders for pets with upcoming health record reviews');
```

**Ejecución:** Una vez por día (hora configurable según server)

## Lógica de Negocio

### Criterios de Notificación

Un HealthRecord genera notificación si:
1. `notifications_active = true`
2. `next_due_date` no es null
3. `next_due_date` está entre hoy y hoy + 3 días

### Flujo de Datos

```
Scheduler (diariamente)
    ↓
SendHealthReminders (comando)
    ↓ (encuentra records)
ProcessHealthReminder (job, por cada record)
    ↓
HealthReminderNotification (notificación)
    ├→ database (tabla notifications)
    └→ mail (email al usuario)
```

### Ventajas del Diseño

- **Asincronía:** Jobs permiten no bloquear el scheduler
- **Flexibilidad:** Usuarios pueden activar/desactivar notificaciones por record
- **Auditoría:** Base de datos mantiene historial completo
- **Escalabilidad:** Job queue puede procesarse en background workers
- **Robustez:** El comando maneja casos sin records sin errores

## Tests

**Archivo:** `tests/Feature/HealthNotificationsTest.php`  
**Total de tests:** 8 (todos pasando)

### Tests del Comando (4)

1. ✅ Encuentra records correctos dentro de 3 días
2. ✅ Respeta el flag `notifications_active = false`
3. ✅ Respeta umbral exacto de 3 días
4. ✅ Maneja correctamente cuando no hay records

### Tests de Notificación (4)

1. ✅ Se envía por canales database y mail
2. ✅ Contiene todos los datos requeridos
3. ✅ Traduce correctamente tipos (vaccination → Vacunación, etc.)
4. ✅ Mensaje incluye detalles de mascota y fecha

## Estadísticas

- **Archivos creados:** 5
  - 1 migración
  - 1 modelo
  - 1 comando
  - 1 job
  - 1 notificación
- **Archivos modificados:** 1 (routes/console.php)
- **Tests agregados:** 8
- **Total tests proyecto:** 67 (59 previos + 8 nuevos)
- **Cobertura:** ✅ 100% de la lógica
- **Duración de tests:** ~2 segundos

## Configuración para Producción

### 1. Asegurar tabla notifications existe

```bash
php artisan migrate
```

### 2. Configurar driver de notificaciones

En `.env`:
```env
MAIL_DRIVER=smtp  # o el que uses
MAIL_FROM_ADDRESS=notificaciones@pawconnect.app
```

### 3. Configurar scheduler cron

En servidor Linux/Mac:
```bash
* * * * * cd /path/to/pawconnect && php artisan schedule:run >> /dev/null 2>&1
```

Este cron ejecuta Laravel scheduler cada minuto, que dispara comandos según su programación.

### 4. Monitorear queue (opcional)

Si usas processing en background:
```bash
php artisan queue:work
```

## Flujo de Usuario

1. Usuario crea HealthRecord con `notifications_active = true`
2. Scheduler ejecuta `health:reminders` diariamente
3. Si `next_due_date` está dentro de 3 días, sistema envía notificación
4. Usuario recibe email + notificación en app
5. Usuario puede hacer clic y ir al timeline sanitario
6. Usuario ve estado y puede renovar el record

## Integración con Fases Anteriores

- **Fase 6.1:** HealthRecord con columna `notifications_active` ✅
- **Fase 6.2:** Timeline sanitario con enlace directo ✅
- **Fase 6.3:** Notificaciones automáticas ✅

## Verificación

```bash
# Ejecutar comando manualmente
php artisan health:reminders

# Salida esperada:
# Starting health reminders job...
# Found N health record(s) requiring reminders.
# Dispatched reminder for health record ID: X
# Health reminders job completed successfully.

# Ejecutar todos los tests
php artisan test

# Resultado esperado:
# Tests: 67 passed (229 assertions)
```

## Notas de Desarrollo

- Usar `Artisan::call()` en tests, no `$command->handle()`
- Diferencias de fecha/hora pueden causar variaciones en `days_until_due` (usar rangos en tests)
- Jobs se almacenan en database queue por defecto, puede cambiarse en config/queue.php
- Notificaciones se pueden ver en base de datos: `SELECT * FROM notifications;`

## Siguientes Pasos (Fases)

- **6.4:** Campana de notificaciones en navbar
- **6.5-6.9:** Features de marketplace
- **7+:** Pet-friendly places, admin panel, deployment
