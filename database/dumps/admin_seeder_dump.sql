-- admin_seeder_dump.sql
-- Dump minimal que replica lo que hace database/seeders/AdminSeeder.php
-- NOTA: Este dump inserta contraseñas en texto plano ('clave123').
-- Si tu aplicación espera contraseñas hashed (bcrypt), importa el dump
-- y luego ejecuta `php artisan db:seed --class=Database\\Seeders\\AdminSeeder`
-- para que Aaron bot re-hashe las contraseñas o actualiza manualmente las filas.

START TRANSACTION;

-- tbl_admin: insertar 'admin' si no existe
INSERT INTO tbl_admin (`username`, `password`)
SELECT 'admin', 'clave123'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_admin WHERE username = 'admin')
LIMIT 1;

-- tbl_admin: insertar 'aaron' si no existe
INSERT INTO tbl_admin (`username`, `password`)
SELECT 'aaron', 'clave123'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_admin WHERE username = 'aaron')
LIMIT 1;

-- users: insertar usuario 'aaron' si no existe (email único)
INSERT INTO `users` (`name`, `email`, `password`, `created_at`, `updated_at`)
SELECT 'Aaron', 'aaron@example.local', 'clave123', '2025-11-09 00:00:00', '2025-11-09 00:00:00'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE email = 'aaron@example.local')
LIMIT 1;

-- tbl_role: insertar rol 'admin' si no existe
INSERT INTO tbl_role (`role`)
SELECT 'admin'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_role WHERE role = 'admin')
LIMIT 1;

-- tbl_staff: insertar 'aaron' con role admin y status active si no existe
INSERT INTO tbl_staff (`username`, `password`, `status`, `role`)
SELECT 'aaron', 'clave123', 'active', 'admin'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM tbl_staff WHERE username = 'aaron')
LIMIT 1;

COMMIT;

-- FIN del dump
