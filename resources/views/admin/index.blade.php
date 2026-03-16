@extends('layouts.dashboard')
@section('title', 'Admin Panel')
@section('dashboard-content')
<h1 style="font-family:'DM Serif Display',serif;font-size:2rem;margin-bottom:.5rem">Admin Panel</h1>
<p style="color:rgba(245,240,232,.5);font-size:.85rem;margin-bottom:2rem">All portfolios · <a href="{{ route('admin.flagged') }}" style="color:#e84040">View Flagged Content</a></p>

@foreach($portfolios as $portfolio)
<div style="background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;padding:20px;margin-bottom:12px;display:flex;align-items:center;gap:16px">
    <div style="flex:1">
        <div style="font-weight:700">{{ $portfolio->user->full_name }}</div>
        <div style="font-size:.72rem;color:rgba(245,240,232,.5);font-family:'Space Mono',monospace">{{ $portfolio->user->email }} · {{ $portfolio->user->program }}</div>
        <div style="font-size:.72rem;color:rgba(245,240,232,.4);margin-top:4px">{{ $portfolio->items->count() }} items · {{ $portfolio->is_public ? 'Public' : 'Private' }}</div>
    </div>
    <div style="display:flex;gap:8px">
        @if($portfolio->user->username)
        <a href="{{ route('portfolio.public', $portfolio->user->username) }}" target="_blank" style="padding:.5rem 1rem;background:rgba(255,255,255,.05);color:#f5f0e8;border:1.5px solid rgba(245,240,232,.15);border-radius:999px;font-size:.65rem;font-family:'Space Mono',monospace;text-transform:uppercase">View</a>
        @endif
        <button onclick="document.getElementById('notify-{{ $portfolio->user->id }}').style.display='block'" style="padding:.5rem 1rem;background:rgba(232,64,64,.1);color:#e84040;border:1.5px solid rgba(232,64,64,.3);border-radius:999px;font-size:.65rem;font-family:'Space Mono',monospace;text-transform:uppercase;cursor:pointer">Notify</button>
    </div>
</div>
<div id="notify-{{ $portfolio->user->id }}" style="display:none;background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;padding:20px;margin-bottom:12px">
    <form method="POST" action="{{ route('admin.users.notify', $portfolio->user) }}">
        @csrf
        <input type="text" name="subject" placeholder="Subject" required style="width:100%;padding:10px 14px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.82rem;margin-bottom:8px;font-family:'Instrument Sans',sans-serif">
        <textarea name="message" placeholder="Message" rows="3" required style="width:100%;padding:10px 14px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.82rem;margin-bottom:8px;font-family:'Instrument Sans',sans-serif;resize:vertical"></textarea>
        <button type="submit" style="padding:.5rem 1.2rem;background:#e84040;color:#fff;border:none;border-radius:999px;font-family:'Space Mono',monospace;font-size:.65rem;font-weight:700;text-transform:uppercase;cursor:pointer">Send</button>
    </form>
</div>
@endforeach

<div style="margin-top:2rem">{{ $portfolios->links() }}</div>
@endsection
