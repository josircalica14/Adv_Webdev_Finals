<!DOCTYPE html>
<html><head><meta charset="UTF-8"><style>body{font-family:'Instrument Sans',Arial,sans-serif;background:#f5f5f5;margin:0;padding:20px}.card{max-width:560px;margin:0 auto;background:#fff;border-radius:8px;padding:40px}.logo{font-family:monospace;font-size:.8rem;letter-spacing:.1em;font-weight:700;color:#181818;margin-bottom:2rem}.h1{font-size:1.8rem;font-weight:700;color:#181818;margin-bottom:1rem}.p{font-size:.9rem;color:#555;line-height:1.7;margin-bottom:1rem}.btn{display:inline-block;padding:.8rem 2rem;background:#e84040;color:#fff;text-decoration:none;border-radius:999px;font-weight:700;font-size:.82rem;margin:1rem 0}.muted{font-size:.75rem;color:#999;margin-top:2rem}</style></head>
<body><div class="card">
    <div class="logo">PORTFOLIO PLATFORM</div>
    <div class="h1">Welcome, {{ $user->full_name }}!</div>
    <p class="p">Thanks for joining. Please verify your email address to get started.</p>
    <a href="{{ url('/email/verify/' . $verificationToken) }}" class="btn">Verify Email</a>
    <p class="muted">This link expires in 24 hours. If you didn't create an account, ignore this email.</p>
</div></body></html>
