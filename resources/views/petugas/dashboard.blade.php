@extends('layouts.app')

@section('content')
    <h3>Permintaan Peminjaman Masuk</h3>
    <div class="card mb-4">
        <div class="card-header bg-warning">Menunggu Persetujuan</div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Peminjam</th>
                        <th>Alat</th>
                        <th>Tgl Pinjam</th>
                        <th>Rencana Kembali</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $loan)
                        <tr>
                            <td>{{ $loan->user->name }}</td>
                            <td>{{ $loan->tool->nama_alat }}</td>
                            <td>{{ $loan->tanggal_pinjam }}</td>
                            <td>{{ $loan->tanggal_kembali_rencana }}</td>
                            <td>
                                <form action="{{ url('/petugas/approve/'.$loan->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-success btn-sm">Setujui</button>
                                </form>
                                <button class="btn btn-danger btn-sm">Tolak</button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center">Tidak ada permintaan baru</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <h3>Daftar Sedang Dipinjam (Belum Kembali)</h3>
    <div class="card">
        <div class="card-header bg-info text-white">Monitor Peminjaman</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Peminjam</th>
                            <th>Alat</th>
                            <th>Status</th>
                            <th>Rencana Kembali</th>
                            <th>Tanggal Kembali Aktual</th>
                            <th>Denda (Rp)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeLoans as $active)
                            <tr>
                                <td>{{ $active->user->name }}</td>
                                <td>{{ $active->tool->nama_alat }}</td>
                                <td><span class="badge bg-primary">{{ $active->status }}</span></td>
                                <td>{{ $active->tanggal_kembali_rencana->format('d-m-Y') }}</td>
                                <form action="{{ url('/petugas/return/'.$active->id) }}" method="POST">
                                    @csrf
                                    <td>
                                        <input type="date" name="tanggal_kembali_aktual" class="form-control form-control-sm" 
                                               value="{{ date('Y-m-d') }}" required>
                                    </td>
                                    <td>
                                        @php
                                            $denda = $active->calculateDenda();
                                        @endphp
                                        @if($denda > 0)
                                            <span class="text-danger fw-bold">Rp {{ number_format($denda, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted">Rp 0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="submit" class="btn btn-primary btn-sm">Proses Kembali</button>
                                    </td>
                                </form>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">Tidak ada data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <h3>Daftar Sudah Dikembalikan</h3>
    <div class="card">
        <div class="card-header bg-success text-white">Monitor Peminjaman</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Peminjam</th>
                            <th>Alat</th>
                            <th>Status</th>
                            <th>Tanggal Kembali Aktual</th>
                            <th>Denda (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sudahDikembalikan as $sudah)
                            <tr>
                                <td>{{ $sudah->user->name }}</td>
                                <td>{{ $sudah->tool->nama_alat }}</td>
                                <td><span class="badge bg-success">Sudah Kembali</span></td>
                                <td>{{ $sudah->tanggal_kembali_aktual ? $sudah->tanggal_kembali_aktual->format('d-m-Y') : '-' }}</td>
                                <td>
                                    @if($sudah->denda > 0)
                                        <span class="text-danger fw-bold">Rp {{ number_format($sudah->denda, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">Rp 0</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7  " class="text-center text-muted">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection