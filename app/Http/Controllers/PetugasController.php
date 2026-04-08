<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Tool;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PetugasController extends Controller
{
    public function index()
    {
        //data yang statusnya pending
        $loans = Loan::where('status', 'pending')->with(['user', 'tool'])->get();

        //data yang statusnya disetujui (sedang dipinjam)
        $activeLoans = Loan::where('status', 'disetujui')->with(['user', 'tool'])->get();

        //data yang statusnya kembali
        $sudahDikembalikan = Loan::where('status', 'kembali')->with(['user', 'tool'])->latest('tanggal_kembali_aktual')->get();

        return view('petugas.dashboard', compact('loans', 'activeLoans', 'sudahDikembalikan'));
    }

    public function approve($id) 
    {
        $loan = Loan::findOrFail($id);
        $loan->update([
            'status' => 'disetujui',
            'petugas_id' => Auth::id()
        ]);

        // Kurangi stok alat
        $tool = Tool::find($loan->tool_id);
        if ($tool) {
            $tool->decrement('stok');
        }

        return back()->with('success', 'Peminjaman disetujui.');
    }

    public function processReturn(Request $request, $id) 
    {
        $request->validate([
            'tanggal_kembali_aktual' => 'required|date',
        ]);

        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'disetujui') {
            return back()->with('error', 'Peminjaman tidak valid atau sudah dikembalikan.');
        }

        // 1) Simpan tanggal kembali aktual dan ubah status terlebih dahulu
        $tanggalAktual = Carbon::parse($request->tanggal_kembali_aktual);
        $loan->tanggal_kembali_aktual = $tanggalAktual;
        $loan->status = 'kembali';
        $loan->save();

        // 2) Hitung denda berdasarkan data yang sudah tersimpan di model
        $denda = $loan->calculateDenda();

        // 3) Simpan nilai denda
        $loan->denda = $denda;
        $loan->save();

        // 4) Kembalikan stok
        $tool = Tool::find($loan->tool_id);
        if ($tool) {
            $tool->increment('stok');
        }

        // 5) Catat aktivitas
        if (class_exists(ActivityLog::class)) {
            ActivityLog::record('Pengembalian (Petugas)', 'memproses pengembalian alat: ' . ($loan->tool->nama_alat ?? '-') . ' dengan denda RP ' . number_format($denda, 0, ',', '.'));
        }

        return back()->with('success', 'Alat telah dikembalikan. Denda: Rp ' . number_format($denda, 0, ',', '.'));
    }

    public function report(Request $request) 
    {
        // Bisa tambahkan filter tanggal jika mau
        $loans = Loan::with(['user', 'tool'])->get();
        return view('petugas.laporan', compact('loans'));
    }
}
