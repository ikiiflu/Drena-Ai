@extends('layout.body')

@section('title', 'Mapa')

@section('content')
    <div class="map-section" style="height: calc(100vh - 120px); display: flex; flex-direction: column;">
        <div class="map-overlay-top">
            <div class="map-pill" aria-label="Sensores no mapa">10 sensores</div>
            <div class="map-pill" id="map-time" aria-live="off">--:--</div>
        </div>
        <div id="city-map" class="map-container" style="flex: 1; min-height: 100%;"></div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/maplibre-gl/dist/maplibre-gl.js"></script>
    <link href="https://unpkg.com/maplibre-gl/dist/maplibre-gl.css" rel="stylesheet">
@endpush