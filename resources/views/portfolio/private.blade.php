@extends('layouts.app')
@section('title', 'Portfolio Private')
@section('content')
<div style="min-height:60vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:3rem">
    <div>
        <i class="fas fa-lock" style="font-size:3rem;color:#e84040;margin-bottom:1.5rem;display:block"></i>
        <h1 style="font-family:'DM Serif Display',serif;font-size:2rem;margin-bottom:.75rem">Your portfolio is private</h1>
        <p style="color:rgba(245,240,232,.5);font-size:.9rem;margin-bottom:2rem">Enable public visibility in Settings to share your portfolio.</p>
        <a href="{{ route('dashboard.settings.show') }}" style="padding:.7rem 1.8rem;background:#e84040;color:#fff;border-radius:999px;font-family:'Space Mono',monospace;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase">Go to Settings</a>
    </div>
</div>
@endsection
