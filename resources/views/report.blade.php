<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">
    <title>Scam-rapport {{ $listing->report_slug ?? '#'.$listing->id }} — Woning Scam Checker</title>
    <meta name="description" content="Uitgebreid risicorapport (score {{ $listing->scam_score }}/100): signalen, benchmark, advies en controlelijst.">
    <meta property="og:title" content="Scam-rapport — Woning Scam Checker">
    <meta property="og:description" content="Risicoscore {{ $listing->scam_score }}/100 met concrete signalen en aanbevelingen.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <style>
        :root {
            --bg: #0a0e14;
            --bg2: #121a24;
            --card: #161f2c;
            --card2: #1c2738;
            --text: #e8eef4;
            --muted: #8b9aab;
            --accent: #2dd4bf;
            --accent-dim: rgba(45, 212, 191, 0.12);
            --border: #2d3d52;
            --warn: #fb923c;
            --danger: #f87171;
            --ok: #4ade80;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
            background: radial-gradient(1200px 600px at 10% -10%, rgba(45, 212, 191, 0.08), transparent 50%), var(--bg);
            color: var(--text);
            line-height: 1.6;
            font-size: 1rem;
        }
        .wrap { max-width: 46rem; margin: 0 auto; padding: 2rem 1.25rem 4rem; }
        .badge {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--accent);
            background: var(--accent-dim);
            padding: 0.25rem 0.55rem;
            border-radius: 6px;
            margin-bottom: 0.75rem;
        }
        h1 { font-family: 'Outfit', system-ui, sans-serif; font-size: clamp(1.35rem, 4vw, 1.65rem); font-weight: 700; margin: 0 0 0.35rem; letter-spacing: -0.02em; }
        .lead { color: var(--muted); font-size: 0.95rem; margin: 0 0 2rem; max-width: 40rem; }
        .meta { font-size: 0.8rem; color: var(--muted); margin-bottom: 1.5rem; }
        .card {
            padding: 1.35rem 1.25rem;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: linear-gradient(165deg, var(--card) 0%, var(--card2) 100%);
            margin-bottom: 1rem;
        }
        .score-row { display: flex; align-items: flex-end; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.5rem; }
        .score-val { font-size: clamp(2.5rem, 8vw, 3.25rem); font-weight: 800; line-height: 1; letter-spacing: -0.04em; }
        .pill {
            display: inline-block;
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .c-green { background: rgba(74, 222, 128, 0.15); color: var(--ok); }
        .c-orange { background: rgba(251, 146, 60, 0.15); color: var(--warn); }
        .c-red { background: rgba(248, 113, 113, 0.15); color: var(--danger); }
        .rule-note { font-size: 0.85rem; color: var(--muted); margin-top: 0.5rem; }
        h2 {
            font-family: 'Outfit', system-ui, sans-serif;
            font-size: 1.05rem;
            font-weight: 600;
            margin: 0 0 0.65rem;
            color: var(--text);
            letter-spacing: -0.01em;
        }
        h3 { font-size: 0.9rem; font-weight: 600; margin: 1rem 0 0.4rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; }
        p { margin: 0 0 0.75rem; }
        ul, ol { margin: 0 0 0.75rem; padding-left: 1.2rem; }
        li { margin: 0.4rem 0; }
        .prose { color: var(--text); }
        .prose.muted { color: var(--muted); font-size: 0.9375rem; }
        .prose pre-wrap { white-space: pre-wrap; word-break: break-word; }
        .breakdown-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
        }
        .breakdown-item:last-child { border-bottom: none; }
        .breakdown-cat { font-weight: 600; color: var(--accent); font-size: 0.9rem; }
        .breakdown-pts { font-size: 0.75rem; color: var(--muted); margin-left: 0.35rem; }
        .market-box {
            background: var(--bg2);
            border-radius: 10px;
            padding: 1rem;
            border: 1px solid var(--border);
            font-size: 0.9rem;
            color: var(--muted);
        }
        .market-box strong { color: var(--text); }
        .back { margin-top: 2rem; font-size: 0.9rem; }
        .back a { color: var(--accent); text-decoration: none; }
        .back a:hover { text-decoration: underline; }
        pre.raw {
            margin-top: 0.75rem;
            padding: 1rem;
            border-radius: 10px;
            background: #080c11;
            border: 1px solid var(--border);
            font-size: 0.78rem;
            white-space: pre-wrap;
            word-break: break-word;
            color: var(--muted);
            max-height: 22rem;
            overflow: auto;
        }
        details summary { cursor: pointer; color: var(--muted); font-size: 0.875rem; }
        .empty { color: var(--muted); font-style: italic; }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,600;0,9..40,700&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
@php
    $s = (int) $listing->scam_score;
    $tier = $s <= 30 ? 'c-green' : ($s <= 60 ? 'c-orange' : 'c-red');
    $tierLabel = $s <= 30 ? 'Laag risico' : ($s <= 60 ? 'Matig risico' : 'Hoog risico');
    $flags = $listing->scam_flags ?? [];
    $snap = $listing->report_snapshot ?? [];
    $ruleScore = $snap['rule_score'] ?? $s;
    $rec = $snap['recommendations'] ?? [];
    $verify = $snap['what_to_verify'] ?? [];
    $breakdown = $snap['risk_breakdown'] ?? [];
    $methodology = $snap['methodology'] ?? null;
    $marketContext = $snap['market_context'] ?? null;
    $linkAssessment = $snap['link_assessment'] ?? null;
    $llmUsed = $snap['llm_used'] ?? false;
@endphp
    <div class="wrap">
        <span class="badge">Gedeeld rapport</span>
        <h1>{{ $listing->report_slug ?? 'Rapport #'.$listing->id }}</h1>
        <p class="lead">Volledig overzicht van de automatische analyse: risicoscore, onderdelen, benchmark, adviezen en een controlelijst. Geen login nodig.</p>
        <p class="meta">
            Aangemaakt: {{ $listing->created_at?->format('d-m-Y H:i') ?? '—' }}
            @if ($listing->source_url)
                · <a href="{{ $listing->source_url }}" style="color:var(--accent);">{{ parse_url($listing->source_url, PHP_URL_HOST) ?? 'Bronlink' }}</a>
            @endif
        </p>

        <div class="card">
            <div class="score-row">
                <span class="score-val">{{ $listing->scam_score }}</span>
                <div>
                    <span class="pill {{ $tier }}">{{ $tierLabel }}</span>
                    @if ($llmUsed)
                        <span class="pill" style="background:var(--accent-dim);color:var(--accent);margin-left:0.35rem;">AI-toetsing</span>
                    @endif
                </div>
            </div>
            @if ((int) $ruleScore !== $s)
                <p class="rule-note">Regel-engine: <strong>{{ $ruleScore }}</strong> — eindscore na combinatie (regels + optionele AI): <strong>{{ $s }}</strong>.</p>
            @endif
        </div>

        @if ($methodology)
            <div class="card">
                <h2>Hoe dit rapport tot stand komt</h2>
                <p class="prose muted">{{ $methodology }}</p>
            </div>
        @endif

        @if ($marketContext)
            <div class="card">
                <h2>Markt- en prijscontext</h2>
                <div class="market-box prose">{!! nl2br(e($marketContext)) !!}</div>
                <h3>Benchmark in cijfers</h3>
                <p class="prose muted">
                    Geschatte maandhuur-benchmark: <strong>€ {{ $listing->market_average ?? '—' }}</strong>
                    @if ($listing->market_difference_percent !== null)
                        · Afwijking t.o.v. ingevoerde prijs: <strong>{{ $listing->market_difference_percent }}%</strong>
                    @endif
                </p>
            </div>
        @else
            <div class="card">
                <h2>Prijsvergelijking</h2>
                <p class="prose muted">
                    Benchmark (model): <strong>€ {{ $listing->market_average ?? '—' }}</strong> per maand.
                    @if ($listing->market_difference_percent !== null)
                        Afwijking: <strong>{{ $listing->market_difference_percent }}%</strong>.
                    @endif
                </p>
            </div>
        @endif

        @if (count($breakdown))
            <div class="card">
                <h2>Risico per onderdeel</h2>
                <p class="prose muted" style="margin-bottom:1rem;">Elk blok toont welk thema bijdroeg aan de inschatting. Dit is geen juridisch oordeel.</p>
                @foreach ($breakdown as $row)
                    <div class="breakdown-item">
                        <span class="breakdown-cat">{{ $row['category'] ?? 'Onderdeel' }}</span>
                        <span class="breakdown-pts">({{ $row['points'] ?? 0 }} pt)</span>
                        <p class="prose muted" style="margin:0.35rem 0 0;">{{ $row['detail'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="card">
            <h2>Signalen (rode vlaggen)</h2>
            @if (count($flags))
                <ul class="prose">
                    @foreach ($flags as $f)
                        <li>{{ $f }}</li>
                    @endforeach
                </ul>
            @else
                <p class="empty">Geen automatisch gedetecteerde signalen in de tekst.</p>
            @endif
        </div>

        @if ($linkAssessment && $listing->source_url)
            <div class="card">
                <h2>Link & inhoud</h2>
                <p class="prose">{{ $linkAssessment }}</p>
            </div>
        @endif

        @if (count($rec))
            <div class="card">
                <h2>Aanbevelingen</h2>
                <ol class="prose">
                    @foreach ($rec as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ol>
            </div>
        @endif

        @if (count($verify))
            <div class="card">
                <h2>Controleer dit vóór je betaalt</h2>
                <ol class="prose">
                    @foreach ($verify as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ol>
            </div>
        @endif

        <div class="card">
            <h2>Toelichting &amp; bevindingen</h2>
            <div class="prose pre-wrap muted" style="white-space: pre-wrap;">{{ $listing->ai_summary }}</div>
        </div>

        @if ($listing->city)
            <p class="prose muted" style="margin:0 0 1rem;">Gedetecteerde plaats in tekst: <strong>{{ $listing->city }}</strong></p>
        @endif

        <p class="back"><a href="{{ url('/') }}">← Nieuwe analyse</a></p>

        <details>
            <summary>Volledige bron (ingevoerde tekst of URL)</summary>
            <pre class="raw" aria-label="Bron">{{ $listing->raw_input }}</pre>
        </details>
    </div>
</body>
</html>
