@extends('layouts.dashboard')
@section('title', 'Edit Profile')
@section('dashboard-content')
<h1 style="font-family:'DM Serif Display',serif;font-size:2rem;margin-bottom:2rem">Edit Profile</h1>

<div style="display:grid;grid-template-columns:300px 1fr;gap:24px">
    <div style="background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;padding:24px;text-align:center">
        <h2 style="font-size:.8rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:1.5rem">Photo</h2>
        @if($user->profile_photo_path)
            <img src="{{ Storage::disk('portfolio')->url($user->profile_photo_path) }}" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:3px solid #e84040;margin-bottom:1rem">
        @else
            <div style="width:120px;height:120px;border-radius:50%;background:rgba(232,64,64,.15);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem"><i class="fas fa-user" style="font-size:48px;color:#e84040"></i></div>
        @endif
        <form method="POST" action="{{ route('dashboard.profile.photo') }}" enctype="multipart/form-data">
            @csrf
            <label style="display:inline-flex;align-items:center;gap:8px;padding:.6rem 1.4rem;background:#e84040;color:#fff;border-radius:999px;cursor:pointer;font-family:'Space Mono',monospace;font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase">
                <i class="fas fa-upload"></i> Upload
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" style="display:none" onchange="this.form.submit()">
            </label>
        </form>
    </div>

    <div style="background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;padding:24px">
        <h2 style="font-size:.8rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:1.5rem">Profile Info</h2>
        <form method="POST" action="{{ route('dashboard.profile.update') }}">
            @csrf @method('PUT')

            {{-- Username --}}
            <div style="margin-bottom:1.2rem">
                <label style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:.5rem">Username</label>
                <div style="position:relative">
                    <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:rgba(245,240,232,.35);font-family:'Space Mono',monospace;font-size:.82rem">@</span>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}"
                        style="width:100%;padding:12px 16px 12px 30px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.85rem;font-family:'Instrument Sans',sans-serif;box-sizing:border-box">
                </div>
                @error('username')<p style="color:#ff5252;font-size:.72rem;margin-top:.3rem">{{ $message }}</p>@enderror
            </div>

            @foreach([['full_name','Full Name','text',$user->full_name],['bio','Bio','textarea',$user->bio]] as [$name,$label,$type,$val])
            <div style="margin-bottom:1.2rem">
                <label style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:.5rem">{{ $label }}</label>
                @if($type==='textarea')
                    <textarea name="{{ $name }}" rows="3" style="width:100%;padding:12px 16px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.85rem;font-family:'Instrument Sans',sans-serif;resize:vertical">{{ old($name,$val) }}</textarea>
                @else
                    <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name,$val) }}" style="width:100%;padding:12px 16px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.85rem;font-family:'Instrument Sans',sans-serif">
                @endif
                @error($name)<p style="color:#ff5252;font-size:.72rem;margin-top:.3rem">{{ $message }}</p>@enderror
            </div>
            @endforeach

            <div style="margin-bottom:1.5rem">
                <label style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:.5rem">Program</label>
                <select name="program" style="width:100%;padding:12px 16px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.85rem;font-family:'Instrument Sans',sans-serif">
                    <option value="BSIT" {{ $user->program=='BSIT'?'selected':'' }}>BSIT</option>
                    <option value="CSE" {{ $user->program=='CSE'?'selected':'' }}>CSE</option>
                </select>
            </div>

            {{-- Social Media --}}
            <div style="margin-bottom:1.5rem">
                <label style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:.8rem">Social Media</label>
                @php
                    $socials = [
                        'github'    => ['GitHub',    'fab fa-github',    'https://github.com/username'],
                        'linkedin'  => ['LinkedIn',  'fab fa-linkedin',  'https://linkedin.com/in/username'],
                        'twitter'   => ['Twitter/X', 'fab fa-x-twitter', 'https://x.com/username'],
                        'facebook'  => ['Facebook',  'fab fa-facebook',  'https://facebook.com/username'],
                        'instagram' => ['Instagram', 'fab fa-instagram', 'https://instagram.com/username'],
                        'website'   => ['Website',   'fas fa-globe',     'https://yoursite.com'],
                    ];
                    $ci = $user->contact_info ?? [];
                @endphp
                <div style="display:flex;flex-direction:column;gap:10px">
                    @foreach($socials as $key => [$lbl, $icon, $ph])
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="width:28px;text-align:center;opacity:.5;font-size:.95rem"><i class="{{ $icon }}"></i></span>
                        <input type="url" name="contact_info[{{ $key }}]"
                            value="{{ old("contact_info.{$key}", $ci[$key] ?? '') }}"
                            placeholder="{{ $ph }}"
                            style="flex:1;padding:10px 14px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.82rem;font-family:'Instrument Sans',sans-serif">
                    </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" style="padding:.7rem 1.8rem;background:#e84040;color:#fff;border:none;border-radius:999px;font-family:'Space Mono',monospace;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;cursor:pointer">Save Profile</button>
        </form>
    </div>
</div>
@endsection
