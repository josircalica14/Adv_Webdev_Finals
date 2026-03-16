@extends('layouts.app')
@section('title', 'New Password')
@section('content')
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem">
    <div style="width:100%;max-width:420px">
        <h1 style="font-family:'DM Serif Display',serif;font-size:2rem;margin-bottom:2rem">Set new password</h1>
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            @foreach([['email','Email','email'],['password','New Password','password'],['password_confirmation','Confirm Password','password']] as [$name,$label,$type])
            <div style="margin-bottom:1.2rem">
                <label style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:.5rem">{{ $label }}</label>
                <input type="{{ $type }}" name="{{ $name }}" {{ $type!='password'?'value="'.old($name).'"':'' }} required style="width:100%;padding:12px 16px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.85rem;font-family:'Instrument Sans',sans-serif">
                @error($name)<p style="color:#ff5252;font-size:.72rem;margin-top:.4rem">{{ $message }}</p>@enderror
            </div>
            @endforeach
            <button type="submit" style="width:100%;padding:.8rem;background:#e84040;color:#fff;border:none;border-radius:999px;font-family:'Space Mono',monospace;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;cursor:pointer">Reset Password</button>
        </form>
    </div>
</div>
@endsection
