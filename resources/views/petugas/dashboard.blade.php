@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="border-0 pt-4 pb-0">
        <h1>Dashboard Petugas</h1>
        <p class="text-muted">Selamat datang, {{ auth()->user()->name }}!</p>
    </div>
    <div class="card mb-4 pb-4 shadow-sm border-0 rounded-0"> 
        <h3 class="pt-4 pb-2">Permintaan Peminjaman Masuk</h3>
        <div class="card">
            <div class="card-header fw-bold bg-white py-2">Menunggu Persetujuan</div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table">
                            <tr>
                                <th>Peminjam</th>
                                <th>Alat</th>
                                <th>Tgl Pinjam</th>
                                <th>Rencana Kembali</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $groupedPendingLoans = $loans->groupBy('user_id');
                            @endphp
                            @forelse($groupedPendingLoans as $userLoans)
                                <tr>
                                    <td>{{ $userLoans->first()->user->name }}</td>
                                    <td>
                                        @foreach($userLoans as $loan)
                                            <div class="mb-2 pb-2 border-bottom">
                                                <strong>{{ $loan->tool->nama_alat }}</strong> ({{ $loan->jumlah ?? 1 }} unit)
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($userLoans as $loan)
                                            <div class="mb-2 pb-2 border-bottom">
                                                {{ \Carbon\Carbon::parse($loan->tanggal_pinjam)->format('d-m-Y') }}
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($userLoans as $loan)
                                            <div class="mb-2 pb-2 border-bottom">
                                                {{ \Carbon\Carbon::parse($loan->tanggal_kembali_rencana)->format('d-m-Y') }}
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($userLoans as $loan)
                                            <div class="mb-2 pb-2 border-bottom">
                                                <button type="button"
                                                    class="btn btn-success btn-sm btn-open-approve-modal"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#approveModal"
                                                    data-loan-id="{{ $loan->id }}"
                                                    data-tool-name="{{ $loan->tool->nama_alat }}">
                                                    Setujui
                                                </button>
                                                <form action="{{ url('/petugas/reject/' .$loan->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-danger btn-sm">Tolak</button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center">Tidak ada permintaan baru</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4 pb-4 shadow-sm border-0 rounded-0">
      <h3 class="pt-4 pb-2">Daftar sedang dipinjam</h3>
      <div class="card">
          <div class="card-header fw-bold bg-white">Monitor Peminjaman</div>
          <div class="card-body p-4">
              <div class="table-responsive">
                  <table class="table table-bordered table-hover align-middle">
                      <thead class="table-light">
                          <tr>
                              <th>Peminjam</th>
                              <th>Alat</th>
                              <th>Status</th>
                              <th>Rencana Kembali</th>
                              <th>Tanggal Kembali Aktual</th>
                              <th>Jumlah</th>
                              <th>Denda (Rp)</th>
                              <th>Aksi</th>
                          </tr>
                      </thead>
                      <tbody>
                          @php
                              $groupedActiveLoans = $activeLoans->groupBy('user_id');
                          @endphp
                          @forelse($groupedActiveLoans as $userActiveLoans)
                              <tr>
                                  <td>{{ $userActiveLoans->first()->user->name }}</td>
                                  <td>
                                      @foreach($userActiveLoans as $active)
                                          <div class="mb-2 pb-2 border-bottom">
                                              {{ $active->tool->nama_alat }}
                                          </div>
                                      @endforeach
                                  </td>
                                  <td><span class="badge bg-primary">Sedang Dipinjam</span></td>
                                  <td>
                                      @foreach($userActiveLoans as $active)
                                          <div class="mb-2 pb-2 border-bottom">
                                              {{ $active->tanggal_kembali_rencana->format('d-m-Y') }}
                                          </div>
                                      @endforeach
                                  </td>
                                  <td>
                                      @foreach($userActiveLoans as $active)
                                          <div class="mb-2 pb-2 border-bottom">
                                              <input type="date" name="tanggal_kembali_aktual" class="form-control form-control-sm tgl-aktual"
                                                  data-id="{{ $active->id }}"
                                                  data-rencana="{{ $active->tanggal_kembali_rencana->format('Y-m-d') }}"
                                                  value="{{ date('Y-m-d') }}">
                                          </div>
                                      @endforeach
                                  </td>
                                  <td>
                                      @foreach($userActiveLoans as $active)
                                          <div class="mb-2 pb-2 border-bottom">
                                              {{ $active->jumlah }}
                                          </div>
                                      @endforeach
                                  </td>
                                  <td>
                                      @foreach($userActiveLoans as $active)
                                          <div class="denda-display mb-2 pb-2 border-bottom" id="denda-{{ $active->id }}">
                                              <span class="text-muted">Rp 0</span>
                                          </div>
                                      @endforeach
                                  </td>
                                  <td>
                                      @foreach($userActiveLoans as $active)
                                          <div class="mb-2 pb-2 border-bottom">
                                              <div class="mb-1">
                                                  @if($active->initial_photo)
                                                      <a href="{{ asset('storage/' . $active->initial_photo) }}" target="_blank" class="btn btn-sm btn-outline-secondary mb-1">
                                                          Lihat Foto Awal
                                                      </a>
                                                  @else
                                                      <small class="d-block text-muted">Foto kondisi awal belum tersedia</small>
                                                  @endif
                                              </div>
                                              <div class="mb-1">
                                                  <select name="kerusakan" class="form-select form-select-sm select-kerusakan" data-id="{{ $active->id }}">
                                                      <option value="tidak_rusak">Tidak rusak (Rp 0)</option>
                                                      <option value="ringan">Rusak ringan (Rp 5.000)</option>
                                                      <option value="sedang">Rusak sedang (Rp 10.000)</option>
                                                      <option value="berat">Rusak berat (Rp 20.000)</option>
                                                  </select>
                                                  <small class="text-muted">Pilih tingkat kerusakan</small>
                                              </div>
                                              <button type="button"
                                                  class="btn btn-primary btn-sm mt-2 btn-open-return-modal"
                                                  data-bs-toggle="modal"
                                                  data-bs-target="#returnModal"
                                                  data-loan-id="{{ $active->id }}"
                                                  data-tool-name="{{ $active->tool->nama_alat }}">
                                                  Upload Foto & Proses
                                              </button>
                                          </div>
                                      @endforeach
                                  </td>
                              </tr>
                          @empty
                              <tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
                          @endforelse
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
    </div>
    <div class="card mb-4 pb-4 shadow-sm border-0 rounded-0">
        <h3 class="pt-4 pb-2">Daftar Sudah Dikembalikan</h3>
        <div class="card">
            <div class="card-header fw-bold bg-white">Monitor Peminjaman</div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Peminjam</th>
                                <th>Alat</th>
                                <th>Status</th>
                                <th>Tanggal Kembali Aktual</th>
                                <th>Jumlah</th>
                                <th>Denda (Rp)</th>
                                <th>Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $groupedReturnedLoans = $sudahDikembalikan->groupBy('user_id');
                            @endphp
                            @forelse($groupedReturnedLoans as $userReturnedLoans)
                                <tr>
                                    <td>{{ $userReturnedLoans->first()->user->name }}</td>
                                    <td>
                                        @foreach($userReturnedLoans as $sudah)
                                            <div class="mb-2 pb-2 border-bottom">{{ $sudah->tool->nama_alat }}</div>
                                        @endforeach
                                    </td>
                                    <td><span class="badge bg-success">Sudah Kembali</span></td>
                                    <td>
                                        @foreach($userReturnedLoans as $sudah)
                                            <div class="mb-2 pb-2 border-bottom">
                                                {{ $sudah->tanggal_kembali_aktual ? $sudah->tanggal_kembali_aktual->format('d-m-Y') : '-' }}
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($userReturnedLoans as $sudah)
                                            <div class="mb-2 pb-2 border-bottom">{{ $sudah->jumlah }}</div>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($userReturnedLoans as $sudah)
                                            <div class="mb-2 pb-2 border-bottom">
                                                @if($sudah->denda > 0)
                                                    <span class="text-danger fw-bold">Rp {{ number_format($sudah->denda, 0, ',', '.') }}</span>
                                                @else
                                                    <span class="text-muted">Rp 0</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($userReturnedLoans as $sudah)
                                            <div class="mb-2 pb-2 border-bottom">
                                                @if($sudah->proof_photo)
                                                    <a href="{{ asset('storage/' . $sudah->proof_photo) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                        Bukti {{ $sudah->tool->nama_alat }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Tidak ada bukti</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Tidak ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveModalForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Setujui Peminjaman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Upload foto kondisi awal untuk alat: <strong id="approveToolName">-</strong></p>
                    <input type="file" name="initial_photo" accept="image/*" required class="form-control">
                    <small class="text-muted">Format: JPG/PNG, maksimal 2MB.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Setujui</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="returnModalForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="tanggal_kembali_aktual" id="modalTanggalKembaliAktual">
                <input type="hidden" name="kerusakan" id="modalKerusakan">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnModalLabel">Proses Pengembalian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Upload foto kondisi akhir untuk alat: <strong id="returnToolName">-</strong></p>
                    <input type="file" name="proof_photo" accept="image/*" required class="form-control mb-2">
                    <small class="text-muted d-block">Tanggal kembali dan tingkat kerusakan mengikuti pilihan di tabel.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Proses Kembali</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fungsi untuk menghitung denda keterlambatan (Rp 5000/hari)
        function hitungDendaKeterlambatan(tglRencana, tglAktual) {
            if (!tglRencana || !tglAktual) return 0;
            const rencana = new Date(tglRencana);
            const aktual = new Date(tglAktual);
            rencana.setHours(0, 0, 0, 0);
            aktual.setHours(0, 0, 0, 0);
            if (aktual <= rencana) return 0;
            const diffDays = Math.ceil((aktual - rencana) / (1000 * 60 * 60 * 24));
            return diffDays * 5000; // Rp 5000 per hari
        }

        // Fungsi untuk mendapatkan denda kerusakan berdasarkan pilihan
        function getDendaKerusakan(value) {
            switch (value) {
                case 'ringan': return 5000;
                case 'sedang': return 10000;
                case 'berat': return 20000;
                default: return 0;
            }
        }

        // Fungsi utama untuk memperbarui tampilan total denda
        function updateTotalDenda(loanId) {
            const tglInput = document.querySelector(`.tgl-aktual[data-id="${loanId}"]`);
            const selectKerusakan = document.querySelector(`.select-kerusakan[data-id="${loanId}"]`);
            const dendaSpan = document.getElementById(`denda-${loanId}`);
            
            if (!tglInput || !selectKerusakan || !dendaSpan) return;
            
            const tglRencana = tglInput.getAttribute('data-rencana');
            const tglAktual = tglInput.value;
            const kerusakan = selectKerusakan.value;
            
            const dendaTelat = hitungDendaKeterlambatan(tglRencana, tglAktual);
            const dendaRusak = getDendaKerusakan(kerusakan);
            const totalDenda = dendaTelat + dendaRusak;
            
            // Update tampilan
            if (totalDenda > 0) {
                dendaSpan.innerHTML = `<span class="text-danger fw-bold">Rp ${totalDenda.toLocaleString('id-ID')}</span>`;
            } else {
                dendaSpan.innerHTML = `<span class="text-muted">Rp 0</span>`;
            }
            
            // Update hidden input untuk dikirim ke server (opsional, karena server akan hitung ulang)
            const hiddenInput = document.getElementById(`hidden-tgl-${loanId}`);
            if (hiddenInput) hiddenInput.value = tglAktual;
        }
        
        // Pasang event listener untuk semua input tanggal dan dropdown kerusakan
        const tanggalInputs = document.querySelectorAll('.tgl-aktual');
        const kerusakanSelects = document.querySelectorAll('.select-kerusakan');
        
        // Inisialisasi semua denda saat halaman dimuat
        tanggalInputs.forEach(input => {
            const loanId = input.getAttribute('data-id');
            updateTotalDenda(loanId);
            input.addEventListener('change', function() {
                updateTotalDenda(loanId);
            });
        });
        
        kerusakanSelects.forEach(select => {
            const loanId = select.getAttribute('data-id');
            select.addEventListener('change', function() {
                updateTotalDenda(loanId);
            });
        });

        const approveModalForm = document.getElementById('approveModalForm');
        const approveToolName = document.getElementById('approveToolName');
        const approveButtons = document.querySelectorAll('.btn-open-approve-modal');

        approveButtons.forEach((btn) => {
            btn.addEventListener('click', function () {
                const loanId = this.getAttribute('data-loan-id');
                const toolName = this.getAttribute('data-tool-name');
                approveModalForm.setAttribute('action', `/petugas/approve/${loanId}`);
                approveToolName.textContent = toolName || '-';
            });
        });

        const returnModalForm = document.getElementById('returnModalForm');
        const returnToolName = document.getElementById('returnToolName');
        const modalTanggalInput = document.getElementById('modalTanggalKembaliAktual');
        const modalKerusakanInput = document.getElementById('modalKerusakan');
        const returnButtons = document.querySelectorAll('.btn-open-return-modal');

        returnButtons.forEach((btn) => {
            btn.addEventListener('click', function () {
                const loanId = this.getAttribute('data-loan-id');
                const toolName = this.getAttribute('data-tool-name');
                const tglInput = document.querySelector(`.tgl-aktual[data-id="${loanId}"]`);
                const kerusakanInput = document.querySelector(`.select-kerusakan[data-id="${loanId}"]`);

                returnModalForm.setAttribute('action', `/petugas/return/${loanId}`);
                returnToolName.textContent = toolName || '-';
                modalTanggalInput.value = tglInput ? tglInput.value : '';
                modalKerusakanInput.value = kerusakanInput ? kerusakanInput.value : 'tidak_rusak';
            });
        });
    });
</script>
@endsection