@extends('layout.body')

@section('title', 'AquaSense — Manutenção')

@section('content')
<div class="dash-header">
    <div>
        <h1 class="dash-header-title">Manutenção</h1>
        <div class="dash-header-meta">
            <span>{{ $records->total() }} registros</span>
        </div>
    </div>
</div>

<section style="padding:0 1.5rem 2rem;overflow-x:auto">
    @if($records->isEmpty())
        <div class="empty-state" style="margin-top:3rem">
            <div class="empty-state-icon">○</div>
            <div class="empty-state-title">Sem registros de manutenção</div>
            <div class="empty-state-desc">Os registros serão exibidos aqui após a primeira manutenção cadastrada.</div>
        </div>
    @else
        <table style="width:100%;border-collapse:collapse;font-size:0.82rem">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid var(--border);color:var(--text-muted)">
                    <th style="padding:0.5rem 0.75rem">Data</th>
                    <th style="padding:0.5rem 0.75rem">Sensor</th>
                    <th style="padding:0.5rem 0.75rem">Operador</th>
                    <th style="padding:0.5rem 0.75rem">Descrição</th>
                    <th style="padding:0.5rem 0.75rem">Observações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $r)
                    <tr style="border-bottom:1px solid color-mix(in srgb,var(--border) 50%,transparent)">
                        <td style="padding:0.5rem 0.75rem;font-family:var(--font-mono);color:var(--text-secondary)">
                            {{ $r->performed_at->format('d/m/Y H:i') }}
                        </td>
                        <td style="padding:0.5rem 0.75rem">
                            <span style="font-size:0.7rem;color:var(--accent);font-family:var(--font-mono)">
                                {{ $r->sensor->code ?? '—' }}
                            </span><br>
                            <span>{{ $r->sensor->name ?? '—' }}</span>
                        </td>
                        <td style="padding:0.5rem 0.75rem">{{ $r->operator_name }}</td>
                        <td style="padding:0.5rem 0.75rem">{{ $r->description }}</td>
                        <td style="padding:0.5rem 0.75rem;color:var(--text-muted)">{{ $r->notes ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:1.5rem">
            {{ $records->links() }}
        </div>
    @endif
</section>
@stop
