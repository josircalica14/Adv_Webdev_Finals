@extends('layouts.dashboard')
@section('title', 'Edit Item')
@section('dashboard-content')
@php
use Illuminate\Support\Facades\Storage;
$types = [
    'project'     => ['icon' => 'fa-code',         'label' => 'Project'],
    'achievement' => ['icon' => 'fa-trophy',        'label' => 'Achievement'],
    'milestone'   => ['icon' => 'fa-flag',          'label' => 'Milestone'],
    'skill'       => ['icon' => 'fa-bolt',          'label' => 'Skill'],
    'experience'  => ['icon' => 'fa-briefcase',     'label' => 'Experience'],
    'education'   => ['icon' => 'fa-graduation-cap','label' => 'Education'],
];
$selected = old('item_type', $item->item_type);
$images   = $item->files->filter(fn($f) => str_starts_with($f->file_type, 'image/'));
@endphp

<h1 style="font-family:'DM Serif Display',serif;font-size:2rem;margin-bottom:2rem">Edit Item</h1>
<div style="max-width:700px;background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;padding:30px">
    <form method="POST" action="{{ route('dashboard.items.update', $item) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        {{-- Type picker --}}
        <div style="margin-bottom:1.5rem">
            <label class="field-label">Type</label>
            <input type="hidden" name="item_type" id="item_type" value="{{ $selected }}">
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
                @foreach($types as $val => $meta)
                <button type="button"
                    onclick="selectType('{{ $val }}')"
                    id="type-btn-{{ $val }}"
                    class="type-btn {{ $val === $selected ? 'type-btn--on' : '' }}">
                    <i class="fas {{ $meta['icon'] }}" style="font-size:.9rem"></i>
                    <span>{{ $meta['label'] }}</span>
                </button>
                @endforeach
            </div>
        </div>

        <div style="margin-bottom:1.2rem">
            <label class="field-label">Title</label>
            <input type="text" name="title" value="{{ old('title', $item->title) }}" maxlength="255" class="field-input">
        </div>
        <div style="margin-bottom:1.2rem">
            <label class="field-label">Description</label>
            <textarea name="description" rows="4" class="field-input" style="resize:vertical">{{ old('description', $item->description) }}</textarea>
        </div>
        <div style="margin-bottom:1.2rem">
            <label class="field-label">Date <span class="field-hint">(optional)</span></label>
            <input type="date" name="item_date" value="{{ old('item_date', $item->item_date?->format('Y-m-d')) }}" class="field-input">
        </div>
        <div style="margin-bottom:1.2rem">
            <label class="field-label">Tags <span class="field-hint">(comma-separated)</span></label>
            <input type="text" name="tags_input" value="{{ old('tags_input', implode(', ', $item->tags ?? [])) }}" class="field-input">
        </div>

        {{-- Existing images --}}
        @if($images->isNotEmpty())
        <div style="margin-bottom:1.2rem">
            <label class="field-label">Current Images</label>
            <div style="display:flex;flex-wrap:wrap;gap:10px">
                @foreach($images as $file)
                <div style="position:relative">
                    <img src="{{ Storage::disk('portfolio')->url($file->thumbnail_path ?? $file->file_path) }}"
                         alt="{{ $file->original_filename }}"
                         style="width:80px;height:80px;object-fit:cover;border-radius:.5rem;border:1.5px solid rgba(245,240,232,.15)">
                    <form method="POST" action="{{ route('dashboard.files.destroy', $file) }}"
                          style="position:absolute;top:-6px;right:-6px"
                          onsubmit="return confirm('Remove image?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="width:20px;height:20px;border-radius:50%;background:#e84040;border:none;color:#fff;font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center">×</button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div style="margin-bottom:1.8rem">
            <label class="field-label">
                {{ $images->isNotEmpty() ? 'Add Another Image' : 'Image' }}
                <span class="field-hint">(optional, max 10MB)</span>
            </label>
            <label class="file-drop" id="file-drop">
                <input type="file" name="image" accept="image/*" style="display:none" id="image-input" onchange="previewImage(this)">
                <div id="file-placeholder">
                    <i class="fas fa-image" style="font-size:1.4rem;color:rgba(245,240,232,.2);margin-bottom:6px"></i>
                    <span style="font-size:.75rem;color:rgba(245,240,232,.4)">Click to upload or drag & drop</span>
                    <span style="font-size:.65rem;color:rgba(245,240,232,.25);margin-top:2px">JPG, PNG, WEBP, GIF</span>
                </div>
                <img id="image-preview" src="" alt="" style="display:none;max-height:160px;border-radius:.4rem;object-fit:contain">
            </label>
            @error('image')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <div style="display:flex;gap:12px">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('dashboard.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>
</div>

@push('styles')
<style>
.field-label {
    display: block;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    font-family: 'Space Mono', monospace;
    margin-bottom: .5rem;
    color: rgba(245,240,232,.7);
}
.field-hint {
    font-weight: 400;
    text-transform: none;
    letter-spacing: 0;
    opacity: .45;
    font-size: .68rem;
}
.field-input {
    width: 100%;
    padding: 12px 16px;
    background: rgba(255,255,255,.05);
    border: 1.5px solid rgba(245,240,232,.15);
    border-radius: .5rem;
    color: #f5f0e8;
    font-size: .85rem;
    font-family: 'Instrument Sans', sans-serif;
    transition: border-color .2s;
    box-sizing: border-box;
}
.field-input:focus { outline: none; border-color: rgba(232,64,64,.5); }
.field-error { color: #ff5252; font-size: .72rem; margin-top: .4rem; }

.type-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: .75rem .5rem;
    background: rgba(255,255,255,.04);
    border: 1.5px solid rgba(245,240,232,.12);
    border-radius: .6rem;
    color: rgba(245,240,232,.5);
    font-family: 'Space Mono', monospace;
    font-size: .6rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    cursor: pointer;
    transition: all .2s;
}
.type-btn:hover { border-color: rgba(232,64,64,.4); color: #f5f0e8; background: rgba(232,64,64,.08); }
.type-btn--on { background: rgba(232,64,64,.15); border-color: #e84040; color: #e84040; }

.file-drop {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    width: 100%;
    min-height: 110px;
    padding: 20px;
    background: rgba(255,255,255,.03);
    border: 1.5px dashed rgba(245,240,232,.15);
    border-radius: .6rem;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    box-sizing: border-box;
    text-align: center;
}
.file-drop:hover { border-color: rgba(232,64,64,.4); background: rgba(232,64,64,.04); }

.btn-primary {
    padding: .7rem 1.8rem;
    background: #e84040;
    color: #fff;
    border: none;
    border-radius: 999px;
    font-family: 'Space Mono', monospace;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    cursor: pointer;
    transition: opacity .2s;
}
.btn-primary:hover { opacity: .85; }
.btn-ghost {
    padding: .7rem 1.8rem;
    background: rgba(255,255,255,.05);
    color: #f5f0e8;
    border: 1.5px solid rgba(245,240,232,.15);
    border-radius: 999px;
    font-family: 'Space Mono', monospace;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    transition: background .2s;
}
.btn-ghost:hover { background: rgba(255,255,255,.09); }
</style>
@endpush

@push('scripts')
<script>
function selectType(val) {
    document.getElementById('item_type').value = val;
    document.querySelectorAll('.type-btn').forEach(btn => btn.classList.remove('type-btn--on'));
    document.getElementById('type-btn-' + val).classList.add('type-btn--on');
}
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const placeholder = document.getElementById('file-placeholder');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
const drop = document.getElementById('file-drop');
drop.addEventListener('dragover', e => { e.preventDefault(); drop.style.borderColor = '#e84040'; });
drop.addEventListener('dragleave', () => { drop.style.borderColor = ''; });
drop.addEventListener('drop', e => {
    e.preventDefault();
    drop.style.borderColor = '';
    const input = document.getElementById('image-input');
    input.files = e.dataTransfer.files;
    previewImage(input);
});
</script>
@endpush

@endsection
