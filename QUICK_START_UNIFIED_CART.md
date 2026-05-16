# Unified Cart System - Quick Start Guide

## What Was Built

A complete **unified sales and rental management system** where:
- One transaction can contain BOTH sales items AND rental items
- Real-time cart with Alpine.js calculations
- Complete inventory tracking with stock management
- Sales reporting with filtering, sorting, and CRUD operations
- Role-based permissions (admin/owner can edit all, sellers edit only their own)

---

## 🚀 Quick Setup

### 1. Run Migrations
```bash
php artisan migrate
```

This creates/modifies:
- `sales` table (refactored to be transaction header)
- `sale_details` table (individual sold items)
- `rental_details` table (individual rented items)
- `products` table (adds stock tracking fields)

### 2. Test the System

#### Access Points:
- **ProductCatalog**: Browse products, select "Vender" or "Alquilar"
- **CartManager**: View and manage cart items
- **SalesIndex**: View all transactions, filter and edit

#### Sample Workflow:
1. Go to **ProductCatalog**
2. Click a product
3. Click **"Vender"** → Enter quantity & price → "Agregar al Carrito"
4. Click same/different product
5. Click **"Alquilar"** → Select customer → Enter rental details → "Agregar al Carrito"
6. View **CartManager** → See both items mixed
7. Click **"Procesar Transacción"** → Creates Sale + SaleDetails + RentalDetails
8. Go to **SalesIndex** → See both items in unified table
9. Filter by type, date, or edit individual items

---

## 📁 Key Files

### Models (`app/Models/`)
- `Sale.php` - Transaction container (refactored)
- `SaleDetail.php` - Individual sale item
- `RentalDetail.php` - Individual rental item
- `Product.php` - Enhanced with stock methods

### Components (`app/Livewire/`)
- `CartManager.php` - Unified cart (sales + rentals)
- `ProductCatalog.php` - Product browser with Vender/Alquilar buttons
- `SalesIndex.php` - Transaction reporting & management

### Views (`resources/views/livewire/`)
- `cart/cart-manager.blade.php` - Cart UI with Alpine.js
- `inventory/product-catalog.blade.php` - Product grid + modals
- `sales/sales-index.blade.php` - Transaction table + filters

### Enums (`app/Enums/`)
- `SaleStatus.php` - Transaction states (Spanish)
- `ProductStatus.php` - Product states
- `RentalStatus.php` - Rental states

### Migrations (`database/migrations/`)
- `2024_01_15_000001_refactor_sales_table.php`
- `2024_01_15_000002_create_sale_details_table.php`
- `2024_01_15_000003_create_rental_details_table.php`
- `2024_01_15_000004_add_stock_fields_to_products_table.php`

---

## 🎯 Core Features

### ✅ CartManager
- Add sales items (qty + price)
- Add rental items (customer, DNI, dates, guarantee)
- Edit quantity and price in real-time
- Session-based persistence
- Calculate totals automatically
- Process transaction (creates Sale + Details)

### ✅ ProductCatalog
- Grid view of products
- Filter by category, status, location
- **Vender** modal: quantity + price
- **Alquilar** modal: customer selection, DNI, photos, dates, guarantee
- Add items to unified cart

### ✅ SalesIndex
- Unified table: each row = 1 line item (sale or rental)
- Columns: Transaction#, Type(badge), Date, Product, Qty, Price, Subtotal, Seller, Actions
- **Filter by**:
  - Type (All / Sales Only / Rentals Only)
  - Single date
  - Date range (from/to)
- **Edit item**: change quantity, price, product (sales only)
- **Delete item**: remove and revert stock
- **Totals dashboard**: Sales, Rentals, Guarantees, Transactions, Grand Total

### ✅ Stock Management
- `quantity_available` - Items ready to sell/rent
- `quantity_rented_out` - Currently rented
- `quantity_sold_total` - Lifetime sold
- Auto-update on sale/rental/return
- Auto-revert on item deletion

### ✅ Permissions
- **Admin/Owner**: Edit any transaction
- **Seller**: Edit only their own transactions
- **Viewer**: Read-only access

---

## 📊 Transaction Structure

### Example Sale #1:

```
Sale (transaction_number = #001, total_amount = $2,750)
├── SaleDetail 1
│   ├─ Product: Laptop
│   ├─ Quantity: 2
│   ├─ Unit Price: $800
│   └─ Subtotal: $1,600
│
├── SaleDetail 2
│   ├─ Product: Mouse
│   ├─ Quantity: 5
│   ├─ Unit Price: $15
│   └─ Subtotal: $75
│
└── RentalDetail 1
    ├─ Product: Camera
    ├─ Customer: Juan Pérez (DNI: 12345678)
    ├─ Quantity: 1
    ├─ Rental Price: $50/day
    ├─ Guarantee: $300
    ├─ Start: 2024-01-20
    ├─ Return: 2024-01-25
    └─ Subtotal: $250 + $800 guarantee
```

**Grand Total**: $1,600 + $75 + $250 rental + $800 guarantee = $2,725

---

## 🔄 Complete Workflow

```
1. ProductCatalog
   └─ Click product → Choose "Vender" or "Alquilar"

2. Vender Modal
   └─ Qty + Price → "Agregar al Carrito"

3. Alquilar Modal
   └─ Customer + DNI + Photos + Dates + Guarantee → "Agregar al Carrito"

4. CartManager
   ├─ Tab: Ventas (editable)
   ├─ Tab: Alquileres (editable)
   ├─ Real-time totals
   └─ "Procesar Transacción"

5. SalesIndex
   ├─ View all items in unified table
   ├─ Filter by type/date
   ├─ Edit (qty, price, product)
   └─ Delete (reverts stock)
```

---

## 📝 Configuration

### Routes (Add to `routes/web.php`)
```php
Route::group(['middleware' => 'auth', 'prefix' => 'pos'], function () {
    Route::get('products', \App\Livewire\Inventory\ProductCatalog::class)->name('products');
    Route::get('cart', \App\Livewire\Cart\CartManager::class)->name('cart');
    Route::get('sales', \App\Livewire\Sales\SalesIndex::class)->name('sales');
});
```

### Navigation (Add to your layout)
```blade
<nav>
    <a href="{{ route('pos.products') }}">Catálogo</a>
    <a href="{{ route('pos.cart') }}">Carrito</a>
    <a href="{{ route('pos.sales') }}">Reportes</a>
</nav>
```

---

## 🧪 Testing Checklist

- [ ] Add product via **Vender** → verify in cart
- [ ] Add product via **Alquilar** → verify in cart
- [ ] Edit sale quantity → total updates
- [ ] Edit rental guarantee → total updates
- [ ] Delete sale item → stock reverts
- [ ] Process transaction → Sale + Details created
- [ ] View SalesIndex → all items visible
- [ ] Filter by "Ventas" → rentals hidden
- [ ] Filter by date → correct items shown
- [ ] Edit sale item → subtotal updates
- [ ] Delete with last item → Sale auto-deleted
- [ ] Verify permissions → seller can't edit other's sales

---

## 🐛 Troubleshooting

### Cart not persisting
- Check session configuration in `.env`
- Verify `CACHE_DRIVER` is set (e.g., `redis` or `file`)

### Stock not updating
- Ensure `Product::reduceAvailableQuantity()` called in CartManager::processCart()
- Check migrations ran successfully

### Permission errors
- Verify `auth()->user()` returns valid user
- Check user roles in database
- Confirm `Sale::where('user_id', auth()->id())`  filter works

### Totals not calculating
- Check Alpine.js console for errors
- Verify `calculateTotals()` called after each edit
- Check `$saleItems` and `$rentalItems` have correct structure

---

## 📚 Documentation Files

- **UNIFIED_CART_SYSTEM_SUMMARY.md** - Complete reference (665 lines)
- **CART_SALES_PROGRESS.md** - Implementation progress & remaining tasks
- **QUICK_START_UNIFIED_CART.md** - This file (quick reference)

---

## 🚀 Next Steps

1. **Run migrations**: `php artisan migrate`
2. **Add routes**: Copy to `routes/web.php`
3. **Add navigation**: Link to your layout
4. **Create demo data**: Seed some products
5. **Test workflow**: Complete end-to-end test
6. **Deploy**: Push to production

---

## 💡 Key Concepts

### One Sale, Multiple Types
Unlike traditional systems with separate sales and rentals, this system allows mixing both in a **single transaction** (Sale). This is useful for:
- Quick check-in/check-out (rent one, buy another)
- Bulk orders with mixed items
- Simplified reporting (one transaction number covers everything)

### Real-Time Stock
- `quantity_available` decreases when item added to cart
- Returns to original when item removed from cart
- Finalizes when transaction processed
- Reverts if item deleted from SalesIndex

### Permission Isolation
- Sellers can only edit their own transactions
- Admins/Owners can edit any transaction
- Prevents unauthorized modifications
- Maintains audit trail (user_id on Sale)

---

## ✨ You're All Set!

The system is ready to use. Start with ProductCatalog, try adding items to the cart, process a transaction, and explore SalesIndex.

For detailed documentation, see **UNIFIED_CART_SYSTEM_SUMMARY.md**.

Happy selling and renting! 🎉
