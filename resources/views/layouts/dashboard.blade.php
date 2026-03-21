@extends('layouts.app')

@section('styles')
<style>
.dash-wrap{display:flex;min-height:100vh}
.dash-sidebar{width:240px;flex-shrink:0;background:rgba(255,255,255,.03);border-right:1px solid rgba(245,240,232,.08);padding:2rem 1.5rem;position:sticky;top:60px;height:calc(100vh - 60px);overflow-y:auto}
.dash-sidebar a{display:flex;align-items:center;gap:10px;padding:.6rem .8rem;border-radius:.5rem;font-size:.78rem;font-weight:600;color:rgba(245,240,232,.7);transition:all .2s;margin-bottom:4px}
.dash-sidebar a:hover,.dash-sidebar a.active{background:rgba(232,64,64,.1);color:#f5f0e8;border-left:2px solid #e84040}
.dash-sidebar a i{color:#e84040;width:16px}
.dash-main{flex:1;padding:2.5rem 3rem;max-width:1200px}
</style>
@yield('dashboard-styles')
@endsection

@section('content')
<div class="dash-wrap">
    <aside class="dash-sidebar">
        <div style="font-family:'Space Mono',monospace;font-size:.6rem;letter-spacing:.15em;text-transform:uppercase;color:rgba(245,240,232,.4);margin-bottom:1.5rem">Menu</div>
        <a href="{{ route('dashboard.index') }}" class="{{ request()->routeIs('dashboard.index') ? 'active' : '' }}"><i class="fas fa-home"></i> Dashboard</a>
        <a href="{{ route('dashboard.items.create') }}" class="{{ request()->routeIs('dashboard.items.create') ? 'active' : '' }}"><i class="fas fa-plus"></i> Add Item</a>
        <a href="{{ route('dashboard.customize.show') }}" class="{{ request()->routeIs('dashboard.customize.*') ? 'active' : '' }}"><i class="fas fa-palette"></i> Customize</a>
        <a href="{{ route('dashboard.profile.show') }}" class="{{ request()->routeIs('dashboard.profile.*') ? 'active' : '' }}"><i class="fas fa-user"></i> Profile</a>
        <a href="{{ route('dashboard.settings.show') }}" class="{{ request()->routeIs('dashboard.settings.*') ? 'active' : '' }}"><i class="fas fa-cog"></i> Settings</a>
        <a href="{{ route('dashboard.export.pdf') }}"><i class="fas fa-download"></i> Export PDF</a>
        <a href="{{ auth()->user()->username ? route('portfolio.public', auth()->user()->username) : route('dashboard.profile.show') }}"><i class="fas fa-external-link-alt"></i> View Public</a>
        @if(auth()->user()->is_admin)
        <hr style="border-color:rgba(245,240,232,.1);margin:1rem 0">
        <a href="{{ route('admin.index') }}"><i class="fas fa-shield-alt"></i> Admin Panel</a>
        @endif
    </aside>
    <div class="dash-main">
        @if(session('status'))
            <div style="background:rgba(76,175,80,.15);color:#4caf50;border:1px solid rgba(76,175,80,.3);padding:12px 16px;border-radius:.5rem;margin-bottom:1.5rem;font-size:.78rem">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div style="background:rgba(232,64,64,.15);color:#ff5252;border:1px solid rgba(232,64,64,.3);padding:12px 16px;border-radius:.5rem;margin-bottom:1.5rem;font-size:.78rem">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif
        @yield('dashboard-content')
    </div>
</div>
@endsection
