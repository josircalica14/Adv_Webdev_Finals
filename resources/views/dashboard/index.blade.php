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

{{-- Portfolio Score Card --}}
@php
    $s = $score['total'];
    $color = $s >= 80 ? '#22c55e' : ($s >= 50 ? '#f59e0b' : '#e84040');
    $label = $s >= 80 ? 'Great' : ($s >= 50 ? 'Good' : 'Needs Work');
@endphp
<div style="background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;padding:24px;margin-bottom:2.5rem">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:12px">
        <div>
            <div style="font-family:'DM Serif Display',serif;font-size:1.15rem;margin-bottom:2px">Portfolio Score</div>
            <div style="font-size:.75rem;color:rgba(245,240,232,.45)">Complete your profile to rank higher in the showcase</div>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <div style="font-size:2.2rem;font-weight:700;font-family:'Space Mono',monospace;color:{{ $color }}">{{ $s }}<span style="font-size:1rem;opacity:.5">/100</span></div>
            <span style="padding:.3rem .8rem;background:{{ $color }}22;border:1.5px solid {{ $color }}55;border-radius:999px;font-size:.7rem;font-family:'Space Mono',monospace;color:{{ $color }};text-transform:uppercase;letter-spacing:.08em">{{ $label }}</span>
        </div>
    </div>

    {{-- Progress bar --}}
    <div style="height:6px;background:rgba(255,255,255,.07);border-radius:999px;margin-bottom:20px;overflow:hidden">
        <div style="height:100%;width:{{ $s }}%;background:{{ $color }};border-radius:999px;transition:width .6s ease"></div>
    </div>

    {{-- Checklist --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px">
        @foreach($score['checks'] as $check)
        <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(255,255,255,.02);border:1px solid rgba(245,240,232,.07);border-radius:.5rem">
            <span style="flex-shrink:0;width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.65rem;
                background:{{ $check['done'] ? '#22c55e22' : 'rgba(255,255,255,.05)' }};
                border:1.5px solid {{ $check['done'] ? '#22c55e' : 'rgba(245,240,232,.15)' }};
                color:{{ $check['done'] ? '#22c55e' : 'rgba(245,240,232,.3)' }}">
                <i class="fas {{ $check['done'] ? 'fa-check' : 'fa-xmark' }}"></i>
            </span>
            <div style="flex:1;min-width:0">
                <div style="font-size:.75rem;color:{{ $check['done'] ? '#f5f0e8' : 'rgba(245,240,232,.45)' }}">{{ $check['label'] }}</div>
            </div>
            <span style="font-size:.65rem;font-family:'Space Mono',monospace;color:{{ $check['done'] ? '#22c55e' : 'rgba(245,240,232,.25)' }}">+{{ $check['points'] }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- AI Insights --}}
<div id="ai-insights-card" style="background:rgba(255,255,255,.03);border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;padding:24px;margin-bottom:2.5rem">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
        <div style="display:flex;align-items:center;gap:10px">
            <span style="width:32px;height:32px;background:rgba(139,92,246,.15);border:1.5px solid rgba(139,92,246,.4);border-radius:50%;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-wand-magic-sparkles" style="font-size:13px;color:#a78bfa"></i>
            </span>
            <div>
                <div style="font-family:'DM Serif Display',serif;font-size:1.1rem">AI Insights</div>
                <div style="font-size:.72rem;color:rgba(245,240,232,.4)">Powered by Groq · Llama 3.3 70B</div>
            </div>
        </div>
        <button id="ai-analyze-btn" onclick="loadAiFeedback()"
            style="display:flex;align-items:center;gap:8px;padding:.45rem 1.1rem;background:rgba(139,92,246,.15);border:1.5px solid rgba(139,92,246,.4);border-radius:999px;color:#a78bfa;font-size:.75rem;font-family:'Space Mono',monospace;cursor:pointer;transition:all .2s">
            <i class="fas fa-sparkles"></i> Analyze My Portfolio
        </button>
    </div>

    <div id="ai-idle" style="text-align:center;padding:28px 0;color:rgba(245,240,232,.3);font-size:.8rem">
        <i class="fas fa-robot" style="font-size:28px;margin-bottom:10px;display:block;opacity:.3"></i>
        Click "Analyze My Portfolio" to get AI-powered feedback
    </div>

    <div id="ai-loading" style="display:none;text-align:center;padding:28px 0">
        <div style="display:inline-flex;gap:6px;align-items:center;color:rgba(245,240,232,.4);font-size:.8rem">
            <span class="ai-dot"></span><span class="ai-dot" style="animation-delay:.2s"></span><span class="ai-dot" style="animation-delay:.4s"></span>
            <span style="margin-left:6px">Analyzing your portfolio…</span>
        </div>
    </div>

    <div id="ai-result" style="display:none"></div>
    <div id="ai-error" style="display:none;color:#e84040;font-size:.8rem;padding:12px 0"></div>
</div>

<style>
.ai-dot{width:7px;height:7px;background:#a78bfa;border-radius:50%;animation:aiPulse 1s infinite}
@keyframes aiPulse{0%,100%{opacity:.3;transform:scale(.8)}50%{opacity:1;transform:scale(1)}}
.ai-tag{display:inline-block;padding:.2rem .6rem;background:rgba(139,92,246,.12);border:1px solid rgba(139,92,246,.3);border-radius:999px;font-size:.68rem;color:#a78bfa;font-family:'Space Mono',monospace}
</style>

<script>
async function loadAiFeedback() {
    const btn = document.getElementById('ai-analyze-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing…';
    document.getElementById('ai-idle').style.display    = 'none';
    document.getElementById('ai-loading').style.display = 'block';
    document.getElementById('ai-result').style.display  = 'none';
    document.getElementById('ai-error').style.display   = 'none';

    try {
        const res  = await fetch('{{ route("dashboard.ai.feedback") }}', {
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        const data = await res.json();

        document.getElementById('ai-loading').style.display = 'none';

        if (data.error) {
            document.getElementById('ai-error').style.display = 'block';
            document.getElementById('ai-error').textContent   = data.error;
        } else {
            const strengths    = (data.strengths    || []).map(s => `<li style="margin-bottom:4px">${s}</li>`).join('');
            const improvements = (data.improvements || []).map(s => `<li style="margin-bottom:4px">${s}</li>`).join('');
            document.getElementById('ai-result').style.display = 'block';
            document.getElementById('ai-result').innerHTML = `
                <div style="padding:14px;background:rgba(139,92,246,.07);border:1px solid rgba(139,92,246,.2);border-radius:.6rem;margin-bottom:14px;font-size:.82rem;line-height:1.6;color:rgba(245,240,232,.85)">
                    ${data.overall || ''}
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
                    <div style="padding:14px;background:rgba(34,197,94,.06);border:1px solid rgba(34,197,94,.2);border-radius:.6rem">
                        <div style="font-size:.7rem;color:#22c55e;font-family:'Space Mono',monospace;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px"><i class="fas fa-thumbs-up"></i> Strengths</div>
                        <ul style="margin:0;padding-left:16px;font-size:.78rem;color:rgba(245,240,232,.75);line-height:1.6">${strengths}</ul>
                    </div>
                    <div style="padding:14px;background:rgba(245,158,11,.06);border:1px solid rgba(245,158,11,.2);border-radius:.6rem">
                        <div style="font-size:.7rem;color:#f59e0b;font-family:'Space Mono',monospace;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px"><i class="fas fa-arrow-up"></i> Improvements</div>
                        <ul style="margin:0;padding-left:16px;font-size:.78rem;color:rgba(245,240,232,.75);line-height:1.6">${improvements}</ul>
                    </div>
                </div>
                ${data.tip ? `<div style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(245,240,232,.1);border-radius:.6rem;font-size:.78rem;color:rgba(245,240,232,.7)"><i class="fas fa-lightbulb" style="color:#f59e0b;margin-top:2px;flex-shrink:0"></i><span>${data.tip}</span></div>` : ''}
            `;
        }
    } catch (e) {
        document.getElementById('ai-loading').style.display = 'none';
        document.getElementById('ai-error').style.display   = 'block';
        document.getElementById('ai-error').textContent     = 'Something went wrong. Please try again.';
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-rotate-right"></i> Re-analyze';
}
</script>

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
