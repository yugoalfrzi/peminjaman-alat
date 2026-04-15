<?php

namespace App\Http\Controllers;
use App\Models\tool;
use App\Models\Category;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ToolController extends Controller
{
    /**
     * menampilkan daftar alat
     */
    public function index()
    {
        //fitur pencarian sederhana
        $query = Tool::query();

        if ($search = request('search')) {
            $query->where('nama_alat', 'like', "%$search%");
        }

        // Mengambil data alat, diurutkan terbaru, dengan pagination 10 per halaman
        // with digunakan untuk eager loading relasi kategori agar query lebih ringan
        $tools = $query->with('category')->latest()->paginate(10);

        return view('admin.tools.index', compact('tools'));
    }

    /**
     * menampilkan form tambah alat (create)
     */
    public function create()
    {
        $categories = Category::all(); //membutuhkan data kategori untuk dropdown
        return view('admin.tools.create', compact('categories'));
    }

    /**
     * menyimpan data alat baru ke database (store)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'stok' => 'required|integer|min:0',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'deskripsi' => 'nullable|string',
        ]);

        //siapkan data selain gambar
        $data = $request->except('gambar');
        // Handle upload gambar
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('tools', 'public');
            $data['gambar'] = $path;
        }

        // Simpan ke database
        Tool::create($data);

        ActivityLog::record('Tambah Alat', 'Menambahkan alat baru: ' . $request->nama_alat);

        return redirect()->route('tools.index')->with('success', 'Alat berhasil ditambahkan.');
    }
    
    /**
     * menampilkan form edit alat
     */
    public function edit(Tool $tool)
    {
        $categories = Category::all();
        return view('admin.tools.edit', compact('tool', 'categories'));
    }

    /**
     * memperbarui data alat (update)
     */
    public function update(Request $request, Tool $tool)
    {
        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'stok' => 'required|integer|min:0',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'deskripsi' => 'nullable|string',
        ]);

        // Ambil semua data kecuali gambar (file)
        $data = $request->except('gambar');

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama
            if ($tool->gambar && Storage::disk('public')->exists($tool->gambar)) {
                Storage::disk('public')->delete($tool->gambar);
            }
            // Upload baru, simpan di folder 'tools' (sama seperti store)
            $gambarPath = $request->file('gambar')->store('tools', 'public');
            $data['gambar'] = $gambarPath;
        }

        // Update data
        $tool->update($data);

        ActivityLog::record('Update Alat', 'Memperbarui data alat: ' . $tool->nama_alat);

        return redirect()->route('tools.index')->with('success', 'Data alat berhasil diperbarui');
    }
    
     /**
     * menghapus alat (delete)
     */
    public function destroy(Tool $tool)
    {
        // Cek apakah alat ini masih memiliki data peminjaman (apapun statusnya)
        if ($tool->loans()->count() > 0) {
            return back()->withErrors(['error' => 'Alat tidak bisa dihapus karena masih memiliki riwayat peminjaman. Hapus data peminjaman terlebih dahulu.']);
        }
        
        //hapus gambar jika ada
        if ($tool->gambar && Storage::disk('public')->exists($tool->gambar)) {
            Storage::disk('public')->delete($tool->gambar);
        }

        $namaAlat = $tool->nama_alat; //simpan nama alat sebelum dihapus untuk log
        $tool->delete();

        ActivityLog::record('Hapus alat', 'Menghapus alat' . $namaAlat);

        return redirect()->route('tools.index')->with('success', 'Data alat berhasil dihapus');
    }
}