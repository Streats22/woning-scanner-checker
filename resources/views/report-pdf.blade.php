<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
@php
    /*
     * Huisstijl G (bosgroen, mint, koraal), gelijk aan frontend/app/assets/css/main.css.
     * Lettertype: DejaVu Sans (ingebouwd in Dompdf) — rendert ë, ï, ö, é en € betrouwbaar.
     */
    $dark = $theme === 'dark';
    $c = $dark ? [
        'page' => '#0a1f18', 'card' => '#10291f', 'text' => '#eaf4ef', 'text2' => '#b4cbc0', 'text3' => '#9db8ac',
        'line' => '#24443a', 'brand' => '#0e3b2e', 'deep' => '#06140f', 'soft' => '#8fe3b8', 'accent' => '#8fe3b8',
        'action' => '#ff8a6b', 'track' => '#24443a',
    ] : [
        'page' => '#ffffff', 'card' => '#f3f7f4', 'text' => '#0e2a21', 'text2' => '#3d5249', 'text3' => '#4c6159',
        'line' => '#dce5e0', 'brand' => '#0e3b2e', 'deep' => '#0a1f18', 'soft' => '#c8f2dc', 'accent' => '#0e3b2e',
        'action' => '#ff7a59', 'track' => '#dce5e0',
    ];
    $risk = [
        'p-low' => ['fg' => '#1e6b44', 'bg' => '#ddf3e6', 'meter' => '#2e9e62', 'onBrand' => '#8fe3b8'],
        'p-mid' => ['fg' => '#7a4f00', 'bg' => '#fdf0d5', 'meter' => '#e0a100', 'onBrand' => '#f2c14e'],
        'p-high' => ['fg' => '#a12e18', 'bg' => '#fce3dc', 'meter' => '#e5482a', 'onBrand' => '#ff9c85'],
    ];
@endphp
    <style>
        /* Onderrand = hoogte van de vaste footer (.foot), die op elke pagina onderaan staat. */
        @page { margin: 26pt 0 40pt 0; background-color: {{ $c['page'] }}; }
        @page :first { margin-top: 0; }
        /* Geen html-marge zetten: in Dompdf overschrijft die de @page-marges. */
        html { padding: 0; background: {{ $c['page'] }}; }
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5pt;
            line-height: 1.5;
            color: {{ $c['text'] }};
            background: {{ $c['page'] }};
        }
        table { border-collapse: collapse; border: 0; }
        td, th { border: 0; padding: 0; vertical-align: top; }

        .band {
            background: {{ $c['brand'] }};
            color: #ffffff;
            padding: 22pt 34pt 24pt;
            border-bottom-left-radius: 22pt;
            border-bottom-right-radius: 22pt;
        }
        .brand td { vertical-align: middle; }
        .brand__name { font-size: 13pt; font-weight: bold; color: #ffffff; }
        .brand__tag { font-size: 8pt; color: #cfe3da; }
        .brand__meta { text-align: right; font-size: 8pt; color: #cfe3da; }

        .hero { width: 100%; margin-top: 18pt; }
        .hero td { vertical-align: middle; }
        .hero__score { font-size: 40pt; font-weight: bold; line-height: 1; color: #ffffff; }
        .hero__of { font-size: 12pt; color: #cfe3da; }
        .hero__label { font-size: 7.5pt; font-weight: bold; letter-spacing: 0.6pt; text-transform: uppercase; color: #cfe3da; }
        .hero__risk { font-size: 20pt; font-weight: bold; line-height: 1.15; }
        .meter { width: 100%; margin-top: 10pt; }
        .meter td { height: 8pt; }
        .chip {
            display: inline-block;
            margin-top: 6pt;
            padding: 2pt 8pt;
            border-radius: 9pt;
            font-size: 7.5pt;
            font-weight: bold;
            background: #1d5442;
            color: #c8f2dc;
        }

        .content { padding: 18pt 34pt 0; }
        h1 { margin: 0; font-size: 22pt; line-height: 1.1; color: #ffffff; }
        h2 {
            margin: 16pt 0 7pt;
            font-size: 12pt;
            font-weight: bold;
            color: {{ $c['text'] }};
        }
        p { margin: 0 0 6pt; }
        .muted { color: {{ $c['text2'] }}; }
        .small { font-size: 8pt; color: {{ $c['text3'] }}; }

        .card {
            background: {{ $c['card'] }};
            border-radius: 10pt;
            padding: 9pt 12pt;
            margin-bottom: 5pt;
        }

        .facts { width: 100%; background: {{ $c['card'] }}; border-radius: 10pt; }
        .facts td { padding: 5pt 12pt; border-bottom: 1px solid {{ $c['line'] }}; }
        .facts td.muted { width: 42%; }

        .row { width: 100%; }
        .row__cat { font-weight: bold; color: {{ $c['text'] }}; }
        .pts {
            display: inline-block;
            padding: 1pt 7pt;
            border-radius: 8pt;
            font-size: 8pt;
            font-weight: bold;
            background: {{ $risk['p-high']['bg'] }};
            color: {{ $risk['p-high']['fg'] }};
        }

        .flag {
            background: {{ $dark ? '#3a1d16' : $risk['p-high']['bg'] }};
            border-radius: 10pt;
            padding: 6pt 12pt;
            margin-bottom: 4pt;
        }
        .dot { color: {{ $c['action'] }}; font-weight: bold; }
        .aitag {
            display: inline-block;
            padding: 0 5pt;
            border-radius: 6pt;
            font-size: 7pt;
            font-weight: bold;
            background: {{ $c['brand'] }};
            color: #c8f2dc;
        }
        .aitag--soft { background: #c8f2dc; color: #0e3b2e; }
        .ai {
            margin-top: 10pt;
            padding: 10pt 12pt;
            border-radius: 10pt;
            background: {{ $c['brand'] }};
            color: #ffffff;
        }
        .ai__title { font-size: 11pt; font-weight: bold; margin-bottom: 5pt; color: #ffffff; }
        .ai__score { color: #ffffff; margin: 0 0 5pt; }
        .ai__lead { color: #cfe3da; margin: 0 0 4pt; }
        .ai__item { margin: 2pt 0; color: #ffffff; }

        .check td { padding: 5pt 0; border-bottom: 1px solid {{ $c['line'] }}; }
        .box {
            width: 9pt;
            height: 9pt;
            border: 1.2pt solid {{ $c['accent'] }};
            border-radius: 2pt;
        }

        ol.rec { margin: 0 0 0 14pt; padding: 0; }
        ol.rec li { margin: 2pt 0; }

        .summary { white-space: pre-wrap; word-wrap: break-word; color: {{ $c['text2'] }}; }

        .foot {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -40pt;
            height: 40pt;
            padding: 0 34pt;
            background: {{ $c['deep'] }};
            color: #9db8ac;
            font-size: 7.5pt;
        }
        .foot table { height: 40pt; }
        /* Paginagrote achtergrond (ook de marges); @page background-color doet Dompdf niet overal. */
        .pagebg {
            position: fixed;
            top: -26pt;
            bottom: -40pt;
            left: 0;
            right: 0;
            z-index: -1;
            background: {{ $c['page'] }};
        }
        .foot td { vertical-align: middle; }
        .foot strong { color: #ffffff; }
        .foot a { color: #8fe3b8; text-decoration: none; }
    </style>
</head>
<body>
{{-- Dompdf: alleen PNG data-URI; zie App\Support\PdfBrandLogo (public + resources fallback). --}}
@php
    $logoDataUri = \App\Support\PdfBrandLogo::dataUri($theme);
@endphp
    <div class="pagebg"></div>
    {{-- Vaste footer: staat vóór de inhoud zodat Dompdf hem op elke pagina onderaan herhaalt. --}}
    <div class="foot">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <strong>{{ __('pdf.brand_name') }}</strong> · {{ __('pdf.foot_tagline') }} · dehuurradar.nl
                </td>
                {{-- Paginanummer wordt na het renderen in het midden gezet (ReportPdfService). --}}
                <td style="text-align: right;">
                    <a href="https://streatsdesign.nl">{{ __('pdf.made_by') }}</a> · <a href="mailto:info@streatsdesign.nl">{{ __('pdf.contact') }}</a>
                </td>
            </tr>
        </table>
    </div>
@php
    $s = (int) $listing->scam_score;
    $tier = $s <= 30 ? 'p-low' : ($s <= 60 ? 'p-mid' : 'p-high');
    $tierLabel = $s <= 30 ? __('pdf.risk_low') : ($s <= 60 ? __('pdf.risk_medium') : __('pdf.risk_high'));
    $flags = $listing->scam_flags ?? [];
    $snap = $listing->report_snapshot ?? [];
    $ruleScore = $snap['rule_score'] ?? $s;
    $rec = $snap['recommendations'] ?? [];
    $verify = $snap['what_to_verify'] ?? [];
    $breakdown = $snap['risk_breakdown'] ?? [];
    $listingFacts = is_array($snap['listing_facts'] ?? null) ? $snap['listing_facts'] : null;
    $aiFindings = is_array($snap['ai_findings'] ?? null) ? $snap['ai_findings'] : null;
    $aiFlags = $aiFindings['flags'] ?? [];
@endphp
@php
    $rc = $risk[$tier];
    $pct = max(0, min(100, $s));
@endphp
    <div class="band">
        <table class="brand" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 46px; padding-right: 9pt;">
                    @if ($logoDataUri !== '')
                        <img src="{{ $logoDataUri }}" width="38" height="38" alt="" />
                    @endif
                </td>
                <td>
                    <div class="brand__name">{{ __('pdf.brand_name') }}</div>
                    <div class="brand__tag">{{ __('pdf.brand_tagline') }}</div>
                </td>
                <td class="brand__meta">
                    {{ $listing->report_slug ?? __('pdf.report_number', ['id' => $listing->id]) }}<br>
                    {{ $listing->created_at?->format('d-m-Y H:i') ?? '—' }}
                </td>
            </tr>
        </table>

        <table class="hero" cellpadding="0" cellspacing="0">
            <tr>
                <td style="width: 34%;">
                    <div class="hero__label">{{ __('pdf.score_label') }}</div>
                    <span class="hero__score">{{ $s }}</span><span class="hero__of"> / 100</span>
                </td>
                <td>
                    <h1>{{ __('pdf.report_title') }}</h1>
                    <div class="hero__risk" style="color: {{ $rc['onBrand'] }};">{{ $tierLabel }}</div>
                    @if (!empty($snap['llm_used']))
                        <span class="chip">{{ __('pdf.ai_badge') }}</span>
                    @endif
                </td>
            </tr>
        </table>
        <table class="meter" cellpadding="0" cellspacing="0">
            <tr>
                @if ($pct > 0)
                    <td style="width: {{ $pct }}%; background: {{ $rc['meter'] }}; border-radius: 4pt;"></td>
                @endif
                @if ($pct < 100)
                    <td style="background: #1d5442; border-radius: 4pt;"></td>
                @endif
            </tr>
        </table>
    </div>

    <div class="content">
        @if ((int) $ruleScore !== $s)
            <p class="small">{{ __('pdf.rule_engine_line', ['rule' => $ruleScore, 'final' => $s]) }}</p>
        @endif

        <h2>{{ __('pdf.market_heading') }}</h2>
        <div class="card">
            {{ __('pdf.market_benchmark', ['avg' => $listing->market_average ?? '—']) }}
            @if ($listing->market_difference_percent !== null)
                {{ ' '.__('pdf.market_diff', ['pct' => $listing->market_difference_percent]) }}
            @endif
        </div>

    @if ($listingFacts)
        <h2>{{ __('pdf.facts_title') }}</h2>
        <table class="facts" cellpadding="0" cellspacing="0">
            <tr>
                <td class="muted">{{ __('pdf.facts_city') }}</td>
                <td>{{ $listingFacts['city'] ?? __('pdf.facts_empty') }}</td>
            </tr>
            <tr>
                <td class="muted">{{ __('pdf.facts_street') }}</td>
                <td>{{ $listingFacts['street_line'] ?? ($listingFacts['street'] ?? __('pdf.facts_empty')) }}</td>
            </tr>
            <tr>
                <td class="muted">{{ __('pdf.facts_price') }}</td>
                <td>
                    @if (isset($listingFacts['price_eur']) && $listingFacts['price_eur'] !== null)
                        € {{ number_format((int) $listingFacts['price_eur'], 0, ',', '.') }}
                    @else
                        {{ __('pdf.facts_empty') }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="muted">{{ __('pdf.facts_benchmark') }}</td>
                <td>
                    € {{ number_format((int) ($listingFacts['benchmark_monthly_eur'] ?? $listing->market_average ?? 0), 0, ',', '.') }}
                    @if (($listingFacts['benchmark_diff_percent'] ?? $listing->market_difference_percent) !== null)
                        · {{ __('pdf.facts_diff') }}: <strong>{{ $listingFacts['benchmark_diff_percent'] ?? $listing->market_difference_percent }}%</strong>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="muted">{{ __('pdf.facts_source') }}</td>
                <td style="word-break:break-all;">
                    @if (!empty($listingFacts['source_url']))
                        {{ $listingFacts['source_url'] }}
                    @else
                        {{ __('pdf.facts_empty') }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="muted">{{ __('pdf.facts_contact') }}</td>
                <td>{{ $listingFacts['contact_hint'] ?? __('pdf.facts_empty') }}</td>
            </tr>
            @if (! empty($listingFacts['dwelling']) && is_array($listingFacts['dwelling']))
                <x-listing-dwelling-facts :dwelling="$listingFacts['dwelling']" variant="pdf" />
            @endif
            <x-listing-surface-facts :listing-facts="$listingFacts" variant="pdf" />
        </table>
        <p class="small" style="margin-top:5pt;">
            @if (($listingFacts['benchmark_scope'] ?? '') === 'national')
                {{ __('pdf.facts_scope_national') }}
            @elseif (!empty($listingFacts['benchmark_city']))
                {{ __('pdf.facts_scope_city', ['city' => $listingFacts['benchmark_city']]) }}
            @endif
        </p>
    @endif

        @if (count($breakdown))
            <h2>{{ __('pdf.risk_breakdown_heading') }}</h2>
            @foreach ($breakdown as $row)
                <div class="card">
                    <table class="row" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="row__cat">{{ $row['category'] ?? __('pdf.breakdown_fallback') }}</td>
                            <td style="text-align: right; width: 60pt;"><span class="pts">{{ __('pdf.points', ['n' => $row['points'] ?? 0]) }}</span></td>
                        </tr>
                    </table>
                    @if (!empty($row['detail']))
                        <p class="muted" style="margin: 3pt 0 0;">{{ $row['detail'] }}</p>
                    @endif
                </div>
            @endforeach
        @endif

        <h2>{{ __('pdf.signals_heading') }}</h2>
        @if (count($flags))
            @foreach ($flags as $f)
                <div class="flag"><span class="dot">&#9679;</span>&nbsp; {{ $f }}@if (in_array($f, $aiFlags, true))&nbsp; <span class="aitag">AI</span>@endif</div>
            @endforeach
        @else
            <p class="muted">{{ __('pdf.signals_empty') }}</p>
        @endif

        @if ($aiFindings)
            <div class="ai">
                <div class="ai__title"><span class="aitag aitag--soft">AI</span>&nbsp; {{ __('pdf.ai_findings_title') }}</div>
                @if (! empty($aiFindings['raised_score']))
                    <p class="ai__score">{{ __('pdf.ai_findings_raised', ['ai' => $aiFindings['score'], 'rule' => $aiFindings['rule_score']]) }}</p>
                @endif
                @if (count($aiFlags))
                    <p class="ai__lead">{{ __('pdf.ai_findings_lead') }}</p>
                    @foreach ($aiFlags as $f)
                        <div class="ai__item"><span class="dot">&#9679;</span>&nbsp; {{ $f }}</div>
                    @endforeach
                @endif
            </div>
        @endif

        @if (count($rec))
            <h2>{{ __('pdf.recommendations_heading') }}</h2>
            <ol class="rec">
                @foreach ($rec as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </ol>
        @endif

        @if (count($verify))
            <h2>{{ __('pdf.verify_heading') }}</h2>
            <table class="check" width="100%" cellpadding="0" cellspacing="0">
                @foreach ($verify as $item)
                    <tr>
                        <td style="width: 18pt; padding-top: 7pt;"><div class="box"></div></td>
                        <td>{{ $item }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        <h2>{{ __('pdf.summary_heading') }}</h2>
        <p class="summary">{{ $listing->ai_summary }}</p>

        <p class="small" style="margin-top: 12pt;">{{ __('pdf.footer_disclaimer') }}</p>
    </div>

</body>
</html>
