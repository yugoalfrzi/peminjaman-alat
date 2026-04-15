@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h3>Daftar Alat Tersedia</h3>
        <a href="{{ route('peminjam.multi.create') }}" class="btn btn-success">
            Pinjam Alat
        </a>
    </div>

    <div class="mb-3">
        <form action="{{ url('/peminjam/dashboard') }}" method="GET" class="d-flex gap-2" style="max-width: 400px">
            <input type="text" name="search" class="form-control" placeholder="Cari alat" value="{{ request('search') }}">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(request('search'))
                <a href="{{ route('peminjam.dashboard') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            @endif
        </form>
    </div>

    {{-- Kelompokkan alat berdasarkan kategori --}}
    @php
        $groupedTools = $tools->groupBy(function($tool) {
            return $tool->category->nama_kategori ?? 'Tanpa Kategori';
        });
    @endphp

    @forelse($groupedTools as $kategori => $kategoriTools)
        <div class="mt-4 mb-2">
            <h4 class="border-bottom pb-2">
                {{ $kategori }}
            </h4>
        </div>
        <div class="row">
            @foreach($kategoriTools as $tool)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        @if($tool->gambar)
                            <img src="{{ asset('storage/' . $tool->gambar) }}" class="card-img-top" alt="{{ $tool->nama_alat }}" style="height: 200px; object-fit: cover;">
                        @else
                            <img src="{{ asset('images/default-tool.png') }}" class="card-img-top" alt="Default" style="height: 200px; object-fit: cover;">
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $tool->nama_alat }}</h5>
                            <p class="card-text">{{ Str::limit($tool->deskripsi, 100) }}</p>
                            <p class="fw-bold text-primary">Sisa Stok: {{ $tool->stok }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="alert alert-warning text-center mt-4">
            Tidak ada alat yang tersedia sesuai pencarian.
        </div>
    @endforelse
@endsection