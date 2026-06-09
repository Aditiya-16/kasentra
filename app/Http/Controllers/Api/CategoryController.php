<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /** Daftar kategori (untuk filter produk di kasir). */
    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection(
            Category::withCount('products')->orderBy('name')->get(),
        );
    }

    /** Tambah kategori (admin). */
    public function store(Request $request): CategoryResource
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        return CategoryResource::make(Category::create($data));
    }

    /** Ubah nama kategori (admin). */
    public function update(Request $request, Category $category): CategoryResource
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
        ]);

        $category->update($data);

        return CategoryResource::make($category);
    }

    /** Hapus kategori (admin). Ditolak bila masih ada produk di dalamnya. */
    public function destroy(Category $category): JsonResponse
    {
        if ($category->products()->exists()) {
            return response()->json([
                'message' => 'Kategori masih berisi produk. Pindahkan atau hapus '
                    .'produknya dulu sebelum menghapus kategori.',
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Kategori dihapus.']);
    }
}
