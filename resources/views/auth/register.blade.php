@extends('layouts.app')
@section('title', 'Register')
@section('content')
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem">
    <div style="width:100%;max-width:460px">
        <h1 style="font-family:'DM Serif Display',serif;font-size:2.5rem;margin-bottom:.5rem">Create account</h1>
        <p style="color:rgba(245,240,232,.5);font-size:.85rem;margin-bottom:2rem">Build your student portfolio</p>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            @foreach([['full_name','Full Name','text'],['email','Email','email']] as [$name,$label,$type])
            <div style="margin-bottom:1.2rem">
                <label style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:.5rem">{{ $label }}</label>
                <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name) }}" required style="width:100%;padding:12px 16px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.85rem;font-family:'Instrument Sans',sans-serif">
                @error($name)<p style="color:#ff5252;font-size:.72rem;margin-top:.4rem">{{ $message }}</p>@enderror
            </div>
            @endforeach
            <div style="margin-bottom:1.2rem">
                <label style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:.5rem">Username</label>
                <div style="position:relative">
                    <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:rgba(245,240,232,.35);font-family:'Space Mono',monospace;font-size:.82rem">@</span>
                    <input type="text" name="username" value="{{ old('username') }}" required placeholder="yourname"
                        style="width:100%;padding:12px 16px 12px 30px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.85rem;font-family:'Instrument Sans',sans-serif;box-sizing:border-box">
                </div>
                <p style="font-size:.68rem;color:rgba(245,240,232,.35);margin-top:.35rem">Letters, numbers, dashes and underscores only. This becomes your public URL.</p>
                @error('username')<p style="color:#ff5252;font-size:.72rem;margin-top:.4rem">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:1.2rem">
                <label style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:.5rem">Program</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    @foreach(['BSIT' => 'BS Information Technology', 'CSE' => 'Computer Science & Engineering'] as $val => $desc)
                    <label style="cursor:pointer">
                        <input type="radio" name="program" value="{{ $val }}" {{ old('program', 'BSIT')==$val ? 'checked' : '' }} style="display:none" class="prog-radio">
                        <div class="prog-card" data-val="{{ $val }}" style="padding:14px 16px;border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;transition:all .2s;background:rgba(255,255,255,.03)">
                            <div style="font-family:'Space Mono',monospace;font-size:.75rem;font-weight:700;letter-spacing:.06em;margin-bottom:4px">{{ $val }}</div>
                            <div style="font-size:.7rem;color:rgba(245,240,232,.5);line-height:1.4">{{ $desc }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('program')<p style="color:#ff5252;font-size:.72rem;margin-top:.4rem">{{ $message }}</p>@enderror
            </div>
            @foreach([['password','Password'],['password_confirmation','Confirm Password']] as [$name,$label])
            <div style="margin-bottom:1.2rem">
                <label style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:.5rem">{{ $label }}</label>
                <input type="password" name="{{ $name }}" required style="width:100%;padding:12px 16px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.85rem;font-family:'Instrument Sans',sans-serif">
                @error($name)<p style="color:#ff5252;font-size:.72rem;margin-top:.4rem">{{ $message }}</p>@enderror
            </div>
            @endforeach
            <button type="submit" style="width:100%;padding:.8rem;background:#e84040;color:#fff;border:none;border-radius:999px;font-family:'Space Mono',monospace;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;cursor:pointer;margin-top:.5rem">Create Account</button>
        </form>
        <p style="text-align:center;margin-top:1.5rem;font-size:.82rem;color:rgba(245,240,232,.5)">Already have an account? <a href="{{ route('login') }}" style="color:#e84040;font-weight:600">Sign in</a></p>
    </div>
</div>
@endsection

@push('styles')
<style>
.prog-card { user-select: none; }
.prog-radio:checked + .prog-card {
    border-color: #e84040;
    background: rgba(232,64,64,.1);
    color: #f5f0e8;
}
.prog-card:hover {
    border-color: rgba(245,240,232,.35);
    background: rgba(255,255,255,.06);
}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('.prog-radio').forEach(radio => {
    // set initial state
    if (radio.checked) radio.nextElementSibling.style.borderColor = '#e84040';

    radio.addEventListener('change', () => {
        document.querySelectorAll('.prog-radio').forEach(r => {
            r.nextElementSibling.style.borderColor = '';
            r.nextElementSibling.style.background = 'rgba(255,255,255,.03)';
        });
        radio.nextElementSibling.style.borderColor = '#e84040';
        radio.nextElementSibling.style.background = 'rgba(232,64,64,.1)';
    });
});
</script>
@endpush
