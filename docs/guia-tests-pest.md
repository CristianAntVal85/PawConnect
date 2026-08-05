# 🧪 Breve Guía de Tests con Pest

**Para estudiantes de DAW** · Nivel: Básico

---

## ¿Qué son los tests y por qué son importantes?

**Los tests son código que verifica que tu código funciona correctamente.**

Imagina que estás construyendo una casa:
- ❌ **Sin tests**: construyes y esperas que no se caiga
- ✅ **Con tests**: verificas que cada pared aguanta peso antes de seguir

### Beneficios reales

1. **Confianza** — Sabes que tus cambios no rompen nada
2. **Documentación viva** — Los tests muestran cómo se usa cada funcionalidad
3. **Refactorización segura** — Puedes mejorar el código sin miedo
4. **Detección temprana** — Los bugs se descubren antes de producción

---

## Pest vs PHPUnit

Laravel incluye PHPUnit por defecto, pero **Pest es mejor para aprender**:

### PHPUnit (tradicional):
```php
public function test_user_can_login(): void
{
    $user = User::factory()->create();
    $response = $this->postJson('/api/login', [...]);
    $response->assertOk();
}
```

### Pest (moderno):
```php
test('un usuario puede iniciar sesión', function () {
    $user = User::factory()->create();
    $response = $this->postJson('/api/login', [...]);
    $response->assertOk();
});
```

**Ventajas de Pest:**
- ✅ Sintaxis más clara y legible
- ✅ Menos código repetitivo
- ✅ Funciones helper globales
- ✅ Mejor para tests en español

---

## Tipos de tests

### 1. Tests Unitarios (`tests/Unit/`)
Prueban **una función o clase** aislada.

**Ejemplo:**
```php
test('calcula el precio total correctamente', function () {
    $order = new Order();
    $total = $order->calculateTotal(10.50, 2); // precio, cantidad
    expect($total)->toBe(21.0);
});
```

### 2. Tests de Funcionalidad (`tests/Feature/`)
Prueban **flujos completos** (peticiones HTTP, base de datos, etc.).

**Ejemplo:**
```php
test('un usuario puede crear una mascota', function () {
    actingAsUser(); // Helper que autentica un usuario
    
    $response = $this->postJson('/api/v1/pets', [
        'species' => 'dog',
        'name' => 'Max',
    ]);
    
    $response->assertCreated(); // HTTP 201
    $this->assertDatabaseHas('pets', ['name' => 'Max']);
});
```

**Regla general:** En proyectos web, el 80% son tests de funcionalidad.

---

## Estructura de un test

Todos los tests siguen el patrón **AAA**:

### 1. **Arrange** (Preparar)
Configura el escenario (crea usuarios, datos, etc.)

### 2. **Act** (Actuar)
Ejecuta la acción que quieres probar

### 3. **Assert** (Afirmar)
Verifica que el resultado es el esperado

**Ejemplo completo:**
```php
test('un usuario NO puede editar mascotas ajenas', function () {
    // ARRANGE
    $owner = User::factory()->create();
    $intruder = actingAsUser(); // Usuario autenticado diferente
    $pet = Pet::factory()->create(['user_id' => $owner->id]);
    
    // ACT
    $response = $this->putJson("/api/v1/pets/{$pet->id}", [
        'name' => 'Hackeo',
    ]);
    
    // ASSERT
    $response->assertForbidden(); // HTTP 403
    $this->assertDatabaseHas('pets', [
        'id' => $pet->id,
        'name' => $pet->name, // El nombre NO cambió
    ]);
});
```

---

## Assertions más comunes

### Respuestas HTTP
```php
$response->assertOk();           // 200
$response->assertCreated();      // 201
$response->assertUnauthorized(); // 401
$response->assertForbidden();    // 403
$response->assertNotFound();     // 404
$response->assertUnprocessable(); // 422 (errores de validación)
```

### JSON
```php
$response->assertJson(['name' => 'Max']);
$response->assertJsonStructure(['data' => ['id', 'name']]);
$response->assertJsonCount(3, 'data'); // Array con 3 elementos
```

### Base de datos
```php
$this->assertDatabaseHas('pets', ['name' => 'Max']);
$this->assertDatabaseMissing('pets', ['id' => $petId]);
$this->assertDatabaseCount('pets', 5); // Hay 5 registros
```

### Valores con `expect()`
```php
expect($valor)->toBe(10);
expect($nombre)->toContain('Max');
expect($array)->toHaveCount(3);
expect($token)->not->toBeEmpty();
```

---

## RefreshDatabase: clave en tests

**Problema:** Si creas datos en un test, se quedan en la base de datos y afectan a otros tests.

**Solución:** El trait `RefreshDatabase`

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('crea una mascota', function () {
    // La base de datos se limpia automáticamente antes de este test
    actingAsUser();
    $this->postJson('/api/v1/pets', ['name' => 'Max']);
    $this->assertDatabaseCount('pets', 1);
});

test('otro test independiente', function () {
    // La base de datos está limpia de nuevo
    $this->assertDatabaseCount('pets', 0);
});
```

**¿Qué hace?**
- Antes de cada test: ejecuta migraciones frescas
- Después de cada test: revierte todos los cambios
- Resultado: cada test empieza con BD limpia

---

## Helpers personalizados

En `tests/Pest.php` definimos funciones globales:

```php
function actingAsUser(): \App\Models\User
{
    $user = \App\Models\User::factory()->create();
    \Laravel\Sanctum\Sanctum::actingAs($user);
    return $user;
}

function actingAsAdmin(): \App\Models\User
{
    $admin = \App\Models\User::factory()->create(['role' => 'admin']);
    \Laravel\Sanctum\Sanctum::actingAs($admin);
    return $admin;
}
```

**Uso:**
```php
test('solo admins pueden ver todos los usuarios', function () {
    actingAsAdmin(); // Una línea en vez de 2
    $this->getJson('/api/v1/admin/users')->assertOk();
});
```

---

## Factories: generar datos de prueba

En vez de crear datos manualmente, usa **factories**:

```php
// ❌ Manual (tedioso)
$user = new User();
$user->name = 'Test User';
$user->email = 'test@test.com';
$user->password = bcrypt('password');
$user->save();

// ✅ Con factory (rápido)
$user = User::factory()->create();

// ✅ Con datos personalizados
$user = User::factory()->create([
    'email' => 'custom@test.com',
    'role' => 'admin',
]);

// ✅ Crear varios
$pets = Pet::factory()->count(5)->create(['species' => 'dog']);
```

---

## Ejemplo real: AuthTest.php explicado

```php
test('un usuario puede registrarse con datos válidos', function () {
    // ARRANGE: preparar datos
    $data = [
        'name' => 'Test User',
        'email' => 'test@pawconnect.test',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ];
    
    // ACT: hacer la petición
    $response = $this->postJson('/api/v1/auth/register', $data);
    
    // ASSERT: verificar resultados
    $response->assertCreated(); // HTTP 201
    $response->assertJsonStructure([
        'token',
        'user' => ['id', 'name', 'email'],
    ]);
    
    // Verificar que el token no está vacío
    expect($response->json('token'))->not->toBeEmpty();
    
    // Verificar que el usuario se guardó en BD
    $this->assertDatabaseHas('users', [
        'email' => 'test@pawconnect.test',
    ]);
});
```

---

## Ejecutar tests

### Todos los tests
```bash
php artisan test
```

### Un archivo específico
```bash
php artisan test --filter=PetTest
```

### Un test específico
```bash
php artisan test --filter="un usuario puede crear una mascota"
```

### Ver más detalles
```bash
php artisan test --verbose
```

---

## Flujo de trabajo con tests

### Desarrollo con TDD (Test-Driven Development)

1. **Escribe el test** (todavía falla, el código no existe)
2. **Escribe el código mínimo** para que pase
3. **Refactoriza** si es necesario
4. **Repite**

### Desarrollo normal (tests después)

1. **Escribe la funcionalidad**
2. **Escribe tests** para cubrir casos normales
3. **Añade tests** para casos de error (validaciones, permisos, etc.)
4. **Ejecuta tests** antes de cada commit

---

## Tests en PawConnect

### AuthTest.php (6 tests)
- ✅ Registro exitoso
- ✅ Registro con errores de validación
- ✅ Login exitoso
- ✅ Login con credenciales incorrectas
- ✅ Logout exitoso
- ✅ Logout sin autenticación

### PetTest.php (9 tests)
- ✅ Invitados no pueden acceder
- ✅ Usuario crea su mascota
- ✅ Validación de campos requeridos
- ✅ Validación de peso máximo
- ✅ Listar solo mascotas propias
- ✅ Actualizar mascota propia
- ✅ NO actualizar mascota ajena (Policy)
- ✅ Eliminar mascota propia
- ✅ NO eliminar mascota ajena

### HealthTest.php (12 tests)
- ✅ Crear registro sanitario
- ✅ Cálculo automático de next_due_date
- ✅ No crear en mascotas ajenas
- ✅ Listar registros propios
- ✅ No ver registros ajenos
- ✅ Actualizar registro
- ✅ Eliminar registro
- ✅ Validaciones varias

### OrderTest.php (11 tests)
- ✅ Crear pedido
- ✅ Cálculo automático de total_price
- ✅ No crear pedidos con mascotas ajenas
- ✅ Listar solo pedidos propios
- ✅ Ver detalle de pedido propio
- ✅ No ver pedidos ajenos
- ✅ Validaciones (campos requeridos, cantidad mínima, etc.)

---

## Interpretando resultados

### ✅ Test pasa
```
✓ un usuario puede crear una mascota  0.05s
```
**Significado:** La funcionalidad funciona correctamente.

### ❌ Test falla
```
⨯ un usuario puede listar solo sus propias mascotas  0.09s
Failed to assert that the response count matched the expected 3
```
**Significado:** El endpoint devuelve más mascotas de las esperadas. Probablemente falta filtrar por `user_id`.

**Esto es BUENO** — el test detectó un bug antes de que llegue a producción.

---

## Errores comunes

### 1. "SQLSTATE: Field doesn't have a default value"
**Problema:** Tu factory no tiene todos los campos requeridos.
**Solución:** Añade el campo al factory.

### 2. "Expected 201 but received 422"
**Problema:** La validación está fallando.
**Solución:** Revisa qué campos faltan en `$response->json('errors')`.

### 3. "Expected 403 but received 200"
**Problema:** La Policy no está funcionando.
**Solución:** Verifica que el Policy esté registrado en `AuthServiceProvider`.

### 4. Tests pasan individualmente pero fallan en conjunto
**Problema:** No estás usando `RefreshDatabase`.
**Solución:** Añade `uses(RefreshDatabase::class);` al inicio.

---

## Resumen: conceptos clave

1. **Tests = código que verifica código**
2. **Pest = sintaxis moderna y clara**
3. **Feature tests = pruebas de flujo completo** (lo más común en web)
4. **AAA = Arrange, Act, Assert** (estructura de un test)
5. **RefreshDatabase = BD limpia en cada test**
6. **Factories = generar datos de prueba fácilmente**
7. **Helpers = funciones globales para tests** (`actingAsUser()`)
8. **`php artisan test` = ejecutar todos los tests**

---

## Próximos pasos

1. ✅ Entender qué son los tests (ya lo sabes)
2. ⏭️ Escribir más tests para otros endpoints
3. ⏭️ Aprender coverage (% de código cubierto)
4. ⏭️ Tests de integración (varios módulos juntos)
5. ⏭️ CI/CD (ejecutar tests automáticamente en cada commit)

---

**Regla de oro:** Si una funcionalidad no tiene tests, eventualmente se romperá y nadie lo sabrá hasta que un usuario lo reporte.

**¡Ahora tienes 38 tests escritos que validan las funcionalidades principales de PawConnect!** 🎉
