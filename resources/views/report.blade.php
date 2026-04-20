<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">
    <title>Scam-rapport {{ $listing->report_slug ?? '#'.$listing->id }} — De Huur Radar</title>
    <meta name="description" content="Uitgebreid risicorapport (score {{ $listing->scam_score }}/100): signalen, benchmark, advies en controlelijst.">
    <meta property="og:title" content="Scam-rapport — De Huur Radar">
    <meta property="og:description" content="Risicoscore {{ $listing->scam_score }}/100 met concrete signalen en aanbevelingen.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @vite(['resources/css/report-web.css'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,600;0,9..40,700&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
@php
    $s = (int) $listing->scam_score;
    if ($s <= 30) {
        $tier = 'c-green';
        $tierLabel = 'Laag risico';
    } elseif ($s <= 60) {
        $tier = 'c-orange';
        $tierLabel = 'Matig risico';
    } else {
        $tier = 'c-red';
        $tierLabel = 'Hoog risico';
    }
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
    $listingFit = is_array($snap['listing_fit'] ?? null) ? $snap['listing_fit'] : null;
    $listingFacts = is_array($snap['listing_facts'] ?? null) ? $snap['listing_facts'] : null;
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

        @if ($listingFacts)
            <div class="card">
                <h2>Geëxtraheerde gegevens</h2>
                <table class="facts-table" style="width:100%; font-size:0.9375rem; border-collapse:collapse;">
                    <tbody>
                        <tr>
                            <td style="padding:0.35rem 0.75rem 0.35rem 0; color:var(--muted); vertical-align:top; width:38%;">Stad (herkenning)</td>
                            <td style="padding:0.35rem 0;">{{ $listingFacts['city'] ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:0.35rem 0.75rem 0.35rem 0; color:var(--muted); vertical-align:top;">Straat / huisnummer</td>
                            <td style="padding:0.35rem 0;">{{ $listingFacts['street_line'] ?? ($listingFacts['street'] ?? '—') }}</td>
                        </tr>
                        <tr>
                            <td style="padding:0.35rem 0.75rem 0.35rem 0; color:var(--muted); vertical-align:top;">Prijs in advertentie</td>
                            <td style="padding:0.35rem 0;">
                                @if (isset($listingFacts['price_eur']) && $listingFacts['price_eur'] !== null)
                                    € {{ number_format((int) $listingFacts['price_eur'], 0, ',', '.') }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0.35rem 0.75rem 0.35rem 0; color:var(--muted); vertical-align:top;">Benchmark (model)</td>
                            <td style="padding:0.35rem 0;">
                                € {{ number_format((int) ($listingFacts['benchmark_monthly_eur'] ?? $listing->market_average ?? 0), 0, ',', '.') }} / maand
                                @if (($listingFacts['benchmark_diff_percent'] ?? $listing->market_difference_percent) !== null)
                                    · afwijking t.o.v. prijs: <strong>{{ $listingFacts['benchmark_diff_percent'] ?? $listing->market_difference_percent }}%</strong>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0.35rem 0.75rem 0.35rem 0; color:var(--muted); vertical-align:top;">Bron</td>
                            <td style="padding:0.35rem 0; word-break:break-all;">
                                @if (!empty($listingFacts['source_url']))
                                    <a href="{{ $listingFacts['source_url'] }}" style="color:var(--accent);">{{ $listingFacts['source_url'] }}</a>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0.35rem 0.75rem 0.35rem 0; color:var(--muted); vertical-align:top;">Contact (indicatie)</td>
                            <td style="padding:0.35rem 0;">{{ $listingFacts['contact_hint'] ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
                <p class="prose muted" style="margin:0.75rem 0 0; font-size:0.85rem;">
                    @if (($listingFacts['benchmark_scope'] ?? '') === 'national')
                        Benchmark: landelijk gemiddelde — geen specifieke stad herkend in de tekst.
                    @elseif (!empty($listingFacts['benchmark_city']))
                        Benchmark: model voor <strong>{{ $listingFacts['benchmark_city'] }}</strong> (indicatief, geen taxatie).
                    @endif
                </p>
            </div>
        @endif

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

        @if ($listingFit && isset($listingFit['tier'], $listingFit['score']))
            <div class="card" style="border-left:3px solid var(--accent);">
                <h2>Lijkt dit op een huuradvertentie?</h2>
                <p class="prose muted" style="margin:0;">
                    Automatische inschatting op basis van ingelezen tekst en link: <strong>{{ (int) $listingFit['score'] }}/100</strong>.
                    @if ($listingFit['tier'] === 'strong')
                        Dit lijkt redelijk typerend voor een huuradvertentie (prijs, termen en/of bekend platform).
                    @elseif ($listingFit['tier'] === 'mixed')
                        Twijfelachtig: enkele signalen, maar de inhoud kan ook iets anders zijn — plak bij voorkeur de volledige advertentie.
                    @else
                        Weinig typerend voor een advertentie; de analyse kan minder betrouwbaar zijn.
                    @endif
                </p>
            </div>
        @endif

        @if ($methodology)
            <div class="card">
                <h2>Hoe dit rapport tot stand komt</h2>
                <p class="prose muted methodology-text">{!! nl2br(e($methodology)) !!}</p>
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
            <div class="prose muted prose-pre-wrap">{{ $listing->ai_summary }}</div>
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
