<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Aplikasi Peminjaman Alat</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, rgba(244, 249, 254, 0.9), rgba(31, 59, 76, 0.1)), 
                        url('{{ asset('images/SIPINJAM.png') }}');
            background-repeat: no-repeat;
            background-position: center center;
            background-size: cover;
            background-attachment: fixed;
            color: #1f3b4c;
            line-height: 1.5;
        }

        .navbar-modern {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02), 0 1px 0 rgba(0, 0, 0, 0.05);
            padding: 0.75rem 0;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.7rem;
            background: linear-gradient(135deg, #1f5e7e, #3282a3);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            letter-spacing: -0.5px;
        }

        .nav-link {
            font-weight: 500;
            color: #2c5a77 !important;
            transition: 0.2s;
            margin: 0 2px;
            border-radius: 40px;
            padding: 0.5rem 1rem;
        }

        .nav-link:hover {
            background: #e9f2f9;
            color: #1a4b66 !important;
            transform: translateY(-1px);
        }

        .dropdown-menu {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(4px);
            padding: 0.5rem;
        }

        .dropdown-item {
            border-radius: 14px;
            font-weight: 500;
            transition: 0.2s;
        }

        .dropdown-item:hover {
            background: #eef4fa;
            transform: translateX(4px);
        }

        .alert {
            border: none;
            border-radius: 24px;
            padding: 1rem 1.5rem;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background: #e0f2e9;
            color: #1f6e43;
        }

        .alert-danger {
            background: #feeceb;
            color: #b33c2e;
        }

        
        .card-modern {
            background: white;
            border-radius: 32px;
            border: 1px solid rgba(160, 195, 220, 0.4);
            box-shadow: 0 8px 20px rgba(0,0,0,0.02);
            transition: all 0.2s;
        }

        .btn-primary-soft {
            background: #2c7da0;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 40px;
            font-weight: 600;
            color: white;
            transition: 0.2s;
        }

        .btn-primary-soft:hover {
            background: #1f5e7e;
            transform: translateY(-2px);
        }

        .btn-outline-soft {
            border: 1.5px solid #cbdde9;
            background: transparent;
            border-radius: 40px;
            padding: 0.4rem 1.2rem;
            font-weight: 500;
            color: #2c5a77;
        }

        .btn-outline-soft:hover {
            background: #eef4fa;
            border-color: #2c7da0;
        }

         
        .main-container {
            max-width: 1400px;
            margin: 1.5rem auto;
            padding: 0 24px;
        }
    </style>
    @stack('styles')
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-modern sticky-top">
        <div class="container">
            <a class="navbar-brand">SIPINJAM</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <!-- Left menu -->
                <ul class="navbar-nav me-auto">
                    @auth
                        @if(auth()->user()->role == 'admin')
                            <li class="nav-item"><a class="nav-link" href="/admin/dashboard"><i class="fas fa-home-alt me-1"></i>Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('categories.index') }}"><i class="fas fa-tags me-1"></i>Kategori</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('tools.index') }}"><i class="fas fa-tools me-1"></i>Alat</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}"><i class="fas fa-users me-1"></i>User</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('admin.loans.index') }}"><i class="fas fa-hand-holding me-1"></i>Peminjaman</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('admin.returns.index') }}"><i class="fas fa-undo-alt me-1"></i>Pengembalian</a></li>
                        @elseif(auth()->user()->role == 'petugas')
                            <li class="nav-item"><a class="nav-link" href="/petugas/dashboard"><i class="fas fa-check-circle me-1"></i>Validasi Peminjaman</a></li>
                            <li class="nav-item"><a class="nav-link" href="/petugas/laporan"><i class="fas fa-chart-line me-1"></i>Laporan</a></li>
                        @elseif(auth()->user()->role == 'peminjam')
                            <li class="nav-item"><a class="nav-link" href="/peminjam/dashboard"><i class="fas fa-list me-1"></i>Daftar Alat</a></li>
                            <li class="nav-item"><a class="nav-link" href="/peminjam/riwayat"><i class="fas fa-history me-1"></i>Riwayat Saya</a></li>
                        @endif
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i>{{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link btn-outline-soft px-3" href="{{ route('login') }}"><i class="fas fa-key me-1"></i>Login</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> Terjadi kesalahan:
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>