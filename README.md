# Smart Academic Deadline (Laravel)

Aplikasi ini ialah versi Laravel untuk projek `Smart Academic Deadline` dengan:
- Backend API Laravel (`/api/courses`, `/api/tasks`, dll)
- Frontend Vue + Vite (layout dan logic sama seperti versi asal)
- Database MariaDB/MySQL (`courses`, `tasks`)

## 1. Keperluan

- PHP 8.3+
- Composer 2+
- Node.js 18+ dan npm
- MariaDB/MySQL
- Extension PHP: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `ctype`, `json`, `fileinfo`

## 2. Setup Local

```bash
cd Smart-Academic-Deadline
composer install
npm install
cp .env.example .env
php artisan key:generate
```

## 3. Setup Database

### Pilihan A: Import SQL siap

```bash
mysql -u root -p < Smart-Academic-Deadline/database/schema.sql
```

SQL ini akan:
- create database `akademik` (jika belum ada)
- create table `courses` dan `tasks`

### Pilihan B: Guna migration Laravel

Pastikan database `akademik` sudah ada. Jika belum ada, create dulu:

```sql
CREATE DATABASE IF NOT EXISTS `akademik`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Kemudian:

```bash
cd Smart-Academic-Deadline
php artisan migrate
```

## 4. Konfigurasi `.env`

Contoh minimum:

```env
APP_NAME="Smart Academic Deadline"
APP_ENV=local
APP_KEY=base64:generated_key_here
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=akademik
DB_USERNAME=root
DB_PASSWORD=your_db_password
```

Untuk production, guna:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.com`

## 5. Run Local

Terminal 1 (Laravel server):

```bash
cd Smart-Academic-Deadline
php artisan serve
```

Terminal 2 (Vite dev):

```bash
cd Smart-Academic-Deadline
npm run dev
```

Akses: `http://127.0.0.1:8000`

## 6. Build Production (Local)

```bash
cd Smart-Academic-Deadline
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

## 7. File/Folder Yang Perlu Upload (Shared Hosting/VPS)

Upload semua dalam folder `Smart-Academic-Deadline/` kecuali yang tak perlu.

Perlu upload:
- `app/`
- `bootstrap/`
- `config/`
- `database/`
- `public/` (termasuk `public/build/`)
- `resources/`
- `routes/`
- `storage/`
- `vendor/`
- `artisan`
- `composer.json`
- `composer.lock`
- `.env`

Tak perlu upload:
- `node_modules/`
- `.git/`
- fail temporary/editor

## 8. Permission Penting

Pastikan writable:

```bash
cd /var/www/Smart-Academic-Deadline
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 9. Final Step Lepas Deploy

```bash
cd /var/www/Smart-Academic-Deadline
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jika update frontend:
- rebuild `npm run build`
- upload semula `public/build`

## 9.1 Rebuild Di VPS (Selepas Tukar Code Frontend)

```bash
cd /var/www/Smart-Academic-Deadline
npm install
npm run build
php artisan view:clear
php artisan route:clear
php artisan config:clear
```

Lepas siap:
- hard refresh browser: `Ctrl/Cmd + Shift + R`
- jika guna reverse proxy/CDN, purge cache sekali

## 10. API Ringkas

- `GET /api/health`
- `GET /api/courses`
- `POST /api/courses`
- `PUT /api/courses/{id}`
- `DELETE /api/courses/{id}`
- `GET /api/tasks`
- `POST /api/tasks`
- `PUT /api/tasks/{id}`
- `PATCH /api/tasks/{id}/status`
- `DELETE /api/tasks/{id}`
