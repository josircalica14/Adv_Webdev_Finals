@extends('layouts.dashboard')
@section('title', 'Dashboard')
@section('dashboard-content')
<div style="margin-bottom:2rem">
    <h1 style="font-family:'DM Serif Display',serif;font-size:2rem;margin-bottom:.3rem">Welcome, {{ $user->full_name }}</h1>
    <p style="color:rgba(245,240,232,.5);font-size:.85rem">{{ $user->program }} · {{ $user->email }}</p>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:2.5rem">
    @foreach([['fa-layer-group',$stats['total_items'],'Total Items'],['fa-code',$stats['projects'],'Projects'],['fa-trophy',$stats['achievements'],'Achievements'],['fa-eye',$stats['view_count'],'Profile Views']] as [$icon,$val,$label])
    <div style="background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;padding:25px;text-align:center">
        <i class="fas {{ $icon }}" style="font-size:28px;color:#e84040;margin-bottom:12px"></i>
        <div style="font-size:2rem;font-weight:700;font-family:'Space Mono',monospace">{{ $val }}</div>
        <div style="font-size:.62rem;color:rgba(245,240,232,.5);text-transform:uppercase;letter-spacing:.1em;font-family:'Space Mono',monospace">{{ $label }}</div>
    </div>
    @endforeach
</div>

<div style="display:flex;gap:12px;margin-bottom:2.5rem;flex-wrap:wrap">
    <a href="{{ route('dashboard.items.create') }}" style="display:inline-flex;align-items:center;gap:8px;padding:.7rem 1.8rem;background:#e84040;color:#fff;border-radius:999px;font-family:'Space Mono',monospace;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase"><i class="fas fa-plus"></i> Add Item</a>
    <a href="{{ route('dashboard.customize.show') }}" style="display:inline-flex;align-items:center;gap:8px;padding:.7rem 1.8rem;background:rgba(255,255,255,.05);color:#f5f0e8;border:1.5px solid rgba(245,240,232,.15);border-radius:999px;font-family:'Space Mono',monospace;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase"><i class="fas fa-palette"></i> Customize</a>
    <a href="{{ route('dashboard.export.pdf') }}" style="display:inline-flex;align-items:center;gap:8px;padding:.7rem 1.8rem;background:rgba(255,255,255,.05);color:#f5f0e8;border:1.5px solid rgba(245,240,232,.15);border-radius:999px;font-family:'Space Mono',monospace;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase"><i class="fas fa-download"></i> Export PDF</a>
</div>

<h2 style="font-size:1.3rem;font-weight:700;margin-bottom:1.2rem;font-family:'DM Serif Display',serif">Portfolio Items</h2>
@forelse($portfolio->items as $item)
<div class="item-row" style="display:flex;gap:16px;padding:20px;background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;margin-bottom:12px;align-items:flex-start;transition:border-color .2s,background .2s,transform .2s">
    <div style="flex-shrink:0;width:44px;height:44px;background:rgba(232,64,64,.15);border-radius:50%;display:flex;align-items:center;justify-content:center"><i class="fas fa-code" style="color:#e84040"></i></div>
    <div style="flex:1">
        <div style="font-weight:700;margin-bottom:4px">{{ $item->title }}</div>
        <div style="font-size:.78rem;color:rgba(245,240,232,.6);margin-bottom:8px">{{ Str::limit($item->description, 100) }}</div>
        <span style="font-size:.6rem;background:rgba(232,64,64,.15);color:#e84040;padding:.2rem .6rem;border-radius:999px;font-family:'Space Mono',monospace;text-transform:uppercase">{{ $item->item_type }}</span>
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('dashboard.items.edit', $item) }}" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:50%;color:#f5f0e8"><i class="fas fa-edit" style="font-size:12px"></i></a>
        <form method="POST" action="{{ route('dashboard.items.destroy', $item) }}" onsubmit="return confirm('Delete this item?')">@csrf @method('DELETE')<button type="submit" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:rgba(232,64,64,.1);border:1.5px solid rgba(232,64,64,.3);border-radius:50%;color:#e84040;cursor:pointer"><i class="fas fa-trash" style="font-size:12px"></i></button></form>
    </div>
</div>
@empty
<div style="text-align:center;padding:60px 20px;background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem">
    <i class="fas fa-folder-open" style="font-size:48px;color:rgba(232,64,64,.4);margin-bottom:16px"></i>
    <p style="color:rgba(245,240,232,.5)">No items yet. <a href="{{ route('dashboard.items.create') }}" style="color:#e84040">Add your first item</a></p>
</div>
@endforelse
@endsection

@push('styles')
<style>
    .item-row:hover {
        border-color: rgba(245,240,232,.22) !important;
        background: rgba(255,255,255,.055) !important;
        transform: translateX(3px);
    }
    /* Stat cards */
    div[style*="text-align:center"][style*="border-radius:.75rem"] {
        transition: border-color .25s, background .25s, transform .25s, box-shadow .25s;
    }
    div[style*="text-align:center"][style*="border-radius:.75rem"]:hover {
        border-color: rgba(232,64,64,.3) !important;
        background: rgba(255,255,255,.055) !important;
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0,0,0,.25);
    }
</style>
@endpush
