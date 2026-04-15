@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="border-0 pt-4 pb-0">
        <h1>Dashboard Petugas</h1>
        <p class="text-muted">Selamat datang, {{ auth()->user()->name }}!</p>
    </div>
    <div class="card mb-4 pb-4 shadow-sm border-0 rounded-4"> 
        <h3 class="pt-4 pb-2">Permintaan Peminjaman Masuk</h3>
        <div class="card">
            <div class="card-header bg-warning text-dark">Menunggu Persetujuan</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-light">
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
                                                <div class="small mb-1">{{ $loan->tool->nama_alat }}</div>
                                                <form action="{{ url('/petugas/approve/'.$loan->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-success btn-sm">Setujui</button>
                                                </form>
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
    <div class="card mb-4 pb-4 shadow-sm border-0 rounded-4">
      <h3 class="pt-4 pb-2">Daftar sedang dipinjam</h3>
      <div class="card">
          <div class="card-header bg-info text-dark">Monitor Peminjaman</div>
          <div class="card-body">
              <div class="table-responsive">
                  <table class="table table-bordered table-striped table-hover align-middle">
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
                                              <form action="{{ url('/petugas/return/'.$active->id) }}" method="POST" class="return-form" enctype="multipart/form-data">
                                                  @csrf
                                                  <div class="mb-1">
                                                      <input type="file" name="proof_photo" accept="image/*" required class="form-control form-control-sm">
                                                      <small class="d-block text-muted">Upload bukti {{ $active->tool->nama_alat }}</small>
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
                                                  <input type="hidden" name="tanggal_kembali_aktual" class="hidden-tgl" id="hidden-tgl-{{ $active->id }}" value="">
                                                  <button type="submit" class="btn btn-primary btn-sm mt-2">Proses Kembali</button>
                                              </form>
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
    <div class="card mb-4 pb-4 shadow-sm border-0 rounded-4">
        <h3 class="pt-4 pb-2">Daftar Sudah Dikembalikan</h3>
        <div class="card">
            <div class="card-header bg-success text-dark">Monitor Peminjaman</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle">
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
    });
</script>
@endsection