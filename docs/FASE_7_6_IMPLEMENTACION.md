# ✅ Fase 7.6 — Integración Marketplace con Módulo Sanitario

**Estado:** ✅ COMPLETADA  
**Fecha:** Agosto 2026

---

## 📋 Descripción de la Fase

Conectar el módulo sanitario de mascotas con el marketplace de forma contextual y útil.  
Cuando el usuario visualiza su historial sanitario, puede ver qué productos del marketplace
son adecuados para cada registro. El caso especial y más avanzado es la **desparasitación externa**:
si el registro es de tipo `deworming`, el sistema calcula automáticamente la combinación
óptima de pastillas antiparasitarias según el peso exacto del animal.

---

## ✅ Tareas Completadas

### 1. ✅ Migración: campo `dosage_kg` en tabla `products`

**Archivo:** `database/migrations/2026_08_12_045157_add_dosage_kg_to_products_table.php`

- Columna `dosage_kg DECIMAL(6,2) NULLABLE`
- Semántica: kg de peso corporal que cubre **cada unidad** del producto (solo para pastillas de dosificación)
- Null → el producto no es de dosificación (accesorio, alimento, etc.)

---

### 2. ✅ `RecommendationService::getDewormingCombination(Pet $pet)`

**Archivo:** `app/Services/RecommendationService.php`

**Algoritmo greedy de combinación:**
1. Recupera todas las pastillas antiparasitarias con `dosage_kg > 0` y `stock > 0` para la especie
2. Ordena por `dosage_kg DESC` (empezar por las más grandes)
3. Para cada pastilla: calcula cuántas unidades entran en el peso restante → `floor(restante / dosage_kg)`
4. Si queda resto positivo, añade 1 unidad extra de la pastilla más pequeña para cubrirlo
5. Retorna la combinación con `covered: bool` y `total_dosage_kg`

**Ejemplo práctico:**
```
Perro: 35 kg
Pastillas disponibles: 20 kg, 10 kg, 5 kg

Iteración 1: floor(35 / 20) = 1 × 20 kg → resta 15 kg
Iteración 2: floor(15 / 10) = 1 × 10 kg → resta 5 kg
Iteración 3: floor(5 / 5)   = 1 × 5 kg  → resta 0 kg

Resultado: [1×20kg, 1×10kg, 1×5kg] → cubierto ✅
```

**Retorno:**
```php
[
    'items' => [
        ['product' => Product, 'quantity' => int],
        // ...
    ],
    'total_dosage_kg' => float,
    'pet_weight_kg' => float,
    'covered' => bool,
]
```

---

### 3. ✅ Endpoint API: `GET /api/v1/products/deworming-combo/{petId}`

**Controller:** `app/Http/Controllers/Api/ProductController::dewormingCombo()`

- Autenticado + autorización (mascota del usuario)
- Devuelve combinación calculada con precio por ítem y subtotal
- Respuesta JSON:
```json
{
  "message": "Combinación de pastillas para Rex (35 kg).",
  "pet": { "id": 1, "name": "Rex", "species": "dog", "weight_kg": 35 },
  "combination": [
    { "product": { ... }, "quantity": 1, "subtotal": 33.00 },
    { "product": { ... }, "quantity": 1, "subtotal": 18.00 },
    { "product": { ... }, "quantity": 1, "subtotal": 10.50 }
  ],
  "total_dosage_kg": 35,
  "covered": true
}
```

---

### 4. ✅ Endpoint API: `GET /api/v1/products/by-service/{serviceType}/{petId}`

**Controller:** `app/Http/Controllers/Api/ProductController::byServiceType()`

- Para todos los tipos de registro sanitario excepto `deworming` (que usa el endpoint anterior)
- Filtra por especie, rango de peso, categorías relacionadas al tipo de servicio
- Devuelve paginado con datos del servicio y la mascota

**Mapeo tipo → categorías:**
| Tipo | Categorías prioritarias |
|---|---|
| `vaccination` | salud, veterinaria, medicinas |
| `deworming` | antiparasitarios, salud, veterinaria |
| `checkup` | salud, transportines, accesorios |
| `surgery` | vendajes, recuperación, conos |
| `dental` | higiene, dental |
| `grooming` | higiene, estetica |

---

### 5. ✅ `HealthReminderNotification` enriquecida con productos

**Archivo:** `app/Notifications/HealthReminderNotification.php`

**Datos en base de datos (`toArray`):**  
- Campo `product_recommendations` añadido al payload
- Para `deworming`: incluye el combo de pastillas calculado automáticamente al lanzarse la notificación
- Para otros tipos: `product_recommendations: null`

```php
// Ejemplo de payload para notificación de desparasitación
[
    'type' => 'deworming',
    'pet_name' => 'Rex',
    'product_recommendations' => [
        'type' => 'deworming_combo',
        'pet_weight_kg' => 35.0,
        'total_dosage_kg' => 35.0,
        'covered' => true,
        'items' => [
            ['product_id' => 5, 'product_name' => 'Antiparasitario 20kg', 'dosage_kg' => 20, 'price' => 22.00, 'quantity' => 1],
            ['product_id' => 6, 'product_name' => 'Antiparasitario 10kg', 'dosage_kg' => 10, 'price' => 12.00, 'quantity' => 1],
            ['product_id' => 7, 'product_name' => 'Antiparasitario 5kg',  'dosage_kg' => 5,  'price' => 7.50,  'quantity' => 1],
        ],
    ],
]
```

**Email (`toMail`):**  
- Para `deworming`: añade sección "🛍️ Productos recomendados" con la lista de pastillas y precios
- Muestra confirmación "✅ Esta combinación cubre los X kg de [Mascota]"

---

### 6. ✅ Vista historial sanitario con UX diferenciada

**Archivo:** `resources/views/health/timeline.blade.php`

**Botón contextual:**
- Tipo `deworming` → botón **"💊 Combo pastillas"** (verde) + tooltip explicativo con el nombre del animal
- Otros tipos → botón **"🛍️ Productos"** genérico

**Modal de combo antiparasitario (para `deworming`):**
- Banner verde con explicación del algoritmo
- Listado en forma de tabla: emoji de pastilla, nombre del producto, dosis cubierta, cantidad (×N), subtotal
- Línea de total estimado
- Botón "Ver todos en marketplace" para explorar más opciones

**Modal genérico (para otros tipos):**
- Grid de tarjetas de producto con foto/placeholder, nombre, categoría, precio y stock
- Botón "Ver detalle" hacia la ficha del producto

---

### 7. ✅ Factory con estado `dewormingPill`

**Archivo:** `database/factories/ProductFactory.php`

```php
Product::factory()->dewormingPill(20, 'dog')->create();  // Pastilla de 20kg para perros
Product::factory()->dewormingPill(10, 'dog')->create();  // Pastilla de 10kg para perros
Product::factory()->dewormingPill(5, 'dog')->create();   // Pastilla de 5kg para perros
```

---

## 🧪 Tests Añadidos

**Archivo:** `tests/Feature/RecommendationTest.php` — 8 tests nuevos

| Test | Descripción |
|---|---|
| combo 35 kg con 20+10+5 | Caso principal: 3 pastillas distintas, cobertura exacta |
| múltiples unidades 40 kg | Perro 40 kg con sólo pastilla de 20 → 2 unidades |
| resto con pastilla más pequeña | Peso no exacto: 27 kg con 20+10 → cubre con margen |
| covered=false sin pastillas | BD vacía → retorna covered false |
| covered=false sin peso del animal | Pet.weight=null → no puede calcular |
| endpoint API /deworming-combo | HTTP 200, estructura correcta, covered=true |
| endpoint 403 mascota ajena | Autorización correcta |
| notificación deworming incluye combo | product_recommendations con tipo deworming_combo |
| notificación vaccination sin combo | product_recommendations null para otros tipos |

---

## 📁 Archivos Modificados / Creados

| Archivo | Cambio |
|---|---|
| `database/migrations/2026_08_12_045157_add_dosage_kg_to_products_table.php` | **Nuevo** — columna dosage_kg |
| `app/Models/Product.php` | `$fillable` + `casts` con `dosage_kg` |
| `database/factories/ProductFactory.php` | Estado `dewormingPill($dosageKg, $species)` |
| `app/Services/RecommendationService.php` | Método `getDewormingCombination()` |
| `app/Http/Controllers/Api/ProductController.php` | Método `dewormingCombo()` |
| `routes/api.php` | Ruta `GET /api/v1/products/deworming-combo/{petId}` |
| `app/Notifications/HealthReminderNotification.php` | Enriquecida con `product_recommendations` + email con pastillas |
| `resources/views/health/timeline.blade.php` | Botón y modal diferenciados para deworming |
| `tests/Feature/RecommendationTest.php` | 8 tests nuevos de combo antiparasitario |
| `docs/06-plan.md` | Punto 7.6 marcado como completado con descripción detallada |

---

## 💡 Decisiones de Diseño

### Por qué `dosage_kg` como campo separado

`min_weight_kg`/`max_weight_kg` ya existen para indicar el **rango de peso apropiado** para el animal. Para las pastillas antiparasitarias el concepto es diferente: no importa si el perro "puede" usar el producto, sino **cuántos kg de peso corporal cubre cada pastilla**. Mezclar los dos conceptos en los mismos campos habría roto la semántica actual y los tests existentes.

### Por qué greedy y no programación dinámica

Las tallas de pastillas antiparasitarias son estandarizadas (1kg, 2kg, 5kg, 10kg, 20kg, 40kg) y cumplen la propiedad del sistema monedario: la pastilla grande siempre vale más que la suma de las pequeñas. El greedy es O(n) y da la combinación mínima de unidades.

### Por qué el combo en la notificación y no solo en el historial

El objetivo declarado del punto 7.6 es que "al llegarle una notificación diciéndole al usuario que en 7 días le caduca X producto, se muestre la o las pastillas que necesita". La notificación persiste en base de datos y el frontend puede leer `product_recommendations` directamente del payload sin hacer una segunda llamada a la API.
