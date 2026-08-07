# Guía Responsive Design — PawConnect

**Versión:** 1.0  
**Breakpoints:** Mobile (375px) → Tablet (768px) → Desktop (1280px)  
**Framework:** TailwindCSS 3.4 + Mobile-First Approach

---

## 📱 Breakpoints Utilizados

| Prefix | Viewport | Uso |
|--------|----------|-----|
| (none) | 0px+ | Mobile first (estilos base) |
| `sm:` | 640px+ | Tablets pequeños |
| `md:` | 768px+ | Tablets |
| `lg:` | 1024px+ | Desktops |
| `xl:` | 1280px+ | Desktops grandes |

---

## 🎯 Estrategia Mobile-First

**Regla de Oro:** Escribir primero para móvil, luego agregar breakpoints para pantallas más grandes.

### ❌ Incorrecto (Desktop-First)
```html
<div class="hidden lg:block">...</div>  <!-- Oculto hasta desktop -->
```

### ✅ Correcto (Mobile-First)
```html
<div class="block lg:hidden">...</div>  <!-- Visible en móvil, oculto en desktop -->
```

---

## 🔤 Tipografía Responsiva

```html
<!-- Tamaño de fuente escalable -->
<h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl">Título</h1>

<!-- Espacio escalable -->
<p class="text-xs sm:text-sm md:text-base lg:text-lg">Párrafo</p>

<!-- Línea escalable -->
<div class="leading-5 sm:leading-6 md:leading-7">Texto con altura de línea responsive</div>
```

---

## 🔘 Componentes Responsive

### Botones

**Mobile:** Pequeños, más espacio para tocar
**Desktop:** Tamaño normal con texto visible

```html
<!-- Botón Responsive -->
<button class="
    px-2 py-1.5 sm:px-4 sm:py-2
    text-xs sm:text-sm
    rounded-lg
    bg-blue-500 hover:bg-blue-600
    transition duration-200
">
    <svg class="w-4 h-4 sm:w-5 sm:h-5">...</svg>
    <span class="hidden sm:inline">Texto</span>
</button>
```

**Breakpoints:**
- `px-2 py-1.5` → Móvil (pequeño, compacto)
- `sm:px-4 sm:py-2` → Tablet+ (más grande)
- `text-xs sm:text-sm` → Texto más pequeño en móvil
- `hidden sm:inline` → Ocultar texto en móvil

### Formularios

```html
<form class="space-y-4 sm:space-y-5">
    <div>
        <label class="mb-1 block text-xs sm:text-sm font-medium">Email</label>
        <input class="w-full px-3 py-2 sm:py-2.5 text-sm">
    </div>
</form>
```

### Grillas

```html
<!-- 1 columna en móvil, 2 en tablet, 3 en desktop -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
    <div>Tarjeta 1</div>
    <div>Tarjeta 2</div>
    <div>Tarjeta 3</div>
</div>
```

---

## 📐 Espaciado Responsivo

```html
<!-- Padding escalable -->
<div class="p-3 sm:p-4 md:p-6 lg:p-8">
    <!-- Móvil: p-3 (12px) -->
    <!-- Tablet: p-4 (16px) -->
    <!-- Desktop: p-8 (32px) -->
</div>

<!-- Gap escalable -->
<div class="flex gap-2 sm:gap-3 md:gap-4">
    <!-- Móvil: gap-2 (8px) -->
    <!-- Desktop: gap-4 (16px) -->
</div>

<!-- Margin escalable -->
<h1 class="mb-3 sm:mb-4 md:mb-6">Título</h1>
```

---

## 🧭 Flex Responsivo

```html
<!-- Dirección responsiva: columna en móvil, fila en desktop -->
<div class="flex flex-col sm:flex-row gap-2">
    <textarea class="flex-1">...</textarea>
    <button class="self-end sm:self-auto">Enviar</button>
</div>
```

**Casos de uso:**
- Formularios en móvil: apilados verticalmente
- Botones de acción: abajo a la derecha en móvil, al lado en desktop

---

## 📏 Sidebar Responsivo

Patrón usado en `layouts/app.blade.php`:

```html
<aside class="
    fixed inset-y-0 left-0 z-40
    w-72
    -translate-x-full  <!-- Oculto por defecto -->
    lg:static lg:translate-x-0  <!-- Visible en desktop -->
    transition-transform duration-200
">
    Contenido
</aside>

<div id="overlay" class="
    fixed inset-0 z-30
    hidden lg:hidden  <!-- Visible cuando sidebar visible -->
    bg-slate-900/35
">
</div>
```

---

## 🎨 Componentes Blade Responsive

### favorite-button.blade.php

```html
<!-- Pequeño en móvil (solo ícono) -->
<!-- Grande en desktop (ícono + texto) -->
<button class="
    px-2 py-1.5 sm:px-3 sm:py-2
    text-xs sm:text-sm
">
    <svg class="w-4 h-4 sm:w-5 sm:h-5">...</svg>
    <span class="hidden sm:inline">Guardar</span>
</button>
```

### message-form.blade.php

```html
<!-- Columna en móvil, fila en desktop -->
<form class="flex flex-col sm:flex-row gap-2">
    <textarea class="flex-1"></textarea>
    <button class="self-end sm:self-auto">Enviar</button>
</form>
```

### join-walk-button.blade.php

```html
<!-- Compacto en móvil, botón normal en desktop -->
<button class="
    px-2 py-1.5 sm:px-4 sm:py-2
    text-xs sm:text-sm
">
    <svg class="w-4 h-4 sm:w-5 sm:h-5">...</svg>
    <span class="hidden sm:inline">Unirse</span>
</button>
```

---

## 🔍 Testing Breakpoints

### En DevTools del navegador

1. Abrir DevTools (F12)
2. Toggle Device Toolbar (Ctrl+Shift+M)
3. Seleccionar dispositivo o ingresar dimensiones personalizadas

### Tamaños Recomendados

```
iPhone SE:       375px × 667px
iPhone 14:       390px × 844px
Galaxy S21:      360px × 800px
iPad Pro:        768px × 1024px
Desktop:         1280px × 720px
```

### Checklist de Prueba

- [ ] Móvil 375px: Botones tocables, texto legible, sin overflow
- [ ] Tablet 768px: Layouts balanceados, menú visible
- [ ] Desktop 1280px: Máximo ancho respetado (max-w-7xl)
- [ ] Orientación: Portrait y Landscape
- [ ] Touch: Botones con tamaño mínimo 44×44px
- [ ] Readability: Líneas texto < 65 caracteres

---

## 📋 Patrones Comunes

### Hidden en Móvil

```html
<div class="hidden sm:block">Solo en desktop</div>
<div class="hidden lg:block">Solo en desktop grande</div>
```

### Mostrado solo en Móvil

```html
<div class="block sm:hidden">Solo en móvil</div>
```

### Texto Responsivo

```html
<p class="text-sm sm:text-base md:text-lg">Escalable</p>
```

### Flex con Dirección Responsiva

```html
<div class="flex flex-col sm:flex-row">
    <div>Item 1</div>
    <div>Item 2</div>
</div>
```

### Grid Responsivo

```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
    <!-- 1 col móvil, 2 col tablet, 3 col desktop -->
</div>
```

---

## ⚠️ Errores Comunes

### ❌ No ocultar menú en móvil

```html
<!-- INCORRECTO -->
<div class="block">Menú largo</div>
```

### ✅ Ocultar en móvil, mostrar en desktop

```html
<!-- CORRECTO -->
<div class="hidden lg:flex">Menú</div>
```

### ❌ Botones muy pequeños

```html
<!-- INCORRECTO: imposible de tocar en móvil -->
<button class="px-1 py-0.5">X</button>
```

### ✅ Botones con tamaño mínimo

```html
<!-- CORRECTO: mínimo 44px de altura -->
<button class="px-2 py-2 sm:px-4 sm:py-2.5">
    <!-- 20px móvil, 32px desktop -->
</button>
```

---

## 📚 Recursos

- **TailwindCSS Responsive**: https://tailwindcss.com/docs/responsive-design
- **Mobile-First Approach**: https://www.nngroup.com/articles/mobile-first-web-design/
- **Touch Target Size**: https://www.smashingmagazine.com/2020/12/usability-design-guidelines/

---

## ✅ Checklist Implementación

- [x] Componentes de botones responsive (favorite, join-walk)
- [x] Formularios responsive (message-form)
- [x] Vistas auth responsive (login, register)
- [x] Layout sidebar responsive (hamburguesa en móvil)
- [x] Tipografía escalable en todas las vistas
- [x] Espaciado adaptativo
- [x] Testing en breakpoints 375px, 768px, 1280px
- [ ] Optimizar imágenes para móvil (srcset)
- [ ] Performance mobile (Lighthouse)

---

**Última actualización:** 2026-08-07
