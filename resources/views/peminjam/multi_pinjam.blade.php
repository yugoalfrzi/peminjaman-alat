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
                                
                            </div>
                            <div class="col-md-1">
                                <label class="form-label fw-bold">Jumlah <span class="text-danger">*</span></label>
                                <input type="number" name="items[0][jumlah]" class="form-control jumlah-input" value="0" min="0" required>
                            </div>
                            <div class="col-md-2">
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

        // ======================= FUNGSI UMUM =======================
        function updateRemoveButtons() {
            const items = document.querySelectorAll('.pinjam-item');
            items.forEach((item) => {
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

        // Cegah duplikasi alat yang sama dalam satu form
        function preventDuplicateTools() {
            const selects = document.querySelectorAll('.tool-select');
            const selectedValues = Array.from(selects).map(s => s.value).filter(v => v !== '');
            selects.forEach(select => {
                if (select.value !== '') {
                    const isDuplicate = selectedValues.filter(v => v === select.value).length > 1;
                    if (isDuplicate) {
                        select.setCustomValidity('Anda memilih alat yang sama lebih dari satu kali. Periksa kembali.');
                    } else {
                        // Jangan timpa validasi stok habis jika ada
                        if (select.getCustomValidity() !== 'Stok habis' && select.getCustomValidity() !== 'Pilih alat terlebih dahulu') {
                            select.setCustomValidity('');
                        }
                    }
                }
            });
        }

        // ======================= VALIDASI STOK DAN JUMLAH =======================
        function attachItemListeners(itemElement) {
            const selectEl = itemElement.querySelector('.tool-select');
            const jumlahInput = itemElement.querySelector('.jumlah-input');
            const stokInfo = itemElement.querySelector('.stok-info');
            const jumlahInfo = itemElement.querySelector('.jumlah-info');

            function validateJumlah() {
                if (!selectEl.value) {
                    jumlahInput.setCustomValidity('Pilih alat terlebih dahulu');
                    if (jumlahInfo) jumlahInfo.innerHTML = '<span class="text-warning">⚠ Pilih alat</span>';
                    return;
                }

                const selectedOption = selectEl.options[selectEl.selectedIndex];
                const stok = parseInt(selectedOption?.getAttribute('data-stok') || 0);
                const jumlah = parseInt(jumlahInput.value) || 0;

                if (stok <= 0) {
                    jumlahInput.setCustomValidity('Stok habis, tidak dapat dipinjam');
                    if (jumlahInfo) jumlahInfo.innerHTML = '<span class="text-danger">Stok habis</span>';
                } else if (jumlah > stok) {
                    jumlahInput.setCustomValidity(`Jumlah tidak boleh melebihi stok (${stok})`);
                    if (jumlahInfo) jumlahInfo.innerHTML = `<span class="text-danger">Maksimal ${stok}</span>`;
                } else if (jumlah < 1) {
                    jumlahInput.setCustomValidity('Minimal 1');
                    if (jumlahInfo) jumlahInfo.innerHTML = '<span class="text-danger">Minimal 1</span>';
                } else {
                    jumlahInput.setCustomValidity('');
                    if (jumlahInfo) jumlahInfo.innerHTML = `<span class="text-success">✓ Tersedia ${stok}</span>`;
                }
            }

            function updateStokInfo() {
                const selectedOption = selectEl.options[selectEl.selectedIndex];
                const stok = selectedOption?.getAttribute('data-stok') || 0;
                if (selectEl.value) {
                    if (stokInfo) stokInfo.innerHTML = `<span class="text-muted">Stok: ${stok}</span>`;
                    validateJumlah();
                } else {
                    if (stokInfo) stokInfo.innerHTML = '';
                    jumlahInput.setCustomValidity('Pilih alat');
                    if (jumlahInfo) jumlahInfo.innerHTML = '<span class="text-warning">⚠ Pilih alat</span>';
                }
            }

            selectEl.addEventListener('change', () => {
                updateStokInfo();
                validateJumlah();
                preventDuplicateTools();
            });
            jumlahInput.addEventListener('input', validateJumlah);
            jumlahInput.addEventListener('change', validateJumlah);

            // Trigger awal
            updateStokInfo();
            validateJumlah();
        }

        // ======================= INISIALISASI ITEM PERTAMA =======================
        const firstItem = document.querySelector('.pinjam-item');
        if (firstItem) {
            attachItemListeners(firstItem);
            const firstPinjam = firstItem.querySelector('.date-pinjam');
            const firstKembali = firstItem.querySelector('.date-kembali');
            if (firstPinjam && firstKembali) validateDateRange(firstPinjam, firstKembali);
        }

        // ======================= TAMBAH ITEM BARU =======================
        addBtn.addEventListener('click', function () {
            const newIndex = itemCount;
            const newItem = document.createElement('div');
            newItem.className = 'pinjam-item card mb-3 p-3 border';
            newItem.innerHTML = `
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
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
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" name="items[${newIndex}][jumlah]" class="form-control jumlah-input" value="1" min="1" required>
                        <div class="form-text text-muted jumlah-info"></div>
                    </div>
                    <div class="col-md-2">
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

            // Attach semua event listener untuk item baru
            attachItemListeners(newItem);
            const newPinjam = newItem.querySelector('.date-pinjam');
            const newKembali = newItem.querySelector('.date-kembali');
            if (newPinjam && newKembali) validateDateRange(newPinjam, newKembali);

            const removeBtn = newItem.querySelector('.remove-item-btn');
            removeBtn.addEventListener('click', function () {
                newItem.remove();
                updateRemoveButtons();
                preventDuplicateTools(); // re-check duplikasi setelah hapus
            });

            updateRemoveButtons();
            preventDuplicateTools();
        });

        // ======================= HANDLE HAPUS UNTUK ITEM STATIS & DINAMIS =======================
        function bindRemoveForExisting() {
            document.querySelectorAll('.pinjam-item .remove-item-btn').forEach(btn => {
                btn.removeEventListener('click', btn._listener);
                const listener = function (e) {
                    const item = this.closest('.pinjam-item');
                    if (document.querySelectorAll('.pinjam-item').length > 1) {
                        item.remove();
                        updateRemoveButtons();
                        preventDuplicateTools();
                    } else {
                        alert('Minimal satu alat harus dipinjam.');
                    }
                };
                btn.addEventListener('click', listener);
                btn._listener = listener;
            });
        }

        const observer = new MutationObserver(() => {
            bindRemoveForExisting();
            updateRemoveButtons();
        });
        observer.observe(container, { childList: true, subtree: true });
        bindRemoveForExisting();
        updateRemoveButtons();

        // ======================= EVENT DUPLIKASI ALAT =======================
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