<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Tool;
use App\Models\ActivityLog;

class AdminReturnController extends Controller
{
    //menampilkan riwayat pengembalian (history)
    public function index()
    {
        /**
         * ambil dengan status 'kembali'
         */
        $return = Loan::with(['user', 'tool'])
            ->where('status', 'kembali')
            ->latest('tanggal_kembali_aktual')
            ->paginate(10);

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
        $request->validat ([
            'loan_id' => 'required|exist:loans,id',
            'denda' => 'nullable|integer' 
        ]);

        $loan = Loan::findOrFail($request->loan_id);

        if ($loan->status != 'disetujui'){
            return back()->with('error', 'data tidak valid atau sudah dikembalikan');
        }

        // 1. update status dan tanggal
        $loan->update([
            'status' => 'kembali',
            'tanggal_kembali_aktual' => now(),
            //'denda' => $request->denda //jika tabel loans punya kolom denda
        ]);

        //2. kembalikan stok alat
        $tool = Tool::findOrFail($loan->tool_id);
        $tool->increment('stok');

        ActivityLog::record('Pengembalian (Admin)', 'memproses pengembalian alat: ' . $tool->nama_alat);

        return redirect()->route('admin.returns.index')->with('success', 'alat berhasil dikembalikan');
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
            'tanggal_kembali_aktual' => $request->tanggal_kembali_aktual
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
