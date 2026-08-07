# Guía de JavaScript - API Wrapper y AJAX sin Recarga

**Versión:** 1.0  
**Ubicación:** `resources/js/`  
**Compilado:** `public/build/`

---

## 📋 Índice de Módulos

| Módulo | Archivo | Propósito |
|--------|---------|----------|
| **API Base** | `api.js` | Wrapper de fetch con autenticación Sanctum |
| **Favoritos** | `favorites.js` | Guardar/eliminar lugares sin recarga |
| **Mensajes** | `messages.js` | Enviar/recibir mensajes en tiempo real |
| **Paseos** | `walks.js` | Unirse/abandonar paseos sin recarga |

---

## 🔧 Módulo `api.js` - Wrapper Central

Todos los módulos lo utilizan. Proporciona:

### Funciones Exportadas

```javascript
// Métodos HTTP
apiGet(endpoint)           // GET /api/v1{endpoint}
apiPost(endpoint, body)    // POST con body JSON
apiPut(endpoint, body)     // PUT con body JSON
apiDelete(endpoint)        // DELETE
apiPatch(endpoint, body)   // PATCH

// Gestión de tokens
setAuthToken(token)        // Guardar token (después de login)
clearAuthToken()           // Limpiar token (logout)
isAuthenticated()          // Verificar si está autenticado

// Manejo de errores
getErrorMessage(error)     // Parsear mensaje de error para usuario
```

### Uso desde JavaScript Puro

```javascript
// Importar en vistas Blade (ya está en window.API)
const { apiPost, getErrorMessage } = window.API;

// GET
const pets = await window.API.apiGet('/pets');
console.log(pets.data); // Array de mascotas

// POST
const newPet = await window.API.apiPost('/pets', {
    name: 'Max',
    species: 'dog',
});

// Manejo de errores
try {
    await window.API.apiPost('/order', { /* ... */ });
} catch (error) {
    const message = window.API.getErrorMessage(error);
    alert(message); // Muestra al usuario
}
```

### Headers Automáticos

✅ Agrega automáticamente:
- `Authorization: Bearer {token}` (si está autenticado)
- `Content-Type: application/json`
- `Accept: application/json`
- `X-CSRF-Token` (si existe en `<meta>`)

---

## ❤️ Módulo `favorites.js` - Guardar Lugares

### Funciones Disponibles

```javascript
addFavorite(placeId)                    // Guardar lugar
removeFavorite(placeId)                 // Eliminar de favoritos
toggleFavorite(placeId, isFavorited)    // Toggle
handleFavoriteClick(button, placeId, isFavorited) // Helper UI
```

### Componente Blade

```blade
<x-favorite-button :place-id="$place->id" :is-favorited="$isFavorited" />
```

### Uso Manual en Blade

```html
<button onclick="handleFav(this, 15, false)">
    Guardar lugar
</button>

<script>
function handleFav(btn, placeId, isFav) {
    window.Favorites.handleFavoriteClick(btn, placeId, isFav);
}
</script>
```

### Flujo

1. Usuario hace click
2. Botón se deshabilita (feedback visual)
3. API recibe `POST /api/v1/places/{id}/favorites`
4. ✅ Ícono cambia de gris a rojo
5. ❌ Si falla: muestra alerta, reactiva botón

---

## 💬 Módulo `messages.js` - Chat sin Recarga

### Funciones Disponibles

```javascript
sendMessage(matchId, body)              // Envía mensaje
markMessageAsRead(matchId, messageId)   // Marca leído
fetchMessages(matchId, options)         // Obtiene mensajes
handleMessageSubmit(form, matchId, container) // Helper formulario
```

### Componente Blade (Recomendado)

```blade
<x-message-form :match-id="$match->id" :user-id="auth()->id()" />
```

Incluye:
- Textarea para escribir
- Botón enviar
- Contenedor de mensajes

### Uso Manual

```html
<form id="msgForm">
    <textarea name="body" placeholder="Tu mensaje..."></textarea>
    <button type="submit">Enviar</button>
</form>

<div id="messages"></div>

<script>
const form = document.getElementById('msgForm');
const container = document.getElementById('messages');
const matchId = 5;

form.addEventListener('submit', (e) => {
    e.preventDefault();
    window.Messages.handleMessageSubmit(form, matchId, container);
});
</script>
```

### Respuesta Esperada

```javascript
// POST /api/v1/matches/{matchId}/messages
// Body: { body: "Hola!" }

// Respuesta:
{
    "success": true,
    "data": {
        "id": 42,
        "match_id": 5,
        "sender_id": 1,
        "body": "Hola!",
        "created_at": "2026-08-07T15:30:00Z"
    }
}
```

---

## 🚶 Módulo `walks.js` - Paseos sin Recarga

### Funciones Disponibles

```javascript
joinWalk(walkId, petId)                 // Unirse a paseo
leaveWalk(walkId)                       // Abandonar
toggleWalkParticipation(walkId, petId, isJoined) // Toggle
handleWalkToggleClick(button, walkId, petId, isJoined) // Helper UI
getWalkParticipants(walkId)             // Obtener participantes
updateParticipantCount(container, walkId) // Actualizar contador
```

### Componente Blade

```blade
<x-join-walk-button 
    :walk-id="$walk->id" 
    :pet-id="$userPet->id" 
    :is-joined="$isUserInWalk" 
/>
```

### Uso Manual

```html
<button onclick="toggleWalk(this, 10, 3, false)">
    Unirse al paseo
</button>

<div id="count">0/8 participantes</div>

<script>
async function toggleWalk(btn, walkId, petId, isJoined) {
    await window.Walks.handleWalkToggleClick(btn, walkId, petId, isJoined);
    
    // Actualizar contador
    window.Walks.updateParticipantCount(
        document.getElementById('count'),
        walkId
    );
}
</script>
```

### Endpoints Esperados

```
POST   /api/v1/walks/{walkId}/join      { pet_id: 3 }
DELETE /api/v1/walks/{walkId}/leave
GET    /api/v1/walks/{walkId}
```

---

## 🔐 Flujo de Autenticación

### 1. En Login/Registro

```blade
<!-- Guardar token después de login exitoso -->
<script>
const response = await window.API.apiPost('/auth/login', {
    email: 'user@example.com',
    password: 'secret'
});

// Guardar token
window.API.setAuthToken(response.data.token);

// Redirigir al dashboard
window.location.href = '/dashboard';
</script>
```

### 2. En Logout

```javascript
window.API.clearAuthToken();
window.location.href = '/';
```

### 3. Verificar Estado

```javascript
if (window.API.isAuthenticated()) {
    // Mostrar opciones para usuario autenticado
} else {
    // Redirigir a login
    window.location.href = '/login';
}
```

---

## ⚠️ Manejo de Errores

### getErrorMessage() parsea:

```javascript
// 1. Respuesta con .message
{ "message": "Email ya existe" }

// 2. Errores de validación
{
    "errors": {
        "email": ["Email inválido"],
        "password": ["Debe tener 8+ caracteres"]
    }
}

// 3. Errores HTTP estándar
// 401 → "No autorizado. Por favor inicia sesión."
// 403 → "No tienes permiso para realizar esta acción."
// 404 → "Recurso no encontrado."
// 500 → "Error del servidor. Intenta más tarde."
```

### Ejemplo

```javascript
try {
    await window.API.apiPost('/pets', data);
} catch (error) {
    const msg = window.API.getErrorMessage(error);
    document.getElementById('errorAlert').textContent = msg;
}
```

---

## 📱 Ejemplo Completo: Cargar Mascotas

```blade
<div id="pets" class="grid gap-4">
    {{-- Los pets se cargarán aquí --}}
</div>

<script>
async function loadPets() {
    try {
        const response = await window.API.apiGet('/pets');
        
        const container = document.getElementById('pets');
        container.innerHTML = '';
        
        response.data.forEach(pet => {
            container.innerHTML += `
                <div class="p-4 border rounded-lg">
                    <h3 class="font-bold">${pet.name}</h3>
                    <p class="text-gray-600">${pet.species} - ${pet.breed}</p>
                </div>
            `;
        });
    } catch (error) {
        alert(window.API.getErrorMessage(error));
    }
}

// Cargar al abrir la página
document.addEventListener('DOMContentLoaded', loadPets);
</script>
```

---

## 🎯 Casos de Uso Listos

✅ **Guardar lugar favorito** → Usa `<x-favorite-button>`  
✅ **Enviar mensaje** → Usa `<x-message-form>`  
✅ **Unirse a paseo** → Usa `<x-join-walk-button>`  
✅ **Custom AJAX** → Importa del módulo que necesites  

---

## 🔗 Recursos

- **Plan:** [06-plan.md](06-plan.md) Sección 4.7
- **Endpoints API:** [API_endpoints.md](05-API_endpoints.md)
- **Documentación Interactiva:** `/docs` (cuando esté en producción)
