# PawConnect 🐾

Sistema web integral para conectar dueños de mascotas, gestionar salud veterinaria, marketplace y lugares pet-friendly.

## 📁 Estructura del Proyecto

```
pawconnect/
├── doc/                          # Documentación del proyecto
│   ├── 01-requisitos.md         # Especificación de requisitos
│   ├── 02-casos_de_uso.md       # Casos de uso
│   ├── 03-ER_diagrama.md        # Diagrama Entidad-Relación
│   ├── 04-UML_clases.md         # Diagrama de clases
│   ├── 05-API_endpoints.md      # Endpoints API
│   ├── 06-plan.md               # Plan de desarrollo
│   ├── 07-wireframes_prompt_ia.md
│   └── vistas_wireframes/       # Wireframes visibles
│
├── backend/                      # Backend Laravel 11
│   ├── app/                     # Lógica de aplicación
│   ├── routes/                  # Rutas API y web
│   ├── database/                # Migraciones y seeders
│   ├── .env.local              # Variables locales (NO en Git)
│   ├── artisan                  # CLI de Laravel
│   └── composer.json
│
├── frontend/                     # Frontend (pendiente)
│
└── README.md                     # Este archivo
```

## 🚀 Configuración Inicial

### Requisitos
- Docker y Docker Compose
- O bien: PHP 8.2+, Composer y MySQL/MariaDB (local)

### Instalación con Docker ✅ (Recomendado)

1. **Clonar el repositorio**
   ```bash
   git clone <tu-repo>
   cd pawconnect
   ```

2. **Levantar servicios Docker**
   ```bash
   docker compose up -d
   ```
   Esto levanta:
   - **app** (Laravel) en http://localhost:8000
   - **mysql** en localhost:3306

3. **Ejecutar migraciones**
   ```bash
   ./artisan migrate
   ```

4. ✅ **Listo** — Accede en http://localhost:8000

### Instalación Local (Sin Docker)

1. **Instalar dependencias backend**
   ```bash
   cd backend
   composer install
   ```

2. **Configurar .env.local**
   - Copiar `.env.example` a `.env.local`
   - Generar APP_KEY: `php artisan key:generate --show`
   - Ajustar credenciales MySQL

3. **Crear base de datos**
   ```bash
   mysql -u root -e "CREATE DATABASE pawconnect;"
   ```

4. **Generar APP_KEY y ejecutar migraciones**
   ```bash
   cd backend
   php artisan key:generate
   php artisan migrate
   php artisan serve
   ```
   
   Accede en: http://localhost:8000

## 🛠️ Comandos Útiles

### Con Docker (recomendado)
```bash
./artisan migrate              # Crear tablas en BD
./artisan make:model Pet -mfR  # Generar modelo + factory + resource
./artisan tinker               # Terminal interactiva
./artisan test                 # Ejecutar tests
```

### Logs
```bash
docker compose logs app -f     # Logs de la app (live)
docker compose logs mysql -f   # Logs de MySQL
docker compose down            # Detener servicios
```

### Shell directo
```bash
docker compose exec app bash   # Terminal dentro del contenedor app
docker compose exec mysql mysql -u root -proot pawconnect  # MySQL CLI
```

Ver [DOCKER.md](DOCKER.md) para más comandos.

### Local (sin Docker)
```bash
cd backend

# Generar migraciones
php artisan make:migration create_<table>_table

# Generar modelos
php artisan make:model <Model> -mfR

# Ejecutar migraciones
php artisan migrate

# Revertir migraciones
php artisan migrate:rollback

# Tests
php artisan test
```

## 📋 Fases del Proyecto

| Fase | Objetivo | Estado |
|------|----------|--------|
| 1 | Documentación base | ✅ Completa |
| 2 | Backend + BD + Auth | 🔄 En progreso |
| 3 | API completa | ⏳ Pendiente |
| 4 | Frontend Blade | ⏳ Pendiente |
| 5 | Match + Chat + Paseos | ⏳ Pendiente |
| 6 | Sanitario + Marketplace | ⏳ Pendiente |
| 7 | Lugares pet-friendly | ⏳ Pendiente |
| 8 | Panel Admin | ⏳ Pendiente |
| 9 | Deploy + Seguridad | ⏳ Pendiente |

## 📚 Documentación

Ver carpeta `doc/` para especificaciones detalladas.

## 🔐 Seguridad

- `.env.local` no debe subirse a Git
- Usar Laravel Sanctum para API
- Implementar CORS para apps móviles
- Rate limiting en endpoints públicos

## 📞 Contacto

Tu información aquí.
