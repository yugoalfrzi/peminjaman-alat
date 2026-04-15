@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header fw-bold bg-white py-3">Edit Data Pengembalian</div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.returns.update', $loan->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label>Peminjam</label>
                            <input type="text" class="form-control" value="{{ $loan->user->name }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label>Alat</label>
                            <input type="text" class="form-control" value="{{ $loan->tool->nama_alat }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label>Tanggal Kembali Aktual</label>
                            <input type="date" name="tanggal_kembali_aktual" class="form-control" value="{{ $loan->tanggal_kembali_aktual }}" required>
                            <small class="text-muted">Ubah tanggal ini jika admin salah input waktu pengembalian.</small>
                        </div>
                        <div class="d-flex justify-content-between gap-2">
                            <a href="{{ route('admin.returns.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection