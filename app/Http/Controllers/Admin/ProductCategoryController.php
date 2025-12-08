<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    public function store(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:60', 'alpha_dash', Rule::unique('product_categories')->where('organization_id', $organization->id)],
            'description' => ['nullable', 'string', 'max:300'],
            'icon' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'max:1024'],
        ]);

        if (!isset($validated['slug']) || empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $validated['organization_id'] = $organization->id;
        $validated['sort_order'] = $organization->productCategories()->max('sort_order') + 1;
        $validated['is_active'] = true;

        ProductCategory::create($validated);

        return back()->with('success', '¡Categoría creada!');
    }

    public function update(Request $request, ProductCategory $category)
    {
        $this->authorize('update', $category->organization);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:60', 'alpha_dash', Rule::unique('product_categories')->where('organization_id', $category->organization_id)->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:300'],
            'icon' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'max:1024'],
            'is_active' => ['boolean'],
        ]);

        // Generate slug from name if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            if ($category->image) {
                \Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($validated);

        return back()->with('success', '¡Categoría actualizada!');
    }

    public function destroy(ProductCategory $category)
    {
        $this->authorize('update', $category->organization);

        // Move products to uncategorized
        $category->products()->update(['category_id' => null]);

        if ($category->image) {
            \Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return back()->with('success', '¡Categoría eliminada!');
    }
}





