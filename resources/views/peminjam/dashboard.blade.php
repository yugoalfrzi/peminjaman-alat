@extends('layouts.app')

@section('content')
    <h3>Daftar Alat Tersedia</h3>
    <div class="mb-3">
        <form action="{{ url('/peminjam/dashboard') }}" method="GET" class="d-flex gap-2" style="max-width: 400px">
            <input type="text" name="search" class="form-control" placeholder="Cari alat" value="{{ request('search') }}">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(request('search'))
                <a href="{{ route('peminjam.dashboard') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            @endif
        </form>
    </div>
    <div class="row mt-4">
        @foreach($tools as $tool)
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $tool->nama_alat }}</h5>
                        <img src="{{ $tool->gambar ? asset('storage/'.$tool->gambar) : asset('images/default-tool.png') }}" class="card-img-top mb-3" alt="{{ $tool->nama_alat }}" style="height: 200px; object-fit: cover;">
                        <span class="badge bg-secondary mb-2">{{ $tool->category->nama_kategori }}</span>
                        <p class="card-text">{{ $tool->deskripsi }}</p>
                        <p class="fw-bold">Sisa Stok: {{ $tool->stok }}</p>
                        @if($tool->stok > 0)
                            <form action="{{ url('/peminjam/ajukan') }}" method="POST">
                                @csrf
                                <input type="hidden" name="tool_id" value="{{ $tool->id }}">
                                <div class="mb-2">
                                    <label class="small">Tanggal Rencana Kembali</label>
                                    <input type="date" name="tanggal_kembali_rencana" class="form-control form-control-sm" required min="{{ date('Y-m-d') }}">
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Pinjam Alat</button>
                            </form>
                        @else
                            <button class="btn btn-secondary w-100" disabled>Stok Habis</button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection