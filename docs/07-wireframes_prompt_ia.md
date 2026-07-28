# PawConnect — Guía de Wireframes para IA Generativa

> **Instrucción principal:** Genera wireframes de alta fidelidad para la aplicación web **PawConnect**, una plataforma social y de servicios para dueños de mascotas. El estilo debe ser **moderno, elegante y profesional**, inspirado en apps como Hinge, Airbnb y Linear. Usa siempre la guía de diseño definida en la Sección 1 antes de generar cualquier vista.

---

## Sección 1 — Sistema de Diseño

### 1.1 Paleta de Colores

| Rol | HEX | Uso |
|---|---|---|
| **Primary** | `#1A1A2E` | Fondo oscuro de navbar, sidebars y encabezados |
| **Accent** | `#E8834A` | CTAs, badges, iconos activos, gradientes (coral/ámbar cálido) |
| **Accent Light** | `#F5A97F` | Hover states, bordes de tarjetas seleccionadas |
| **Surface** | `#FFFFFF` | Fondo de tarjetas, modales y formularios |
| **Background** | `#F7F5F2` | Fondo de página (blanco roto cálido) |
| **Muted** | `#8A8A9A` | Texto secundario, placeholders, etiquetas |
| **Success** | `#2ECC71` | Semáforo sanitario verde |
| **Warning** | `#F1C40F` | Semáforo sanitario amarillo |
| **Danger** | `#E74C3C` | Semáforo sanitario rojo, errores |
| **Border** | `#EBEBEF` | Separadores y bordes de inputs |

**Gradiente de marca:** `linear-gradient(135deg, #E8834A 0%, #C0392B 100%)` — usado en la landing hero y avatares sin foto.

### 1.2 Tipografía

| Rol | Fuente | Peso | Tamaño |
|---|---|---|---|
| Display / Hero | **Plus Jakarta Sans** | 800 ExtraBold | 48–64px |
| Heading H1 | Plus Jakarta Sans | 700 Bold | 32px |
| Heading H2 | Plus Jakarta Sans | 600 SemiBold | 24px |
| Heading H3 | Plus Jakarta Sans | 600 SemiBold | 18px |
| Body | **Inter** | 400 Regular | 15px / line-height 1.6 |
| Body Small | Inter | 400 Regular | 13px |
| Label / Caption | Inter | 500 Medium | 11px / uppercase + letter-spacing |
| Mono (código) | JetBrains Mono | 400 | 13px |

### 1.3 Espaciado y Bordes

- **Grid base:** 8px. Todos los márgenes y paddings son múltiplos de 8.
- **Border radius:** 12px tarjetas, 8px inputs y botones, 24px chips/badges, 999px pills y avatares.
- **Sombras:**
  - `card`: `0 1px 3px rgba(0,0,0,.08), 0 4px 12px rgba(0,0,0,.06)`
  - `hover`: `0 8px 24px rgba(0,0,0,.12)`
  - `modal`: `0 20px 60px rgba(0,0,0,.20)`

### 1.4 Componentes Globales

#### Navbar (desktop)
```
[ Logo PawConnect ]   [ Dashboard  Mascotas  Match  Paseos  Marketplace  Lugares ]   [ 🔔 3 ]  [ Avatar ▼ ]
Fondo: #1A1A2E | altura: 64px | logo con ícono de huella + texto blanco
```

#### Sidebar (panel admin)
```
Ancho: 240px fijo | Fondo: #1A1A2E
Items: ícono + etiqueta | activo: fondo accent con borde izquierdo 3px accent
```

#### Botones
- **Primary:** fondo `#E8834A`, texto blanco, sin borde, hover oscurece 10%
- **Secondary:** fondo transparente, borde `#E8834A`, texto `#E8834A`
- **Ghost:** sin fondo ni borde, texto `#1A1A2E`, hover fondo `#F7F5F2`
- **Danger:** fondo `#E74C3C`
- Todos los botones: `border-radius: 8px | padding: 10px 20px | font-weight: 600`

#### Inputs
- Borde `#EBEBEF`, focus borde `#E8834A` con sombra `0 0 0 3px rgba(232,131,74,.15)`
- Label siempre encima, nunca placeholder como único label
- Error: borde `#E74C3C` + mensaje debajo en rojo

#### Tarjeta (Card)
- Fondo blanco, `border-radius: 12px`, sombra `card`
- Hover: sombra `hover`, transición `200ms ease`
- Padding interno: 24px

#### Badge / Chip
- `border-radius: 999px | padding: 4px 12px | font-size: 11px | font-weight: 600 | uppercase`
- Colores según contexto (accent, success, warning, danger, muted)

---

## Sección 2 — Vistas a Generar

---

### Vista 1 — Landing Page (`/`)

**Objetivo:** conversión. Primera impresión elegante para nuevos usuarios.

#### Layout (pantalla completa, scroll vertical)

**Navbar flotante (sticky)**
```
[ 🐾 PawConnect ]                  [ Funciones  Precios  Blog ]  [ Iniciar sesión ]  [ Registrarse → ]
Fondo: transparent → #1A1A2E al hacer scroll | texto blanco
```

**Sección Hero (100vh)**
```
┌──────────────────────────────────────────────────────────────────────┐
│  Fondo: gradiente diagonal #1A1A2E → #2D1B4E                         │
│                                                                       │
│   [izquierda 55%]                   [derecha 45%]                    │
│                                                                       │
│   Tag: "🐾 LA RED SOCIAL DE TUS MASCOTAS"  ← chip accent            │
│                                                                       │
│   Tu perro merece          ┌─────────────────────────────────┐       │
│   encontrar su             │  MOCKUP: Stack de 3 tarjetas de  │       │
│   mejor amigo 🐶           │  perfil de perro superpuestas,   │       │
│                            │  la del frente con foto, nombre, │       │
│   [párrafo subtítulo]      │  raza y botones ❤️ / ✗           │       │
│                            └─────────────────────────────────┘       │
│   [ Registrarme gratis → ]  [ Ver demo ]                             │
│                                                                       │
│   — 2.400 dueños activos · 98% satisfacción · Gratis para siempre — │
└──────────────────────────────────────────────────────────────────────┘
```

**Sección Features (4 columnas)**
```
┌──────────────────┬──────────────────┬──────────────────┬──────────────────┐
│  🐾 Match Social │  🏥 Control Sani.│  🛒 Marketplace  │  🗺️ Lugares PF   │
│  Conecta perros  │  Vacunas, despar.│  Productos recomen│  Parques, playas │
│  compatibles por │  y recordatorios │  dados según tu   │  y vets cerca de │
│  personalidad    │  automáticos     │  mascota          │  ti              │
└──────────────────┴──────────────────┴──────────────────┴──────────────────┘
Fondo blanco | cada columna: icono grande accent + H3 + body | animación on-scroll
```

**Sección How It Works (3 pasos con línea conectora)**
```
Fondo: #F7F5F2
[ 1. Crea tu perfil ] ——→ [ 2. Conecta mascotas ] ——→ [ 3. Disfruta juntos ]
Números grandes en accent | ilustración minimalista por paso
```

**Sección Testimonios (carrusel)**
```
Foto circular | Nombre + ciudad | Texto testimonial entre comillas grandes " "
Fondo blanco | dots de navegación accent
```

**Footer**
```
Fondo: #1A1A2E | 4 columnas: logo+tagline, producto, empresa, legal
Copyright centrado abajo | links blancos con opacity 0.6
```

---

### Vista 2 — Dashboard del Usuario (`/dashboard`)

**Objetivo:** overview rápido del estado del usuario tras hacer login.

#### Layout: Navbar + 2 columnas (contenido 70% / aside 30%)

```
┌─ NAVBAR ──────────────────────────────────────────────────────────────────┐
│                                                                            │
├─ MAIN (70%) ──────────────────────────┬─ ASIDE (30%) ─────────────────────┤
│                                       │                                   │
│  ┌─ Saludo ──────────────────────┐    │  ┌─ Mis Mascotas ──────────────┐  │
│  │ "Hola, Carlos 👋"             │    │  │ [ + Añadir mascota ]        │  │
│  │ Jueves 24 Jul · Madrid        │    │  │                              │  │
│  └───────────────────────────────┘    │  │  🐕 Rex (Labrador · 3 años)  │  │
│                                       │  │  🐈 Luna (Gato · 1 año)     │  │
│  ┌─ Stats rápidos (4 tiles) ──────┐   │  └──────────────────────────────┘  │
│  │ 💬 3 Matches │ 📍 2 Paseos    │   │                                   │
│  │ 🛒 1 Pedido  │ 🏥 2 Alertas  │   │  ┌─ Notificaciones ────────────┐  │
│  └────────────────────────────────┘   │  │ 🔴 Rex: vacuna en 2 días    │  │
│                                       │  │ 💬 Nuevo match con Nala     │  │
│  ┌─ Alertas Sanitarias ───────────┐   │  │ 📦 Pedido enviado           │  │
│  │ 🔴 Rex — Desparasitación       │   │  └──────────────────────────────┘  │
│  │   Venció hace 3 días           │   │                                   │
│  │   [ Renovar ] [ Ver detalle ]  │   │  ┌─ Acciones rápidas ──────────┐  │
│  │ 🟡 Luna — Vacuna antirrábica   │   │  │ [ 🐾 Buscar match ]         │  │
│  │   Vence en 5 días              │   │  │ [ 🗺️ Explorar lugares ]     │  │
│  └────────────────────────────────┘   │  │ [ 🛒 Ir al marketplace ]    │  │
│                                       │  └──────────────────────────────┘  │
│  ┌─ Actividad reciente ───────────┐   │                                   │
│  │ Timeline con iconos y fechas   │   │                                   │
│  │ · Match con Nala ❤️  (hace 1h) │   │                                   │
│  │ · Paseo en Retiro (mañana)     │   │                                   │
│  │ · Pedido #142 enviado          │   │                                   │
│  └────────────────────────────────┘   │                                   │
└───────────────────────────────────────┴───────────────────────────────────┘
```

**Detalles visuales:**
- Tiles de stats: icono grande + número grande + label pequeño + borde izquierdo de color accent
- Alertas sanitarias con semáforo de color en borde izquierdo
- Timeline con línea vertical punteada y puntos de color

---

### Vista 3 — Swipe de Match (`/match`)

**Objetivo:** experiencia tipo Tinder, fluida y adictiva, centrada en la tarjeta del perro.

#### Layout: Navbar + columna central centrada

```
┌─ NAVBAR ──────────────────────────────────────────────────────────────────┐
│                                                                            │
│                    ┌─ Seleccionar mascota ─────┐                          │
│                    │ ¿Con quién buscas match?  │                          │
│                    │  [ 🐕 Rex ▼ ]             │                          │
│                    └───────────────────────────┘                          │
│                                                                            │
│              ┌─────────────── TARJETA PRINCIPAL ──────────────────┐       │
│              │                                                      │       │
│              │  ┌────────────────────────────────────────────┐     │       │
│              │  │                                            │     │       │
│              │  │         FOTO del perro (3:4 ratio)         │     │       │
│              │  │         Gradiente oscuro en la parte baja  │     │       │
│              │  │                                            │     │       │
│              │  │  ● ● ○ ○   ← dots de fotos                │     │       │
│              │  │                                            │     │       │
│              │  │  NALA                           3 años     │     │       │
│              │  │  Golden Retriever · Madrid                 │     │       │
│              │  │  ⚡ Energía alta  · 🐕 Talla grande        │     │       │
│              │  └────────────────────────────────────────────┘     │       │
│              │                                                      │       │
│              │  [ ↓ Ver más info ]                                  │       │
│              │                                                      │       │
│              │        [ ✗ PASS ]          [ ❤️  LIKE ]             │       │
│              │     Botón circular gris   Botón circular accent     │       │
│              │                                                      │       │
│              └──────────────────────────────────────────────────────┘       │
│                                                                            │
│                 [ 3 candidatos restantes · Ver mis matches → ]            │
└───────────────────────────────────────────────────────────────────────────┘
```

**Panel expandido (al pulsar "Ver más info"):**
```
Desliza hacia arriba → se muestra un drawer desde abajo con:
- Descripción de personalidad
- Disponibilidad para paseos: ✅ Sí
- Dueño: foto avatar + nombre + distancia "a 1.2 km"
- "Mascotas compatibles con Rex según energía"
```

**Modal "¡Es un Match!" (overlay)**
```
┌──────────────────────────────────────────────────────┐
│                                                       │
│   Fondo: gradiente accent con confetti animado        │
│                                                       │
│   [ Foto Rex ]  ❤️  [ Foto Nala ]                    │
│   Ambas fotos circulares grandes con borde blanco     │
│                                                       │
│   "¡Rex y Nala han hecho match! 🎉"                  │
│                                                       │
│   [ 💬 Enviar mensaje ]  [ Seguir buscando ]         │
│                                                       │
└──────────────────────────────────────────────────────┘
```

---

### Vista 4 — Chat (`/chat`)

**Objetivo:** mensajería en tiempo real, limpia y fluida.

#### Layout: Navbar + 2 paneles fijos (sidebar izq. + área de mensajes)

```
┌─ NAVBAR ──────────────────────────────────────────────────────────────────┐
│                                                                            │
├─ SIDEBAR (320px) ──────────────────┬─ ÁREA DE CONVERSACIÓN ───────────────┤
│                                    │                                       │
│  🔍 [ Buscar conversación... ]     │  ┌─ Header conversación ──────────┐  │
│  ──────────────────────────────    │  │ [ ← ] [Foto Nala] Nala (Rex)  │  │
│                                    │  │ 🟢 Activo ahora                │  │
│  ┌─ Conversación (activa) ──────┐  │  └───────────────────────────────┘  │
│  │ [Foto Nala] Nala             │  │                                       │
│  │ "¿Quedamos mañana en..."     │  │  ┌─ Área de mensajes ─────────────┐  │
│  │                    hace 2 min│  │  │                                  │  │
│  └──────────────────────────────┘  │  │  [Fecha: Hoy]                    │  │
│                                    │  │                                  │  │
│  ┌─ Conversación ───────────────┐  │  │     ┌──────────────────────┐    │  │
│  │ [Foto Bruno] Bruno           │  │  │     │ ¿Quedamos mañana en  │    │  │
│  │ "Genial! A qué hora..."  ✓✓  │  │  │     │ el Retiro? 🐕        │    │  │
│  └──────────────────────────────┘  │  │     └────────── Nala 14:23 ┘    │  │
│                                    │  │                                  │  │
│  ┌─ Conversación ───────────────┐  │  │  ┌──────────────────────┐        │  │
│  │ [Foto Max] Max       🔴 2    │  │  │  │ ¡Claro! A las 11 me  │        │  │
│  │ "Hola! Vi que Rex..."        │  │  │  │ viene genial 😊       │        │  │
│  └──────────────────────────────┘  │  │  └─ Tú 14:25 ──────────┘        │  │
│                                    │  │                                  │  │
│  [+ Más conversaciones]            │  │  ··· Nala está escribiendo...    │  │
│                                    │  │                                  │  │
│                                    │  └──────────────────────────────────┘  │
│                                    │                                       │
│                                    │  ┌─ Input ────────────────────────┐  │
│                                    │  │ [📎] [ Escribe un mensaje... ] [→]│  │
│                                    │  └───────────────────────────────┘  │
└────────────────────────────────────┴───────────────────────────────────────┘
```

**Detalles visuales:**
- Mensajes propios: burbuja con fondo accent, texto blanco, alineado a la derecha
- Mensajes del otro: burbuja con fondo `#F0EEF0`, texto `#1A1A2E`, alineado a la izquierda
- Ticks de leído: ✓ gris (enviado), ✓✓ gris (entregado), ✓✓ accent (leído)
- "Escribiendo…" con 3 puntos animados
- Sidebar: conversación activa con fondo `#F7F5F2` y borde izquierdo 3px accent

---

### Vista 5 — Historial Sanitario (`/pets/{id}/health`)

**Objetivo:** control visual claro de la salud de la mascota, con semáforo de estado.

#### Layout: Navbar + breadcrumb + contenido

```
┌─ NAVBAR ──────────────────────────────────────────────────────────────────┐
│                                                                            │
│  Mis Mascotas › Rex › Historial Sanitario                                 │
│                                                                            │
│  ┌─ Header mascota ────────────────────────────────────────────────────┐  │
│  │  [ Foto Rex circular ]   Rex — Labrador · 3 años · 28 kg           │  │
│  │                          🟢 Salud general: Bien · 2/4 al día        │  │
│  │                          [ ← Volver ]  [ + Añadir registro ]        │  │
│  └─────────────────────────────────────────────────────────────────────┘  │
│                                                                            │
│  ┌─ Resumen de estado (4 tiles) ────────────────────────────────────────┐ │
│  │  🟢 Vacunas     │  🟡 Desparasit. │  🔴 Antipulgas  │  🟢 Revisión   │ │
│  │  Al día         │  Vence en 5 días│  Vencida hace 3d│  Al día         │ │
│  └──────────────────────────────────────────────────────────────────────┘ │
│                                                                            │
│  ┌─ Línea de tiempo ──────────────────────────────────────────────────┐   │
│  │                                                                     │   │
│  │  2026                                                               │   │
│  │  │                                                                  │   │
│  │  ●── Ene 10 ── Vacuna Rabia ────────────────── 🟢 Vigente          │   │
│  │  │             Próxima: Ene 10, 2027                                │   │
│  │  │             [ Ver detalle ]                                      │   │
│  │  │                                                                  │   │
│  │  ●── Mar 22 ── Desparasitación interna ──────── 🟡 Próxima en 5d   │   │
│  │  │             Última: Mar 22, 2026 · Cada 90 días                 │   │
│  │  │             [ Renovar hoy ]  [ Ver detalle ]                    │   │
│  │  │                                                                  │   │
│  │  ●── Abr 01 ── Antipulgas ──────────────────── 🔴 Vencida          │   │
│  │  │             Venció: Abr 01 · Hace 3 días                        │   │
│  │  │             [ ⚠️ Renovar urgente ]  [ Ver detalle ]             │   │
│  │  │                                                                  │   │
│  │  [ Cargar más registros ]                                           │   │
│  │                                                                     │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
└───────────────────────────────────────────────────────────────────────────┘
```

**Modal "Añadir / Editar Registro":**
```
┌─ Modal (480px) ──────────────────────────────────────────┐
│  Nuevo Registro Sanitario                          [ ✕ ] │
│  ──────────────────────────────────────────────────────  │
│  Tipo *          [ Vacuna ▼ ]                            │
│  Fecha último *  [ dd/mm/aaaa 📅 ]                       │
│  Periodicidad *  [ 365 días ]   (cada X días)            │
│  Notas           [ Textarea 3 líneas ]                   │
│  Notificaciones  [ ☑ Activar recordatorio por email ]    │
│  ──────────────────────────────────────────────────────  │
│                    [ Cancelar ]  [ Guardar registro ]    │
└──────────────────────────────────────────────────────────┘
```

---

### Vista 6 — Marketplace (`/marketplace`)

**Objetivo:** tienda elegante con recomendaciones personalizadas.

#### Layout: Navbar + filtros lateral + grid de productos

```
┌─ NAVBAR ──────────────────────────────────────────────────────────────────┐
│                                                                            │
│  ┌─ Hero banner pequeño ──────────────────────────────────────────────┐   │
│  │  Fondo gradiente accent    "Recomendado para Rex 🐕"               │   │
│  │                            Productos seleccionados para su especie, │   │
│  │  [Ilustración perro]       peso y edad                             │   │
│  └────────────────────────────────────────────────────────────────────┘   │
│                                                                            │
├─ FILTROS (240px) ──────────────────┬─ CATÁLOGO ──────────────────────────┤
│                                    │                                       │
│  Mascota                           │  [ Ordenar: Recomendados ▼ ]         │
│  [ 🐕 Rex ▼ ]                      │  120 productos                        │
│                                    │                                       │
│  Categoría                         │  ── Recomendados para Rex ──          │
│  ☑ Alimentación                    │                                       │
│  ☑ Antiparasitarios                │  ┌────────┐ ┌────────┐ ┌────────┐   │
│  ☐ Accesorios                      │  │ [Foto] │ │ [Foto] │ │ [Foto] │   │
│  ☐ Higiene                         │  │Pienso  │ │Pipeta  │ │Collar  │   │
│  ☐ Salud                           │  │Premium │ │Antipar.│ │GPS     │   │
│                                    │  │★ 4.8   │ │★ 4.6  │ │★ 4.9  │   │
│  Precio                            │  │24,90€  │ │8,50€  │ │45,00€ │   │
│  [ 0€ ─────●──── 150€ ]            │  │[Añadir]│ │[Añadir]│ │[Añadir]│   │
│                                    │  └────────┘ └────────┘ └────────┘   │
│  Raza compatible                   │                                       │
│  [ Todas ▼ ]                       │  ── Todos los productos ──            │
│                                    │                                       │
│  Peso mascota                      │  ┌────────┐ ┌────────┐ ┌────────┐   │
│  [ 20–30 kg ]                      │  │  ...   │ │  ...   │ │  ...   │   │
│                                    │  └────────┘ └────────┘ └────────┘   │
│  [ Limpiar filtros ]               │                                       │
│                                    │  [ Cargar más productos ]            │
└────────────────────────────────────┴───────────────────────────────────────┘
```

**Tarjeta de producto:**
- Foto con ratio 1:1, badge "Recomendado" en accent si aplica
- Nombre, precio grande, rating con estrellas
- Botón "Añadir al carrito" con icono
- Hover: sombra elevada + botón "Ver detalle" overlay con fondo semi-transparente

**Modal detalle de producto (overlay):**
```
[ Galería izquierda ] | [ Info derecha: nombre, precio, descripción, select cantidad, botón Comprar ]
Abajo: "Recomendado para razas: Labrador, Golden..." | "Clientes también compraron:"
```

---

### Vista 7 — Mapa de Lugares Pet-Friendly (`/places`)

**Objetivo:** mapa interactivo con sidebar de lugares y filtros por categoría.

#### Layout: Navbar + sidebar izq. + mapa tiempo real (full height)

```
┌─ NAVBAR ──────────────────────────────────────────────────────────────────┐
│                                                                            │
├─ SIDEBAR (360px) ──────────────────┬─ MAPA (resto del ancho, full height) ┤
│                                    │                                       │
│  📍 Lugares Pet-Friendly           │  ┌─ Mapa Leaflet ─────────────────┐  │
│                                    │  │                                  │  │
│  🔍 [ Buscar lugar... ]            │  │   [Control zoom + /−]           │  │
│                                    │  │   [Botón 📍 Mi ubicación]       │  │
│  Filtrar por categoría:            │  │                                  │  │
│  [🌿Parque] [🏖️Playa] [🏥Vet]    │  │   Marcadores en el mapa:        │  │
│  [🛒Tienda] [🍽️Rest.] [🏨Hotel]  │  │   · 🌿 Verde = parque           │  │
│  ──────────────────────────────    │  │   · 🏥 Azul = veterinario       │  │
│                                    │  │   · 🛒 Naranja = tienda         │  │
│  ┌─ Tarjeta lugar ──────────────┐  │  │   · etc.                        │  │
│  │ [Foto]  Parque del Retiro    │  │  │                                  │  │
│  │         🌿 Parque · 0.8 km  │  │  │   ┌─ Popup marcador ──────────┐  │  │
│  │         ★ 4.7 (42 reseñas)  │  │  │   │ Parque del Retiro         │  │  │
│  │         [Ver] [♡ Guardar]   │  │  │   │ 🌿 Parque · ★ 4.7        │  │  │
│  └──────────────────────────────┘  │  │   │ [Ver detalle] [♡ Fav.]   │  │  │
│                                    │  │   └───────────────────────────┘  │  │
│  ┌─ Tarjeta lugar ──────────────┐  │  │                                  │  │
│  │ [Foto]  Clínica VetMad       │  │  └──────────────────────────────────┘  │
│  │         🏥 Veterinario ·1.2km│  │                                       │
│  │         ★ 4.9 (128 reseñas) │  │                                       │
│  │         [Ver] [♡ Guardar]   │  │                                       │
│  └──────────────────────────────┘  │                                       │
│                                    │                                       │
│  [ + Sugerir un lugar ]           │                                       │
└────────────────────────────────────┴───────────────────────────────────────┘
```

**Ficha de lugar (vista dedicada `/places/{id}`):**
```
┌─ Galería (hero full width, 300px altura) ──────────────────────────────────┐
│  [foto1]  [foto2]  [foto3]  [foto4]           [ ♡ Guardar ] [ Compartir ]  │
└────────────────────────────────────────────────────────────────────────────┘

[ Nombre lugar — Categoría badge ]     [ Mapa mini embebido (derecha) ]
Rating ★★★★☆  42 reseñas              Dirección, teléfono, web
Descripción completa

── Reseñas ──
[ Avatar ] Nombre · ★★★★★ · hace 2 días
"Comentario de la reseña..."

[ + Escribir reseña ]
```

---

### Vista 8 — Panel de Administración (`/admin/dashboard`)

**Objetivo:** control total de la plataforma. Denso en datos, limpio en presentación.

#### Layout: Sidebar fijo + área de contenido

```
┌─ SIDEBAR ADMIN (240px) ────────────┬─ CONTENIDO ADMIN ──────────────────────┐
│                                    │                                          │
│  🐾 PawConnect Admin               │  ── Dashboard ──────────────────────── │
│  ──────────────────────────────    │                                          │
│  📊 Dashboard          (activo)    │  ┌─ KPIs (4 tiles grandes) ──────────┐  │
│  👥 Usuarios                       │  │ 👥 2.847   🐾 4.291   💰 12.340€  │  │
│  🐾 Mascotas                       │  │ Usuarios   Mascotas   Ingresos mes │  │
│  💊 Sanitario                      │  │                                    │  │
│  🛒 Marketplace                    │  │ 📦 347     💬 892                  │  │
│  📦 Pedidos                        │  │ Pedidos    Matches                 │  │
│  📍 Lugares                        │  └────────────────────────────────────┘  │
│  ⭐ Reseñas                        │                                          │
│  ──────────────────────────────    │  ┌─ Gráfica ingresos (línea) ────────┐  │
│  ⚙️  Configuración                 │  │  Chart.js · últimos 12 meses       │  │
│                                    │  │  [Área rellena con gradiente accent]│  │
│  ──────────────────────────────    │  └────────────────────────────────────┘  │
│  [ Cerrar sesión ]                 │                                          │
│                                    │  ┌─ Actividad reciente ───────────────┐  │
│                                    │  │ Tabla: Acción | Usuario | Fecha     │  │
│                                    │  │ + Nuevo usuario: Carlos G.  hace 5m │  │
│                                    │  │ 📦 Pedido #348 → Enviado    hace 8m │  │
│                                    │  │ 📍 Nuevo lugar sugerido     hace 1h │  │
│                                    │  └────────────────────────────────────┘  │
│                                    │                                          │
│                                    │  ┌─ Pedidos pendientes ──────────────┐  │
│                                    │  │ Tabla: ID | Cliente | Estado | Acción│ │
│                                    │  │ #345 | Ana M. | 🟡 Pendiente | [→]  │  │
│                                    │  │ #346 | Luis R. | 🟠 En camino | [→] │  │
│                                    │  └────────────────────────────────────┘  │
└────────────────────────────────────┴──────────────────────────────────────────┘
```

**Vista Admin — Gestión de Usuarios (`/admin/users`):**
```
[ Buscar usuario... ]  [ Filtrar: Todos | Activos | Bloqueados ▼ ]  [ Exportar CSV ]

Tabla:
| Avatar+Nombre    | Email            | Rol    | Mascotas | Registro   | Estado  | Acciones   |
|─────────────────|─────────────────|────────|────────|───────────|──────---|───────────|
| [F] Carlos G.   | carlos@...      | Usuario| 2      | Ene 2026   | 🟢 Activo| [Ver][⋮]  |
| [F] Ana M.      | ana@...         | Admin  | 1      | Feb 2026   | 🟢 Activo| [Ver][⋮]  |
| [F] Luis R.     | luis@...        | Usuario| 0      | Mar 2026   | 🔴 Bloqueado|[Ver][⋮]|

Paginación: [ ← ] 1 2 3 ... 48 [ → ]   Mostrando 1–20 de 956
```

---

## Sección 3 — Instrucciones de Renderizado para la IA

```
Al generar cada wireframe, sigue estas reglas:

1. ESTILO: Utiliza el sistema de diseño definido en la Sección 1 exactamente.
   No inventes colores ni tipografías nuevas.

2. FORMATO: Genera wireframes de alta fidelidad (high-fidelity), no bocetos de cajas grises.
   Usa colores reales, tipografía correcta y proporciones exactas.

3. VIEWPORT:
   - Vista desktop: 1440×900px (diseña a 1280px de contenido central con márgenes)
   - Vista mobile (si se pide): 390×844px (iPhone 14)
   - La navbar en mobile se convierte en hamburger menu que despliega drawer lateral

4. INTERACTIVIDAD: Muestra estados hover, focus y active en los componentes interactivos
   relevantes de cada vista.

5. CONTENIDO: Usa datos ficticios pero realistas:
   - Nombres de perros: Rex, Nala, Bruno, Max, Luna, Simba
   - Razas: Labrador, Golden Retriever, Pastor Alemán, Bulldog, Gato Europeo
   - Ciudades: Madrid, Barcelona, Valencia, Sevilla
   - Precios en euros, fechas en 2026

6. CONSISTENCIA: El logo "🐾 PawConnect" con el ícono de huella debe aparecer en todas
   las vistas. La navbar y el footer son idénticos en todas las páginas públicas.

7. PRIORIDAD: Si solo puedes generar una vista, genera el DASHBOARD (Vista 2) primero,
   ya que es la más representativa de la identidad visual de la app.

8. ACCESIBILIDAD: Asegura contraste mínimo WCAG AA. Los textos sobre fondos oscuros
   son blancos (#FFFFFF). Los textos sobre fondos claros son #1A1A2E.
```

---

## Sección 4 — Prompt de Ejemplo para Usar con la IA

```
Prompt listo para copiar y pegar:

---
Eres un diseñador UI/UX experto. Genera un wireframe de alta fidelidad para
la vista [NOMBRE DE VISTA] de la aplicación web PawConnect, siguiendo estrictamente
el sistema de diseño del archivo wireframes_prompt_ia.md.

Requisitos:
- Viewport: 1440px desktop
- Colores: exactamente los definidos en la Sección 1.1
- Tipografía: Plus Jakarta Sans + Inter
- Layout: según la especificación de la Vista [NÚMERO] de la Sección 2
- Muestra estados: normal, hover, focus donde corresponda
- Usa datos de ejemplo realistas (perros, precios en €, fechas 2026)
- El resultado debe parecer una captura de pantalla real de una app profesional,
  no un wireframe de cajas grises
---
```

---

*Documento generado para el proyecto PawConnect — Fase 1.5*
*Compatible con: Figma AI, Midjourney, DALL-E 3, Adobe Firefly, v0 by Vercel, Galileo AI*
