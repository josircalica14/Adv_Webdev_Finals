@extends('layouts.dashboard')
@section('title', 'Customize Portfolio')
@section('dashboard-content')

<h1 style="font-family:'DM Serif Display',serif;font-size:2rem;margin-bottom:2rem">Customize Portfolio</h1>

<div style="display:grid;grid-template-columns:340px 1fr;gap:28px;align-items:start">

    {{-- ── CONTROLS ── --}}
    <div style="background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;padding:28px;position:sticky;top:100px">
        <form method="POST" action="{{ route('dashboard.customize.save') }}" id="customize-form">
            @csrf @method('PUT')
            @php $fonts = ['Roboto','Open Sans','Lato','Montserrat','Poppins','Raleway','Ubuntu','Nunito','Playfair Display','Merriweather']; @endphp

            {{-- Theme --}}
            <div style="margin-bottom:1.2rem">
                <label class="ctrl-label">Theme</label>
                <div style="display:flex;flex-wrap:wrap;gap:6px" id="theme-pills">
                    @foreach(['default','dark','light','professional','creative'] as $t)
                    <label style="cursor:pointer">
                        <input type="radio" name="theme" value="{{ $t }}" {{ $customization->theme==$t?'checked':'' }} style="display:none" class="preview-trigger">
                        <span class="theme-pill {{ $customization->theme==$t ? 'pill-on' : '' }}">{{ ucfirst($t) }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Layout --}}
            <div style="margin-bottom:1.2rem">
                <label class="ctrl-label">Layout</label>
                <div style="display:flex;gap:8px">
                    @foreach(['grid' => '⊞ Grid', 'list' => '☰ List', 'timeline' => '⏱ Timeline'] as $l => $lbl)
                    <label style="cursor:pointer;flex:1">
                        <input type="radio" name="layout" value="{{ $l }}" {{ $customization->layout==$l?'checked':'' }} style="display:none" class="preview-trigger">
                        <span class="layout-pill {{ $customization->layout==$l ? 'pill-on' : '' }}" style="display:block;text-align:center;padding:.45rem .5rem;border-radius:.4rem;font-family:'Space Mono',monospace;font-size:.6rem;letter-spacing:.06em;border:1.5px solid rgba(245,240,232,.15);transition:all .2s">{{ $lbl }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Colors --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:1.2rem">
                <div>
                    <label class="ctrl-label">Primary</label>
                    <div style="display:flex;align-items:center;gap:8px">
                        <input type="color" name="primary_color" id="primary_color" value="{{ $customization->primary_color }}" class="preview-trigger color-swatch">
                        <span id="primary_hex" style="font-family:'Space Mono',monospace;font-size:.65rem;opacity:.6">{{ $customization->primary_color }}</span>
                    </div>
                </div>
                <div>
                    <label class="ctrl-label">Accent</label>
                    <div style="display:flex;align-items:center;gap:8px">
                        <input type="color" name="accent_color" id="accent_color" value="{{ $customization->accent_color }}" class="preview-trigger color-swatch">
                        <span id="accent_hex" style="font-family:'Space Mono',monospace;font-size:.65rem;opacity:.6">{{ $customization->accent_color }}</span>
                    </div>
                </div>
            </div>

            {{-- Fonts --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:1.6rem">
                <div>
                    <label class="ctrl-label">Heading Font</label>
                    <select name="heading_font" class="ctrl-select preview-trigger">
                        @foreach($fonts as $f)<option value="{{ $f }}" {{ $customization->heading_font==$f?'selected':'' }}>{{ $f }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="ctrl-label">Body Font</label>
                    <select name="body_font" class="ctrl-select preview-trigger">
                        @foreach($fonts as $f)<option value="{{ $f }}" {{ $customization->body_font==$f?'selected':'' }}>{{ $f }}</option>@endforeach
                    </select>
                </div>
            </div>

            <div style="display:flex;gap:10px">
                <button type="submit" style="flex:1;padding:.7rem;background:#e84040;color:#fff;border:none;border-radius:999px;font-family:'Space Mono',monospace;font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;cursor:pointer">Save</button>
                <form method="POST" action="{{ route('dashboard.customize.reset') }}" style="flex:1">@csrf
                    <button type="submit" style="width:100%;padding:.7rem;background:rgba(255,255,255,.05);color:#f5f0e8;border:1.5px solid rgba(245,240,232,.15);border-radius:999px;font-family:'Space Mono',monospace;font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;cursor:pointer">Reset</button>
                </form>
            </div>
        </form>
    </div>

    {{-- ── LIVE PREVIEW ── --}}
    <div style="border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;overflow:hidden;min-height:500px;display:flex;flex-direction:column">
        <div style="padding:10px 16px;background:rgba(255,255,255,.03);border-bottom:1px solid rgba(245,240,232,.08);display:flex;align-items:center;gap:6px">
            <span style="width:10px;height:10px;border-radius:50%;background:#e84040;display:inline-block"></span>
            <span style="width:10px;height:10px;border-radius:50%;background:rgba(245,240,232,.2);display:inline-block"></span>
            <span style="width:10px;height:10px;border-radius:50%;background:rgba(245,240,232,.2);display:inline-block"></span>
            <span style="font-family:'Space Mono',monospace;font-size:.6rem;opacity:.35;margin-left:8px">Live Preview</span>
        </div>
        <div id="preview-pane" style="padding:32px 28px;transition:background .3s,color .3s;flex:1">
            {{-- header --}}
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;padding-bottom:20px;border-bottom:2px solid" id="preview-divider">
                <div style="width:52px;height:52px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.3rem" id="preview-avatar">👤</div>
                <div>
                    <div id="preview-name" style="font-size:1.2rem;font-weight:700;margin-bottom:2px">{{ auth()->user()->full_name }}</div>
                    <div style="font-size:.72rem;opacity:.5;font-family:'Space Mono',monospace">{{ auth()->user()->program }} · {{ auth()->user()->username }}</div>
                </div>
            </div>
            {{-- section title --}}
            <div id="preview-section-title" style="font-size:1rem;font-weight:700;margin-bottom:14px;letter-spacing:.02em">
                {{ $items->isNotEmpty() ? ucfirst($items->first()->item_type) . 's' : 'Projects' }}
            </div>
            {{-- cards — real items, fallback to placeholders if empty --}}
            <div id="preview-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                @forelse($items->take(3) as $item)
                <div class="preview-card" style="padding:14px;border-radius:.5rem;border:1.5px solid rgba(245,240,232,.1)">
                    <div class="preview-card-title" style="font-size:.82rem;font-weight:700;margin-bottom:4px">{{ $item->title }}</div>
                    <div style="font-size:.7rem;opacity:.5;line-height:1.5">{{ Str::limit($item->description, 80) }}</div>
                    @if($item->tags)
                    <div style="margin-top:8px;display:flex;gap:4px;flex-wrap:wrap">
                        @foreach(array_slice((array)$item->tags, 0, 2) as $tag)
                        <span class="preview-tag" style="font-size:.6rem;padding:.15rem .5rem;border-radius:999px">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
                @empty
                @foreach(['Portfolio Website','Mobile App','API Service'] as $title)
                <div class="preview-card" style="padding:14px;border-radius:.5rem;border:1.5px solid rgba(245,240,232,.1)">
                    <div class="preview-card-title" style="font-size:.82rem;font-weight:700;margin-bottom:4px">{{ $title }}</div>
                    <div style="font-size:.7rem;opacity:.5;line-height:1.5">A sample project description that shows how your content will look.</div>
                    <div style="margin-top:8px;display:flex;gap:4px">
                        <span class="preview-tag" style="font-size:.6rem;padding:.15rem .5rem;border-radius:999px">PHP</span>
                        <span class="preview-tag" style="font-size:.6rem;padding:.15rem .5rem;border-radius:999px">Laravel</span>
                    </div>
                </div>
                @endforeach
                @endforelse
            </div>
        </div>
    </div>

</div>

@push('styles')
{{-- Pre-load currently selected fonts so they're ready on page load --}}
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family={{ urlencode($customization->heading_font) }}:wght@400;700&family={{ urlencode($customization->body_font) }}:wght@400;700&display=swap">
<style>
.ctrl-label {
    display: block;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    font-family: 'Space Mono', monospace;
    margin-bottom: .45rem;
    opacity: .7;
}
.ctrl-select {
    width: 100%;
    padding: 9px 12px;
    background: rgba(255,255,255,.05);
    border: 1.5px solid rgba(245,240,232,.15);
    border-radius: .4rem;
    color: #f5f0e8;
    font-size: .8rem;
    font-family: 'Instrument Sans', sans-serif;
}
.color-swatch {
    width: 36px;
    height: 36px;
    padding: 2px;
    border-radius: .4rem;
    border: 1.5px solid rgba(245,240,232,.2);
    background: none;
    cursor: pointer;
}
.theme-pill, .layout-pill {
    display: inline-block;
    padding: .3rem .8rem;
    border-radius: 999px;
    font-family: 'Space Mono', monospace;
    font-size: .6rem;
    font-weight: 700;
    letter-spacing: .06em;
    border: 1.5px solid rgba(245,240,232,.2);
    color: rgba(245,240,232,.6);
    transition: all .2s;
    cursor: pointer;
}
.pill-on {
    background: #e84040;
    color: #fff;
    border-color: #e84040;
}
#preview-pane * {
    box-sizing: border-box;
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    const form     = document.getElementById('customize-form');
    const pane     = document.getElementById('preview-pane');
    const grid     = document.getElementById('preview-grid');
    const divider  = document.getElementById('preview-divider');
    const avatar   = document.getElementById('preview-avatar');
    const secTitle = document.getElementById('preview-section-title');

    const loadedFonts = new Set();
    function loadFont(name) {
        if (loadedFonts.has(name)) return Promise.resolve();
        loadedFonts.add(name);
        return new Promise(resolve => {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = `https://fonts.googleapis.com/css2?family=${encodeURIComponent(name)}:wght@400;700&display=swap`;
            link.onload = resolve;
            link.onerror = resolve;
            document.head.appendChild(link);
        });
    }

    const themeMap = {
        default:      { bg: '#181818', text: '#f5f0e8', card: 'rgba(255,255,255,.04)', sub: 'rgba(245,240,232,.6)' },
        dark:         { bg: '#0d0d0d', text: '#e0e0e0', card: 'rgba(255,255,255,.06)', sub: 'rgba(224,224,224,.6)' },
        light:        { bg: '#f5f0e8', text: '#181818', card: '#fff',                  sub: 'rgba(24,24,24,.6)'    },
        professional: { bg: '#1a1f2e', text: '#e8eaf0', card: 'rgba(255,255,255,.05)', sub: 'rgba(232,234,240,.6)' },
        creative:     { bg: '#1a0a1a', text: '#f0e8f5', card: 'rgba(255,255,255,.05)', sub: 'rgba(240,232,245,.6)' },
    };

    function getVal(name) {
        const checked = form.querySelector(`input[name="${name}"]:checked`);
        if (checked) return checked.value;
        const el = form.elements[name];
        return el ? el.value : '';
    }

    async function applyPreview() {
        const theme   = getVal('theme');
        const layout  = getVal('layout');
        const primary = getVal('primary_color');
        const accent  = getVal('accent_color');
        const hFont   = getVal('heading_font');
        const bFont   = getVal('body_font');

        // Load fonts and wait for them
        await Promise.all([loadFont(hFont), loadFont(bFont)]);
        await document.fonts.ready;

        const t = themeMap[theme] || themeMap.default;
        const bFontStack = `'${bFont}', sans-serif`;
        const hFontStack = `'${hFont}', serif`;

        // Pane base styles — set font on the container so all children inherit
        pane.style.cssText += `background:${t.bg};color:${t.text};font-family:${bFontStack};`;

        // Divider + avatar
        divider.style.borderColor = primary;
        avatar.style.background   = primary + '33';
        avatar.style.color        = primary;

        // Name (heading font)
        const nameEl = document.getElementById('preview-name');
        nameEl.style.fontFamily = hFontStack;
        nameEl.style.color      = t.text;

        // Program line
        const progEl = divider.querySelector('div:last-child > div:last-child');
        if (progEl) progEl.style.color = t.sub;

        // Section title
        secTitle.style.fontFamily = hFontStack;
        secTitle.style.color      = primary;

        // Cards
        document.querySelectorAll('.preview-card').forEach(c => {
            c.style.background  = t.card;
            c.style.borderColor = primary + '44';
            c.style.fontFamily  = bFontStack;
            c.style.color       = t.text;

            const titleEl = c.querySelector('.preview-card-title');
            if (titleEl) {
                titleEl.style.fontFamily = hFontStack;
                titleEl.style.color      = t.text;
            }
            // Description text
            const descEl = c.querySelector('div:not(.preview-card-title):not([style*="gap"])');
            if (descEl) {
                descEl.style.fontFamily = bFontStack;
                descEl.style.color      = t.sub;
            }
        });

        // Tags
        document.querySelectorAll('.preview-tag').forEach(tag => {
            tag.style.background = accent + '22';
            tag.style.color      = accent;
        });

        // Layout
        if (layout === 'list') {
            grid.style.gridTemplateColumns = '1fr';
            grid.style.borderLeft  = 'none';
            grid.style.paddingLeft = '0';
        } else if (layout === 'timeline') {
            grid.style.gridTemplateColumns = '1fr';
            grid.style.borderLeft  = `2px solid ${primary}`;
            grid.style.paddingLeft = '16px';
        } else {
            grid.style.gridTemplateColumns = '1fr 1fr';
            grid.style.borderLeft  = 'none';
            grid.style.paddingLeft = '0';
        }

        // Hex labels
        document.getElementById('primary_hex').textContent = primary;
        document.getElementById('accent_hex').textContent  = accent;

        // Pill active states
        document.querySelectorAll('.theme-pill').forEach(p => {
            const radio = p.closest('label')?.querySelector('input');
            p.classList.toggle('pill-on', radio?.checked ?? false);
        });
        document.querySelectorAll('.layout-pill').forEach(p => {
            const radio = p.closest('label')?.querySelector('input');
            p.classList.toggle('pill-on', radio?.checked ?? false);
        });
    }

    // Bind all controls
    form.querySelectorAll('.preview-trigger').forEach(el => {
        el.addEventListener('input',  applyPreview);
        el.addEventListener('change', applyPreview);
    });

    // Initial render after fonts are ready
    document.fonts.ready.then(applyPreview);
})();
</script>
@endpush

@endsection
