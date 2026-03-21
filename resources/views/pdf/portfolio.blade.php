<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
@php
    $primary     = $customization->primary_color    ?? '#3498db';
    $accent      = $customization->accent_color     ?? '#e74c3c';
    $theme       = $customization->theme            ?? 'default';
    $layout      = $customization->layout           ?? 'grid';
    $hFont       = $customization->heading_font     ?? 'Roboto';
    $bFont       = $customization->body_font        ?? 'Open Sans';
    $fontSize    = $customization->font_size        ?? 'medium';
    $spacing     = $customization->spacing          ?? 'normal';
    $headerStyle = $customization->header_style     ?? 'dark';
    $showEmail   = $customization->show_email       ?? true;
    $showUser    = $customization->show_username    ?? true;
    $showBio     = $customization->show_bio         ?? true;

    $allSections   = ['project','experience','education','achievement','milestone','skill'];
    $sectionLabels = ['project'=>'Projects','experience'=>'Experience','education'=>'Education','achievement'=>'Achievements','milestone'=>'Milestones','skill'=>'Skills'];

    $visibleSections = $customization->visible_sections ?? $allSections;
    $sectionOrder    = $customization->section_order    ?? $allSections;
    $orderedSections = collect($sectionOrder)->filter(fn($s) => in_array($s, $visibleSections))->values()->all();
    if (empty($orderedSections)) $orderedSections = $allSections;

    $themes = [
        'default'      => ['bg'=>'#ffffff','surface'=>'#f8f8f8','text'=>'#1a1a1a','sub'=>'#555','border'=>'#e0e0e0'],
        'dark'         => ['bg'=>'#141414','surface'=>'#1e1e1e','text'=>'#e0e0e0','sub'=>'#999','border'=>'#2e2e2e'],
        'light'        => ['bg'=>'#fafaf7','surface'=>'#ffffff', 'text'=>'#1a1a1a','sub'=>'#555','border'=>'#ddd8ce'],
        'professional' => ['bg'=>'#ffffff','surface'=>'#f4f6fb','text'=>'#1a1f2e','sub'=>'#4a5568','border'=>'#d0d5e8'],
        'creative'     => ['bg'=>'#ffffff','surface'=>'#fdf6ff','text'=>'#1a0a1a','sub'=>'#5a3a6a','border'=>'#e0cce8'],
    ];
    $t = $themes[$theme] ?? $themes['default'];

    $headerBgs = [
        'dark'    => ['bg'=>'#1a1a1a',    'text'=>'#ffffff'],
        'primary' => ['bg'=>$primary,     'text'=>'#ffffff'],
        'accent'  => ['bg'=>$accent,      'text'=>'#ffffff'],
        'light'   => ['bg'=>'#f5f0e8',    'text'=>'#1a1a1a'],
        'minimal' => ['bg'=>'transparent','text'=>$t['text']],
    ];
    $hdr = $headerBgs[$headerStyle] ?? $headerBgs['dark'];

    $fontScales = [
        'small'  => ['base'=>10,'name'=>22,'section'=>10,'item'=>11,'meta'=>9, 'tag'=>8.5],
        'medium' => ['base'=>12,'name'=>26,'section'=>11,'item'=>13,'meta'=>11,'tag'=>9.5],
        'large'  => ['base'=>14,'name'=>30,'section'=>13,'item'=>15,'meta'=>13,'tag'=>11],
    ];
    $fs = $fontScales[$fontSize] ?? $fontScales['medium'];

    $spacingScales = [
        'compact'  => ['section_pad'=>'12px 40px 0','item_pad'=>'8px 12px', 'item_mb'=>'6px', 'header_pad'=>'20px 40px 16px'],
        'normal'   => ['section_pad'=>'22px 40px 0','item_pad'=>'13px 16px','item_mb'=>'10px','header_pad'=>'32px 40px 24px'],
        'relaxed'  => ['section_pad'=>'32px 40px 0','item_pad'=>'18px 20px','item_mb'=>'16px','header_pad'=>'44px 40px 32px'],
    ];
    $sp = $spacingScales[$spacing] ?? $spacingScales['normal'];

    $fontMap = [
        'Roboto'=>'DejaVu Sans,Arial,sans-serif','Open Sans'=>'DejaVu Sans,Arial,sans-serif',
        'Lato'=>'DejaVu Sans,Arial,sans-serif','Montserrat'=>'DejaVu Sans,Arial,sans-serif',
        'Poppins'=>'DejaVu Sans,Arial,sans-serif','Raleway'=>'DejaVu Sans,Arial,sans-serif',
        'Ubuntu'=>'DejaVu Sans,Arial,sans-serif','Nunito'=>'DejaVu Sans,Arial,sans-serif',
        'Playfair Display'=>'DejaVu Serif,Georgia,serif','Merriweather'=>'DejaVu Serif,Georgia,serif',
    ];
    $hStack = $fontMap[$hFont] ?? 'DejaVu Sans,Arial,sans-serif';
    $bStack = $fontMap[$bFont] ?? 'DejaVu Sans,Arial,sans-serif';

    if (!function_exists('hexRgba')) { function hexRgba(string $hex, float $a): string {
        $hex = ltrim($hex,'#');
        if (strlen($hex)===3) $hex=$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        return 'rgba('.hexdec(substr($hex,0,2)).','.hexdec(substr($hex,2,2)).','.hexdec(substr($hex,4,2)).','.round($a,2).')';
    }}

    // Sidebar layout needs all items flattened for the right column
    $isSidebar = $layout === 'sidebar';
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family={{ urlencode($hFont) }}:wght@400;700&family={{ urlencode($bFont) }}:wght@400;700&display=swap" rel="stylesheet">

<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'{{ $bFont }}',{{ $bStack }}; background:{{ $t['bg'] }}; color:{{ $t['text'] }}; font-size:{{ $fs['base'] }}px; line-height:1.55; }

/* ── HEADER ── */
@if($headerStyle === 'classic')
/* Classic: dark left-aligned with accent underline */
.header { background:#1a1a1a; color:#fff; padding:{{ $sp['header_pad'] }}; }
.header__name { font-family:'{{ $hFont }}',{{ $hStack }}; font-size:{{ $fs['name'] }}px; font-weight:700; color:#fff; margin-bottom:4px; }
.header__accent-line { width:48px; height:3px; background:{{ $accent }}; margin:8px 0 10px; border-radius:2px; }
.header__meta { font-size:{{ $fs['meta'] }}px; color:rgba(255,255,255,.6); margin-bottom:3px; }
.header__bio  { font-size:{{ $fs['meta'] }}px; color:rgba(255,255,255,.75); margin-top:8px; line-height:1.6; max-width:520px; }
.header__bar  { height:4px; background:linear-gradient(90deg,{{ $accent }},{{ $primary }}); }

@elseif($headerStyle === 'centered')
/* Centered: gradient bg, everything centered, large name */
.header { background:linear-gradient(135deg,{{ $primary }},{{ $accent }}); color:#fff; padding:{{ $sp['header_pad'] }}; text-align:center; }
.header__name { font-family:'{{ $hFont }}',{{ $hStack }}; font-size:{{ $fs['name'] + 4 }}px; font-weight:700; color:#fff; margin-bottom:6px; letter-spacing:.02em; }
.header__accent-line { width:60px; height:3px; background:rgba(255,255,255,.5); margin:8px auto 10px; border-radius:2px; }
.header__meta { font-size:{{ $fs['meta'] }}px; color:rgba(255,255,255,.75); margin-bottom:3px; }
.header__bio  { font-size:{{ $fs['meta'] }}px; color:rgba(255,255,255,.85); margin-top:8px; line-height:1.6; max-width:480px; margin-left:auto; margin-right:auto; }
.header__bar  { height:0; }

@elseif($headerStyle === 'minimal')
/* Minimal: white bg, left colored border, no bar */
.header { background:{{ $t['bg'] }}; color:{{ $t['text'] }}; padding:{{ $sp['header_pad'] }}; border-left:5px solid {{ $accent }}; padding-left:24px; }
.header__name { font-family:'{{ $hFont }}',{{ $hStack }}; font-size:{{ $fs['name'] }}px; font-weight:700; color:{{ $primary }}; margin-bottom:4px; }
.header__accent-line { display:none; }
.header__meta { font-size:{{ $fs['meta'] }}px; color:{{ $t['sub'] }}; margin-bottom:3px; }
.header__bio  { font-size:{{ $fs['meta'] }}px; color:{{ $t['sub'] }}; margin-top:6px; line-height:1.6; max-width:520px; }
.header__bar  { height:1px; background:{{ $t['border'] }}; }

@elseif($headerStyle === 'banner')
/* Banner: two-column — color block left with initials, info right */
.header { background:{{ $t['bg'] }}; color:{{ $t['text'] }}; padding:0; display:flex; min-height:90px; }
.header__left { background:{{ $primary }}; width:90px; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.header__initials { font-family:'{{ $hFont }}',{{ $hStack }}; font-size:{{ $fs['name'] - 4 }}px; font-weight:700; color:#fff; }
.header__right { flex:1; padding:{{ $sp['header_pad'] }}; border-bottom:3px solid {{ $accent }}; }
.header__name { font-family:'{{ $hFont }}',{{ $hStack }}; font-size:{{ $fs['name'] }}px; font-weight:700; color:{{ $t['text'] }}; margin-bottom:4px; }
.header__accent-line { display:none; }
.header__meta { font-size:{{ $fs['meta'] }}px; color:{{ $t['sub'] }}; margin-bottom:3px; }
.header__bio  { font-size:{{ $fs['meta'] }}px; color:{{ $t['sub'] }}; margin-top:6px; line-height:1.6; }
.header__bar  { height:0; }

@else
/* Fallback = classic */
.header { background:#1a1a1a; color:#fff; padding:{{ $sp['header_pad'] }}; }
.header__name { font-family:'{{ $hFont }}',{{ $hStack }}; font-size:{{ $fs['name'] }}px; font-weight:700; color:#fff; margin-bottom:4px; }
.header__accent-line { width:48px; height:3px; background:{{ $accent }}; margin:8px 0 10px; border-radius:2px; }
.header__meta { font-size:{{ $fs['meta'] }}px; color:rgba(255,255,255,.6); margin-bottom:3px; }
.header__bio  { font-size:{{ $fs['meta'] }}px; color:rgba(255,255,255,.75); margin-top:8px; line-height:1.6; max-width:520px; }
.header__bar  { height:4px; background:linear-gradient(90deg,{{ $accent }},{{ $primary }}); }
@endif

/* ── SECTION TITLE (shared) ── */
.section-title {
    font-family:'{{ $hFont }}',{{ $hStack }};
    font-size:{{ $fs['section'] }}px; font-weight:700;
    text-transform:uppercase; letter-spacing:.12em;
    color:{{ $primary }};
    border-bottom:1.5px solid {{ hexRgba($primary,0.22) }};
    padding-bottom:5px; margin-bottom:10px;
}

/* ── ITEM BASE ── */
.item__title { font-family:'{{ $hFont }}',{{ $hStack }}; font-size:{{ $fs['item'] }}px; font-weight:700; color:{{ $t['text'] }}; }
.item__date  { font-size:{{ $fs['tag'] }}px; color:{{ $t['sub'] }}; white-space:nowrap; margin-left:8px; }
.item__desc  { font-size:{{ $fs['base'] }}px; line-height:1.65; color:{{ $t['sub'] }}; margin-bottom:6px; }
.item__tags  { display:flex; flex-wrap:wrap; gap:3px; }
.item__tag   { font-size:{{ $fs['tag'] }}px; background:{{ hexRgba($accent,0.1) }}; color:{{ $accent }}; padding:2px 7px; border-radius:999px; border:1px solid {{ hexRgba($accent,0.3) }}; }
.item__header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:4px; }

/* ── TYPE OVERRIDES ── */
.item--milestone .item__title { color:{{ $primary }}; }
.item--achievement .item__title { color:#a07800; }

/* ════════════════════════════════════════
   LAYOUT: LIST  — full-width stacked rows
   ════════════════════════════════════════ */
@if($layout === 'list')
.section { padding:{{ $sp['section_pad'] }}; }
.items-wrap { }
.item {
    margin-bottom:{{ $sp['item_mb'] }};
    padding:{{ $sp['item_pad'] }};
    border:1px solid {{ $t['border'] }};
    border-left:3px solid {{ $accent }};
    border-radius:4px;
    background:{{ $t['surface'] }};
}
.item--milestone { border-left-color:{{ $primary }}; background:{{ hexRgba($primary,0.05) }}; }
.item--achievement { border-left-color:#d4a017; background:{{ hexRgba('#d4a017',0.05) }}; }

/* ════════════════════════════════════════
   LAYOUT: GRID  — 2-column cards
   ════════════════════════════════════════ */
@elseif($layout === 'grid')
.section { padding:{{ $sp['section_pad'] }}; }
.items-wrap { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.item {
    padding:{{ $sp['item_pad'] }};
    border:1px solid {{ $t['border'] }};
    border-top:3px solid {{ $accent }};
    border-radius:4px;
    background:{{ $t['surface'] }};
}
.item--milestone { border-top-color:{{ $primary }}; background:{{ hexRgba($primary,0.05) }}; }
.item--achievement { border-top-color:#d4a017; background:{{ hexRgba('#d4a017',0.05) }}; }

/* ════════════════════════════════════════
   LAYOUT: TIMELINE  — left rail with dots
   ════════════════════════════════════════ */
@elseif($layout === 'timeline')
.section { padding:{{ $sp['section_pad'] }}; }
.items-wrap { border-left:2px solid {{ hexRgba($primary,0.3) }}; padding-left:20px; margin-left:10px; }
.item {
    position:relative;
    margin-bottom:{{ $sp['item_mb'] }};
    padding:{{ $sp['item_pad'] }};
    border:1px solid {{ $t['border'] }};
    border-radius:4px;
    background:{{ $t['surface'] }};
}
.item::before { content:''; position:absolute; left:-26px; top:14px; width:8px; height:8px; border-radius:50%; background:{{ $accent }}; border:2px solid {{ $t['bg'] }}; }
.item--milestone::before { background:{{ $primary }}; }
.item--achievement::before { background:#d4a017; }

/* ════════════════════════════════════════
   LAYOUT: COMPACT  — minimal, divider-only
   ════════════════════════════════════════ */
@elseif($layout === 'compact')
.section { padding:{{ $sp['section_pad'] }}; }
.items-wrap { }
.item {
    margin-bottom:0;
    padding:8px 0;
    border:none;
    border-bottom:1px solid {{ $t['border'] }};
    background:transparent;
}
.item:last-child { border-bottom:none; }
.item__header { margin-bottom:2px; }
.item__desc { font-size:{{ $fs['tag'] }}px; margin-bottom:4px; }

/* ════════════════════════════════════════
   LAYOUT: CARDS  — 3-column compact cards
   ════════════════════════════════════════ */
@elseif($layout === 'cards')
.section { padding:{{ $sp['section_pad'] }}; }
.items-wrap { display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; }
.item {
    padding:10px 12px;
    border:1px solid {{ $t['border'] }};
    border-radius:6px;
    background:{{ $t['surface'] }};
    border-top:2px solid {{ $accent }};
}
.item__title { font-size:{{ $fs['base'] }}px; }
.item__desc { display:none; }
.item--milestone { border-top-color:{{ $primary }}; }
.item--achievement { border-top-color:#d4a017; }

/* ════════════════════════════════════════
   LAYOUT: MAGAZINE  — first item featured, rest 2-col
   ════════════════════════════════════════ */
@elseif($layout === 'magazine')
.section { padding:{{ $sp['section_pad'] }}; }
.items-wrap { }
.item {
    margin-bottom:{{ $sp['item_mb'] }};
    padding:{{ $sp['item_pad'] }};
    border:1px solid {{ $t['border'] }};
    border-left:3px solid {{ $accent }};
    border-radius:4px;
    background:{{ $t['surface'] }};
}
.item:first-child {
    border-left:none;
    border-top:4px solid {{ $primary }};
    padding:20px;
    margin-bottom:14px;
    background:{{ hexRgba($primary,0.04) }};
}
.item:first-child .item__title { font-size:{{ $fs['name'] - 4 }}px; color:{{ $primary }}; }
.item:first-child .item__desc { font-size:{{ $fs['item'] - 2 }}px; }
.items-sub { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.item--milestone { border-left-color:{{ $primary }}; }
.item--achievement { border-left-color:#d4a017; }

/* ════════════════════════════════════════
   LAYOUT: SIDEBAR  — left nav + right content
   ════════════════════════════════════════ */
@elseif($layout === 'sidebar')
.page-body { display:flex; gap:0; min-height:600px; }
.sidebar {
    width:160px;
    flex-shrink:0;
    background:{{ hexRgba($primary,0.06) }};
    border-right:2px solid {{ hexRgba($primary,0.2) }};
    padding:20px 14px;
}
.sidebar__name { font-family:'{{ $hFont }}',{{ $hStack }}; font-size:{{ $fs['item'] }}px; font-weight:700; color:{{ $primary }}; margin-bottom:14px; line-height:1.3; }
.sidebar__section { font-size:{{ $fs['tag'] }}px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:{{ $t['sub'] }}; margin-bottom:4px; margin-top:12px; }
.sidebar__item { font-size:{{ $fs['tag'] }}px; color:{{ $t['text'] }}; padding:2px 0; border-bottom:1px solid {{ hexRgba($primary,0.1) }}; margin-bottom:3px; }
.main-content { flex:1; padding:20px 28px; }
.section { padding:0 0 {{ $spacing==='compact'?'12px':($spacing==='relaxed'?'28px':'18px') }}; }
.items-wrap { }
.item {
    margin-bottom:{{ $sp['item_mb'] }};
    padding:{{ $sp['item_pad'] }};
    border:1px solid {{ $t['border'] }};
    border-left:3px solid {{ $accent }};
    border-radius:4px;
    background:{{ $t['surface'] }};
}
.item--milestone { border-left-color:{{ $primary }}; background:{{ hexRgba($primary,0.04) }}; }
.item--achievement { border-left-color:#d4a017; }
@endif

/* ── FOOTER ── */
.footer { margin-top:{{ $spacing==='spacious'?'36px':($spacing==='compact'?'16px':'24px') }}; padding:10px 40px; border-top:1px solid {{ $t['border'] }}; font-size:{{ $fs['tag'] }}px; color:{{ $t['sub'] }}; display:flex; justify-content:space-between; }
.footer__brand { color:{{ $primary }}; font-weight:700; }
.page-break { page-break-after:always; }
</style>
</head>
<body>

{{-- HEADER --}}
@if($headerStyle === 'banner')
<div class="header">
    <div class="header__left">
        <div class="header__initials">{{ strtoupper(substr($user->full_name,0,1)) }}{{ strtoupper(substr(strrchr($user->full_name,' '),1,1)) }}</div>
    </div>
    <div class="header__right">
        <div class="header__name">{{ $user->full_name }}</div>
        @if($user->program)<div class="header__meta">{{ $user->program }}@if($showEmail && $user->email) &nbsp;·&nbsp; {{ $user->email }}@endif</div>@endif
        @if($showUser && $user->username)<div class="header__meta">portfolio / {{ $user->username }}</div>@endif
        @if($showBio && $user->bio)<div class="header__bio">{{ $user->bio }}</div>@endif
    </div>
</div>
@else
<div class="header">
    <div class="header__name">{{ $user->full_name }}</div>
    <div class="header__accent-line"></div>
    @if($user->program)<div class="header__meta">{{ $user->program }}@if($showEmail && $user->email) &nbsp;·&nbsp; {{ $user->email }}@endif</div>@endif
    @if($showUser && $user->username)<div class="header__meta">portfolio / {{ $user->username }}</div>@endif
    @if($showBio && $user->bio)<div class="header__bio">{{ $user->bio }}</div>@endif
</div>
@endif
<div class="header__bar"></div>

@if($layout === 'sidebar')
{{-- ── SIDEBAR LAYOUT ── --}}
<div class="page-body">
    <div class="sidebar">
        <div class="sidebar__name">{{ $user->full_name }}</div>
        @foreach($orderedSections as $type)
        @if(isset($grouped[$type]) && count($grouped[$type]))
        <div class="sidebar__section">{{ $sectionLabels[$type] ?? ucfirst($type) }}</div>
        @foreach($grouped[$type] as $item)
        <div class="sidebar__item">{{ \Illuminate\Support\Str::limit($item['title'], 22) }}</div>
        @endforeach
        @endif
        @endforeach
    </div>
    <div class="main-content">
        @foreach($orderedSections as $type)
        @if(isset($grouped[$type]) && count($grouped[$type]))
        <div class="section">
            <div class="section-title">{{ $sectionLabels[$type] ?? ucfirst($type) }}</div>
            <div class="items-wrap">
                @foreach($grouped[$type] as $item)
                <div class="item item--{{ $type }}">
                    <div class="item__header">
                        <div class="item__title">{{ $item['title'] }}</div>
                        @if(!empty($item['item_date']))<div class="item__date">{{ \Carbon\Carbon::parse($item['item_date'])->format('M Y') }}</div>@endif
                    </div>
                    @if(!empty($item['description']))<div class="item__desc">{{ $item['description'] }}</div>@endif
                    @if(!empty($item['tags']))<div class="item__tags">@foreach((array)$item['tags'] as $tag)<span class="item__tag">{{ $tag }}</span>@endforeach</div>@endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>

@elseif($layout === 'magazine')
{{-- ── MAGAZINE LAYOUT ── --}}
@foreach($orderedSections as $type)
@if(isset($grouped[$type]) && count($grouped[$type]))
<div class="section">
    <div class="section-title">{{ $sectionLabels[$type] ?? ucfirst($type) }}</div>
    @php $sectionItems = $grouped[$type]; $first = array_shift($sectionItems); @endphp
    {{-- Featured first item --}}
    <div class="item item--{{ $type }}">
        <div class="item__header">
            <div class="item__title">{{ $first['title'] }}</div>
            @if(!empty($first['item_date']))<div class="item__date">{{ \Carbon\Carbon::parse($first['item_date'])->format('M Y') }}</div>@endif
        </div>
        @if(!empty($first['description']))<div class="item__desc">{{ $first['description'] }}</div>@endif
        @if(!empty($first['tags']))<div class="item__tags">@foreach((array)$first['tags'] as $tag)<span class="item__tag">{{ $tag }}</span>@endforeach</div>@endif
    </div>
    {{-- Rest in 2-col --}}
    @if(count($sectionItems))
    <div class="items-sub">
        @foreach($sectionItems as $item)
        <div class="item item--{{ $type }}">
            <div class="item__header">
                <div class="item__title">{{ $item['title'] }}</div>
                @if(!empty($item['item_date']))<div class="item__date">{{ \Carbon\Carbon::parse($item['item_date'])->format('M Y') }}</div>@endif
            </div>
            @if(!empty($item['description']))<div class="item__desc">{{ $item['description'] }}</div>@endif
            @if(!empty($item['tags']))<div class="item__tags">@foreach((array)$item['tags'] as $tag)<span class="item__tag">{{ $tag }}</span>@endforeach</div>@endif
        </div>
        @endforeach
    </div>
    @endif
</div>
@endif
@endforeach

@else
{{-- ── ALL OTHER LAYOUTS (list, grid, timeline, compact, cards) ── --}}
@foreach($orderedSections as $type)
@if(isset($grouped[$type]) && count($grouped[$type]))
<div class="section">
    <div class="section-title">{{ $sectionLabels[$type] ?? ucfirst($type) }}</div>
    <div class="items-wrap">
        @foreach($grouped[$type] as $item)
        <div class="item item--{{ $type }}">
            <div class="item__header">
                <div class="item__title">{{ $item['title'] }}</div>
                @if(!empty($item['item_date']))<div class="item__date">{{ \Carbon\Carbon::parse($item['item_date'])->format('M Y') }}</div>@endif
            </div>
            @if(!empty($item['description']))<div class="item__desc">{{ $item['description'] }}</div>@endif
            @if(!empty($item['tags']))<div class="item__tags">@foreach((array)$item['tags'] as $tag)<span class="item__tag">{{ $tag }}</span>@endforeach</div>@endif
        </div>
        @endforeach
    </div>
</div>
@endif
@endforeach
@endif

<div class="footer">
    <span><span class="footer__brand">{{ $user->full_name }}</span> — Portfolio Export</span>
    <span>Generated {{ now()->format('F j, Y') }}</span>
</div>

</body>
</html>
