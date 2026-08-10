# 🐾 PawConnect — Plan de Ejecución del Proyecto

**Versión Web con Laravel 11** · Última revisión: Julio 2026

---

## Resumen Ejecutivo

PawConnect es una plataforma web construida en Laravel 11 que cubre 5 pilares: **Social, Sanitario, Marketplace, Geolocalización y Administración**.

### Alcance funcional

| Módulo | Alcance |
|---|---|
| Social (match y paseos) | Target principal: **perros** |
| Sanitario | **Multiespecie** desde V1 |
| Marketplace | **Multiespecie** desde V1 |
| Geolocalización | Todos |
| Administración | Todos |

> **Regla de negocio:** las funciones sociales pueden limitarse a especie canina en la primera versión, pero sanitario y marketplace son multiespecie desde el inicio.

El proyecto se divide en **9 fases (~19 semanas)**. Cada fase tiene tareas concretas, comandos, archivos a crear y un criterio de aceptación (✅) que confirma que está terminada antes de pasar a la siguiente.

### Servicios externos elegidos

| Servicio | Tecnología |
|---|---|
| Chat en tiempo real | Laravel Reverb (incluido en Laravel) |
| Almacenamiento fotos | Laravel Storage (local / S3) |
| Mapas | Leaflet.js (gratuito, sin clave API) |
| Pagos | Stripe (mejor SDK PHP) |
| Documentación API | Scribe (más moderno que L5-Swagger) |
| Tests | Pest (sintaxis más clara que PHPUnit) |

---

## Estructura de Carpetas Objetivo

```
app/
  Http/
    Controllers/
      Api/          ← Controllers de la API REST
      Web/          ← Controllers del frontend Blade
    Requests/       ← Form Requests (validaciones)
    Resources/      ← API Resources (transformadores JSON)
  Models/           ← Eloquent Models
  Services/         ← Lógica de negocio (no en controllers)
  Repositories/     ← Acceso a datos (abstracción sobre Eloquent)
  Jobs/             ← Tareas asíncronas (notificaciones sanitarias)
  Events/           ← Eventos del dominio
  Listeners/        ← Respuestas a eventos
  Policies/         ← Autorización por recurso
  Notifications/    ← Notificaciones (mail, base de datos, push)

database/
  migrations/
  seeders/
  factories/

resources/
  views/
    layouts/        ← app.blade.php, admin.blade.php
    components/
    auth/
    dashboard/
    pets/
    canines/
    matches/
    chat/
    walks/
    health/
    marketplace/
    places/
    admin/

routes/
  web.php           ← Rutas Blade
  api.php           ← Rutas API v1

tests/
  Feature/
  Unit/
```

---

## Fase 1 — Documentación y Diseño *(Semanas 1–2)*

**Objetivo:** tener todo diseñado en papel antes de escribir código.

- [x] **1.1** Redactar el SRS (Software Requirements Specification)
  - Actores: Usuario, Administrador, Sistema (Scheduler)
  - Casos de uso por módulo
  - Requisitos funcionales y no funcionales

- [x] **1.2** Diseñar el Diagrama Entidad-Relación (ER)

  | Tabla | Columnas principales |
  |---|---|
  | `users` | id, name, email, password, role, photo, lat, lng, address, billing_address, created_at |
  | `pets` | id, user_id, species, name, breed, age, weight, size, energy_level, personality, available_for_walks, created_at |
  | `pet_photos` | id, pet_id, path, order |
  | `health_records` | id, pet_id, type, last_date, periodicity_days, notes, notifications_active, next_due_date |
  | `matches` | id, pet_a_id, pet_b_id, status, matched_at |
  | `messages` | id, match_id, sender_id, body, read_at, created_at |
  | `walks` | id, creator_id, title, description, lat, lng, scheduled_at, max_pets |
  | `walk_pet` | walk_id, pet_id *(pivote)* |
  | `products` | id, provider_id, name, category, description, price, stock, min_weight_kg, max_weight_kg, recommended_breeds, photo |
  | `orders` | id, user_id, product_id, pet_id, quantity, total_price, shipping_address, payment_method, stripe_payment_id, status, created_at |
  | `order_status_history` | id, order_id, status, note, created_at |
  | `providers` | id, name, email, phone, address |
  | `petfriendly_places` | id, name, category, description, lat, lng, address, phone, website, photo, status, created_by |
  | `place_reviews` | id, place_id, user_id, rating, body, photo, created_at |
  | `place_favorites` | user_id, place_id *(pivote)* |

- [x] **1.3** Crear diagrama UML de clases *(ver `UML_clases.md`)*

- [x] **1.4** Definir la API REST *(ver `API_endpoints.md`)*

  Base: `/api/v1/`

  | Grupo | Endpoints principales |
  |---|---|
  | AUTH | POST /auth/register, login, logout, forgot-password, reset-password |
  | USERS | GET/PUT/DELETE /users/me |
  | PETS | CRUD /pets, /pets/{id} |
  | CANINES | GET /canines, /canines/{id} *(social, solo dogs)* |
  | HEALTH | CRUD /pets/{id}/health/{hid} |
  | MATCHES | GET /matches/candidates, like/{petId}, pass/{petId}, GET /matches |
  | MESSAGES | GET/POST /matches/{id}/messages |
  | WALKS | CRUD /walks, join, leave |
  | PRODUCTS | GET /products, /products/recommended/{petId}, /products/{id} |
  | ORDERS | POST/GET /orders, GET /orders/{id} |
  | PLACES | CRUD /places, reviews, favorites |
  | ADMIN | /admin/* (solo rol admin) |

- [ ] **1.5** Crear wireframes básicos de las vistas principales
  - Landing page
  - Dashboard del usuario
  - Swipe de match
  - Chat
  - Historial sanitario
  - Marketplace
  - Mapa de lugares
  - Panel admin

> ✅ **Criterio de aceptación:** el ER está completo y revisado, los endpoints aprobados y los wireframes bocetados.

---

## Fase 2 — Backend Base *(Semanas 3–5)*

**Objetivo:** proyecto Laravel funcional con base de datos, autenticación y CRUD de usuarios y mascotas.

- [x] **2.1** Instalar Laravel y dependencias base

  ```bash
  composer create-project laravel/laravel:^11.0 pawconnect
  cd pawconnect
  composer require laravel/sanctum
  composer require knuckleswtf/scribe --dev
  npm install && npm install tailwindcss @tailwindcss/vite
  ```

- [x] **2.2** Configurar `.env`

  ```env
  DB_DATABASE=pawconnect
  DB_USERNAME=pawconnect
  DB_PASSWORD=pawconnect
  BROADCAST_DRIVER=reverb
  FILESYSTEM_DISK=local
  SANCTUM_STATEFUL_DOMAINS=localhost:8000,127.0.0.1:8000
  ```

- [x] **2.3** Crear migraciones *(respetar orden de FKs)*

  ```bash
  php artisan make:migration add_fields_to_users_table
  php artisan make:migration create_pets_table
  php artisan make:migration create_pet_photos_table
  php artisan make:migration create_health_records_table
  php artisan make:migration create_matches_table
  php artisan make:migration create_messages_table
  php artisan make:migration create_walks_table
  php artisan make:migration create_walk_pet_table
  php artisan make:migration create_providers_table
  php artisan make:migration create_products_table
  php artisan make:migration create_orders_table
  php artisan make:migration create_order_status_history_table
  php artisan make:migration create_petfriendly_places_table
  php artisan make:migration create_place_reviews_table
  php artisan make:migration create_place_favorites_table
  ```

- [x] **2.4** Crear modelos con relaciones

  ```bash
  php artisan make:model Pet -mfR
  php artisan make:model HealthRecord -f
  # Match es palabra reservada en PHP; usar PetMatch para la tabla matches
  php artisan make:model PetMatch -f
  php artisan make:model Message -f
  php artisan make:model Walk -f
  php artisan make:model Product -f
  php artisan make:model Order -f
  php artisan make:model Provider -f
  php artisan make:model PetFriendlyPlace -f
  php artisan make:model PlaceReview -f
  ```

  | Modelo | Relaciones clave |
  |---|---|
  | `User` | hasMany Pets, Orders, PlaceReviews |
  | `Pet` | belongsTo User, hasMany HealthRecords, belongsToMany Walks/Matches |
  | `PetMatch` | hasMany Messages |
  | `Order` | hasMany OrderStatusHistory, belongsTo Provider |

- [x] **2.5** Implementar autenticación completa

  ```bash
  php artisan install:api
  php artisan make:controller Api/AuthController
  ```

  - `register`: validar email único, hashear contraseña, devolver token
  - `login`: verificar credenciales, devolver token
  - `logout`: revocar token actual
  - `forgotPassword / resetPassword`: usar built-in de Laravel

- [x] **2.6** Sistema de roles

  ```bash
  php artisan make:policy PetPolicy
  php artisan make:policy OrderPolicy
  ```

  - Añadir columna `role` a `users` (enum: `user`, `admin`)
  - Crear middleware `IsAdmin`

- [x] **2.7** CRUD Usuarios y Mascotas

  ```bash
  php artisan make:controller Api/UserController
  php artisan make:controller Api/PetController
  php artisan make:request StorePetRequest
  php artisan make:request UpdatePetRequest
  ```

- [x] **2.8** Subida de fotos

  ```bash
  php artisan storage:link
  ```

  - Usar Laravel Storage con disco `public`
  - Crear `StorageService` para centralizar la lógica

- [x] **2.9** Seeders y Factories

  ```bash
  php artisan make:seeder UserSeeder
  php artisan make:seeder PetSeeder
  php artisan make:seeder ProductSeeder
  php artisan make:seeder PetFriendlyPlaceSeeder
  ```

  Mínimo: 10 usuarios, 20 mascotas, 30 productos, 15 lugares.

- [x] **2.10** Ejecutar y verificar

  ```bash
  php artisan migrate --seed
  php artisan test
  ```

> ✅ **Criterio de aceptación:** `migrate:fresh --seed` sin errores, login devuelve token, CRUD de mascotas autenticado, Policies funcionan.

---

## Fase 3 — API REST Completa *(Semanas 6–7)*

**Objetivo:** todos los endpoints implementados, documentados y con tests.

- [x] **3.1** Crear controllers API restantes

  ```bash
  php artisan make:controller Api/HealthController
  php artisan make:controller Api/MatchController
  php artisan make:controller Api/MessageController
  php artisan make:controller Api/WalkController
  php artisan make:controller Api/ProductController
  php artisan make:controller Api/OrderController
  php artisan make:controller Api/PlaceController
  ```

- [x] **3.2** Crear API Resources

  ```bash
  php artisan make:resource PetResource
  php artisan make:resource HealthRecordResource
  php artisan make:resource MatchResource
  php artisan make:resource MessageResource
  php artisan make:resource WalkResource
  php artisan make:resource ProductResource
  php artisan make:resource OrderResource
  php artisan make:resource PlaceResource
  ```

  > Siempre devolver datos a través de Resources, nunca el modelo directo.

- [x] **3.3** Definir rutas en `routes/api.php`

  ```php
  Route::prefix('v1')->group(function () {
      Route::post('/auth/register', ...);
      // ...
      Route::middleware('auth:sanctum')->group(function () {
          // rutas protegidas
          Route::middleware('is_admin')->prefix('admin')->group(...);
      });
  });
  ```

- [x] **3.4** Implementar `ApiResponse` trait

  Métodos: `success($data, $message, $code = 200)` y `error($message, $code = 400)`.

- [x] **3.5** Configurar Scribe

  ```bash
  php artisan vendor:publish --tag=scribe-config
  php artisan scribe:generate
  ```

  Expone la documentación interactiva en `/docs` y genera colección Postman + especificación OpenAPI.

- [x] **3.6** Escribir tests con Pest

  - `tests/Feature/AuthTest.php` — 6 tests (registro, login, logout)
  - `tests/Feature/PetTest.php` — 9 tests (CRUD, autorización, validaciones)
  - `tests/Feature/HealthTest.php` — 12 tests (registros sanitarios)
  - `tests/Feature/OrderTest.php` — 11 tests (pedidos y validaciones)

  ```bash
  php artisan test  # Ejecutar todos los tests
  ```

- [x] **3.7** Configurar Rate Limiting

  ```php
  RateLimiter::for('api', fn ($req) =>
      Limit::perMinute(60)->by($req->user()?->id ?: $req->ip())
  );
  ```

> ✅ **Criterio de aceptación:** `php artisan test` en verde, `/docs` con todos los endpoints, Postman collection funcionando.

---

## Fase 4 — Frontend Web *(Semanas 8–10)*

**Objetivo:** interfaz web usable, responsive, que consume la API.

- [x] **4.1** Configurar TailwindCSS con Vite

  ```bash
  npm install -D tailwindcss @tailwindcss/forms @tailwindcss/typography
  npm run dev
  ```

- [x] **4.2** Crear layouts Blade
  - `layouts/app.blade.php` — layout principal con nav y sidebar
  - `layouts/admin.blade.php` — layout del panel admin
  - `layouts/auth.blade.php` — layout limpio para login/register

- [x] **4.3** Crear componentes Blade reutilizables
  - `components/pet-card.blade.php`
  - `components/health-badge.blade.php`
  - `components/alert.blade.php`
  - `components/modal.blade.php`
  - `components/map.blade.php` — componente Leaflet genérico

- [x] **4.4** Vistas a crear
  - `auth/login.blade.php`, `register.blade.php`, `forgot-password.blade.php`
  - `dashboard/index.blade.php`
  - `pets/index.blade.php`, `create.blade.php`, `show.blade.php`, `edit.blade.php`
  - `profile/edit.blade.php`

- [x] **4.5** Crear Web Controllers

  ```bash
  php artisan make:controller Web/DashboardController
  php artisan make:controller Web/PetController
  php artisan make:controller Web/ProfileController
  ```

- [x] **4.6** Formularios
  - Validación server-side con Form Requests
  - Errores con `@error` en Blade
  - `@csrf` en todos los formularios
  - Previsualización de fotos antes de subir (JS puro)

- [x] **4.7** JavaScript (Fetch API)
  - `resources/js/api.js` con wrapper para fetch + auth token
  - Sin recarga de página para: guardar favorito, enviar mensaje, unirse a paseo
  - Módulos: `favorites.js`, `messages.js`, `walks.js`
  - Componentes Blade: `favorite-button`, `message-form`, `join-walk-button`
  - Documentación en `guia-fetch-api.md`

- [x] **4.8** Responsive design
  - Probar en 375px (móvil), 768px (tablet), 1280px (escritorio)
  - Menú hamburguesa en móvil (ya implementado en layout app)
  - Componentes responsive: botones, formularios, layout
  - Documentación en `guia-responsive-design.md`
  - Mobile-First approach con TailwindCSS breakpoints
  - Padding/gap/fuentes escalables en todas las vistas

> ✅ **Criterio de aceptación:** flujo registro → login → crear mascota → ver dashboard. Sin errores JS. Responsive en móvil (375px) y escritorio (1280px). Menú hamburguesa funciona.

---

## Fase 5 — Match, Chat y Paseos *(Semanas 11–12)*

**Objetivo:** las tres funciones sociales principales operativas.

### Match

- [x] **5.1** Crear `MatchService` con algoritmo de candidatos
  - Excluir mascotas caninas del propio usuario
  - Excluir candidatas ya vistas (likes + passes)
  - Filtrar por distancia (Haversine con lat/lng)
  - Ordenar por tamaño compatible, nivel energía, disponibilidad

- [x] **5.2** Implementar acciones like/pass
  - Tabla `pet_swipes (swiper_pet_id, swiped_pet_id, action)`
  - Si ambas dan like → crear `matches` + evento `MatchCreated`

- [x] **5.3** Vista swipe
  - Tarjeta con fotos, nombre, raza, energía
  - Botones Like ❤️ y Pass ✗
  - Animación CSS de deslizamiento
  - AJAX a `POST /api/v1/matches/like/{petId}`

### Chat

- [x] **5.4** Instalar Laravel Reverb

  ```bash
  php artisan install:broadcasting
  npm install --save-dev laravel-echo pusher-js
  ```

- [x] **5.5** Canal privado `private-match.{matchId}` con evento `MessageSent`

- [x] **5.6** Vista del chat
  - Lista de conversaciones en sidebar
  - Scroll automático al último mensaje
  - Indicador "escribiendo…" (evento `client-typing`)
  - Marcar como leído al abrir

### Paseos

- [x] **5.7** CRUD de paseos (`WalkController`)
  - Crear paseo: título, descripción, ubicación, fecha, max mascotas caninas
  - Listar cercanos, unirse/abandonar, ver participantes

- [x] **5.8** Mapa con Leaflet.js
  - Marcador por paseo, popup con botón "Unirse"
  - Geolocalización del usuario para centrar

> ✅ **Criterio de aceptación:** swipe genera match, mensaje en tiempo real, paseo aparece en mapa.

---

## Fase 6 — Módulo Sanitario + Marketplace *(Semanas 13–15)*

### Sanitario

- [x] **6.1** CRUD sanitario (`HealthController`)
  - `next_due_date = last_date + periodicity_days`

- [x] **6.2** Vista historial sanitario
  - Timeline por mascota
  - Semáforo verde / amarillo / rojo
  - Botón "Renovar" → `last_date = hoy`

- [x] **6.3** Notificaciones automáticas con Scheduler

  ```bash
  php artisan make:command SendHealthReminders
  php artisan make:job ProcessHealthReminder
  php artisan make:notification HealthReminderNotification
  ```

  - Scheduler diario: `$schedule->command('health:reminders')->daily()`
  - Busca registros con `next_due_date <= now() + 3 días` y `notifications_active = true`

- [x] **6.4** Campana de notificaciones en navbar
  - `GET /api/v1/notifications` (no leídas)
  - Badge con contador

### Marketplace

- [x] **6.5** CRUD de productos (solo admin)

- [x] **6.6** `RecommendationService`
  - Input: `Pet $pet`
  - Filtra por especie, peso, raza, edad y cruza con historial sanitario

- [x] **6.7** Vista marketplace
  - Sección "Recomendado para {mascota}"
  - Filtros: categoría, precio, raza

- [x] **6.8** Flujo de compra

  ```json
  POST /api/v1/orders
  { "product_id": 31, "pet_id": 10, "quantity": 2,
    "shipping_address": "...", "payment_method": "card" }
  ```

  1. Crear `PaymentIntent` en Stripe
  2. Confirmar pago desde frontend (Stripe.js)
  3. Si OK → crear `Order` + `OrderStatusHistory('pending')`

- [ ] **6.9** Página "Mis Pedidos" con timeline de estado y tracking

> ✅ **Criterio de aceptación:** Scheduler genera notificaciones, recomendación relevante, pago Stripe en modo test.

---

## Fase 7 — Módulo Pet-Friendly *(Semanas 16–17)*

- [ ] **7.1** CRUD de lugares
  - Categorías: `pipican, beach, park, vet, shop, restaurant, hotel`
  - Usuarios sugieren → status: `pending` → admin aprueba/rechaza

- [ ] **7.2** Mapa interactivo con Leaflet.js
  - Icono diferente por categoría
  - Popup con botones "Ver detalle" y "Guardar favorito"
  - Geolocalización del usuario

- [ ] **7.3** Ficha del lugar
  - Galería, descripción, contacto, mapa embebido
  - Reseñas (rating 1–5 + comentario)

- [ ] **7.4** Favoritos
  - Botón corazón + vista "Mis Lugares Favoritos"

- [ ] **7.5** Integración con módulo sanitario
  - En registro próximo a vencer → mostrar veterinarios cercanos

- [ ] **7.6** Integración con marketplace
  - En ficha de producto → mostrar "Dónde comprarlo cerca"

> ✅ **Criterio de aceptación:** mapa con filtros, reseña visible, veterinarios cercanos en sanitario.

---

## Fase 8 — Panel de Administración *(Semana 18)*

- [ ] **8.1** Dashboard de estadísticas
  - Total usuarios, mascotas, matches, pedidos, ingresos
  - Gráficas con Chart.js

- [ ] **8.2** Gestión de Usuarios — listar, editar rol, bloquear

- [ ] **8.3** Gestión de Mascotas — buscar por nombre/especie/raza/dueño

- [ ] **8.4** Gestión de Productos y Proveedores — CRUD completo

- [ ] **8.5** Gestión de Pedidos — cambiar estado + historial + URL tracking

- [ ] **8.6** Gestión de Lugares — aprobar/rechazar sugerencias

- [ ] **8.7** Moderación de Reseñas

- [ ] **8.8** Exportar datos a CSV

  ```bash
  composer require maatwebsite/excel
  ```

> ✅ **Criterio de aceptación:** admin gestiona todo sin BBDD directa, cambio de estado registrado, CSV descargable.

---

## Fase 9 — Preparación Multiplataforma y Cierre *(Semana 19)*

- [ ] **9.1** Revisar y limpiar la API REST
  - Respuestas con `ApiResponse` trait
  - Paginación consistente (`LengthAwarePaginator`)
  - Sin stack traces en producción

- [ ] **9.2** Checklist OWASP
  - CSRF activo en `web.php`
  - Headers de seguridad (`Content-Security-Policy`, `X-Frame-Options`)
  - Rate limiting configurado
  - Tokens Sanctum con expiración
  - `APP_DEBUG=false` en producción

- [ ] **9.3** Configurar CORS
  - `config/cors.php` → `allowed_origins` para cliente móvil

- [ ] **9.4** Documentación final

  ```bash
  php artisan scribe:generate
  ```

  - Exportar Postman Collection
  - `README.md` con requisitos, instalación, `.env`, comandos artisan

- [ ] **9.5** Propuesta técnica para app móvil (Flutter / React Native)

- [ ] **9.6** Despliegue *(opcional)*
  - VPS / Laravel Forge / Railway
  - HTTPS con Let's Encrypt
  - Supervisor para workers + cron para Scheduler

> ✅ **Criterio de aceptación:** tests 100% verde, `/docs` completo, OWASP revisado, README funcional en < 15 min.

---

## Tabla Resumen de Fases

| Fase | Semanas | Entregable principal |
|:---:|:---:|---|
| 1 | 1–2 | SRS + ER + endpoints + wireframes |
| 2 | 3–5 | Laravel funcional, BBDD, auth, CRUD base |
| 3 | 6–7 | API completa, documentada y con tests |
| 4 | 8–10 | Frontend Blade responsive funcional |
| 5 | 11–12 | Match, chat en tiempo real, paseos |
| 6 | 13–15 | Sanitario con Scheduler + Marketplace + Stripe |
| 7 | 16–17 | Lugares pet-friendly con mapa y reseñas |
| 8 | 18 | Panel de administración completo |
| 9 | 19 | API limpia, seguridad revisada, docs, deploy |

---

## Dependencias entre Fases

```
Fase 1 → prerequisito de Fase 2
Fase 2 → prerequisito de Fase 3
Fase 4 puede arrancar con Fase 3 parcial (auth + pets)
Fase 5 → Fase 3 completa + Fase 4 parcial
Fase 6 → Fase 2 + Fase 3 + Fase 4
Fase 7 → Fase 6
Fase 8 → todas las anteriores
Fase 9 → Fase 8 completa
```

---

## Paquetes a Instalar

### Composer (producción)

```bash
composer require laravel/sanctum
composer require stripe/stripe-php
composer require knuckleswtf/scribe
composer require spatie/laravel-permission   # opcional
composer require maatwebsite/excel
```

### Composer (desarrollo)

```bash
composer require pestphp/pest --dev
composer require pestphp/pest-plugin-laravel --dev
composer require barryvdh/laravel-debugbar --dev
```

### NPM

```bash
npm install tailwindcss @tailwindcss/forms laravel-echo pusher-js chart.js leaflet
```

---

## Comandos Artisan de Referencia Rápida

```bash
php artisan migrate:fresh --seed    # resetear BBDD con datos de prueba
php artisan test                    # ejecutar tests
php artisan scribe:generate         # regenerar docs API
php artisan queue:work              # procesar jobs en desarrollo
php artisan health:reminders        # ejecutar recordatorios sanitarios manualmente
```
