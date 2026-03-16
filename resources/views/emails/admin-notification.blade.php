<!DOCTYPE html>
<html><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif;background:#f5f5f5;margin:0;padding:20px}.card{max-width:560px;margin:0 auto;background:#fff;border-radius:8px;padding:40px}.h1{font-size:1.4rem;font-weight:700;color:#181818;margin-bottom:1rem}.p{font-size:.9rem;color:#555;line-height:1.7}</style></head>
<body><div class="card">
    <div class="h1">{{ $adminSubject }}</div>
    <p class="p">Hi {{ $recipient->full_name }},</p>
    <p class="p">{{ $adminMessage }}</p>
    <p class="p" style="color:#999;font-size:.78rem;margin-top:2rem">This message was sent by the Portfolio Platform admin team.</p>
</div></body></html>
