# 🐾 PawConnect — Casos de Uso

**Versión Web con Laravel 11** · Basados en SRS y plan.md

---

## Actores del sistema

| Actor | Rol |
|---|---|
| **Usuario** | Dueño de una o varias mascotas, autenticado |
| **Administrador** | Gestiona todo el sistema |
| **Sistema (Scheduler)** | Ejecuta tareas automáticas periódicas |

---

## 1. Gestión de Usuarios

### CU-01 — Registrar usuario

- **Actor:** Usuario
- **Precondición:** No estar registrado
- **Flujo principal:**
  1. El usuario accede al formulario de registro
  2. Introduce nombre, email y contraseña
  3. El sistema valida datos (email único)
  4. Se crea el usuario en `users`
  5. Se genera token Sanctum
- **Postcondición:** Usuario registrado y autenticado
- **Alternativas:** Email ya existe → mensaje de error 422

---

### CU-02 — Iniciar sesión

- **Actor:** Usuario
- **Precondición:** Usuario registrado
- **Flujo:**
  1. Usuario introduce email y contraseña
  2. Sistema valida credenciales
  3. Se genera token Sanctum
- **Postcondición:** Sesión iniciada
- **Alternativas:** Credenciales incorrectas → 401

---

### CU-03 — Editar perfil

- **Actor:** Usuario
- **Precondición:** Estar autenticado
- **Flujo:**
  1. Usuario accede a su perfil
  2. Modifica datos personales, foto, dirección, ubicación
  3. Sistema valida y guarda cambios
- **Postcondición:** Perfil actualizado

---

### CU-04 — Eliminar cuenta

- **Actor:** Usuario
- **Precondición:** Estar autenticado
- **Flujo:**
  1. Usuario solicita eliminar su cuenta
  2. Sistema confirma acción
  3. Se elimina el usuario y sus mascotas (soft delete)
- **Postcondición:** Cuenta eliminada, token revocado

---

## 2. Gestión de Mascotas

### CU-05 — Registrar mascota

- **Actor:** Usuario
- **Precondición:** Usuario autenticado
- **Flujo:**
  1. Usuario abre "Añadir mascota"
  2. Introduce especie, nombre, raza, edad, peso, tamaño, energía, personalidad
  3. Sube fotos
  4. Sistema valida y guarda en `pets` y `pet_photos`
- **Postcondición:** Mascota registrada

---

### CU-06 — Editar mascota

- **Actor:** Usuario
- **Precondición:** Ser dueño de la mascota
- **Flujo:**
  1. Usuario abre ficha de mascota
  2. Modifica datos
  3. Sistema valida y actualiza
- **Postcondición:** Mascota actualizada
- **Alternativas:** Tercero intenta editar → 403

---

### CU-07 — Eliminar mascota

- **Actor:** Usuario
- **Precondición:** Ser dueño
- **Flujo:**
  1. Usuario solicita eliminar
  2. Sistema pide confirmación
  3. Se elimina mascota y fotos asociadas
- **Postcondición:** Mascota eliminada

---

## 3. Sistema de Match *(perros en V1)*

### CU-08 — Ver candidatos

- **Actor:** Usuario (con perro registrado)
- **Precondición:** Tener al menos un perro con `available_for_walks = true`
- **Flujo:**
  1. El sistema obtiene perros cercanos (Haversine)
  2. Excluye perros ya vistos (`pet_swipes`)
  3. Filtra por tamaño, energía y disponibilidad
  4. Muestra tarjeta swipe
- **Postcondición:** Lista de candidatos disponible

---

### CU-09 — Dar Like

- **Actor:** Usuario
- **Flujo:**
  1. Usuario pulsa "Like" ❤️
  2. Se registra `action = like` en `pet_swipes`
  3. Sistema comprueba si el otro perro también dio like
  4. Si es mutuo → crear registro en `matches` + disparar `MatchCreated`
- **Postcondición:** Match creado si es mutuo

---

### CU-10 — Dar Pass

- **Actor:** Usuario
- **Flujo:**
  1. Usuario pulsa "Pass" ✗
  2. Se registra `action = pass` en `pet_swipes`
- **Postcondición:** El candidato no vuelve a mostrarse

---

### CU-11 — Ver matches activos

- **Actor:** Usuario
- **Flujo:**
  1. Usuario abre sección "Matches"
  2. Sistema lista coincidencias desde `matches` con status `matched`
- **Postcondición:** Matches visibles

---

## 4. Chat en Tiempo Real

### CU-12 — Enviar mensaje

- **Actor:** Usuario
- **Precondición:** Tener un match
- **Flujo:**
  1. Usuario abre chat
  2. Escribe y envía mensaje
  3. Sistema guarda en `messages`
  4. Se emite evento `MessageSent` por canal privado `private-match.{matchId}`
- **Postcondición:** Mensaje enviado y recibido en tiempo real

---

### CU-13 — Ver historial de mensajes

- **Actor:** Usuario
- **Flujo:**
  1. Usuario abre conversación
  2. Sistema carga mensajes desde `messages` ordenados por `created_at`
  3. Marca mensajes como leídos (`read_at`)
- **Postcondición:** Historial visible

---

## 5. Paseos

### CU-14 — Crear paseo

- **Actor:** Usuario
- **Flujo:**
  1. Usuario abre "Crear paseo"
  2. Introduce título, descripción, ubicación (lat/lng), fecha, aforo máximo
  3. Sistema guarda en `walks`
- **Postcondición:** Paseo creado y visible en mapa

---

### CU-15 — Unirse a paseo

- **Actor:** Usuario
- **Precondición:** Tener perro con `available_for_walks = true`; paseo no completo
- **Flujo:**
  1. Usuario abre paseo
  2. Selecciona su perro y pulsa "Unirse"
  3. Sistema añade registro en `walk_pet`
- **Postcondición:** Mascota canina unida al paseo

---

### CU-16 — Ver paseos cercanos

- **Actor:** Usuario
- **Flujo:**
  1. Usuario abre mapa
  2. Sistema filtra paseos por distancia (lat/lng + radius_km)
  3. Muestra marcadores Leaflet con popup
- **Postcondición:** Paseos visibles en mapa

---

## 6. Módulo Sanitario

### CU-17 — Registrar tratamiento sanitario

- **Actor:** Usuario
- **Flujo:**
  1. Usuario abre historial sanitario de la mascota
  2. Añade tipo, fecha, periodicidad y notas
  3. Sistema calcula `next_due_date = last_date + periodicity_days`
  4. Guarda en `health_records`
- **Postcondición:** Registro creado con próxima fecha calculada

---

### CU-18 — Recibir notificación sanitaria

- **Actor:** Sistema (Scheduler)
- **Flujo:**
  1. Cron diario ejecuta `health:reminders`
  2. Busca registros con `next_due_date <= now() + 3 días` y `notifications_active = true`
  3. Despacha `ProcessHealthReminder` Job por cada registro
  4. El job envía notificación al usuario
- **Postcondición:** Usuario notificado con tiempo de antelación

---

### CU-19 — Renovar tratamiento

- **Actor:** Usuario
- **Flujo:**
  1. Usuario pulsa "Renovar" en el registro
  2. Sistema actualiza `last_date = hoy`
  3. Recalcula `next_due_date`
- **Postcondición:** Tratamiento renovado con nueva fecha

---

## 7. Marketplace

### CU-20 — Ver productos

- **Actor:** Usuario
- **Flujo:**
  1. Usuario abre marketplace
  2. Sistema lista productos desde `products` (filtros: categoría, precio, stock, especie)
- **Postcondición:** Productos visibles con filtros

---

### CU-21 — Ver recomendaciones

- **Actor:** Usuario
- **Flujo:**
  1. Usuario selecciona mascota
  2. `RecommendationService` filtra por especie, peso, raza y edad
  3. Cruza con `health_records` próximos a vencer
  4. Muestra productos ordenados por relevancia
- **Postcondición:** Recomendaciones personalizadas generadas

---

### CU-22 — Comprar producto (1 clic)

- **Actor:** Usuario
- **Flujo:**
  1. Usuario pulsa "Comprar"
  2. Sistema crea `PaymentIntent` en Stripe
  3. Usuario confirma pago (Stripe.js en frontend)
  4. Si pago OK → crea `orders` + `order_status_history('pending')`
  5. Notifica al proveedor y al usuario
- **Postcondición:** Pedido creado y procesándose

---

## 8. Lugares Pet-Friendly

### CU-23 — Ver lugares en mapa

- **Actor:** Usuario
- **Flujo:**
  1. Usuario abre mapa
  2. Sistema carga lugares desde `petfriendly_places` con `status = approved`
  3. Muestra marcadores por categoría con iconos diferenciados
- **Postcondición:** Lugares visibles y filtrables

---

### CU-24 — Añadir reseña

- **Actor:** Usuario
- **Precondición:** Autenticado
- **Flujo:**
  1. Usuario abre ficha del lugar
  2. Añade rating (1–5) + comentario + foto opcional
  3. Sistema guarda en `place_reviews`
- **Postcondición:** Reseña registrada y visible en la ficha

---

### CU-25 — Guardar favorito

- **Actor:** Usuario
- **Flujo:**
  1. Usuario pulsa corazón en tarjeta o ficha
  2. Sistema guarda en `place_favorites`
- **Postcondición:** Lugar marcado como favorito; visible en "Mis Favoritos"

---

### CU-26 — Sugerir nuevo lugar

- **Actor:** Usuario
- **Flujo:**
  1. Usuario rellena formulario con datos del lugar
  2. Sistema guarda con `status = pending`
  3. Admin recibe notificación para revisión
- **Postcondición:** Lugar pendiente de aprobación

---

## 9. Panel de Administración

### CU-27 — Gestionar usuarios

- **Actor:** Administrador
- **Flujo:**
  1. Admin abre panel → sección usuarios
  2. Lista con búsqueda y filtros
  3. Edita rol, bloquea o elimina
- **Postcondición:** Gestión realizada

---

### CU-28 — Gestionar productos

- **Actor:** Administrador
- **Flujo:**
  1. Admin abre sección productos
  2. Crea, edita o elimina productos; actualiza stock y proveedor
- **Postcondición:** Catálogo actualizado

---

### CU-29 — Gestionar pedidos

- **Actor:** Administrador
- **Flujo:**
  1. Admin abre pedidos
  2. Cambia estado del pedido
  3. Sistema guarda en `order_status_history`
  4. Opcionalmente añade URL de tracking
- **Postcondición:** Pedido actualizado con historial

---

### CU-30 — Aprobar / rechazar lugares sugeridos

- **Actor:** Administrador
- **Flujo:**
  1. Admin revisa lugares con `status = pending`
  2. Aprueba (`status = approved`) o rechaza (`status = rejected`)
  3. Si aprobado, el lugar aparece en el mapa público
- **Postcondición:** Lugar publicado o descartado

---

### CU-31 — Moderar reseñas

- **Actor:** Administrador
- **Flujo:**
  1. Admin lista todas las reseñas
  2. Elimina reseñas inapropiadas
- **Postcondición:** Reseña eliminada del sistema

---

### CU-32 — Ver estadísticas del sistema

- **Actor:** Administrador
- **Flujo:**
  1. Admin abre dashboard
  2. Sistema muestra totales: usuarios, mascotas, matches, pedidos, ingresos
  3. Gráficas con Chart.js por período
- **Postcondición:** Panel de métricas actualizado
