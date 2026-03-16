@extends('layouts.dashboard')
@section('title', 'Flagged Content')
@section('dashboard-content')
<h1 style="font-family:'DM Serif Display',serif;font-size:2rem;margin-bottom:1rem">Flagged Content</h1>
<div style="display:flex;gap:8px;margin-bottom:2rem">
    @foreach(['all','pending','reviewed','resolved'] as $s)
    <a href="{{ route('admin.flagged', ['status'=>$s]) }}" style="padding:.4rem 1rem;border-radius:999px;font-family:'Space Mono',monospace;font-size:.65rem;font-weight:700;text-transform:uppercase;{{ $status==$s ? 'background:#e84040;color:#fff' : 'background:rgba(255,255,255,.05);color:rgba(245,240,232,.6);border:1.5px solid rgba(245,240,232,.15)' }}">{{ ucfirst($s) }}</a>
    @endforeach
</div>

@forelse($flagged as $flag)
<div style="background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;padding:20px;margin-bottom:12px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px">
        <div>
            <div style="font-weight:700;margin-bottom:4px">{{ $flag->portfolioItem->title }}</div>
            <div style="font-size:.78rem;color:rgba(245,240,232,.5);margin-bottom:8px">Reason: {{ $flag->reason }}</div>
            <span style="font-size:.62rem;padding:.2rem .6rem;border-radius:999px;font-family:'Space Mono',monospace;text-transform:uppercase;{{ $flag->status=='pending' ? 'background:rgba(255,193,7,.15);color:#ffc107' : ($flag->status=='reviewed' ? 'background:rgba(33,150,243,.15);color:#2196f3' : 'background:rgba(76,175,80,.15);color:#4caf50') }}">{{ $flag->status }}</span>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0">
            <form method="POST" action="{{ route('admin.items.hide', $flag->portfolioItem) }}">@csrf @method('PUT')<button type="submit" style="padding:.4rem .9rem;background:rgba(232,64,64,.1);color:#e84040;border:1.5px solid rgba(232,64,64,.3);border-radius:999px;font-family:'Space Mono',monospace;font-size:.62rem;text-transform:uppercase;cursor:pointer">Hide</button></form>
            <form method="POST" action="{{ route('admin.items.unhide', $flag->portfolioItem) }}">@csrf @method('PUT')<button type="submit" style="padding:.4rem .9rem;background:rgba(76,175,80,.1);color:#4caf50;border:1.5px solid rgba(76,175,80,.3);border-radius:999px;font-family:'Space Mono',monospace;font-size:.62rem;text-transform:uppercase;cursor:pointer">Unhide</button></form>
        </div>
    </div>
</div>
@empty
<div style="text-align:center;padding:60px;color:rgba(245,240,232,.4)">No flagged content.</div>
@endforelse
@endsection
