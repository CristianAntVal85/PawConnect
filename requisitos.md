# 🐾 PawConnect — Requisitos del Proyecto

**Versión Web con Laravel 11** · Última revisión: Julio 2026

---

## 🎯 Visión Global

PawConnect es una plataforma web integral para **dueños de mascotas** que combina funcionalidades sociales, sanitarias, comerciales y de geolocalización.

El proyecto se desarrolla inicialmente en Laravel, con una arquitectura modular y una API REST preparada para evolucionar hacia aplicaciones móviles y de escritorio.

### Alcance multiespecie (decisión de producto)

| Módulo | Alcance V1 |
|---|---|
| Social (match y paseos) | **Target principal: perros** |
| Sanitario | **Multiespecie** |
| Marketplace | **Multiespecie** |
| Administración | **Multiespecie** |

> **Política de implementación V1:** social prioriza caninos; sanitario, marketplace y administración son multiespecie desde el inicio.

### Cinco pilares

| Pilar | Descripción |
|---|---|
| **Social** | Perfiles, match, chat, paseos *(principalmente caninos)* |
| **Sanitario** | Historial, recordatorios, notificaciones inteligentes |
| **Marketplace** | Productos recomendados según la mascota, compra en un clic |
| **Geolocalización** | Lugares pet-friendly, veterinarios, pipicans, playas |
| **Administración** | Panel completo para gestionar usuarios, mascotas, productos, lugares y pedidos |

---

## 🧱 Tecnologías Principales

### Backend

- Laravel 11
- PHP 8.2+
- Eloquent ORM
- Laravel Sanctum (API)
- Laravel Scheduler + Jobs (notificaciones sanitarias)
- Broadcasting / Laravel Reverb (chat en tiempo real)

### Frontend

- Blade Templates
- TailwindCSS o Bootstrap
- JavaScript (Fetch API)
- Vue.js opcional para componentes dinámicos

### Base de Datos

- MySQL / MariaDB
- Migraciones y seeders
- Relaciones con claves foráneas

### Servicios externos

| Servicio | Tecnología |
|---|---|
| Almacenamiento | Laravel Storage (local → S3 en producción) |
| Mapas | Leaflet.js (gratuito, sin clave API) |
| Pagos | Stripe (mejor SDK PHP) |
| WebSocket | Laravel Reverb |
| Documentación API | Scribe |
| Tests | Pest |

---

## 🐾 Datos de la Mascota (Modelo Completo)

Cada mascota tendrá:

| Campo | Valores |
|---|---|
| **Especie** | `dog / cat / bird / rodent / other` |
| **Nombre** | texto libre |
| **Raza** | texto libre |
| **Edad** | número entero |
| **Peso** | decimal (kg) |
| **Tamaño** | `small / medium / large / xlarge` |
| **Nivel de energía** | `low / medium / high` |
| **Personalidad** | texto libre |
| **Fotos** | tabla `pet_photos` (id, pet_id, path, order) |
| **Disponible para paseos** | booleano |
| **Historial sanitario** | relación con `health_records` |
| **Matches** | relación con `matches` vía pet_a_id / pet_b_id |
| **Paseos** | many-to-many con `walks` *(V1 centrada en perros)* |
| **Ubicación** | heredada del usuario (lat/lng del dueño) |

---

## 🧩 Módulos y Funcionalidades

### 1. Autenticación y Roles

- Registro / login
- Roles: `usuario` y `administrador`
- Recuperación de contraseña
- Tokens API con Sanctum

### 2. Perfil del Usuario

- Datos personales, foto, ubicación
- Dirección de facturación y envío (para marketplace)
- Preferencias

### 3. Perfil de la Mascota

- Datos básicos, raza, fotos
- Preferencias de socialización
- Historial sanitario, paseos, matches

### 4. Sistema de Match

Algoritmo basado en:

- Distancia (Haversine)
- Tamaño compatible
- Nivel de energía
- Raza (afinidad opcional)
- Disponibilidad

> Interfaz tipo "swipe" *(en V1, orientada a perros)*.

### 5. Chat en Tiempo Real

- Conversaciones entre usuarios con match
- Mensajes en tiempo real (Laravel Reverb)
- Indicador "escribiendo…"
- Historial persistente

### 6. Gestión de Paseos

- Crear paseos (título, descripción, ubicación, fecha, aforo)
- Unirse / abandonar
- Mapa con ubicación
- Historial

### 7. Módulo Sanitario Inteligente 🩺

**Objetivo:** registrar tratamientos periódicos y avisar automáticamente cuando se acerca la siguiente dosis.

#### Tipos de tratamiento

| Tipo | Descripción |
|---|---|
| `vaccine` | Vacunas |
| `dewormer` | Pastillas desparasitarias |
| `pipette` | Pipetas |
| `collar` | Collares antiparasitarios (Seresto) |
| `vet_visit` | Revisiones veterinarias |
| `food` | Alimentación |

Cada tratamiento incluye: tipo, fecha de última administración, periodicidad, observaciones, activar notificaciones.

#### Notificaciones automáticas

El Scheduler detecta tratamientos próximos a vencer y envía alertas:

> *"Tu mascota Thor necesita la pastilla desparasitaria en 3 días."*

### 8. Marketplace Integrado 🛒

**Objetivo:** ofrecer productos adecuados a la mascota según especie, raza, peso, edad y necesidades sanitarias.

#### Catálogo

- Pastillas desparasitarias, collares Seresto, pipetas
- Comida según raza/tamaño
- Suplementos

#### Recomendación automática

Basada en: especie, peso, edad, raza, historial sanitario y stock disponible.

> *"Recomendado: NexGard 10–25 kg para Thor."*

#### Flujo de compra

1. Usuario pulsa "Comprar ahora"
2. Sistema crea `PaymentIntent` en Stripe
3. Usuario confirma pago
4. Se crea `Order` + `OrderStatusHistory`

### 9. Módulo de Lugares Pet-Friendly 🌍

**Objetivo:** mostrar lugares cercanos donde las mascotas son bienvenidas.

#### Categorías

`pipican · beach · park · vet · shop · restaurant · hotel`

#### Funciones

- Mapa interactivo con Leaflet.js
- Filtros por categoría
- Reseñas de usuarios (rating 1–5)
- Fotos, favoritos

#### Integraciones

- **Sanitario:** veterinarios cercanos cuando un tratamiento está próximo
- **Marketplace:** tiendas cercanas donde comprar el producto

---

## 🧱 Arquitectura Técnica

### Capas

| Capa | Responsabilidad |
|---|---|
| **Controllers** | Reciben HTTP, delegan en Services, devuelven respuesta |
| **Services** | Lógica de negocio (MatchService, RecommendationService…) |
| **Repositories** | Acceso a datos, abstracción sobre Eloquent |
| **Models** | Entidades Eloquent con relaciones |
| **Resources** | Transformadores JSON para la API |
| **Policies** | Autorización por recurso |
| **Form Requests** | Validación de entrada |
| **Events & Listeners** | Comunicación desacoplada entre módulos |
| **Jobs** | Tareas asíncronas (notificaciones sanitarias) |

### Tablas principales

| Tabla | Notas |
|---|---|
| `users` | |
| `pets` | |
| `pet_photos` | |
| `health_records` | |
| `matches` | |
| `pet_swipes` | registro de likes/passes para evitar repetidos |
| `messages` | |
| `walks` | |
| `walk_pet` | tabla pivote many-to-many walks/pets |
| `providers` | |
| `products` | |
| `orders` | |
| `order_status_history` | |
| `petfriendly_places` | |
| `place_reviews` | |
| `place_favorites` | tabla pivote many-to-many users/petfriendly_places |

---

## 📅 Planificación por Fases

> El detalle completo con tareas, comandos y criterios de aceptación está en `plan.md`.

| Fase | Semanas | Contenido |
|:---:|:---:|---|
| 1 | 1–2 | SRS, casos de uso, UML, ER, API REST, wireframes |
| 2 | 3–5 | Laravel, migraciones, modelos, auth, CRUD mascotas, seeders |
| 3 | 6–7 | API completa, Resources, Sanctum, Scribe, tests Pest |
| 4 | 8–10 | TailwindCSS + Vite, layouts Blade, vistas, responsive |
| 5 | 11–12 | Match (swipe), chat Reverb, paseos Leaflet |
| 6 | 13–15 | Sanitario + Scheduler + Marketplace + Stripe |
| 7 | 16–17 | Lugares pet-friendly, reseñas, favoritos |
| 8 | 18 | Panel admin, estadísticas, CSV |
| 9 | 19 | OWASP, CORS, docs, deploy, propuesta móvil |

---

## 🚀 Resultado Final

PawConnect se convierte en una plataforma completa que combina:

- **Social** — match y paseos caninos
- **Sanitario** — historial y recordatorios multiespecie
- **Comercial** — marketplace con recomendación inteligente
- **Geolocalización** — mapa de lugares pet-friendly
- **Administración** — panel completo de gestión

> Proyecto de nivel DAW avanzado con potencial real de convertirse en producto.

---

## 📋 SRS — Software Requirements Specification

### 1. Introducción

#### 1.1 Propósito

Este documento especifica los requisitos funcionales y no funcionales del sistema PawConnect, una plataforma web destinada a conectar dueños de mascotas y ofrecer servicios sociales, sanitarios, comerciales y de geolocalización.

#### 1.2 Alcance

PawConnect permitirá:

- Crear perfiles de usuario y de mascotas
- Realizar "match" entre perros compatibles *(V1 canina)*
- Chatear en tiempo real
- Gestionar paseos y actividades
- Registrar y monitorizar tratamientos sanitarios
- Recibir notificaciones inteligentes
- Comprar productos recomendados según la mascota
- Consultar lugares pet-friendly cercanos
- Administrar usuarios, mascotas, productos, pedidos y lugares

#### 1.3 Definiciones

| Término | Definición |
|---|---|
| **Match** | Coincidencia entre dos mascotas compatibles *(V1: perros)* |
| **Marketplace** | Módulo de compra de productos para mascotas |
| **Pet-friendly** | Lugares donde las mascotas son admitidas |
| **Tratamiento sanitario** | Vacuna, desparasitación, pipeta, collar, etc. |

---

### 2. Descripción General

#### 2.1 Perspectiva del producto

| Área | Funcionalidad |
|---|---|
| Social | Perfiles, match, chat, paseos |
| Sanitaria | Historial, recordatorios, notificaciones |
| Marketplace | Productos recomendados, compra en un clic |
| Geolocalización | Lugares pet-friendly |
| Administración | Panel completo de gestión |

#### 2.2 Usuarios del sistema

- **Usuario estándar:** dueño de una o varias mascotas
- **Administrador:** gestiona todo el sistema
- **Proveedor:** recibe pedidos y envía productos *(opcional)*

#### 2.3 Entorno operativo

- Navegadores modernos (Chrome, Edge, Firefox)
- Backend en Laravel 11
- Base de datos MySQL/MariaDB
- API REST para apps futuras

---

### 3. Requisitos Funcionales

#### 3.1 Gestión de usuarios

- **RF1:** El sistema permitirá registrar usuarios
- **RF2:** El usuario podrá iniciar sesión y cerrar sesión
- **RF3:** El usuario podrá editar su perfil
- **RF4:** El usuario podrá gestionar su dirección de facturación y envío
- **RF5:** El usuario podrá eliminar su cuenta

#### 3.2 Gestión de mascotas

- **RF6:** El usuario podrá registrar una o varias mascotas
- **RF7:** Cada mascota tendrá: especie, nombre, raza, edad, peso, tamaño, nivel de energía, personalidad, fotos
- **RF8:** El usuario podrá editar o eliminar el perfil de la mascota

#### 3.3 Sistema de match

- **RF9:** El sistema mostrará candidatos compatibles según distancia, tamaño, energía, raza y disponibilidad *(V1: perros)*
- **RF10:** El usuario podrá aceptar o rechazar sugerencias
- **RF11:** Si ambos aceptan, se genera un match

#### 3.4 Chat en tiempo real

- **RF12:** Los usuarios con match podrán chatear
- **RF13:** El chat mostrará mensajes en tiempo real
- **RF14:** El sistema mostrará estado "escribiendo…"

#### 3.5 Paseos

- **RF15:** El usuario podrá crear paseos
- **RF16:** Otros usuarios podrán unirse
- **RF17:** El sistema mostrará ubicación en mapa
- **RF18:** Se guardará historial de paseos

#### 3.6 Módulo sanitario inteligente

- **RF19:** El usuario podrá registrar tratamientos sanitarios (vacunas, desparasitantes, pipetas, collares, revisiones, alimentación)
- **RF20:** Cada tratamiento incluirá tipo, fecha de última administración, periodicidad, notas y activar notificaciones
- **RF21:** El sistema calculará automáticamente la próxima dosis
- **RF22:** El sistema enviará notificaciones cuando se acerque la fecha
- **RF23:** El usuario podrá ver el historial sanitario completo

#### 3.7 Marketplace

- **RF24:** El sistema mostrará productos disponibles
- **RF25:** El sistema recomendará productos según especie, peso, edad, raza e historial sanitario
- **RF26:** El usuario podrá comprar productos con un clic
- **RF27:** El sistema gestionará pedidos (creación, envío al proveedor, actualización de estado)
- **RF28:** El usuario podrá ver el estado del pedido
- **RF29:** El sistema usará la dirección de facturación del usuario

#### 3.8 Lugares pet-friendly

- **RF30:** El sistema mostrará lugares pet-friendly (pipicans, playas, parques, veterinarios, tiendas, restaurantes, hoteles)
- **RF31:** El usuario podrá ver lugares en un mapa
- **RF32:** El usuario podrá filtrar por categoría
- **RF33:** El usuario podrá ver ficha detallada del lugar
- **RF34:** El usuario podrá añadir reseñas
- **RF35:** El usuario podrá guardar lugares favoritos
- **RF36:** El sistema integrará lugares con el módulo sanitario (veterinarios cercanos para vacunas, tiendas para productos)

#### 3.9 Panel de administración

- **RF37:** El administrador podrá gestionar: usuarios, mascotas, productos, pedidos, lugares y reseñas
- **RF38:** El administrador podrá ver estadísticas del sistema

---

### 4. Requisitos No Funcionales

#### 4.1 Rendimiento

- **RNF1:** El sistema debe soportar múltiples usuarios concurrentes
- **RNF2:** Las notificaciones deben procesarse diariamente sin retrasos

#### 4.2 Seguridad

- **RNF3:** Autenticación con Laravel Sanctum
- **RNF4:** Contraseñas cifradas
- **RNF5:** Validación estricta de formularios
- **RNF6:** Protección contra CSRF, XSS y SQL Injection

#### 4.3 Usabilidad

- **RNF7:** Interfaz responsive
- **RNF8:** Navegación intuitiva
- **RNF9:** Accesibilidad AA

#### 4.4 Mantenibilidad

- **RNF10:** Arquitectura modular (Services, Repositories)
- **RNF11:** Código documentado
- **RNF12:** API REST clara y versionada

#### 4.5 Escalabilidad

- **RNF13:** Preparado para apps móviles
- **RNF14:** Preparado para integración con proveedores externos

---

### 5. Modelo de Datos (Resumen)

```
users · pets · pet_photos · health_records · matches · pet_swipes
messages · walks · walk_pet · providers · products · orders
order_status_history · petfriendly_places · place_reviews · place_favorites
```

---

### 6. Requisitos de API REST

| Endpoint | Descripción |
|---|---|
| `/api/v1/auth/login` | Autenticación |
| `/api/v1/auth/register` | Registro |
| `/api/v1/pets` | CRUD mascotas |
| `/api/v1/matches` | Gestión de matches |
| `/api/v1/matches/{id}/messages` | Chat por match |
| `/api/v1/walks` | Gestión de paseos |
| `/api/v1/pets/{id}/health` | Historial sanitario |
| `/api/v1/products` | Catálogo de productos |
| `/api/v1/orders` | Pedidos |
| `/api/v1/places` | Lugares pet-friendly |

---

### 7. Requisitos Futuros (Multiplataforma)

- Integración con Flutter o React Native
- Sincronización completa con API REST
- Notificaciones push móviles
