@extends('layout.body')

@section('title', 'AquaSense — Gráficos')

@section('content')
<div class="dash-header">
    <div>
        <h1 class="dash-header-title">Gráficos analíticos</h1>
        <div class="dash-header-meta">
            <span>Dados em tempo real · últimas 6 horas</span>
        </div>
    </div>
</div>

{{-- Obstrução por sensor (barras) --}}
<section style="padding:0 1.5rem 2rem">
    <h2 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--text-secondary)">
        Obstrução atual por sensor
    </h2>

    @if($sensors->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">○</div>
            <div class="empty-state-title">Sem dados</div>
            <div class="empty-state-desc">Execute o seeder e a simulação de leituras.</div>
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:0.6rem">
            @foreach($sensors as $s)
                @php
                    $obs   = $s->latestReading?->obstruction_pct ?? 0;
                    $st    = $obs >= 70 ? 'critico' : ($obs >= 40 ? 'risco' : ($obs >= 10 ? 'atencao' : 'ok'));
                    $width = max(2, (int) $obs);
                @endphp
                <div style="display:flex;align-items:center;gap:0.75rem">
                    <span style="font-size:0.75rem;font-family:var(--font-mono);color:var(--text-muted);width:5rem;flex-shrink:0">
                        {{ $s->code }}
                    </span>
                    <div style="flex:1;background:var(--panel);border-radius:4px;height:18px;overflow:hidden">
                        <div style="width:{{ $width }}%;height:100%;background:var(--status-{{ $st }});
                                    transition:width 0.4s ease;border-radius:4px"></div>
                    </div>
                    <span style="font-size:0.75rem;font-family:var(--font-mono);color:var(--status-{{ $st }});
                                 width:3.5rem;text-align:right">
                        {{ number_format($obs, 1) }}%
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</section>

{{-- Vazão por sensor (barras) --}}
<section style="padding:0 1.5rem 2rem">
    <h2 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--text-secondary)">
        Vazão atual por sensor (L/s)
    </h2>

    @if($sensors->isNotEmpty())
        @php $maxFlow = $sensors->map(fn($s) => $s->latestReading?->flow_lps ?? 0)->max() ?: 1; @endphp
        <div style="display:flex;flex-direction:column;gap:0.6rem">
            @foreach($sensors as $s)
                @php
                    $flow  = $s->latestReading?->flow_lps ?? 0;
                    $w     = max(2, (int) (($flow / $maxFlow) * 100));
                @endphp
                <div style="display:flex;align-items:center;gap:0.75rem">
                    <span style="font-size:0.75rem;font-family:var(--font-mono);color:var(--text-muted);width:5rem;flex-shrink:0">
                        {{ $s->code }}
                    </span>
                    <div style="flex:1;background:var(--panel);border-radius:4px;height:18px;overflow:hidden">
                        <div style="width:{{ $w }}%;height:100%;background:var(--accent);
                                    opacity:0.8;transition:width 0.4s ease;border-radius:4px"></div>
                    </div>
                    <span style="font-size:0.75rem;font-family:var(--font-mono);color:var(--text-secondary);
                                 width:4rem;text-align:right">
                        {{ number_format($flow, 1) }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</section>

{{-- Tabela resumo por região --}}
<section style="padding:0 1.5rem 2rem">
    <h2 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--text-secondary)">
        Resumo por região
    </h2>

    @php
        $byRegion = $sensors->groupBy('region')->map(function($group) {
            $readings = $group->map(fn($s) => $s->latestReading)->filter();
            return [
                'count'           => $group->count(),
                'avg_obstruction' => $readings->isEmpty() ? null : round($readings->avg('obstruction_pct'), 1),
                'avg_rainfall'    => $readings->isEmpty() ? null : round($readings->avg('rainfall_mm'), 2),
                'avg_flow'        => $readings->isEmpty() ? null : round($readings->avg('flow_lps'), 1),
            ];
        })->sortKeys();
    @endphp

    <table style="width:100%;border-collapse:collapse;font-size:0.82rem">
        <thead>
            <tr style="text-align:left;border-bottom:1px solid var(--border);color:var(--text-muted)">
                <th style="padding:0.5rem 0.75rem">Região</th>
                <th style="padding:0.5rem 0.75rem">Sensores</th>
                <th style="padding:0.5rem 0.75rem">Obst. média (%)</th>
                <th style="padding:0.5rem 0.75rem">Precipitação (mm)</th>
                <th style="padding:0.5rem 0.75rem">Vazão (L/s)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($byRegion as $region => $data)
                <tr style="border-bottom:1px solid color-mix(in srgb,var(--border) 50%,transparent)">
                    <td style="padding:0.5rem 0.75rem;font-weight:600">{{ $region }}</td>
                    <td style="padding:0.5rem 0.75rem;color:var(--text-secondary)">{{ $data['count'] }}</td>
                    <td style="padding:0.5rem 0.75rem;font-family:var(--font-mono)">{{ $data['avg_obstruction'] ?? '—' }}</td>
                    <td style="padding:0.5rem 0.75rem;font-family:var(--font-mono)">{{ $data['avg_rainfall'] ?? '—' }}</td>
                    <td style="padding:0.5rem 0.75rem;font-family:var(--font-mono)">{{ $data['avg_flow'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>
@stop
