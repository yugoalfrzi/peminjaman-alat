@extends('layouts.app')

@section('content')
    <h3>Riwayat Peminjaman Saya</h3>

    {{-- Menunggu Persetujuan --}}
    <div class="card mt-3 shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Menunggu Persetujuan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>No</th><th>Alat</th><th>Jumlah</th><th>Tgl Pinjam</th><th>Rencana Kembali</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($pendingLoans as $loan)
                            <tr><td>{{ $loop->iteration }}</td><td>{{ $loan->tool->nama_alat }}</td><td>{{ $loan->jumlah ?? 1 }}</td><td>{{ $loan->tanggal_pinjam }}</td><td>{{ $loan->tanggal_kembali_rencana }}</td><td><span class="badge bg-warning text-dark">Menunggu Persetujuan</span></td></tr>
                        @empty
                            <tr><td colspan="6" class="text-center">Belum ada pengajuan yang menunggu persetujuan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sudah Disetujui --}}
    <div class="card mt-4 shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Sudah Disetujui</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>No</th><th>Alat</th><th>Jumlah</th><th>Tgl Pinjam</th><th>Rencana Kembali</th><th>Status</th><th>Catatan</th></tr>
                    </thead>
                    <tbody>
                        @forelse($approvedLoans as $loan)
                            <tr><td>{{ $loop->iteration }}</td><td>{{ $loan->tool->nama_alat }}</td><td>{{ $loan->jumlah ?? 1 }}</td><td>{{ $loan->tanggal_pinjam }}</td><td>{{ $loan->tanggal_kembali_rencana }}</td><td><span class="badge bg-primary">Sedang Dipinjam</span></td><td><small class="text-muted">Harap kembalikan ke petugas sebelum tanggal rencana.</small></td></tr>
                        @empty
                            <td><td colspan="7" class="text-center">Belum ada peminjaman yang disetujui.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sudah Dikembalikan (dengan pembayaran via Midtrans) --}}
    <div class="card mt-4 shadow-sm border-0 rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Sudah Dikembalikan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Alat</th>
                            <th>Jumlah</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                            <th>Denda</th>
                            <th>Status Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returnedLoans as $loan)
                            <tr id="loan-row-{{ $loan->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $loan->tool->nama_alat }}</td>
                                <td>{{ $loan->jumlah ?? 1 }}</td>
                                <td>{{ $loan->tanggal_kembali_aktual ?? '-' }}</td>
                                <td><span class="badge bg-success">Sudah Dikembalikan</span></td>
                                <td>
                                    @if(($loan->denda ?? 0) > 0)
                                        <span class="text-danger fw-bold">Rp {{ number_format($loan->denda, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">Rp 0</span>
                                    @endif
                                </td>
                                <td id="payment-status-{{ $loan->id }}">
                                    @if(($loan->denda ?? 0) > 0 && !$loan->is_paid)
                                        <span class="badge bg-danger">Belum Lunas</span>
                                    @else
                                        <span class="badge bg-success">Lunas</span>
                                    @endif
                                </td>
                                <td>
                                    @if(($loan->denda ?? 0) > 0 && !$loan->is_paid)
                                        <button class="btn btn-sm btn-primary pay-now-btn" 
                                                data-id="{{ $loan->id }}"
                                                data-amount="{{ $loan->denda }}"
                                                data-tool="{{ $loan->tool->nama_alat }}">
                                            Bayar Denda
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>Lunas</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center">Belum ada peminjaman yang sudah dikembalikan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
{{-- Hanya script Midtrans Snap --}}
<script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
    (function() {
        // Ambil token CSRF dari meta tag (pastikan ada di layout utama)
        const getCsrfToken = () => {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '{{ csrf_token() }}';
        };

        // Event listener untuk tombol bayar (delegasi)
        document.addEventListener('click', async function(e) {
            const button = e.target.closest('.pay-now-btn');
            if (!button) return;

            e.preventDefault();
            
            const loanId = button.dataset.id;
            const originalText = button.innerText;
            
            // Disable tombol
            button.disabled = true;
            button.innerText = 'Memproses...';
            
            try {
                // Panggil endpoint untuk membuat transaksi Midtrans
                const response = await fetch('{{ route("payment.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({ loan_id: loanId })
                });
                
                const data = await response.json();
                
                if (data.success && data.snap_token) {
                    // Buka popup Midtrans Snap
                    snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            // Update tampilan
                            const statusCell = document.getElementById('payment-status-' + loanId);
                            if (statusCell) statusCell.innerHTML = '<span class="badge bg-success">Lunas</span>';
                            
                            // Ganti tombol dengan tombol Lunas (disabled)
                            const newBtn = document.createElement('button');
                            newBtn.className = 'btn btn-sm btn-secondary';
                            newBtn.disabled = true;
                            newBtn.innerText = 'Lunas';
                            button.parentNode.replaceChild(newBtn, button);
                            
                            alert('Pembayaran berhasil! Terima kasih.');
                        },
                        onPending: function(result) {
                            alert('Menunggu pembayaran Anda. Silakan selesaikan pembayaran.');
                            button.disabled = false;
                            button.innerText = originalText;
                        },
                        onError: function(result) {
                            alert('Pembayaran gagal. Silakan coba lagi.');
                            button.disabled = false;
                            button.innerText = originalText;
                        },
                        onClose: function() {
                            alert('Anda menutup popup pembayaran sebelum selesai.');
                            button.disabled = false;
                            button.innerText = originalText;
                        }
                    });
                } else {
                    alert(data.message || 'Gagal memproses pembayaran.');
                    button.disabled = false;
                    button.innerText = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silakan coba lagi.');
                button.disabled = false;
                button.innerText = originalText;
            }
        });
    })();
</script>
@endpush