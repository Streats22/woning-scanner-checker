<!DOCTYPE html>
<html lang="{{ $locale }}" class="pdf-html pdf-html--{{ $theme }}">
<head>
    <meta charset="utf-8">
    @if ($theme === 'dark')
    <style>
        /**
         * Dark: zelfde tokens als frontend `html.dark` (main.css — surface-page, text-*, border-subtle, accent).
         */
        @page {
            margin: 0;
            background-color: #0a101d;
        }

        html.pdf-html--dark {
            background: #0a101d !important;
        }
    </style>
    @else
    <style>
        @page {
            margin: 32pt 40pt 36pt 40pt;
            background-color: #ffffff;
        }

        html.pdf-html--light {
            background: #ffffff !important;
        }
    </style>
    @endif
    <style>
        /* Light (default) */
        body.pdf-root--light {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            line-height: 1.45;
            background: #ffffff !important;
            margin: 0;
            padding: 0;
        }
        body.pdf-root--light h1 { font-size: 15pt; margin: 0 0 6pt; color: #0f172a; }
        body.pdf-root--light h2 { font-size: 11pt; margin: 14pt 0 6pt; color: #0f172a; border-bottom: 1px solid #cbd5e1; padding-bottom: 3pt; }
        body.pdf-root--light .muted { color: #64748b; font-size: 9pt; }
        body.pdf-root--light .score { font-size: 28pt; font-weight: bold; color: #0f172a; }
        body.pdf-root--light .pill { display: inline-block; padding: 2pt 8pt; border-radius: 999pt; font-size: 8pt; font-weight: bold; text-transform: uppercase; }
        body.pdf-root--light .p-low { background: #dcfce7; color: #166534; }
        body.pdf-root--light .p-mid { background: #ffedd5; color: #9a3412; }
        body.pdf-root--light .p-high { background: #fee2e2; color: #991b1b; }
        body.pdf-root--light .pdf-pill--ai { background: #dbeafe; color: #1e40af; }
        body.pdf-root--light table.meta { width: 100%; font-size: 9pt; margin-bottom: 10pt; }
        body.pdf-root--light table.meta td { padding: 2pt 8pt 2pt 0; vertical-align: top; }
        body.pdf-root--light ul, body.pdf-root--light ol { margin: 4pt 0 8pt 14pt; padding: 0; }
        body.pdf-root--light li { margin: 3pt 0; color: #1e293b; }
        body.pdf-root--light .break { margin-bottom: 8pt; padding-bottom: 6pt; border-bottom: 1px solid #e2e8f0; }
        body.pdf-root--light .break:last-child { border-bottom: none; }
        body.pdf-root--light .cat { font-weight: bold; color: #1e40af; }
        body.pdf-root--light .summary { white-space: pre-wrap; word-wrap: break-word; font-size: 9pt; color: #334155; }
        body.pdf-root--light .footer { margin-top: 16pt; font-size: 8pt; color: #94a3b8; }
        body.pdf-root--light .pdf-brand { width: 100%; margin-bottom: 12pt; border-bottom: 1px solid #e2e8f0; padding-bottom: 10pt; }
        body.pdf-root--light .pdf-brand td { vertical-align: middle; }
        body.pdf-root--light .pdf-brand__name { font-size: 16pt; font-weight: bold; color: #1e40af; letter-spacing: -0.02em; }
        body.pdf-root--light .pdf-brand__tag { font-size: 8.5pt; color: #64748b; margin-top: 2pt; }
        body.pdf-root--light .pdf-brand__logo { width: 44px; height: 44px; }
        body.pdf-root--light .pdf-brand-foot { margin-top: 14pt; padding-top: 10pt; border-top: 1px solid #e2e8f0; text-align: center; font-size: 8pt; color: #94a3b8; }
        body.pdf-root--light .pdf-brand-foot img { vertical-align: middle; margin-right: 4pt; }
        body.pdf-root--light .pdf-brand-foot strong { color: #1e40af; font-weight: bold; }

        /* Dark — tokens: surface-page #0a101d, text-primary #f1f5f9, text-secondary #94a3b8, text-tertiary #64748b, border-subtle #2d3b55, accent #60a5fa */
        body.pdf-root--dark {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            color: #f1f5f9 !important;
            line-height: 1.45;
            background: #0a101d !important;
            margin: 0 !important;
            padding: 32pt 40pt 36pt 40pt;
        }

        body.pdf-root--dark h1,
        body.pdf-root--dark h2 {
            background: transparent !important;
        }

        body.pdf-root--dark h1 { font-size: 15pt; margin: 0 0 6pt; color: #f1f5f9 !important; }
        body.pdf-root--dark h2 { font-size: 11pt; margin: 14pt 0 6pt; color: #f1f5f9 !important; border-bottom: 1px solid #2d3b55; padding-bottom: 3pt; }
        body.pdf-root--dark p,
        body.pdf-root--dark td,
        body.pdf-root--dark th,
        body.pdf-root--dark li,
        body.pdf-root--dark span { color: #f1f5f9 !important; }
        body.pdf-root--dark .muted { color: #94a3b8 !important; font-size: 9pt; }
        body.pdf-root--dark .score { font-size: 28pt; font-weight: bold; color: #f1f5f9 !important; }
        body.pdf-root--dark .pill { display: inline-block; padding: 2pt 8pt; border-radius: 999pt; font-size: 8pt; font-weight: bold; text-transform: uppercase; }
        body.pdf-root--dark .p-low { background: #14532d !important; color: #bbf7d0 !important; }
        body.pdf-root--dark .p-mid { background: #7c2d12 !important; color: #ffedd5 !important; }
        body.pdf-root--dark .p-high { background: #7f1d1d !important; color: #fecaca !important; }
        body.pdf-root--dark .pdf-pill--ai { background: #172033 !important; color: #60a5fa !important; }
        body.pdf-root--dark table.meta { width: 100%; font-size: 9pt; margin-bottom: 10pt; background: transparent !important; }
        body.pdf-root--dark table.meta td { padding: 2pt 8pt 2pt 0; vertical-align: top; background: transparent !important; }
        body.pdf-root--dark table { background: transparent !important; border-collapse: collapse; }
        body.pdf-root--dark ul,
        body.pdf-root--dark ol {
            margin: 4pt 0 8pt 14pt;
            padding: 0;
            background: transparent !important;
            list-style-color: #64748b;
        }
        body.pdf-root--dark li {
            margin: 3pt 0;
            color: #f1f5f9 !important;
        }
        body.pdf-root--dark .break {
            margin-bottom: 8pt;
            padding-bottom: 6pt;
            border-bottom: 1px solid #2d3b55;
            background: transparent !important;
        }
        body.pdf-root--dark .break:last-child { border-bottom: none; }
        body.pdf-root--dark .cat { font-weight: bold; color: #60a5fa !important; }
        body.pdf-root--dark .summary { white-space: pre-wrap; word-wrap: break-word; font-size: 9pt; color: #94a3b8 !important; }
        body.pdf-root--dark .footer { margin-top: 16pt; font-size: 8pt; color: #64748b !important; }
        body.pdf-root--dark strong { color: #60a5fa !important; font-weight: bold; }
        body.pdf-root--dark .pdf-brand { width: 100%; margin-bottom: 12pt; border-bottom: 1px solid #2d3b55; padding-bottom: 10pt; background: transparent !important; }
        body.pdf-root--dark .pdf-brand td { vertical-align: middle; background: transparent !important; }
        body.pdf-root--dark .pdf-brand__name { font-size: 16pt; font-weight: bold; color: #f1f5f9 !important; letter-spacing: -0.02em; }
        body.pdf-root--dark .pdf-brand__tag { font-size: 8.5pt; color: #94a3b8 !important; margin-top: 2pt; }
        body.pdf-root--dark .pdf-brand__logo { width: 44px; height: 44px; }
        body.pdf-root--dark .pdf-brand-foot {
            margin-top: 14pt;
            padding-top: 10pt;
            border-top: 1px solid #2d3b55;
            text-align: center;
            font-size: 8pt;
            color: #94a3b8 !important;
            background: transparent !important;
        }
        body.pdf-root--dark .pdf-brand-foot img { vertical-align: middle; margin-right: 4pt; }
        body.pdf-root--dark .pdf-brand-foot strong { color: #60a5fa !important; font-weight: bold; }
    </style>
</head>
<body class="pdf-root--{{ $theme }}">
@php
    $logoPath = public_path('img/huurradar-mark.svg');
    $logoDataUri = is_readable($logoPath)
        ? 'data:image/svg+xml;base64,'.base64_encode((string) file_get_contents($logoPath))
        : '';
@endphp
    <table class="pdf-brand" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 52px; padding-right: 10pt;">
                @if ($logoDataUri !== '')
                    <img class="pdf-brand__logo" src="{{ $logoDataUri }}" width="44" height="44" alt="" />
                @endif
            </td>
            <td>
                <div class="pdf-brand__name">{{ __('pdf.brand_name') }}</div>
                <div class="pdf-brand__tag">{{ __('pdf.brand_tagline') }}</div>
            </td>
        </tr>
    </table>

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
@endphp
    <h1>{{ __('pdf.report_title') }}</h1>
    <p class="muted">{{ $listing->report_slug ?? __('pdf.report_number', ['id' => $listing->id]) }} · {{ $listing->created_at?->format('d-m-Y H:i') ?? '—' }}</p>

    <table class="meta">
        <tr>
            <td><span class="score">{{ $listing->scam_score }}</span></td>
            <td>
                <span class="pill {{ $tier }}">{{ $tierLabel }}</span>
                @if (!empty($snap['llm_used']))
                    <span class="pill pdf-pill--ai">{{ __('pdf.ai_badge') }}</span>
                @endif
            </td>
        </tr>
        @if ((int) $ruleScore !== $s)
            <tr>
                <td colspan="2" class="muted">{{ __('pdf.rule_engine_line', ['rule' => $ruleScore, 'final' => $s]) }}</td>
            </tr>
        @endif
    </table>

    <h2>{{ __('pdf.market_heading') }}</h2>
    <p class="muted">{{ __('pdf.market_benchmark', ['avg' => $listing->market_average ?? '—']) }}
        @if ($listing->market_difference_percent !== null)
            {{ ' '.__('pdf.market_diff', ['pct' => $listing->market_difference_percent]) }}
        @endif
    </p>

    @if (count($breakdown))
        <h2>{{ __('pdf.risk_breakdown_heading') }}</h2>
        @foreach ($breakdown as $row)
            <div class="break">
                <span class="cat">{{ $row['category'] ?? __('pdf.breakdown_fallback') }}</span>
                <span class="muted"> ({{ __('pdf.points', ['n' => $row['points'] ?? 0]) }})</span>
                @if (!empty($row['detail']))
                    <p class="muted pdf-detail" style="margin:4pt 0 0;">{{ $row['detail'] }}</p>
                @endif
            </div>
        @endforeach
    @endif

    <h2>{{ __('pdf.signals_heading') }}</h2>
    @if (count($flags))
        <ul>
            @foreach ($flags as $f)
                <li>{{ $f }}</li>
            @endforeach
        </ul>
    @else
        <p class="muted">{{ __('pdf.signals_empty') }}</p>
    @endif

    @if (count($rec))
        <h2>{{ __('pdf.recommendations_heading') }}</h2>
        <ol>
            @foreach ($rec as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ol>
    @endif

    @if (count($verify))
        <h2>{{ __('pdf.verify_heading') }}</h2>
        <ol>
            @foreach ($verify as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ol>
    @endif

    <h2>{{ __('pdf.summary_heading') }}</h2>
    <p class="summary">{{ $listing->ai_summary }}</p>

    <p class="footer">{{ __('pdf.footer_disclaimer') }}</p>

    <div class="pdf-brand-foot">
        @if ($logoDataUri !== '')
            <img src="{{ $logoDataUri }}" width="20" height="20" alt="" />
        @endif
        <strong>{{ __('pdf.brand_name') }}</strong> — {{ __('pdf.foot_tagline') }}
    </div>
</body>
</html>
