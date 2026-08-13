# 🐾 PawConnect Backend

**Laravel 11 API + Frontend Blade** · API REST con autenticación Sanctum, CRUD de mascotas, chat en tiempo real y marketplace.

---

## 🚀 Inicio Rápido

### Requisitos
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+
- Extensiones PHP: mbstring, xml, curl, zip, pdo_mysql

### Instalación

```bash
# Instalar dependencias PHP
composer install

# Instalar dependencias frontend (desde este directorio)
npm install

# Crear archivo .env
cp .env.example .env
php artisan key:generate

# Base de datos
php artisan migrate --seed

# Cache recomendada para entorno local
php artisan config:clear
php artisan cache:clear
```

### Configuración de .env (mínima)

```env
APP_NAME=PawConnect
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pawconnect
DB_USERNAME=pawconnect
DB_PASSWORD=pawconnect

SANCTUM_STATEFUL_DOMAINS=localhost:8000,127.0.0.1:8000
SANCTUM_EXPIRATION=87600

# CORS (web + móvil)
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://127.0.0.1:3000,http://10.0.2.2:3000,capacitor://localhost,ionic://localhost
```

### Desarrollo

```bash
# Terminal 1: Servidor Laravel
php artisan serve

# Terminal 2: Vite (compilar assets + hot reload)
cd backend && npm run dev
# O si no estás en backend/:
npm run dev --prefix backend
```

---

## ⚠️ Importante: Frontend Assets

**El frontend Vite/Tailwind vive en la carpeta `backend/`**, no en la raíz del proyecto.

Siempre ejecuta comandos npm desde `backend/`:
```bash
cd backend && npm run dev
cd backend && npm run build
```

O usa `--prefix` desde cualquier lugar:
```bash
npm run dev --prefix backend
npm run build --prefix backend
```

---

## 📚 Comandos Principales

```bash
# Tests
php artisan test

# Documentación API
php artisan scribe:generate
# Luego abre: http://localhost:8000/docs

# Base de datos
php artisan migrate:fresh --seed   # resetear BBDD con datos
php artisan db:seed                # solo seeders

# Colas y scheduler
php artisan queue:work
php artisan schedule:work

# Utilidades
php artisan route:list
php artisan optimize:clear

# Control de código
php artisan tinker                 # REPL interactivo
```

---

## 📂 Estructura

```
app/
  Http/
    Controllers/Api/    ← Endpoints REST
    Controllers/Web/    ← Vistas Blade
  Models/               ← ORM Eloquent
  Services/             ← Lógica de negocio
  Policies/             ← Autorización

routes/
  api.php               ← Rutas /api/v1/*
  web.php               ← Rutas Blade

resources/
  views/               ← Plantillas Blade
  css/
  js/

tests/
  Feature/
  Unit/
```

---

## 🔗 Documentación

- **Plan detallado:** ver `../docs/06-plan.md`
- **Endpoints API:** ver `../docs/05-API_endpoints.md`
- **Diseño ER:** ver `../docs/03-ER_diagrama.md`
- **Tests:** ver `../docs/guia-tests-pest.md`

### Artefactos generados por Scribe

- Documentación web: `/docs`
- Vista Blade generada: `resources/views/scribe/index.blade.php`
- Postman Collection: `storage/app/private/scribe/collection.json`
- OpenAPI Spec: `storage/app/private/scribe/openapi.yaml`
