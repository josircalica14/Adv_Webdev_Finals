@extends('layouts.app')
@section('title', $user->full_name . "'s Portfolio")
@section('styles')
@if($customization)
<style>
    :root {
        --portfolio-primary: {{ $customization->primary_color }};
        --portfolio-accent: {{ $customization->accent_color }};
    }
    .portfolio-heading { font-family: '{{ $customization->heading_font }}', serif; }
    .portfolio-body    { font-family: '{{ $customization->body_font }}', sans-serif; }
</style>
@endif
<style>
    .pv-card:hover {
        border-color: rgba(245,240,232,.25) !important;
        background: rgba(255,255,255,.06) !important;
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0,0,0,.3);
    }
    .pv-card {
        cursor: default;
    }
</style>
@endsection
@section('content')
<div style="padding-top:80px">
    <div style="text-align:center;padding:80px 20px 60px;border-bottom:1px solid rgba(245,240,232,.1)">
        @if($user->profile_photo_path)
            <img src="{{ Storage::disk('portfolio')->url($user->profile_photo_path) }}" style="width:140px;height:140px;border-radius:50%;object-fit:cover;border:3px solid #e84040;margin-bottom:20px">
        @else
            <div style="width:140px;height:140px;border-radius:50%;background:rgba(232,64,64,.15);display:flex;align-items:center;justify-content:center;margin:0 auto 20px"><i class="fas fa-user" style="font-size:56px;color:#e84040"></i></div>
        @endif
        <h1 class="portfolio-heading" style="font-size:clamp(2rem,5vw,3rem);font-weight:700;margin-bottom:8px">{{ $user->full_name }}</h1>
        <p style="font-family:'Space Mono',monospace;font-size:.8rem;color:rgba(245,240,232,.5);margin-bottom:16px">{{ $user->username }} · {{ $user->program }}</p>
        @if($user->bio)<p style="max-width:600px;margin:0 auto;color:rgba(245,240,232,.7);line-height:1.8;font-size:.9rem">{{ $user->bio }}</p>@endif

        {{-- Social links --}}
        @php
            $ci = $user->contact_info ?? [];
            $socialIcons = [
                'github'    => ['fab fa-github',    $ci['github']    ?? null],
                'linkedin'  => ['fab fa-linkedin',  $ci['linkedin']  ?? null],
                'twitter'   => ['fab fa-x-twitter', $ci['twitter']   ?? null],
                'facebook'  => ['fab fa-facebook',  $ci['facebook']  ?? null],
                'instagram' => ['fab fa-instagram', $ci['instagram'] ?? null],
                'website'   => ['fas fa-globe',     $ci['website']   ?? null],
            ];
            $hasSocials = collect($socialIcons)->filter(fn($v) => $v[1])->isNotEmpty();
        @endphp
        @if($hasSocials)
        <div style="display:flex;justify-content:center;gap:14px;margin-top:20px">
            @foreach($socialIcons as $key => [$icon, $url])
                @if($url)
                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                   style="width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.06);border:1.5px solid rgba(245,240,232,.15);display:flex;align-items:center;justify-content:center;font-size:.95rem;color:rgba(245,240,232,.7);transition:all .2s"
                   onmouseover="this.style.background='rgba(232,64,64,.2)';this.style.color='#e84040';this.style.borderColor='rgba(232,64,64,.4)'"
                   onmouseout="this.style.background='rgba(255,255,255,.06)';this.style.color='rgba(245,240,232,.7)';this.style.borderColor='rgba(245,240,232,.15)'">
                    <i class="{{ $icon }}"></i>
                </a>
                @endif
            @endforeach
        </div>
        @endif
    </div>

    <div style="max-width:1200px;margin:0 auto;padding:60px 40px">
        @php $grouped = $items->groupBy('item_type'); @endphp
        @foreach(['project'=>'Projects','achievement'=>'Achievements','milestone'=>'Milestones','skill'=>'Skills','experience'=>'Experience','education'=>'Education'] as $type => $label)
        @if($grouped->has($type))
        <div style="margin-bottom:60px">
            <h2 class="portfolio-heading" style="font-size:1.8rem;font-weight:700;margin-bottom:30px;padding-bottom:12px;border-bottom:2px solid rgba(232,64,64,.3)">{{ $label }}</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px">
                @foreach($grouped[$type] as $item)
                <div class="pv-card" style="background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;overflow:hidden;transition:border-color .25s,background .25s,transform .25s,box-shadow .25s">
                    @php
                        $image = $item->files->first(fn($f) => str_starts_with($f->file_type, 'image/'));
                    @endphp
                    @if($image)
                    <div style="width:100%;height:180px;overflow:hidden;background:rgba(0,0,0,.2)">
                        <img src="{{ Storage::disk('portfolio')->url($image->thumbnail_path ?? $image->file_path) }}"
                             alt="{{ $item->title }}"
                             style="width:100%;height:100%;object-fit:cover;display:block;transition:transform .3s"
                             onmouseover="this.style.transform='scale(1.04)'"
                             onmouseout="this.style.transform='scale(1)'">
                    </div>
                    @endif
                    <div style="padding:24px">
                        <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:8px">{{ $item->title }}</h3>
                        @if($item->item_date)<p style="font-size:.72rem;color:rgba(245,240,232,.5);font-family:'Space Mono',monospace;margin-bottom:8px">{{ $item->item_date->format('M Y') }}</p>@endif
                        <p style="font-size:.85rem;color:rgba(245,240,232,.7);line-height:1.7;margin-bottom:12px">{{ $item->description }}</p>
                        @if($item->tags)
                        <div style="display:flex;flex-wrap:wrap;gap:6px">
                            @foreach($item->tags as $tag)<span style="font-size:.65rem;background:rgba(232,64,64,.15);color:#e84040;padding:.2rem .6rem;border-radius:999px">{{ $tag }}</span>@endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>
@endsection
