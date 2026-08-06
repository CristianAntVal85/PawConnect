# 🐾 PawConnect Backend

**Laravel 11 API + Frontend Blade** · API REST con autenticación Sanctum, CRUD de mascotas, chat en tiempo real y marketplace.

---

## 🚀 Inicio Rápido

### Requisitos
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

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
