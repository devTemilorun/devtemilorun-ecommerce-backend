<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');
        
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $products = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json($products);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:draft,published,archived',
        ]);
        
        $validated['slug'] = Str::slug($validated['name']);
        $validated['sku'] = strtoupper(Str::random(10));
        
        $product = Product::create($validated);
        
        return response()->json($product, 201);
    }
    
    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);
        return response()->json($product);
    }
    
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'status' => 'sometimes|in:draft,published,archived',
        ]);
        
        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        
        $product->update($validated);
        
        return response()->json($product);
    }
    
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        
        return response()->json(['message' => 'Product deleted successfully']);
    }
    
    public function toggleFeatured($id)
    {
        $product = Product::findOrFail($id);
        $product->is_featured = !$product->is_featured;
        $product->save();
        
        return response()->json(['message' => 'Product featured status updated']);
    }
}