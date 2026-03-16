@extends('layouts.dashboard')
@section('title', 'Settings')
@section('dashboard-content')
<h1 style="font-family:'DM Serif Display',serif;font-size:2rem;margin-bottom:2rem">Settings</h1>

<div style="max-width:700px">
    <div style="background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;padding:30px;margin-bottom:20px">
        <h2 style="font-size:.8rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:1.5rem;padding-bottom:12px;border-bottom:1px solid rgba(245,240,232,.1)">Portfolio Visibility</h2>
        <form method="POST" action="{{ route('dashboard.settings.visibility') }}">
            @csrf @method('PUT')
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:1.5rem">
                <label style="position:relative;cursor:pointer">
                    <input type="checkbox" name="is_public" value="1" {{ $portfolio->is_public ? 'checked' : '' }} style="position:absolute;opacity:0;width:0;height:0" id="vis-toggle">
                    <span id="vis-slider" style="display:block;width:56px;height:28px;background:{{ $portfolio->is_public ? 'rgba(232,64,64,.3)' : 'rgba(255,255,255,.1)' }};border:2px solid {{ $portfolio->is_public ? '#e84040' : 'rgba(245,240,232,.2)' }};border-radius:50px;position:relative;transition:all .3s">
                        <span style="position:absolute;width:20px;height:20px;background:{{ $portfolio->is_public ? '#e84040' : 'rgba(245,240,232,.5)' }};border-radius:50%;top:2px;left:{{ $portfolio->is_public ? '30px' : '2px' }};transition:all .3s"></span>
                    </span>
                </label>
                <div>
                    <div style="font-weight:600;font-size:.9rem">Make portfolio public</div>
                    <div style="font-size:.78rem;color:rgba(245,240,232,.5);margin-top:4px">When enabled, your portfolio appears on the showcase page.</div>
                </div>
            </div>
            <button type="submit" style="padding:.7rem 1.8rem;background:#e84040;color:#fff;border:none;border-radius:999px;font-family:'Space Mono',monospace;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;cursor:pointer"><i class="fas fa-save"></i> Save</button>
        </form>
    </div>

    <div style="background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;padding:30px">
        <h2 style="font-size:.8rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:1.5rem;padding-bottom:12px;border-bottom:1px solid rgba(245,240,232,.1)">Account</h2>
        @foreach([['dashboard.profile.show','fa-user','Edit Profile','Update your name, bio, and photo'],['dashboard.profile.show','fa-lock','Change Password','Update your account password'],['dashboard.customize.show','fa-palette','Customize Portfolio','Personalize your portfolio appearance']] as [$route,$icon,$title,$desc])
        <a href="{{ route($route) }}" style="display:flex;align-items:center;gap:16px;padding:16px;background:rgba(255,255,255,.02);border:1.5px solid rgba(245,240,232,.08);border-radius:.5rem;margin-bottom:10px;transition:all .2s">
            <i class="fas {{ $icon }}" style="font-size:20px;color:#e84040;width:32px;text-align:center"></i>
            <div style="flex:1"><div style="font-weight:700;font-size:.9rem">{{ $title }}</div><div style="font-size:.78rem;color:rgba(245,240,232,.5);margin-top:2px">{{ $desc }}</div></div>
            <i class="fas fa-chevron-right" style="color:rgba(245,240,232,.3)"></i>
        </a>
        @endforeach
    </div>
</div>
@endsection
