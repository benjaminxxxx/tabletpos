# Referencia Rápida - Sistema de Autenticación

## 🚀 Para Instalar (5 minutos)

```bash
# 1. Instalar Spatie
composer require spatie/laravel-permission

# 2. Publicar migraciones
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --tag="migrations"

# 3. Correr migraciones
php artisan migrate

# 4. Ejecutar seeders
php artisan db:seed

# 5. Probar
php artisan serve
# Ir a: http://localhost:8000/login
# Email: admin@example.com
# Password: password
```

---

## 📊 Matriz de Permisos Rápida

```
USUARIO: admin@example.com / password
ROL:     admin
PERMISOS: TODOS

MÓDULOS VISIBLES EN DASHBOARD:
✅ Vender           (GET /pos/sell)
✅ Rentals          (GET /pos/rent)
✅ Productos        (GET /inventory/catalog)
✅ Registro Lote    (GET /inventory/batch-register)
✅ Reportes         (GET /dashboard/reports)
✅ Cierre Caja      (GET /dashboard/cash-close)
✅ Usuarios         (GET /settings/users)
```

---

## 🛡️ Protección en Rutas

### Middleware en routes/web.php

```php
// Ruta protegida por permiso 'sell'
Route::get('sell', SellProduct::class)->middleware('can:sell');

// Ruta protegida por múltiples permisos
Route::get('users', UserManagement::class)->middleware('can:manage-users');
```

### Si usuario NO tiene permiso → Error 403 Forbidden

---

## 🎨 Control en Vistas

### Mostrar elemento solo si tiene permiso

```blade
@can('sell')
    <a href="/pos/sell">Vender</a>
@endcan

@if(auth()->user()->hasRole('admin'))
    <a href="/settings/users">Gestionar Usuarios</a>
@endif
```

### Si usuario NO tiene permiso → El elemento no se muestra

---

## 👥 Crear Nuevo Usuario (Como Admin)

### Opción 1: Vía Interfaz (/register)

1. Ir a `/register`
2. Completa el formulario
3. Se crea con rol "viewer" por defecto
4. Admin va a `/settings/users`
5. Cambia el rol a "seller" o "admin"

### Opción 2: Vía Tinker (Terminal)

```bash
php artisan tinker

# Crear usuario
$user = User::create([
    'name' => 'Juan Pérez',
    'email' => 'juan@example.com',
    'password' => Hash::make('password123')
])

# Asignar rol
$user->assignRole('seller')

# Verificar
$user->hasRole('seller')  # true
$user->can('sell')        # true
$user->can('manage-users')# false (solo admin puede)
```

---

## 📱 Flujo Visual del Usuario

### 1. Nuevo Usuario

```
Visita tu-app.com
        ↓
Click "¿Eres nuevo?"
        ↓
/register
        ↓
Completa: nombre, email, password
        ↓
Se registra con rol "viewer"
        ↓
Puede: Ver reportes solamente
```

### 2. Admin Cambia Rol

```
Admin va a /settings/users
        ↓
Busca al nuevo usuario
        ↓
Cambia rol de "viewer" a "seller"
        ↓
Usuario actualizado:
  - Ahora puede vender
  - Ahora puede gestionar rentals
  - Ahora puede ver productos
  - Ahora puede ver reportes
```

### 3. Intentar Acceder a Módulo Sin Permiso

```
Usuario "viewer" intenta acceder a /pos/sell
        ↓
Middleware verifica: ¿tiene permiso 'sell'?
        ↓
NO tiene permiso
        ↓
Error 403: Forbidden
```

---

## 🔑 Permisos por Módulo

| Módulo | Permiso | Roles | Descripción |
|--------|---------|-------|-------------|
| Vender | `sell` | admin, seller | Realizar transacciones de venta |
| Rentals | `rent` | admin, seller | Gestionar rentals de productos |
| Productos | `manage-products` | admin, seller | Ver/editar catálogo |
| Lote | `manage-products` | admin, seller | Importar productos en masa |
| Reportes | `view-reports` | admin, seller, viewer | Ver reportes diarios |
| Cierre Caja | `cash-close` | admin, seller | Reconciliar caja |
| Usuarios | `manage-users` | admin | Invitar y gestionar usuarios |

---

## 🛠️ Comandos Útiles

```bash
# Ver todos los roles
php artisan tinker
>>> Role::all()

# Ver todos los permisos
>>> Permission::all()

# Ver roles del usuario actual
>>> auth()->user()->roles

# Ver permisos del usuario actual
>>> auth()->user()->permissions

# Salir de tinker
>>> exit

# Resetear caché de permisos
php artisan permission:cache-reset
php artisan cache:clear

# Resetear base de datos
php artisan migrate:fresh --seed
```

---

## 🚨 Troubleshooting Común

### Error: "Route not found"

**Causa:** Ruta no existe  
**Solución:** Verifica routes/web.php

### Error: "The user is not authorized"

**Causa:** Usuario no tiene el permiso  
**Solución:** Admin asigna permiso en /settings/users

### Error: "Call to undefined method hasRole"

**Causa:** User.php no tiene trait HasRoles  
**Solución:** Verifica que User.php importa y usa el trait

### Cambios no se reflejan

**Causa:** Caché de permisos  
**Solución:** 
```bash
php artisan permission:cache-reset
php artisan cache:clear
```

---

## 📋 Estructura de BD

### Tablas Spatie

```
roles
├── id
├── name (admin, seller, viewer)
└── guard_name

permissions
├── id
├── name (sell, rent, manage-products, etc)
└── guard_name

model_has_roles
├── role_id
├── model_id (user_id)
└── model_type

role_has_permissions
├── permission_id
├── role_id
```

---

## 🎯 Casos de Uso

### Caso 1: Nueva tienda quiere usar el sistema

```
1. Admin crea cuenta principal
2. Se registra con credentials
3. Login con email y password
4. Ve dashboard con todos los módulos (admin)
5. Va a /settings/users
6. Invita vendedores por email
7. Cada vendedor se registra
8. Admin asigna rol "seller" a cada uno
9. Vendedores pueden vender, ver reportes, etc
```

### Caso 2: Supervisor solo quiere ver reportes

```
1. Se registra por /register
2. Por defecto rol "viewer"
3. No lo cambia a "seller" el admin
4. Login y ve solo: Dashboard + Reportes
5. No puede vender ni gestionar productos
```

### Caso 3: Admin se va de vacaciones

```
1. Admin designa a otro como "admin"
2. Asigna rol "admin" en /settings/users
3. Ahora ambos son admin
4. Segundo admin puede hacer todo
5. Puede invitar más usuarios
6. Puede cambiar roles
```

---

## 💡 Tips de Seguridad

✅ DO (Sí)
- Cambiar contraseña de admin@example.com después de instalar
- Usar contraseñas fuertes (12+ caracteres)
- Verificar email antes de dar acceso a venta
- Auditar acceso a módulos críticos
- Usar HTTPS en producción

❌ DON'T (No)
- No compartir credenciales de admin
- No dejar usuarios sin rol asignado
- No usar "password" como contraseña en producción
- No hacer cambios de permisos en DB directamente

---

## 📞 Soporte Rápido

### Si algo no funciona:

1. Ejecuta: `php artisan migrate:fresh --seed`
2. Verifica: `php artisan tinker` → `Role::all()`
3. Revisa logs: `tail -f storage/logs/laravel.log`
4. Limpia caché: `php artisan cache:clear`
5. Si persiste: Abre issue en GitHub

---

## 📖 Docs Completas

- **AUTH_SETUP_GUIDE.md** - Instalación paso a paso
- **AUTHENTICATION_SYSTEM.md** - Documentación técnica completa
- **routes/web.php** - Definición de rutas
- **app/Models/User.php** - Modelo User

---

**¡Listo para empezar!** 🚀

Accede a http://localhost:8000/login con:
- Email: `admin@example.com`
- Password: `password`
