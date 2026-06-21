@extends('layout.body')

@section('title', 'AquaSense — Configurações')

@section('content')
<div class="dash-header">
    <div>
        <h1 class="dash-header-title">Configurações</h1>
        <div class="dash-header-meta">
            <span>Parâmetros do sistema de monitoramento</span>
        </div>
    </div>
</div>

@if(session('success'))
    <div style="margin:0 1.5rem 1rem;padding:0.75rem 1rem;background:color-mix(in srgb,var(--status-ok) 12%,transparent);
                border:1px solid var(--status-ok);border-radius:8px;color:var(--status-ok);font-size:0.85rem">
        {{ session('success') }}
    </div>
@endif

<form method="POST" action="{{ route('settings.update') }}" style="padding:0 1.5rem 2rem">
    @csrf

    {{-- ---- Coleta de dados ---- --}}
    <section style="margin-bottom:2rem">
        <h2 style="font-size:0.8rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;
                   color:var(--text-muted);margin-bottom:1rem;padding-bottom:0.5rem;
                   border-bottom:1px solid var(--border)">
            Coleta de dados
        </h2>

        <div class="settings-field">
            <label for="reading_interval_seconds" class="settings-label">
                Intervalo entre leituras
                <span class="settings-hint">Tempo (em segundos) entre cada coleta automática de todos os sensores.</span>
            </label>
            <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap">
                <input
                    type="number"
                    id="reading_interval_seconds"
                    name="reading_interval_seconds"
                    value="{{ old('reading_interval_seconds', $settings->get('reading_interval_seconds')?->value ?? 60) }}"
                    min="10"
                    max="86400"
                    class="settings-input"
                    style="width:120px">
                <span style="font-size:0.8rem;color:var(--text-muted)">segundos</span>

                {{-- Atalhos rápidos --}}
                <div style="display:flex;gap:0.4rem;flex-wrap:wrap">
                    @foreach([['30','30 s'],['60','1 min'],['300','5 min'],['600','10 min'],['1800','30 min'],['3600','1 h']] as [$val,$lbl])
                        <button type="button"
                                onclick="document.getElementById('reading_interval_seconds').value='{{ $val }}'"
                                style="padding:0.25rem 0.6rem;font-size:0.72rem;background:var(--panel);
                                       border:1px solid var(--border);border-radius:5px;color:var(--text-secondary);
                                       cursor:pointer;transition:all 0.15s"
                                onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
                                onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-secondary)'">
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>
            </div>
            @error('reading_interval_seconds')
                <p style="color:var(--status-critico);font-size:0.75rem;margin-top:0.4rem">{{ $message }}</p>
            @enderror
        </div>
    </section>

    {{-- ---- Limites de alerta ---- --}}
    <section style="margin-bottom:2rem">
        <h2 style="font-size:0.8rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;
                   color:var(--text-muted);margin-bottom:1rem;padding-bottom:0.5rem;
                   border-bottom:1px solid var(--border)">
            Limites de obstrução para alertas
        </h2>

        @foreach([
            ['alert_threshold_atencao', 'Atenção',  'atencao', 'Obstrução (%) a partir da qual o sensor entra em estado de atenção.'],
            ['alert_threshold_risco',   'Risco',    'risco',   'Obstrução (%) a partir da qual o sensor entra em estado de risco.'],
            ['alert_threshold_critico', 'Crítico',  'critico', 'Obstrução (%) a partir da qual o sensor entra em estado crítico.'],
        ] as [$key, $label, $status, $hint])
            <div class="settings-field">
                <label for="{{ $key }}" class="settings-label">
                    <span style="display:inline-flex;align-items:center;gap:0.5rem">
                        <span style="width:10px;height:10px;border-radius:50%;background:var(--status-{{ $status }});flex-shrink:0"></span>
                        {{ $label }}
                    </span>
                    <span class="settings-hint">{{ $hint }}</span>
                </label>
                <div style="display:flex;align-items:center;gap:0.75rem">
                    <input
                        type="number"
                        id="{{ $key }}"
                        name="{{ $key }}"
                        value="{{ old($key, $settings->get($key)?->value ?? '') }}"
                        min="1"
                        max="99"
                        class="settings-input"
                        style="width:90px">
                    <span style="font-size:0.8rem;color:var(--text-muted)">%</span>
                </div>
                @error($key)
                    <p style="color:var(--status-critico);font-size:0.75rem;margin-top:0.4rem">{{ $message }}</p>
                @enderror
            </div>
        @endforeach
    </section>

    {{-- ---- Comando de simulação ---- --}}
    <section style="margin-bottom:2rem">
        <h2 style="font-size:0.8rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;
                   color:var(--text-muted);margin-bottom:1rem;padding-bottom:0.5rem;
                   border-bottom:1px solid var(--border)">
            Simulação de dados (terminal)
        </h2>
        @php $interval = $settings->get('reading_interval_seconds')?->value ?? 60; @endphp
        <div style="background:var(--panel);border:1px solid var(--border);border-radius:8px;padding:1rem;
                    font-family:var(--font-mono);font-size:0.8rem;color:var(--text-secondary)">
            <div style="margin-bottom:0.5rem;color:var(--text-muted);font-size:0.72rem">
                Comandos para gerar leituras com o intervalo configurado:
            </div>
            <div>php artisan sensor:simulate <span style="color:var(--accent)">--loop={{ $interval }}</span></div>
            <div style="margin-top:0.35rem">php artisan sensor:simulate <span style="color:var(--accent)">--backfill=24</span></div>
        </div>
    </section>

    <button type="submit"
            style="padding:0.6rem 1.5rem;background:var(--accent);color:var(--void);
                   font-weight:700;font-size:0.85rem;border:none;border-radius:8px;
                   cursor:pointer;transition:opacity 0.15s"
            onmouseover="this.style.opacity='0.85'"
            onmouseout="this.style.opacity='1'">
        Salvar configurações
    </button>
</form>
@stop

@push('styles')
<style>
.settings-field {
    margin-bottom: 1.25rem;
}
.settings-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}
.settings-hint {
    display: block;
    font-size: 0.75rem;
    font-weight: 400;
    color: var(--text-muted);
    margin-top: 0.2rem;
}
.settings-input {
    background: var(--panel);
    border: 1px solid var(--border);
    color: var(--text-primary);
    padding: 0.45rem 0.75rem;
    border-radius: 6px;
    font-size: 0.9rem;
    font-family: var(--font-mono);
    transition: border-color 0.15s;
}
.settings-input:focus {
    outline: none;
    border-color: var(--accent);
}
</style>
@endpush
