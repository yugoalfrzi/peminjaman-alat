<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Tool;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

        return back()->with('success', 'Peminjaman disetujui.');
    }

    public function processReturn(Request $request, $id) 
    {
        $request->validate([
            'tanggal_kembali_aktual' => 'required|date',
            'proof_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048', // validasi foto
        ]);
    
        $loan = Loan::findOrFail($id);
    
        if ($loan->status !== 'disetujui') {
            return back()->with('error', 'Peminjaman tidak valid atau sudah dikembalikan.');
        }
    
        // handle file foto
        $proof_photo = null;
        if ($request->hasFile('proof_photo')) {
            $proof_photo = $request->file('proof_photo')->store('returns', 'public');
            $loan->proof_photo = $proof_photo; // Tambah
            $loan->save();
        }

    
        $tanggalAktual = Carbon::parse($request->tanggal_kembali_aktual);
        $loan->tanggal_kembali_aktual = $tanggalAktual;
        $denda = $loan->calculateDenda();
        $loan->status = 'kembali';
        $loan->denda = $denda;
        $loan->save();
    
        // Kembalikan stok
        $tool = Tool::find($loan->tool_id);
        if ($tool) {
            $tool->increment('stok');
        }
    
        // Catat aktivitas (opsional)
        if (class_exists(ActivityLog::class)) {
            ActivityLog::record('Pengembalian (Petugas)', 'Memproses pengembalian alat: ' . ($loan->tool->nama_alat ?? '-') . ' dengan denda Rp ' . number_format($denda, 0, ',', '.') . '. Foto bukti: ' . $proof_photo);
        }
    
        return back()->with('success', 'Alat telah dikembalikan. Denda: Rp ' . number_format($denda, 0, ',', '.'));
    }

    public function report(Request $request) 
    {
        // Bisa tambahkan filter tanggal jika mau
        $loans = Loan::with(['user', 'tool'])->get();
        return view('petugas.laporan', compact('loans'));
    }

    public function reject($id)
{
    $loan = Loan::findOrFail($id);

    // memastikan hanya status pending yang bisa ditolak
    if ($loan->status !== 'pending') {
        return back()->with('error', 'Peminjaman tidak dapat ditolak karena sudah diproses.');
    }

    // Kembalikan stok alat (karena sebelumnya sudah dikurangi saat pengajuan)
    $tool = Tool::find($loan->tool_id);
    if ($tool) {
        $tool->increment('stok', $loan->jumlah ?? 1); // asumsikan ada kolom jumlah
    }

    // Ubah status menjadi ditolak
    $loan->update([
        'status' => 'ditolak',
        'petugas_id' => Auth::id()
    ]);

    ActivityLog::record('Penolakan Peminjaman', "Peminjaman alat {$loan->tool->nama_alat} oleh {$loan->user->name} ditolak.");

    return back()->with('success', 'Peminjaman ditolak.');
}
}
