@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Form Peminjaman Multi Alat</h3>
        <a href="{{ route('peminjam.dashboard') }}" class="btn btn-outline-secondary">&larr; Kembali ke Dashboard</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <strong>📋 Pinjam Lebih dari Satu Alat Sekaligus</strong>
            <small class="d-block text-white-50">Setiap baris dapat memilih alat, tanggal pinjam, dan rencana kembali</small>
        </div>
        <div class="card-body">
            <form action="{{ route('peminjam.multi.store') }}" method="POST" id="multiPinjamForm">
                @csrf

                <div id="itemsContainer">
                    <div class="pinjam-item card mb-3 p-3 border">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Pilih Alat <span class="text-danger">*</span></label>
                                <select name="items[0][tool_id]" class="form-select tool-select" required>
                                    <option value="">-- Pilih Alat --</option>
                                    @foreach($tools as $tool)
                                        <option value="{{ $tool->id }}" data-stok="{{ $tool->stok }}">
                                            {{ $tool->nama_alat }} (Stok: {{ $tool->stok }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted stok-info"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tanggal Pinjam <span class="text-danger">*</span></label>
                                <input type="date" name="items[0][tanggal_pinjam]" class="form-control date-pinjam" required value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tanggal Rencana Kembali <span class="text-danger">*</span></label>
                                <input type="date" name="items[0][tanggal_kembali_rencana]" class="form-control date-kembali" required min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-2 text-center">
                                <button type="button" class="btn btn-danger remove-item-btn" disabled style="opacity:0.5">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3 mt-2">
                    <button type="button" id="addItemBtn" class="btn btn-outline-primary">
                        + Tambah Alat Lain
                    </button>
                </div>

                <hr>
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success px-4">Ajukan Peminjaman</button>
                    <button type="reset" class="btn btn-danger">Reset Form</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let itemCount = 1; // karena index 0 sudah ada

        const container = document.getElementById('itemsContainer');
        const addBtn = document.getElementById('addItemBtn');

        // Fungsi untuk update status tombol hapus (disable jika hanya 1 item)
        function updateRemoveButtons() {
            const items = document.querySelectorAll('.pinjam-item');
            items.forEach((item, idx) => {
                const removeBtn = item.querySelector('.remove-item-btn');
                if (items.length === 1) {
                    removeBtn.disabled = true;
                    removeBtn.style.opacity = '0.5';
                    removeBtn.title = 'Minimal satu alat harus dipinjam';
                } else {
                    removeBtn.disabled = false;
                    removeBtn.style.opacity = '1';
                    removeBtn.title = 'Hapus baris ini';
                }
            });
        }

        // Fungsi untuk menampilkan info stok ketika memilih alat
        function attachStokListener(selectEl) {
            selectEl.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const stok = selectedOption.getAttribute('data-stok') || 0;
                const infoDiv = this.closest('.row')?.querySelector('.stok-info');
                if (infoDiv) {
                    if (this.value && stok <= 0) {
                        infoDiv.innerHTML = '<span class="text-danger">Stok habis, tidak dapat dipinjam</span>';
                        this.setCustomValidity('Stok habis');
                    } else if (this.value) {
                        infoDiv.innerHTML = `<span class="text-success">✓ Stok tersedia: ${stok}</span>`;
                        this.setCustomValidity('');
                    } else {
                        infoDiv.innerHTML = '';
                        this.setCustomValidity('');
                    }
                }
            });
            // Trigger awal
            selectEl.dispatchEvent(new Event('change'));
        }

        // Validasi tanggal: tanggal kembali harus >= tanggal pinjam
        function validateDateRange(pinjamInput, kembaliInput) {
            function check() {
                const tglPinjam = pinjamInput.value;
                const tglKembali = kembaliInput.value;
                if (tglPinjam && tglKembali && tglKembali < tglPinjam) {
                    kembaliInput.setCustomValidity('Tanggal kembali tidak boleh sebelum tanggal pinjam');
                } else {
                    kembaliInput.setCustomValidity('');
                }
            }
            pinjamInput.addEventListener('change', check);
            kembaliInput.addEventListener('change', check);
            check();
        }

        // Inisialisasi untuk item pertama (index 0)
        const firstSelect = document.querySelector('.pinjam-item .tool-select');
        const firstPinjam = document.querySelector('.pinjam-item .date-pinjam');
        const firstKembali = document.querySelector('.pinjam-item .date-kembali');
        if (firstSelect) attachStokListener(firstSelect);
        if (firstPinjam && firstKembali) validateDateRange(firstPinjam, firstKembali);

        // Tambah item baru
        addBtn.addEventListener('click', function () {
            const newIndex = itemCount;
            const newItem = document.createElement('div');
            newItem.className = 'pinjam-item card mb-3 p-3 border';
            newItem.innerHTML = `
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Pilih Alat <span class="text-danger">*</span></label>
                        <select name="items[${newIndex}][tool_id]" class="form-select tool-select" required>
                            <option value="">-- Pilih Alat --</option>
                            @foreach($tools as $tool)
                                <option value="{{ $tool->id }}" data-stok="{{ $tool->stok }}">
                                    {{ $tool->nama_alat }} (Stok: {{ $tool->stok }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted stok-info"></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tanggal Pinjam <span class="text-danger">*</span></label>
                        <input type="date" name="items[${newIndex}][tanggal_pinjam]" class="form-control date-pinjam" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tanggal Rencana Kembali <span class="text-danger">*</span></label>
                        <input type="date" name="items[${newIndex}][tanggal_kembali_rencana]" class="form-control date-kembali" required min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-2 text-center">
                        <button type="button" class="btn btn-danger remove-item-btn">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            `;

            container.appendChild(newItem);
            itemCount++;

            // Attach event listeners untuk elemen baru
            const newSelect = newItem.querySelector('.tool-select');
            const newPinjam = newItem.querySelector('.date-pinjam');
            const newKembali = newItem.querySelector('.date-kembali');
            attachStokListener(newSelect);
            if (newPinjam && newKembali) validateDateRange(newPinjam, newKembali);

            // Event hapus
            const removeBtn = newItem.querySelector('.remove-item-btn');
            removeBtn.addEventListener('click', function () {
                newItem.remove();
                updateRemoveButtons();
            });

            updateRemoveButtons();
        });

        // Event hapus untuk item awal (setelah ada item tambahan, tombol hapus di item pertama diaktifkan)
        function bindRemoveForExisting() {
            document.querySelectorAll('.pinjam-item .remove-item-btn').forEach(btn => {
                btn.removeEventListener('click', btn._listener);
                const listener = function (e) {
                    const item = this.closest('.pinjam-item');
                    if (document.querySelectorAll('.pinjam-item').length > 1) {
                        item.remove();
                        updateRemoveButtons();
                    } else {
                        alert('Minimal satu alat harus dipinjam.');
                    }
                };
                btn.addEventListener('click', listener);
                btn._listener = listener;
            });
        }

        // Override supaya tombol hapus dinamis bekerja
        const observer = new MutationObserver(() => {
            bindRemoveForExisting();
            updateRemoveButtons();
        });
        observer.observe(container, { childList: true, subtree: true });
        bindRemoveForExisting();
        updateRemoveButtons();

        // Validasi tambahan: cegah duplikasi alat yang sama dalam satu form? (opsional)
        function preventDuplicateTools() {
            const selects = document.querySelectorAll('.tool-select');
            const selectedValues = Array.from(selects).map(s => s.value).filter(v => v !== '');
            selects.forEach(select => {
                if (select.value !== '') {
                    const options = select.options;
                    for (let i = 0; i < options.length; i++) {
                        const opt = options[i];
                        if (opt.value !== '' && selectedValues.filter(v => v === opt.value).length > 1 && opt.value === select.value) {
                            select.setCustomValidity('Anda memilih alat yang sama lebih dari satu kali. Periksa kembali.');
                        } else {
                            if (select.getCustomValidity() !== 'Stok habis') select.setCustomValidity('');
                        }
                    }
                }
            });
        }

        container.addEventListener('change', function(e) {
            if (e.target.classList.contains('tool-select')) {
                preventDuplicateTools();
            }
        });
        preventDuplicateTools();
    });
</script>
@endpush

@endsection