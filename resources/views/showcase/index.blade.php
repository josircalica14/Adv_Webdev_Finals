@extends('layouts.app')
@section('title', 'Showcase')
@section('content')

{{-- HERO --}}
<section class="hero" id="hero">
    <div class="hero__glow" aria-hidden="true"></div>
    @auth
        @php
            $firstName = explode(' ', auth()->user()->full_name)[0];
            $len = strlen($firstName);
            $maxVw = max(5, 12 - ($len - 4) * 0.8);
            $maxRem = max(3.5, 7 - ($len - 4) * 0.4);
            $nameSize = "clamp(2.5rem, {$maxVw}vw, {$maxRem}rem)";
        @endphp
        <div class="hero__bg-text hero__bg-text--auth" aria-hidden="true">{{ strtoupper($firstName) }}</div>
        <div class="hero__content">
            <div class="hero__eyebrow hero__eyebrow--auth">MAKE A NAME,</div>
            <h1 class="hero__headline">
                <span class="line" data-text="{{ strtoupper($firstName) }}." style="font-size:{{ $nameSize }}">{{ strtoupper($firstName) }}.</span>
            </h1>
            <div class="hero__below">
                <div class="hero__rule"></div>
                <p class="hero__sub">Your portfolio is live. Keep building.</p>
                <a href="{{ route('dashboard.index') }}" class="hero__cta">Go to Dashboard <span>→</span></a>
            </div>
        </div>
    @else
        <div class="hero__bg-text hero__bg-text--guest" aria-hidden="true">MAKE A NAME</div>
        <div class="hero__content">
            <div class="hero__eyebrow">STUDENT PORTFOLIOS</div>
            <h1 class="hero__headline">
                <span class="line" data-text="MAKE A NAME.">MAKE A NAME.</span>
            </h1>
            <div class="hero__below">
                <div class="hero__rule"></div>
                <p class="hero__sub">Discover portfolios from BSIT &amp; CSE — built, owned, and showcased here.</p>
                <a href="{{ route('register') }}" class="hero__cta">Get Started <span>→</span></a>
            </div>
        </div>
    @endauth
    <div class="hero__scroll-hint" aria-hidden="true">scroll ↓</div>
</section>

{{-- STATS STRIP --}}
<section class="stats-strip">
    <div class="stats-inner">
        <div class="stat-item">
            <span class="stat-num" data-target="{{ $stats['students'] }}">0</span>
            <span class="stat-label">Students</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <span class="stat-num" data-target="{{ $stats['portfolios'] }}">0</span>
            <span class="stat-label">Portfolios Published</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <span class="stat-num" data-target="{{ $stats['skills'] }}">0</span>
            <span class="stat-label">Unique Skills</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <span class="stat-num" data-target="{{ $stats['views'] }}">0</span>
            <span class="stat-label">Total Views</span>
        </div>
    </div>
</section>

{{-- HOW IT WORKS --}}
@guest
<section class="hiw-section">
    <div class="hiw-inner">
        <p class="section-eyebrow">— HOW IT WORKS</p>
        <h2 class="section-title">Three steps to your portfolio</h2>
        <div class="hiw-steps">
            <div class="hiw-step">
                <div class="hiw-step__num">01</div>
                <div class="hiw-step__icon"><i class="fas fa-user-plus"></i></div>
                <h3 class="hiw-step__title">Create an account</h3>
                <p class="hiw-step__desc">Sign up with your student email. Takes less than a minute.</p>
            </div>
            <div class="hiw-step__connector" aria-hidden="true">→</div>
            <div class="hiw-step">
                <div class="hiw-step__num">02</div>
                <div class="hiw-step__icon"><i class="fas fa-layer-group"></i></div>
                <h3 class="hiw-step__title">Build your portfolio</h3>
                <p class="hiw-step__desc">Add projects, achievements, skills, and milestones. Customize the look.</p>
            </div>
            <div class="hiw-step__connector" aria-hidden="true">→</div>
            <div class="hiw-step">
                <div class="hiw-step__num">03</div>
                <div class="hiw-step__icon"><i class="fas fa-globe"></i></div>
                <h3 class="hiw-step__title">Get discovered</h3>
                <p class="hiw-step__desc">Your public portfolio appears in the showcase for everyone to find.</p>
            </div>
        </div>
    </div>
</section>
@endguest

{{-- FEATURED PORTFOLIOS --}}
@if($featured->isNotEmpty())
<section class="featured-section">
    <div class="featured-inner">
        <p class="section-eyebrow">— FEATURED</p>
        <h2 class="section-title">Top Portfolios</h2>
        <div class="featured-grid">
            @foreach($featured as $i => $portfolio)
            @if(!$portfolio->user->username) @continue @endif
            <a href="{{ route('portfolio.public', $portfolio->user->username) }}" class="featured-card {{ $i === 0 ? 'featured-card--hero' : '' }}">
                <div class="featured-card__badge">
                    <i class="fas fa-fire"></i>
                    @if($i === 0) Top Viewed @else #{{ $i + 1 }} @endif
                </div>
                <div class="featured-card__top">
                    @if($portfolio->user->profile_photo_path)
                        <img src="{{ Storage::disk('portfolio')->url($portfolio->user->profile_photo_path) }}" class="featured-card__avatar" alt="{{ $portfolio->user->full_name }}">
                    @else
                        @php $initials = collect(explode(' ', $portfolio->user->full_name))->map(fn($w) => strtoupper($w[0]))->take(2)->implode(''); @endphp
                        <div class="featured-card__avatar featured-card__avatar--initials">{{ $initials }}</div>
                    @endif
                    <div>
                        <div class="featured-card__name">{{ $portfolio->user->full_name }}</div>
                        <div class="featured-card__program">{{ $portfolio->user->program }}</div>
                    </div>
                </div>
                @if($portfolio->user->bio)
                <p class="featured-card__bio">{{ Str::limit($portfolio->user->bio, 110) }}</p>
                @endif
                @php $tags = $portfolio->items->flatMap(fn($i) => (array)($i->tags ?? []))->unique()->take(4)->values(); @endphp
                @if($tags->isNotEmpty())
                <div class="featured-card__tags">
                    @foreach($tags as $tag)<span class="featured-card__tag">{{ $tag }}</span>@endforeach
                </div>
                @endif
                <div class="featured-card__footer">
                    <span>{{ $portfolio->items->count() }} items</span>
                    <span>{{ number_format($portfolio->view_count) }} views</span>
                    <span class="featured-card__arrow">View Portfolio →</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- SKILLS CLOUD --}}
@if($skills->isNotEmpty())
<section class="skills-section">
    <div class="skills-inner">
        <p class="section-eyebrow">— SKILLS & TECH</p>
        <h2 class="section-title">What students are building with</h2>
        <div class="skills-cloud">
            @foreach($skills as $skill)
            <button type="button" class="skill-tag pill pill-program" data-val="" onclick="filterBySkill('{{ addslashes($skill) }}')">{{ $skill }}</button>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- PROGRAMS SPOTLIGHT --}}
<section class="programs-section">
    <div class="programs-inner">
        <p class="section-eyebrow">— PROGRAMS</p>
        <h2 class="section-title">Find your people</h2>
        <div class="programs-grid">
            <div class="program-card">
                <div class="program-card__icon"><i class="fas fa-laptop-code"></i></div>
                <div class="program-card__label">BSIT</div>
                <h3 class="program-card__title">BS Information Technology</h3>
                <p class="program-card__desc">Web development, systems, databases, networking — the builders of digital infrastructure.</p>
                <button type="button" class="program-card__cta pill pill-program" data-val="BSIT" onclick="filterByProgram('BSIT')">Browse BSIT →</button>
            </div>
            <div class="program-card">
                <div class="program-card__icon"><i class="fas fa-microchip"></i></div>
                <div class="program-card__label">CSE</div>
                <h3 class="program-card__title">Computer Science & Engineering</h3>
                <p class="program-card__desc">Algorithms, machine learning, AI, embedded systems — the engineers pushing the frontier.</p>
                <button type="button" class="program-card__cta pill pill-program" data-val="CSE" onclick="filterByProgram('CSE')">Browse CSE →</button>
            </div>
        </div>
    </div>
</section>

{{-- RECENTLY JOINED --}}
@if($recent->isNotEmpty())
<section class="recent-section">
    <div class="recent-inner">
        <p class="section-eyebrow">— RECENTLY JOINED</p>
        <h2 class="section-title">New to the showcase</h2>
        <div class="recent-scroll">
            @foreach($recent as $portfolio)
            @if(!$portfolio->user->username) @continue @endif
            <a href="{{ route('portfolio.public', $portfolio->user->username) }}" class="recent-card">
                @if($portfolio->user->profile_photo_path)
                    <img src="{{ Storage::disk('portfolio')->url($portfolio->user->profile_photo_path) }}" class="recent-card__avatar" alt="{{ $portfolio->user->full_name }}">
                @else
                    @php $initials = collect(explode(' ', $portfolio->user->full_name))->map(fn($w) => strtoupper($w[0]))->take(2)->implode(''); @endphp
                    <div class="recent-card__avatar recent-card__avatar--initials">{{ $initials }}</div>
                @endif
                <div class="recent-card__name">{{ $portfolio->user->full_name }}</div>
                <div class="recent-card__program">{{ $portfolio->user->program }}</div>
                <div class="recent-card__count">{{ $portfolio->items->count() }} items</div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- CTA BANNER --}}
@guest
<section class="cta-section">
    <div class="cta-inner">
        <div class="cta-glow" aria-hidden="true"></div>
        <p class="section-eyebrow" style="color:rgba(245,240,232,.5)">— YOUR TURN</p>
        <h2 class="cta-title">Ready to build yours?</h2>
        <p class="cta-sub">Join {{ $stats['students'] }} students already showcasing their work.</p>
        <div class="cta-actions">
            <a href="{{ route('register') }}" class="cta-btn cta-btn--primary">Get Started Free</a>
            <a href="#showcase-results" class="cta-btn cta-btn--ghost">Browse Portfolios</a>
        </div>
    </div>
</section>
@endguest

{{-- SHOWCASE --}}
<div class="showcase-section">
    <div class="showcase-header">
        <div>
            <p class="showcase-label">— BROWSE</p>
            <h2 class="showcase-title">Student Showcase</h2>
        </div>
    </div>

    <form method="GET" action="{{ route('showcase.index') }}" id="showcase-form" class="showcase-filters">
        <div class="search-row">
            <div class="search-wrap">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="q" value="{{ $criteria['query'] }}" placeholder="Search by name, skill, or program…" class="search-input">
            </div>
            <button type="submit" class="search-btn">Search</button>
        </div>
        <div class="filter-row">
            <div class="filter-group">
                <span class="filter-label">Program</span>
                @foreach(['' => 'All', 'BSIT' => 'BSIT', 'CSE' => 'CSE'] as $val => $label)
                <button type="button" class="pill pill-program {{ $criteria['program'] === $val ? 'pill-active' : '' }}" data-val="{{ $val }}">{{ $label }}</button>
                @endforeach
            </div>
            <div class="filter-group">
                <span class="filter-label">Sort</span>
                @foreach(['updated' => 'Recent', 'name' => 'A–Z'] as $val => $label)
                <button type="button" class="pill pill-sort {{ $criteria['sort'] === $val ? 'pill-active' : '' }}" data-val="{{ $val }}">{{ $label }}</button>
                @endforeach
            </div>
        </div>
    </form>

    <div id="showcase-results">
        @include('showcase.partials.results')
    </div>
</div>

@push('styles')
<style>
    /* ── HERO ─────────────────────────────────────── */
    .hero {
        position: relative;
        min-height: 100vh;
        display: flex;
        align-items: center;
        padding: 3rem 5vw 4rem;
        overflow: hidden;
        background: #181818;
    }
    /* Ghost background text — shared base */
    .hero__bg-text {
        position: absolute;
        top: 50%;
        transform: translateY(-90%);
        font-family: 'Space Mono', monospace;
        font-weight: 700;
        letter-spacing: -0.04em;
        white-space: nowrap;
        color: transparent;
        -webkit-text-stroke: 1.5px rgba(255,255,255,0.25);
        pointer-events: none;
        user-select: none;
        z-index: 0;
        animation: bg-scan 9s ease-in-out infinite;
    }
    .hero__bg-text::after { display: none; content: none; }
    /* Guest: centered, full width, pure CSS sizing — "MAKE A NAME" is a fixed string */
    .hero__bg-text--guest {
        left: 0;
        width: 100%;
        text-align: center;
        font-size: 260px; /* JS will shrink to fit viewport */
    }
    /* Auth: left-aligned with small indent, JS controls font-size */
    .hero__bg-text--auth {
        left: 3vw;
        width: auto;
        text-align: left;
        font-size: 260px; /* JS will shrink if name is long */
    }
    @keyframes bg-scan {
        0% {
            -webkit-text-stroke-color: rgba(255,255,255,0.12);
            filter: drop-shadow(-60px 0 0px rgba(255,255,255,0))
                    drop-shadow(0 0 0px rgba(255,255,255,0));
            transform: translateY(-90%) scale(1);
        }
        25% {
            -webkit-text-stroke-color: rgba(255,255,255,0.5);
            filter: drop-shadow(-20px 0 18px rgba(255,255,255,0.6))
                    drop-shadow(0 0 40px rgba(255,255,255,0.2));
            transform: translateY(-90%) scale(1.01);
        }
        50% {
            -webkit-text-stroke-color: rgba(255,255,255,0.55);
            filter: drop-shadow(20px 0 22px rgba(255,255,255,0.7))
                    drop-shadow(0 0 60px rgba(255,255,255,0.25));
            transform: translateY(-90%) scale(1.02);
        }
        75% {
            -webkit-text-stroke-color: rgba(255,255,255,0.4);
            filter: drop-shadow(60px 0 14px rgba(255,255,255,0.4))
                    drop-shadow(0 0 30px rgba(255,255,255,0.12));
            transform: translateY(-90%) scale(1.01);
        }
        100% {
            -webkit-text-stroke-color: rgba(255,255,255,0.12);
            filter: drop-shadow(100px 0 0px rgba(255,255,255,0))
                    drop-shadow(0 0 0px rgba(255,255,255,0));
            transform: translateY(-90%) scale(1);
        }
    }
    .hero__content { position: relative; z-index: 1; padding-left: 0; margin-top: -4rem; }
    .hero__eyebrow {
        font-family: 'Space Mono', monospace;
        font-size: 1.1rem;
        letter-spacing: .2em;
        color: rgba(245,240,232,.4);
        margin-bottom: 2rem;
        opacity: 0;
        animation: fade-up .8s cubic-bezier(0.16,1,0.3,1) 0s forwards;
    }
    .hero__eyebrow--auth {
        margin-top: -2rem;
    }
    .hero__headline {
        font-family: 'Space Mono', monospace;
        font-size: clamp(3rem, 10vw, 7rem);
        font-weight: 700;
        line-height: 0.9;
        letter-spacing: -0.03em;
        text-transform: uppercase;
        display: flex;
        flex-direction: column;
        padding-top: 1rem;
    }
    /* Each word slides up */
    .line {
        display: block;
        transform: translateY(60%) skewY(3deg);
        opacity: 0;
        filter: blur(8px);
        animation: line-in 1.1s cubic-bezier(0.16,1,0.3,1) 0.15s forwards;
    }
    .line--greeting {
        font-size: clamp(1rem, 3vw, 2rem);
        letter-spacing: 0.15em;
        opacity: 0;
        animation: line-in 1.1s cubic-bezier(0.16,1,0.3,1) 0s forwards;
        color: rgba(245,240,232,0.5);
        text-shadow: none;
    }
    @keyframes line-in {
        to { transform: translateY(0) skewY(0deg); opacity: 1; filter: blur(0); }
    }
    /* Glow text effect on the headline — static, no pulse */
    .hero__headline .line {
        color: #f5f0e8;
        text-shadow:
            0 0 18px rgba(255,255,255,0.55),
            0 0 55px rgba(255,255,255,0.22),
            0 0 110px rgba(255,255,255,0.08);
        animation: line-in 1.1s cubic-bezier(0.16,1,0.3,1) 0.15s forwards;
    }
    /* Shimmer sweep across the text */
    .hero__headline .line {
        position: relative;
    }
    .hero__headline .line::after {
        content: attr(data-text);
        position: absolute;
        inset: 0;
        background: linear-gradient(
            105deg,
            transparent 30%,
            rgba(255,255,255,0.55) 50%,
            transparent 70%
        );
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        background-size: 200% 100%;
        background-position: -200% 0;
        animation: shimmer 3.5s ease-in-out 1.5s infinite;
        pointer-events: none;
    }
    @keyframes shimmer {
        0%   { background-position: -200% 0; }
        40%,100% { background-position: 200% 0; }
    }
    /* Subtitle */
    .hero__sub {
        font-family: 'Instrument Sans', sans-serif;
        font-size: 0.9rem;
        letter-spacing: 0.02em;
        line-height: 1.7;
        opacity: 0;
        filter: blur(4px);
        animation: fade-up 1s cubic-bezier(0.16,1,0.3,1) 0.75s forwards;
        margin-top: 6rem;
        color: rgba(245,240,232,0.75);
        max-width: 480px;
        text-shadow:
            0 0 12px rgba(255,255,255,0.35),
            0 0 30px rgba(255,255,255,0.12);
    }
    /* Scroll hint */
    .hero__scroll-hint {
        position: absolute;
        bottom: 2rem;
        right: 2.5rem;
        font-family: 'Space Mono', monospace;
        font-size: 0.65rem;
        letter-spacing: 0.15em;
        opacity: 0;
        animation: fade-up 1s cubic-bezier(0.16,1,0.3,1) 1.2s forwards;
    }
    @keyframes fade-up {
        from { opacity: 0; transform: translateY(16px); filter: blur(4px); }
        to   { opacity: 1; transform: translateY(0);   filter: blur(0); }
    }
    /* Glow orb */
    .hero__glow {
        position: absolute;
        top: 50%;
        left: 10%;
        transform: translate(-50%, -50%);
        width: 700px;
        height: 700px;
        background: radial-gradient(circle, rgba(255,255,255,0.055) 0%, transparent 65%);
        pointer-events: none;
        z-index: 0;
        animation: orb-drift 12s ease-in-out infinite;
    }
    @keyframes orb-drift {
        0%,100% { transform: translate(-50%, -50%) scale(1);    opacity: .6; }
        50%      { transform: translate(-38%, -58%) scale(1.2); opacity: 1; }
    }
    @keyframes fade-up {
        from { opacity: 0; transform: translateY(16px); filter: blur(4px); }
        to   { opacity: 1; transform: translateY(0);   filter: blur(0); }
    }

    /* ── SHOWCASE SECTION ─────────────────────────── */
    .showcase-section {
        max-width: 1400px;
        margin: 0 auto;
        padding: 80px 5vw 80px;
    }
    .showcase-header {
        display: flex;
        align-items: flex-end;
        margin-bottom: 3rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(245,240,232,.08);
    }
    .showcase-label {
        font-family: 'Space Mono', monospace;
        font-size: .6rem;
        letter-spacing: .2em;
        color: #e84040;
        margin-bottom: .5rem;
    }
    .showcase-title {
        font-family: 'DM Serif Display', serif;
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 400;
        line-height: 1;
    }
    .showcase-filters { display: flex; flex-direction: column; gap: 14px; margin-bottom: 3rem; }
    .search-row { display: flex; gap: 10px; }
    .search-wrap { flex: 1; position: relative; display: flex; align-items: center; }
    .search-icon { position: absolute; left: 16px; color: rgba(245,240,232,.3); font-size: .8rem; pointer-events: none; }
    .search-input {
        width: 100%;
        padding: 12px 18px 12px 40px;
        background: rgba(255,255,255,.04);
        border: 1.5px solid rgba(245,240,232,.12);
        border-radius: 999px;
        color: #f5f0e8;
        font-size: .85rem;
        font-family: 'Instrument Sans', sans-serif;
        outline: none;
        transition: border-color .2s;
    }
    .search-input:focus { border-color: rgba(245,240,232,.35); }
    .search-input::placeholder { color: rgba(245,240,232,.3); }
    .search-btn {
        padding: 12px 1.8rem;
        background: #e84040;
        color: #fff;
        border: none;
        border-radius: 999px;
        font-family: 'Space Mono', monospace;
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        cursor: pointer;
        transition: opacity .2s;
        flex-shrink: 0;
    }
    .search-btn:hover { opacity: .85; }
    .filter-row { display: flex; align-items: center; gap: 24px; flex-wrap: wrap; }
    .filter-group { display: flex; align-items: center; gap: 6px; }
    .filter-label {
        font-family: 'Space Mono', monospace;
        font-size: .58rem;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: rgba(245,240,232,.35);
        margin-right: 2px;
    }
    .pill {
        padding: .3rem .9rem;
        border-radius: 999px;
        font-family: 'Space Mono', monospace;
        font-size: .6rem;
        font-weight: 700;
        letter-spacing: .08em;
        cursor: pointer;
        transition: all .2s;
        background: transparent;
        color: rgba(245,240,232,.5);
        border: 1.5px solid rgba(245,240,232,.15);
    }
    .pill:hover { border-color: rgba(245,240,232,.4); color: #f5f0e8; }
    .pill.pill-active { background: #e84040; color: #fff; border-color: #e84040; }

    /* ── HERO CTA + RULE ──────────────────────────── */
    .hero__below {
        margin-top: 8rem;
    }
    .hero__rule {
        width: 48px; height: 2px;
        background: #e84040;
        margin: 3rem 0 1.5rem;
        opacity: 0;
        animation: fade-up .8s cubic-bezier(0.16,1,0.3,1) .5s forwards;
    }
    .hero__sub {
        font-family: 'Instrument Sans', sans-serif;
        font-size: .95rem;
        line-height: 1.7;
        color: rgba(245,240,232,.65);
        max-width: 420px;
        opacity: 0;
        filter: blur(4px);
        animation: fade-up 1s cubic-bezier(0.16,1,0.3,1) .65s forwards;
        text-shadow: 0 0 12px rgba(255,255,255,.2);
    }
    .hero__cta {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        margin-top: 2rem;
        padding: .65rem 1.6rem;
        background: transparent;
        border: 1.5px solid rgba(245,240,232,.25);
        border-radius: 999px;
        font-family: 'Space Mono', monospace;
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: #f5f0e8;
        opacity: 0;
        animation: fade-up .8s cubic-bezier(0.16,1,0.3,1) .85s forwards;
        transition: background .2s, border-color .2s;
    }
    .hero__cta:hover { background: #e84040; border-color: #e84040; }
    .hero__scroll-hint {
        position: absolute;
        bottom: 2rem; right: 2.5rem;
        font-family: 'Space Mono', monospace;
        font-size: .62rem;
        letter-spacing: .15em;
        color: rgba(245,240,232,.3);
        opacity: 0;
        animation: fade-up 1s cubic-bezier(0.16,1,0.3,1) 1.2s forwards;
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    // Fit bg text: shrink from max size if it would overflow
    const MAX_FONT_PX = 260;
    function fitBgText() {
        // Auth: left-aligned, account for 3vw left offset
        const auth = document.querySelector('.hero__bg-text--auth');
        if (auth) {
            auth.style.fontSize = MAX_FONT_PX + 'px';
            const leftOffset = window.innerWidth * 0.03;
            if (auth.scrollWidth + leftOffset > window.innerWidth) {
                const ratio = (window.innerWidth - leftOffset) / auth.scrollWidth;
                auth.style.fontSize = Math.floor(MAX_FONT_PX * ratio) + 'px';
            }
        }
        // Guest: centered, no offset — fit to full viewport width
        const guest = document.querySelector('.hero__bg-text--guest');
        if (guest) {
            guest.style.fontSize = MAX_FONT_PX + 'px';
            if (guest.scrollWidth > window.innerWidth) {
                const ratio = window.innerWidth / guest.scrollWidth;
                guest.style.fontSize = Math.floor(MAX_FONT_PX * ratio) + 'px';
            }
        }
    }
    fitBgText();
    window.addEventListener('resize', fitBgText);

    // Parallax on bg text
    const heroBgText = document.querySelector('.hero__bg-text--auth, .hero__bg-text--guest');
    if (heroBgText) {
        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    heroBgText.style.transform = `translateY(calc(-65% + ${window.scrollY * 0.35}px))`;
                    ticking = false;
                });
                ticking = true;
            }
        });
    }
    // Fade out hero content on scroll
    const heroContent = document.querySelector('.hero__content');
    const hero = document.querySelector('.hero');
    if (heroContent && hero) {
        let ticking2 = false;
        window.addEventListener('scroll', () => {
            if (!ticking2) {
                requestAnimationFrame(() => {
                    const progress = Math.min(window.scrollY / (hero.offsetHeight * 0.6), 1);
                    heroContent.style.opacity = 1 - progress;
                    heroContent.style.transform = `translateY(${progress * 40}px)`;
                    ticking2 = false;
                });
                ticking2 = true;
            }
        });
    }

    // AJAX filter — no full page reload
    const form = document.getElementById('showcase-form');
    const results = document.getElementById('showcase-results');

    async function fetchResults(url) {
        results.style.opacity = '0.4';
        results.style.transition = 'opacity .2s';
        try {
            const res = await fetch(url, { headers: { 'X-Showcase-Fetch': '1' } });
            const html = await res.text();
            results.innerHTML = html;
            // re-bind pagination links
            bindPagination();
        } finally {
            results.style.opacity = '1';
        }
        history.pushState(null, '', url);
        // update active pill styles
        const params = new URLSearchParams(new URL(url).search);
        updatePills(params);
    }

    function buildUrl(overrides = {}) {
        const params = new URLSearchParams();
        const q = form.querySelector('input[name="q"]').value.trim();
        if (q) params.set('q', q);
        // current active values
        const activeProgram = form.querySelector('.pill-program.pill-active');
        const activeSort    = form.querySelector('.pill-sort.pill-active');
        if (activeProgram) params.set('program', activeProgram.dataset.val);
        if (activeSort)    params.set('sort', activeSort.dataset.val);
        // apply overrides
        Object.entries(overrides).forEach(([k, v]) => v ? params.set(k, v) : params.delete(k));
        return '{{ route("showcase.index") }}?' + params.toString();
    }

    function updatePills(params) {
        form.querySelectorAll('.pill-program').forEach(btn => {
            const active = (params.get('program') ?? '') === btn.dataset.val;
            applyPillStyle(btn, active);
        });
        form.querySelectorAll('.pill-sort').forEach(btn => {
            const active = (params.get('sort') ?? 'updated') === btn.dataset.val;
            applyPillStyle(btn, active);
        });
    }

    function applyPillStyle(btn, active) {
        btn.style.background = active ? '#e84040' : 'transparent';
        btn.style.color      = active ? '#fff' : 'rgba(245,240,232,.6)';
        btn.style.borderColor = active ? '#e84040' : 'rgba(245,240,232,.2)';
        if (active) btn.classList.add('pill-active');
        else btn.classList.remove('pill-active');
    }

    function bindPagination() {
        results.querySelectorAll('a[href]').forEach(a => {
            // only pagination links (contain ?page=)
            if (!a.href.includes('page=')) return;
            a.addEventListener('click', e => {
                e.preventDefault();
                fetchResults(a.href);
            });
        });
    }

    // pill clicks
    form.querySelectorAll('.pill-program').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            fetchResults(buildUrl({ program: btn.dataset.val, sort: null }));
        });
    });
    form.querySelectorAll('.pill-sort').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            fetchResults(buildUrl({ sort: btn.dataset.val }));
        });
    });

    // search submit
    form.addEventListener('submit', e => {
        e.preventDefault();
        fetchResults(buildUrl());
    });

    bindPagination();

    // ── Skill tag click → filter showcase ──
    window.filterBySkill = function(skill) {
        const input = document.querySelector('input[name="q"]');
        if (input) input.value = skill;
        fetchResults(buildUrl({ q: skill }));
        document.getElementById('showcase-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    // ── Program button click → filter showcase ──
    window.filterByProgram = function(program) {
        // activate the matching pill
        document.querySelectorAll('.pill-program').forEach(btn => {
            const active = btn.dataset.val === program;
            btn.style.background  = active ? '#e84040' : 'transparent';
            btn.style.color       = active ? '#fff' : 'rgba(245,240,232,.6)';
            btn.style.borderColor = active ? '#e84040' : 'rgba(245,240,232,.2)';
            if (active) btn.classList.add('pill-active');
            else btn.classList.remove('pill-active');
        });
        fetchResults(buildUrl({ program }));
        document.getElementById('showcase-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
    };
})();

// ── Stat counter animation ──
(function () {
    function animateCount(el) {
        const target = parseInt(el.dataset.target, 10);
        if (!target) return;
        const duration = 1400;
        const start = performance.now();
        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const ease = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.floor(ease * target).toLocaleString();
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target.toLocaleString();
        }
        requestAnimationFrame(step);
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCount(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.stat-num').forEach(el => observer.observe(el));
})();
</script>
@endpush

@endsection

@push('styles')
<style>
    /* ── Shared section helpers ── */
    .section-eyebrow {
        font-family: 'Space Mono', monospace;
        font-size: .6rem; letter-spacing: .2em;
        color: #e84040; margin-bottom: .6rem;
    }
    .section-title {
        font-family: 'DM Serif Display', serif;
        font-size: clamp(1.6rem, 3.5vw, 2.4rem);
        font-weight: 400; line-height: 1.1;
        margin-bottom: 2.5rem;
    }

    /* ── Stats strip ── */
    .stats-strip {
        background: rgba(255,255,255,.025);
        border-top: 1px solid rgba(245,240,232,.07);
        border-bottom: 1px solid rgba(245,240,232,.07);
    }
    .stats-inner {
        max-width: 1100px; margin: 0 auto;
        padding: 2.5rem 5vw;
        display: flex; align-items: center;
        justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;
    }
    .stat-item { display: flex; flex-direction: column; align-items: center; gap: .3rem; flex: 1; min-width: 120px; }
    .stat-num {
        font-family: 'Space Mono', monospace;
        font-size: clamp(2rem, 4vw, 2.8rem);
        font-weight: 700; color: #f5f0e8; line-height: 1;
    }
    .stat-label {
        font-family: 'Space Mono', monospace;
        font-size: .58rem; letter-spacing: .14em;
        text-transform: uppercase; color: rgba(245,240,232,.4);
    }
    .stat-divider { width: 1px; height: 48px; background: rgba(245,240,232,.1); flex-shrink: 0; }
    @media(max-width:600px) { .stat-divider { display: none; } }

    /* ── How it works ── */
    .hiw-section { padding: 100px 5vw; }
    .hiw-inner { max-width: 1000px; margin: 0 auto; }
    .hiw-steps { display: flex; align-items: stretch; gap: 0; flex-wrap: wrap; }
    .hiw-step {
        flex: 1; min-width: 220px;
        padding: 2rem 1.5rem;
        background: rgba(255,255,255,.025);
        border: 1.5px solid rgba(245,240,232,.08);
        border-radius: .75rem;
        transition: border-color .25s, background .25s, transform .25s;
    }
    .hiw-step:hover { border-color: rgba(232,64,64,.3); background: rgba(255,255,255,.045); transform: translateY(-4px); }
    .hiw-step__num { font-family: 'Space Mono', monospace; font-size: .6rem; letter-spacing: .15em; color: #e84040; margin-bottom: 1rem; }
    .hiw-step__icon { font-size: 1.6rem; color: rgba(245,240,232,.7); margin-bottom: 1rem; }
    .hiw-step__title { font-family: 'DM Serif Display', serif; font-size: 1.2rem; margin-bottom: .6rem; }
    .hiw-step__desc { font-size: .82rem; color: rgba(245,240,232,.55); line-height: 1.7; }
    .hiw-step__connector {
        display: flex; align-items: center; justify-content: center;
        padding: 0 1rem; font-size: 1.2rem;
        color: rgba(245,240,232,.2); flex-shrink: 0;
    }
    @media(max-width:700px) { .hiw-step__connector { display: none; } .hiw-step { min-width: 100%; } }

    /* ── Featured ── */
    .featured-section { padding: 100px 5vw; border-top: 1px solid rgba(245,240,232,.06); }
    .featured-inner { max-width: 1400px; margin: 0 auto; }
    .featured-grid { display: grid; grid-template-columns: 1.6fr 1fr 1fr; gap: 20px; }
    @media(max-width:900px) { .featured-grid { grid-template-columns: 1fr; } }
    .featured-card {
        display: flex; flex-direction: column; gap: 14px;
        padding: 28px; border-radius: .75rem;
        background: rgba(255,255,255,.03);
        border: 1.5px solid rgba(245,240,232,.1);
        color: #f5f0e8; position: relative; overflow: hidden;
        transition: border-color .25s, background .25s, transform .25s, box-shadow .25s;
    }
    .featured-card::before {
        content: ''; position: absolute; inset: 0;
        background: radial-gradient(circle at 50% 0%, rgba(232,64,64,.08) 0%, transparent 65%);
        opacity: 0; transition: opacity .3s;
    }
    .featured-card:hover { border-color: rgba(232,64,64,.45); background: rgba(255,255,255,.055); transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,.3); }
    .featured-card:hover::before { opacity: 1; }
    .featured-card--hero { grid-row: span 1; }
    .featured-card__badge {
        position: absolute; top: 16px; right: 16px;
        background: rgba(232,64,64,.15); color: #e84040;
        border: 1px solid rgba(232,64,64,.3);
        border-radius: 999px; padding: .2rem .7rem;
        font-family: 'Space Mono', monospace; font-size: .58rem;
        letter-spacing: .08em; display: flex; align-items: center; gap: 5px;
    }
    .featured-card__top { display: flex; align-items: center; gap: 12px; }
    .featured-card__avatar {
        width: 48px; height: 48px; border-radius: 50%;
        object-fit: cover; border: 2px solid rgba(232,64,64,.4); flex-shrink: 0;
    }
    .featured-card__avatar--initials {
        background: rgba(232,64,64,.15); color: #e84040;
        font-family: 'Space Mono', monospace; font-size: .8rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
    }
    .featured-card__name { font-weight: 700; font-size: 1rem; }
    .featured-card__program { font-size: .6rem; font-family: 'Space Mono', monospace; color: rgba(245,240,232,.4); letter-spacing: .08em; margin-top: 2px; }
    .featured-card__bio { font-size: .82rem; color: rgba(245,240,232,.55); line-height: 1.6; }
    .featured-card__tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .featured-card__tag {
        padding: .2rem .6rem; background: rgba(255,255,255,.05);
        border: 1px solid rgba(245,240,232,.1); border-radius: 999px;
        font-size: .58rem; font-family: 'Space Mono', monospace;
        color: rgba(245,240,232,.5); letter-spacing: .06em;
        transition: background .2s, border-color .2s, color .2s;
    }
    .featured-card:hover .featured-card__tag { background: rgba(232,64,64,.1); border-color: rgba(232,64,64,.25); color: #e84040; }
    .featured-card__footer {
        display: flex; gap: 14px; align-items: center;
        font-size: .62rem; font-family: 'Space Mono', monospace;
        color: rgba(245,240,232,.3); margin-top: auto;
        padding-top: 10px; border-top: 1px solid rgba(245,240,232,.06);
    }
    .featured-card__arrow { margin-left: auto; color: rgba(245,240,232,.4); transition: color .2s, transform .2s; }
    .featured-card:hover .featured-card__arrow { color: #e84040; transform: translateX(4px); }

    /* ── Skills cloud ── */
    .skills-section { padding: 100px 5vw; border-top: 1px solid rgba(245,240,232,.06); }
    .skills-inner { max-width: 1000px; margin: 0 auto; }
    .skills-cloud { display: flex; flex-wrap: wrap; gap: 10px; }
    .skill-tag {
        padding: .4rem 1rem !important;
        font-size: .68rem !important;
        cursor: pointer;
        transition: background .2s, border-color .2s, color .2s, transform .15s !important;
    }
    .skill-tag:hover { transform: translateY(-2px) scale(1.04) !important; }

    /* ── Programs spotlight ── */
    .programs-section { padding: 100px 5vw; border-top: 1px solid rgba(245,240,232,.06); }
    .programs-inner { max-width: 900px; margin: 0 auto; }
    .programs-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    @media(max-width:640px) { .programs-grid { grid-template-columns: 1fr; } }
    .program-card {
        padding: 2.5rem 2rem; border-radius: .75rem;
        background: rgba(255,255,255,.025);
        border: 1.5px solid rgba(245,240,232,.08);
        transition: border-color .25s, background .25s, transform .25s, box-shadow .25s;
    }
    .program-card:hover { border-color: rgba(232,64,64,.35); background: rgba(255,255,255,.045); transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,.25); }
    .program-card__icon { font-size: 2rem; color: #e84040; margin-bottom: 1rem; }
    .program-card__label { font-family: 'Space Mono', monospace; font-size: .6rem; letter-spacing: .18em; color: #e84040; margin-bottom: .4rem; }
    .program-card__title { font-family: 'DM Serif Display', serif; font-size: 1.3rem; margin-bottom: .8rem; }
    .program-card__desc { font-size: .82rem; color: rgba(245,240,232,.55); line-height: 1.7; margin-bottom: 1.5rem; }
    .program-card__cta { display: inline-block; }

    /* ── Recently joined ── */
    .recent-section { padding: 100px 5vw; border-top: 1px solid rgba(245,240,232,.06); }
    .recent-inner { max-width: 1400px; margin: 0 auto; }
    .recent-scroll {
        display: flex; gap: 16px; overflow-x: auto; overflow-y: visible;
        padding-bottom: 12px; padding-top: 8px; scroll-snap-type: x mandatory;
    }
    .recent-scroll::-webkit-scrollbar { height: 3px; }
    .recent-scroll::-webkit-scrollbar-thumb { background: #e84040; border-radius: 2px; }
    .recent-card {
        flex-shrink: 0; width: 160px; scroll-snap-align: start;
        display: flex; flex-direction: column; align-items: center;
        gap: 8px; padding: 1.5rem 1rem;
        background: rgba(255,255,255,.025);
        border: 1.5px solid rgba(245,240,232,.08);
        border-radius: .75rem; color: #f5f0e8;
        transition: border-color .25s, background .25s, transform .25s;
        text-align: center;
    }
    .recent-card:hover { border-color: rgba(232,64,64,.3); background: rgba(255,255,255,.05); transform: translateY(-4px); }
    .recent-card__avatar {
        width: 56px; height: 56px; border-radius: 50%;
        object-fit: cover; border: 2px solid rgba(232,64,64,.3);
    }
    .recent-card__avatar--initials {
        width: 56px; height: 56px; border-radius: 50%;
        background: rgba(232,64,64,.15); color: #e84040;
        font-family: 'Space Mono', monospace; font-size: .8rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        border: 2px solid rgba(232,64,64,.3);
    }
    .recent-card__name { font-weight: 700; font-size: .82rem; line-height: 1.3; }
    .recent-card__program { font-family: 'Space Mono', monospace; font-size: .55rem; letter-spacing: .1em; color: rgba(245,240,232,.4); }
    .recent-card__count { font-family: 'Space Mono', monospace; font-size: .58rem; color: rgba(245,240,232,.3); }

    /* ── CTA banner ── */
    .cta-section {
        padding: 120px 5vw;
        border-top: 1px solid rgba(245,240,232,.06);
        text-align: center; position: relative; overflow: hidden;
    }
    .cta-glow {
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        width: 600px; height: 400px;
        background: radial-gradient(ellipse, rgba(232,64,64,.12) 0%, transparent 70%);
        pointer-events: none;
    }
    .cta-title {
        font-family: 'DM Serif Display', serif;
        font-size: clamp(2.2rem, 5vw, 3.5rem);
        font-weight: 400; margin-bottom: 1rem;
        position: relative;
    }
    .cta-sub {
        font-size: .95rem; color: rgba(245,240,232,.55);
        margin-bottom: 2.5rem; position: relative;
    }
    .cta-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; position: relative; }
    .cta-btn {
        padding: .75rem 2rem; border-radius: 999px;
        font-family: 'Space Mono', monospace; font-size: .7rem;
        font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
        transition: opacity .2s, transform .2s, box-shadow .2s;
    }
    .cta-btn--primary { background: #e84040; color: #fff; border: none; }
    .cta-btn--primary:hover { opacity: .88; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(232,64,64,.4); }
    .cta-btn--ghost { background: transparent; color: #f5f0e8; border: 1.5px solid rgba(245,240,232,.25); }
    .cta-btn--ghost:hover { border-color: rgba(245,240,232,.5); background: rgba(245,240,232,.06); transform: translateY(-2px); }
</style>
@endpush
