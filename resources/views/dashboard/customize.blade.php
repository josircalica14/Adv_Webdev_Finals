@extends('layouts.dashboard')
@section('title', 'Customize Portfolio')
@section('dashboard-content')

@php
$fonts      = ['Roboto','Open Sans','Lato','Montserrat','Poppins','Raleway','Ubuntu','Nunito','Playfair Display','Merriweather'];
$allSects   = ['project','experience','education','achievement','milestone','skill'];
$sectLabels = ['project'=>'Projects','experience'=>'Experience','education'=>'Education','achievement'=>'Achievements','milestone'=>'Milestones','skill'=>'Skills'];
$visSects   = $customization->visible_sections ?? $allSects;
$ordSects   = $customization->section_order   ?? $allSects;
$sectDisplay = collect($ordSects)->merge(collect($allSects)->diff($ordSects))->values()->all();
@endphp

<style>
.ctrl-section-title{font-size:.6rem;text-transform:uppercase;letter-spacing:.12em;color:rgba(245,240,232,.35);font-family:'Space Mono',monospace;margin:18px 0 8px}
.ctrl-group{margin-bottom:14px}
.ctrl-label{display:block;font-size:.72rem;color:rgba(245,240,232,.6);margin-bottom:5px;font-family:'Space Mono',monospace}
.ctrl-select{width:100%;padding:7px 10px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.12);border-radius:.4rem;color:#f5f0e8;font-size:.78rem;outline:none}
.cpill{display:inline-block;padding:.25rem .65rem;border-radius:999px;font-size:.68rem;font-family:'Space Mono',monospace;border:1.5px solid rgba(245,240,232,.15);color:rgba(245,240,232,.5);cursor:pointer;transition:all .2s;text-transform:capitalize;white-space:nowrap}
.cpill-on{background:rgba(232,64,64,.15);border-color:#e84040;color:#e84040}
.lpill{display:block;padding:8px 6px;border-radius:.4rem;font-size:.65rem;font-family:'Space Mono',monospace;border:1.5px solid rgba(245,240,232,.12);color:rgba(245,240,232,.5);cursor:pointer;transition:all .2s;text-align:center;text-transform:capitalize}
.lpill-on{background:rgba(232,64,64,.15);border-color:#e84040;color:#e84040}
.toggle-wrap{position:relative;display:inline-block;width:34px;height:18px;flex-shrink:0;cursor:pointer}
.toggle-wrap input{opacity:0;width:0;height:0;position:absolute}
.toggle-track{position:absolute;inset:0;background:rgba(255,255,255,.1);border-radius:999px;transition:background .2s}
.toggle-wrap input:checked ~ .toggle-track{background:#e84040}
.toggle-thumb{position:absolute;top:2px;left:2px;width:14px;height:14px;background:#fff;border-radius:50%;transition:transform .2s}
.toggle-wrap input:checked ~ .toggle-track .toggle-thumb{transform:translateX(16px)}
.toggle-row{display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid rgba(245,240,232,.06)}
.sect-row{display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid rgba(245,240,232,.06);cursor:grab}
.sect-row:last-child,.toggle-row:last-child{border-bottom:none}
.drag-handle{color:rgba(245,240,232,.25);font-size:.75rem;cursor:grab}
.sect-row--ghost{opacity:.4;background:rgba(232,64,64,.08);border-radius:.4rem}
</style>

<h1 style="font-family:'DM Serif Display',serif;font-size:2rem;margin-bottom:1.5rem">Customize Portfolio</h1>

<div style="display:grid;grid-template-columns:360px 1fr;gap:24px;height:calc(100vh - 155px);min-height:500px">

    {{-- CONTROLS --}}
    <div style="display:flex;flex-direction:column;background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;overflow:hidden;position:sticky;top:75px;max-height:calc(100vh - 155px)">
        <div style="flex:1;overflow-y:auto;padding:20px 20px 0">
            <form method="POST" action="{{ route('dashboard.customize.save') }}" id="customize-form">
                @csrf @method('PUT')

                {{-- APPEARANCE --}}
                <p class="ctrl-section-title">Appearance</p>

                <div class="ctrl-group">
                    <label class="ctrl-label">Theme</label>
                    <div style="display:flex;flex-wrap:wrap;gap:5px">
                        @foreach(['default','dark','light','professional','creative'] as $th)
                        <label style="cursor:pointer">
                            <input type="radio" name="theme" value="{{ $th }}" {{ ($customization->theme??'default')==$th?'checked':'' }} style="display:none" class="pt">
                            <span class="cpill {{ ($customization->theme??'default')==$th?'cpill-on':'' }}">{{ $th }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="ctrl-group">
                    <label class="ctrl-label">Layout</label>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px">
                        @foreach(['list','grid','timeline','compact','cards','magazine','sidebar'] as $ly)
                        <label style="cursor:pointer">
                            <input type="radio" name="layout" value="{{ $ly }}" {{ ($customization->layout??'grid')==$ly?'checked':'' }} style="display:none" class="pt">
                            <span class="lpill {{ ($customization->layout??'grid')==$ly?'lpill-on':'' }}">{{ $ly }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="ctrl-group">
                    <label class="ctrl-label">Heading Font</label>
                    <select name="heading_font" class="ctrl-select pt">
                        @foreach($fonts as $f)
                        <option value="{{ $f }}" {{ ($customization->heading_font??'Roboto')==$f?'selected':'' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ctrl-group">
                    <label class="ctrl-label">Body Font</label>
                    <select name="body_font" class="ctrl-select pt">
                        @foreach($fonts as $f)
                        <option value="{{ $f }}" {{ ($customization->body_font??'Open Sans')==$f?'selected':'' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ctrl-group">
                    <label class="ctrl-label">Font Size</label>
                    <div style="display:flex;gap:5px">
                        @foreach(['small','medium','large'] as $fs)
                        <label style="cursor:pointer;flex:1;text-align:center">
                            <input type="radio" name="font_size" value="{{ $fs }}" {{ ($customization->font_size??'medium')==$fs?'checked':'' }} style="display:none" class="pt">
                            <span class="cpill {{ ($customization->font_size??'medium')==$fs?'cpill-on':'' }}" style="width:100%;display:block;text-align:center">{{ $fs }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="ctrl-group">
                    <label class="ctrl-label">Spacing</label>
                    <div style="display:flex;gap:5px">
                        @foreach(['compact','normal','relaxed'] as $sp)
                        <label style="cursor:pointer;flex:1;text-align:center">
                            <input type="radio" name="spacing" value="{{ $sp }}" {{ ($customization->spacing??'normal')==$sp?'checked':'' }} style="display:none" class="pt">
                            <span class="cpill {{ ($customization->spacing??'normal')==$sp?'cpill-on':'' }}" style="width:100%;display:block;text-align:center">{{ $sp }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="ctrl-group">
                    <label class="ctrl-label">Header Style</label>
                    <div style="display:flex;gap:5px;flex-wrap:wrap">
                        @foreach(['classic','centered','minimal','banner'] as $hs)
                        <label style="cursor:pointer">
                            <input type="radio" name="header_style" value="{{ $hs }}" {{ ($customization->header_style??'classic')==$hs?'checked':'' }} style="display:none" class="pt">
                            <span class="cpill {{ ($customization->header_style??'classic')==$hs?'cpill-on':'' }}">{{ $hs }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- COLORS --}}
                <p class="ctrl-section-title">Colors</p>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px">
                    <div class="ctrl-group" style="margin-bottom:0">
                        <label class="ctrl-label">Primary</label>
                        <input type="color" name="primary_color" value="{{ $customization->primary_color??'#3498db' }}" class="pt" style="width:100%;height:36px;border-radius:.4rem;border:1.5px solid rgba(245,240,232,.12);background:none;cursor:pointer">
                    </div>
                    <div class="ctrl-group" style="margin-bottom:0">
                        <label class="ctrl-label">Accent</label>
                        <input type="color" name="accent_color" value="{{ $customization->accent_color??'#e74c3c' }}" class="pt" style="width:100%;height:36px;border-radius:.4rem;border:1.5px solid rgba(245,240,232,.12);background:none;cursor:pointer">
                    </div>
                </div>

                {{-- VISIBILITY --}}
                <p class="ctrl-section-title">Profile Visibility</p>

                @foreach(['show_email'=>'Show Email','show_username'=>'Show Username','show_bio'=>'Show Bio'] as $field=>$flabel)
                <div class="toggle-row">
                    <span style="font-size:.78rem;color:rgba(245,240,232,.7)">{{ $flabel }}</span>
                    <label class="toggle-wrap">
                        <input type="checkbox" name="{{ $field }}" value="1" class="pt toggle-cb" id="{{ $field }}" {{ ($customization->$field??true)?'checked':'' }}>
                        <span class="toggle-track"><span class="toggle-thumb"></span></span>
                    </label>
                </div>
                @endforeach

                {{-- SECTIONS --}}
                <p class="ctrl-section-title">Sections</p>
                <div id="sect-list">
                    @foreach($sectDisplay as $s)
                    <div class="sect-row" data-sect="{{ $s }}">
                        <i class="fas fa-grip-vertical drag-handle"></i>
                        <label class="toggle-wrap" onclick="event.stopPropagation()">
                            <input type="checkbox" name="visible_sections[]" value="{{ $s }}" class="pt toggle-cb sect-cb" {{ in_array($s,$visSects)?'checked':'' }}>
                            <span class="toggle-track"><span class="toggle-thumb"></span></span>
                        </label>
                        <span style="font-size:.78rem;color:rgba(245,240,232,.7)">{{ $sectLabels[$s] }}</span>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="section_order" id="section_order" value="{{ implode(',',$sectDisplay) }}">

            </form>
        </div>

        {{-- SAVE / RESET --}}
        <div style="padding:14px 20px;border-top:1px solid rgba(245,240,232,.08);display:flex;gap:10px;flex-shrink:0">
            <button type="submit" form="customize-form" style="flex:1;padding:.6rem;background:#e84040;color:#fff;border:none;border-radius:.4rem;font-size:.78rem;font-family:'Space Mono',monospace;cursor:pointer;transition:opacity .2s">Save Changes</button>
            <form method="POST" action="{{ route('dashboard.customize.reset') }}" style="flex:1">
                @csrf
                <button type="submit" style="width:100%;padding:.6rem;background:rgba(255,255,255,.05);color:rgba(245,240,232,.6);border:1.5px solid rgba(245,240,232,.12);border-radius:.4rem;font-size:.78rem;font-family:'Space Mono',monospace;cursor:pointer">Reset</button>
            </form>
        </div>
    </div>

    {{-- PREVIEW --}}
    <div style="background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;overflow:hidden;display:flex;flex-direction:column">
        <div style="padding:12px 16px;border-bottom:1px solid rgba(245,240,232,.08);font-size:.7rem;font-family:'Space Mono',monospace;color:rgba(245,240,232,.4);display:flex;align-items:center;gap:8px">
            <i class="fas fa-eye"></i> Live Preview
            <span id="preview-status" style="margin-left:auto;font-size:.6rem;opacity:.5">Watching for changes…</span>
        </div>
        <iframe id="preview-frame"
            style="flex:1;border:none;width:100%;background:#fff"
            title="Portfolio Preview"></iframe>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
// ── Initial preview load (deferred so session cookie is ready) ──
window.addEventListener('load', function() {
    setTimeout(schedulePreview, 100);
});

// ── Pill active state ──
document.querySelectorAll('input[type=radio].pt').forEach(function(inp) {
    inp.addEventListener('change', function() {
        document.querySelectorAll('input[name="'+this.name+'"]').forEach(function(r) {
            var s = r.nextElementSibling;
            if (s) s.classList.remove('cpill-on','lpill-on');
        });
        var s = this.nextElementSibling;
        if (s) s.classList.add(s.classList.contains('lpill') ? 'lpill-on' : 'cpill-on');
        schedulePreview();
    });
});

// ── Attach change listener to every checkbox and select individually ──
document.querySelectorAll('#customize-form input[type=checkbox], #customize-form select, #customize-form input[type=color]').forEach(function(el) {
    el.addEventListener('change', schedulePreview);
});
document.querySelectorAll('#customize-form select, #customize-form input[type=color]').forEach(function(el) {
    el.addEventListener('input', schedulePreview);
});

// ── Debounced preview reload ──
var previewTimer = null;
function schedulePreview() {
    var status = document.getElementById('preview-status');
    status.textContent = 'Updating…';
    clearTimeout(previewTimer);
    previewTimer = setTimeout(function() {
        var form   = document.getElementById('customize-form');
        var data   = new FormData(form);
        var params = new URLSearchParams();
        data.forEach(function(v, k) { params.append(k, v); });

        // Ensure section_order from hidden input is always included (overwrite if already set)
        var sectionOrder = document.getElementById('section_order').value;
        params.set('section_order', sectionOrder);

        var frame  = document.getElementById('preview-frame');
        frame.onload = function() { status.textContent = 'Updated'; };
        frame.src  = '{{ route("dashboard.customize.preview") }}?' + params.toString();
    }, 600);
}

// ── SortableJS drag-to-reorder ──
Sortable.create(document.getElementById('sect-list'), {
    animation: 150,
    ghostClass: 'sect-row--ghost',
    filter: '.toggle-wrap',
    preventOnFilter: false,
    onEnd: function() {
        var order = Array.from(document.querySelectorAll('#sect-list .sect-row'))
            .map(function(r) { return r.dataset.sect; });
        document.getElementById('section_order').value = order.join(',');
        schedulePreview();
    }
});
</script>

@endsection