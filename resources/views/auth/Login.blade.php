@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-5 col-lg-4">
        <div class="card border-0 shadow-sm" style="border-radius: 2rem; background: rgba(255,255,255,0.95); backdrop-filter: blur(2px);">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h3 class="fw-bold" style="color: #1f5e7e;">SIPINJAM</h3>
                    <p class="text-muted">Silakan login untuk melanjutkan</p>
                </div>

                <form action="{{ url('/login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold"> Email</label>
                        <div class="input-group">
                            <input type="email" name="email" id="email" class="form-control border-start-0" 
                                   style="border-radius:  60px 60px ;" 
                                   placeholder="contoh@email.com" value="{{ old('email') }}" required autofocus>
                        </div>
                        @error('email')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Kata Sandi</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control border-start-0" 
                                   style="border-radius: 60px 60px ;" placeholder="********" required>
                        </div>
                        @error('password')
                            <small class="text-danger mt-1 d-block">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary-soft py-2 fw-semibold">
                            <i class="fas fa-arrow-right-to-bracket me-2"></i> Masuk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-primary-soft {
        background: #2c7da0;
        border: none;
        border-radius: 60px;
        transition: all 0.2s ease;
        color: white;
    }
    .btn-primary-soft:hover {
        background: #1f5e7e;
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(44,125,160,0.2);
    }
    .form-control, .input-group-text {
        border-color: #d4e0e9;
        background-color: white;
        padding: 0.6rem 1rem;
    }
    .form-control:focus {
        border-color: #2c7da0;
        box-shadow: 0 0 0 3px rgba(44,125,160,0.1);
    }
</style>
@endpush     