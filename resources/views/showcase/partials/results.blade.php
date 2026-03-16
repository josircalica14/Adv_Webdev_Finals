<div class="portfolio-grid">
    @forelse($portfolios as $portfolio)
    <a href="{{ route('portfolio.public', $portfolio->user->username) }}" class="portfolio-card">
        <div class="card-top">
            @if($portfolio->user->profile_photo_path)
                <img src="{{ Storage::disk('portfolio')->url($portfolio->user->profile_photo_path) }}" class="card-avatar" alt="{{ $portfolio->user->full_name }}">
            @else
                @php $initials = collect(explode(' ', $portfolio->user->full_name))->map(fn($w) => strtoupper($w[0]))->take(2)->implode(''); @endphp
                <div class="card-avatar card-avatar--initials">{{ $initials }}</div>
            @endif
            <div class="card-meta">
                <div class="card-name">{{ $portfolio->user->full_name }}</div>
                <div class="card-program">{{ $portfolio->user->program }}</div>
            </div>
            <div class="card-arrow">→</div>
        </div>
        @if($portfolio->user->bio)
        <p class="card-bio">{{ Str::limit($portfolio->user->bio, 90) }}</p>
        @endif
        @php
            $tags = $portfolio->items->flatMap(fn($i) => (array)($i->tags ?? []))->unique()->take(4)->values();
        @endphp
        @if($tags->isNotEmpty())
        <div class="card-tags">
            @foreach($tags as $tag)
            <span class="card-tag">{{ $tag }}</span>
            @endforeach
        </div>
        @endif
        <div class="card-footer">
            <span>{{ $portfolio->items->count() }} items</span>
            <span>{{ number_format($portfolio->view_count) }} views</span>
        </div>
    </a>
    @empty
    <div class="no-results">No portfolios found.</div>
    @endforelse
</div>

<div id="showcase-pagination" style="margin-top:2rem">{{ $portfolios->withQueryString()->links() }}</div>

<style>
.portfolio-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 2rem;
}
.portfolio-card {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 24px;
    background: rgba(255,255,255,.03);
    border: 1.5px solid rgba(245,240,232,.08);
    border-radius: 12px;
    text-decoration: none;
    color: #f5f0e8;
    transition: border-color .25s, background .25s, transform .25s;
    position: relative;
    overflow: hidden;
}
.portfolio-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% 0%, rgba(232,64,64,.06) 0%, transparent 60%);
    opacity: 0;
    transition: opacity .3s;
}
.portfolio-card:hover { border-color: rgba(232,64,64,.4); background: rgba(255,255,255,.05); transform: translateY(-3px); }
.portfolio-card:hover::before { opacity: 1; }
.card-top { display: flex; align-items: center; gap: 12px; }
.card-avatar {
    width: 44px; height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 1.5px solid rgba(245,240,232,.15);
    flex-shrink: 0;
}
.card-avatar--initials {
    background: rgba(232,64,64,.15);
    color: #e84040;
    font-family: 'Space Mono', monospace;
    font-size: .75rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
}
.card-meta { flex: 1; min-width: 0; }
.card-name { font-weight: 700; font-size: .95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.card-program { font-size: .6rem; font-family: 'Space Mono', monospace; color: rgba(245,240,232,.4); letter-spacing: .08em; margin-top: 2px; }
.card-arrow { color: rgba(245,240,232,.2); font-size: 1rem; transition: color .2s, transform .2s; flex-shrink: 0; }
.portfolio-card:hover .card-arrow { color: #e84040; transform: translateX(4px); }
.card-bio { font-size: .8rem; color: rgba(245,240,232,.55); line-height: 1.6; }
.card-tags { display: flex; flex-wrap: wrap; gap: 6px; }
.card-tag {
    padding: .2rem .6rem;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(245,240,232,.1);
    border-radius: 999px;
    font-size: .58rem;
    font-family: 'Space Mono', monospace;
    color: rgba(245,240,232,.5);
    letter-spacing: .06em;
    transition: background .2s, border-color .2s, color .2s;
}
.card-tag:hover {
    background: rgba(232,64,64,.12);
    border-color: rgba(232,64,64,.3);
    color: #e84040;
}
.card-footer {
    display: flex;
    gap: 16px;
    font-size: .62rem;
    font-family: 'Space Mono', monospace;
    color: rgba(245,240,232,.3);
    margin-top: auto;
    padding-top: 4px;
    border-top: 1px solid rgba(245,240,232,.06);
}
.no-results {
    grid-column: 1/-1;
    text-align: center;
    padding: 80px;
    color: rgba(245,240,232,.3);
    font-family: 'Space Mono', monospace;
    font-size: .75rem;
    letter-spacing: .1em;
}
</style>
