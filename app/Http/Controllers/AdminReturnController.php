<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Tool;
use App\Models\ActivityLog;

class AdminReturnController extends Controller
{
    //menampilkan riwayat pengembalian (history)
    public function index(Request $request)
    {
        
        $search = $request->get('search');

        $return = Loan::with(['user', 'tool'])
            ->where('status', 'kembali') // hanya data yang sudah kembali
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    // Cari berdasarkan nama peminjam (relasi user)
                    $q->whereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })
                    // Atau berdasarkan nama alat (relasi tool)
                    ->orWhereHas('tool', function ($sub) use ($search) {
                        $sub->where('nama_alat', 'like', "%{$search}%");
                    })
                    // Atau berdasarkan tanggal pinjam
                    ->orWhere('tanggal_pinjam', 'like', "%{$search}%")
                    // Atau berdasarkan tanggal kembali aktual
                    ->orWhere('tanggal_kembali_aktual', 'like', "%{$search}%")
                    // Atau berdasarkan status (meskipun sudah 'kembali', tetap bisa)
                    ->orWhere('status', 'like', "%{$search}%");
                });
            })
            ->latest('tanggal_kembali_aktual')
            ->paginate(10);

        // Pertahankan keyword search saat pagination
        $return->appends(['search' => $search]);

        return view('admin.returns.index', compact('return'));
    }

    /**
     * form create, menampilkan daftar alat yang sedang dipinjam
     * admin memilih dari sini untuk dikembalikan.
     */
    public function create()
    {
        //ambil data yang status ya disetujui (sedang diluar)
        $activeLoans = Loan::with(['user', 'tool'])
            ->where('status', 'disetujui')
            ->latest()
            ->get();

        return view('admin.returns.create', compact('activeLoans'));
    }

    /**
     * store: proses simpan pengembalian
     */
    public function store(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
        ]);
    
        $loan = Loan::findOrFail($request->loan_id);
    
        if ($loan->status != 'disetujui') {
            return back()->with('error', 'Data tidak valid atau sudah dikembalikan.');
        }
    
        // Set tanggal kembali aktual = sekarang
        $loan->tanggal_kembali_aktual = now();
        
        // Hitung denda
        $denda = $loan->calculateDenda();
    
        // Update data
        $loan->update([
            'status' => 'kembali',
            'tanggal_kembali_aktual' => now(),
            'denda' => $denda
        ]);
    
        // Kembalikan stok
        $tool = Tool::findOrFail($loan->tool_id);
        $tool->increment('stok');
    
        ActivityLog::record('Pengembalian (Admin)', 'Memproses pengembalian alat: ' . $loan->tool->nama_alat . ' dengan denda Rp ' . number_format($denda, 0, ',', '.'));
    
        return redirect()->route('admin.returns.index')->with('success', 'Alat berhasil dikembalikan. Denda: Rp ' . number_format($denda, 0, ',', '.'));
    }

    /**
     * edit data pengembalian
     */
    public function edit($id)
    {
        $loan = Loan::findOrFail($id);

        //pastikan hanya bisa edit yang statusnya sudah kembali
        if ($loan->status != 'kembali'){
            return redirect()->route('admin.returns.index');
        }

        return view('admin.returns.edit', compact('loan'));
    }

    /**
     * update perubahan data pengembalian
     */
    public function update(Request $request, $id)
    {
        $loan = Loan::findOrFail($id);

        $request->validate([
            'tanggal_kembali_aktual' => 'required|date'
        ]);

        $loan->update([
            'tanggal_kembali_aktual' => $request->tanggal_kembali_aktual,
            'denda' => $loan->calculateDenda() //update denda jika tanggal berubah
        ]);

        return redirect()->route('admin.returns.index')->with('success', 'Data pengembalian berhasil diperbarui');
    }

    /**
     * hapus riwayat pengembalian
     */
    public function destroy($id)
    {
        $loan = Loan::findOrFail($id);

        // Jika data dihapus, apakah stok mau dikurangi lagi?
        // Biasanya hapus riwayat tidak mempengaruhi stok fisik saat ini, tapi tergantung kebijakan.
        // Di sini kita asumsikan hanya hapus arsip.

        $loan->delete();

        return redirect()->route('admin.returns.index')->with('success', 'riwayat dihapus');
    }
}
