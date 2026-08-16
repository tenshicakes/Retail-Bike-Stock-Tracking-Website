<?php

use App\Models\Account;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('accounts', function (Blueprint $table) {
        $table->id('UserID');
        $table->string('Username');
        $table->string('Password');
        $table->string('Role');
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('accounts');
});

it('allows administrators to view the accounts page', function () {
    $account = Account::create([
        'Username' => 'admin_test',
        'Password' => bcrypt('secret123'),
        'Role' => 'Administrator',
    ]);

    Auth::login($account);

    $this->get('/dashboard/accounts')->assertOk();
});

it('stores a new account with a valid role and hashed password', function () {
    $account = Account::create([
        'Username' => 'admin_test',
        'Password' => bcrypt('secret123'),
        'Role' => 'Administrator',
    ]);

    Auth::login($account);

    $this->post('/dashboard/accounts', [
        'Username' => 'new_staff',
        'Password' => 'secret456',
        'Password_confirmation' => 'secret456',
        'Role' => 'Staff',
    ])->assertRedirect('/dashboard/accounts');

    $this->assertDatabaseHas('accounts', [
        'Username' => 'new_staff',
        'Role' => 'Staff',
    ]);

    $stored = Account::where('Username', 'new_staff')->first();
    expect($stored->Password)->not->toBe('secret456');
    expect(Hash::check('secret456', $stored->Password))->toBeTrue();
});

it('filters accounts by username search query', function () {
    $admin = Account::create([
        'Username' => 'admin_test',
        'Password' => bcrypt('secret123'),
        'Role' => 'Administrator',
    ]);

    Account::create([
        'Username' => 'staff_member',
        'Password' => bcrypt('secret123'),
        'Role' => 'Staff',
    ]);

    Account::create([
        'Username' => 'engineer_one',
        'Password' => bcrypt('secret123'),
        'Role' => 'Mechanic',
    ]);

    Auth::login($admin);

    $this->get('/dashboard/accounts?search=staff')->assertOk()
        ->assertSee('staff_member')
        ->assertDontSee('engineer_one');
});

it('prevents mechanic accounts from editing products', function () {
    $mechanic = new Account([
        'Role' => 'Mechanic',
    ]);

    expect($mechanic->canEditProducts())->toBeFalse();
});
