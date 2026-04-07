<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIPINJAM | Peminjaman Alat Modern</title>
  <!-- Google Fonts + Font Awesome (tanpa JS, hanya ikon) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', system-ui, sans-serif;
      background: #f4f9fe;
      color: #1f3b4c;
      line-height: 1.5;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 24px;
    }

    /* navbar sederhana, tanpa JS */
    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 0;
      flex-wrap: wrap;
      gap: 16px;
    }

    .logo {
      font-size: 1.9rem;
      font-weight: 800;
      background: linear-gradient(135deg, #1f5e7e, #3282a3);
      background-clip: text;
      -webkit-background-clip: text;
      color: transparent;
      letter-spacing: -0.5px;
    }

    .btn-login {
      display: inline-block;
      background: transparent;
      border: 1.5px solid #bed4e8;
      padding: 8px 28px;
      border-radius: 40px;
      font-weight: 600;
      color: #1f5e7e;
      text-decoration: none;
      transition: 0.2s;
      font-size: 0.9rem;
    }

    .btn-login:hover {
      background: #e7f0f8;
      border-color: #3282a3;
      transform: translateY(-2px);
    }

    /* Hero section - gradasi pastel */
    .hero {
      background: linear-gradient(115deg, #ecf5fb 0%, #e0edf6 100%);
      border-radius: 0 0 48px 48px;
      padding: 64px 0 72px;
      margin-bottom: 56px;
      text-align: center;
    }

    .hero h1 {
      font-size: 2.8rem;
      font-weight: 800;
      color: #0e3a4f;
      margin-bottom: 16px;
      letter-spacing: -0.02em;
    }

    .hero p {
      font-size: 1.2rem;
      color: #2f607d;
      max-width: 620px;
      margin: 0 auto 28px;
    }

    .btn-primary {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: #2c7da0;
      padding: 12px 32px;
      border-radius: 60px;
      font-weight: 600;
      font-size: 1rem;
      color: white;
      text-decoration: none;
      transition: 0.2s;
      box-shadow: 0 6px 14px rgba(44,125,160,0.2);
    }

    .btn-primary:hover {
      background: #1c5e7c;
      transform: translateY(-3px);
      box-shadow: 0 12px 22px rgba(44,125,160,0.25);
    }

    /* 3 Card fitur */
    .features {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      justify-content: center;
      margin: 40px 0 30px;
    }

    .card {
      background: white;
      border-radius: 32px;
      padding: 32px 24px;
      flex: 1;
      min-width: 240px;
      text-align: center;
      transition: all 0.25s ease;
      box-shadow: 0 8px 20px rgba(0,0,0,0.02), 0 2px 6px rgba(0,0,0,0.03);
      border: 1px solid rgba(160, 195, 220, 0.4);
    }

    .card:hover {
      transform: translateY(-8px);
      border-color: #c2d9ed;
      box-shadow: 0 20px 30px -12px rgba(0,0,0,0.1);
    }

    .icon-bg {
      background: #e6f0f7;
      width: 76px;
      height: 76px;
      border-radius: 60px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 2rem;
      color: #2c7da0;
    }

    .card h3 {
      font-size: 1.6rem;
      font-weight: 700;
      margin-bottom: 12px;
      color: #1b4e6b;
    }

    .card p {
      color: #4f6f8a;
      font-size: 0.95rem;
    }

    /* info strip */
    .info-strip {
      display: flex;
      justify-content: center;
      gap: 32px;
      flex-wrap: wrap;
      margin: 40px 0 20px;
      padding: 16px 0;
      border-top: 1px solid #d3e2ef;
      border-bottom: 1px solid #d3e2ef;
    }

    .info-item {
      font-size: 0.85rem;
      color: #3c6b8f;
      font-weight: 500;
    }

    .info-item i {
      margin-right: 8px;
      color: #2c7da0;
    }

    /* footer */
    footer {
      margin-top: 60px;
      background: #eaf0f6;
      padding: 28px 0;
      text-align: center;
      color: #456f8c;
      font-size: 0.8rem;
      border-radius: 32px 32px 0 0;
    }

    @media (max-width: 768px) {
      .hero h1 {
        font-size: 2rem;
      }
      .hero p {
        font-size: 1rem;
      }
      .card h3 {
        font-size: 1.4rem;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <nav class="navbar">
    <div class="logo">SIPINJAM</div>
    <a href="{{ route('login') }}" class="btn-login"><i class="fas fa-user-circle"></i> Login</a>
  </nav>
</div>

<section class="hero">
  <div class="container">
    <h1>Peminjaman Alat <br> Jadi Lebih Mudah</h1>
    <p>Sistem manajemen peminjaman alat laboratorium dan bengkel sekolah yang terintegrasi, cepat, dan transparan.</p>
    <a href="{{ route('login') }}" class="btn-primary"><i class="fas fa-calendar-check"></i> Mulai Peminjaman</a>
  </div>
</section>

<div class="container">
  <div class="features">
    <div class="card">
      <div class="icon-bg"><i class="fas fa-search"></i></div>
      <h3>Cari Alat</h3>
      <p>Cek ketersediaan stok alat secara real-time tanpa perlu bolak-balik ke ruang penyimpanan.</p>
    </div>
    <div class="card">
      <div class="icon-bg"><i class="fas fa-file-signature"></i></div>
      <h3>Ajukan Pinjaman</h3>
      <p>Proses pengajuan peminjaman yang praktis melalui sistem dan persetujuan petugas yang cepat.</p>
    </div>
    <div class="card">
      <div class="icon-bg"><i class="fas fa-undo-alt"></i></div>
      <h3>Pengembalian</h3>
      <p>Sistem monitoring pengembalian alat yang terstruktur untuk menghindari kehilangan aset.</p>
    </div>
  </div>

  <div class="info-strip">
    <span class="info-item"><i class="fas fa-charging-station"></i> Real-time stok</span>
    <span class="info-item"><i class="fas fa-bolt"></i> Persetujuan cepat</span>
    <span class="info-item"><i class="fas fa-chart-line"></i> Laporan bulanan</span>
  </div>
</div>

<footer>
  <div class="container">
    <p>&copy; {{ date('Y')}} SIPINJAM — Sistem Peminjaman Alat Laboratorium & Bengkel Sekolah</p>
  </div>
</footer>

</body>
</html>