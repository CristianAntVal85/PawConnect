# 🚀 Guía de Despliegue Seguro - PawConnect

Esta guía detalla los pasos críticos para desplegar PawConnect de forma segura en producción.

## Pre-requisitos

- Servidor con Ubuntu 22.04 LTS o superior
- Nginx o Apache configurado
- PHP 8.2+ con extensiones: mbstring, xml, pdo_mysql, gd, curl, zip
- MySQL 8.0+ o MariaDB 10.6+
- Certificado SSL válido (Let's Encrypt recomendado)
- Supervisor para gestión de procesos

## Checklist de Seguridad Pre-Deploy

### 1. Variables de Entorno

Crear archivo `.env` en producción con estos valores **CRÍTICOS**:

```env
# CRÍTICO: Nunca dejar en true en producción
APP_ENV=production
APP_DEBUG=false

# Generar nueva key única para producción
APP_KEY=base64:GENERAR_CON_php_artisan_key:generate

# URL real del sitio
APP_URL=https://pawconnect.com

# Database - credenciales únicas de producción
DB_DATABASE=pawconnect_production
DB_USERNAME=pawconnect_prod_user
DB_PASSWORD=CONTRASEÑA_SEGURA_AQUÍ

# Sanctum - configurar dominios reales
SANCTUM_STATEFUL_DOMAINS=pawconnect.com,app.pawconnect.com
SANCTUM_EXPIRATION=43200  # 30 días en producción

# Stripe - usar claves LIVE
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...

# Session y Cache - usar Redis en producción
SESSION_DRIVER=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=CONTRASEÑA_REDIS
REDIS_PORT=6379

# Mail - configurar servicio real
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@pawconnect.com
MAIL_PASSWORD=CONTRASEÑA_MAIL
MAIL_ENCRYPTION=tls
```

### 2. Configuración del Servidor Web

#### Nginx (Recomendado)

```nginx
# /etc/nginx/sites-available/pawconnect.com
server {
    listen 80;
    server_name pawconnect.com www.pawconnect.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name pawconnect.com www.pawconnect.com;
    root /var/www/pawconnect/public;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/pawconnect.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/pawconnect.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256';
    ssl_prefer_server_ciphers off;
    
    # Security Headers (ya configurados en Laravel, pero reforzamos)
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Logs
    access_log /var/log/nginx/pawconnect-access.log;
    error_log /var/log/nginx/pawconnect-error.log;

    # Index
    index index.php index.html;

    # Character set
    charset utf-8;

    # Max upload size
    client_max_body_size 20M;

    # Laravel configuration
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Block access to sensitive files
    location ~ /\.env {
        deny all;
    }

    location ~ /storage {
        deny all;
    }
}
```

### 3. Permisos de Archivos

```bash
# Propietario: usuario del servidor web
sudo chown -R www-data:www-data /var/www/pawconnect

# Permisos correctos
sudo find /var/www/pawconnect -type f -exec chmod 644 {} \;
sudo find /var/www/pawconnect -type d -exec chmod 755 {} \;

# Storage y bootstrap/cache escribibles
sudo chmod -R 775 /var/www/pawconnect/storage
sudo chmod -R 775 /var/www/pawconnect/bootstrap/cache

# CRÍTICO: .env solo lectura para propietario
sudo chmod 600 /var/www/pawconnect/.env
```

### 4. Optimizaciones de Producción

```bash
cd /var/www/pawconnect

# Instalar dependencias de producción (sin dev)
composer install --no-dev --optimize-autoloader

# Cachear configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimizar autoloader
composer dump-autoload -o

# Compilar assets
npm ci
npm run build
```

### 5. Supervisor para Workers

Crear `/etc/supervisor/conf.d/pawconnect-worker.conf`:

```ini
[program:pawconnect-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/pawconnect/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/pawconnect/storage/logs/worker.log
stopwaitsecs=3600
```

Activar:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start pawconnect-worker:*
```

### 6. Cron para Scheduler

```bash
sudo crontab -e -u www-data

# Agregar línea:
* * * * * cd /var/www/pawconnect && php artisan schedule:run >> /dev/null 2>&1
```

### 7. Firewall

```bash
# Habilitar solo puertos necesarios
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP (redirige a HTTPS)
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
sudo ufw status
```

### 8. Base de Datos

```sql
-- Crear usuario con permisos mínimos
CREATE DATABASE pawconnect_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pawconnect_prod'@'localhost' IDENTIFIED BY 'CONTRASEÑA_SEGURA';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX, ALTER, DROP ON pawconnect_production.* TO 'pawconnect_prod'@'localhost';
FLUSH PRIVILEGES;
```

### 9. Migraciones en Producción

```bash
# Backup de base de datos ANTES de migrar
mysqldump -u root -p pawconnect_production > backup_$(date +%Y%m%d_%H%M%S).sql

# Ejecutar migraciones
php artisan migrate --force

# Si hay seeders necesarios (solo una vez)
php artisan db:seed --class=ProductSeeder --force
```

### 10. SSL con Let's Encrypt

```bash
# Instalar certbot
sudo apt install certbot python3-certbot-nginx

# Obtener certificado
sudo certbot --nginx -d pawconnect.com -d www.pawconnect.com

# Auto-renovación (verificar que esté activa)
sudo systemctl status certbot.timer
```

## Verificación Post-Deploy

### Checklist de Validación

- [ ] `https://pawconnect.com` carga correctamente
- [ ] HTTP redirige automáticamente a HTTPS
- [ ] Headers de seguridad presentes (verificar con https://securityheaders.com)
- [ ] Login funciona correctamente
- [ ] Registro de nuevos usuarios funciona
- [ ] Subida de fotos funciona
- [ ] Emails se envían correctamente
- [ ] Notificaciones en tiempo real funcionan (WebSocket)
- [ ] Jobs se procesan (verificar logs de Supervisor)
- [ ] Scheduler ejecuta tareas (verificar cron logs)
- [ ] Rate limiting funciona (intentar > 60 requests/min)
- [ ] SSL es válido (https://www.ssllabs.com/ssltest/)

### Comandos de Verificación

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Ver status de workers
sudo supervisorctl status

# Ver cola de jobs
php artisan queue:monitor

# Verificar configuración
php artisan config:show

# Test de email
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('admin@pawconnect.com')->subject('Test'); });
```

## Monitoreo

### Logs Críticos

```bash
# Laravel
/var/www/pawconnect/storage/logs/laravel.log

# Nginx
/var/log/nginx/pawconnect-error.log
/var/log/nginx/pawconnect-access.log

# Supervisor
/var/www/pawconnect/storage/logs/worker.log

# MySQL
/var/log/mysql/error.log
```

### Alertas Recomendadas

1. **Espacio en disco < 10%**
2. **CPU > 80% por 5 minutos**
3. **Memoria > 90%**
4. **Errores 500 > 10/minuto**
5. **Workers inactivos**
6. **Base de datos inaccesible**

## Backup

### Script de Backup Diario

```bash
#!/bin/bash
# /usr/local/bin/pawconnect-backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/pawconnect"

# Crear directorio si no existe
mkdir -p $BACKUP_DIR

# Backup de base de datos
mysqldump -u pawconnect_prod -p'CONTRASEÑA' pawconnect_production | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup de archivos subidos
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz /var/www/pawconnect/storage/app/public

# Backup del .env
cp /var/www/pawconnect/.env $BACKUP_DIR/env_$DATE

# Mantener solo últimos 7 días
find $BACKUP_DIR -type f -mtime +7 -delete

# Log
echo "Backup completado: $DATE" >> $BACKUP_DIR/backup.log
```

Agregar a cron:
```bash
0 2 * * * /usr/local/bin/pawconnect-backup.sh
```

## Rollback Plan

Si algo falla después del deploy:

```bash
# 1. Revertir código
cd /var/www/pawconnect
git reset --hard COMMIT_ANTERIOR

# 2. Revertir migraciones (si es necesario)
php artisan migrate:rollback --step=1

# 3. Restaurar backup de base de datos
mysql -u root -p pawconnect_production < backup_FECHA.sql

# 4. Limpiar cachés
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 5. Reiniciar servicios
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
sudo supervisorctl restart pawconnect-worker:*
```

## Soporte

**Documentación adicional:**
- [SECURITY.md](/backend/SECURITY.md) - Detalles de configuración de seguridad
- [README.md](/backend/README.md) - Documentación general del proyecto

**Logs y debugging:**
- En caso de error 500, revisar `storage/logs/laravel.log`
- Para problemas de permisos, verificar propietario con `ls -la`
- Para problemas de cache, ejecutar `php artisan optimize:clear`
