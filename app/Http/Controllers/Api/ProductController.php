<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Daftar produk untuk halaman kasir. Mendukung pencarian (nama/SKU) dan
     * filter kategori. Admin bisa menyertakan ?all=1 untuk ikut menampilkan
     * produk nonaktif (dibutuhkan layar pengelolaan).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $showAll = $request->boolean('all') && $request->user()?->isAdmin();

        $products = Product::query()
            ->when(! $showAll, fn ($q) => $q->where('is_active', true))
            ->with('category')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim($request->string('search'));
                $q->where(fn ($sub) => $sub
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%"));
            })
            ->when($request->filled('category_id'), fn ($q) => $q
                ->where('category_id', $request->integer('category_id')))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 50));

        return ProductResource::collection($products);
    }

    /** Tambah produk baru (admin). Foto opsional via multipart. */
    public function store(Request $request): ProductResource
    {
        $data = $this->validateData($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return ProductResource::make($product->load('category'));
    }

    /** Ubah produk (admin). Dikirim lewat POST agar multipart konsisten. */
    public function update(Request $request, Product $product): ProductResource
    {
        $data = $this->validateData($request, $product->id);

        if ($request->hasFile('image')) {
            // Hapus foto lama agar storage tidak menumpuk.
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        } else {
            // Jangan menimpa foto bila tidak ada file baru.
            unset($data['image']);
        }

        $product->update($data);

        return ProductResource::make($product->fresh()->load('category'));
    }

    /**
     * Hapus produk (admin). Bila produk sudah pernah dipakai di transaksi,
     * penghapusan ditolak (histori harus tetap valid) — sarankan nonaktifkan.
     */
    public function destroy(Product $product): JsonResponse
    {
        if ($product->transactionItems()->exists()) {
            return response()->json([
                'message' => 'Produk sudah pernah terjual, jadi tidak bisa dihapus. '
                    .'Nonaktifkan saja agar tidak muncul di kasir.',
            ], 422);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return response()->json(['message' => 'Produk dihapus.']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'nullable', 'string', 'max:255',
                Rule::unique('products', 'sku')->ignore($ignoreId),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ]);
    }
}
