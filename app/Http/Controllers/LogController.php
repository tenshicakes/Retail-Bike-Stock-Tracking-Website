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
        // Category 
        $categories = Product::select('Category')->distinct()->whereNotNull('Category')->pluck('Category');

        $query = Log::with(['product', 'account'])->latest('LogDate');

        if ($request->filled('category')) {
            // whereHas filter the logs based on the product's category
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('Category', $request->category);
                
                if ($request->filled('subcategory')) {
                    $q->where('SubCategory', $request->subcategory);
                }
            });
        }

        // paginate
        $logs = $query->paginate(5)->withQueryString(); 

        return view('dashboard.logs', compact('logs', 'categories'));
    }

    // ajax cascading dropdown
    public function getSubcategories(Request $request)
    {
        $subcategories = Product::where('Category', $request->category)
                                ->select('SubCategory')
                                ->distinct()
                                ->whereNotNull('SubCategory')
                                ->pluck('SubCategory');
                                
        return response()->json($subcategories);
    }

    // Generate PDF 
    public function exportPdf(Request $request)
    {
        $request->validate([
            'log_ids' => 'required|array',
            'log_ids.*' => 'integer|exists:logs,LogID'
        ]);

        $logs = Log::with(['product', 'account'])
                   ->whereIn('LogID', $request->log_ids)
                   ->orderBy('LogDate', 'desc')
                   ->get();

        $pdf = Pdf::loadView('pdf.invoice', compact('logs'));

        $fileName = 'Transaction_Invoice_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }
}