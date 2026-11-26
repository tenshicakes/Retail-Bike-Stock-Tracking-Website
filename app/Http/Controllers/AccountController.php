<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::query();

        if ($request->filled('search')) {
            $query->where('Username', 'like', '%' . trim($request->search) . '%');
        }

        $accounts = $query->orderBy('UserID', 'desc')->paginate(5)->withQueryString();

        return view('dashboard.accounts', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'Username' => trim((string) $request->Username),
        ]);

        $validated = $request->validate([
            'Username' => ['required', 'string', 'email', 'max:120', 'unique:accounts,Username'],
            'Password' => ['required', 'string', 'min:8', 'confirmed'],
            'Role' => ['required', 'in:Administrator,Owner,Staff,Mechanic'],
        ], [
            'Username.email' => 'Please enter a valid email address (e.g., name@example.com).',
            'Password.min' => 'Password must be at least 8 characters long.',
        ]);

        Account::create([
            'Username' => $validated['Username'],
            'Password' => Hash::make($validated['Password']),
            'Role' => $validated['Role'],
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Account created successfully.',
            ]);
        }

        return redirect('/dashboard/accounts')->with('success', 'Account created successfully.');
    }

    public function update(Request $request, Account $account)
    {
        $request->merge([
            'Username' => trim((string) $request->Username),
        ]);

        $validated = $request->validate([
            'Username' => ['required', 'string', 'email', 'max:120', 'unique:accounts,Username,' . $account->UserID . ',UserID'],
            'Password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'Role' => ['required', 'in:Administrator,Owner,Staff,Mechanic'],
        ], [
            'Username.email' => 'Please enter a valid email address (e.g., name@example.com).',
            'Password.min' => 'Password must be at least 8 characters long.',
        ]);

        // Prevent the currently logged-in Administrator from changing/downgrading their own role
        if ((int) $account->UserID === (int) Auth::id() && $validated['Role'] !== $account->Role) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot change or downgrade your own Administrator role.',
            ], 422);
        }

        // Prevent downgrading the last remaining Administrator in the system
        if (
            strcasecmp((string) $account->Role, 'Administrator') === 0 &&
            strcasecmp($validated['Role'], 'Administrator') !== 0 &&
            Account::where('Role', 'Administrator')->count() <= 1
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot downgrade the last remaining Administrator account.',
            ], 422);
        }

        $account->Username = $validated['Username'];
        $account->Role = $validated['Role'];

        if (! empty($validated['Password'])) {
            $account->Password = Hash::make($validated['Password']);
        }

        $account->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Account updated successfully.',
            ]);
        }

        return redirect('/dashboard/accounts')->with('success', 'Account updated successfully.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:accounts,UserID'],
        ]);

        $currentUserId = Auth::id();
        $ids = collect($request->ids)
            ->filter(fn (mixed $accountId): bool => (int) $accountId !== (int) $currentUserId)
            ->values()
            ->all();

        if (! empty($ids)) {
            Account::whereIn('UserID', $ids)->delete();
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Selected account(s) deleted successfully.',
            ]);
        }

        return redirect('/dashboard/accounts')->with('success', 'Selected account(s) deleted successfully.');
    }
}