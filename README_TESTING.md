Prueba rápida - Cómo ejecutar la aplicación localmente (Windows / PowerShell)

Requisitos previos
- PHP 8.x compatible con la versión de Laravel incluida
- Composer
- Node.js + npm (opcional, si quieres compilar assets)

Pasos rápidos (PowerShell) — en la raíz del proyecto `laravel_full`:

1. Crear el archivo SQLite (ya creado en esta sesión):

   New-Item -ItemType File -Path database\database.sqlite -Force

2. Copiar `.env` (si no existe) o usar el ejemplo y ajustar si necesitas otra DB:

   cp .env.example .env
   # En PowerShell puedes usar Copy-Item: Copy-Item .env.example .env

3. Instalar dependencias PHP:

   composer install

4. Generar clave de aplicación (si no existe):

   php artisan key:generate

5. Ejecutar migraciones y seeders (esto crea tablas y datos de prueba):

   php artisan migrate --force
   php artisan db:seed --force

6. (Opcional) Instalar dependencias JS y compilar assets:

   npm install
   npm run build

7. Iniciar el servidor local:

   php artisan serve

Credenciales de prueba (semilla creada):
- Admin: admin@example.com / password

Rutas útiles:
- Panel Admin: http://127.0.0.1:8000/admin (necesita login como admin)
- Panel Staff: http://127.0.0.1:8000/staff (necesita login con rol staff)

Notas
- La base de datos usada en estos pasos es SQLite (`database/database.sqlite`) por simplicidad. Si prefieres MySQL, actualiza `.env` y crea la base de datos antes de migrar.
- Si algo falla, copia aquí el error y lo depuramos; puedo ejecutar los comandos por ti si me pides que los ejecute en esta sesión.

Resumen de lo que hice ahora
- Creé `database/database.sqlite`.
- Ejecuté `php artisan migrate` y `php artisan db:seed` en esta sesión — seeders insertaron datos de prueba (admin, menus, items, staff, orders).
