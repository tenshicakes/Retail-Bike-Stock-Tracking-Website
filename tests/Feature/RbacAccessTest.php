<?php

use App\Models\Account;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('accounts', function (Blueprint $table) {
        $table->id('UserID');
        $table->string('Username');
        $table->string('Password');
        $table->string('Role');
        $table->timestamps();
    });

    Schema::create('products', function (Blueprint $table) {
        $table->id('ProductID');
        $table->string('ProductName');
        $table->string('Category')->nullable();
        $table->string('SubCategory')->nullable();
        $table->decimal('Price', 10, 2)->default(0);
        $table->integer('Stocks')->default(0);
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('products');
    Schema::dropIfExists('accounts');
});

it('denies staff access to the low stock and no stock pages', function () {
    $account = Account::create([
        'Username' => 'staff_rbac',
        'Password' => bcrypt('secret123'),
        'Role' => 'Staff',
    ]);

    Auth::login($account);

    $this->get('/dashboard/lowstock')->assertStatus(403);
    $this->get('/dashboard/nostock')->assertStatus(403);
});

it('denies mechanic access to logs and accounts pages', function () {
    $account = Account::create([
        'Username' => 'mechanic_rbac',
        'Password' => bcrypt('secret123'),
        'Role' => 'Mechanic',
    ]);

    Auth::login($account);

    $this->get('/dashboard/logs')->assertStatus(403);
    $this->get('/dashboard/accounts')->assertStatus(403);
});

it('allows owner access to products while denying accounts', function () {
    $account = Account::create([
        'Username' => 'owner_rbac',
        'Password' => bcrypt('secret123'),
        'Role' => 'Owner',
    ]);

    Auth::login($account);

    $this->get('/dashboard/products')->assertStatus(200);
    $this->get('/dashboard/accounts')->assertStatus(403);
});
