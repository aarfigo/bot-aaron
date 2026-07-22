# Plan de migración: Legacy PHP -> Laravel

Fecha: 2025-11-08

Objetivo
- Migrar la aplicación legacy (procedural PHP) a una arquitectura Laravel organizada (routes -> controllers -> models -> views).

Alcance inicial (prioridad)
1. Autenticación y roles (login, staff/admin) — necesario para control de acceso.
2. Gestión de menú (CRUD de menús y items) — core del negocio.
3. Creación y edición de pedidos (front y staff) — flujo clave: crear pedido, añadir items, cambiar estado.
4. Panel de cocina (ver pedidos en preparación/ready) — real-time/refresh posterior.
5. Reportes y ventas (exportar ordenes) — baja prioridad inicial.

Mapeo de tablas (de `fosdb.sql`)
- `tbl_admin` -> puede mapearse a `users` con role=admin.
- `tbl_staff` -> mapear a `users` con role=staff (o mesero/cocina_barra según `role` columna).
- `tbl_menu`, `tbl_menuitem` -> `menus`, `menu_items` (migrations ya creadas con prefijo `tbl_` para compatibilidad).
- `tbl_order`, `tbl_orderdetail` -> `orders`, `order_items`.
- `tbl_role` -> puede normalizarse a roles fijos (seeders existentes).

Rutas legacy principales -> Controladores Laravel sugeridos
- /admin/index.php, /admin/login.php -> AuthController (Breeze ya instalado). Mantener `Admin\` namespace para rutas administrativas.
- /admin/menu.php, additem.php, edititem.php -> Admin\MenuController (index, create, store, edit, update, destroy)
  - FormRequest: `StoreMenuItemRequest` con reglas: menuID (exists:tbl_menu,menuID), menuItemName (required|string), price (numeric|min:0)
- /staff/insertorder.php, displayitem.php -> Staff\OrderController (create, store) + Api endpoints para añadir items vía AJAX
  - FormRequest: `StoreOrderRequest` con reglas: items array, quantities, optional comments
- /staff/kitchen.php, editstatus.php -> Staff\KitchenController (index for kitchen, changeStatus route)

Validaciones y seguridad
- Centralizar validación con FormRequest para cada endpoint que reciba datos POST/PUT.
- Reemplazar todas las consultas concatenadas por Eloquent/Query Builder con bindings.
- Añadir CSRF (ya cubierto por Blade + middleware) y sanitizar outputs en las vistas.
- Autorización: middleware `role` (ya añadimos `EnsureUserHasRole`). Considerar `spatie/laravel-permission` para roles complejos.

Plan de migración en fases (acciones concretas)
Fase 0 - Preparación
  - Ejecutar migraciones y seeders (hecho).
  - Instalar Breeze y crear `users` (hecho).

Fase 1 - Auth & roles
  - Implementar proceso de migración de cuentas legacy -> users (crear Artisan command `sync:legacy-users`) y ejecutarlo.
  - Verificar login con cuentas migradas.

Fase 2 - Core CRUD (mínimo viable)
  - Implementar `Admin\MenuController` + views para CRUD.
  - Implementar `Staff\OrderController` para crear pedidos y `Staff\KitchenController` para ver pedidos por estado.
  - Tests: PHPUnit tests para MenuController (store validation) y OrderController (happy path).

Fase 3 - API/AJAX
  - Reimplementar endpoints AJAX como `routes/api.php` con controllers `Api\OrderApiController`.
  - Añadir JSON responses consistentes y status codes.

Fase 4 - Hardening y mejoras
  - Añadir roles con Spatie si se desea granularidad.
  - Revisar CSRF, XSS, rate-limiting y logging.
  - Añadir tests de integración y CI (GitHub Actions) para `phpunit` y `phpstan`/`psalm`.

Notas técnicas y decisiones rápidas
- Para compatibilidad rápida con la DB legacy mantenemos nombres `tbl_*` en migrations; al estabilizar renombrar a convenciones Laravel.
- Importante: los historiales de contraseña legacy pueden estar en texto claro; en el import hay que detectar si la contraseña ya está hasheada y, si no, aplicar `Hash::make`.

Siguiente paso inmediato
- Implementar y ejecutar el comando Artisan `sync:legacy-users` para poblar `users` desde `tbl_admin` y `tbl_staff`. Luego probar login con `admin@example.com` y/o cuentas migradas.

---
Archivo generado automáticamente por las tareas de migración. Mantener y actualizar según avances.

## Observaciones recientes (2025-11-09)

- Se añadió la columna `kitchen_cleared` como columna booleana en `tbl_order`.
- Algunas migraciones dependían del orden de ejecución en entornos de tests locales (SQLite). Para evitar errores al ejecutar la suite de tests se aplicaron comprobaciones adicionales en las migraciones que alteran tablas: ahora las migraciones verifican `Schema::hasTable('tbl_order')` antes de intentar `Schema::table(...)->addColumn()` o `dropColumn()`.
- Recomendación: mantener el orden lógico de migraciones (crear tablas antes de alterarlas) o agrupar alteraciones como parte de la migración de creación cuando sea posible. Añadir estas notas a la documentación de despliegue y los scripts de CI para evitar errores en entornos limpios.

