<!DOCTYPE html>
<html><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif;background:#f5f5f5;margin:0;padding:20px}.card{max-width:560px;margin:0 auto;background:#fff;border-radius:8px;padding:40px}.h1{font-size:1.6rem;font-weight:700;color:#181818;margin-bottom:1rem}.p{font-size:.9rem;color:#555;line-height:1.7;margin-bottom:1rem}.btn{display:inline-block;padding:.8rem 2rem;background:#e84040;color:#fff;text-decoration:none;border-radius:999px;font-weight:700;font-size:.82rem;margin:1rem 0}.muted{font-size:.75rem;color:#999;margin-top:2rem}</style></head>
<body><div class="card">
    <div class="h1">Reset Your Password</div>
    <p class="p">Hi {{ $user->full_name }}, click the button below to reset your password.</p>
    <a href="{{ url('/password/reset/' . $token) }}" class="btn">Reset Password</a>
    <p class="muted">This link expires in 1 hour. If you didn't request a reset, ignore this email.</p>
</div></body></html>
