<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Make a Name')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=DM+Serif+Display&family=Instrument+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .fa-x-twitter::before { content: "\e61b"; }
    </style>
    <style>
        :root { --black:#181818;--white:#f5f0e8;--accent:#e84040;--mono:'Space Mono',monospace;--serif:'DM Serif Display',serif;--sans:'Instrument Sans',sans-serif; }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body{background:var(--black);color:var(--white);font-family:var(--sans);overflow-x:hidden}
        a{color:inherit;text-decoration:none}
        ::-webkit-scrollbar{width:4px}::-webkit-scrollbar-track{background:var(--black)}::-webkit-scrollbar-thumb{background:var(--accent);border-radius:2px}
    </style>
    <style>
        /* ── Top Nav ── */
        .top-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 2.5rem; height: 60px;
            background: rgba(24,24,24,.85);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(245,240,232,.07);
            transition: background .3s, box-shadow .3s;
        }
        .top-nav.scrolled {
            background: rgba(24,24,24,.97);
            box-shadow: 0 4px 24px rgba(0,0,0,.4);
        }
        .top-nav__logo {
            font-family: var(--mono); font-size: .9rem; letter-spacing: .14em;
            font-weight: 700; color: var(--white);
            display: flex; align-items: center; gap: .6rem;
        }
        .top-nav__logo-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 8px var(--accent);
            flex-shrink: 0;
        }
        .nav-logo-img {
            filter: drop-shadow(0 0 8px var(--accent)) drop-shadow(0 0 18px var(--accent));
            animation: logoGlow 2.5s ease-in-out infinite alternate;
        }
        @keyframes logoGlow {
            from { filter: drop-shadow(0 0 6px var(--accent)) drop-shadow(0 0 14px var(--accent)); }
            to   { filter: drop-shadow(0 0 12px var(--accent)) drop-shadow(0 0 28px var(--accent)); }
        }
        .top-nav__links {
            display: flex; align-items: center;
            gap: .25rem;
            font-family: var(--mono); font-size: .82rem;
            letter-spacing: .1em; text-transform: uppercase;
        }
        .top-nav__links a,
        .top-nav__links .nav-btn {
            position: relative; padding: .45rem .75rem;
            color: rgba(245,240,232,.6);
            border-radius: .35rem;
            transition: color .2s, background .2s;
            background: none; border: none; cursor: pointer;
            font-family: var(--mono); font-size: .82rem;
            letter-spacing: .1em; text-transform: uppercase;
        }
        .top-nav__links a:hover,
        .top-nav__links .nav-btn:hover {
            color: var(--white);
            background: rgba(245,240,232,.06);
        }
        .top-nav__links a.active {
            color: var(--white);
        }
        .top-nav__links a.active::after {
            content: ''; position: absolute; bottom: 2px; left: .75rem; right: .75rem;
            height: 2px; background: var(--accent); border-radius: 1px;
        }
        .top-nav__cta {
            background: var(--accent) !important;
            color: var(--white) !important;
            padding: .4rem 1.1rem !important;
            border-radius: 999px !important;
            font-weight: 700 !important;
            transition: opacity .2s, transform .2s !important;
        }
        .top-nav__cta:hover {
            opacity: .88 !important;
            transform: translateY(-1px) !important;
            background: var(--accent) !important;
        }
        /* Hamburger */
        .nav-hamburger {
            display: none; flex-direction: column; gap: 5px;
            background: none; border: none; cursor: pointer; padding: .4rem;
        }
        .nav-hamburger span {
            display: block; width: 22px; height: 2px;
            background: var(--white); border-radius: 2px;
            transition: transform .3s, opacity .3s;
        }
        .nav-hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .nav-hamburger.open span:nth-child(2) { opacity: 0; }
        .nav-hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
        /* Mobile drawer */
        .nav-mobile {
            display: none; position: fixed; top: 60px; left: 0; right: 0;
            background: rgba(24,24,24,.97); backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(245,240,232,.07);
            flex-direction: column; padding: 1rem 2rem 1.5rem; gap: .25rem;
            z-index: 99;
        }
        .nav-mobile.open { display: flex; }
        .nav-mobile a, .nav-mobile .nav-btn {
            font-family: var(--mono); font-size: .7rem; letter-spacing: .1em;
            text-transform: uppercase; color: rgba(245,240,232,.7);
            padding: .65rem .5rem; border-bottom: 1px solid rgba(245,240,232,.06);
            background: none; border-left: none; border-right: none; border-top: none;
            cursor: pointer; text-align: left; transition: color .2s;
        }
        .nav-mobile a:hover, .nav-mobile .nav-btn:hover { color: var(--white); }
        .nav-mobile a.active { color: var(--accent); }
        @media(max-width:640px) {
            .top-nav__links { display: none; }
            .nav-hamburger { display: flex; }
        }
    </style>
    <style>
        /* ═══════════════════════════════════════════
           GLOBAL HOVER SYSTEM
           Covers: buttons, links, inputs, cards,
           icon buttons, tags, sidebar links, pills
        ═══════════════════════════════════════════ */

        /* ── Smooth transition baseline ── */
        a, button, input, textarea, select,
        [role="button"], label[style*="cursor:pointer"] {
            transition: color .2s, background .2s, border-color .2s,
                        box-shadow .2s, opacity .2s, transform .2s;
        }

        /* ── Primary CTA buttons (red filled) ── */
        button[type="submit"]:not(.nav-btn):not(.chip),
        a[style*="background:#e84040"],
        a[style*="background:var(--accent)"],
        button[style*="background:#e84040"] {
            position: relative;
            overflow: hidden;
        }
        button[type="submit"]:not(.nav-btn):not(.chip):hover,
        a[style*="background:#e84040"]:hover,
        button[style*="background:#e84040"]:hover {
            opacity: .88;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(232,64,64,.35);
        }
        button[type="submit"]:not(.nav-btn):not(.chip):active,
        a[style*="background:#e84040"]:active,
        button[style*="background:#e84040"]:active {
            transform: translateY(0);
            box-shadow: none;
        }

        /* ── Ghost / outline buttons ── */
        a[style*="border:1.5px solid rgba(245,240,232"],
        button[style*="border:1.5px solid rgba(245,240,232"] {
            position: relative;
        }
        a[style*="border:1.5px solid rgba(245,240,232"]:hover,
        button[style*="border:1.5px solid rgba(245,240,232"]:hover {
            border-color: rgba(245,240,232,.5) !important;
            background: rgba(245,240,232,.07) !important;
            transform: translateY(-1px);
        }

        /* ── Form inputs & textareas ── */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="url"],
        input[type="date"],
        textarea,
        select {
            transition: border-color .2s, box-shadow .2s, background .2s;
            outline: none;
        }
        input[type="text"]:hover,
        input[type="email"]:hover,
        input[type="password"]:hover,
        input[type="url"]:hover,
        input[type="date"]:hover,
        textarea:hover,
        select:hover {
            border-color: rgba(245,240,232,.3) !important;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="url"]:focus,
        input[type="date"]:focus,
        textarea:focus,
        select:focus {
            border-color: var(--accent) !important;
            box-shadow: 0 0 0 3px rgba(232,64,64,.12);
        }

        /* ── Dashboard stat cards ── */
        div[style*="border:1.5px solid rgba(245,240,232,.1)"][style*="border-radius:.75rem"] {
            transition: border-color .25s, background .25s, transform .25s, box-shadow .25s;
        }
        div[style*="border:1.5px solid rgba(245,240,232,.1)"][style*="border-radius:.75rem"]:hover {
            border-color: rgba(245,240,232,.22) !important;
            background: rgba(255,255,255,.05) !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(0,0,0,.25);
        }

        /* ── Dashboard portfolio item rows ── */
        div[style*="border:1.5px solid rgba(245,240,232,.1)"][style*="align-items:flex-start"] {
            transition: border-color .2s, background .2s;
        }
        div[style*="border:1.5px solid rgba(245,240,232,.1)"][style*="align-items:flex-start"]:hover {
            border-color: rgba(245,240,232,.2) !important;
            background: rgba(255,255,255,.05) !important;
        }

        /* ── Icon circle buttons (edit/delete) ── */
        a[style*="border-radius:50%"],
        button[style*="border-radius:50%"]:not(#chatbot-toggle):not(#chatbot-send) {
            transition: background .2s, border-color .2s, transform .2s, box-shadow .2s;
        }
        a[style*="border-radius:50%"]:hover {
            background: rgba(245,240,232,.12) !important;
            border-color: rgba(245,240,232,.35) !important;
            transform: scale(1.1);
        }
        button[style*="border-radius:50%"][style*="background:rgba(232,64,64"]:hover {
            background: rgba(232,64,64,.25) !important;
            border-color: rgba(232,64,64,.6) !important;
            transform: scale(1.1);
        }

        /* ── Settings account links ── */
        a[style*="display:flex"][style*="border:1.5px solid rgba(245,240,232,.08)"] {
            transition: background .2s, border-color .2s, transform .2s;
        }
        a[style*="display:flex"][style*="border:1.5px solid rgba(245,240,232,.08)"]:hover {
            background: rgba(255,255,255,.05) !important;
            border-color: rgba(245,240,232,.2) !important;
            transform: translateX(4px);
        }

        /* ── Dashboard sidebar links ── */
        .dash-sidebar a {
            transition: background .2s, color .2s, border-left-color .2s, transform .15s;
        }
        .dash-sidebar a:hover {
            transform: translateX(3px);
        }

        /* ── Portfolio view item cards ── */
        div[style*="transition:all .3s"]:hover {
            border-color: rgba(245,240,232,.22) !important;
            background: rgba(255,255,255,.06) !important;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,.3);
        }

        /* ── Social icon links on portfolio view ── */
        a[style*="border-radius:50%"][style*="width:38px"] {
            transition: background .2s, color .2s, border-color .2s, transform .2s !important;
        }
        a[style*="border-radius:50%"][style*="width:38px"]:hover {
            transform: translateY(-2px) scale(1.08) !important;
        }

        /* ── Admin panel portfolio rows ── */
        div[style*="border:1.5px solid rgba(245,240,232,.1)"][style*="align-items:center"] {
            transition: border-color .2s, background .2s;
        }
        div[style*="border:1.5px solid rgba(245,240,232,.1)"][style*="align-items:center"]:hover {
            border-color: rgba(245,240,232,.2) !important;
            background: rgba(255,255,255,.05) !important;
        }

        /* ── Admin "View" / "Notify" small buttons ── */
        a[style*="border-radius:999px"][style*="font-size:.65rem"],
        button[style*="border-radius:999px"][style*="font-size:.65rem"] {
            transition: background .2s, border-color .2s, color .2s, transform .15s;
        }
        a[style*="border-radius:999px"][style*="font-size:.65rem"]:hover {
            background: rgba(245,240,232,.1) !important;
            border-color: rgba(245,240,232,.35) !important;
            transform: translateY(-1px);
        }
        button[style*="border-radius:999px"][style*="font-size:.65rem"]:hover {
            background: rgba(232,64,64,.2) !important;
            border-color: rgba(232,64,64,.5) !important;
            transform: translateY(-1px);
        }

        /* ── Chatbot toggle button ── */
        #chatbot-toggle:hover {
            transform: scale(1.1) !important;
            box-shadow: 0 6px 28px rgba(232,64,64,.55) !important;
        }
        #chatbot-send:hover {
            opacity: .85;
            transform: scale(1.08);
        }

        /* ── Showcase search button ── */
        .search-btn:hover {
            opacity: 1 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(232,64,64,.35);
        }

        /* ── Showcase filter pills ── */
        .pill {
            transition: background .2s, border-color .2s, color .2s, transform .15s !important;
        }
        .pill:hover {
            transform: translateY(-1px);
        }

        /* ── Hero CTA ── */
        .hero__cta:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 24px rgba(232,64,64,.3) !important;
        }

        /* ── Customize page theme/layout pills ── */
        .theme-pill:hover, .layout-pill:hover {
            border-color: rgba(245,240,232,.4) !important;
            color: var(--white) !important;
            transform: translateY(-1px);
        }

        /* ── Register program cards ── */
        .prog-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,.2);
        }

        /* ── Color swatches ── */
        .color-swatch:hover {
            border-color: rgba(245,240,232,.5) !important;
            transform: scale(1.08);
        }

        /* ── Pagination links ── */
        nav[role="navigation"] a,
        .pagination a {
            transition: background .2s, border-color .2s, color .2s, transform .15s;
        }
        nav[role="navigation"] a:hover,
        .pagination a:hover {
            transform: translateY(-1px);
        }

        /* ── Upload label button ── */
        label[style*="background:#e84040"]:hover {
            opacity: .88;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(232,64,64,.35);
            cursor: pointer;
        }

        /* ── Inline text links (e.g. "Add your first item", "Register") ── */
        a[style*="color:#e84040"] {
            position: relative;
            transition: opacity .2s;
        }
        a[style*="color:#e84040"]:hover {
            opacity: .8;
            text-decoration: underline;
        }

        /* ── Reduce motion for accessibility ── */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                transition-duration: .01ms !important;
                animation-duration: .01ms !important;
            }
        }
    </style>
    @yield('styles')
    @stack('styles')
</head>
<body>

<nav class="top-nav" id="topNav">
    <a href="{{ route('showcase.index') }}" class="top-nav__logo">
        <img src="{{ asset('website_logo.png') }}" alt="Make a Name" class="nav-logo-img" style="height:64px;width:auto;display:block;">
        <span>MAKE A NAME.</span>
    </a>

    {{-- Desktop links --}}
    <div class="top-nav__links">
        <a href="{{ route('showcase.index') }}" class="{{ request()->routeIs('showcase.*') ? 'active' : '' }}">Showcase</a>
        @auth
            <a href="{{ route('dashboard.index') }}" class="{{ request()->routeIs('dashboard.*') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ auth()->user()->username ? route('portfolio.public', auth()->user()->username) : route('dashboard.profile.show') }}">My Portfolio</a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit" class="nav-btn">Logout</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">Login</a>
            <a href="{{ route('register') }}" class="top-nav__cta">Register</a>
        @endauth
    </div>

    {{-- Hamburger --}}
    <button class="nav-hamburger" id="navHamburger" aria-label="Toggle menu" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>
</nav>

{{-- Mobile drawer --}}
<div class="nav-mobile" id="navMobile">
    <a href="{{ route('showcase.index') }}" class="{{ request()->routeIs('showcase.*') ? 'active' : '' }}">Showcase</a>
    @auth
        <a href="{{ route('dashboard.index') }}" class="{{ request()->routeIs('dashboard.*') ? 'active' : '' }}">Dashboard</a>
        <a href="{{ auth()->user()->username ? route('portfolio.public', auth()->user()->username) : route('dashboard.profile.show') }}">My Portfolio</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-btn">Logout</button>
        </form>
    @else
        <a href="{{ route('login') }}">Login</a>
        <a href="{{ route('register') }}" style="color:var(--accent)">Register</a>
    @endauth
</div>

<main style="padding-top:60px">
    @yield('content')
</main>

<footer class="site-footer">
    <div class="footer-inner">

        {{-- Top row --}}
        <div class="footer-top">
            <div class="footer-brand">
                <div class="footer-logo">
                    <span class="footer-logo__dot"></span>
                    PORTFOLIO PLATFORM
                </div>
                <p class="footer-tagline">A showcase for BSIT &amp; CSE students to build, own, and share their work.</p>
            </div>
            <div class="footer-links">
                <div class="footer-col">
                    <div class="footer-col__heading">Platform</div>
                    <a href="{{ route('showcase.index') }}">Showcase</a>
                    @auth
                        <a href="{{ route('dashboard.index') }}">Dashboard</a>
                        <a href="{{ route('dashboard.items.create') }}">Add Item</a>
                        <a href="{{ route('dashboard.customize.show') }}">Customize</a>
                    @else
                        <a href="{{ route('login') }}">Login</a>
                        <a href="{{ route('register') }}">Register</a>
                    @endauth
                </div>
                <div class="footer-col">
                    <div class="footer-col__heading">Account</div>
                    @auth
                        <a href="{{ route('dashboard.profile.show') }}">Profile</a>
                        <a href="{{ route('dashboard.settings.show') }}">Settings</a>
                        <a href="{{ route('dashboard.export.pdf') }}">Export PDF</a>
                        <form method="POST" action="{{ route('logout') }}" style="display:inline">
                            @csrf
                            <button type="submit" class="footer-logout">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('register') }}">Create Account</a>
                        <a href="{{ route('password.request') }}">Forgot Password</a>
                    @endauth
                </div>
            </div>
        </div>

        {{-- Bottom row --}}
        <div class="footer-bottom">
            <span>© {{ date('Y') }} Portfolio Platform. Built for BSIT &amp; CSE students.</span>
            <span class="footer-bottom__right">
                Made with <span style="color:#e84040">♥</span> using Laravel
            </span>
        </div>

    </div>
</footer>

<style>
    .site-footer {
        background: rgba(255,255,255,.02);
        border-top: 1px solid rgba(245,240,232,.07);
        margin-top: auto;
    }
    .footer-inner {
        max-width: 1200px; margin: 0 auto;
        padding: 4rem 5vw 2rem;
    }
    .footer-top {
        display: flex; gap: 4rem;
        justify-content: space-between;
        flex-wrap: wrap;
        padding-bottom: 3rem;
        border-bottom: 1px solid rgba(245,240,232,.07);
        margin-bottom: 2rem;
    }
    .footer-brand { max-width: 300px; }
    .footer-logo {
        font-family: 'Space Mono', monospace;
        font-size: .7rem; font-weight: 700;
        letter-spacing: .14em; color: #f5f0e8;
        display: flex; align-items: center; gap: .6rem;
        margin-bottom: 1rem;
    }
    .footer-logo__dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: #e84040; box-shadow: 0 0 8px #e84040;
        flex-shrink: 0;
    }
    .footer-tagline {
        font-size: .8rem; color: rgba(245,240,232,.4);
        line-height: 1.7;
    }
    .footer-links { display: flex; gap: 3rem; flex-wrap: wrap; }
    .footer-col { display: flex; flex-direction: column; gap: .6rem; }
    .footer-col__heading {
        font-family: 'Space Mono', monospace;
        font-size: .58rem; letter-spacing: .16em;
        text-transform: uppercase; color: rgba(245,240,232,.3);
        margin-bottom: .3rem;
    }
    .footer-col a, .footer-logout {
        font-size: .8rem; color: rgba(245,240,232,.55);
        transition: color .2s;
        background: none; border: none; cursor: pointer;
        padding: 0; font-family: inherit; text-align: left;
    }
    .footer-col a:hover, .footer-logout:hover { color: #f5f0e8; }
    .footer-bottom {
        display: flex; justify-content: space-between;
        align-items: center; flex-wrap: wrap; gap: .5rem;
        font-family: 'Space Mono', monospace;
        font-size: .58rem; letter-spacing: .06em;
        color: rgba(245,240,232,.25);
    }
    @media(max-width:640px) {
        .footer-top { flex-direction: column; gap: 2.5rem; }
        .footer-bottom { flex-direction: column; align-items: flex-start; }
    }
</style>

{{-- AI Chatbot Widget --}}
<button id="chatbot-toggle" style="position:fixed;bottom:30px;right:30px;width:56px;height:56px;background:#e84040;color:#fff;border:none;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:22px;box-shadow:0 4px 20px rgba(232,64,64,.4);z-index:9999;transition:all .3s">
    <i class="fas fa-comment-dots" id="chatbot-icon"></i>
</button>

<div id="chatbot-container" style="display:none;position:fixed;bottom:100px;right:30px;width:370px;max-height:560px;background:#1f1f1f;border:1.5px solid rgba(245,240,232,.1);border-radius:.75rem;box-shadow:0 20px 60px rgba(0,0,0,.5);flex-direction:column;z-index:9998;overflow:hidden">
    <div style="padding:14px 18px;background:#181818;border-bottom:1px solid rgba(245,240,232,.1);display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:8px;height:8px;border-radius:50%;background:#4caf50;box-shadow:0 0 6px #4caf50"></div>
            <span style="font-family:'Space Mono',monospace;font-size:.65rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase">AI Assistant</span>
        </div>
        <button id="chatbot-close" style="background:none;border:none;color:rgba(245,240,232,.5);cursor:pointer;font-size:16px;padding:4px;border-radius:50%;transition:all .2s" onmouseover="this.style.color='#e84040'" onmouseout="this.style.color='rgba(245,240,232,.5)'"><i class="fas fa-times"></i></button>
    </div>
    <div id="chatbot-messages" style="flex:1;padding:14px;overflow-y:auto;max-height:340px;background:#181818">
        <div style="margin-bottom:10px">
            <div class="bot-bubble">Hello! I can help you find students, explore portfolios, or navigate the platform. What are you looking for?</div>
            <div class="msg-time">Just now</div>
        </div>
    </div>
    <div id="chatbot-chips" style="padding:10px 14px 0;background:#181818;display:flex;flex-wrap:wrap;gap:6px;flex-shrink:0">
        @auth
        <button class="chip" onclick="chipSend('Show me all students')">👥 All Students</button>
        <button class="chip" onclick="chipSend('Show me BSIT students')">💻 BSIT</button>
        <button class="chip" onclick="chipSend('Show me CSE students')">🔬 CSE</button>
        <button class="chip" onclick="chipSend('How do I add a portfolio item?')">➕ Add Item</button>
        <button class="chip" onclick="chipSend('How do I customize my portfolio?')">🎨 Customize</button>
        <button class="chip" onclick="chipSend('How do I export my portfolio as PDF?')">📄 Export PDF</button>
        <button class="chip" onclick="chipSend('Who works with machine learning?')">🤖 ML Students</button>
        <button class="chip" onclick="chipSend('Who works with mobile development?')">📱 Mobile Dev</button>
        @else
        <button class="chip" onclick="chipSend('Show me all students')">👥 All Students</button>
        <button class="chip" onclick="chipSend('Show me BSIT students')">💻 BSIT</button>
        <button class="chip" onclick="chipSend('Show me CSE students')">🔬 CSE</button>
        <button class="chip" onclick="chipSend('Who works with web development?')">🌐 Web Dev</button>
        <button class="chip" onclick="chipSend('Who works with machine learning?')">🤖 ML Students</button>
        <button class="chip" onclick="chipSend('How do I create an account?')">🔑 Register</button>
        <button class="chip" onclick="chipSend('What is this platform?')">ℹ️ About</button>
        @endauth
    </div>
    <div style="padding:12px;background:#1f1f1f;border-top:1px solid rgba(245,240,232,.1);display:flex;gap:8px;flex-shrink:0;margin-top:8px">
        <input id="chatbot-input" type="text" placeholder="Ask anything..." autocomplete="off"
            style="flex:1;padding:9px 14px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.15);border-radius:999px;color:#f5f0e8;font-size:.8rem;font-family:'Instrument Sans',sans-serif;outline:none;transition:border-color .2s"
            onfocus="this.style.borderColor='#e84040'" onblur="this.style.borderColor='rgba(245,240,232,.15)'">
        <button id="chatbot-send" style="width:40px;height:40px;background:#e84040;color:#fff;border:none;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;transition:all .2s">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>

<style>
.bot-bubble{max-width:85%;padding:10px 14px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.1);border-radius:.5rem;font-size:.8rem;line-height:1.6;font-family:'Instrument Sans',sans-serif;color:#f5f0e8;word-break:break-word}
.bot-bubble a{color:#e84040;text-decoration:underline}
.chat-link{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .65rem;background:rgba(232,64,64,.12);border:1.5px solid rgba(232,64,64,.35);border-radius:999px;color:#e84040 !important;text-decoration:none !important;font-size:.75rem;font-family:'Space Mono',monospace;transition:all .2s;white-space:nowrap;margin:2px 0}
.chat-link:hover{background:rgba(232,64,64,.25);border-color:#e84040;transform:translateY(-1px)}
.chat-link--ext{background:rgba(255,255,255,.06);border-color:rgba(245,240,232,.2);color:rgba(245,240,232,.8) !important}
.chat-link--ext:hover{background:rgba(255,255,255,.12);border-color:rgba(245,240,232,.5);color:#f5f0e8 !important}
.chat-img-wrap{display:block;margin-top:8px}
.chat-img{max-width:100%;border-radius:.5rem;border:1.5px solid rgba(245,240,232,.15);display:block;transition:transform .2s}
.chat-img:hover{transform:scale(1.02)}
.user-bubble{max-width:85%;padding:10px 14px;background:#e84040;border-radius:.5rem;font-size:.8rem;line-height:1.6;font-family:'Instrument Sans',sans-serif;color:#fff;word-break:break-word;margin-left:auto}
.msg-time{font-size:.6rem;color:rgba(245,240,232,.35);margin-top:4px;font-family:'Space Mono',monospace}
.chip{padding:.3rem .75rem;background:rgba(255,255,255,.06);border:1.5px solid rgba(245,240,232,.15);border-radius:999px;color:rgba(245,240,232,.7);font-size:.65rem;font-family:'Space Mono',monospace;cursor:pointer;transition:all .2s;white-space:nowrap}
.chip:hover{background:rgba(232,64,64,.15);border-color:#e84040;color:#f5f0e8}
#chatbot-messages::-webkit-scrollbar{width:3px}
#chatbot-messages::-webkit-scrollbar-thumb{background:#e84040;border-radius:2px}
</style>

<script>
// Force scroll to top on every page load
history.scrollRestoration = 'manual';
window.scrollTo(0, 0);

// Nav scroll effect + hamburger
(function(){
    const nav = document.getElementById('topNav');
    const hamburger = document.getElementById('navHamburger');
    const mobile = document.getElementById('navMobile');

    window.addEventListener('scroll', function() {
        nav.classList.toggle('scrolled', window.scrollY > 10);
    }, { passive: true });

    hamburger.addEventListener('click', function() {
        const isOpen = mobile.classList.toggle('open');
        hamburger.classList.toggle('open', isOpen);
        hamburger.setAttribute('aria-expanded', isOpen);
    });

    // Close mobile menu on link click
    mobile.querySelectorAll('a').forEach(function(a) {
        a.addEventListener('click', function() {
            mobile.classList.remove('open');
            hamburger.classList.remove('open');
            hamburger.setAttribute('aria-expanded', 'false');
        });
    });
})();
</script>

<script>
(function(){
    const toggle   = document.getElementById('chatbot-toggle');
    const box      = document.getElementById('chatbot-container');
    const closeBtn = document.getElementById('chatbot-close');
    const input    = document.getElementById('chatbot-input');
    const send     = document.getElementById('chatbot-send');
    const messages = document.getElementById('chatbot-messages');
    const icon     = document.getElementById('chatbot-icon');
    let open = false;

    function toggleChat() {
        open = !open;
        box.style.display = open ? 'flex' : 'none';
        box.style.flexDirection = 'column';
        icon.className = open ? 'fas fa-times' : 'fas fa-comment-dots';
        if (open) setTimeout(() => input.focus(), 50);
    }

    toggle.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', toggleChat);

    function hideChips() {
        const chips = document.getElementById('chatbot-chips');
        if (chips) chips.style.display = 'none';
    }

    function time() {
        return new Date().toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit',hour12:true});
    }

    function linkify(text) {
        const escaped = text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        // Portfolio links → styled pill button
        let out = escaped.replace(/\/portfolio\/([a-zA-Z0-9_-]+)/g,
            '<a class="chat-link" href="/portfolio/$1"><i class="fas fa-user-circle"></i> /portfolio/$1</a>');
        // Plain http/https URLs → styled link
        out = out.replace(/(https?:\/\/[^\s&]+)/g,
            '<a class="chat-link chat-link--ext" href="$1" target="_blank" rel="noopener"><i class="fas fa-link"></i> $1 <i class="fas fa-arrow-up-right-from-square" style="font-size:.6rem;opacity:.7"></i></a>');
        // Image URLs (.png .jpg .gif .webp .svg) → inline image
        out = out.replace(/chat-link--ext" href="([^"]+\.(png|jpg|jpeg|gif|webp|svg))[^"]*"/g,
            (match, url) => `chat-link--ext chat-link--img" href="${url}"`);
        out = out.replace(/<a class="chat-link chat-link--ext chat-link--img"[^>]+>.*?<\/a>/g, (match) => {
            const url = match.match(/href="([^"]+)"/)?.[1];
            return url ? `<a href="${url}" target="_blank" rel="noopener" class="chat-img-wrap"><img src="${url}" alt="image" class="chat-img"></a>` : match;
        });
        return out;
    }

    function addMsg(text, who) {
        const wrap = document.createElement('div');
        wrap.style.cssText = `margin-bottom:10px;display:flex;flex-direction:column;align-items:${who==='user'?'flex-end':'flex-start'}`;
        const bubble = document.createElement('div');
        bubble.className = who === 'user' ? 'user-bubble' : 'bot-bubble';
        if (who === 'bot') { bubble.innerHTML = linkify(text); } else { bubble.textContent = text; }
        const ts = document.createElement('div');
        ts.className = 'msg-time';
        ts.style.textAlign = who === 'user' ? 'right' : 'left';
        ts.textContent = time();
        wrap.appendChild(bubble);
        wrap.appendChild(ts);
        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;
    }

    function typing() {
        const wrap = document.createElement('div');
        wrap.id = 'typing';
        wrap.style.cssText = 'margin-bottom:10px';
        wrap.innerHTML = `<div style="display:flex;gap:5px;padding:10px 14px;background:rgba(255,255,255,.05);border:1.5px solid rgba(245,240,232,.1);border-radius:.5rem;width:fit-content"><span style="width:6px;height:6px;background:rgba(245,240,232,.4);border-radius:50%;animation:tdot 1.2s infinite"></span><span style="width:6px;height:6px;background:rgba(245,240,232,.4);border-radius:50%;animation:tdot 1.2s .2s infinite"></span><span style="width:6px;height:6px;background:rgba(245,240,232,.4);border-radius:50%;animation:tdot 1.2s .4s infinite"></span></div>`;
        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;
    }

    async function doSend(overrideText) {
        const txt = (overrideText || input.value).trim();
        if (!txt) return;
        addMsg(txt, 'user');
        input.value = '';
        send.disabled = true;
        hideChips();
        typing();
        try {
            const res = await fetch('/api/chatbot', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ message: txt }),
            });
            document.getElementById('typing')?.remove();
            const data = await res.json();
            addMsg(data.reply || 'Sorry, no response received.', 'bot');
        } catch (e) {
            document.getElementById('typing')?.remove();
            addMsg('Connection error. Please try again.', 'bot');
        } finally {
            send.disabled = false;
        }
    }

    window.chipSend = function(text) {
        if (!open) toggleChat();
        doSend(text);
    };

    send.addEventListener('click', () => doSend());
    input.addEventListener('keydown', e => { if (e.key === 'Enter') doSend(); });

    const style = document.createElement('style');
    style.textContent = '@keyframes tdot{0%,60%,100%{transform:translateY(0);opacity:.4}30%{transform:translateY(-6px);opacity:1}}';
    document.head.appendChild(style);
})();
</script>
{{-- End AI Chatbot Widget --}}
@yield('scripts')
@stack('scripts')
</body>
</html>
