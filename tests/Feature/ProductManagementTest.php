<?php

use App\Models\Account;
use Illuminate\Support\Facades\Hash;

it('allows an administrator to add a product', function () {
    $account = Account::create([
        'Username' => 'adminuser',
        'Password' => Hash::make('secret123'),
        'Role' => 'Administrator',
    ]);

    $this->actingAs($account, 'web')
        ->post('/dashboard/products', [
            'ProductName' => 'City Bike Pro',
            'Category' => 'Whole Bikes',
            'SubCategory' => 'Road Bikes',
            'Price' => '1299.99',
            'Stocks' => 12,
        ])
        ->assertStatus(201);

    $this->assertDatabaseHas('products', [
        'ProductName' => 'City Bike Pro',
        'Category' => 'Whole Bikes',
        'SubCategory' => 'Road Bikes',
        'Price' => 1299.99,
        'Stocks' => 12,
    ]);
});

it('blocks mechanics from adding products', function () {
    $account = Account::create([
        'Username' => 'mechanicuser',
        'Password' => Hash::make('secret123'),
        'Role' => 'Mechanic',
    ]);

    $this->actingAs($account, 'web')
        ->post('/dashboard/products', [
            'ProductName' => 'Forbidden Bike',
            'Category' => 'Whole Bikes',
            'SubCategory' => 'Road Bikes',
            'Price' => 999,
            'Stocks' => 5,
        ])
        ->assertStatus(403);
});
