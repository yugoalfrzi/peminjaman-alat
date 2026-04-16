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

    public function store(Request $request) {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.tool_id' => 'required|exists:tools,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.tanggal_pinjam' => 'required|date|after_or_equal:today',
            'items.*.tanggal_kembali_rencana' => 'required|date',
        ]);

        $user = Auth::user();
        $successCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                $tool = Tool::findOrFail($item['tool_id']);
                if ($item['tanggal_kembali_rencana'] < $item['tanggal_pinjam']) {
                    $errors[] = "Tanggal rencana kembali untuk alat '{$tool->nama_alat}' tidak boleh sebelum tanggal pinjam.";
                    continue;
                }

                if ($tool->stok < $item['jumlah']) {
                    $errors[] = "Stok alat '{$tool->nama_alat}' tidak mencukupi (tersedia {$tool->stok}, diminta {$item['jumlah']}).";
                    continue;
                }

                Loan::create([
                    'user_id' => $user->id,
                    'tool_id' => $tool->id,
                    'jumlah' => $item['jumlah'],
                    'tanggal_pinjam' => $item['tanggal_pinjam'],
                    'tanggal_kembali_rencana' => $item['tanggal_kembali_rencana'],
                    'status' => 'pending'
                ]);

                $tool->decrement('stok', $item['jumlah']);
                ActivityLog::record('Pengajuan Peminjaman', "Mengajukan peminjaman {$item['jumlah']} unit alat: {$tool->nama_alat}");
                $successCount++;
            }

            if (!empty($errors)) {
                DB::rollBack();
                return back()->withErrors($errors)->withInput();
            }

            DB::commit();
            return back()->with('success', "Pengajuan {$successCount} alat berhasil, menunggu persetujuan.");
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

        $pendingLoans = $loans->where('status', 'pending')->values();
        $approvedLoans = $loans->where('status', 'disetujui')->values();
        $returnedLoans = $loans->where('status', 'kembali')->values();

        return view('peminjam.riwayat', compact('pendingLoans', 'approvedLoans', 'returnedLoans'));
    }

}
