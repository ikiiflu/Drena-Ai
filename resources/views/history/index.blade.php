@extends('layout.body')

@section('title', 'AquaSense — Histórico')

@section('content')
<div class="dash-header">
    <div>
        <h1 class="dash-header-title">Histórico de leituras</h1>
        <div class="dash-header-meta">
            <span>{{ $readings->count() }} registros exibidos</span>
        </div>
    </div>
</div>

<section style="padding:0 1.5rem 1rem">
    <form method="GET" action="{{ route('history.index') }}"
          style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap">
        <label for="sensor_id" style="font-size:0.8rem;color:var(--text-muted)">Sensor:</label>
        <select id="sensor_id" name="sensor_id" onchange="this.form.submit()"
                style="background:var(--panel);border:1px solid var(--border);color:var(--text-primary);
                       padding:0.4rem 0.75rem;border-radius:6px;font-size:0.85rem">
            @foreach($sensors as $s)
                <option value="{{ $s->id }}" {{ $s->id == $selected ? 'selected' : '' }}>
                    {{ $s->code }} — {{ $s->name }}
                </option>
            @endforeach
        </select>
    </form>
</section>

<section style="padding:0 1.5rem 2rem;overflow-x:auto">
    @if($readings->isEmpty())
        <div class="empty-state" style="margin-top:3rem">
            <div class="empty-state-icon">○</div>
            <div class="empty-state-title">Sem leituras</div>
            <div class="empty-state-desc">Execute <code>php artisan sensor:simulate --backfill=24</code> para gerar histórico.</div>
        </div>
    @else
        <table style="width:100%;border-collapse:collapse;font-size:0.82rem;font-family:var(--font-mono)">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid var(--border);color:var(--text-muted)">
                    <th style="padding:0.5rem 0.75rem">Data/Hora</th>
                    <th style="padding:0.5rem 0.75rem">Obstrução (%)</th>
                    <th style="padding:0.5rem 0.75rem">Precipitação (mm)</th>
                    <th style="padding:0.5rem 0.75rem">Vazão (L/s)</th>
                    <th style="padding:0.5rem 0.75rem">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($readings as $r)
                    @php
                        $obs = $r->obstruction_pct;
                        $st  = $obs >= 70 ? 'critico' : ($obs >= 40 ? 'risco' : ($obs >= 10 ? 'atencao' : 'ok'));
                    @endphp
                    <tr style="border-bottom:1px solid color-mix(in srgb,var(--border) 50%,transparent)">
                        <td style="padding:0.45rem 0.75rem;color:var(--text-secondary)">
                            {{ $r->recorded_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td style="padding:0.45rem 0.75rem">{{ number_format($r->obstruction_pct, 2) }}</td>
                        <td style="padding:0.45rem 0.75rem">{{ number_format($r->rainfall_mm, 3) }}</td>
                        <td style="padding:0.45rem 0.75rem">{{ number_format($r->flow_lps, 3) }}</td>
                        <td style="padding:0.45rem 0.75rem">
                            <span style="color:var(--status-{{ $st }});font-weight:600">{{ ucfirst($st) }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>
@stop
