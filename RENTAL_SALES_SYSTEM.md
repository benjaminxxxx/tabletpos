# Rental & Sales Management System

A complete Laravel 11 + Livewire 3 + MySQL rental and sales management system optimized for tablet use (10–13 inch screens).

## Features

### 🏪 Core Modules

1. **POS - Sell Module** (`Pos/SellProduct`)
   - Search products by code, name, or brand
   - Add items to cart with pricing
   - Complete sales transactions
   - Automatic product status update (available → blocked)
   - Movement tracking for financial records

2. **Rental Module** (`Pos/RentProduct`)
   - Search available products for rental
   - Customer lookup or quick creation
   - Capture customer details (DNI, phone)
   - Set rental amount, deposit, and return date
   - Track rental status (active, returned, overdue, cancelled)
   - Automatic product status update (available → rented)

3. **Daily Report** (`Dashboard/DailyReport`)
   - Date navigation (previous/next day, go to today)
   - Income/Outflow/Net summary
   - List of all sales and rentals for the day
   - Real-time movement calculations

4. **Product Catalog** (`Inventory/ProductCatalog`)
   - 2-column grid layout (responsive to 3+ on desktop)
   - Filter by category, status, or location
   - Product detail modal with media preview
   - Badges for status (available=green, rented=amber, blocked=red, etc.)

5. **Batch Product Registration** (`Inventory/BatchProductRegistration`)
   - Tab-separated spreadsheet input (Excel-style)
   - Auto-generate sequential public codes
   - Create multiple products in one action
   - Error reporting for invalid rows

6. **Cash Close** (`Dashboard/CashClose`)
   - End-of-day cash reconciliation
   - Compare expected vs confirmed amounts
   - Flag discrepancies
   - Store close records with user & timestamp

7. **User Management** (`Settings/UserManagement`)
   - Invite users by email
   - Assign roles (seller, admin)
   - Block/unblock users
   - Remove users from account

---

## Database Schema

### Core Tables

**accounts** - Multi-tenant support
- id, name, description, timestamps, soft deletes

**account_users** - User-account mapping with roles
- id, account_id (FK), user_id (FK), role (owner/admin/seller), is_blocked, timestamps, soft deletes
- unique([account_id, user_id])

**users** - Laravel default with extensions
- id, name, email, password, account_id (FK), google_id, profile_photo_path, timestamps
- account_id for primary account, accounts() for all accounts

**locations** - Warehouse/store locations
- id, account_id (FK), name, expected_capacity, timestamps, soft deletes

**products** - Individual items for sale/rent
- id, account_id (FK), location_id (FK), public_code (unique), name, description, brand, origin, category_prefix (2-char), status (available/rented/laundry/maintenance/blocked), can_sell, can_rent, rent_count, sale_count, timestamps, soft deletes
- Auto-generate public_code = category_prefix + sequence (e.g., ZA0001, ZA0002)

**product_media** - Photos, videos, 3D models per product
- id, product_id (FK), type (photo/video/3d_model), path, media_type (MIME), sort_order, timestamps

**purchases** - Batch purchase records (for inventory management)
- id, account_id (FK), user_id (FK), purchase_date, notes, timestamps, soft deletes

**sales** - Individual sales transactions
- id, account_id (FK), user_id (FK), product_id (FK, nullable), public_code_snapshot, description_snapshot, amount, status (completed/annulled), timestamps
- Product marked as 'blocked' after sale; never deleted

**customers** - Rental customers with KYC
- id, account_id (FK), dni, full_name, phone, dni_photo_path, selfie_photo_path, notes, timestamps, soft deletes

**rentals** - Rental transactions
- id, account_id (FK), user_id (FK), product_id (FK, nullable), customer_id (FK), public_code_snapshot, description_snapshot, amount, deposit_amount, return_date, returned_at, status (active/returned/overdue/cancelled), timestamps

**movements** - Unified financial ledger (never deleted)
- id, account_id (FK), user_id (FK), type (string: sale/rental/sale_return/rental_return/rental_cancel/late_fee/product_return/other_income/other_expense), reference_id, reference_type, amount, direction (in/out), notes, timestamps

**cash_closes** - End-of-day reconciliation
- id, account_id (FK), user_id (FK), close_date, expected_amount, confirmed_amount (nullable), notes, timestamps
- unique([account_id, close_date])

---

## UI/UX Design

### Tablet-First Layout
- Minimum tap targets: **48×48px** everywhere
- 2–3 column grid layouts for product cards
- Bottom navigation bar for main modules (planned)
- Large, touch-friendly buttons and inputs
- No hover-only interactions; all actions work on touch

### Color Coding
- **Available** = Green (#10b981)
- **Rented** = Amber (#f59e0b)
- **Blocked** = Red (#ef4444)
- **Laundry** = Blue (#3b82f6)
- **Maintenance** = Gray (#9ca3af)

### Key Views
- **Search + Selection** (left/sidebar on desktop, top on mobile)
- **Cart/Form** (center/main content area)
- **Preview/Summary** (right sidebar on desktop, bottom on mobile)
- **Modals** for confirmations and details

---

## API Flows

### Sale Flow
1. Search product by code/name/brand
2. Select product
3. Add to cart with amount
4. Confirm sale → creates Sale record, updates Product status → blocked, increments sale_count
5. Creates Movement record (type=sale, direction=in)

### Rental Flow
1. Search product by code/name
2. Select product
3. Search or create customer (DNI, name, phone required)
4. Enter rental amount, deposit (optional), return date
5. Confirm rental → creates Rental record, updates Product status → rented, increments rent_count
6. Creates Movement records for rental amount + deposit (if applicable)

### Annulment (Sales)
- Sale status → 'annulled'
- Creates Movement record (type=sale_return, direction=out)
- Product remains blocked (not relisted)

### Return (Rentals)
- Rental status → 'returned', returned_at = today
- Product status → 'available' (can be rented again)
- Creates Movement record (type=rental_return, direction=out) if applicable

### Daily Report
- Sum all Movement records for selected date
- Show income (direction=in), outflow (direction=out), net (in - out)
- List all Sales and Rentals for the day

### Cash Close
- Calculate expected = sum(direction=in) - sum(direction=out) for the day
- User enters confirmed amount
- Flag discrepancies
- Store CashClose record (immutable)

---

## Permissions Summary

| Action | Seller | Admin | Owner |
|--------|--------|-------|-------|
| Sell Products | ✓ | ✓ | ✓ |
| Rent Products | ✓ | ✓ | ✓ |
| View Daily Report | ✓ | ✓ | ✓ |
| View Catalog | ✓ | ✓ | ✓ |
| Register Products | ✗ | ✓ | ✓ |
| Manage Purchases | ✗ | ✓ | ✓ |
| Annul/Cancel Transactions | ✗ | ✓ | ✓ |
| Cash Close | ✗ | ✓ | ✓ |
| User Management | ✗ | ✓ | ✓ |
| Block/Unblock Users | ✗ | ✓ | ✓ |
| Manage Locations | ✗ | ✓ | ✓ |

---

## Setup Instructions

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed Data (Optional)
```bash
php artisan db:seed
```

### 3. Create Routes
Add routes in `routes/web.php` for your controllers:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pos/sell', App\Livewire\Pos\SellProduct::class)->name('pos.sell');
    Route::get('/pos/rent', App\Livewire\Pos\RentProduct::class)->name('pos.rent');
    Route::get('/dashboard/report', App\Livewire\Dashboard\DailyReport::class)->name('dashboard.report');
    Route::get('/catalog', App\Livewire\Inventory\ProductCatalog::class)->name('catalog');
    Route::get('/inventory/batch', App\Livewire\Inventory\BatchProductRegistration::class)->name('inventory.batch');
    Route::get('/dashboard/cash-close', App\Livewire\Dashboard\CashClose::class)->name('cash.close');
    Route::get('/settings/users', App\Livewire\Settings\UserManagement::class)->name('settings.users');
});
```

### 4. Create Navigation Layout
Create a main layout with bottom navigation (or sidebar on desktop):

```blade
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t">
  <button onclick="navigate('/pos/sell')">POS</button>
  <button onclick="navigate('/pos/rent')">Rentals</button>
  <button onclick="navigate('/catalog')">Catalog</button>
  <button onclick="navigate('/dashboard/report')">Reports</button>
</nav>
```

### 5. Configure Authentication
For Google OAuth integration with Socialite:

```bash
composer require laravel/socialite
```

Update config/services.php and add your Google OAuth credentials.

---

## Key Methods & Helpers

### Product
```php
Product::generatePublicCode($accountId, $categoryPrefix); // Auto-generate ZA0001, etc.
$product->getStatusColor(); // Returns color hex for status badge

// Relationships
$product->location();
$product->media();
$product->sales();
$product->rentals();
```

### Customer
```php
$customer->getRentalCount(); // Count of completed rentals
$customer->isEligibleForLoyaltyDiscount(); // 4+ rentals of category ZA
```

### Rental
```php
$rental->isOverdue(); // Check if past return_date
$rental->getDaysOverdue(); // Days past return_date
$rental->updateStatus(); // Mark as overdue if applicable
```

### User
```php
$user->getRoleInAccount($accountId); // Returns 'seller'|'admin'|'owner'
$user->isBlockedInAccount($accountId); // Boolean
```

### CashClose
```php
$close->hasDiscrepancy(); // confirmed_amount != expected_amount
$close->getDiscrepancyAmount(); // Difference in amount
```

---

## Notes

- **Soft Deletes**: All core entities (products, customers, users) support soft deletes for audit trail
- **Movements**: The source of truth for financial data; never delete movement records
- **Snapshots**: sales & rentals store product code + description snapshots to preserve history if product is deleted
- **Status Tracking**: Status enums (available/rented/blocked/laundry/maintenance) are strings for flexibility
- **Audit Trail**: All actions logged via user_id in sales, rentals, movements
- **Locales**: All table/column names in English for international deployment

---

## Future Enhancements

1. **Barcode/QR Scanner** - Camera integration for product lookup
2. **Customer Photos** - DNI + selfie capture for KYC
3. **Late Fee Tracking** - Overdue rental fees as Movement type
4. **Product Swap** - Update rental product during active rental
5. **Loyalty Discounts** - Apply discounts based on rental history
6. **Reports Export** - CSV/PDF export of sales, rentals, movements
7. **Multi-Location Inventory** - Reallocate products between locations
8. **Offline Support** - Progressive Web App (PWA) for offline transactions
9. **Mobile App** - Native iOS/Android app for sellers
10. **Analytics Dashboard** - Charts for sales trends, product popularity, etc.

