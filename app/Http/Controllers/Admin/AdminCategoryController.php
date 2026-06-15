<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->orderBy('sort_order')->get();
        return response()->json($categories);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:categories',
            'description' => 'nullable|string',
        ]);
        
        $category = Category::create($validated);
        return response()->json($category, 201);
    }
    
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }
    
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:categories,slug,' . $id,
            'description' => 'nullable|string',
        ]);
        
        $category->update($validated);
        return response()->json($category);
    }
    
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Category deleted']);
    }
    
    public function toggleStatus($id)
    {
        $category = Category::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();
        return response()->json($category);
    }
    
    public function reorder(Request $request)
    {
        foreach ($request->categories as $categoryData) {
            Category::where('id', $categoryData['id'])->update(['sort_order' => $categoryData['order']]);
        }
        return response()->json(['message' => 'Categories reordered']);
    }
}
