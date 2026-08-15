<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function home()
    {
        // 1. Calculate the counters
        $totalProducts = Product::count();
        $lowStocks = Product::where('Stocks', '<=', 2)->where('Stocks', '>', 0)->count();
        $noStocks = Product::where('Stocks', 0)->count();

        // 2. Fetch products with Pagination (e.g., 5 items per page)
        $products = Product::latest()->paginate(5);

        // 3. Pass all this data to the view
        return view('dashboard.home', compact('totalProducts', 'lowStocks', 'noStocks', 'products'));
    }

    // --- NEW METHOD ---
    public function processStock(Request $request)
    {
        $request->validate([
            'actionType' => 'required|string',
            'description' => 'nullable|string',
            'items' => 'required|array',
        ]);

        // Use a database transaction so if one product fails, the whole batch rolls back safely
        DB::beginTransaction();

        try {
            $batchId = Str::uuid()->toString(); // Generate unique Batch ID
            $now = now();
            $userId = Auth::id(); // Get the currently logged-in user

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['id']);
                $qty = (int) $item['quantity'];

                // 1. Update Product Stock
                if ($request->actionType === 'Stock-In') {
                    $product->Stocks += $qty;
                } else {
                    // Prevent negative stock
                    if ($product->Stocks < $qty) {
                        throw new \Exception("Not enough stock for " . $product->ProductName);
                    }
                    $product->Stocks -= $qty;
                }
                $product->save();

                // 2. Insert into Logs Table
                Log::create([
                    'UserID' => $userId,
                    'ProductID' => $product->ProductID,
                    'ActionType' => $request->actionType,
                    'Quantity' => $qty,
                    'UnitPrice' => $product->Price,
                    'TotalPrice' => $qty * $product->Price, // Calculated individually
                    'LogDate' => $now,
                    'LogDescription' => $request->description,
                    'BatchID' => $batchId
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaction completed successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}