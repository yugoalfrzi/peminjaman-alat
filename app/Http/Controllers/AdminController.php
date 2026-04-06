<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tool;
use App\Models\Category;
use App\Models\Loan;
use App\Models\ActivityLog;

class AdminController extends Controller
{
    public function index()
    {
        // mengambil data statistik untuk kartu dashboard
        $totalUser = User::count();
        $totalAlat = Tool::count(); //jumlah jenis alat
        $totalStok = Tool::sum('stok'); //total fisik seluruh alat
        $totalKategori = Category::count();
        // menghitung peminjaman yang sedang berlangsung (status disetujui)
        $sedangDipinjam = Loan::where('status', 'disetujui')->count();
        $sedangDikembalikan = Loan::where('status', 'kembali')->count();
        // ambil 5 aktivitas terbaru
        $recentLogs = ActivityLog::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalUser',
            'totalAlat', 
            'totalStok', 
            'totalKategori', 
            'sedangDipinjam', 
            'sedangDikembalikan', 
            'recentLogs'
        ));
    }
}
