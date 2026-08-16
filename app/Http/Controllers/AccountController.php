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
        $validated = $request->validate([
            'Username' => ['required', 'string', 'max:120', 'unique:accounts,Username'],
            'Password' => ['required', 'string', 'min:6', 'confirmed'],
            'Role' => ['required', 'in:Administrator,Owner,Staff,Mechanic'],
        ]);

        Account::create([
            'Username' => trim($validated['Username']),
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
        $validated = $request->validate([
            'Username' => ['required', 'string', 'max:120', 'unique:accounts,Username,' . $account->UserID . ',UserID'],
            'Password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'Role' => ['required', 'in:Administrator,Owner,Staff,Mechanic'],
        ]);

        $account->Username = trim($validated['Username']);
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
