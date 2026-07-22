<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Aaron bot

Aaron bot is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Aaron bot takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

## Arquitectura técnica de Aaron bot

Aaron bot está construido sobre Laravel 12 y PHP 8.2, con el esqueleto estándar de Laravel y una mezcla de componentes modernos y tablas legacy.

### Puntos clave de implementación

- Autenticación y autorización gestionadas con Laravel Breeze / Laravel auth.
- Middleware personalizado `App\Http\Middleware\EnsureUserHasRole` para controlar roles: `admin`, `mesero`, `cocina_barra`.
- Rutas agrupadas por prefijo y nombre en `routes/web.php` para separar claramente las áreas de `admin` y `staff`.
- Gestión de órdenes y mesas dentro de `app/Http/Controllers/Staff/` y administración en `app/Http/Controllers/Admin/`.
- Lógica de negocio que combina Eloquent con consultas directas mediante `DB::table(...)`, especialmente para trabajar con tablas legacy como `tbl_order`, `tbl_orderdetail`, `tbl_menuitem`, `sales_history` y `tables`.

### Flujo principal de la aplicación

- `admin` gestiona menú, items de menú, personal y órdenes archivadas.
- `staff` crea, edita y consulta órdenes, calcula totales, maneja estado de cocina y cobra mesas.
- Las órdenes activas permanecen en `tbl_order`, mientras que las ventas cerradas se archivan en `sales_history`.
- El estado de `kitchen` se actualiza con endpoints especializados y la vista KDS usa sesiones para ocultar temporalmente pedidos.
- Hay endpoints en tiempo real con caché de 1 segundo (`orders-counts`, `orders-metrics`) para dashboards activos.

### Modelo de datos y validación

- Modelos Eloquent como `Order`, `OrderDetail`, `Menu`, `MenuItem`, `Table`, `ExchangeRate` existen, pero muchas operaciones usan query builder para compatibilidad con el esquema legacy.
- Las solicitudes críticas usan `FormRequest` para validación, por ejemplo `StoreOrderRequest`, `StoreMenuRequest`, `StoreMenuItemRequest`.
- `StoreOrderRequest` normaliza entradas legacy como `customer_table` a `table_number` antes de validar.

### Tecnologías y herramientas

- Laravel 12
- PHP 8.2
- Vite + Tailwind CSS (según configuración de `vite.config.js` y `tailwind.config.js`)
- Composer para dependencias PHP
- NPM para assets y frontend

### Mejoras recomendadas

- Migrar más lógica de negocio fuera de rutas y controladores hacia servicios o repositorios.
- Reforzar el uso de Eloquent y relaciones en lugar de consultas directas constantes con `DB::table(...)`.
- Consolidar la gestión de datos legacy (`tbl_order`, `tbl_orderdetail`, `tbl_menuitem`, `sales_history`) en modelos y adaptadores de datos.
- Añadir pruebas funcionales para flujos de órdenes, cobro de mesas y control de roles.
- Centralizar la validación y garantizar que todos los endpoints críticos usen `FormRequest`.

Laravel es accesible, poderoso y proporciona herramientas requeridas para aplicaciones grandes y robustas.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
