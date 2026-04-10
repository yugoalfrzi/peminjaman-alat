@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Data Pengembalian Alat</h3>
        <a href="{{ route('admin.returns.create') }}" class="btn btn-success">+ Proses Pengembalian Baru</a>
    </div>
    <div class="mb-3">
        <form action="{{ route('admin.returns.index') }}" method="GET" class="d-flex gap-2" style="max-width: 400px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nama peminjam atau alat..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-outline-primary">Cari</button>
            @if(request('search'))
                <a href="{{ route('admin.returns.index') }}" class="btn btn-outline-danger btn-sm">Reset</a>
                @endif
        </form>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-hover table-striped">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Peminjam</th>
                        <th>Alat</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali (Aktual)</th>
                        <th>Denda (Rp)</th>
                        <th>Petugas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($return as $key => $r)
                    <tr>
                        <td>{{ $return->firstItem() + $key }}</td>
                        <td>{{ $r->user->name }}</td>
                        <td>{{ $r->tool->nama_alat }}</td>
                        <td>{{ $r->tanggal_pinjam }}</td>
                        <td>
                            {{ $r->tanggal_kembali_aktual }}
                            @if($r->tanggal_kembali_aktual > $r->tanggal_kembali_rencana)
                                <span class="badge bg-danger">Telat</span>
                            @else
                                <span class="badge bg-success">Tepat Waktu</span>
                            @endif
                        </td>
                        <td>{{ $r->denda > 0 ? 'Rp ' . number_format($r->denda, 0, ',', '.') : 'Rp 0' }}</td>
                        <td>{{ $r->petugas ? $r->petugas->name : 'Admin' }}</td>
                        <td>
                            <a href="{{ route('admin.returns.edit', $r->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('admin.returns.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus riwayat ini?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center">Belum ada data pengembalian.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">{{ $return->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
@endsection