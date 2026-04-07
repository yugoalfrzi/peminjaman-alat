@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <h3>Dashboard Administrator</h3>
        <p class="text-muted">Selamat datang, {{ auth()->user()->name }}!</p>
    </div>
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3 h-100">
                <div class="card-header">Total Pengguna</div>
                    <div class="card-body">
                    <h2 class="card-title">{{ $totalUser }}</h2>
                    <p class="card-text">User Terdaftar</p>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <a href="{{ route('users.index') }}" class="text-white text-decoration-none small">Lihat Detail</a>
                    <span class="small">&rarr;</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-success mb-3 h-100">
                <div class="card-header">Data Alat</div>
                    <div class="card-body">
                    <h2 class="card-title">{{ $totalAlat }} <span class="fs-6">(Stok: {{ $totalStok }})</span></h2>
                    <p class="card-text">Jenis Alat Tersedia</p>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <a href="{{ route('tools.index') }}" class="text-white text-decoration-none small">Lihat Detail</a>
                    <span class="small">&rarr;</span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3 h-100">
                <div class="card-header text-dark">Kategori</div>
                    <div class="card-body text-dark">
                        <h2 class="card-title">{{ $totalKategori }}</h2>
                        <p class="card-text">Kategori Alat</p>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <a href="{{ route('categories.index') }}" class="text-dark text-decoration-none small">Lihat Detail</a>
                        <span class="small text-dark">&rarr;</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card text-white bg-danger mb-3 h-100">
                    <div class="card-header">Sedang Dipinjam</div>
                    <div class="card-body">
                        <h2 class="card-title">{{ $sedangDipinjam }}</h2>
                        <p class="card-text">Transaksi Aktif</p>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.loans.index') }}" class="text-white text-decoration-none small">Pantau</a>
                        <span class="small">&rarr;</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-white bg-info mb-3 h-100">
                    <div class="card-header">Sudah Dikembalikan</div>
                        <div class="card-body">
                            <h2 class="card-title">{{ $sedangDikembalikan }}</h2>
                            <p class="card-text">Transaksi Selesai</p>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.returns.index') }}" class="text-white text-decoration-none small">Pantau</a>
                            <span class="small">&rarr;</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card mb-4 shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h2 class="h5 fw-bold ">
                             Aktivitas Sistem Terakhir
                        </h2>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 15%">Waktu</th>
                                        <th style="width: 20%">User</th>
                                        <th style="width: 20%">Aksi</th>
                                        <th style="width: 45%">Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentLogs as $log)
                                        <tr>
                                            <td class="small text-muted">{{ $log->created_at->diffForHumans() }}</td>
                                            <td>
                                                <span class="fw-bold">{{ $log->user->name }}</span><br>
                                                <span class="badge bg-primary bg-opacity-10 text-secondary" style="font-size: 0.7em">
                                                    {{ ucfirst($log->user->role) }}
                                                </span>
                                            </td>
                                            <td>{{ $log->action }}</td>
                                            <td class="text-muted small">{{ Str::limit($log->description, 60) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                <i class="fas fa-inbox me-2"></i> Belum ada aktivitas tercatat.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <a href="{{ url('/admin/logs') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua Log</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection