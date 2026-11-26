<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function home(Request $request)
    {
        // Counter labels
        $totalProducts = Product::count();
        $lowStocks = Product::where('Stocks', '<=', 2)->where('Stocks', '>', 0)->count();
        $noStocks = Product::where('Stocks', 0)->count();
        $products = Product::latest()->paginate(5);

        if ($request->ajax()) {
            return view('components.product-table', compact('products'))->render();
        }

        // Build initial data for the 4 charts
        $chartData = $this->buildChartData($totalProducts, $lowStocks, $noStocks);

        // Pass the data to the page
        return view('dashboard.home', compact('totalProducts', 'lowStocks', 'noStocks', 'products', 'chartData'));
    }

    
    // AJAX endpoint for the "Refresh Charts" button
    public function chartData()
    {
        $totalProducts = Product::count();
        $lowStocks = Product::where('Stocks', '<=', 2)->where('Stocks', '>', 0)->count();
        $noStocks = Product::where('Stocks', 0)->count();

        return response()->json([
            'status' => 'success',
            'charts' => $this->buildChartData($totalProducts, $lowStocks, $noStocks),
            'updatedAt' => now()->format('h:i:s A'),
        ]);
    }

    private function buildChartData(int $totalProducts, int $lowStocks, int $noStocks): array
    {
        $categories = ['Whole Bikes', 'Bike Parts', 'Accessories'];

        // 1. Bar Chart: Total Stock Units per Category
        $stockByCategory = Product::selectRaw('Category, COALESCE(SUM(Stocks), 0) as total_stocks')
            ->whereIn('Category', $categories)
            ->groupBy('Category')
            ->pluck('total_stocks', 'Category');

        $barValues = array_map(
            fn (string $cat): int => (int) ($stockByCategory[$cat] ?? 0),
            $categories
        );

        // 2. Pie Chart: Inventory Value (Price * Stocks) per Category
        $valueByCategory = Product::selectRaw('Category, COALESCE(SUM(Price * Stocks), 0) as total_value')
            ->whereIn('Category', $categories)
            ->groupBy('Category')
            ->pluck('total_value', 'Category');

        $pieValues = array_map(
            fn (string $cat): float => round((float) ($valueByCategory[$cat] ?? 0), 2),
            $categories
        );

        // 3. Line Chart: 7-Day Stock-In vs. Stock-Out Quantity Trend
        $days = collect(range(6, 0))->map(fn (int $daysAgo): string => now()->subDays($daysAgo)->format('Y-m-d'));
        $startDate = now()->subDays(6)->startOfDay();

        $logRows = Log::selectRaw('DATE(LogDate) as log_day, ActionType, COALESCE(SUM(Quantity), 0) as total_qty')
            ->where('LogDate', '>=', $startDate)
            ->groupBy('log_day', 'ActionType')
            ->get();

        $lineLabels = [];
        $stockInSeries = [];
        $stockOutSeries = [];

        foreach ($days as $dateStr) {
            $lineLabels[] = Carbon::parse($dateStr)->format('M d');

            $inRow = $logRows->first(fn ($r) => $r->log_day === $dateStr && $r->ActionType === 'Stock-In');
            $outRow = $logRows->first(fn ($r) => $r->log_day === $dateStr && $r->ActionType === 'Stock-Out');

            $stockInSeries[] = (int) ($inRow->total_qty ?? 0);
            $stockOutSeries[] = (int) ($outRow->total_qty ?? 0);
        }

        // 4. Doughnut Chart: Stock Health Breakdown
        $healthyStocks = max(0, $totalProducts - $lowStocks - $noStocks);

        return [
            'bar' => [
                'labels' => $categories,
                'values' => $barValues,
            ],
            'pie' => [
                'labels' => $categories,
                'values' => $pieValues,
            ],
            'line' => [
                'labels' => $lineLabels,
                'stockIn' => $stockInSeries,
                'stockOut' => $stockOutSeries,
            ],
            'doughnut' => [
                'labels' => ['Healthy Stock', 'Low Stock', 'No Stock'],
                'values' => [$healthyStocks, $lowStocks, $noStocks],
            ],
        ];
    }

    public function processStock(Request $request)
    {
        $request->validate([
            'actionType' => 'required|string',
            'description' => 'nullable|string',
            'items' => 'required|array',
        ]);

        // Transaction method so it rolls back if anything happens
        DB::beginTransaction();

        try {
            $batchId = Str::uuid()->toString(); // Generate unique Batch ID
            $now = now();
            $userId = Auth::id(); // Get which user is logged in

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['id']);
                $qty = (int) $item['quantity'];

                // Update Product Stock
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

                // Insert into Logs Table
                Log::create([
                    'UserID' => $userId,
                    'ProductID' => $product->ProductID,
                    'ActionType' => $request->actionType,
                    'Quantity' => $qty,
                    'UnitPrice' => $product->Price,
                    'TotalPrice' => $qty * $product->Price,
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