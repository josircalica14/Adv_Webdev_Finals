@extends('layouts.dashboard')
@section('title', 'Edit Item')
@section('dashboard-content')
<h1 style="font-family:'DM Serif Display',serif;font-size:2rem;margin-bottom:2rem">Edit Item</h1>
<div style="max-width:700px;background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;padding:30px">
    <form method="POST" action="{{ route('dashboard.items.update', $item) }}">
        @csrf @method('PUT')
        <div style="margin-bottom:1.2rem">
            <label style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:.5rem">Type</label>
            <select name="item_type" style="width:100%;padding:12px 16px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.85rem;font-family:'Instrument Sans',sans-serif">
                @foreach(['project','achievement','milestone','skill'] as $t)
                <option value="{{ $t }}" {{ $item->item_type==$t?'selected':'' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div style="margin-bottom:1.2rem">
            <label style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:.5rem">Title</label>
            <input type="text" name="title" value="{{ old('title',$item->title) }}" maxlength="255" style="width:100%;padding:12px 16px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.85rem;font-family:'Instrument Sans',sans-serif">
        </div>
        <div style="margin-bottom:1.2rem">
            <label style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:.5rem">Description</label>
            <textarea name="description" rows="4" style="width:100%;padding:12px 16px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.85rem;font-family:'Instrument Sans',sans-serif;resize:vertical">{{ old('description',$item->description) }}</textarea>
        </div>
        <div style="margin-bottom:1.2rem">
            <label style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:.5rem">Date</label>
            <input type="date" name="item_date" value="{{ old('item_date',$item->item_date?->format('Y-m-d')) }}" style="width:100%;padding:12px 16px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.85rem;font-family:'Instrument Sans',sans-serif">
        </div>
        <div style="margin-bottom:1.5rem">
            <label style="display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;font-family:'Space Mono',monospace;margin-bottom:.5rem">Tags (comma-separated)</label>
            <input type="text" name="tags_input" value="{{ old('tags_input', implode(', ', $item->tags ?? [])) }}" style="width:100%;padding:12px 16px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:.5rem;color:#f5f0e8;font-size:.85rem;font-family:'Instrument Sans',sans-serif">
        </div>
        <div style="display:flex;gap:12px">
            <button type="submit" style="padding:.7rem 1.8rem;background:#e84040;color:#fff;border:none;border-radius:999px;font-family:'Space Mono',monospace;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;cursor:pointer">Save Changes</button>
            <a href="{{ route('dashboard.index') }}" style="padding:.7rem 1.8rem;background:rgba(255,255,255,.05);color:#f5f0e8;border:1.5px solid rgba(245,240,232,.15);border-radius:999px;font-family:'Space Mono',monospace;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase">Cancel</a>
        </div>
    </form>
</div>
@endsection
