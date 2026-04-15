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
            'proof_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'kerusakan' => 'required|in:tidak_rusak,ringan,sedang,berat', // validasi kerusakan
        ]);

        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'disetujui') {
            return back()->with('error', 'Peminjaman tidak valid atau sudah dikembalikan.');
        }

        // Handle file foto
        $proof_photo = null;
        if ($request->hasFile('proof_photo')) {
            $proof_photo = $request->file('proof_photo')->store('returns', 'public');
            $loan->proof_photo = $proof_photo;
            $loan->save();
        }

        $tanggalAktual = Carbon::parse($request->tanggal_kembali_aktual);
        $loan->tanggal_kembali_aktual = $tanggalAktual;

        // Hitung denda keterlambatan
        $dendaTerlambat = $loan->calculateDenda(); // menghitung denda berdasarkan selisih tanggal

        // Hitung denda kerusakan berdasarkan pilihan
        $dendaKerusakan = 0;
        switch ($request->kerusakan) {
            case 'ringan':
                $dendaKerusakan = 5000;
                break;
            case 'sedang':
                $dendaKerusakan = 10000;
                break;
            case 'berat':
                $dendaKerusakan = 20000;
                break;
            case 'tidak_rusak':
            default:
                $dendaKerusakan = 0;
                break;
        }

        $totalDenda = $dendaTerlambat + $dendaKerusakan;

        $loan->status = 'kembali';
        $loan->denda = $totalDenda;
        $loan->save();

        // Kembalikan stok alat
        $tool = Tool::find($loan->tool_id);
        if ($tool) {
            $tool->increment('stok', $loan->jumlah ?? 1);
        }

        // Catat aktivitas
        if (class_exists(ActivityLog::class)) {
            ActivityLog::record('Pengembalian (Petugas)', 
                'Memproses pengembalian alat: ' . ($loan->tool->nama_alat ?? '-') . 
                ' | Denda terlambat: Rp ' . number_format($dendaTerlambat, 0, ',', '.') .
                ' | Denda kerusakan (' . $request->kerusakan . '): Rp ' . number_format($dendaKerusakan, 0, ',', '.') .
                ' | Total denda: Rp ' . number_format($totalDenda, 0, ',', '.') .
                ' | Foto bukti: ' . $proof_photo
            );
        }

        return back()->with('success', 
            'Alat telah dikembalikan. Denda terlambat: Rp ' . number_format($dendaTerlambat, 0, ',', '.') .
            ', Denda kerusakan: Rp ' . number_format($dendaKerusakan, 0, ',', '.') .
            ', Total: Rp ' . number_format($totalDenda, 0, ',', '.')
        );
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
