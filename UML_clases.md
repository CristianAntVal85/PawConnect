# 🐾 PawConnect — Diagrama UML de Clases

**Versión Web con Laravel 11** · Última revisión: Julio 2026

---

## Leyenda de Notación UML

| Símbolo | Significado |
|---|---|
| `+` | público |
| `-` | privado |
| `#` | protegido |
| `◆────` | Composición (el hijo no existe sin el padre) |
| `◇────` | Agregación (el hijo puede existir independientemente) |
| `────▷` | Herencia / Generalización |
| `· · · ▷` | Dependencia / uso puntual |

| Multiplicidad | Significado |
|---|---|
| `1` | exactamente uno |
| `*` | cero o más |
| `0..1` | cero o uno |
| `1..*` | uno o más |

---

## Zona A — Núcleo (User & Pet)

### `User` *(Model)*

| Sección | Miembro |
|---|---|
| **Atributos** | `+ id: int` |
| | `+ name: str` |
| | `+ email: str [unique]` |
| | `- password: str` |
| | `+ role: str [user\|admin]` |
| | `+ photo: str\|null` |
| | `+ lat: flt\|null` |
| | `+ lng: flt\|null` |
| | `+ address: str\|null` |
| | `+ billing_address: str\|null` |
| | `+ created_at: dt` |
| **Relaciones** | `+ pets(): HasMany → Pet` ◆ |
| | `+ walks(): HasMany → Walk` ◆ |
| | `+ orders(): HasMany → Order` ◆ |
| | `+ placeReviews(): HasMany → PlaceReview` ◆ |
| | `+ favoritePlaces(): BelongsToMany → PetFriendlyPlace` ◇ |
| | `+ messages(): HasManyThrough → Message` |
| **Métodos** | `+ isAdmin(): bool` |
| | `+ getFullAddressAttr(): str` |

---

### `Pet` *(Model)* — `N..*` con User

| Sección | Miembro |
|---|---|
| **Atributos** | `+ id: int` |
| | `+ user_id: int [FK]` |
| | `+ species: str [dog\|cat\|bird\|rodent\|other]` |
| | `+ name: str` |
| | `+ breed: str\|null` |
| | `+ age: int\|null` |
| | `+ weight: flt\|null` |
| | `+ size: str\|null [small\|medium\|large\|xlarge]` |
| | `+ energy_level: str\|null [low\|medium\|high]` |
| | `+ personality: str\|null` |
| | `+ available_for_walks: bool` |
| | `+ created_at: dt` |
| **Relaciones** | `+ user(): BelongsTo → User` |
| | `+ photos(): HasMany → PetPhoto` ◆ |
| | `+ healthRecords(): HasMany → HealthRecord` ◆ |
| | `+ walks(): BelongsToMany → Walk` ◇ |
| | `+ matchesAsA(): HasMany → Match` ◇ |
| | `+ matchesAsB(): HasMany → Match` ◇ |
| | `+ orders(): HasMany → Order` ◆ |
| | `+ swipesMade(): HasMany → PetSwipe` ◆ |
| **Métodos** | `+ scopeCanine(): Builder` |
| | `+ scopeAvailable(): Builder` |
| | `+ isCanine(): bool` |

---

### `PetPhoto` *(Model)* — `1..*` con Pet

| Sección | Miembro |
|---|---|
| **Atributos** | `+ id: int` · `+ pet_id: int [FK]` · `+ path: str` · `+ order: int` |
| **Relaciones** | `+ pet(): BelongsTo → Pet` |
| **Métodos** | `+ getUrl(): str` |

---

### `HealthRecord` *(Model)* — `1..*` con Pet

| Sección | Miembro |
|---|---|
| **Atributos** | `+ id: int` |
| | `+ pet_id: int [FK]` |
| | `+ type: str [vaccine\|dewormer\|pipette\|collar\|vet_visit\|food]` |
| | `+ last_date: dt` |
| | `+ periodicity_days: int` |
| | `+ notes: str\|null` |
| | `+ notifications_active: bool` |
| | `+ next_due_date: dt` ← auto-calculado al save |
| **Relaciones** | `+ pet(): BelongsTo → Pet` |
| **Métodos** | `+ isOverdue(): bool` |
| | `+ isDueSoon(): bool` |
| | `+ getDaysUntilDue(): int` |
| | `# calculateNextDue(): dt` |

---

### `Walk` *(Model)*

| Sección | Miembro |
|---|---|
| **Atributos** | `+ id: int` · `+ creator_id: int [FK → User]` · `+ title: str` · `+ description: str\|null` · `+ lat: flt` · `+ lng: flt` · `+ scheduled_at: dt` · `+ max_pets: int` |
| **Relaciones** | `+ creator(): BelongsTo → User` |
| | `+ pets(): BelongsToMany → Pet` |
| **Métodos** | `+ isFull(): bool` · `+ scopeUpcoming(): Builder` · `+ scopeNearby(): Builder` |

---

## Zona B — Social (Match, PetSwipe, Message)

```
Pet ◇─── 1 ──────────── * ───── PetSwipe
         (swiper / swiped — self-join caninos)
```

### `PetSwipe` *(Model)*

| Sección | Miembro |
|---|---|
| **Atributos** | `+ swiper_pet_id: int [FK → Pet]` · `+ swiped_pet_id: int [FK → Pet]` · `+ action: str [like\|pass]` · `+ created_at: dt` |
| **Relaciones** | `+ swiperPet(): BelongsTo → Pet` · `+ swipedPet(): BelongsTo → Pet` |

> Si `action = like` en ambos sentidos → genera `Match`.

---

### `Match` *(Model)*

| Sección | Miembro |
|---|---|
| **Atributos** | `+ id: int` · `+ pet_a_id: int [FK → Pet]` · `+ pet_b_id: int [FK → Pet]` · `+ status: str [pending\|matched\|unmatched]` · `+ matched_at: dt\|null` |
| **Relaciones** | `+ petA(): BelongsTo → Pet` · `+ petB(): BelongsTo → Pet` · `+ messages(): HasMany → Message` ◆ |
| **Métodos** | `+ isMatched(): bool` · `+ getOtherPet(Pet $pet): Pet` · `+ scopeActive(): Builder` |

---

### `Message` *(Model)* — `1..*` con Match

| Sección | Miembro |
|---|---|
| **Atributos** | `+ id: int` · `+ match_id: int [FK]` · `+ sender_id: int [FK → User]` · `+ body: str` · `+ read_at: dt\|null` · `+ created_at: dt` |
| **Relaciones** | `+ match(): BelongsTo → Match` · `+ sender(): BelongsTo → User` |
| **Métodos** | `+ markAsRead(): void` · `+ isRead(): bool` |

---

## Zona C — Comercio (Provider, Product, Order, OrderStatusHistory)

### `Provider` *(Model)*

| Sección | Miembro |
|---|---|
| **Atributos** | `+ id: int` · `+ name: str` · `+ email: str` · `+ phone: str\|null` · `+ address: str\|null` |
| **Relaciones** | `+ products(): HasMany → Product` ◆ · `+ orders(): HasManyThrough → Order` |

---

### `Product` *(Model)* — `1..*` con Provider

| Sección | Miembro |
|---|---|
| **Atributos** | `+ id: int` · `+ provider_id: int [FK]` · `+ name: str` · `+ category: str [food\|dewormer\|pipette\|collar\|supplement\|other]` · `+ description: str\|null` · `+ price: flt` · `+ stock: int` · `+ min_weight_kg: flt\|null` · `+ max_weight_kg: flt\|null` · `+ recommended_breeds: str\|null` · `+ photo: str\|null` |
| **Relaciones** | `+ provider(): BelongsTo → Provider` · `+ orders(): HasMany → Order` |
| **Métodos** | `+ isInStock(): bool` · `+ scopeForPet(Pet): Builder` |

---

### `Order` *(Model)*

| Sección | Miembro |
|---|---|
| **Atributos** | `+ id: int` · `+ user_id: int [FK → User]` · `+ product_id: int [FK → Product]` · `+ pet_id: int [FK → Pet]` · `+ quantity: int` · `+ total_price: flt` · `+ shipping_address: str` · `+ payment_method: str [card\|paypal\|bank_transfer]` · `+ stripe_payment_id: str\|null` · `+ status: str [pending\|processing\|shipped\|delivered\|cancelled]` · `+ created_at: dt` |
| **Relaciones** | `+ user(): BelongsTo → User` · `+ product(): BelongsTo → Product` · `+ pet(): BelongsTo → Pet` · `+ statusHistory(): HasMany → OrderStatusHistory` ◆ |
| **Métodos** | `+ updateStatus(str): void` · `+ isPending(): bool` · `+ scopeByStatus(str): Builder` |

---

### `OrderStatusHistory` *(Model)*

| Sección | Miembro |
|---|---|
| **Atributos** | `+ id: int` · `+ order_id: int [FK → Order]` · `+ status: str` · `+ note: str\|null` · `+ created_at: dt` |
| **Relaciones** | `+ order(): BelongsTo → Order` |

---

## Zona D — Lugares (PetFriendlyPlace, PlaceReview, place_favorites)

### `PetFriendlyPlace` *(Model)*

| Sección | Miembro |
|---|---|
| **Atributos** | `+ id: int` · `+ created_by: int [FK → User]` · `+ name: str` · `+ category: str [pipican\|beach\|park\|vet\|shop\|restaurant\|hotel]` · `+ description: str\|null` · `+ lat: flt` · `+ lng: flt` · `+ address: str` · `+ phone: str\|null` · `+ website: str\|null` · `+ photo: str\|null` · `+ status: str [pending\|approved\|rejected]` |
| **Relaciones** | `+ creator(): BelongsTo → User` · `+ reviews(): HasMany → PlaceReview` ◆ · `+ favoritedByUsers(): BelongsToMany → User` ◇ |
| **Métodos** | `+ avgRating(): flt` · `+ scopeByCategory(): Builder` · `+ scopeNearby(): Builder` |

---

### `PlaceReview` *(Model)*

| Sección | Miembro |
|---|---|
| **Atributos** | `+ id: int` · `+ place_id: int [FK → PetFriendlyPlace]` · `+ user_id: int [FK → User]` · `+ rating: int (1–5)` · `+ body: str\|null` · `+ photo: str\|null` · `+ created_at: dt` |
| **Relaciones** | `+ place(): BelongsTo → PetFriendlyPlace` · `+ user(): BelongsTo → User` |

---

### `place_favorites` *(Pivot Model)*

| Columna | FK |
|---|---|
| `user_id` | → User |
| `place_id` | → PetFriendlyPlace |

---

## Zona E — Capa de Servicios

> Los Services encapsulan lógica de negocio. Los Controllers los inyectan vía DI. No extienden modelos Eloquent. Se registran en `AppServiceProvider`.

### `MatchService` *(Service)*

| Dependencias | `PetRepository`, `HaversineHelper` |
|---|---|
| `+ getCandidates(Pet $pet): Collection` | Excluye caninas propias y ya vistas; filtra por distancia, tamaño y energía |
| `+ like(Pet $swiper, Pet $swiped): Match\|null` | Registra PetSwipe; si mutual → crea Match |
| `+ pass(Pet $swiper, Pet $swiped): void` | |
| `+ unmatch(Match $match): void` | |

Usa: `Pet` · `PetSwipe` · `Match`

---

### `RecommendationService` *(Service)*

| Dependencias | `ProductRepository` |
|---|---|
| `+ getForPet(Pet $pet): Collection` | Filtra por especie, peso, raza y edad; cruza con HealthRecord próximos a vencer |
| `+ getUrgent(Pet $pet): Collection` | Solo productos ligados a tratamientos vencidos |

Usa: `Pet` · `HealthRecord` · `Product`

---

### `StorageService` *(Service)*

| Dependencias | `disk: string` |
|---|---|
| `+ upload(UploadedFile, folder): string` | |
| `+ delete(path): void` | |
| `+ getUrl(path): string` | |

Usa: `PetPhoto` · `PlaceReview`

---

### `OrderService` *(Service)*

| Dependencias | `StripeClient` |
|---|---|
| `+ createPaymentIntent(Order): PaymentIntent` | |
| `+ confirmOrder(Order, intentId): Order` | |
| `+ cancelOrder(Order): void` | |

Usa: `Order` · `OrderStatusHistory`

---

### `HealthReminderService` *(Service)*

| Método | Descripción |
|---|---|
| `+ getDueRecords(days = 3): Collection` | Registros con `next_due_date <= hoy + days` y `notifications_active = true` |
| `+ sendReminders(): void` | Llamado por Scheduler diariamente |

Usa: `HealthRecord` · dispatches: `ProcessHealthReminder` (Job)

---

## Zona F — Policies & Form Requests

### Policies

| Policy | Métodos |
|---|---|
| `PetPolicy` | `view(User, Pet)` · `create(User)` · `update(User, Pet)` · `delete(User, Pet)` |
| `OrderPolicy` | `view(User, Order)` · `create(User)` · `cancel(User, Order)` |
| `PlacePolicy` | `view(User, Place)` · `create(User)` · `update(User, Place)` · `approve(User, Place)` |

### Form Requests

| Request | Métodos |
|---|---|
| `StorePetRequest` | `authorize(): bool` · `rules(): array` |
| `UpdatePetRequest` | `authorize(): bool` · `rules(): array` |

---

## Tabla de Relaciones Eloquent (resumen)

| Clase origen | Método | Tipo | Clase destino |
|---|---|---|---|
| `User` | `pets()` | HasMany | `Pet` |
| `User` | `walks()` | HasMany | `Walk` |
| `User` | `orders()` | HasMany | `Order` |
| `User` | `placeReviews()` | HasMany | `PlaceReview` |
| `User` | `favoritePlaces()` | BelongsToMany | `PetFriendlyPlace` |
| `Pet` | `user()` | BelongsTo | `User` |
| `Pet` | `photos()` | HasMany | `PetPhoto` |
| `Pet` | `healthRecords()` | HasMany | `HealthRecord` |
| `Pet` | `walks()` | BelongsToMany | `Walk` |
| `Pet` | `matchesAsA()` | HasMany | `Match` (pet_a_id) |
| `Pet` | `matchesAsB()` | HasMany | `Match` (pet_b_id) |
| `Pet` | `orders()` | HasMany | `Order` |
| `Pet` | `swipesMade()` | HasMany | `PetSwipe` |
| `Match` | `petA()` | BelongsTo | `Pet` |
| `Match` | `petB()` | BelongsTo | `Pet` |
| `Match` | `messages()` | HasMany | `Message` |
| `Walk` | `creator()` | BelongsTo | `User` |
| `Walk` | `pets()` | BelongsToMany | `Pet` |
| `Provider` | `products()` | HasMany | `Product` |
| `Product` | `provider()` | BelongsTo | `Provider` |
| `Product` | `orders()` | HasMany | `Order` |
| `Order` | `user()` | BelongsTo | `User` |
| `Order` | `product()` | BelongsTo | `Product` |
| `Order` | `pet()` | BelongsTo | `Pet` |
| `Order` | `statusHistory()` | HasMany | `OrderStatusHistory` |
| `PetFriendlyPlace` | `creator()` | BelongsTo | `User` |
| `PetFriendlyPlace` | `reviews()` | HasMany | `PlaceReview` |
| `PetFriendlyPlace` | `favoritedByUsers()` | BelongsToMany | `User` |
| `PlaceReview` | `place()` | BelongsTo | `PetFriendlyPlace` |
| `PlaceReview` | `user()` | BelongsTo | `User` |

---

## Herencia

Todos los modelos Eloquent heredan implícitamente de:

```
Illuminate\Database\Eloquent\Model
```

Todos los Services se registran en `App\Providers\AppServiceProvider` y se inyectan vía el contenedor IoC de Laravel. No tienen clase base común.
