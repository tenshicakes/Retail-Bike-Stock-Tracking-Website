<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    protected function buildStatusProducts(string $type, Request $request)
    {
        $categoryMap = config('bike_categories');
        $categories = array_keys($categoryMap);

        $query = Product::query();

        if ($type === 'low') {
            $query->where('Stocks', '>', 0)
                ->where('Stocks', '<=', 2);
        }

        if ($type === 'none') {
            $query->where('Stocks', 0);
        }

        if ($request->filled('search')) {
            $query->where('ProductName', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('Category', $request->category);
        }

        if ($request->filled('subcategory')) {
            $query->where('SubCategory', $request->subcategory);
        }

        $products = $query->orderBy('ProductName', 'asc')->paginate(5)->withQueryString();

        return compact('categories', 'categoryMap', 'products');
    }

    public function index(Request $request)
    {
        $data = $this->buildStatusProducts('all', $request);
        $products = $data['products'];
        $categoryMap = $data['categoryMap'];
        $categories = $data['categories'];

        if ($request->ajax()) {
            return view('components.search-result', ['products' => $products, 'showStockOut' => true])->render();
        }

        return view('dashboard.products', compact('products', 'categories', 'categoryMap'));
    }

    public function lowStockIndex(Request $request)
    {
        $data = $this->buildStatusProducts('low', $request);
        $products = $data['products'];
        $categoryMap = $data['categoryMap'];
        $categories = $data['categories'];

        if ($request->ajax()) {
            return view('components.search-result', ['products' => $products, 'showStockOut' => true])->render();
        }

        return view('dashboard.lowstock', compact('products', 'categories', 'categoryMap'));
    }

    public function noStockIndex(Request $request)
    {
        $data = $this->buildStatusProducts('none', $request);
        $products = $data['products'];
        $categoryMap = $data['categoryMap'];
        $categories = $data['categories'];

        if ($request->ajax()) {
            return view('components.search-result', ['products' => $products, 'showStockOut' => false])->render();
        }

        return view('dashboard.nostock', compact('products', 'categories', 'categoryMap'));
    }

    public function lowStockSearch(Request $request)
    {
        $data = $this->buildStatusProducts('low', $request);

        return view('components.search-result', ['products' => $data['products'], 'showStockOut' => true])->render();
    }

    public function noStockSearch(Request $request)
    {
        $data = $this->buildStatusProducts('none', $request);

        return view('components.search-result', ['products' => $data['products'], 'showStockOut' => false])->render();
    }

    public function store(Request $request)
    {
        $request->validate([
            'ProductName' => 'required|string|max:255',
            'Category' => 'required|string|max:100',
            'SubCategory' => 'required|string|max:100',
            'Price' => 'required|numeric|min:0',
            'Stocks' => 'required|integer|min:0',
        ]);

        $categoryMap = config('bike_categories');
        $validCategory = $request->Category;
        $validSubcategory = $request->SubCategory;

        if (!isset($categoryMap[$validCategory]) || !in_array($validSubcategory, $categoryMap[$validCategory], true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please select a valid category and subcategory combination.',
            ], 422);
        }

        try {
            $product = Product::create([
                'ProductName' => trim($request->ProductName),
                'Category' => $validCategory,
                'SubCategory' => $validSubcategory,
                'Price' => $request->Price,
                'Stocks' => $request->Stocks,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Product created successfully.',
                'product' => $product,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create product: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getSubcategories(Request $request)
    {
        $category = $request->query('category');
        $categoryMap = config('bike_categories');

        if ($category && isset($categoryMap[$category])) {
            return response()->json($categoryMap[$category]);
        }

        return response()->json([]);
    }

    // Dynamic AJAX Search & Category/SubCategory Filtering
    public function search(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $query->where('ProductName', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('Category', $request->category);
        }

        if ($request->filled('subcategory')) {
            $query->where('SubCategory', $request->subcategory);
        }

        $products = $query->orderBy('ProductName', 'asc')->paginate(5)->withQueryString();

        // REUSING YOUR EXISTING TABLE COMPONENT DIRECTLY HERE:
        return view('components.search-result', compact('products'))->render();
    }

    // Bulk Edit Products
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.ProductID' => 'required|exists:products,ProductID',
            'products.*.ProductName' => 'required|string|max:255',
            'products.*.Category' => 'required|string|max:100',
            'products.*.SubCategory' => 'required|string|max:100',
            'products.*.Price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->products as $item) {
                Product::where('ProductID', $item['ProductID'])->update([
                    'ProductName' => $item['ProductName'],
                    'Category'    => $item['Category'],
                    'SubCategory' => $item['SubCategory'],
                    'Price'       => $item['Price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Products updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update: ' . $e->getMessage()
            ], 500);
        }
    }
}