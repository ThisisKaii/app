{{-- resources/views/auth/verify-otp.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .otp-input {
            font-size: 24px;
            text-align: center;
            letter-spacing: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Verify OTP</h4>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <p>We've sent a 6-digit OTP to <strong>{{ session('email') }}</strong></p>
                        <p class="text-muted">Please enter the OTP below:</p>

                        <form method="POST" action="{{ route('password.verify') }}">
                            @csrf
                            <input type="hidden" name="email" value="{{ session('email') }}">

                            <div class="mb-3">
                                <input 
                                    type="text" 
                                    class="form-control otp-input @error('otp') is-invalid @enderror" 
                                    id="otp" 
                                    name="otp" 
                                    maxlength="6"
                                    placeholder="000000"
                                    required 
                                    autofocus
                                >
                                @error('otp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    Verify OTP
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <form method="POST" action="{{ route('password.resend') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="email" value="{{ session('email') }}">
                                <button type="submit" class="btn btn-link">Resend OTP</button>
                            </form>
                            <span class="mx-2">|</span>
                            <a href="{{ route('password.request') }}">Change Email</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>