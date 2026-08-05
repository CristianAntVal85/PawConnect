# 📚 Guía de Scribe: Documentación Automática de APIs

**Para estudiantes de DAW** · Nivel: Iniciación

---

## 🎯 ¿Qué es Scribe y por qué lo necesitas?

Imagina que has creado una API (una interfaz para que otras aplicaciones se comuniquen con tu proyecto). Ahora necesitas que otros desarrolladores sepan **cómo usarla**:

- ¿Qué rutas existen?
- ¿Qué parámetros acepta cada ruta?
- ¿Qué devuelve cada endpoint?
- ¿Cómo autenticarse?

**Scribe** es una herramienta que **genera automáticamente toda esa documentación** leyendo tu código Laravel. Es como tener un asistente que crea un manual de usuario de tu API sin que tengas que escribir nada (o casi nada).

---

## 🆚 ¿Scribe vs otras alternativas?

| Herramienta | Ventaja | Desventaja |
|---|---|---|
| **Scribe** | 🟢 Automático, moderno, bonito | 🟡 Solo para Laravel/PHP |
| **Swagger/OpenAPI manual** | 🟢 Estándar universal | 🔴 Tienes que escribir YAML a mano |
| **Postman manual** | 🟢 Fácil de probar | 🔴 Se desactualiza rápido |
| **Sin documentación** | 🔴 Nada | 🔴 Nadie usará tu API |

**Conclusión:** Scribe es la mejor opción para proyectos Laravel porque hace el 80% del trabajo por ti.

---

## 🧠 Conceptos básicos que debes entender

### 1. **¿Qué es una API REST?**

Es un conjunto de rutas (URLs) que responden con datos (normalmente JSON) en lugar de HTML.

**Ejemplo:**
```
GET  /api/v1/pets      → Devuelve lista de mascotas
POST /api/v1/pets      → Crea una nueva mascota
GET  /api/v1/pets/15   → Devuelve la mascota con ID 15
```

### 2. **¿Qué es un endpoint?**

Cada ruta de tu API. Por ejemplo: `GET /api/v1/pets` es un endpoint.

### 3. **¿Qué es la documentación de API?**

Un sitio web donde puedes ver:
- Lista de todos los endpoints
- Qué hace cada uno
- Qué parámetros necesita
- Ejemplos de petición y respuesta
- **Incluso probarlos desde el navegador** (Try It Out)

---

## 🔧 ¿Cómo funciona Scribe internamente?

Scribe hace **3 cosas** cuando ejecutas `php artisan scribe:generate`:

### Paso 1: **Escanea tus rutas**
Lee el archivo `routes/api.php` y encuentra todas las rutas que comienzan con `/api/*`.

### Paso 2: **Analiza cada endpoint**
Por cada ruta:
- Lee el **controller** asociado
- Lee las **Form Requests** (reglas de validación)
- Lee los **comentarios PHPDoc** (`/** */`)
- Ejecuta una petición de prueba y captura la respuesta

### Paso 3: **Genera la documentación**
Crea:
- Una página web interactiva (`/docs`)
- Un archivo Postman Collection (`.json`)
- Un archivo OpenAPI Spec (`.yaml`)

---

## 📦 Instalación y configuración (ya hecho en PawConnect)

### Paso 1: Instalar Scribe
```bash
composer require knuckleswtf/scribe --dev
```

### Paso 2: Publicar la configuración
```bash
php artisan vendor:publish --tag=scribe-config
```

Esto crea el archivo `config/scribe.php` donde puedes personalizar TODO.

### Paso 3: Generar la documentación
```bash
php artisan scribe:generate
```

**¡Ya está!** Ahora puedes visitar: `http://localhost:8000/docs`

---

## ⚙️ Configuración importante (`config/scribe.php`)

### 1. **Título y descripción**
```php
'title' => config('app.name').' API Documentation',
'description' => 'API REST para conectar dueños de mascotas...',
```

Esto aparece en la portada de la documentación.

### 2. **Tipo de documentación**
```php
'type' => 'laravel',  // Blade view accesible en /docs
// 'type' => 'static', // HTML estático en public/docs
```

- **laravel**: La documentación es una vista Blade (más flexible, puedes protegerla con auth)
- **static**: Genera HTML plano (más rápido, pero menos flexible)

### 3. **Rutas a documentar**
```php
'routes' => [
    [
        'match' => [
            'prefixes' => ['api/*'],  // Solo rutas que empiecen con /api/
        ],
    ],
],
```

### 4. **Autenticación**
```php
'auth' => [
    'enabled' => true,
    'default' => true,  // Por defecto, todos los endpoints requieren auth
    'in' => 'bearer',   // Token Bearer (Sanctum)
    'name' => 'Authorization',
],
```

Esto indica que tu API usa tokens Bearer en el header `Authorization`.

### 5. **Try It Out (probar desde el navegador)**
```php
'try_it_out' => [
    'enabled' => true,  // Activa el botón "Send Request"
],
```

**Importante:** necesitas tener CORS configurado para que funcione desde el navegador.

---

## 📝 Cómo mejorar la documentación generada

Scribe hace el 80% del trabajo, pero tú puedes mejorar el 20% restante.

### 1. **Comentarios PHPDoc en controllers**

```php
/**
 * Listar todas las mascotas del usuario autenticado
 *
 * Devuelve un array paginado con todas las mascotas registradas.
 * 
 * @group Mascotas
 * @authenticated
 */
public function index()
{
    // ...
}
```

**Anotaciones útiles:**
- `@group Nombre` → Agrupa endpoints en secciones
- `@authenticated` → Marca que requiere token
- `@unauthenticated` → Marca que NO requiere token
- `@response 200 {"data": [...]}` → Ejemplo de respuesta exitosa
- `@responseFile storage/responses/pets.json` → Lee respuesta desde archivo

### 2. **Método `bodyParameters()` en Form Requests**

Este es el **secreto** para que Scribe genere descripciones detalladas.

**Ejemplo en `StorePetRequest`:**
```php
public function bodyParameters(): array
{
    return [
        'name' => [
            'description' => 'Nombre de la mascota',
            'example' => 'Max',
        ],
        'species' => [
            'description' => 'Especie: dog, cat, rabbit, bird...',
            'example' => 'dog',
        ],
        'age' => [
            'description' => 'Edad en años (0-35)',
            'example' => 3,
        ],
    ];
}
```

**¿Qué hace esto?**
- Scribe lee este método **antes** que `rules()`
- Usa las descripciones y ejemplos en la documentación
- Si no lo defines, solo extrae información básica de `rules()`

### 3. **Ejemplos de respuesta automáticos**

Scribe puede ejecutar tus endpoints y capturar las respuestas reales.

En `config/scribe.php`:
```php
'strategies' => [
    'responses' => [
        Strategies\Responses\ResponseCalls::class,  // Ejecuta peticiones reales
    ],
],
```

**Cuidado:** esto ejecuta peticiones contra tu base de datos de desarrollo. Usa `faker_seed` para resultados consistentes.

---

## 🎨 Estructura de la documentación generada

Cuando generas docs con `php artisan scribe:generate`, se crean:

### Archivos generados:

```
resources/views/scribe/       ← Vistas Blade de la documentación
public/vendor/scribe/         ← CSS, JS, imágenes
storage/app/private/scribe/
  ├── collection.json         ← Colección Postman
  └── openapi.yaml            ← Especificación OpenAPI
```

### Rutas creadas automáticamente:

```
GET /docs              → Página principal de documentación
GET /docs.postman      → Descarga colección Postman
GET /docs.openapi      → Descarga especificación OpenAPI
```

---

## 🚀 Flujo de trabajo recomendado

### Día a día (desarrollo):

1. Creas un nuevo endpoint en tu controller
2. Creas una Form Request con `bodyParameters()`
3. Añades comentarios PHPDoc al método
4. Regeneras documentación: `php artisan scribe:generate`
5. Revisas `/docs` en el navegador
6. Si algo no está bien, ajustas comentarios y vuelves a generar

### Antes de commit (buenas prácticas):

```bash
php artisan scribe:generate  # Regenera docs actualizadas
git add .
git commit -m "feat: endpoint de mascotas + docs"
```

---

## 📊 Comparativa: documentación manual vs Scribe

### Sin Scribe (manual):
```
😓 Escribir 200 líneas de YAML por cada endpoint
😓 Actualizar a mano cuando cambias un campo
😓 Los ejemplos se vuelven obsoletos
😓 Nadie quiere mantener la documentación
```

### Con Scribe (automático):
```
😎 Escribes 10 líneas de bodyParameters()
😎 Se actualiza al regenerar (30 segundos)
😎 Los ejemplos son consistentes con las reglas de validación
😎 La documentación siempre está sincronizada
```

---

## 🔍 Casos de uso reales en PawConnect

### Ejemplo 1: Endpoint de crear mascota

**Controller:** `PetController@store`
**Request:** `StorePetRequest`
**Documentación generada:** `POST /api/v1/pets`

Lo que Scribe extrae automáticamente:
- ✅ Campos requeridos: `species`, `name`
- ✅ Campos opcionales: `breed`, `age`, `weight`...
- ✅ Tipos de datos: string, integer, numeric, boolean
- ✅ Validaciones: máximo 120 caracteres, entre 0 y 35 años...
- ✅ Ejemplos realistas: "Max", "Labrador", 3 años

### Ejemplo 2: Endpoints de autenticación

```php
/**
 * Registrar nuevo usuario
 *
 * @group Autenticación
 * @unauthenticated
 */
public function register() { ... }

/**
 * Iniciar sesión
 *
 * @group Autenticación
 * @unauthenticated
 */
public function login() { ... }
```

Scribe agrupa estos endpoints en la sección "Autenticación" y los marca como públicos.

---

## 🎓 Conceptos avanzados (para más adelante)

### 1. **OpenAPI Spec (Swagger)**

Es un archivo `.yaml` estándar que describe tu API. Puedes:
- Importarlo en Swagger UI (otra herramienta de docs)
- Usarlo con herramientas de generación de código cliente
- Compartirlo con equipos frontend

### 2. **Postman Collection**

Un archivo `.json` que puedes importar en Postman con todos tus endpoints preconfigurados.

**Ventaja:** tu equipo puede probar la API sin configurar nada.

### 3. **Estrategias de extracción**

Scribe usa "estrategias" para extraer información:
- `Metadata`: Lee comentarios PHPDoc
- `UrlParameters`: Detecta `{id}` en rutas
- `BodyParameters`: Lee Form Requests
- `Responses`: Ejecuta peticiones o lee archivos

Puedes desactivar o personalizar cada estrategia en `config/scribe.php`.

---

## 🐛 Problemas comunes y soluciones

### ❌ "No bodyParameters() method found"
**Solución:** Añade el método `bodyParameters()` a tu Form Request.

### ❌ La documentación no se actualiza
**Solución:** 
```bash
php artisan scribe:generate --force  # Regenera desde cero
```

### ❌ Los ejemplos no son realistas
**Solución:** Define `example` en `bodyParameters()`.

### ❌ Error 500 al generar docs
**Solución:** Revisa `storage/logs/laravel.log`. Probablemente hay un endpoint que falla al ejecutarse.

### ❌ "Try It Out" no funciona (CORS)
**Solución:** Configura CORS en `config/cors.php`:
```php
'paths' => ['api/*'],
'allowed_origins' => ['*'],  // En desarrollo
```

---

## 📚 Recursos adicionales

### Documentación oficial
🔗 https://scribe.knuckles.wtf/laravel/

### Tutoriales recomendados
- [Documentación de APIs con Scribe](https://scribe.knuckles.wtf/laravel/getting-started)
- [Personalización avanzada](https://scribe.knuckles.wtf/laravel/advanced)

### Herramientas relacionadas
- **Postman**: Para probar APIs manualmente
- **Insomnia**: Alternativa a Postman
- **Swagger UI**: Otra forma de visualizar OpenAPI specs

---

## ✅ Resumen: Lo que debes recordar

1. **Scribe automatiza la documentación** de tu API Laravel
2. Se ejecuta con `php artisan scribe:generate`
3. Genera **3 formatos**: web interactiva, Postman, OpenAPI
4. Lee tu código: rutas, controllers, Form Requests, comentarios
5. Puedes mejorarla con `bodyParameters()` y PHPDoc
6. **Siempre regenera antes de hacer commit**
7. La documentación vive en `/docs` (configurable)

---

## 🎯 Ejercicio práctico

Para que practiques:

1. Abre `http://localhost:8000/docs` en tu navegador
2. Busca el endpoint `POST /api/v1/pets`
3. Observa cómo se ven las descripciones que añadimos en `StorePetRequest`
4. Prueba el botón "Try It Out" (necesitas un token)
5. Compara con un endpoint sin `bodyParameters()` (ejemplo: algún endpoint de messages)

**Pregunta de reflexión:** ¿Cuál es más fácil de entender para un desarrollador que no conoce tu proyecto?

---

## 📌 Próximos pasos en tu aprendizaje

1. ✅ Entender qué es Scribe (ya lo sabes)
2. ⏭️ Aprender a usar Postman para probar APIs
3. ⏭️ Entender qué es OpenAPI / Swagger
4. ⏭️ Aprender a versionar APIs (`/api/v1/`, `/api/v2/`)
5. ⏭️ Documentar APIs en aplicaciones multiplataforma (móvil + web)

---

**¡Felicidades!** Ahora sabes más sobre documentación de APIs que el 70% de los desarrolladores junior. 🎉

Usa este conocimiento para que tus proyectos sean profesionales y fáciles de usar para otros desarrolladores.
