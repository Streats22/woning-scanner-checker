<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1a1a1a; line-height: 1.45; }
        h1 { font-size: 15pt; margin: 0 0 6pt; color: #0f172a; }
        h2 { font-size: 11pt; margin: 14pt 0 6pt; color: #0f172a; border-bottom: 1px solid #cbd5e1; padding-bottom: 3pt; }
        .muted { color: #64748b; font-size: 9pt; }
        .score { font-size: 28pt; font-weight: bold; color: #0f172a; }
        .pill { display: inline-block; padding: 2pt 8pt; border-radius: 999pt; font-size: 8pt; font-weight: bold; text-transform: uppercase; }
        .p-low { background: #dcfce7; color: #166534; }
        .p-mid { background: #ffedd5; color: #9a3412; }
        .p-high { background: #fee2e2; color: #991b1b; }
        table.meta { width: 100%; font-size: 9pt; margin-bottom: 10pt; }
        table.meta td { padding: 2pt 8pt 2pt 0; vertical-align: top; }
        ul { margin: 4pt 0 8pt 14pt; padding: 0; }
        li { margin: 3pt 0; }
        .break { margin-bottom: 8pt; padding-bottom: 6pt; border-bottom: 1px solid #e2e8f0; }
        .break:last-child { border-bottom: none; }
        .cat { font-weight: bold; color: #0d9488; }
        .summary { white-space: pre-wrap; word-wrap: break-word; font-size: 9pt; color: #334155; }
        .footer { margin-top: 16pt; font-size: 8pt; color: #94a3b8; }
    </style>
</head>
<body>
@php
    $s = (int) $listing->scam_score;
    $tier = $s <= 30 ? 'p-low' : ($s <= 60 ? 'p-mid' : 'p-high');
    $tierLabel = $s <= 30 ? 'Laag risico' : ($s <= 60 ? 'Matig risico' : 'Hoog risico');
    $flags = $listing->scam_flags ?? [];
    $snap = $listing->report_snapshot ?? [];
    $ruleScore = $snap['rule_score'] ?? $s;
    $rec = $snap['recommendations'] ?? [];
    $verify = $snap['what_to_verify'] ?? [];
    $breakdown = $snap['risk_breakdown'] ?? [];
@endphp
    <h1>Woning Scam Checker — rapport</h1>
    <p class="muted">{{ $listing->report_slug ?? 'Rapport #'.$listing->id }} · {{ $listing->created_at?->format('d-m-Y H:i') ?? '—' }}</p>

    <table class="meta">
        <tr>
            <td><span class="score">{{ $listing->scam_score }}</span></td>
            <td>
                <span class="pill {{ $tier }}">{{ $tierLabel }}</span>
                @if (!empty($snap['llm_used']))
                    <span class="pill" style="background:#ccfbf1;color:#0f766e;">AI</span>
                @endif
            </td>
        </tr>
        @if ((int) $ruleScore !== $s)
            <tr>
                <td colspan="2" class="muted">Regel-engine: {{ $ruleScore }} — eindscore: {{ $s }}</td>
            </tr>
        @endif
    </table>

    <h2>Marktbenchmark</h2>
    <p class="muted">Geschatte benchmark: € {{ $listing->market_average ?? '—' }} per maand.
        @if ($listing->market_difference_percent !== null)
            Afwijking t.o.v. prijs in advertentie: {{ $listing->market_difference_percent }}%.
        @endif
    </p>

    @if (count($breakdown))
        <h2>Risico per onderdeel</h2>
        @foreach ($breakdown as $row)
            <div class="break">
                <span class="cat">{{ $row['category'] ?? 'Onderdeel' }}</span>
                <span class="muted"> ({{ $row['points'] ?? 0 }} pt)</span>
                @if (!empty($row['detail']))
                    <p class="muted" style="margin:4pt 0 0;">{{ $row['detail'] }}</p>
                @endif
            </div>
        @endforeach
    @endif

    <h2>Signalen</h2>
    @if (count($flags))
        <ul>
            @foreach ($flags as $f)
                <li>{{ $f }}</li>
            @endforeach
        </ul>
    @else
        <p class="muted">Geen automatisch gedetecteerde signalen.</p>
    @endif

    @if (count($rec))
        <h2>Aanbevelingen</h2>
        <ol>
            @foreach ($rec as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ol>
    @endif

    @if (count($verify))
        <h2>Controleer vóór je betaalt</h2>
        <ol>
            @foreach ($verify as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ol>
    @endif

    <h2>Toelichting</h2>
    <p class="summary">{{ $listing->ai_summary }}</p>

    <p class="footer">Geen juridisch of financieel advies. Geautomatiseerde inschatting op basis van tekstpatronen en eenvoudige benchmark.</p>
</body>
</html>
