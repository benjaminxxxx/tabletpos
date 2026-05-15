<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use App\Models\Location;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Rental;
use App\Models\Movement;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test account
        $account = Account::create([
            'name' => 'Fashion Rental Store',
            'description' => 'Premium clothing rental service',
        ]);

        // Get or create test user
        $user = User::firstOrCreate(
            ['email' => 'admin@store.local'],
            [
                'name' => 'Store Admin',
                'password' => bcrypt('password'),
            ]
        );

        // Assign user to account as admin
        $account->users()->syncWithoutDetaching([
            $user->id => ['role' => 'admin']
        ]);

        // Create locations
        $mainWarehouse = Location::create([
            'account_id' => $account->id,
            'name' => 'Main Warehouse',
            'expected_capacity' => 500,
        ]);

        $store = Location::create([
            'account_id' => $account->id,
            'name' => 'Downtown Store',
            'expected_capacity' => 100,
        ]);

        // Create sample products
        $productData = [
            // Suits (ZA)
            ['name' => 'Blue Business Suit', 'brand' => 'Hugo Boss', 'origin' => 'Gamarra', 'category' => 'ZA'],
            ['name' => 'Black Formal Suit', 'brand' => 'Armani Exchange', 'origin' => 'Gamarra', 'category' => 'ZA'],
            ['name' => 'Gray Wool Suit', 'brand' => 'Calvin Klein', 'origin' => 'Temu', 'category' => 'ZA'],

            // Dresses (VE)
            ['name' => 'Red Evening Gown', 'brand' => 'Forever21', 'origin' => 'Shein', 'category' => 'VE'],
            ['name' => 'Black Cocktail Dress', 'brand' => 'H&M', 'origin' => 'Gamarra', 'category' => 'VE'],
            ['name' => 'White Summer Dress', 'brand' => 'ASOS', 'origin' => 'Temu', 'category' => 'VE'],

            // Casual (CA)
            ['name' => 'Blue Denim Jacket', 'brand' => 'Levi\'s', 'origin' => 'Gamarra', 'category' => 'CA'],
            ['name' => 'White T-Shirt Pack', 'brand' => 'Uniqlo', 'origin' => 'Taobao', 'category' => 'CA'],
        ];

        $products = [];
        foreach ($productData as $data) {
            for ($i = 0; $i < 3; $i++) {
                $publicCode = Product::generatePublicCode($account->id, $data['category']);
                $products[] = Product::create([
                    'account_id' => $account->id,
                    'location_id' => $i % 2 == 0 ? $mainWarehouse->id : $store->id,
                    'public_code' => $publicCode,
                    'name' => $data['name'],
                    'brand' => $data['brand'],
                    'origin' => $data['origin'],
                    'category_prefix' => $data['category'],
                    'status' => 'available',
                    'can_sell' => true,
                    'can_rent' => true,
                ]);
            }
        }

        // Create sample customers
        $customers = [];
        $customerNames = [
            ['name' => 'Juan Pérez', 'dni' => '12345678'],
            ['name' => 'Maria Garcia', 'dni' => '87654321'],
            ['name' => 'Carlos Lopez', 'dni' => '11223344'],
            ['name' => 'Ana Martinez', 'dni' => '44332211'],
        ];

        foreach ($customerNames as $data) {
            $customers[] = Customer::create([
                'account_id' => $account->id,
                'dni' => $data['dni'],
                'full_name' => $data['name'],
                'phone' => '555-' . rand(1000, 9999),
            ]);
        }

        // Create sample sales (today)
        for ($i = 0; $i < 3; $i++) {
            $product = $products[array_rand($products)];
            $amount = rand(30, 100);

            $sale = Sale::create([
                'account_id' => $account->id,
                'user_id' => $user->id,
                'product_id' => $product->id,
                'public_code_snapshot' => $product->public_code,
                'description_snapshot' => $product->name,
                'amount' => $amount,
                'status' => 'completed',
            ]);

            Movement::create([
                'account_id' => $account->id,
                'user_id' => $user->id,
                'type' => 'sale',
                'reference_id' => $sale->id,
                'reference_type' => 'Sale',
                'amount' => $amount,
                'direction' => 'in',
                'notes' => 'Sale: ' . $product->public_code,
            ]);

            $product->update(['status' => 'blocked']);
            $product->increment('sale_count');
        }

        // Create sample rentals (active and past)
        for ($i = 0; $i < 2; $i++) {
            $product = $products[array_rand($products)];
            $customer = $customers[array_rand($customers)];
            $amount = rand(15, 50);
            $deposit = rand(20, 100);

            $rental = Rental::create([
                'account_id' => $account->id,
                'user_id' => $user->id,
                'product_id' => $product->id,
                'customer_id' => $customer->id,
                'public_code_snapshot' => $product->public_code,
                'description_snapshot' => $product->name,
                'amount' => $amount,
                'deposit_amount' => $deposit,
                'return_date' => Carbon::now()->addDays(rand(3, 7)),
                'status' => 'active',
            ]);

            Movement::create([
                'account_id' => $account->id,
                'user_id' => $user->id,
                'type' => 'rental',
                'reference_id' => $rental->id,
                'reference_type' => 'Rental',
                'amount' => $amount,
                'direction' => 'in',
                'notes' => 'Rental: ' . $product->public_code,
            ]);

            if ($deposit > 0) {
                Movement::create([
                    'account_id' => $account->id,
                    'user_id' => $user->id,
                    'type' => 'rental',
                    'reference_id' => $rental->id,
                    'reference_type' => 'Rental',
                    'amount' => $deposit,
                    'direction' => 'in',
                    'notes' => 'Deposit: ' . $product->public_code,
                ]);
            }

            $product->update(['status' => 'rented']);
            $product->increment('rent_count');
        }

        // Create past rentals (returned)
        for ($i = 0; $i < 2; $i++) {
            $product = $products[array_rand($products)];
            $customer = $customers[array_rand($customers)];
            $amount = rand(15, 50);

            $rental = Rental::create([
                'account_id' => $account->id,
                'user_id' => $user->id,
                'product_id' => $product->id,
                'customer_id' => $customer->id,
                'public_code_snapshot' => $product->public_code,
                'description_snapshot' => $product->name,
                'amount' => $amount,
                'return_date' => Carbon::now()->subDays(rand(1, 10)),
                'returned_at' => Carbon::now()->subDays(rand(1, 5)),
                'status' => 'returned',
            ]);

            Movement::create([
                'account_id' => $account->id,
                'user_id' => $user->id,
                'type' => 'rental',
                'reference_id' => $rental->id,
                'reference_type' => 'Rental',
                'amount' => $amount,
                'direction' => 'in',
                'notes' => 'Rental (returned): ' . $product->public_code,
            ]);

            $product->update(['status' => 'available']);
            $product->increment('rent_count');
        }

        echo "\n✅ Demo data created successfully!\n";
        echo "Account: {$account->name}\n";
        echo "Email: {$user->email}\n";
        echo "Password: password\n";
        echo "Products: " . count($products) . "\n";
        echo "Customers: " . count($customers) . "\n";
    }
}
