# Comandos Docker para PawConnect 🐳

## Inicio rápido

```bash
# Levantar servicios (desde raíz del proyecto)
docker compose up -d

# Ver estado de contenedores
docker compose ps

# Detener servicios
docker compose down
```

## Comandos Artisan dentro de Docker

```bash
# Ejecutar artisan dentro del contenedor
docker compose exec app php artisan <comando>

# Ejemplos:
docker compose exec app php artisan --version
docker compose exec app php artisan migrate
docker compose exec app php artisan tinker
docker compose exec app php artisan make:model <Model> -mfR
```

## Base de datos

### Crear base de datos y tablas
```bash
docker compose exec app php artisan migrate
```

### Revertir migraciones
```bash
docker compose exec app php artisan migrate:rollback
```

### Seed (datos de prueba)
```bash
docker compose exec app php artisan db:seed
```

## Logs

```bash
# Ver logs de la app
docker compose logs app -f

# Ver logs de MySQL
docker compose logs mysql -f

# Ver logs de ambos
docker compose logs -f
```

## Acceso directo

### Shell del contenedor app
```bash
docker compose exec app bash
```

### MySQL desde terminal
```bash
docker compose exec mysql mysql -u root -proot pawconnect
```

## Reconstruir imagen (después de cambios en Dockerfile)

```bash
docker compose down
docker image rm pawconnect-app
docker compose up -d --build
```

## URLs

- **App:** http://localhost:8000
- **MySQL:** localhost:3306

## Credenciales

- **MySQL User:** pawconnect
- **MySQL Password:** pawconnect
- **MySQL Database:** pawconnect

## Troubleshooting

**Contenedor no inicia:**
```bash
docker compose logs app
```

**Puerto en uso:**
```bash
# Cambiar puerto en docker-compose.yml
# Ejemplo: "8001:8000" en lugar de "8000:8000"
```

**BD no existe:**
```bash
docker compose exec app php artisan migrate
```
