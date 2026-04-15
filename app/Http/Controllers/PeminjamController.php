<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Loan;
use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class PeminjamController extends Controller
{
    public function index(Request $request) {

        $search = $request->get('search');
    
        $query = Tool::with('category'); // eager loading kategori
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_alat', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($q2) use ($search) {
                      $q2->where('nama_kategori', 'like', "%{$search}%");
                  });
            });
        }
        
        $tools = $query->get(); // atau paginate()
        
        return view('peminjam.dashboard', compact('tools'));
    }

    /**
     * form create untuk peminjaman multi alat
     */
    public function createMulti() 
    {
        $tools = Tool::where('stok', '>', 0)->get(); 
        
        return view('peminjam.multi_pinjam', compact('tools'));
    }

    public function store(Request $request) {
        // Cek stok dulu
        $tool = Tool::find($request->tool_id);
        if($tool->stok > 0) {
            Loan::create([
                'user_id' => Auth::id(),
                'tool_id' => $request->tool_id,
                'tanggal_pinjam' => now(),
                'tanggal_kembali_rencana' => $request->tanggal_kembali_rencana,
                'status' => 'pending'
            ]);

            ActivityLog::record('Tambah Alat', 'Menambahkan alat baru: ' . $request->nama_alat);

            // Opsional: Kurangi stok langsung atau saat disetujui (tergantung logika bisnis)
            return back()->with('success', 'Pengajuan berhasil, menunggu persetujuan.');
        }
    }

    /**
     * form untuk proses peminjaman multi alat
     */
    public function storeMulti(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.tool_id' => 'required|exists:tools,id',
            'items.*.jumlah' => 'required|integer|min:0',          
            'items.*.tanggal_pinjam' => 'required|date|after_or_equal:today',
            'items.*.tanggal_kembali_rencana' => 'required|date|after_or_equal:items.*.tanggal_pinjam',
        ]);

        $user = Auth::user();
        $successCount = 0;
        $errors = [];

        // Gunakan DB::transaction agar konsisten
        DB::beginTransaction();

        try {
            foreach ($request->items as $item) {
                $tool = Tool::find($item['tool_id']);

                // Validasi stok berdasarkan jumlah yang diminta
                if ($tool->stok < $item['jumlah']) {
                    $errors[] = "Stok alat '{$tool->nama_alat}' tidak mencukupi (tersedia {$tool->stok}, diminta {$item['jumlah']}).";
                    continue;
                }

                // Simpan peminjaman dengan jumlah
                Loan::create([
                    'user_id' => $user->id,
                    'tool_id' => $tool->id,
                    'jumlah' => $item['jumlah'],                     // simpan jumlah
                    'tanggal_pinjam' => $item['tanggal_pinjam'],
                    'tanggal_kembali_rencana' => $item['tanggal_kembali_rencana'],
                    'status' => 'pending',
                ]);

                // Kurangi stok alat sesuai jumlah
                $tool->stok -= $item['jumlah'];
                $tool->save();

                // Activity log
                ActivityLog::record('Pengajuan Peminjaman', "Mengajukan peminjaman {$item['jumlah']} unit alat: {$tool->nama_alat}");

                $successCount++;
            }

            // Jika ada error, rollback semua perubahan (karena transaksi)
            if (!empty($errors)) {
                DB::rollBack();
                return back()->withErrors($errors)->withInput();
            }

            DB::commit();

            return redirect()->route('peminjam.dashboard')
                ->with('success', "Berhasil mengajukan {$successCount} peminjaman. Menunggu persetujuan.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    public function history() 
    {
        $loans = Loan::where('user_id', Auth::id())
            ->with('tool')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('peminjam.riwayat', compact('loans'));
    }

}
