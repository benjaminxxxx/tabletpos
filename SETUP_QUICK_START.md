# Quick Start Guide

## Prerequisites
- PHP 8.3+
- Laravel 13
- MySQL 8.0+
- Node.js 18+

## Installation Steps

### 1. Clone and Install
```bash
cd your-project
composer install
npm install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

Configure `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rental_sales
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Database
```bash
# Create database
mysql -u root -e "CREATE DATABASE rental_sales;"

# Run migrations
php artisan migrate

# (Optional) Seed demo data
php artisan db:seed
```

### 4. Setup Routes
Add to `routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Pos\SellProduct;
use App\Livewire\Pos\RentProduct;
use App\Livewire\Dashboard\DailyReport;
use App\Livewire\Dashboard\CashClose;
use App\Livewire\Inventory\ProductCatalog;
use App\Livewire\Inventory\BatchProductRegistration;
use App\Livewire\Settings\UserManagement;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // POS
    Route::get('/pos/sell', SellProduct::class)->name('pos.sell');
    Route::get('/pos/rent', RentProduct::class)->name('pos.rent');

    // Inventory
    Route::get('/catalog', ProductCatalog::class)->name('catalog');
    Route::get('/inventory/batch-register', BatchProductRegistration::class)->name('inventory.batch');

    // Reports
    Route::get('/dashboard/daily-report', DailyReport::class)->name('dashboard.report');
    Route::get('/dashboard/cash-close', CashClose::class)->name('cash.close');

    // Settings
    Route::get('/settings/users', UserManagement::class)->name('settings.users');
});

require __DIR__.'/auth.php';
```

### 5. Create Main Layout
Update `resources/views/layouts/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50">
    <div class="pb-20 lg:pb-0">
        {{ $slot }}
    </div>

    <!-- Bottom Navigation (Mobile) -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 grid grid-cols-4 gap-0 lg:hidden">
        <a href="{{ route('pos.sell') }}" class="p-4 text-center text-sm font-semibold hover:bg-gray-50">
            💰 Sell
        </a>
        <a href="{{ route('pos.rent') }}" class="p-4 text-center text-sm font-semibold hover:bg-gray-50">
            🔑 Rent
        </a>
        <a href="{{ route('catalog') }}" class="p-4 text-center text-sm font-semibold hover:bg-gray-50">
            📦 Catalog
        </a>
        <a href="{{ route('dashboard.report') }}" class="p-4 text-center text-sm font-semibold hover:bg-gray-50">
            📊 Report
        </a>
    </nav>

    <!-- Sidebar Navigation (Desktop) -->
    <aside class="hidden lg:fixed lg:left-0 lg:top-0 lg:w-64 lg:h-screen lg:bg-white lg:border-r lg:border-gray-200 lg:p-6 lg:flex lg:flex-col lg:gap-6">
        <div>
            <h1 class="text-2xl font-bold">Rental & Sales</h1>
        </div>
        <nav class="space-y-2">
            <a href="{{ route('pos.sell') }}" class="block px-4 py-3 rounded-lg hover:bg-blue-50">💰 Sell</a>
            <a href="{{ route('pos.rent') }}" class="block px-4 py-3 rounded-lg hover:bg-blue-50">🔑 Rent</a>
            <a href="{{ route('catalog') }}" class="block px-4 py-3 rounded-lg hover:bg-blue-50">📦 Catalog</a>
            <a href="{{ route('dashboard.report') }}" class="block px-4 py-3 rounded-lg hover:bg-blue-50">📊 Report</a>
            <a href="{{ route('inventory.batch') }}" class="block px-4 py-3 rounded-lg hover:bg-blue-50">⚙️ Batch Register</a>
            <a href="{{ route('cash.close') }}" class="block px-4 py-3 rounded-lg hover:bg-blue-50">🔐 Cash Close</a>
            <a href="{{ route('settings.users') }}" class="block px-4 py-3 rounded-lg hover:bg-blue-50">👥 Users</a>
        </nav>
        <div class="mt-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full px-4 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700">
                    Logout
                </button>
            </form>
        </div>
    </aside>

    @livewireScripts
</body>
</html>
```

### 6. Run Dev Server
```bash
# Terminal 1: PHP
php artisan serve

# Terminal 2: Vite
npm run dev
```

Visit: `http://localhost:8000/dashboard`

---

## Testing the System

### Test Account Setup
```bash
# Create test account via tinker
php artisan tinker

> $account = App\Models\Account::create(['name' => 'Test Store', 'description' => 'Test Account']);
> $user = App\Models\User::first();
> $account->users()->attach($user->id, ['role' => 'admin']);
> exit
```

### Test Product Registration
1. Go to **⚙️ Batch Register**
2. Paste this sample data:
```
Blue Suit	Formal wear	Hugo Boss	Gamarra	ZA	2
Red Dress	Party wear	Forever21	Shein	VE	3
White Shirt	Casual	Uniqlo	Taobao	CA	5
```
3. Click **Parse Data** → **Create Products**

### Test Sales
1. Go to **💰 Sell**
2. Search for "ZA" (suits)
3. Select a product
4. Click **Add to Cart**
5. Enter an amount (e.g., 50)
6. Click **Confirm Sale** → **Complete**

### Test Rentals
1. Go to **🔑 Rent**
2. Search for a product
3. Create a new customer (or search existing)
4. Enter rental amount, deposit, return date
5. Click **Create Rental** → **Confirm**

### View Reports
1. Go to **📊 Report**
2. Use date navigation to see sales, rentals, income/outflow
3. Try **🔐 Cash Close** to reconcile daily cash

---

## Common Issues & Solutions

### Migration Errors
```bash
# Drop all tables and re-run
php artisan migrate:refresh

# Or if you need to keep data
php artisan migrate:rollback
php artisan migrate
```

### Livewire Not Updating
- Clear cache: `php artisan cache:clear`
- Rebuild: `npm run build`
- Restart dev server

### Database Connection Error
- Check `.env` DB credentials
- Ensure MySQL is running
- Verify database exists: `mysql -u root -e "SHOW DATABASES;"`

### Permissions Issues
```bash
# Fix permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

---

## Next Steps

1. **Configure Authentication** - Set up Google OAuth in services.php
2. **Add Media Storage** - Configure S3 or local storage for product photos
3. **Setup Barcode Scanner** - Add camera integration
4. **Create Dashboard** - Build analytics and charts
5. **Mobile Optimization** - Test on actual tablets (10-13 inch)
6. **Deploy** - Push to production server

---

## File Structure
```
app/
  ├── Models/
  │   ├── Account.php
  │   ├── Product.php
  │   ├── Sale.php
  │   ├── Rental.php
  │   ├── Customer.php
  │   ├── Movement.php
  │   ├── CashClose.php
  │   └── ... (other models)
  │
  └── Livewire/
      ├── Pos/
      │   ├── SellProduct.php
      │   └── RentProduct.php
      ├── Dashboard/
      │   ├── DailyReport.php
      │   └── CashClose.php
      ├── Inventory/
      │   ├── ProductCatalog.php
      │   └── BatchProductRegistration.php
      └── Settings/
          └── UserManagement.php

resources/views/
  └── livewire/
      ├── pos/
      ├── dashboard/
      ├── inventory/
      └── settings/

database/migrations/
  ├── 2024_01_01_000001_create_accounts_table.php
  ├── 2024_01_01_000002_add_account_id_to_users_table.php
  └── ... (other migrations)
```

---

## Support
For detailed system documentation, see: `RENTAL_SALES_SYSTEM.md`
