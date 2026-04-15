<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\User;
use App\Models\Tool;
Use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoanController extends Controller
{
    //tampilkan semua data
    public function index()
    {
        $query = Loan::with(['user', 'tool']);

        if ($search = request('search')) {
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%$search%");
                })->orWhereHas('tool', function($q) use ($search) {
                    $q->where('nama_alat', 'like', "%$search%");
                });
            });
        }

        $loans = $query->latest()->paginate(10);
        return view('admin.loans.index', compact('loans'));
    }

    // Form tambah (create)
    public function create()
    {
        //ambil user yang rolenya peminjam saja
        $users = User::where('role', 'peminjam')->get();
        //ambil semua tools
        $tools = Tool::all();

        return view('admin.loans.create', compact('users', 'tools'));
    }

    //simpan data baru
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'tool_id' => 'required',
            'jumlah' => 'required|integer|min:1',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali_rencana' => 'required|date|after_or_equal:tanggal_pinjam',
            'status' => 'required'
        ]);
        
        // cek stok jika status langsung disetujui
        $tool = Tool::findOrFail($request->tool_id);
        if($request->status == 'disetujui' && $tool->stok < $request->jumlah) {
            return back()->with(['error' => "Stok alat tidak cukup. Tersedia {$tool->stok}, diminta {$request->jumlah}."]);
        }

        Loan::create([
            'user_id' => $request->user_id,
            'tool_id' => $request->tool_id,
            'jumlah' => $request->jumlah,
            'tanggal_pinjam' => $request->tanggal_pinjam,
            'tanggal_kembali_rencana' => $request->tanggal_kembali_rencana,
            'status' => $request->status,
            'petugas_id' => Auth::id() //admin yang input dianggap petugas
        ]);

        // kurangi stok jika admin langsung set disetujui
        if ($request->status == 'disetujui') {
            $tool->decrement('stok', $request->jumlah);
        }

        ActivityLog::record('Create Loan', 'Admin membuat data pinjaman baru');

        return redirect()->route('admin.loans.index')->with('success', 'Data pinjaman berhasil ditambahkan.');
    }

    // form edit
    public function edit($id)
    {
        $loan = Loan::findOrFail($id);
        $users = User::where('role', 'peminjam')->get();
        $tools = Tool::all();

        return view('admin.loans.edit', compact('loan', 'users', 'tools'));
    }

    // update data (simpan perubahan)
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required',
            'tool_id' => 'required',
            'jumlah' => 'required|integer|min:1',
            'tanggal_pinjam' => 'required|date',
            'tanggal_kembali_rencana' => 'required|date|after_or_equal:tanggal_pinjam',
            'status' => 'required'
        ]);

        $loan = Loan::findOrFail($id);
        $tool = Tool::findOrFail($request->tool_id);
        $jumlahBaru = (int) $request->jumlah;
        $jumlahLama = (int) ($loan->jumlah ?? 1);

        // Logika perubahan stok berdasarkan perubahan status
        // 1. jika sebelumnya pending -> diubah menjadi disetujui (stok berkurang)
        if ($loan->status == 'pending' && $request->status == 'disetujui') {
            if ($tool->stok < $jumlahBaru) {
                return back()->with(['error' => "Stok alat tidak cukup. Tersedia {$tool->stok}, diminta {$jumlahBaru}."]);
            }
            $tool->decrement('stok', $jumlahBaru);
        }

        //2. jika sebelumnya disetujui -> diubah menjadi kembali (stok bertambah)
        elseif ($loan->status == 'disetujui' && $request->status == 'kembali') {
            $tool->increment('stok', $jumlahLama);
            $request->merge(['tanggal_kembali_aktual' => now()]);
        }

        //3. jika sebelumnya disetujui -> diubah jadi pending/batal (stok bertambah/koreksi)
        elseif ($loan->status == 'disetujui' && $request->status == 'pending') {
            $tool->increment('stok', $jumlahLama);
        }

        $loan->update([
            'user_id' => $request->user_id,
            'tool_id' => $request->tool_id,
            'jumlah' => $jumlahBaru,
            'tanggal_pinjam' => $request->tanggal_pinjam,
            'tanggal_kembali_rencana' => $request->tanggal_kembali_rencana,
            'status' => $request->status,
            'tanggal_kembali_aktual' => $request->tanggal_kembali_aktual ?? $loan->tanggal_kembali_aktual
        ]);

        return redirect()->route('admin.loans.index')->with('success', 'Data berhasil diperbarui.');
    }

    //hapus data
    public function destroy($id)
    {
        $loan = Loan::findOrFail($id);

        //jika menghapus data yang statusnya masih disetujui (sedang dipinjam), kembalikan stok 
        if ($loan->status == 'disetujui'){
            $loan->tool->increment('stok', $loan->jumlah ?? 1);
        }

        $loan->delete();
        return redirect()->route('admin.loans.index')->with('success', 'Data berhasil dihapus.');
    }
}