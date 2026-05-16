# Unified Cart & Sales System - Implementation Progress

## COMPLETED ✅

### 1. Enums (3 files - 130 líneas total)
- **SaleStatus.php**: Pendiente, Completada, Cancelada con labels, colores y badges
- **ProductStatus.php**: Disponible, Vendido, Bloqueado, Alquilado, Perdido
- **RentalStatus.php**: Pendiente, Activo, Devuelto, Perdido, Vencido

### 2. Migrations (4 files)
- **refactor_sales_table.php**: Elimina campos viejos, agrega transaction_number, transaction_date, total_amount, status
- **create_sale_details_table.php**: Tabla para items vendidos (producto, cantidad, precio, subtotal)
- **create_rental_details_table.php**: Tabla SEPARADA para alquileres (DNI, fotos, garantía, fechas, status)
- **add_stock_fields_to_products_table.php**: quantity_available, quantity_rented_out, quantity_sold_total

### 3. Models Refactored (5 files)
- **Sale.php**: Ahora contiene transaction_number, transaction_date, total_amount, relaciones con SaleDetail y RentalDetail
- **SaleDetail.php**: Items de venta con cálculo automático de subtotal
- **RentalDetail.php**: Items de alquiler con métodos isOverdue() y getDaysRented()
- **Product.php**: Nuevos campos de stock y métodos (reduceAvailable, increaseRented, etc)
- **RentalStatus.php**: Enum para estados de alquiler

### 4. CartManager Component (333 líneas)
- **app/Livewire/Cart/CartManager.php**: Componente completo que maneja:
  - Agregar items de venta (con validación de stock)
  - Agregar items de alquiler (con datos de cliente)
  - Modificar precios unitarios
  - Modificar cantidades y garantías
  - Cambiar producto en una venta
  - Eliminar items
  - Procesar carrito completo (crea Sale + SaleDetail + RentalDetail)
  - Actualiza stock y estado de productos automáticamente
  - Gestiona sesión para persistencia

### 5. Cart View (213 líneas)
- **resources/views/livewire/cart/cart-manager.blade.php**:
  - Interfaz con tabs (Ventas | Alquileres)
  - Cálculos dinámicos con Alpine.js
  - Campos editables para cantidad, precio, garantía
  - Resumen de totales en tiempo real
  - Botón para procesar transacción

## TODO - REMAINING TASKS 🚧

### 6. Refactor ProductIndex Component
**File**: `app/Livewire/Products/ProductIndex.php`

Tasks:
- Remove "Edit" button from product detail modal
- Replace with TWO buttons: "Vender" and "Alquilar"
- "Vender" button: Opens modal with simple price input, calls addSaleItem()
- "Alquilar" button: Opens modal with fields:
  - Customer selector/search
  - DNI number
  - DNI photo upload
  - Additional photo upload (optional)
  - Guarantee amount (optional)
  - Rental start date
  - Rental return date
  - Observations
  - Calls addRentalItem() with all data

**View Changes**: `resources/views/livewire/products/product-index.blade.php`
- Update modal to show Vender/Alquilar buttons
- Create sell modal (simple)
- Create rental modal (complex with file uploads and date pickers)

### 7. Create SalesIndex Component
**File**: `app/Livewire/Sales/SalesIndex.php`

Key Features:
- Display table where EACH ROW = 1 PRODUCT (not one row per sale)
- Combine SaleDetail and RentalDetail rows
- Columns: Transaction#, Type(badge), Date, Product Code, Product Name, Qty, Price, Subtotal, Seller, Actions
- Filters:
  - Type: All / Sales only / Rentals only
  - Specific date
  - Date range (from-to)
- Permissions:
  - Admin/Owner: see ALL, edit ALL
  - Seller: see ALL, edit ONLY their own
- Totals card: Total Sales, Total Rentals, Total Guarantees, Transaction Count
- Edit modal for individual items:
  - Change quantity
  - Change price
  - Change product (for sales only)
  - Delete item (reverts product status)
- Update totals dynamically

**View**: `resources/views/livewire/sales/sales-index.blade.php`
- Filters section at top
- Totals cards
- Main table with sorting
- Color-coded badges (Venta=green, Alquiler=purple)
- Modal for editing items
- Pagination (50 per page)

### 8. Create Sales Routes
**File**: `routes/web.php`

Add:
```php
Route::group(['middleware' => 'can:view-reports', 'prefix' => 'sales', 'as' => 'sales.'], function () {
    Route::get('index', SalesIndex::class)->name('index');
});
```

And update product routes to include new cart endpoints:
```php
Route::post('cart/add-sale', [CartManager::class, 'addSaleItem']);
Route::post('cart/add-rental', [CartManager::class, 'addRentalItem']);
Route::post('cart/process', [CartManager::class, 'processCart']);
```

### 9. Database Seeders
**File**: `database/seeders/DemoDataSeeder.php`

Update to:
- Create demo products with quantity_available > 1
- Create test sales with SaleDetails
- Create test rentals with RentalDetails
- Test data for different product statuses

### 10. Update Navigation/Dashboard
**File**: `resources/views/dashboard.blade.php`

- Update route from "dashboard/reports" to "sales/index"
- Update route names and links

## IMPORTANT NOTES 📌

### Structure Overview
```
Sale (transaction #1)
├── SaleDetail 1 (Product A - vendido)
├── SaleDetail 2 (Product B - vendido)
└── RentalDetail 1 (Product C - alquilado, con DNI y fotos)
```

### Stock Management Flow
1. **On Sale**: `product.quantity_available -= qty`, `product.quantity_sold_total += qty`
2. **On Rental**: `product.quantity_available -= qty`, `product.quantity_rented_out += qty`
3. **On Rental Return**: `product.quantity_available += qty`, `product.quantity_rented_out -= qty`
4. **On Item Delete**: Reverse the above based on type

### Permission Model
- **Admin**: Full access to all features
- **Owner**: Full access to all features (for their account)
- **Seller**: Can see all sales but edit only their own (filter by user_id)
- **Viewer**: Can only view reports (no edit)

### Key Alpine.js Calculations
- Real-time subtotals: quantity × unit_price
- Running totals: sum of all subtotals + guarantees
- Grand total: sales + rentals + guarantees

### File Sizes (Current)
- CartManager.php: 333 líneas
- cart-manager.blade.php: 213 líneas
- Enums: 130 líneas total
- Models: 250 líneas total
- Migrations: 180 líneas total

**Total committed: 1087 insertions across 13 files**

## Next Steps
1. Refactor ProductIndex to show Vender/Alquilar buttons
2. Create complex rental modal with validations
3. Build SalesIndex with full CRUD operations
4. Add date pickers and file upload handling
5. Create comprehensive error handling
6. Test full workflow: Select Product → Add to Cart → Process → View in Sales

## Git Status
- Branch: v0/benuserxxx-9845-0c0bcadc
- Commits: Cart system ready for next phase
- Ready to push when next phase complete
