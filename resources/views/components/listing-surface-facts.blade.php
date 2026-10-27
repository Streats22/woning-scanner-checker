@php
    $s = $listingFacts['surface_m2'];
    $ppm = $listingFacts['price_per_m2_month_eur'] ?? null;
    $bpm = $listingFacts['benchmark_per_m2_month_eur'] ?? null;
    $adj = $listingFacts['adjusted_benchmark_per_m2_month_eur'] ?? null;
    $diff = $listingFacts['per_m2_vs_adjusted_percent'] ?? null;
    $small = ! empty($listingFacts['small_surface']);
    $refM2 = $listingFacts['benchmark_reference_m2'] ?? 55;
    $label = $listingFacts['energy_label'] ?? null;
    $labelPoints = $listingFacts['energy_label_wws_points'] ?? null;
    $labelText = $label !== null
        ? $label.($labelPoints !== null ? ' ('.__('pdf.facts_energy_label_points', ['points' => ($labelPoints > 0 ? '+' : '').number_format((float) $labelPoints, $labelPoints == floor($labelPoints) ? 0 : 1, ',', '.')]).')' : '')
        : __('pdf.facts_energy_label_missing');
@endphp
@if ($variant === 'pdf')
    <tr>
        <td class="muted">{{ __('pdf.facts_surface_m2') }}</td>
        <td>{{ is_numeric($s) ? number_format((float) $s, $s == floor($s) ? 0 : 1, ',', '.').' m²' : __('pdf.facts_empty') }}</td>
    </tr>
    @if ($ppm !== null)
        <tr>
            <td class="muted">{{ __('pdf.facts_price_per_m2') }}</td>
            <td>€ {{ number_format((int) $ppm, 0, ',', '.') }} {{ __('pdf.facts_per_m2_suffix') }}</td>
        </tr>
    @endif
    @if ($bpm !== null)
        <tr>
            <td class="muted">{{ __('pdf.facts_benchmark_per_m2') }}</td>
            <td>€ {{ number_format((int) $bpm, 0, ',', '.') }} {{ __('pdf.facts_per_m2_suffix') }} ({{ __('pdf.facts_per_m2_model_note', ['m2' => $refM2]) }})</td>
        </tr>
    @endif
    @if ($adj !== null && $ppm !== null)
        <tr>
            <td class="muted">{{ __('pdf.facts_adjusted_per_m2') }}</td>
            <td>€ {{ number_format((float) $adj, 2, ',', '.') }} {{ __('pdf.facts_per_m2_suffix') }}</td>
        </tr>
        <tr>
            <td class="muted">{{ __('pdf.facts_per_m2_diff') }}</td>
            <td>
                @if ($diff !== null)
                    {{ $diff > 0 ? '+' : '' }}{{ $diff }}%
                @else
                    {{ __('pdf.facts_empty') }}
                @endif
            </td>
        </tr>
    @endif
    @if ($small)
        <tr>
            <td colspan="2" class="muted" style="font-size:8pt;padding-top:4pt;">{{ __('pdf.facts_per_m2_small_unit') }}</td>
        </tr>
    @endif
    @if (array_key_exists('energy_label', $listingFacts))
        <tr>
            <td class="muted">{{ __('pdf.facts_energy_label') }}</td>
            <td>{{ $labelText }}</td>
        </tr>
    @endif
@else
    <tr>
        <td style="padding:0.35rem 0.75rem 0.35rem 0; color:var(--muted); vertical-align:top;">{{ __('pdf.facts_surface_m2') }}</td>
        <td style="padding:0.35rem 0;">{{ is_numeric($s) ? number_format((float) $s, $s == floor($s) ? 0 : 1, ',', '.').' m²' : '—' }}</td>
    </tr>
    @if ($ppm !== null)
        <tr>
            <td style="padding:0.35rem 0.75rem 0.35rem 0; color:var(--muted); vertical-align:top;">{{ __('pdf.facts_price_per_m2') }}</td>
            <td style="padding:0.35rem 0;">€ {{ number_format((int) $ppm, 0, ',', '.') }} {{ __('pdf.facts_per_m2_suffix') }}</td>
        </tr>
    @endif
    @if ($bpm !== null)
        <tr>
            <td style="padding:0.35rem 0.75rem 0.35rem 0; color:var(--muted); vertical-align:top;">{{ __('pdf.facts_benchmark_per_m2') }}</td>
            <td style="padding:0.35rem 0;">€ {{ number_format((int) $bpm, 0, ',', '.') }} {{ __('pdf.facts_per_m2_suffix') }} ({{ __('pdf.facts_per_m2_model_note', ['m2' => $refM2]) }})</td>
        </tr>
    @endif
    @if ($adj !== null && $ppm !== null)
        <tr>
            <td style="padding:0.35rem 0.75rem 0.35rem 0; color:var(--muted); vertical-align:top;">{{ __('pdf.facts_adjusted_per_m2') }}</td>
            <td style="padding:0.35rem 0;">€ {{ number_format((float) $adj, 2, ',', '.') }} {{ __('pdf.facts_per_m2_suffix') }}</td>
        </tr>
        <tr>
            <td style="padding:0.35rem 0.75rem 0.35rem 0; color:var(--muted); vertical-align:top;">{{ __('pdf.facts_per_m2_diff') }}</td>
            <td style="padding:0.35rem 0;">
                @if ($diff !== null)
                    {{ $diff > 0 ? '+' : '' }}{{ $diff }}%
                @else
                    —
                @endif
            </td>
        </tr>
    @endif
    @if ($small)
        <tr>
            <td colspan="2" style="padding:0.5rem 0 0; color:var(--muted); font-size:0.85rem;">{{ __('pdf.facts_per_m2_small_unit') }}</td>
        </tr>
    @endif
    @if (array_key_exists('energy_label', $listingFacts))
        <tr>
            <td style="padding:0.35rem 0.75rem 0.35rem 0; color:var(--muted); vertical-align:top;">{{ __('pdf.facts_energy_label') }}</td>
            <td style="padding:0.35rem 0;">{{ $labelText }}</td>
        </tr>
    @endif
@endif
