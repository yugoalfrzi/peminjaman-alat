@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h3 class="mb-0">Kelola Data Peminjaman (Admin)</h3>
        <a href="{{ route('admin.loans.create') }}" class="btn btn-primary">+ Tambah Peminjaman Manual</a>
    </div>
    <div class="card shadow-sm border-0 rounded-0">
        <div class="card-body p-4">
            <div class="mb-3">
                <form action="{{ route('admin.loans.index') }}" method="GET" class="d-flex gap-2" style="max-width: 400px;">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama peminjam atau alat..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-outline-primary">Cari</button>
                    @if(request('search'))
                        <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-danger btn-sm">Reset</a>
                    @endif
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table">
                        <tr>
                            <th>No</th>
                            <th>Peminjam</th>
                            <th>Alat</th>
                            <th>Jumlah</th>
                            <th>Tanggal Pinjam</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $key => $loan)
                            <tr>
                                <td>{{ $loans->firstItem() + $key }}</td>
                                <td>{{ $loan->user->name }}</td>
                                <td>{{ $loan->tool->nama_alat }}</td>
                                <td>{{ $loan->jumlah ?? 1 }}</td>
                                <td>
                                    {{ $loan->tanggal_pinjam }} <br>
                                    <small class="text-muted">Kembali: {{ $loan->tanggal_kembali_rencana }}</small>
                                </td>
                                <td>
                                    @if($loan->status == 'pending') <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($loan->status == 'disetujui') <span class="badge bg-primary">Sedang Dipinjam</span>
                                        @elseif($loan->status == 'kembali') <span class="badge bg-success">Sudah Kembali</span>
                                        @elseif($loan->status == 'ditolak') <span class="badge bg-danger">Ditolak</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.loans.edit', $loan->id) }}" class="btn btn-sm btn-warning text-white">Edit</a>
                                    <form action="{{ route('admin.loans.destroy', $loan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus data ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $loans->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
@endsection