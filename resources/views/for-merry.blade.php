<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>For Merry 🖤 — Mahal Magmahal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            min-height: 100%;
            background: #070709;
            color: #f5f5f5;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Subtle film grain + monochrome moonlight glow */
        .ambient {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background:
                radial-gradient(circle at 20% 15%, rgba(255, 255, 255, 0.07), transparent 45%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.05), transparent 50%),
                linear-gradient(180deg, #050507 0%, #0d0d11 50%, #050507 100%);
        }
        canvas#stars {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
        }

        /* Envelope Intro Overlay */
        .intro {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            text-align: center;
            background: radial-gradient(circle at center, #141418 0%, #050507 75%);
            transition: opacity 0.9s ease, visibility 0.9s ease;
        }
        .intro.opened {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        .intro-eyebrow {
            font-size: 11px;
            letter-spacing: 0.35em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
        }
        .intro-title {
            margin-top: 14px;
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: clamp(2.4rem, 7vw, 3.8rem);
            font-weight: 600;
            font-style: italic;
            letter-spacing: 0.02em;
            color: #ffffff;
        }
        .intro-sub {
            margin-top: 10px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            max-width: 320px;
            line-height: 1.6;
        }
        .open-btn {
            margin-top: 32px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 30px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.08em;
            cursor: pointer;
            backdrop-filter: blur(12px);
            transition: transform 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
            box-shadow: 0 0 32px rgba(255, 255, 255, 0.12);
        }
        .open-btn:hover {
            transform: translateY(-2px) scale(1.02);
            background: rgba(255, 255, 255, 0.16);
            box-shadow: 0 0 42px rgba(255, 255, 255, 0.22);
        }

        /* Main Page */
        .page {
            position: relative;
            z-index: 2;
            max-width: 560px;
            margin: 0 auto;
            padding: 40px 20px 72px;
            opacity: 0;
            transform: translateY(18px);
            transition: opacity 1s ease 0.2s, transform 1s ease 0.2s;
        }
        .page.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .top-tag {
            text-align: center;
            font-size: 11px;
            letter-spacing: 0.32em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.45);
        }
        .hero-name {
            margin-top: 10px;
            text-align: center;
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: clamp(2.5rem, 8vw, 3.6rem);
            font-weight: 600;
            font-style: italic;
            line-height: 1.1;
            color: #ffffff;
        }
        .hero-caption {
            margin-top: 8px;
            text-align: center;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.58);
        }

        /* Cinematic Black & White Photo Frame */
        .photo-frame {
            margin-top: 28px;
            position: relative;
            border-radius: 24px;
            padding: 12px 12px 18px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(255, 255, 255, 0.14);
            box-shadow: 0 28px 70px rgba(0, 0, 0, 0.75);
            cursor: pointer;
            user-select: none;
        }
        .photo-wrap {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            aspect-ratio: 3 / 4;
            background: #111;
        }
        .photo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 35%;
            filter: grayscale(100%) contrast(1.14) brightness(0.95);
            transition: filter 0.8s ease, transform 0.8s ease;
        }
        .photo-frame.color-mode .photo-wrap img {
            filter: grayscale(0%) contrast(1.04) brightness(1);
            transform: scale(1.02);
        }
        .photo-vignette {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at center, transparent 48%, rgba(0, 0, 0, 0.55) 100%);
            pointer-events: none;
        }
        .photo-footer {
            margin-top: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 6px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.58);
        }
        .photo-footer em {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 16px;
            color: rgba(255, 255, 255, 0.9);
        }
        .color-pill {
            font-size: 10.5px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            color: rgba(255, 255, 255, 0.75);
        }

        /* Now Playing & Animated Lyrics Card */
        .card {
            margin-top: 22px;
            border-radius: 22px;
            padding: 22px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.11);
            backdrop-filter: blur(14px);
        }
        .song-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.09);
        }
        .song-meta h2 {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 24px;
            font-weight: 600;
            color: #ffffff;
            line-height: 1.1;
        }
        .song-meta p {
            margin-top: 3px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.55);
            letter-spacing: 0.06em;
        }
        .eq {
            display: flex;
            align-items: flex-end;
            gap: 3px;
            height: 18px;
        }
        .eq span {
            width: 3px;
            border-radius: 99px;
            background: rgba(255, 255, 255, 0.85);
            animation: bounce 1.1s ease-in-out infinite;
        }
        .eq span:nth-child(2) { animation-delay: 0.2s; }
        .eq span:nth-child(3) { animation-delay: 0.4s; }
        .eq span:nth-child(4) { animation-delay: 0.15s; }
        @keyframes bounce {
            0%, 100% { height: 5px; opacity: 0.45; }
            50% { height: 18px; opacity: 1; }
        }

        .lyrics-box {
            margin-top: 18px;
            min-height: 155px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 12px;
        }
        .lyric-line {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 22px;
            line-height: 1.45;
            font-style: italic;
            color: rgba(255, 255, 255, 0.26);
            transition: color 0.6s ease, transform 0.6s ease;
        }
        .lyric-line.active {
            color: #ffffff;
            transform: translateX(4px);
            text-shadow: 0 0 22px rgba(255, 255, 255, 0.25);
        }
        .song-actions {
            margin-top: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .song-link {
            flex: 1;
            min-width: 150px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: background 0.2s ease, transform 0.2s ease;
        }
        .song-link:hover {
            background: rgba(255, 255, 255, 0.14);
            transform: translateY(-1px);
        }

        /* BSIT Code Card */
        .code-card {
            margin-top: 22px;
            border-radius: 20px;
            padding: 18px 20px;
            background: #0c0c10;
            border: 1px solid rgba(255, 255, 255, 0.12);
            font-family: 'JetBrains Mono', monospace;
            font-size: 12.5px;
            line-height: 1.75;
            color: rgba(255, 255, 255, 0.8);
        }
        .code-dots {
            display: flex;
            gap: 6px;
            margin-bottom: 12px;
        }
        .code-dots span {
            width: 9px;
            height: 9px;
            border-radius: 99px;
            background: rgba(255, 255, 255, 0.22);
        }
        .kw { color: rgba(255, 255, 255, 0.45); }
        .str { color: #ffffff; font-weight: 500; }

        /* Love Letter */
        .letter {
            margin-top: 22px;
            padding: 26px 24px;
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        .letter h3 {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 26px;
            font-style: italic;
            font-weight: 600;
            color: #ffffff;
        }
        .letter p {
            margin-top: 14px;
            font-size: 14.5px;
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.78);
        }
        .signature {
            margin-top: 22px;
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 22px;
            font-style: italic;
            color: #ffffff;
            text-align: right;
        }

        .hug-wrap {
            margin-top: 26px;
            text-align: center;
        }
        .hug-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 26px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            background: #ffffff;
            color: #070709;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.04em;
            cursor: pointer;
            transition: transform 0.2s ease, opacity 0.2s ease;
        }
        .hug-btn:active { transform: scale(0.95); }
        .hug-msg {
            margin-top: 10px;
            font-size: 12.5px;
            color: rgba(255, 255, 255, 0.65);
            min-height: 18px;
        }

        .floating-heart {
            position: fixed;
            pointer-events: none;
            z-index: 60;
            font-size: 18px;
            animation: floatUp 2.2s ease-out forwards;
        }
        @keyframes floatUp {
            0% { transform: translateY(0) scale(0.8); opacity: 1; }
            100% { transform: translateY(-140px) scale(1.3); opacity: 0; }
        }

        /* Our Moments Monochrome Gallery */
        .gallery-head {
            margin-top: 28px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
        }
        .gallery-head h3 {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 28px;
            font-style: italic;
            font-weight: 600;
            color: #ffffff;
        }
        .gallery-head span {
            font-size: 11px;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.48);
        }
        .gallery-grid {
            margin-top: 14px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .mem-card {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: #111;
            aspect-ratio: 1 / 1;
            cursor: pointer;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }
        .mem-card:hover {
            transform: translateY(-3px);
            border-color: rgba(255, 255, 255, 0.4);
        }
        .mem-card.wide {
            grid-column: span 2;
            aspect-ratio: 16 / 10;
        }
        .mem-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scale(1.14);
            filter: grayscale(100%) contrast(1.12);
            transition: filter 0.55s ease, transform 0.55s ease;
        }
        .mem-card:hover img {
            filter: grayscale(0%) contrast(1.04);
            transform: scale(1.18);
        }
        .mem-card.wide img {
            transform: scale(1.02);
        }
        .mem-card.wide:hover img {
            transform: scale(1.06);
        }
        .mem-cap {
            position: absolute;
            inset-x: 0;
            bottom: 0;
            padding: 26px 12px 10px;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.85), transparent);
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 16px;
            font-style: italic;
            color: rgba(255, 255, 255, 0.92);
        }

        /* Lightbox */
        .lightbox {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(0, 0, 0, 0.88);
            backdrop-filter: blur(10px);
        }
        .lightbox.show { display: flex; }
        .lightbox-inner {
            position: relative;
            max-width: 480px;
            width: 100%;
            border-radius: 22px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: #0d0d11;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.8);
        }
        .lightbox-inner img {
            width: 100%;
            max-height: 70vh;
            object-fit: cover;
            display: block;
        }
        .lightbox-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
        }
        .lightbox-bar p {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 20px;
            font-style: italic;
            color: #fff;
        }
        .lightbox-close {
            border: 1px solid rgba(255, 255, 255, 0.25);
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="ambient"></div>
    <canvas id="stars"></canvas>

    <!-- Intro Envelope -->
    <div id="intro" class="intro">
        <p class="intro-eyebrow">Coded with love · For you only</p>
        <h1 class="intro-title">Hi, Merry 🖤</h1>
        <p class="intro-sub">Gumawa ako ng maliit na sulat gamit ang code — para lang sa'yo.</p>
        <button id="open-btn" type="button" class="open-btn">
            <span>✉</span>
            <span>Tap to Open</span>
        </button>
    </div>

    <main id="page" class="page">
        <p class="top-tag">In black &amp; white · Always you</p>
        <h1 class="hero-name">Merry</h1>
        <p class="hero-caption">Every quiet night feels like home when I’m holding you.</p>

        <!-- Black & White Portrait -->
        <div id="photo-frame" class="photo-frame" title="Tap to toggle Black & White / Color">
            <div class="photo-wrap">
                <img src="{{ asset('images/merry-moment.jpg') }}" alt="Us together">
                <div class="photo-vignette"></div>
            </div>
            <div class="photo-footer">
                <em>“My favorite place to be.”</em>
                <span id="color-pill" class="color-pill">Tap for color ✨</span>
            </div>
        </div>

        <!-- Song & Animated Romantic Lines -->
        <section class="card">
            <div class="song-head">
                <div class="song-meta">
                    <h2>Mahal Magmahal</h2>
                    <p>ESREMBORAK · OUR SONG</p>
                </div>
                <div class="eq" aria-hidden="true">
                    <span></span><span></span><span></span><span></span>
                </div>
            </div>

            <div id="lyrics-box" class="lyrics-box">
                <p class="lyric-line active">“Hindi man perpekto ang mundo, pero sa’yo ako laging sigurado.”</p>
                <p class="lyric-line">“Kahit gaano kahirap ang araw, isang yakap mo lang, kumpleto na ako.”</p>
                <p class="lyric-line">“Mahal magmahal — pero kung ikaw lang din, handa akong piliin ka araw-araw.”</p>
                <p class="lyric-line">“Sa bawat pangarap ko, Merry, palagi kang kasama.”</p>
            </div>

            <div id="music-embed-wrap" style="margin-top:16px;overflow:hidden;border-radius:14px;border:1px solid rgba(255,255,255,0.12);background:#000;display:none;">
                <iframe
                    id="yt-player"
                    width="100%"
                    height="86"
                    src=""
                    title="Mahal Magmahal — Esremborak"
                    frameborder="0"
                    allow="autoplay; encrypted-media; picture-in-picture"
                    allowfullscreen
                    style="display:block;filter:grayscale(100%) contrast(1.08);"
                ></iframe>
            </div>

            <div class="song-actions">
                <a class="song-link" target="_blank" rel="noopener noreferrer" href="https://www.youtube.com/watch?v=5mnZcoQldHM">
                    ▶ Open in YouTube
                </a>
                <a class="song-link" target="_blank" rel="noopener noreferrer" href="https://open.spotify.com/search/Mahal%20Magmahal%20Esremborak">
                    ♫ Open on Spotify
                </a>
            </div>
        </section>

        <!-- BSIT Romantic Code Block -->
        <section class="code-card" aria-label="Love code">
            <div class="code-dots"><span></span><span></span><span></span></div>
            <div><span class="kw">const</span> myHeart = <span class="str">"Merry"</span>;</div>
            <div><span class="kw">while</span> (true) {</div>
            <div>&nbsp;&nbsp;choose(myHeart);</div>
            <div>&nbsp;&nbsp;protect(myHeart);</div>
            <div>&nbsp;&nbsp;love(myHeart, <span class="str">"always &amp; beyond"</span>);</div>
            <div>} <span class="kw">// no bugs, no break, just you.</span></div>
        </section>

        <!-- Love Letter -->
        <article class="letter">
            <h3>Dear Merry,</h3>
            <p>
                Alam kong busy tayo minsan sa school at sa mga pangarap natin, pero gusto ko lang ipaalala sa’yo na kahit anong mangyari, ikaw pa rin ang pahinga ko at paborito kong tao sa buong mundo.
            </p>
            <p>
                Sabi nga nila, mahal magmahal — pero sa’yo, hindi ako kailanman mapapagod mag-effort, maghintay, at mahalin ka nang buo. Salamat sa pagmamahal, sa pasensya, at sa bawat ngiti mo na nagpapagaan ng araw ko.
            </p>
            <p>
                I love you so much, Merry. Always. 🖤
            </p>
            <div class="signature">— Always yours, Drexell 🤍</div>
        </article>

        <!-- Our Moments Gallery -->
        <div class="gallery-head">
            <h3>Our Moments</h3>
            <span>Tap photo to view in HD ✨</span>
        </div>
        <section class="gallery-grid" aria-label="Our memories">
            <div class="mem-card" data-mem-src="{{ asset('images/merry/cute.jpg') }}" data-mem-cap="Cutest face in the world. 🤍">
                <img src="{{ asset('images/merry/cute.jpg') }}" alt="Merry" style="transform:scale(1.02);">
                <div class="mem-cap">Cutest face in the world. 🤍</div>
            </div>
            <div class="mem-card" data-mem-src="{{ asset('images/merry/m7.jpg') }}" data-mem-cap="Close to you is my favorite place. 🤍">
                <img src="{{ asset('images/merry/m7.jpg') }}" alt="Close to you" style="transform:scale(1.02);">
                <div class="mem-cap">Close to you is my favorite place. 🤍</div>
            </div>
            <div class="mem-card wide" style="aspect-ratio: 16 / 9;" data-mem-src="{{ asset('images/merry/collage.jpg') }}" data-mem-cap="Every memory with you is my favorite chapter. 🖤✨">
                <img src="{{ asset('images/merry/collage.jpg') }}" alt="Our collage memories" style="object-fit: cover;">
                <div class="mem-cap">Every memory with you is my favorite chapter. 🖤✨</div>
            </div>
        </section>

        <!-- Lightbox Modal -->
        <div id="lightbox" class="lightbox" aria-hidden="true">
            <div class="lightbox-inner" onclick="event.stopPropagation()">
                <img id="lb-img" src="" alt="Memory">
                <div class="lightbox-bar">
                    <p id="lb-cap"></p>
                    <button id="lb-close" type="button" class="lightbox-close">Close ✕</button>
                </div>
            </div>
        </div>

        <div class="hug-wrap">
            <button id="hug-btn" type="button" class="hug-btn">Send a Hug Back 🤍</button>
            <p id="hug-msg" class="hug-msg"></p>
        </div>
    </main>

    <script>
        // Open envelope & auto-play "Mahal Magmahal" by Esremborak
        const intro = document.getElementById('intro');
        const page = document.getElementById('page');
        const ytPlayer = document.getElementById('yt-player');
        const musicWrap = document.getElementById('music-embed-wrap');
        document.getElementById('open-btn').addEventListener('click', () => {
            intro.classList.add('opened');
            page.classList.add('visible');
            if (ytPlayer && musicWrap) {
                musicWrap.style.display = 'block';
                ytPlayer.src = 'https://www.youtube.com/embed/5mnZcoQldHM?autoplay=1&loop=1&playlist=5mnZcoQldHM&playsinline=1&rel=0';
            }
        });

        // Toggle B&W / Color on photo tap
        const frame = document.getElementById('photo-frame');
        const pill = document.getElementById('color-pill');
        frame.addEventListener('click', () => {
            frame.classList.toggle('color-mode');
            pill.textContent = frame.classList.contains('color-mode') ? 'Back to B&W 🖤' : 'Tap for color ✨';
        });

        // Memory gallery lightbox
        const lb = document.getElementById('lightbox');
        const lbImg = document.getElementById('lb-img');
        const lbCap = document.getElementById('lb-cap');
        document.querySelectorAll('[data-mem-src]').forEach((card) => {
            card.addEventListener('click', () => {
                lbImg.src = card.dataset.memSrc;
                lbCap.textContent = card.dataset.memCap || '';
                lb.classList.add('show');
            });
        });
        const closeLb = () => lb.classList.remove('show');
        lb?.addEventListener('click', closeLb);
        document.getElementById('lb-close')?.addEventListener('click', closeLb);

        // Cycle highlighted romantic lyric lines
        const lines = Array.from(document.querySelectorAll('.lyric-line'));
        let activeIdx = 0;
        setInterval(() => {
            lines[activeIdx]?.classList.remove('active');
            activeIdx = (activeIdx + 1) % lines.length;
            lines[activeIdx]?.classList.add('active');
        }, 3800);

        // Send a hug heart burst
        let hugs = 0;
        const hugMsg = document.getElementById('hug-msg');
        document.getElementById('hug-btn').addEventListener('click', (e) => {
            hugs++;
            hugMsg.textContent = hugs === 1
                ? 'Hug received! Missing you right now, Merry 🤍'
                : `${hugs} hugs sent! I love you always, Merry 🖤✨`;
            for (let i = 0; i < 7; i++) {
                const el = document.createElement('span');
                el.className = 'floating-heart';
                el.textContent = i % 2 === 0 ? '🤍' : '✨';
                el.style.left = `${e.clientX + (Math.random() * 100 - 50)}px`;
                el.style.top = `${e.clientY - 10}px`;
                document.body.appendChild(el);
                setTimeout(() => el.remove(), 2200);
            }
        });

        // Subtle floating monochrome stardust
        const canvas = document.getElementById('stars');
        const ctx = canvas.getContext('2d');
        let w = canvas.width = window.innerWidth;
        let h = canvas.height = window.innerHeight;
        window.addEventListener('resize', () => {
            w = canvas.width = window.innerWidth;
            h = canvas.height = window.innerHeight;
        });
        const stars = Array.from({ length: 55 }, () => ({
            x: Math.random() * w,
            y: Math.random() * h,
            r: Math.random() * 1.4 + 0.4,
            s: Math.random() * 0.25 + 0.08,
            a: Math.random() * 0.55 + 0.15,
        }));
        const draw = () => {
            ctx.clearRect(0, 0, w, h);
            stars.forEach((st) => {
                st.y -= st.s;
                if (st.y < -5) { st.y = h + 5; st.x = Math.random() * w; }
                ctx.beginPath();
                ctx.arc(st.x, st.y, st.r, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(255,255,255,${st.a})`;
                ctx.fill();
            });
            requestAnimationFrame(draw);
        };
        draw();
    </script>
</body>
</html>
