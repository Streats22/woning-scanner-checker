@if ($variant === 'pdf')
    <tr>
        <td class="muted">{{ __('pdf.facts_dwelling_kind') }}</td>
        <td>{{ $lines['kind_line'] }}</td>
    </tr>
    <tr>
        <td class="muted">{{ __('pdf.facts_dwelling_sector') }}</td>
        <td>{{ $lines['sector_line'] }}</td>
    </tr>
    @if (! empty($lines['signals']))
        <tr>
            <td class="muted">{{ __('pdf.facts_dwelling_signals') }}</td>
            <td>{{ $lines['signals'] }}</td>
        </tr>
    @endif
@else
    <tr>
        <td style="padding:0.35rem 0.75rem 0.35rem 0; color:var(--muted); vertical-align:top;">{{ __('pdf.facts_dwelling_kind') }}</td>
        <td style="padding:0.35rem 0;">{{ $lines['kind_line'] }}</td>
    </tr>
    <tr>
        <td style="padding:0.35rem 0.75rem 0.35rem 0; color:var(--muted); vertical-align:top;">{{ __('pdf.facts_dwelling_sector') }}</td>
        <td style="padding:0.35rem 0;">{{ $lines['sector_line'] }}</td>
    </tr>
    @if (! empty($lines['signals']))
        <tr>
            <td style="padding:0.35rem 0.75rem 0.35rem 0; color:var(--muted); vertical-align:top;">{{ __('pdf.facts_dwelling_signals') }}</td>
            <td style="padding:0.35rem 0;">{{ $lines['signals'] }}</td>
        </tr>
    @endif
@endif
