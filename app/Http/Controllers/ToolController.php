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
        // Mengambil data alat, diurutkan terbaru, dengan pagination 10 per halaman
        // with digunakan untuk eager loading relasi kategori agar query lebih ringan
        $tools = Tool::with('category')->latest()->paginate(10);

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
        // validasi input
        $request->validate([
            'nama_alat' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'stok' => 'required|integer|min:0',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',//max 2mb
            'deskripsi' => 'nullable|string',
        ]);

        //handle upload gambar (jika ada)
        $gambarPath = null;
        if ($request->hasFile('gambar')){
            //simpan difolder: storage/app/public/tools
            $gambarPath = $request->file('gambar')->store('tools','public');
        }

        // simpan ke database   
        Tool::create([
            'nama_alat' => $request->nama_alat,
            'category_id' => $request->category_id,
            'stok' => $request->stok,
            'gambar' => $gambarPath,
            'deskripsi' => $request->deskripsi,
        ]);

        // catat log
        ActivityLog::record('Tambah Alat', 'Menambahkan alat baru' . $request->nama_alat);

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

        //handle ganti gambar
        if ($request->hasFile('gambar')) {
            //hapus gambar lama jika ada
            if ($tool->gambar && Storage::disk('public')->exists($tool->gambar)) {
                Storage::disk('public')->delete($tool->gambar);
            }
            //simpan gambar baru
            $data['gambar'] = $request->file('gambar')->store('tools', 'public');
        }

        $tool->update($request->all());

        ActivityLog::record('Update alat', 'Memperbarui data alat' . $tool->nama_alat);

        return redirect()->route('tools.index')->with('succes', 'Data alat diperbarui');
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