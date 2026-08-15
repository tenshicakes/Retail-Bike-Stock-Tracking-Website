<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;

class LogController extends Controller
{
    public function index(Request $request)
    {
        // 1. Get unique categories for the first dropdown
        $categories = Product::select('Category')->distinct()->whereNotNull('Category')->pluck('Category');

        // 2. Start the query builder
        $query = Log::with(['product', 'account'])->latest('LogDate');

        // 3. Apply Filters if they exist
        if ($request->filled('category')) {
            // whereHas filters the logs based on the connected product's category
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('Category', $request->category);
                
                if ($request->filled('subcategory')) {
                    $q->where('SubCategory', $request->subcategory);
                }
            });
        }

        // 4. Paginate results
        $logs = $query->paginate(10)->withQueryString(); // withQueryString remembers filters on page 2!

        return view('dashboard.logs', compact('logs', 'categories'));
    }

    // AJAX endpoint for cascading dropdown
    public function getSubcategories(Request $request)
    {
        $subcategories = Product::where('Category', $request->category)
                                ->select('SubCategory')
                                ->distinct()
                                ->whereNotNull('SubCategory')
                                ->pluck('SubCategory');
                                
        return response()->json($subcategories);
    }

    // Generate the PDF
    public function exportPdf(Request $request)
    {
        $request->validate([
            'log_ids' => 'required|array',
            'log_ids.*' => 'integer|exists:logs,LogID'
        ]);

        // Fetch the selected logs with their related data
        $logs = Log::with(['product', 'account'])
                   ->whereIn('LogID', $request->log_ids)
                   ->orderBy('LogDate', 'desc')
                   ->get();

        // Pass data to the PDF view
        $pdf = Pdf::loadView('pdf.invoice', compact('logs'));

        // Force download
        $fileName = 'Transaction_Invoice_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }
}