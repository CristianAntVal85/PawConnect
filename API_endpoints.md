# 🐾 PawConnect — API REST Detallada

**Version:** v1 · **Base path:** `/api/v1` · **Fecha:** 2026-07

---

## 1. Objetivo de esta API

| Objetivo | Detalle |
|---|---|
| **Separación** | Frontend y backend evolucionan independientemente |
| **Reutilización** | Web, móvil y desktop consumen el mismo backend |
| **Escalabilidad** | Versionado por ruta (`/v1`, `/v2`) sin romper clientes |
| **Seguridad** | Auth, permisos, validaciones y rate limiting centralizados |
| **Testing** | Contratos claros facilitan tests automatizados y Postman |

---

## 2. Convenciones Generales

### 2.1 Formato

```
Content-Type: application/json  (request y response)
```

### 2.2 Autenticación

Laravel Sanctum con Bearer Token:

```
Authorization: Bearer {token}
```

### 2.3 Roles

| Rol | Acceso |
|---|---|
| `user` | Recursos propios |
| `admin` | Gestión global bajo prefijo `/admin` |

### 2.4 Formato de respuesta estándar

**Éxito:**

```json
{
  "success": true,
  "message": "texto",
  "data": {}
}
```

**Error:**

```json
{
  "success": false,
  "message": "texto",
  "errors": {}
}
```

### 2.5 Paginación

Parámetros: `page`, `per_page` (default 15, max 100)

```json
{
  "success": true,
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 120,
    "last_page": 8
  }
}
```

### 2.6 Códigos HTTP

| Código | Significado |
|:---:|---|
| `200` | OK |
| `201` | Created |
| `204` | No Content |
| `400` | Bad Request |
| `401` | Unauthorized |
| `403` | Forbidden |
| `404` | Not Found |
| `409` | Conflict |
| `422` | Unprocessable Entity |
| `500` | Internal Server Error |

### 2.7 Ownership

Cualquier recurso de usuario (`pets`, `health`, `orders`, `favorites`) debe validar pertenencia. Si el recurso existe pero no pertenece al usuario: **403**.

---

## 3. AUTH

### `POST /auth/register` — Pública

```json
{
  "name": "Ana",
  "email": "ana@example.com",
  "password": "Secret123!",
  "password_confirmation": "Secret123!"
}
```

**Validación:** `name: required|max:100` · `email: required|email|unique` · `password: min:8|confirmed`

**Respuesta 201:**

```json
{
  "success": true,
  "message": "Usuario registrado",
  "data": {
    "user": { "id": 1, "name": "Ana", "email": "ana@example.com" },
    "token": "..."
  }
}
```

---

### `POST /auth/login` — Pública

```json
{ "email": "ana@example.com", "password": "Secret123!" }
```

**Respuesta 200:** user + token · **401** si credenciales inválidas

---

### `POST /auth/logout` — 🔒 Auth requerida

`204 No Content` — revoca el token actual

---

### `POST /auth/forgot-password` — Pública

```json
{ "email": "ana@example.com" }
```

**Respuesta 200**

---

### `POST /auth/reset-password` — Pública

```json
{
  "token": "token-reset",
  "email": "ana@example.com",
  "password": "NewSecret123!",
  "password_confirmation": "NewSecret123!"
}
```

**Respuesta 200**

---

## 4. USERS

### `GET /users/me` — 🔒

**Respuesta 200:** perfil del usuario autenticado

---

### `PUT /users/me` — 🔒 (parcial)

```json
{
  "name": "Ana Garcia",
  "photo": "storage/users/1.jpg",
  "lat": 40.4168,
  "lng": -3.7038,
  "address": "Calle X",
  "billing_address": "Calle Y"
}
```

**Respuesta 200**

---

### `DELETE /users/me` — 🔒

**Respuesta 204** · Recomendado soft delete

---

## 5. PETS

### `GET /pets` — 🔒

**Query params:** `species` (opcional), `available_for_walks` (opcional)

**Respuesta 200:** lista de mascotas del usuario

---

### `POST /pets` — 🔒

```json
{
  "species": "dog",
  "name": "Thor",
  "breed": "Labrador",
  "age": 4,
  "weight": 22.5,
  "size": "large",
  "energy_level": "high",
  "personality": "Juguetón",
  "available_for_walks": true
}
```

**Validación:**

| Campo | Regla |
|---|---|
| `species` | `in:dog,cat,bird,rodent,other` |
| `name` | `required\|max:100` |
| `weight` | `numeric\|min:0` |
| `size` | `in:small,medium,large,xlarge` |
| `energy_level` | `in:low,medium,high` |

**Respuesta 201**

---

### `GET /pets/{id}` — 🔒

**Respuesta 200** · **404** no existe · **403** no pertenece

---

### `PUT /pets/{id}` — 🔒 (parcial)

**Respuesta 200**

---

### `DELETE /pets/{id}` — 🔒

**Respuesta 204**

---

## 6. CANINES *(vista especializada social)*

> Solo mascotas con `species = dog`, para el módulo de match y paseos.

### `GET /canines` — 🔒

**Query params:** `nearby=true`, `max_distance_km=10`

---

### `GET /canines/{id}` — 🔒

---

## 7. HEALTH

### `GET /pets/{id}/health` — 🔒

**Respuesta 200:** historial sanitario completo de la mascota

---

### `POST /pets/{id}/health` — 🔒

```json
{
  "type": "dewormer",
  "last_date": "2026-07-01",
  "periodicity_days": 30,
  "notes": "Sin incidencias",
  "notifications_active": true
}
```

> **Regla:** `next_due_date` se calcula en servidor = `last_date + periodicity_days`

**Respuesta 201**

---

### `PUT /pets/{id}/health/{hid}` — 🔒 (parcial)

**Respuesta 200**

---

### `DELETE /pets/{id}/health/{hid}` — 🔒

**Respuesta 204**

---

## 8. MATCHES

> ⚠️ Solo aplica a caninos (`species = dog`) en V1.

### `GET /matches/candidates` — 🔒

**Filtros internos:**
- Excluir mascotas del mismo usuario
- Excluir ya vistos (`pet_swipes`)
- Proximidad geográfica
- Compatibilidad por tamaño / energía

---

### `POST /matches/like/{petId}` — 🔒

**Respuesta 200:**

```json
{
  "success": true,
  "message": "Like registrado",
  "data": {
    "matched": true,
    "match_id": 22
  }
}
```

---

### `POST /matches/pass/{petId}` — 🔒

**Respuesta 200**

---

### `GET /matches` — 🔒

**Respuesta 200:** matches activos del usuario

---

## 9. MESSAGES

### `GET /matches/{id}/messages` — 🔒

**Respuesta 200:** mensajes del match

---

### `POST /matches/{id}/messages` — 🔒

```json
{ "body": "Hola! Vamos al parque?" }
```

**Validación:** `body: required|string|max:2000`

**Respuesta 201**

---

## 10. WALKS

### `GET /walks` — 🔒

**Query params:** `lat`, `lng`, `radius_km`, `date_from`

---

### `POST /walks` — 🔒

```json
{
  "title": "Paseo tarde",
  "description": "Parque central",
  "lat": 40.40,
  "lng": -3.70,
  "scheduled_at": "2026-07-25T18:30:00Z",
  "max_pets": 6
}
```

**Respuesta 201**

---

### `GET /walks/{id}` — 🔒

---

### `POST /walks/{id}/join` — 🔒

```json
{ "pet_id": 10 }
```

**Reglas:** pet_id del usuario · `species = dog` en V1 · walk no completo

**Respuesta 200**

---

### `DELETE /walks/{id}/leave` — 🔒

```json
{ "pet_id": 10 }
```

**Respuesta 204**

---

## 11. PRODUCTS

### `GET /products` — Pública

**Query params:** `category`, `min_price`, `max_price`, `in_stock=true`, `species`

---

### `GET /products/recommended/{petId}` — 🔒

> Recomendación personalizada por especie, peso, edad, raza e historial sanitario.

---

### `GET /products/{id}` — Pública

---

## 12. ORDERS

### `POST /orders` — 🔒

```json
{
  "product_id": 31,
  "pet_id": 10,
  "quantity": 2,
  "shipping_address": "Calle Y 12",
  "payment_method": "card"
}
```

**Flujo:**
1. Validar stock y ownership de `pet`
2. Crear `PaymentIntent` en Stripe
3. Confirmar pago
4. Crear `Order` + `OrderStatusHistory('pending')`

**Respuesta 201**

---

### `GET /orders` — 🔒

**Respuesta 200:** pedidos del usuario autenticado

---

### `GET /orders/{id}` — 🔒

**Respuesta 200** · **403** si no pertenece

---

## 13. PLACES

### `GET /places` — Pública

**Query params:** `category` (`pipican|beach|park|vet|shop|restaurant|hotel`), `lat`, `lng`, `radius_km`

> Solo devuelve lugares con `status = approved`.

---

### `POST /places` — 🔒

```json
{
  "name": "Vet Centro",
  "category": "vet",
  "description": "24h",
  "lat": 40.41,
  "lng": -3.70,
  "address": "Calle Z",
  "phone": "+34...",
  "website": "https://...",
  "photo": "storage/places/1.jpg"
}
```

> Status inicial forzado por servidor: `pending`. Solo admin puede aprobar.

**Respuesta 201:**

```json
{ "id": 77, "name": "Vet Centro", "category": "vet", "status": "pending" }
```

---

### `GET /places/{id}` — Pública

---

### `POST /places/{id}/reviews` — 🔒

```json
{ "rating": 5, "body": "Muy buena atención", "photo": "storage/reviews/r1.jpg" }
```

**Validación:** `rating: integer|min:1|max:5`

**Respuesta 201**

---

### `POST /places/{id}/favorites` — 🔒

**Respuesta 201**

---

### `DELETE /places/{id}/favorites` — 🔒

**Respuesta 204**

---

## 14. ADMIN *(solo `role = admin`)*

**Prefijo:** `/api/v1/admin` · **Middleware:** `auth:sanctum` + `is_admin`

### 14.1 Users

| Método | Ruta |
|---|---|
| GET | `/admin/users` |
| GET | `/admin/users/{id}` |
| PUT | `/admin/users/{id}` |
| DELETE | `/admin/users/{id}` |

### 14.2 Pets

| Método | Ruta |
|---|---|
| GET | `/admin/pets` |
| GET | `/admin/pets/{id}` |
| POST | `/admin/pets` |
| PUT | `/admin/pets/{id}` |
| DELETE | `/admin/pets/{id}` |

### 14.3 Products

| Método | Ruta |
|---|---|
| GET | `/admin/products` |
| GET | `/admin/products/{id}` |
| POST | `/admin/products` |
| PUT | `/admin/products/{id}` |
| DELETE | `/admin/products/{id}` |

### 14.4 Orders

| Método | Ruta | Nota |
|---|---|---|
| GET | `/admin/orders` | |
| GET | `/admin/orders/{id}` | |
| PUT | `/admin/orders/{id}` | Cambio de estado |

### 14.5 Providers

| Método | Ruta |
|---|---|
| GET | `/admin/providers` |
| GET | `/admin/providers/{id}` |
| POST | `/admin/providers` |
| PUT | `/admin/providers/{id}` |
| DELETE | `/admin/providers/{id}` |

### 14.6 Places

| Método | Ruta | Nota |
|---|---|---|
| GET | `/admin/places` | |
| GET | `/admin/places/{id}` | |
| POST | `/admin/places` | |
| PUT | `/admin/places/{id}` | Puede actualizar `status` |
| DELETE | `/admin/places/{id}` | |
| POST | `/admin/places/{id}/approve` | `status => approved` |
| POST | `/admin/places/{id}/reject` | `status => rejected` |

### 14.7 Reviews

| Método | Ruta |
|---|---|
| GET | `/admin/reviews` |
| DELETE | `/admin/reviews/{id}` |

---

## 15. Modelo de Errores

### 401 Unauthorized

```json
{ "success": false, "message": "No autenticado" }
```

### 403 Forbidden

```json
{ "success": false, "message": "No autorizado para este recurso" }
```

### 422 Validation Error

```json
{
  "success": false,
  "message": "Errores de validación",
  "errors": { "field": ["mensaje"] }
}
```

### 404 Not Found

```json
{ "success": false, "message": "Recurso no encontrado" }
```

---

## 16. Checklist Técnico para Implementación

- [ ] Definir rutas en `routes/api.php` con `Route::prefix('v1')`
- [ ] Usar Form Requests: `StorePetRequest`, `UpdatePetRequest` y equivalentes
- [ ] Aplicar Policies por recurso y ownership
- [ ] Encapsular lógica en Services (`MatchService`, `RecommendationService`, etc.)
- [ ] API Resources para serialización consistente
- [ ] Rate limiting por usuario/IP
- [ ] Documentar con Scribe → `php artisan scribe:generate`

---

## 17. Criterio de Aceptación

- [x] Todos los endpoints principales listados y versionados
- [x] Cada endpoint define auth, payload y código de respuesta
- [x] Rutas `user` vs `admin` claramente diferenciadas
- [x] Política multiespecie con foco canino en social trazable

---

## 18. Matriz de Trazabilidad RF → API → Dominio

| RF | Descripción | Endpoint(s) | Modelos | Servicio / Regla | Tests sugeridos |
|:---:|---|---|---|---|---|
| RF1 | Registrar usuarios | `POST /auth/register` | User | Hash password + email único | Registro OK, email duplicado (422) |
| RF2 | Login y logout | `POST /auth/login` · `/auth/logout` | User, Token | Sanctum lifecycle | Login OK, 401 inválido, logout revoca |
| RF3 | Editar perfil | `PUT /users/me` | User | Ownership implícito | Update parcial, validación |
| RF4 | Dirección facturación | `PUT /users/me` | User | Persistir billing_address | Reutilización en pedidos |
| RF5 | Eliminar cuenta | `DELETE /users/me` | User | Soft delete | 204 + token 401 posterior |
| RF6 | Registrar mascotas | `GET/POST /pets` | Pet | Enum species | Múltiples mascotas, filtro species |
| RF7 | Datos completos mascota | `POST /pets` · `PUT /pets/{id}` | Pet, PetPhoto, HealthRecord | Enums size/energy/species | Validación enums, persistencia |
| RF8 | Editar / eliminar mascota | `GET/PUT/DELETE /pets/{id}` | Pet | Policy ownership | Owner puede, tercero 403 |
| RF9 | Candidatos compatibles | `GET /matches/candidates` · `/canines` | Pet, PetSwipe | MatchService | Excluye propios y ya vistos |
| RF10 | Aceptar / rechazar | `POST /matches/like/{petId}` · `/pass/{petId}` | PetSwipe | Idempotente por par | Like registra swipe, pass bloquea |
| RF11 | Match mutuo | `POST /matches/like/{petId}` · `GET /matches` | Match, PetSwipe | Mutual like → Match | Match al segundo like recíproco |
| RF12 | Chat con match | `GET/POST /matches/{id}/messages` | Message, Match | Solo miembros del match | Enviar/leer propio, 403 ajeno |
| RF13 | Mensajes tiempo real | `POST /matches/{id}/messages` | Message | Broadcast Reverb | Persistencia + dispatch evento |
| RF14 | Estado escribiendo | Canal realtime | — | `client-typing` canal privado | Auth canal privado |
| RF15 | Crear paseos | `POST /walks` | Walk | Validación fecha/aforo | Create OK, fecha inválida 422 |
| RF16 | Unirse a paseos | `POST /walks/{id}/join` · `/leave` | Walk, Pet, walk_pet | Pet owner + dog V1 + no lleno | Join/leave, conflicto |
| RF17 | Ubicación en mapa | `GET /walks` · `GET /places` | Walk, PetFriendlyPlace | Filtros geográficos | Filtra por lat/lng/radius |
| RF18 | Historial paseos | `GET /walks` · `GET /walks/{id}` | Walk, walk_pet | Listado por participación | Historial con mascotas |
| RF19 | Registrar tratamiento | `POST /pets/{id}/health` | HealthRecord | Tipos permitidos | Create válido e inválido |
| RF20 | Datos tratamiento | `POST/PUT /pets/{id}/health` | HealthRecord | periodicity_days > 0 | Update parcial, validaciones |
| RF21 | Cálculo próxima dosis | `POST/PUT /pets/{id}/health` | HealthRecord | next_due_date = last_date + days | Assert exactitud next_due_date |
| RF22 | Notificaciones vencimiento | `GET /notifications` | HealthRecord, Notification | HealthReminderService + Scheduler | Job selecciona due soon |
| RF23 | Historial sanitario | `GET /pets/{id}/health` | HealthRecord | Ordenado por fecha + ownership | Listado ordenado |
| RF24 | Ver productos | `GET /products` · `/products/{id}` | Product, Provider | Filtros categoría/precio/stock | Filtros combinados |
| RF25 | Recomendación mascota | `GET /products/recommended/{petId}` | Product, Pet, HealthRecord | RecommendationService | Ranking relevante por perfil |
| RF26 | Compra en un clic | `POST /orders` | Order, OrderStatusHistory | OrderService + Stripe | Flujo OK y fallo pago |
| RF27 | Gestión pedidos | `POST/GET /orders` · `PUT /admin/orders/{id}` | Order, OrderStatusHistory | Transición estados + historial | Cambio estado crea historial |
| RF28 | Ver estado pedido | `GET /orders` · `/orders/{id}` | Order, OrderStatusHistory | Ownership pedidos | Usuario solo ve los suyos |
| RF29 | Dirección facturación en pedido | `PUT /users/me` · `POST /orders` | User, Order | Fallback billing desde perfil | Hereda dirección si no se envía |
| RF30 | Lugares pet-friendly | `GET /places` | PetFriendlyPlace | Solo `approved` en público | Visibilidad por status |
| RF31 | Lugares en mapa | `GET /places` | PetFriendlyPlace | Filtros geográficos | Resultados por radio |
| RF32 | Filtrar por categoría | `GET /places?category=...` | PetFriendlyPlace | Enum category validado | Categoría válida/inválida |
| RF33 | Ficha detallada lugar | `GET /places/{id}` | PetFriendlyPlace, PlaceReview | Carga rating/reviews | Detalle con agregados |
| RF34 | Añadir reseñas | `POST /places/{id}/reviews` | PlaceReview | rating 1..5 + ownership | Create review, rating inválido |
| RF35 | Guardar favoritos | `POST/DELETE /places/{id}/favorites` | place_favorites, User | Toggle insert/delete | Add/remove, idempotencia |
| RF36 | Integración sanitario + lugares | `GET /places?category=vet` · `/recommended/{petId}` | PetFriendlyPlace, Product, HealthRecord | RecommendationService + frontend | Sugerencias coherentes |
| RF37 | Panel admin | `/admin/*` | User, Pet, Product, Order, Provider, Place, Review | Middleware is_admin | Admin OK, user 403 |
| RF38 | Estadísticas | `GET /admin/stats` | User, Pet, Match, Order | Agregaciones por período | Métricas básicas totales/series |

---

## 19. Trazabilidad Inversa (Endpoint → RF)

| Endpoint | RF cubiertos |
|---|---|
| `/auth/register` | RF1 |
| `/auth/login`, `/auth/logout` | RF2 |
| `/users/me` (GET/PUT/DELETE) | RF3, RF4, RF5 |
| `/pets*` | RF6, RF7, RF8 |
| `/canines`, `/matches/candidates` | RF9 |
| `/matches/like`, `/matches/pass` | RF10, RF11 |
| `/matches`, `/matches/{id}/messages` | RF11, RF12, RF13 |
| `/walks*` | RF15, RF16, RF17, RF18 |
| `/pets/{id}/health*` | RF19, RF20, RF21, RF22, RF23 |
| `/products*`, `/products/recommended/{petId}` | RF24, RF25 |
| `/orders*` | RF26, RF27, RF28, RF29 |
| `/places*`, `/places/{id}/reviews`, `/places/{id}/favorites` | RF30–RF36 |
| `/admin/*` | RF37, RF38 |
