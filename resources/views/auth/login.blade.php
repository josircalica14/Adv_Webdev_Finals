@extends('layouts.app')
@section('title', 'Login')
@section('content')
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem">
    <div style="width:100%;max-width:420px">
        <h1 style="font-family:'DM Serif Display',serif;font-size:2.5rem;margin-bottom:.5rem">Welcome back</h1>
        <p style="color:rgba(245,240,232,.5);font-size:.85rem;margin-bottom:2rem">Sign in to your portfolio</p>

        @if(session('status'))<div style="background:rgba(76,175,80,.15);color:#4caf50;border:1px solid rgba(76,175,80,.3);padding:12px;border-radius:.5rem;margin-bottom:1rem;font-size:.78rem">{{ session('status') }}</div>@endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div style="margin-bottom:1.2rem">
                <label style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:.5rem">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required style="width:100%;padding:12px 16px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.85rem;font-family:'Instrument Sans',sans-serif" @error('email') style="border-color:#e84040" @enderror>
                @error('email')<p style="color:#ff5252;font-size:.72rem;margin-top:.4rem">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:1.5rem">
                <label style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:.5rem">Password</label>
                <input type="password" name="password" required style="width:100%;padding:12px 16px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.85rem;font-family:'Instrument Sans',sans-serif">
            </div>
            <button type="submit" style="width:100%;padding:.8rem;background:#e84040;color:#fff;border:none;border-radius:999px;font-family:'Space Mono',monospace;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;cursor:pointer">Sign In</button>
        </form>
        <p style="text-align:center;margin-top:1.5rem;font-size:.82rem;color:rgba(245,240,232,.5)">
            No account? <a href="{{ route('register') }}" style="color:#e84040;font-weight:600">Register</a> &nbsp;·&nbsp;
            <a href="{{ route('password.request') }}" style="color:rgba(245,240,232,.5)">Forgot password?</a>
        </p>
    </div>
</div>
@endsection
