# ✅ Fase 7.5 — Integración Sanitaria con Veterinarios Recomendados

**Estado:** ✅ COMPLETADA  
**Fecha:** Enero 2025  
**Tests:** 11 nuevos tests (224 total, 973 assertions)

---

## 📋 Descripción de la Fase

Integrar el módulo sanitario de mascotas con el módulo de lugares pet-friendly. Cuando un usuario tiene registros sanitarios próximos a vencer (amarillo) o vencidos (rojo), se le muestran veterinarios cercanos recomendados con información de contacto y servicios necesarios.

---

## ✅ Tareas Completadas

### 1. ✅ HealthVeterinarianService

**Archivo:** `app/Services/HealthVeterinarianService.php`

**Responsabilidad:** Orquestar la búsqueda y recomendación de veterinarios basada en registros sanitarios críticos.

**Métodos públicos:**

#### `getRecommendedVeterinarians(Pet $pet, User $user, int $radiusKm = 10): Collection`
- Entrada: Mascota, usuario (para obtener ubicación), radio de búsqueda (km)
- Validaciones:
  - Retorna `collect()` si usuario no tiene lat/lng
  - Retorna `collect()` si no hay registros críticos (yellow/red)
- Proceso:
  1. Obtiene registros críticos via `getCriticalHealthRecords()`
  2. Busca veterinarios cercanos via `findNearbyVeterinarians()`
  3. Enriquece con tipos de servicios necesarios
  4. Calcula nivel de urgencia
- Retorno: Collection de objetos con estructura:
  ```php
  {
    place_id: int,
    place: PetFriendlyPlace model,
    distance: float,
    needed_services: array ['vaccination', 'deworming', ...],
    urgency: 'high'|'medium'
  }
  ```

#### `getCriticalHealthRecords(Pet $pet): Collection`
- Filtra registros sanitarios del pet con next_due_date no nulo
- Usa `HealthStatusService::getStatus($record)` para determinar estado
- Retorna solo registros con estado 'yellow' (0-7 días) o 'red' (vencido)
- Lazy-loaded: `whereNotNull('next_due_date')->get()->filter()`

#### `getUrgencyLevel(Pet $pet): string`
- Entrada: Mascota
- Lógica:
  - Si existe algún registro rojo → `'high'` (🚨)
  - Si solo amarillos → `'medium'` (⏰)
- Usado para priorizar visualización y urgencia del contacto

#### `getServicesDescription(array $types): string`
- Traduce tipos de servicios a descripciones amigables
- Mapeo:
  - `vaccination` → `Vacunación`
  - `deworming` → `Desparasitación`
  - `checkup` → `Revisión general`
  - `surgery` → `Cirugía`
  - `medication` → `Medicación`
  - `dental` → `Limpieza dental`
  - `grooming` → `Aseo`
- Retorno: String separado por comas (ej: "Vacunación, Desparasitación")

#### `getUrgencyEmoji(string $urgency): string`
- Mapeo de urgencia a emoji visual
- `'high'` → `🚨`
- `'medium'` → `⏰`
- default → `ℹ️`

**Métodos privados:**

#### `findNearbyVeterinarians(float $userLat, float $userLng, int $radiusKm, Collection $criticalRecords): Collection`
- **Geolocalización con Haversine:**
  ```sql
  (6371 * acos(cos(radians(?)) * cos(radians(lat)) *
  cos(radians(lng) - radians(?)) +
  sin(radians(?)) * sin(radians(lat)))) as distance
  ```
  - 6371 = radio de la Tierra en km
  - Parámetros: userLat, userLng, userLat (trigonometría)
  - Retorna distancia en km

- **Filtros de búsqueda:**
  - `category = 'vet'` (solo veterinarios)
  - `status = 'approved'` (lugares aprobados por admin)
  - `distance <= radiusKm` (dentro del radio)

- **Limitaciones:**
  - Máximo 5 veterinarios retornados
  - Ordenados por distancia ascendente (más cercanos primero)

- **Retorno:** Collection de PetFriendlyPlace enriquecida con atributo `distance`

#### `getUrgencyLevelFromRecords(Collection $criticalRecords): string`
- Versión interna usada durante mapeo de resultados
- Misma lógica que `getUrgencyLevel()` pero recibe Collection de registros

---

### 2. ✅ Web HealthController (Actualizado)

**Archivo:** `app/Http/Controllers/Web/HealthController.php`

**Inyección de dependencia:**
```php
public function __construct(
    HealthStatusService $healthStatusService,
    HealthVeterinarianService $veterinarianService  // NUEVO
) {
    $this->healthStatusService = $healthStatusService;
    $this->veterinarianService = $veterinarianService;
}
```

**Método `timeline(Request $request, Pet $pet)`:**
- Autorización: Solo propietario o admin
- Obtiene registros sanitarios con estado calculado
- Calcula estado general (green/yellow/red)
- **NUEVO:** Obtiene veterinarios recomendados:
  ```php
  $recommendedVeterinarians = $this->veterinarianService->getRecommendedVeterinarians(
      $pet,
      $request->user()
  );
  ```
- Pasa a vista:
  - `$pet`
  - `$records` (con health_status)
  - `$generalStatus`
  - `$recommendedVeterinarians` (Collection)
  - `$veterinarianService` (para acceso a métodos desde Blade)

---

### 3. ✅ API HealthController (Nuevo endpoint)

**Archivo:** `app/Http/Controllers/Api/HealthController.php`

**Método nuevo `recommendedVeterinarians(Request $request, Pet $pet)`:**
- **Ruta:** `GET /api/v1/pets/{pet}/health/veterinarians`
- **Autenticación:** Sanctum (auth:sanctum)
- **Autorización:** Solo propietario o admin (via `ensureCanManagePet()`)
- **Retorno JSON:**
  ```json
  {
    "message": "Veterinarios recomendados obtenidos correctamente.",
    "data": [
      {
        "place": {
          "id": 5,
          "name": "Clínica Veterinaria Los Andes",
          "category": "vet",
          "description": "...",
          "address": "Av. Principal 123",
          "lat": -33.8688,
          "lng": -56.1645,
          "phone": "+54 9 11 1234-5678",
          "website": "https://clinicaandes.com",
          "photo": "https://...",
          "status": "approved"
        },
        "distance": 2.5,
        "distance_unit": "km",
        "needed_services": ["vaccination", "deworming"],
        "urgency": "high"
      },
      ...
    ],
    "count": 3
  }
  ```

---

### 4. ✅ RecommendedVeterinarianResource

**Archivo:** `app/Http/Resources/RecommendedVeterinarianResource.php`

**Estructura de transformación:**
- Serializa información del veterinario (place)
- Incluye distancia con unidad
- Servicios necesarios como array
- Nivel de urgencia

**JSON Output:**
```php
[
  'place' => [id, name, category, description, address, lat, lng, phone, website, photo, status],
  'distance' => float (redondeado a 2 decimales),
  'distance_unit' => 'km',
  'needed_services' => array,
  'urgency' => 'high'|'medium'
]
```

---

### 5. ✅ Blade View - timeline.blade.php (Actualizada)

**Archivo:** `resources/views/health/timeline.blade.php`

**Nueva sección: "Veterinarios Cercanos Recomendados"**

**Posicionamiento:** Después de "Estado de Salud General", antes de "Timeline de registros"

**Visibilidad:** Solo si `$recommendedVeterinarians->isNotEmpty()`

**Estructura HTML:**
```
┌─ Gradient header (blue) ─────────────────────┐
│ 🏥 Veterinarios Cercanos Recomendados       │
│ "Encontramos veterinarios cercanos..."      │
└────────────────────────────────────────────┘

┌─ Grid de veterinarios (1-2 cols) ─────────┐
│ ┌─ Card 1 ────────────────────────────────┐│
│ │ [Foto/Icono 🏥]                        ││
│ │ Nombre vet          [Urgencia 🚨]      ││
│ │ Distancia: 2.5 km                      ││
│ │                                         ││
│ │ Servicios Necesarios                   ││
│ │ Vacunación, Desparasitación            ││
│ │                                         ││
│ │ Descripción breve...                   ││
│ │                                         ││
│ │ 📱 +54 9 11 1234-5678                 ││
│ │ 🌐 Sitio web                           ││
│ │ 📍 Av. Principal 123                   ││
│ │                                         ││
│ │ [Ver detalles]    [❤️]                  ││
│ └─────────────────────────────────────────┘│
│                                             │
│ ┌─ Card 2 ────────────────────────────────┐│
│ ... (más veterinarios)                    │
│ └─────────────────────────────────────────┘│
└────────────────────────────────────────────┘

💡 Consejo: Puedes guardar estos veterinarios
   en favoritos para acceder fácilmente...
```

**Features técnicas:**

- **Grid responsivo:** 1 columna (mobile), 2 (tablet), mantiene en desktop
- **Imágenes:** Si place.photo existe → muestra foto con hover zoom (scale-105)
  - Si no → icono gradiente azul 🏥
- **Distancia:** Redondeada a 1 decimal con "km de distancia"
- **Urgencia emoji:** Dinámico según `$veterinarianService->getUrgencyEmoji($vet->urgency)`
- **Servicios:** Obtenidos via `$veterinarianService->getServicesDescription($vet->needed_services)`
- **Contacto:**
  - Teléfono: link `tel:` si existe
  - Website: link target="_blank" si existe
  - Dirección: solo mostrar
- **Botón favorito:** Toggle AJAX sin reload, uses localStorage auth_token
  - Cambios: 🤍 → ❤️ (POST) o ❤️ → 🤍 (DELETE)
- **Animaciones:** Hover card (shadow-lg), scale botones, transiciones suaves

**JavaScript integrado:**
```javascript
// Toggle favorito con AJAX
document.querySelectorAll('.favorite-toggle-btn').forEach(btn => {
  btn.addEventListener('click', async function(e) {
    const placeId = this.dataset.placeId;
    const isFavorite = this.textContent === '❤️';
    const method = isFavorite ? 'DELETE' : 'POST';
    
    fetch(`/api/v1/places/${placeId}/favorite`, {
      method: method,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
      }
    })
    .then(r => r.json())
    .then(data => {
      if (data.message) {
        this.textContent = isFavorite ? '🤍' : '❤️';
      }
    })
    .catch(err => alert('Error al actualizar favorito'));
  });
});
```

---

### 6. ✅ Rutas API

**Archivo:** `routes/api.php`

**Nueva ruta:**
```php
Route::get('/pets/{pet}/health/veterinarians', [HealthController::class, 'recommendedVeterinarians'])
    ->name('api.v1.pets.health.veterinarians')
    ->where('pet', '[0-9]+');
```

**Posicionamiento:** ANTES de `apiResource('pets.health')` para evitar conflictos de enrutamiento

**Constraints:** `where('pet', '[0-9]+')` para asegurar que solo IDs numéricos coincidan

**Middleware:** Heredado del grupo `auth:sanctum` (rutas protegidas)

---

### 7. ✅ Test Suite - HealthVeterinarianTest.php

**Archivo:** `tests/Feature/HealthVeterinarianTest.php`

**11 tests implementados:**

#### Setup
- Usuario con ubicación (lat: -33.8688, lng: -56.1645)
- Pet asociado al usuario

#### 1. `getCriticalHealthRecords_returns_only_yellow_and_red_records`
- Crea 3 registros: green (+15d), yellow (+3d), red (-5d)
- Verifica que getCriticalHealthRecords retorna solo 2
- Valida que ambos tengan estado yellow o red

#### 2. `getRecommendedVeterinarians_returns_empty_when_no_critical_records`
- Crea solo registros verdes
- Verifica retorno vacío

#### 3. `getRecommendedVeterinarians_finds_nearby_vets`
- Crea registro crítico
- Crea vet aprobado en misma ubicación
- Verifica que se retorna el vet

#### 4. `getRecommendedVeterinarians_respects_search_radius`
- Crea registro crítico
- Crea vet lejano (>5km)
- Crea vet cercano (<1km)
- Verifica que solo se retorna el cercano con radius=5km
- Valida límite de 5 vets máximo

#### 5. `getUrgencyLevel_returns_high_for_red_records`
- Crea registro rojo (vencido)
- Verifica urgency = 'high'

#### 6. `getUrgencyLevel_returns_medium_for_yellow_only`
- Crea solo registro amarillo
- Verifica urgency = 'medium'

#### 7. `api_endpoint_returns_recommended_veterinarians`
- Crea registro crítico + vet cercano
- Llama GET `/api/v1/pets/{pet}/health/veterinarians` con usuario autenticado
- Verifica respuesta 200 OK con estructura JSON correcta
- Valida campos: place (id, name, category, address, phone, website)
- Valida campos: distance, distance_unit, needed_services, urgency, count

#### 8. `api_endpoint_requires_authentication`
- Llama endpoint sin autenticación
- Verifica respuesta 401 Unauthorized

#### 9. `api_endpoint_returns_403_if_not_owner`
- Otro usuario intenta acceder a pet que no le pertenece
- Verifica respuesta 403 Forbidden

#### 10. `getServicesDescription_formats_types_correctly`
- Entrada: ['vaccination', 'deworming', 'checkup']
- Verifica descripción contiene: 'Vacunación', 'Desparasitación', 'Revisión general'

#### 11. `getUrgencyEmoji_returns_correct_emojis`
- Verifica: high → 🚨, medium → ⏰, default → ℹ️

**Coverage:**
- Service layer: getCriticalHealthRecords, getRecommendedVeterinarians, geolocalización, urgency
- API endpoint: autenticación, autorización, respuesta JSON, estructura
- Casos edge: sin registros críticos, radio de búsqueda, límites

---

## 🔄 Flujo de Integración Completo

```
Usuario accede a /pets/{id}/health
    ↓
Web HealthController::timeline()
    ↓
¿Usuario tiene ubicación? → NO → No mostrar vets
                         → SÍ ↓
¿Pet tiene registros críticos? → NO → No mostrar vets
                                → SÍ ↓
getRecommendedVeterinarians()
    ├─ getCriticalHealthRecords() → filtra yellow/red
    ├─ findNearbyVeterinarians() → Haversine formula
    │  └─ WHERE category='vet' AND status='approved'
    │  └─ HAVING distance <= radiusKm
    │  └─ ORDER BY distance
    │  └─ LIMIT 5
    └─ Mapea: place, distance, needed_services, urgency
    ↓
Pasa $recommendedVeterinarians a vista
    ↓
Blade timeline.blade.php
    ├─ IF $recommendedVeterinarians->isNotEmpty()
    │  ├─ Renderiza header con gradient azul
    │  └─ Grid 1-2 cols:
    │     └─ Por cada vet:
    │        ├─ Foto/icono
    │        ├─ Nombre + distancia + urgencia emoji
    │        ├─ Servicios necesarios
    │        ├─ Contacto (tel, web, dirección)
    │        └─ Botones: "Ver detalles" + favorito
    └─ Incluye JavaScript para toggle favorito

Cliente mobile (GET /api/v1/pets/{pet}/health/veterinarians)
    ↓
API Response JSON con vets recomendados
```

---

## 🧪 Validaciones Implementadas

**Backend:**
- ✅ Ubicación de usuario requerida (lat/lng)
- ✅ Solo registros con next_due_date no nulo
- ✅ Filtro por estado sanitario (yellow/red)
- ✅ Filtro por categoría (vet)
- ✅ Filtro por estado (approved)
- ✅ Límite máximo 5 veterinarios
- ✅ Ordenamiento por distancia
- ✅ Autorización: solo propietario/admin accede a /pets/{pet}/health/veterinarios

**Frontend:**
- ✅ Condición de visualización: solo si $recommendedVeterinarians no vacía
- ✅ Fallback a icono si no hay foto
- ✅ Links seguros: tel:, https://, target="_blank"
- ✅ Manejo de campos opcionales: phone, website
- ✅ AJAX favoritos con manejo de errores

---

## 📊 Métricas

| Métrica | Valor |
|---------|-------|
| Tests nuevos | 11 |
| Tests totales | 224 |
| Assertions | 973 |
| Código cubierto | Service, API, Web, Blade |
| Geolocalización | Haversine en SQL |
| Radio búsqueda default | 10 km |
| Vets max retornados | 5 |
| HTTP Status codes | 200 (OK), 403 (Forbidden), 404 (Not Found), 401 (Unauthorized) |

---

## 🚀 Próximos Pasos (Opcional)

1. **Notificaciones:** Email cuando hay vets disponibles por registros críticos
2. **Citas:** Integrar sistema de citas con veterinarios
3. **Historial:** Guardar veterinarios visitados
4. **Rating:** Calificar experiencia con veterinarios
5. **Analytics:** Trackear qué tipos de registros generan más búsquedas de vets
6. **Mobile app:** Consumir endpoint API GET /pets/{pet}/health/veterinarians

---

## 📝 Notas de Desarrollo

- **Haversine Formula:** Cálculo preciso de distancias geodésicas; requiere lat/lng en formato decimal
- **Model Binding:** La ruta usa `{pet}` con constraint `[0-9]+` para evitar conflictos con otras rutas
- **Service Layer:** Separar lógica de negocio (HealthVeterinarianService) facilita testing y reutilización
- **Blade Responsiveness:** Grid 1-2 columnas ajusta automáticamente; no necesita media queries
- **AJAX Favoritos:** Reutiliza endpoint existente `/api/v1/places/{id}/favorite`
