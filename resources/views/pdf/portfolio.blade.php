<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
@php
    $primary = $customization->primary_color ?? '#3498db';
    $accent  = $customization->accent_color  ?? '#e74c3c';
    $sections = [
        'project'     => 'Projects',
        'experience'  => 'Experience',
        'education'   => 'Education',
        'achievement' => 'Achievements',
        'milestone'   => 'Milestones',
        'skill'       => 'Skills',
    ];
@endphp
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, Arial, sans-serif; color: #1a1a1a; font-size: 12px; }

    /* ── HEADER ── */
    .header {
        background: #1a1a1a;
        color: #fff;
        padding: 36px 40px;
        margin-bottom: 0;
    }
    .header__name { font-size: 26px; font-weight: 700; margin-bottom: 6px; }
    .header__meta { font-size: 11px; opacity: .65; margin-bottom: 4px; }
    .header__bio  { font-size: 11.5px; opacity: .8; margin-top: 10px; line-height: 1.5; max-width: 520px; }

    .header__accent-bar { height: 4px; background: {{ $accent }}; }

    /* ── SECTION ── */
    .section { padding: 24px 40px 0; }
    .section-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: {{ $primary }};
        border-bottom: 1.5px solid {{ $primary }}33;
        padding-bottom: 6px;
        margin-bottom: 14px;
    }

    /* ── ITEM ── */
    .item {
        margin-bottom: 14px;
        padding: 14px 16px;
        border: 1px solid #e8e8e8;
        border-left: 3px solid {{ $accent }};
        border-radius: 4px;
        background: #fafafa;
    }
    .item__header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px; }
    .item__title  { font-size: 13px; font-weight: 700; color: #111; }
    .item__date   { font-size: 10px; color: #888; white-space: nowrap; margin-left: 12px; }
    .item__desc   { font-size: 11.5px; line-height: 1.65; color: #444; margin-bottom: 8px; }
    .item__tags   { display: flex; flex-wrap: wrap; gap: 4px; }
    .item__tag    {
        font-size: 9.5px;
        background: {{ $accent }}18;
        color: {{ $accent }};
        padding: 2px 8px;
        border-radius: 999px;
        border: 1px solid {{ $accent }}44;
    }

    /* ── MILESTONE special style ── */
    .item--milestone {
        border-left-color: {{ $primary }};
        background: {{ $primary }}08;
    }
    .item--milestone .item__title { color: {{ $primary }}; }

    /* ── ACHIEVEMENT special style ── */
    .item--achievement {
        border-left-color: #f0a500;
        background: #fffbf0;
    }
    .item--achievement .item__title { color: #b07800; }

    .page-break { page-break-after: always; }

    /* ── FOOTER ── */
    .footer {
        margin-top: 30px;
        padding: 14px 40px;
        border-top: 1px solid #e0e0e0;
        font-size: 10px;
        color: #aaa;
        display: flex;
        justify-content: space-between;
    }
</style>
</head>
<body>

<div class="header">
    <div class="header__name">{{ $user->full_name }}</div>
    <div class="header__meta">{{ $user->program }}@if($user->email) · {{ $user->email }}@endif</div>
    @if($user->username)<div class="header__meta">portfolio.app/{{ $user->username }}</div>@endif
    @if($user->bio)<div class="header__bio">{{ $user->bio }}</div>@endif
</div>
<div class="header__accent-bar"></div>

@foreach($sections as $type => $label)
@if(isset($grouped[$type]) && count($grouped[$type]))
<div class="section">
    <div class="section-title">{{ $label }}</div>
    @foreach($grouped[$type] as $item)
    <div class="item item--{{ $type }}">
        <div class="item__header">
            <div class="item__title">{{ $item['title'] }}</div>
            @if(!empty($item['item_date']))
            <div class="item__date">{{ \Carbon\Carbon::parse($item['item_date'])->format('M Y') }}</div>
            @endif
        </div>
        @if(!empty($item['description']))
        <div class="item__desc">{{ $item['description'] }}</div>
        @endif
        @if(!empty($item['tags']))
        <div class="item__tags">
            @foreach((array)$item['tags'] as $tag)
            <span class="item__tag">{{ $tag }}</span>
            @endforeach
        </div>
        @endif
    </div>
    @endforeach
</div>
@endif
@endforeach

<div class="footer">
    <span>{{ $user->full_name }} — Portfolio Export</span>
    <span>Generated {{ now()->format('F j, Y') }}</span>
</div>

</body>
</html>
