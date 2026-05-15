# Guía de Instalación: Sistema de Autenticación y Permisos

## Estado: ✅ COMPLETO

Este documento guía la instalación final del sistema de autenticación con Spatie Laravel Permission.

---

## 1. INSTALACIÓN DE SPATIE

### Paso 1.1: Instalar la dependencia
```bash
cd /vercel/share/v0-project
composer require spatie/laravel-permission
```

### Paso 1.2: Publicar las migraciones de Spatie
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"
```

### Paso 1.3: Ejecutar migraciones
```bash
php artisan migrate
```

---

## 2. ESTRUCTURA DE ROLES Y PERMISOS

### Roles Creados

| Rol | Descripción | Acceso |
|-----|-------------|--------|
| **admin** | Administrador total | Todo (bypass automático) |
| **seller** | Vendedor de tienda | Vender, rentals, productos, reportes |
| **viewer** | Visualizador de reportes | Solo reportes y dashboard |

### Permisos Creados

| Permiso | Descripción | Roles Asignados |
|---------|-------------|-----------------|
| `sell` | Realizar ventas | seller, admin |
| `rent` | Gestionar rentals | seller, admin |
| `manage-products` | Registrar/editar productos | seller, admin |
| `view-reports` | Ver reportes | viewer, seller, admin |
| `cash-close` | Cierre de caja | seller, admin |
| `manage-users` | Gestionar usuarios | admin |

---

## 3. ARCHIVOS CREADOS/MODIFICADOS

### ✅ ARCHIVOS NUEVOS

```
database/seeders/PermissionSeeder.php
├─ Crea 3 roles (admin, seller, viewer)
├─ Crea 6 permisos (sell, rent, manage-products, view-reports, cash-close, manage-users)
└─ Asigna permisos a roles

resources/views/auth/login.blade.php
├─ Login moderno con diseño profesional
├─ Validación de errores
└─ Link a registro y "¿Olvidó contraseña?"

resources/views/auth/register.blade.php
├─ Formulario de registro completo
├─ Validación en cliente y servidor
├─ Link a login

resources/views/auth/forgot-password.blade.php
├─ Formulario para recuperar contraseña
└─ Envío de link de reset

app/Http/Middleware/EnsureAccountSelected.php
├─ Valida que el usuario haya seleccionado una cuenta
└─ Redirige al dashboard si no tiene cuenta
```

### 📝 ARCHIVOS MODIFICADOS

```
app/Models/User.php
├─ Agregado: use Spatie\Permission\Traits\HasRoles;
├─ Agregado trait HasRoles a la clase User
└─ Permite usar: auth()->user()->hasRole('admin'), etc.

composer.json
├─ Agregado: "spatie/laravel-permission": "^6.4"
└─ Actualizar con: composer install

routes/web.php
├─ Rutas públicas (login, register)
├─ Rutas protegidas con middleware auth
├─ Middleware can: para validar permisos
└─ 7 módulos organizados por permiso

database/seeders/DatabaseSeeder.php
├─ Llamada a PermissionSeeder::class
├─ Creación de usuario admin de prueba
└─ Asignación de rol admin al usuario

resources/views/dashboard.blade.php
├─ Grid de 7 módulos (cards)
├─ Filtrado dinámico por @can() directive
├─ Icons y descripciones de cada módulo
├─ Empty state si usuario no tiene permisos

resources/views/layouts/app.blade.php
├─ Agregada navbar superior
├─ Mostrar nombre y rol del usuario
├─ Botón Salir (logout)
└─ Diseño profesional
```

---

## 4. RUTAS CONFIGURADAS

### Rutas Públicas (sin autenticación)

```
GET  /                          → welcome (landing page)
GET  /login                     → login form
POST /login                     → procesar login (Laravel Fortify)
GET  /register                  → register form
POST /register                  → procesar registro (Laravel Fortify)
GET  /forgot-password           → forgot password form
POST /forgot-password           → enviar reset email
GET  /reset-password/{token}    → reset password form
POST /reset-password            → procesar reset
```

### Rutas Protegidas (require: auth + verified)

```
GET  /dashboard                           → Dashboard principal (todos)

# POS - Vender (require: can:sell)
GET  /pos/sell                            → SellProduct component

# Rentals (require: can:rent)
GET  /pos/rent                            → RentProduct component

# Inventario (require: can:manage-products)
GET  /inventory/catalog                   → ProductCatalog component
GET  /inventory/batch-register            → BatchProductRegistration component

# Reportes (require: can:view-reports)
GET  /dashboard/reports                   → DailyReport component

# Cierre de Caja (require: can:cash-close)
GET  /dashboard/cash-close                → CashClose component

# Gestión de Usuarios - ADMIN ONLY (require: can:manage-users)
GET  /settings/users                      → UserManagement component

# Logout
POST /logout                              → procesar logout
```

---

## 5. CONTROL DE ACCESO EN VISTAS

### Mostrar elemento solo si usuario tiene permiso

```blade
<!-- En vistas Blade -->
@can('sell')
    <!-- Este código solo se muestra si user->can('sell') -->
@endcan

<!-- Verificar rol -->
@if(auth()->user()->hasRole('admin'))
    <!-- Este código solo se muestra si es admin -->
@endif

<!-- Combinación -->
@if(auth()->user()->hasRole('admin') || auth()->user()->can('sell'))
    <!-- Code -->
@endif
```

### En rutas

```php
// Ruta protegida por permiso
Route::get('sell', SellProduct::class)->middleware('can:sell');

// Ruta para admin
Route::get('users', UserManagement::class)->middleware('can:manage-users');

// Múltiples permisos
Route::get('report', DailyReport::class)->middleware(['can:view-reports', 'can:sell']);
```

---

## 6. ASIGNAR ROLES Y PERMISOS EN CÓDIGO

### En un Controller o Comando

```php
// Asignar rol a usuario
$user->assignRole('seller');

// Asignar permiso directo
$user->givePermissionTo('sell');

// Verificar si tiene rol
if ($user->hasRole('admin')) {
    // ...
}

// Verificar si tiene permiso
if ($user->can('sell')) {
    // ...
}

// Revocar rol
$user->removeRole('seller');

// Revocar permiso
$user->revokePermissionTo('sell');
```

---

## 7. USUARIO DE PRUEBA

Después de correr las migraciones y seeders:

**Email:** admin@example.com  
**Contraseña:** password  
**Rol:** admin  

### Cambiar credenciales (Opcional)

Edita `database/seeders/DatabaseSeeder.php`:

```php
$user = User::factory()->create([
    'name' => 'Tu Nombre',
    'email' => 'tu@email.com',
]);
$user->assignRole('admin');
```

Luego: `php artisan migrate:fresh --seed`

---

## 8. CREAR USUARIO VÍA REGISTRO

1. Accede a `/register`
2. Completa el formulario
3. Se registra con rol **viewer** por defecto
4. Admin debe cambiar el rol en `/settings/users`

---

## 9. CARACTERÍSTICAS DE SEGURIDAD

✅ Todos los usuarios deben estar autenticados para acceder a módulos  
✅ Admin tiene bypass automático (hasRole('admin') permite todo)  
✅ Seller solo puede acceder a módulos específicos  
✅ Viewer solo ve reportes  
✅ Middleware `can:` valida permisos en cada ruta  
✅ Blade `@can()` oculta UI si no tiene permiso  

---

## 10. FLUJO TÍPICO DE USUARIO

### Usuario Nuevo (Registro)

```
1. Visita /register
   ↓
2. Completa formulario (name, email, password)
   ↓
3. Se registra con rol "viewer" por defecto
   ↓
4. Puede login en /login
   ↓
5. Ve dashboard con acceso solo a reportes
```

### Admin Asigna Rol Seller

```
1. Admin va a /settings/users
   ↓
2. Busca al usuario registrado
   ↓
3. Cambia rol de "viewer" a "seller"
   ↓
4. Usuario ahora puede: vender, rentals, productos, reportes
```

### Usuario Intenta Acceder a Ruta Sin Permiso

```
1. Usuario seller intenta acceder a /settings/users
   ↓
2. Middleware can:manage-users rechaza acceso
   ↓
3. Laravel muestra error 403 Forbidden
```

---

## 11. CONFIGURACIÓN AVANZADA

### Registrar nuevo rol

En un Comando o Seeder:

```php
use Spatie\Permission\Models\Role;

$role = Role::create(['name' => 'supervisor']);
```

### Registrar nuevo permiso

```php
use Spatie\Permission\Models\Permission;

$permission = Permission::create(['name' => 'approve-sales']);
```

### Asignar permiso a rol

```php
$role = Role::findByName('seller');
$permission = Permission::findByName('sell');
$role->givePermissionTo($permission);
```

---

## 12. TROUBLESHOOTING

### Error: "Route not defined"

**Problema:** Ruta no aparece en `/dashboard`  
**Solución:** Verifica que:
- [ ] Usuario tiene el permiso necesario
- [ ] Middleware en routes/web.php está correcto
- [ ] `@can()` en dashboard.blade.php está correcto

### Error: "Permission not found"

**Problema:** `$user->can('sell')` retorna false  
**Solución:** 
- Verifica que el permiso existe: `Permission::all()`
- Que el usuario tiene el permiso: `$user->permissions`
- Ejecuta: `php artisan cache:clear`

### Usuario no ve cambios después de cambiar rol

**Solución:** 
```bash
php artisan cache:clear
php artisan permission:cache-reset
```

---

## 13. PRÓXIMOS PASOS

1. **Instalar Spatie:**
   ```bash
   composer require spatie/laravel-permission
   php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"
   php artisan migrate
   ```

2. **Ejecutar seeders:**
   ```bash
   php artisan db:seed
   ```

3. **Probar login:**
   - Accede a `http://localhost:8000/login`
   - Email: `admin@example.com`
   - Password: `password`

4. **Explorar módulos:**
   - Deberías ver 7 cards en dashboard
   - Click en cada uno para acceder

5. **Crear más usuarios:**
   - Accede a `/register`
   - Crea usuario nuevo
   - Admin cambia rol en `/settings/users`

---

## 14. REFERENCIA RÁPIDA

### Artisan Commands útiles

```bash
# Ver todos los roles
php artisan tinker
>>> Role::all()

# Ver todos los permisos
>>> Permission::all()

# Asignar rol a usuario (en tinker)
>>> $user = User::find(1)
>>> $user->assignRole('seller')

# Verificar permisos
>>> auth()->user()->can('sell')
>>> auth()->user()->hasRole('admin')
```

---

## ✅ CHECKLIST DE INSTALACIÓN

- [ ] Instalar Spatie: `composer require spatie/laravel-permission`
- [ ] Publicar migrations: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"`
- [ ] Correr migrations: `php artisan migrate`
- [ ] Ejecutar seeder: `php artisan db:seed`
- [ ] Acceder a `/login` con admin@example.com / password
- [ ] Ver dashboard con 7 módulos
- [ ] Crear usuario nuevo vía /register
- [ ] Cambiar rol en /settings/users (admin only)
- [ ] Probar permisos en cada módulo
- [ ] Listo para producción

---

## 📞 Soporte

Si encuentras problemas, revisa:
1. Logs en `storage/logs/laravel.log`
2. Base de datos: verifica `roles`, `permissions`, `model_has_roles`
3. Cache: ejecuta `php artisan cache:clear`

¡Sistema listo para usar! 🚀
