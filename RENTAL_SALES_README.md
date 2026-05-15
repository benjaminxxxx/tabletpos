# 🏪 Rental & Sales Management System for Clothing/Accessories Stores

A complete **Laravel 11 + Livewire 3 + MySQL** tablet-optimized point-of-sale and rental management system for clothing and accessories retailers.

---

## 🎯 Overview

This system is purpose-built for small-to-medium clothing rental and sales businesses with:
- **Multi-tenant architecture** (multiple stores, one system)
- **Role-based access** (owner, admin, seller)
- **Tablet-optimized UI** (10–13 inch screens, 48px+ touch targets)
- **Complete financial tracking** (unified movement ledger)
- **Rental management** (customer KYC, deposit tracking, overdue detection)
- **Inventory management** (product catalog, batch registration, locations)
- **Reporting & analytics** (daily reports, cash reconciliation)

---

## 📋 What's Included

### ✅ Database Layer (12 Migrations)
- Multi-tenant accounts with soft deletes
- User accounts with role management (owner/admin/seller)
- Products with automatic public code generation (e.g., ZA0001)
- Product media (photos, videos, 3D models)
- Sales & rental transactions with snapshots
- Customer profiles with KYC data
- Unified financial movement ledger
- Cash close records

### ✅ Business Logic (11 Models)
- **Account** - Multi-tenant parent
- **User** - Extended with account relationships
- **Product** - Auto-generate codes, track usage
- **Sale** - Sales transactions (completed/annulled)
- **Rental** - Rentals with overdue detection
- **Customer** - Profiles with rental history & loyalty
- **Movement** - Financial ledger (source of truth)
- **CashClose** - Daily reconciliation
- **Location** - Warehouse/store management
- **ProductMedia** - Media attachments
- **Purchase** - Batch inventory records

### ✅ User Interfaces (7 Livewire Components)
1. **POS - Sell** - Search, cart, confirm sales
2. **Rental Manager** - Search, customer lookup, create rentals
3. **Product Catalog** - Grid with filtering (category, status, location)
4. **Batch Registration** - Import products via tab-separated format
5. **Daily Report** - Transaction summary with date navigation
6. **Cash Close** - End-of-day reconciliation with discrepancy detection
7. **User Management** - Add/remove/block team members

### ✅ Responsive Views (7 Blade Templates)
- Tablet-first responsive design
- 48×48px minimum tap targets
- 2–3 column grids
- Status color badges
- Toast notifications
- Modal confirmations

### ✅ Documentation (4 Guides)
- **RENTAL_SALES_SYSTEM.md** - Complete system design & architecture
- **SETUP_QUICK_START.md** - Step-by-step installation & testing
- **IMPLEMENTATION_SUMMARY.md** - What was built & feature checklist
- **DemoDataSeeder.php** - Pre-populated test data

---

## 🚀 Quick Start (5 Minutes)

### Prerequisites
- PHP 8.3+, MySQL 8.0+, Node.js 18+

### Setup
```bash
# 1. Install dependencies
composer install
npm install

# 2. Configure database
cp .env.example .env
# Edit .env with your MySQL credentials

# 3. Setup Laravel
php artisan key:generate
php artisan migrate

# 4. Seed demo data (optional)
php artisan db:seed --class=DemoDataSeeder

# 5. Run dev server
php artisan serve          # Terminal 1
npm run dev                # Terminal 2
```

### Test Account
```
Email: admin@store.local
Password: password
```

### Add Routes (routes/web.php)
```php
use App\Livewire\Pos\SellProduct;
use App\Livewire\Pos\RentProduct;
use App\Livewire\Dashboard\DailyReport;
use App\Livewire\Dashboard\CashClose;
use App\Livewire\Inventory\ProductCatalog;
use App\Livewire\Inventory\BatchProductRegistration;
use App\Livewire\Settings\UserManagement;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pos/sell', SellProduct::class)->name('pos.sell');
    Route::get('/pos/rent', RentProduct::class)->name('pos.rent');
    Route::get('/catalog', ProductCatalog::class)->name('catalog');
    Route::get('/inventory/batch', BatchProductRegistration::class)->name('inventory.batch');
    Route::get('/dashboard/report', DailyReport::class)->name('dashboard.report');
    Route::get('/dashboard/cash-close', CashClose::class)->name('cash.close');
    Route::get('/settings/users', UserManagement::class)->name('settings.users');
});
```

---

## 📊 Core Features

### 💰 Sales Module
- Search products by code, name, or brand
- Add multiple items to cart with pricing
- Confirm & complete sales in one action
- Auto-update product status (available → blocked)
- Record all transactions in financial ledger
- Automatic sale count tracking

### 🔑 Rental Module
- Search products available for rental
- Quick customer creation (DNI, name, phone)
- Capture rental amount, deposit, return date
- Track rental lifecycle (active → returned/overdue)
- Auto-update product status (available → rented)
- Count rentals per customer (loyalty tracking)

### 📦 Inventory Management
- Browse 2-column product grid (responsive to 3+ on desktop)
- Filter by category, status, or location
- Auto-generate sequential public codes (ZA0001, ZA0002, etc.)
- Batch register products from spreadsheet
- Support for product media (photos, videos, 3D models)
- Track rent/sale counts per product

### 📈 Reporting
- Daily transaction summary (income, outflow, net)
- Filter sales & rentals by date
- Date navigation (previous/today/next)
- All-in-one financial overview

### 🔐 Cash Management
- Calculate expected daily cash from movements
- Compare with actual count
- Flag discrepancies automatically
- Immutable close records

### 👥 User Management
- Invite users by email
- Assign roles (seller, admin)
- Block/unblock sellers remotely
- Role-based permission enforcement

---

## 🎯 Use Cases

### Use Case 1: Register Inventory
Admin receives 5 new suits from supplier:
```
Blue Suit      Formal    Hugo Boss    Gamarra    ZA    2
Black Suit     Business  Armani       Gamarra    ZA    3
```
→ System auto-generates: ZA0001–ZA0005
→ Ready to sell or rent

### Use Case 2: Complete Sale
Customer walks in, wants to buy a suit:
1. Seller searches "ZA"
2. Selects ZA0001 (Blue Suit)
3. Enters price $50
4. Confirms → Sale complete
5. Product marked as "blocked" (sold out)
6. Movement recorded: +$50 income

### Use Case 3: Rent to Customer
Customer wants to rent a dress for party:
1. Seller searches "VE"
2. Selects VE0002 (Red Dress)
3. Creates customer (Maria, DNI 12345678)
4. Sets: rental=$20, deposit=$50, return=3 days
5. Confirms → Rental active
6. Product marked as "rented"

### Use Case 4: Daily Report
End of day, manager checks what happened:
- Income: $150 (3 sales + 2 rentals)
- Outflow: $0
- Net: $150
- Details: List of all transactions

### Use Case 5: Cash Reconciliation
Before closing register:
1. System says: expected $150
2. Manager counts register: confirmed $150
3. No discrepancy → Close saved
4. Ready for next day

---

## 🏗️ Architecture

### Database Schema
```
accounts (multi-tenant parent)
  ├── account_users (with roles)
  ├── locations
  ├── products (with auto-generated codes)
  │   ├── product_media
  │   ├── sales
  │   └── rentals
  ├── customers
  ├── purchases
  ├── movements (financial ledger)
  └── cash_closes
```

### Role-Based Permissions
```
Seller: Sell, Rent, View Catalog, View Daily Report
Admin: Everything above + Register Products, User Management, Cash Close
Owner: Everything (account creator)
```

### Financial Tracking
- Every transaction → Movement record (never deleted)
- Snapshots preserve product info even if product deleted
- Direction (in/out) enables cash calculations
- Types: sale, rental, sale_return, rental_return, late_fee, etc.

---

## 💻 Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11 |
| Frontend | Livewire 3 + Blade |
| Styling | Tailwind CSS |
| Database | MySQL 8.0+ |
| Build | Vite + Node.js |

---

## 📱 Tablet Optimization

✅ **Responsive Design**
- 2-column grid (responsive to 3+ on desktop)
- Full-width forms
- Bottom navigation bar

✅ **Touch-Friendly**
- All buttons: 48×48px minimum
- Large input fields (44px height)
- 8px spacing between touch targets
- No hover-only interactions

✅ **Performance**
- Lazy-loading components
- Pagination (12 items/page)
- Minimal dependencies
- CSS optimized

---

## 📚 Documentation

| Document | Purpose |
|----------|---------|
| **SETUP_QUICK_START.md** | Install, configure, test (start here!) |
| **RENTAL_SALES_SYSTEM.md** | Complete system design & database schema |
| **IMPLEMENTATION_SUMMARY.md** | What was built, feature checklist, file structure |
| **DemoDataSeeder.php** | Test data generator |

---

## 🔧 Customization

### Change Colors
Edit status badge colors in component views:
```blade
{{ match($product->status) {
    'available' => '#10b981',  // Green
    'rented' => '#f59e0b',     // Amber
    'blocked' => '#ef4444',    // Red
    ...
} }}
```

### Add Custom Fields
Add columns to migrations, update model fillables, extend views:
```php
// In migration
$table->string('custom_field')->nullable();

// In model
protected $fillable = [..., 'custom_field'];
```

### Extend Permissions
Check roles in controllers/views:
```php
if ($user->getRoleInAccount($accountId) === 'admin') {
    // Admin-only action
}
```

---

## 🐛 Troubleshooting

### Migrations Fail
```bash
php artisan migrate:refresh --seed --class=DemoDataSeeder
```

### Livewire Not Updating
```bash
php artisan cache:clear
npm run build
php artisan serve --force
```

### Database Connection Error
- Check `.env` database credentials
- Ensure MySQL is running
- Verify database exists

---

## 🚢 Deployment

1. **Prepare Server**
   - PHP 8.3+, MySQL 8.0+
   - Proper permissions on storage/bootstrap

2. **Install & Configure**
   ```bash
   git clone <repo>
   composer install --optimize-autoloader --no-dev
   npm install && npm run build
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --force
   ```

3. **Configure Web Server**
   - Point to `/public` directory
   - Setup HTTPS certificate
   - Configure MySQL backup

4. **Test**
   - Create account & test user
   - Run through all modules
   - Verify on actual tablet

---

## 📈 Future Roadmap

- 🎥 Barcode/QR scanner integration
- 📷 Camera for customer photos (DNI/selfie)
- 💳 Payment gateway integration
- 📊 Advanced analytics dashboard
- 📱 Native mobile app (iOS/Android)
- 🌐 Offline PWA support
- 🔔 Push notifications
- 📤 CSV/PDF exports
- 🗺️ Multi-location reallocation
- 🎁 Loyalty program

---

## 📝 License

MIT License (modify as needed)

---

## 🤝 Support

For detailed documentation:
- **Setup**: See `SETUP_QUICK_START.md`
- **Architecture**: See `RENTAL_SALES_SYSTEM.md`
- **Implementation**: See `IMPLEMENTATION_SUMMARY.md`

---

## ✨ Key Highlights

✅ **Production-Ready** - Type-hinted, well-documented code
✅ **Multi-Tenant** - Support multiple stores in one system
✅ **Secure** - Role-based permissions, soft deletes, audit trail
✅ **Scalable** - Indexed queries, pagination, optimized migrations
✅ **Tablet-First** - Touch-friendly UI, responsive design
✅ **Financial Integrity** - Immutable movement ledger, snapshots
✅ **Easy to Use** - Intuitive UI, minimal training required
✅ **Extensible** - Clear architecture for custom features

---

**Status**: ✅ Ready for testing & deployment

**Last Updated**: May 2026

**Framework**: Laravel 13, Livewire 4, Tailwind CSS

**Database**: MySQL 8.0+
