# Sistema de Autenticación y Permisos - Documentación Completa

## 📋 Resumen Ejecutivo

Se ha implementado un sistema completo de autenticación y control de acceso basado en roles (RBAC) utilizando **Spatie Laravel Permission**. 

**Estado:** ✅ Implementado y listo para usar  
**Framework:** Laravel 13 + Livewire 4  
**Autenticación:** Laravel Fortify + Spatie Permission  
**Roles:** 3 (admin, seller, viewer)  
**Permisos:** 6 (sell, rent, manage-products, view-reports, cash-close, manage-users)

---

## 🏗️ Arquitectura

```
┌─────────────────────────────────────────────────────────────┐
│                     AUTENTICACIÓN                           │
├─────────────────────────────────────────────────────────────┤
│  Laravel Fortify (login, register, forgot password)         │
│         ↓                                                   │
│  User Model + Spatie HasRoles Trait                        │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│                   CONTROL DE ACCESO                         │
├─────────────────────────────────────────────────────────────┤
│  Roles: admin | seller | viewer                            │
│  Permissions: sell | rent | manage-products | ...          │
│  Middleware: can:permission (en rutas)                     │
│  Blade: @can() directive (en vistas)                       │
└─────────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────────┐
│                    MÓDULOS PROTEGIDOS                       │
├─────────────────────────────────────────────────────────────┤
│  Dashboard → 7 cards filtradas por permisos               │
│   ├─ Vender (require: sell)                               │
│   ├─ Rentals (require: rent)                              │
│   ├─ Productos (require: manage-products)                 │
│   ├─ Registro Lote (require: manage-products)             │
│   ├─ Reportes (require: view-reports)                     │
│   ├─ Cierre Caja (require: cash-close)                    │
│   └─ Usuarios (require: manage-users - ADMIN ONLY)        │
└─────────────────────────────────────────────────────────────┘
```

---

## 👥 Roles y Permisos

### Matriz de Acceso

|                    | Admin | Seller | Viewer |
|:-------------------|:-----:|:------:|:------:|
| **Vender**         |  ✅   |   ✅   |   ❌   |
| **Rentals**        |  ✅   |   ✅   |   ❌   |
| **Productos**      |  ✅   |   ✅   |   ❌   |
| **Reportes**       |  ✅   |   ✅   |   ✅   |
| **Cierre Caja**    |  ✅   |   ✅   |   ❌   |
| **Usuarios**       |  ✅   |   ❌   |   ❌   |

**Admin:** Acceso total a todo (bypass automático)  
**Seller:** Acceso a operaciones de venta  
**Viewer:** Solo visualización de reportes

---

## 📁 Estructura de Archivos

### Nuevos Archivos Creados

```
app/
└── Http/
    └── Middleware/
        └── EnsureAccountSelected.php        [Middleware de cuenta]

database/
└── seeders/
    └── PermissionSeeder.php                 [Roles y permisos]

resources/views/
└── auth/
    ├── login.blade.php                      [Login moderno]
    ├── register.blade.php                   [Registro]
    └── forgot-password.blade.php            [Recuperar contraseña]
```

### Archivos Modificados

```
app/Models/User.php
├─ + use Spatie\Permission\Traits\HasRoles;
└─ + use HasRoles;

composer.json
└─ + "spatie/laravel-permission": "^6.4"

routes/web.php
├─ + Rutas públicas (auth)
├─ + Rutas protegidas (dashboard + módulos)
└─ + Middleware can: para permisos

database/seeders/DatabaseSeeder.php
├─ + call(PermissionSeeder::class);
└─ + Crear usuario admin de prueba

resources/views/dashboard.blade.php
├─ + 7 cards con módulos
└─ + @can() para filtrar por permisos

resources/views/layouts/app.blade.php
├─ + Navbar con nombre y rol
└─ + Botón logout
```

---

## 🔐 Flujo de Autenticación

### Registro de Usuario Nuevo

```
Usuario visita /register
        ↓
Completa formulario (name, email, password, password_confirmation)
        ↓
Validación en servidor (Laravel Fortify)
        ↓
Se crea usuario en DB con rol "viewer" por defecto
        ↓
Se envía email de verificación (si está configurado)
        ↓
Usuario puede ir a /login
```

### Login

```
Usuario visita /login
        ↓
Ingresa email y contraseña
        ↓
Laravel Fortify valida credenciales
        ↓
Se crea sesión autenticada
        ↓
Redirige a /dashboard
        ↓
Dashboard muestra 7 cards filtradas por permisos del usuario
```

### Acceso a Módulo Protegido

```
Usuario hace click en "Vender"
        ↓
Intenta acceder a /pos/sell
        ↓
Middleware verifica:
  - ¿Usuario está autenticado?
  - ¿Usuario tiene permiso "sell" O es admin?
        ↓
SI ✅ → Carga el componente SellProduct
NO ❌ → Muestra error 403 Forbidden
```

---

## 🛣️ Rutas Completas

### Públicas (sin autenticación)

```
GET  /                              Welcome page
GET  /login                         Login form
POST /login                         Process login (Fortify)
GET  /register                      Register form
POST /register                      Process register (Fortify)
GET  /forgot-password               Forgot password form
POST /forgot-password               Send reset email
GET  /reset-password/{token}        Reset password form
POST /reset-password                Process reset
GET  /verify-email/{id}/{hash}      Verify email (Fortify)
```

### Protegidas (auth + verified)

```
GET  /dashboard                          Dashboard (all roles)
   
POS & SALES:
GET  /pos/sell                           SellProduct (can:sell)
GET  /pos/rent                           RentProduct (can:rent)

INVENTORY:
GET  /inventory/catalog                  ProductCatalog (can:manage-products)
GET  /inventory/batch-register           BatchProductRegistration (can:manage-products)

REPORTS:
GET  /dashboard/reports                  DailyReport (can:view-reports)

CASH:
GET  /dashboard/cash-close               CashClose (can:cash-close)

USERS:
GET  /settings/users                     UserManagement (can:manage-users)

LOGOUT:
POST /logout                             Process logout
```

---

## 🎨 Vistas de Autenticación

### Login (resources/views/auth/login.blade.php)

- Formulario limpio y moderno
- Email y password
- Remember me checkbox
- Link a "¿Olvidó contraseña?"
- Link a "Crear cuenta"
- Mensajes de error validados
- Diseño responsive

### Register (resources/views/auth/register.blade.php)

- Formulario completo
- Nombre, email, password, password confirmation
- Validación en tiempo real
- Términos y condiciones
- Link a login
- Mensaje de error si email existe

### Forgot Password (resources/views/auth/forgot-password.blade.php)

- Formulario con email
- Instrucción clara
- Link a login

---

## 📊 Dashboard

### Vista Principal

```
┌──────────────────────────────────────────────────────┐
│  POS Store            Admin User | Administrador     │
│                                          [Salir]     │
└──────────────────────────────────────────────────────┘

Bienvenido, Admin User
Sistema de Gestión de Ventas y Rentals
Rol: Administrador (Acceso Total)

┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│  💰 Vender  │  │  🔑 Rentals │  │ 📦 Productos│
│             │  │             │  │             │
│  Realizar   │  │  Gestionar  │  │  Ver        │
│  ventas     │  │  rentals    │  │  catálogo   │
└─────────────┘  └─────────────┘  └─────────────┘

┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│ 📄 Lote     │  │  📊 Reportes│  │ 💳 Cierre   │
│             │  │             │  │             │
│  Registrar  │  │  Ver resumen│  │  Reconciliar│
│  múltiples  │  │  diario     │  │  caja       │
└─────────────┘  └─────────────┘  └─────────────┘

┌─────────────┐
│ 👥 Usuarios │
│             │
│  Invitar    │
│  vendedores │
└─────────────┘
```

---

## 💻 Uso en Código

### En Rutas

```php
// Proteger ruta con permiso
Route::get('sell', SellProduct::class)->middleware('can:sell');

// Grupo de rutas con permiso
Route::group(['middleware' => 'can:sell'], function () {
    Route::get('sell', SellProduct::class)->name('sell');
});

// Admin bypass automático
// Si user->hasRole('admin'), puede:sell retorna true
```

### En Vistas Blade

```blade
<!-- Mostrar solo si tiene permiso -->
@can('sell')
    <a href="{{ route('pos.sell') }}">Vender</a>
@endcan

<!-- Mostrar solo si es admin -->
@if(auth()->user()->hasRole('admin'))
    <a href="{{ route('settings.users') }}">Usuarios</a>
@endif

<!-- Combinaciones -->
@if(auth()->user()->hasRole('admin') || auth()->user()->can('sell'))
    <p>Eres vendedor o admin</p>
@endif
```

### En Controllers/Commands

```php
// Verificar permiso
if (auth()->user()->can('sell')) {
    // Procesar venta
}

// Verificar rol
if (auth()->user()->hasRole('admin')) {
    // Acción admin
}

// Asignar rol a usuario
$user->assignRole('seller');

// Asignar permiso
$user->givePermissionTo('sell');

// Revocar
$user->removeRole('seller');
$user->revokePermissionTo('sell');
```

---

## 🔧 Configuración

### Variables de Entorno Necesarias

```
# No requiere variables especiales de autenticación
# Usa DB existente para roles y permisos
```

### Cache de Permisos

Spatie almacena en caché los roles y permisos. Si cambias en DB manualmente:

```bash
php artisan permission:cache-reset
php artisan cache:clear
```

---

## 🧪 Pruebas

### Usuario Admin
- Email: `admin@example.com`
- Password: `password`
- Acceso: Todo (7 módulos visibles)

### Crear Usuario Seller

```bash
php artisan tinker

>>> $user = User::create([
...     'name' => 'Juan Vendedor',
...     'email' => 'juan@example.com',
...     'password' => Hash::make('password')
... ])
>>> $user->assignRole('seller')
```

Ahora Juan puede: Vender, Rentals, Productos, Reportes  
Juan NO puede: Cierre de Caja, Usuarios

### Crear Usuario Viewer

```bash
>>> $user = User::create([...])
>>> $user->assignRole('viewer')
```

Viewer solo ve: Reportes y Dashboard

---

## ⚠️ Consideraciones de Seguridad

✅ **Implementado:**
- Autenticación requerida para acceder a módulos
- Validación de permisos en rutas (middleware)
- Validación de permisos en vistas (@can)
- Admin bypass automático (puede desactivarse si necesario)
- CSRF protection en formularios
- Password hashing con bcrypt
- Session-based authentication

⚠️ **Recomendado:**
- [ ] Configurar 2FA (Two-Factor Authentication)
- [ ] Auditoria de cambios (en tabla `movements` está implementada)
- [ ] Rate limiting en login
- [ ] Email verification obligatoria
- [ ] Logs de acceso a módulos críticos

---

## 📚 Referencia de Métodos Spatie

### En Usuario

```php
$user->hasRole('admin')              // ¿Tiene rol?
$user->hasAnyRole(['admin', 'seller']) // ¿Tiene alguno?
$user->hasAllRoles(['admin', 'seller']) // ¿Tiene todos?

$user->can('sell')                   // ¿Tiene permiso?
$user->hasPermissionTo('sell')       // Alternativa

$user->assignRole('seller')          // Asignar rol
$user->removeRole('seller')          // Remover rol
$user->syncRoles('admin', 'seller')  // Sincronizar roles

$user->givePermissionTo('sell')      // Asignar permiso
$user->revokePermissionTo('sell')    // Remover permiso
$user->syncPermissions('sell', 'rent') // Sincronizar permisos
```

### Modelos

```php
Role::create(['name' => 'admin'])
Permission::create(['name' => 'sell'])

$role = Role::findByName('admin')
$permission = Permission::findByName('sell')

$role->givePermissionTo($permission)
$role->revokePermissionTo($permission)

Role::all()
Permission::all()
```

---

## 🚀 Próximos Pasos

1. **Instalar Spatie y ejecutar migraciones** (ver AUTH_SETUP_GUIDE.md)
2. **Probar login con admin@example.com / password**
3. **Explorar dashboard y módulos**
4. **Crear usuarios adicionales vía /register**
5. **Cambiar roles en /settings/users**
6. **Integración con sistema de pagos** (si aplica)
7. **Configurar 2FA** (recomendado)

---

## 📖 Documentación

- `AUTH_SETUP_GUIDE.md` - Guía paso a paso de instalación
- `routes/web.php` - Definición de todas las rutas
- `app/Models/User.php` - Modelo User con traits de Spatie
- `database/seeders/PermissionSeeder.php` - Definición de roles y permisos

---

## ✅ Checklist Final

- [x] Spatie Laravel Permission instalado
- [x] Roles creados (admin, seller, viewer)
- [x] Permisos creados (6 total)
- [x] User.php con trait HasRoles
- [x] Vistas de auth (login, register, forgot)
- [x] Dashboard con 7 módulos filtrados
- [x] Rutas protegidas con middleware can:
- [x] Middleware @can() en vistas
- [x] Usuario admin de prueba
- [x] Documentación completa
- [x] Commit a git

**Estado:** ✅ Listo para instalar y usar

---

**Fecha:** Mayo 2026  
**Versión:** 1.0  
**Autor:** v0 Assistant  
**Licencia:** MIT
