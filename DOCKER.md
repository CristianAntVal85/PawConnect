# Docker - PawConnect 🐳

## ✅ STATUS: Completamente funcional

- ✅ Contenedor app: Running (HTTP 200)
- ✅ MySQL: Conectado
- ✅ Dockerfile: Single-stage (49 líneas)
- ✅ DevContainer: Listo para VSCode
- ✅ Limpio: Solo 1 Dockerfile (sin duplicados)

## 📋 Estructura del Proyecto

```
pawconnect/
├── docker-compose.yml           ← Orquestación de servicios
├── .devcontainer/
│   └── devcontainer.json       ← Config VSCode DevContainer
├── backend/
│   ├── Dockerfile              ← ÚNICO (49 líneas, single-stage)
│   ├── .env.local              ← DB_HOST=mysql
│   ├── artisan
│   ├── app/, bootstrap/, storage/
│   ├── composer.json
│   └── package.json
```

## 🐳 Stack Docker

**Services**: 
- `app`: PHP 8.2-Apache (puerto 8000:80)
- `mysql`: MySQL 8.0 (puerto 3306)

**Key Configuration**:
- Context: `.` (raíz del proyecto)
- Dockerfile: `./backend/Dockerfile`
- Volume: `./backend:/app`
- DB_HOST: `mysql` (nombre del servicio, NO 127.0.0.1)

## 🚀 Inicio Rápido

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

## 💻 Desarrollo en VSCode (DevContainer)

**Opción recomendada para equipo**: 
1. Abre VSCode desde `/home/cristian-pc/proyectos/pawconnect` (raíz del proyecto)
2. `F1` → `Dev Containers: Reopen in Container`
3. Espera 2-3 min (primera vez)
4. VSCode abre automáticamente en `/app` (dentro del contenedor)
5. Terminal integrada está en `/app`
6. `postCreateCommand` ejecuta: `composer install && npm install`
7. ✅ Listo para codificar

**Qué sucede**:
- `workspaceMount`: Monta `./backend` del proyecto local en `/app` del contenedor
- `workspaceFolder`: VSCode abre en `/app` automáticamente
- Todos los cambios se sincronizan en tiempo real (hot reload)

## Reconstruir imagen (después de cambios en Dockerfile)

```bash
docker compose down
docker image rm pawconnect-app
docker compose up -d --build
```

## 🔑 Variables Críticas

| Variable | Valor | Por qué |
|----------|-------|--------|
| `WORKDIR` | `/app` | Donde vive Laravel |
| `APACHE_DOCUMENT_ROOT` | `/app/public` | Donde Apache sirve archivos |
| `DB_HOST` | `mysql` | Nombre del servicio Docker |
| `COPY backend/` | `/app/` | Solo backend (no raíz) |
| `chown -R www-data` | `/app` | Apache necesita permisos |

**CRÍTICO**: `DB_HOST=mysql` no `127.0.0.1` (dentro de contenedor, `127.0.0.1` = el contenedor mismo)

## ✅ Verificación (Checklist)

```bash
# 1. Contenedores corriendo?
docker compose ps
→ pawconnect-app ✅ | pawconnect-mysql ✅

# 2. App responde?
curl -I http://localhost:8000
→ HTTP/1.1 200 OK ✅

# 3. Base de datos conecta?
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo()
→ Sin excepciones ✅

# 4. Usuario vscode existe?
docker compose exec app id vscode
→ uid=1000(vscode) groups=33(www-data) ✅

# 5. Permisos correctos?
docker compose exec app ls -la /app/storage
→ drwxrwx--- www-data www-data ✅
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
# Cambiar en docker-compose.yml: "8001:80" en lugar de "8000:80"
```

**Permission denied en storage/:**
```bash
docker compose exec app chown -R www-data:www-data /app
docker compose exec app chmod -R 775 /app/storage /app/bootstrap/cache
```

**"Connection refused 127.0.0.1:3306":**
```bash
# Cambiar en .env.local: DB_HOST=mysql (no 127.0.0.1)
```

## 📄 Archivos Importantes

**backend/.env.local** (local development):
```env
APP_ENV=local
APP_DEBUG=true
DB_HOST=mysql              # ← CRÍTICO (nombre del servicio)
DB_PORT=3306
DB_DATABASE=pawconnect
DB_USERNAME=pawconnect
DB_PASSWORD=pawconnect
```

**backend/Dockerfile** (49 líneas):
- Base: `php:8.2-apache`
- Instala: Composer, Node.js 18
- Apache document root: `/app/public`
- Usuario vscode con sudo (para DevContainer)
- Permisos: `www-data:www-data` en `/app`

**.devcontainer/devcontainer.json**:
- Service: `app`
- Workspace: `/app/backend`
- Remote user: `vscode`
- postCreateCommand: `composer install && npm install`
```

**BD no existe:**
```bash
docker compose exec app php artisan migrate
```
