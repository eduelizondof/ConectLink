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
            'slug' => ['nullable', 'string', 'max:60', 'alpha_dash'],
            'description' => ['nullable', 'string', 'max:300'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        // Generate slug from title if not provided
        if (!isset($validated['slug']) || empty($validated['slug'])) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $counter = 1;

            // Ensure slug is unique for this organization
            while (ProductSection::where('organization_id', $organization->id)
                ->where('slug', $slug)
                ->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $validated['slug'] = $slug;
        } else {
            // Validate that provided slug is unique
            $exists = ProductSection::where('organization_id', $organization->id)
                ->where('slug', $validated['slug'])
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'slug' => 'Ya existe una sección con este nombre o slug. Por favor, elige otro nombre.',
                ])->withInput();
            }
        }

        $validated['organization_id'] = $organization->id;
        $validated['sort_order'] = $organization->productSections()->max('sort_order') + 1;
        $validated['is_active'] = true;

        try {
            $section = ProductSection::create($validated);
            return back()->with('success', '¡Sección creada!');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle any remaining unique constraint violations
            if ($e->getCode() === '23000') {
                return back()->withErrors([
                    'title' => 'Ya existe una sección con este nombre. Por favor, elige otro nombre.',
                ])->withInput();
            }
            throw $e;
        }
    }

    public function update(Request $request, ProductSection $section)
    {
        $this->authorize('update', $section->organization);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:60', 'alpha_dash'],
            'description' => ['nullable', 'string', 'max:300'],
            'icon' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        // Generate slug from title if not provided
        if (empty($validated['slug'])) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $counter = 1;

            // Ensure slug is unique for this organization (excluding current section)
            while (ProductSection::where('organization_id', $section->organization_id)
                ->where('slug', $slug)
                ->where('id', '!=', $section->id)
                ->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $validated['slug'] = $slug;
        } else {
            // Validate that provided slug is unique (excluding current section)
            $exists = ProductSection::where('organization_id', $section->organization_id)
                ->where('slug', $validated['slug'])
                ->where('id', '!=', $section->id)
                ->exists();

            if ($exists) {
                return back()->withErrors([
                    'slug' => 'Ya existe una sección con este nombre o slug. Por favor, elige otro nombre.',
                ])->withInput();
            }
        }

        try {
            $section->update($validated);
            return back()->with('success', '¡Sección actualizada!');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle any remaining unique constraint violations
            if ($e->getCode() === '23000') {
                return back()->withErrors([
                    'title' => 'Ya existe una sección con este nombre. Por favor, elige otro nombre.',
                ])->withInput();
            }
            throw $e;
        }
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

