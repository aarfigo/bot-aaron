# 🍽️ Aaron Bot - Sistema de Gestión de Cocina

Sistema integral de gestión de órdenes y operaciones para restaurantes. Diseñado para optimizar el flujo de trabajo entre camareros, cocina y personal administrativo.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php)
![Tailwind CSS](https://img.shields.io/badge/Tailwind%20CSS-3.x-38B2AC?style=flat-square&logo=tailwind-css)

---

## 📋 Descripción

**Aaron Bot** es una aplicación web moderna para la gestión completa de restaurantes y establecimientos de comida. Facilita la comunicación en tiempo real entre el personal de servicio, la cocina y el área administrativa, permitiendo:

- Creación y seguimiento de órdenes
- Gestión de mesas y clientes
- Control de inventario y menú
- KDS (Kitchen Display System) integrado
- Dashboard de métricas y ventas
- Gestión de usuarios con roles diferenciados

---

## 🎯 Características Principales

### Para Personal de Cocina
- **KDS en Tiempo Real**: Pantalla de visualización de órdenes pendientes
- **Priorización Automática**: Marcado de órdenes por estado de cocina
- **Historial de Pedidos**: Acceso a órdenes completadas del día

### Para Camareros/Meseros
- **Gestión de Mesas**: Crear, editar y consultar órdenes activas
- **Cálculo de Totales**: Sistema automático de precios y descuentos
- **Cobro de Mesas**: Procesar pagos y generar recibos
- **Estado de Órdenes**: Seguimiento en tiempo real de cocina

### Para Administración
- **Panel de Control**: Dashboards con métricas de ventas
- **Gestión de Menú**: CRUD completo de artículos y categorías
- **Gestión de Personal**: Usuarios, roles y permisos
- **Historial de Ventas**: Reportes archivados y análisis
- **Tipo de Cambio**: Gestión de moneda para pagos internacionales

---

## 🏗️ Arquitectura Técnica

### Stack Tecnológico
- **Backend**: Laravel 12 con PHP 8.2
- **Frontend**: Blade, Tailwind CSS, Vite
- **Base de Datos**: MySQL/MariaDB (compatible con esquemas legacy)
- **Package Manager**: Composer (PHP) y NPM (Frontend)

### Estructura del Proyecto
```
bot-aaron/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/              # Controladores administrativos
│   │   │   ├── Staff/              # Controladores de personal
│   │   │   └── Middleware/         # Middleware de autenticación y roles
│   │   └── Requests/               # Form Requests de validación
│   ├── Models/                     # Modelos Eloquent
│   └── Services/                   # Servicios de negocio
├── routes/
│   └── web.php                     # Definición de rutas
├── resources/
│   ├── views/                      # Plantillas Blade
│   └── css/                        # Estilos y Tailwind
├── database/
│   ├── migrations/                 # Migraciones de BD
│   └── seeds/                      # Datos iniciales
├── public/                         # Assets compilados
└── config/                         # Configuración de aplicación
```

### Componentes Clave

#### Autenticación y Autorización
- Laravel Breeze para autenticación
- Middleware personalizado `EnsureUserHasRole` para control de acceso
- Roles: `admin`, `mesero`, `cocina_barra`

#### Gestión de Órdenes
- Modelos: `Order`, `OrderDetail`, `MenuItem`, `Menu`
- Validación con `StoreOrderRequest`
- Normalización de datos legacy

#### Datos Legacy
- Compatibilidad con tablas existentes: `tbl_order`, `tbl_orderdetail`, `tbl_menuitem`
- Archivado en `sales_history`
- Query Builder para operaciones complejas

#### Endpoints en Tiempo Real
- `orders-counts`: Contador de órdenes (caché 1s)
- `orders-metrics`: Métricas de cocina (caché 1s)
- Sesiones para ocultar temporalmente pedidos en KDS

---

## 🚀 Instalación y Configuración

### Requisitos Previos
- PHP 8.2 o superior
- Composer
- Node.js 18+ y NPM
- MySQL 8.0+ o MariaDB 10.4+

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone https://github.com/aarfigo/bot-aaron.git
cd bot-aaron
```

2. **Instalar dependencias PHP**
```bash
composer install
```

3. **Instalar dependencias de Node**
```bash
npm install
```

4. **Configurar el archivo .env**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Ejecutar migraciones**
```bash
php artisan migrate
```

6. **Compilar assets**
```bash
npm run dev        # Desarrollo
npm run build      # Producción
```

7. **Iniciar el servidor**
```bash
php artisan serve
```

Acceder a: `http://localhost:8000`

### Scripts PowerShell (Windows)
```powershell
# Configuración e inicio
.\setup-and-start.ps1

# Solo iniciar servidor
.\start-server.ps1
```

---

## 📖 Guía de Uso

### Panel de Administrador
Acceder con credenciales administrativas para:
- Configurar menú y artículos
- Gestionar personal y roles
- Revisar reportes de ventas
- Configurar parámetros del sistema

### Panel de Mesero
Crear y gestionar órdenes:
1. Seleccionar mesa o crear nueva orden
2. Agregar artículos del menú
3. Aplicar descuentos si aplica
4. Procesar cobro

### Kitchen Display System (KDS)
- Vista en tiempo real de órdenes pendientes
- Marcar artículos como preparados
- Priorización automática por tipo de cocina
- Alertas de órdenes con demora

---

## 🔌 API Endpoints

### Órdenes
```
GET    /api/orders               # Listar órdenes
POST   /api/orders               # Crear orden
GET    /api/orders/{id}          # Obtener detalle
PUT    /api/orders/{id}          # Actualizar orden
DELETE /api/orders/{id}          # Eliminar orden
```

### Menú
```
GET    /api/menu                 # Listar menú
GET    /api/menu/{id}            # Obtener artículo
POST   /admin/menu               # Crear artículo (admin)
PUT    /admin/menu/{id}          # Actualizar artículo (admin)
```

### Métricas en Tiempo Real
```
GET    /api/orders-counts        # Contador de órdenes
GET    /api/orders-metrics       # Métricas de cocina
```

---

## 🧪 Testing

Ejecutar pruebas unitarias y funcionales:

```bash
php artisan test
```

Ver cobertura de pruebas:
```bash
php artisan test --coverage
```

Consultar `README_TESTING.md` para más detalles.

---

## 📊 Plan de Migración

El proyecto incluye un `MIGRATION_PLAN.md` que detalla:
- Pasos para migrar datos legacy
- Estrategia de compatibilidad con esquema antiguo
- Timeline estimado

---

## 🛠️ Desarrollo y Contribución

### Estructura de Ramas
- `main`: Versión estable en producción
- `develop`: Rama de desarrollo
- `feature/*`: Nuevas características
- `bugfix/*`: Correcciones de errores

### Estilo de Código
- PSR-12 para PHP
- EditorConfig incluido (`.editorconfig`)
- Validación automática en commits

### Mejoras Recomendadas
1. Migrar más lógica a servicios/repositorios
2. Aumentar cobertura de pruebas funcionales
3. Reforzar uso de Eloquent en lugar de Query Builder
4. Consolidar adaptadores para datos legacy
5. Centralizar validación en FormRequests

---

## 📝 Licencia

Este proyecto está bajo la licencia MIT. Ver archivo `LICENSE` para más detalles.

---

## 👥 Autor

**aarfigo** - [GitHub Profile](https://github.com/aarfigo)

---

## 🤝 Soporte

Para reportar bugs o solicitar características:
- Abrir un [Issue](https://github.com/aarfigo/bot-aaron/issues)
- Consultar la [Documentación](./docs/)
- Revisar [Pull Requests](https://github.com/aarfigo/bot-aaron/pulls)

---

## 📞 Contacto

Para consultas sobre el proyecto o colaboraciones, contactar al desarrollador a través de GitHub.

---

**Última actualización**: Julio 2026 | Versión: 1.0.0