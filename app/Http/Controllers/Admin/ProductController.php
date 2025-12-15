<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function store(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $limits = $request->user()->getLimits();
        if ($organization->products()->count() >= $limits->max_products_per_org) {
            return back()->with('error', 'Has alcanzado el límite de productos.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:60', 'alpha_dash', Rule::unique('products')->where('organization_id', $organization->id)],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'short_description' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'currency' => ['nullable', 'string', 'size:3'],
            'image' => ['nullable', 'image', 'max:2048'],
            'external_url' => ['nullable', 'string', 'max:255'],
            'whatsapp_message' => ['nullable', 'string', 'max:300'],
            'is_featured' => ['boolean'],
            'is_available' => ['boolean'],
        ]);

        if (!isset($validated['slug']) || empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['organization_id'] = $organization->id;
        $validated['sort_order'] = $organization->products()->max('sort_order') + 1;
        $validated['is_active'] = true;

        Product::create($validated);

        return back()->with('success', '¡Producto creado!');
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product->organization);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:60', 'alpha_dash', Rule::unique('products')->where('organization_id', $product->organization_id)->ignore($product->id)],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'short_description' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'image' => ['nullable', 'image', 'max:2048'],
            'external_url' => ['nullable', 'string', 'max:255'],
            'whatsapp_message' => ['nullable', 'string', 'max:300'],
            'is_featured' => ['boolean'],
            'is_available' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        // Generate slug from name if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                \Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return back()->with('success', '¡Producto actualizado!');
    }

    public function destroy(Product $product)
    {
        $this->authorize('update', $product->organization);

        if ($product->image) {
            \Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return back()->with('success', '¡Producto eliminado!');
    }

    public function reorder(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:products,id'],
            'items.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($validated['items'] as $item) {
            Product::where('id', $item['id'])
                ->where('organization_id', $organization->id)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return back()->with('success', '¡Orden actualizado!');
    }

    /**
     * Update sections for a product.
     */
    public function updateSections(Request $request, Product $product)
    {
        $this->authorize('update', $product->organization);

        $validated = $request->validate([
            'section_ids' => ['array'],
            'section_ids.*' => ['exists:product_sections,id'],
        ]);

        // Verify sections belong to the same organization
        $sectionIds = \App\Models\ProductSection::whereIn('id', $validated['section_ids'] ?? [])
            ->where('organization_id', $product->organization_id)
            ->pluck('id')
            ->toArray();

        $product->sections()->sync($sectionIds);

        return back()->with('success', '¡Secciones actualizadas!');
    }
}





