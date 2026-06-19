@extends('layout.body')

@section('title', 'Drena Aí — Dashboard')

@section('content')
    <div class="dash-header">
        <div>
            <h1 class="dash-header-title">Visão operacional</h1>
            <div class="dash-header-meta">
                <span>Território: Central</span>
                <span aria-hidden="true">·</span>
                <span>Turno: Diurno</span>
            </div>
        </div>
    </div>

    <section class="city-band" id="city-band" aria-label="Faixa de status das regiões da cidade" role="status" aria-live="polite">
    </section>

    <section class="metrics-grid" aria-label="Indicadores da rede de drenagem">
        <div class="metric-card" role="region" aria-label="Percentual de obstrução médio">
            <div class="metric-card-eyebrow">
                <svg class="metric-card-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5"/><circle cx="8" cy="5" r="1.5" fill="currentColor"/></svg>
                Obst. média
            </div>
            <div class="metric-card-value" id="metric-obstruction" aria-live="polite">18%<span class="unit">obstrução</span></div>
            <div class="metric-card-status">
                <div class="metric-card-status-dot ok" aria-hidden="true"></div>
                Dentro do limite
            </div>
            <div class="metric-card-spark" aria-hidden="true"></div>
        </div>

        <div class="metric-card" role="region" aria-label="Índice pluviométrico">
            <div class="metric-card-eyebrow">
                <svg class="metric-card-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 1v11M5 6l3-5 3 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M4 9l4-3 4 3v4H4V9Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
                Precipitação
            </div>
            <div class="metric-card-value" id="metric-rainfall" aria-live="polite">4.2<span class="unit">mm</span></div>
            <div class="metric-card-status">
                <div class="metric-card-status-dot ok" aria-hidden="true"></div>
                Chuva moderada
            </div>
            <div class="metric-card-spark" aria-hidden="true"></div>
        </div>

        <div class="metric-card" role="region" aria-label="Volume de vazão das galerias">
            <div class="metric-card-eyebrow">
                <svg class="metric-card-icon" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M2 8c2-4 4-6 6-6s4 2 6 6-4 6-6 6-4-2-6-6Z" stroke="currentColor" stroke-width="1.5"/><path d="M6 8h4M8 6v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                Vazão
            </div>
            <div class="metric-card-value" id="metric-flow" aria-live="polite">378<span class="unit">L/s</span></div>
            <div class="metric-card-status">
                <div class="metric-card-status-dot ok" aria-hidden="true"></div>
                Fluxo normal
            </div>
            <div class="metric-card-spark" aria-hidden="true"></div>
        </div>
    </section>

    <section class="alert-section" aria-label="Central de alertas">
        <div class="alert-header">
            <h2 class="alert-header-title">Alertas recentes</h2>
            <span class="alert-badge" id="alert-badge" aria-live="polite">0</span>
        </div>
        <ul class="alert-list" id="alert-list" role="log" aria-label="Lista de alertas ativos">
        </ul>
    </section>
@stop
