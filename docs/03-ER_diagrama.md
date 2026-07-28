# 🐾 PawConnect — Diagrama Entidad-Relación (ER)

**Versión Web con Laravel 11** · Última revisión: Julio 2026

---

## Leyenda

| Símbolo | Significado |
|---|---|
| `PK` | Clave primaria (Primary Key) |
| `FK` | Clave foránea (Foreign Key) |
| `1 : N` | Uno a muchos |
| `N : N` | Muchos a muchos → requiere tabla pivote |
| `1 : 1` | Uno a uno |

| Tipo | Nombre completo |
|---|---|
| `BIG` | BIGINT |
| `VAR(n)` | VARCHAR(n) |
| `BOOL` | BOOLEAN |
| `DEC(p,s)` | DECIMAL |
| `TXT` | TEXT |
| `TS` | TIMESTAMP |
| `INT` | INTEGER |
| `ENUM` | Enumerado |

---

## Zona A — Núcleo (Usuario & Mascotas)

### `users`

| Columna | Tipo | Notas |
|---|---|---|
| `id` | BIG | PK |
| `name` | VAR(100) | |
| `email` | VAR(150) | unique |
| `password` | VAR(255) | |
| `role` | ENUM(`user`, `admin`) | |
| `photo` | VAR(255) | nullable |
| `lat` | DEC(10,7) | nullable |
| `lng` | DEC(10,7) | nullable |
| `address` | TXT | nullable |
| `billing_address` | TXT | nullable |
| `created_at` | TS | |

---

### `pets` *(1 : N con users)*

| Columna | Tipo | Notas |
|---|---|---|
| `id` | BIG | PK |
| `user_id` | BIG | FK → users |
| `species` | ENUM(`dog`,`cat`,`bird`,`rodent`,`other`) | |
| `name` | VAR(100) | |
| `breed` | VAR(100) | nullable |
| `age` | INT | nullable |
| `weight` | DEC(5,2) | kg, nullable |
| `size` | ENUM(`small`,`medium`,`large`,`xlarge`) | nullable |
| `energy_level` | ENUM(`low`,`medium`,`high`) | nullable |
| `personality` | TXT | nullable |
| `available_for_walks` | BOOL | |
| `created_at` | TS | |

---

### `pet_photos` *(1 : N con pets)*

| Columna | Tipo | Notas |
|---|---|---|
| `id` | BIG | PK |
| `pet_id` | BIG | FK → pets |
| `path` | VAR(255) | |
| `order` | INT | |

---

### `health_records` *(1 : N con pets)*

| Columna | Tipo | Notas |
|---|---|---|
| `id` | BIG | PK |
| `pet_id` | BIG | FK → pets |
| `type` | ENUM(`vaccine`,`dewormer`,`pipette`,`collar`,`vet_visit`,`food`) | |
| `last_date` | DATE | |
| `periodicity_days` | INT | |
| `notes` | TXT | nullable |
| `notifications_active` | BOOL | |
| `next_due_date` | DATE | **calculado** = last_date + periodicity_days |

---

## Zona B — Social (Match, Chat & Paseos)

### `pet_swipes` *(self-referencing en pets)*

| Columna | Tipo | Notas |
|---|---|---|
| `swiper_pet_id` | BIG | FK → pets |
| `swiped_pet_id` | BIG | FK → pets |
| `action` | ENUM(`like`,`pass`) | |
| `created_at` | TS | |

> Si `action = like` en ambas mascotas caninas → se crea un registro en `matches`.

---

### `matches`

| Columna | Tipo | Notas |
|---|---|---|
| `id` | BIG | PK |
| `pet_a_id` | BIG | FK → pets |
| `pet_b_id` | BIG | FK → pets |
| `status` | ENUM(`pending`,`matched`,`unmatched`) | |
| `matched_at` | TS | nullable |

---

### `messages` *(1 : N con matches)*

| Columna | Tipo | Notas |
|---|---|---|
| `id` | BIG | PK |
| `match_id` | BIG | FK → matches |
| `sender_id` | BIG | FK → users |
| `body` | TXT | |
| `read_at` | TS | nullable |
| `created_at` | TS | |

---

### `walks`

| Columna | Tipo | Notas |
|---|---|---|
| `id` | BIG | PK |
| `creator_id` | BIG | FK → users |
| `title` | VAR(150) | |
| `description` | TXT | nullable |
| `lat` | DEC(10,7) | |
| `lng` | DEC(10,7) | |
| `scheduled_at` | TS | |
| `max_pets` | INT | |

---

### `walk_pet` *(pivote N : N entre walks y pets)*

| Columna | Tipo | Notas |
|---|---|---|
| `walk_id` | BIG | FK → walks |
| `pet_id` | BIG | FK → pets |

---

## Zona C — Comercio (Proveedor, Productos & Pedidos)

### `providers`

| Columna | Tipo | Notas |
|---|---|---|
| `id` | BIG | PK |
| `name` | VAR(150) | |
| `email` | VAR(150) | |
| `phone` | VAR(30) | nullable |
| `address` | TXT | nullable |

---

### `products` *(1 : N con providers)*

| Columna | Tipo | Notas |
|---|---|---|
| `id` | BIG | PK |
| `provider_id` | BIG | FK → providers |
| `name` | VAR(150) | |
| `category` | ENUM(`food`,`dewormer`,`pipette`,`collar`,`supplement`,`other`) | |
| `description` | TXT | nullable |
| `price` | DEC(10,2) | |
| `stock` | INT | |
| `min_weight_kg` | DEC(5,2) | nullable |
| `max_weight_kg` | DEC(5,2) | nullable |
| `recommended_breeds` | VAR(255) | nullable |
| `photo` | VAR(255) | nullable |

---

### `orders` *(FK → users, products, pets)*

| Columna | Tipo | Notas |
|---|---|---|
| `id` | BIG | PK |
| `user_id` | BIG | FK → users |
| `product_id` | BIG | FK → products |
| `pet_id` | BIG | FK → pets |
| `quantity` | INT | |
| `total_price` | DEC(10,2) | |
| `shipping_address` | TXT | |
| `payment_method` | ENUM(`card`,`paypal`,`bank_transfer`) | |
| `stripe_payment_id` | VAR(255) | nullable |
| `status` | ENUM(`pending`,`processing`,`shipped`,`delivered`,`cancelled`) | |
| `created_at` | TS | |

---

### `order_status_history` *(1 : N con orders)*

| Columna | Tipo | Notas |
|---|---|---|
| `id` | BIG | PK |
| `order_id` | BIG | FK → orders |
| `status` | ENUM (mismo que `orders.status`) | |
| `note` | TXT | nullable |
| `created_at` | TS | |

---

## Zona D — Lugares (Places, Reseñas & Favoritos)

### `petfriendly_places`

| Columna | Tipo | Notas |
|---|---|---|
| `id` | BIG | PK |
| `created_by` | BIG | FK → users |
| `name` | VAR(150) | |
| `category` | ENUM(`pipican`,`beach`,`park`,`vet`,`shop`,`restaurant`,`hotel`) | |
| `description` | TXT | nullable |
| `lat` | DEC(10,7) | |
| `lng` | DEC(10,7) | |
| `address` | VAR(255) | |
| `phone` | VAR(30) | nullable |
| `website` | VAR(255) | nullable |
| `photo` | VAR(255) | nullable |
| `status` | ENUM(`pending`,`approved`,`rejected`) | Por defecto `pending` |

---

### `place_reviews` *(1 : N con petfriendly_places)*

| Columna | Tipo | Notas |
|---|---|---|
| `id` | BIG | PK |
| `place_id` | BIG | FK → petfriendly_places |
| `user_id` | BIG | FK → users |
| `rating` | INT | 1–5 |
| `body` | TXT | nullable |
| `photo` | VAR(255) | nullable |
| `created_at` | TS | |

---

### `place_favorites` *(pivote N : N entre users y petfriendly_places)*

| Columna | Tipo | Notas |
|---|---|---|
| `user_id` | BIG | FK → users |
| `place_id` | BIG | FK → petfriendly_places |

---

## Mapa Global de Relaciones

| Tabla origen | Cardinalidad | Tabla destino | FK / Pivote |
|---|:---:|---|---|
| `users` | 1 : N | `pets` | `pets.user_id` |
| `users` | 1 : N | `walks` | `walks.creator_id` |
| `users` | 1 : N | `orders` | `orders.user_id` |
| `users` | 1 : N | `place_reviews` | `place_reviews.user_id` |
| `users` | 1 : N | `messages` (sender) | `messages.sender_id` |
| `users` | N : N | `petfriendly_places` | `place_favorites` (pivote) |
| `pets` | 1 : N | `pet_photos` | `pet_photos.pet_id` |
| `pets` | 1 : N | `health_records` | `health_records.pet_id` |
| `pets` | 1 : N | `orders` | `orders.pet_id` |
| `pets` (canina) | N : N | `pets` (canina) | `pet_swipes` (self-ref) |
| `pets` (canina) | N : N | `matches` | `pet_a_id` / `pet_b_id` |
| `pets` | N : N | `walks` | `walk_pet` (pivote) |
| `matches` | 1 : N | `messages` | `messages.match_id` |
| `providers` | 1 : N | `products` | `products.provider_id` |
| `products` | 1 : N | `orders` | `orders.product_id` |
| `orders` | 1 : N | `order_status_history` | `order_status_history.order_id` |
| `petfriendly_places` | 1 : N | `place_reviews` | `place_reviews.place_id` |
| `petfriendly_places` | N : N | `users` | `place_favorites` (pivote) |

### Tablas pivote

| Pivote | Tablas que une |
|---|---|
| `walk_pet` | `walks` + `pets` |
| `pet_swipes` | `pets` + `pets` (self-referencing, caninos) |
| `place_favorites` | `users` + `petfriendly_places` |

---

## Notas de Diseño

### 1. Campo `species` en `pets`
- `species = dog` → habilita match y disponibilidad para paseos.
- Resto de especies → solo módulo sanitario y marketplace activos en V1.

### 2. `next_due_date` en `health_records`
Campo calculado y guardado en DB: `last_date + periodicity_days`. El Scheduler de Laravel lo consulta diariamente para disparar notificaciones.

### 3. `pet_swipes` (self-referencing)
`swiper_pet_id` y `swiped_pet_id` apuntan ambos a `pets.id`. El sistema comprueba si existe el registro inverso con `action = like` para generar el match.

### 4. `orders.pet_id`
Permite al `RecommendationService` personalizar productos por mascota y cruzar compras previas con próximas necesidades sanitarias.

### 5. Soft Deletes
Se recomienda añadir `deleted_at (TIMESTAMP nullable)` a `users`, `pets`, `orders` y `petfriendly_places`.

### 6. Índices recomendados

| Tabla | Índices |
|---|---|
| `pets` | `(user_id)`, `(species)` |
| `health_records` | `(pet_id, next_due_date)`, `(notifications_active)` |
| `matches` | `(pet_a_id, pet_b_id)`, `(status)` |
| `messages` | `(match_id, created_at)` |
| `orders` | `(user_id, status)`, `(created_at)` |
| `petfriendly_places` | `(lat, lng)`, `(category)` |
| `place_reviews` | `(place_id, created_at)` |
