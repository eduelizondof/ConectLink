<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Product;
use App\Models\ProductSection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductSectionController extends Controller
{
    public function store(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:60', 'alpha_dash', Rule::unique('product_sections')->where('organization_id', $organization->id)],
            'description' => ['nullable', 'string', 'max:300'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        if (!isset($validated['slug']) || empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['organization_id'] = $organization->id;
        $validated['sort_order'] = $organization->productSections()->max('sort_order') + 1;
        $validated['is_active'] = true;

        $section = ProductSection::create($validated);

        return back()->with('success', '¡Sección creada!');
    }

    public function update(Request $request, ProductSection $section)
    {
        $this->authorize('update', $section->organization);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:60', 'alpha_dash', Rule::unique('product_sections')->where('organization_id', $section->organization_id)->ignore($section->id)],
            'description' => ['nullable', 'string', 'max:300'],
            'icon' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        // Generate slug from title if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $section->update($validated);

        return back()->with('success', '¡Sección actualizada!');
    }

    public function destroy(ProductSection $section)
    {
        $this->authorize('update', $section->organization);

        // Detach all products from this section (they remain in the system)
        $section->products()->detach();

        $section->delete();

        return back()->with('success', '¡Sección eliminada!');
    }

    public function reorder(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:product_sections,id'],
            'items.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($validated['items'] as $item) {
            ProductSection::where('id', $item['id'])
                ->where('organization_id', $organization->id)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return back()->with('success', '¡Orden actualizado!');
    }

    /**
     * Assign products to a section.
     */
    public function assignProducts(Request $request, ProductSection $section)
    {
        $this->authorize('update', $section->organization);

        $validated = $request->validate([
            'product_ids' => ['required', 'array'],
            'product_ids.*' => ['exists:products,id'],
        ]);

        // Get products that belong to the same organization
        $productIds = Product::whereIn('id', $validated['product_ids'])
            ->where('organization_id', $section->organization_id)
            ->pluck('id')
            ->toArray();

        // Sync the products (this will add new ones and keep existing)
        $syncData = [];
        foreach ($productIds as $index => $productId) {
            $syncData[$productId] = ['sort_order' => $index];
        }

        $section->products()->sync($syncData);

        return back()->with('success', '¡Productos asignados!');
    }

    /**
     * Remove a product from a section.
     */
    public function removeProduct(ProductSection $section, Product $product)
    {
        $this->authorize('update', $section->organization);

        $section->products()->detach($product->id);

        return back()->with('success', '¡Producto removido de la sección!');
    }
}

