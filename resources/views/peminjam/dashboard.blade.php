@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h3>Daftar Alat Tersedia</h3>
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
                    <div class="card h-100 tool-card"
                        role="button"
                        tabindex="0"
                        data-tool-id="{{ $tool->id }}"
                        data-tool-nama="{{ $tool->nama_alat }}"
                        data-tool-stok="{{ $tool->stok }}">
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

    <div class="card shadow-sm mt-4">
        <div class="card-header bg-primary text-white">
            Form Peminjaman Multi Alat
        </div>
        <div class="card-body">
            <form action="{{ url('/peminjam/ajukan') }}" method="POST" id="multiPinjamForm">
                @csrf
                <div class="alert alert-info py-2 mb-3">
                    Klik kartu alat di katalog untuk menambahkan ke daftar peminjaman.
                </div>

                <div id="itemsContainer" class="d-flex flex-column gap-3"></div>
                <p id="emptyItemsMessage" class="text-muted mb-3">
                    Belum ada alat dipilih.
                </p>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">Ajukan Peminjaman</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const today = "{{ date('Y-m-d') }}";
    const cards = document.querySelectorAll('.tool-card');
    const container = document.getElementById('itemsContainer');
    const emptyMessage = document.getElementById('emptyItemsMessage');
    const form = document.getElementById('multiPinjamForm');
    const resetBtn = document.getElementById('resetItemsBtn');

    let itemCount = 0;
    const selectedToolIds = new Set();

    function toggleEmptyMessage() {
        emptyMessage.style.display = container.children.length ? 'none' : 'block';
    }

    function createItemCard(toolId, toolNama, toolStok) {
        const index = itemCount++;
        const wrapper = document.createElement('div');
        wrapper.className = 'card border p-3';
        wrapper.dataset.toolId = toolId;
        wrapper.innerHTML = `
            <input type="hidden" name="items[${index}][tool_id]" value="${toolId}">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nama Alat</label>
                    <input type="text" class="form-control" value="${toolNama}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Jumlah</label>
                    <input type="number" name="items[${index}][jumlah]" class="form-control jumlah-input" min="1" max="${toolStok}" value="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Tanggal Pinjam</label>
                    <input type="date" name="items[${index}][tanggal_pinjam]" class="form-control date-pinjam" min="${today}" value="${today}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Tanggal Rencana Kembali</label>
                    <div class="d-flex gap-2">
                        <input type="date" name="items[${index}][tanggal_kembali_rencana]" class="form-control date-kembali" min="${today}" required>
                        <button type="button" class="btn btn-outline-danger remove-item-btn">Hapus</button>
                    </div>
                </div>
            </div>
        `;

        const jumlahInput = wrapper.querySelector('.jumlah-input');
        const pinjamInput = wrapper.querySelector('.date-pinjam');
        const kembaliInput = wrapper.querySelector('.date-kembali');
        const removeBtn = wrapper.querySelector('.remove-item-btn');

        jumlahInput.addEventListener('input', function () {
            const jumlah = parseInt(this.value || '0', 10);
            if (jumlah > toolStok) {
                this.setCustomValidity(`Jumlah tidak boleh lebih dari stok (${toolStok})`);
            } else if (jumlah < 1) {
                this.setCustomValidity('Jumlah minimal 1');
            } else {
                this.setCustomValidity('');
            }
        });

        pinjamInput.addEventListener('change', function () {
            kembaliInput.min = this.value || today;
            if (kembaliInput.value && kembaliInput.value < this.value) {
                kembaliInput.value = this.value;
            }
        });

        removeBtn.addEventListener('click', function () {
            selectedToolIds.delete(toolId);
            cards.forEach((card) => {
                if (card.getAttribute('data-tool-id') === String(toolId)) {
                    card.classList.remove('border-primary', 'shadow');
                }
            });
            wrapper.remove();
            toggleEmptyMessage();
        });

        return wrapper;
    }

    cards.forEach((card) => {
        card.addEventListener('click', function () {
            const toolId = this.getAttribute('data-tool-id');
            const toolNama = this.getAttribute('data-tool-nama');
            const toolStok = parseInt(this.getAttribute('data-tool-stok') || '0', 10);

            if (selectedToolIds.has(toolId)) {
                return;
            }

            if (toolStok <= 0) {
                alert('Stok alat habis, tidak dapat dipilih.');
                return;
            }

            selectedToolIds.add(toolId);
            this.classList.add('border-primary', 'shadow');
            container.appendChild(createItemCard(toolId, toolNama, toolStok));
            toggleEmptyMessage();
        });
        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    form.addEventListener('submit', function (e) {
        if (!container.children.length) {
            e.preventDefault();
            alert('Pilih minimal satu alat dari katalog.');
        }
    });

    toggleEmptyMessage();
});
</script>
@endpush