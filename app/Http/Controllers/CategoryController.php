<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\ModelsCategoryLog; //untuk mencatat log
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * membuat daftar kategori
     */
    public function index()
    {
        //mengambil data kategori dan hitung jumlah alat didalamnya 
        $categories = Category::withCount('tools')->latest()->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Form tambah kategori
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Menyimpan kategori baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique|categories,nama_kategori'
        ]);

        Category::create([
            'nama_kategori' => $request->nama_kategori
        ]);

        ActivityLog::record('Tambah Kategori', 'Menambah Kategori' . $request->nama_kategori);

        return redirect()->route('Categories.index')->with('success', 'Kategori berhasil ditambahkan');
    }

    /**
     * Form edit kategori
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * update kategori
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:categories,nama_kategori,' . $category->id
        ]);

        $oldName = $category->nama_kategori;
        $category->update([
            'nama_kategori' => $request->nama_kategori
        ]);

        ActivityLog::record('Update Kategori', "Mengupdate Kategori $oldName menjadi" . $request->nama_kategori);

        return redirect()->route('Categories.index')->with('success', 'Kategori berhasil diperbarui');
    }

    /**
     * Hapus kategori
     */
    public function destroy(Category $category)
    {
        //cek apakah kategori ini masih dipakai ditabel tools?
        //kita menggunakan method tools() dari relasi dimodel category
        if ($category->tools()->count() > 0) {
            return back()->withErrors(['error' => 'Kategori tidak bisa dihapus karena masih memiliki data alat. Hapus atau pindahkan alatnya terlebih dahulu']);
        }

        $nama = $category->nama_kategori;
        $category->delete();

        ActivityLog::record('Hapus Kategori', 'menghapus kategori: ' . $nama);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus');
    }
}
