/**
 * ============================================================
 * AquaSense — Dashboard Controller
 * Consome /api/sensors e /api/alerts/active para manter
 * métricas e alertas atualizados a cada 30 segundos.
 * O sidebar e o city-band são renderizados pelo Blade;
 * este script apenas enriquece com interações de mapa.
 * ============================================================ */

(function () {
  "use strict";

  var STATUS_MAP = { ok: 0, atencao: 1, risco: 2, critico: 3 };

  // ---- DOM refs ----
  var clockEl     = document.getElementById("statusbar-clock");
  var metricObs   = document.getElementById("metric-obstruction");
  var metricChuva = document.getElementById("metric-rainfall");
  var metricVazao = document.getElementById("metric-flow");

  // ---- Clock ----
  function tickClock() {
    if (!clockEl) return;
    clockEl.textContent = new Date().toLocaleTimeString("pt-BR", {
      hour: "2-digit", minute: "2-digit", second: "2-digit", hour12: false
    });
  }

  // ---- Sparklines (purely decorative) ----
  function renderSparklines() {
    document.querySelectorAll(".metric-card-spark").forEach(function (el) {
      el.innerHTML = "";
      for (var i = 0; i < 24; i++) {
        var bar = document.createElement("div");
        bar.className = "metric-card-spark-bar";
        var h = 30 + Math.random() * 70;
        bar.style.height = h + "%";
        if (h > 75) bar.classList.add("is-high");
        el.appendChild(bar);
      }
    });
  }

  // ---- Fetch sensors from API and update metric cards ----
  function fetchMetrics() {
    fetch("/api/sensors")
      .then(function (res) { return res.json(); })
      .then(function (data) {
        var sensors = data.data || [];
        if (!sensors.length) return;

        var readings = sensors.map(function (s) { return s.reading; }).filter(Boolean);
        if (!readings.length) return;

        var avgObs  = readings.reduce(function (a, r) { return a + r.obstruction_pct; }, 0) / readings.length;
        var avgRain = readings.reduce(function (a, r) { return a + r.rainfall_mm; }, 0)     / readings.length;
        var avgFlow = readings.reduce(function (a, r) { return a + r.flow_lps; }, 0)        / readings.length;

        if (metricObs) {
          metricObs.innerHTML = Math.round(avgObs) + '%<span class="unit">obstrução</span>';
        }
        if (metricChuva) {
          metricChuva.innerHTML = avgRain.toFixed(1) + '<span class="unit">mm</span>';
        }
        if (metricVazao) {
          metricVazao.innerHTML = Math.round(avgFlow) + '<span class="unit">L/s</span>';
        }

        // Atualiza dots de status no sidebar
        updateSidebarDots(sensors);
      })
      .catch(function (err) { console.warn("AquaSense: erro ao buscar métricas:", err); });
  }

  // ---- Update sidebar sensor status dots ----
  function updateSidebarDots(sensors) {
    var byId = {};
    sensors.forEach(function (s) { byId[s.id] = s.status; });

    document.querySelectorAll(".js-sensor-item").forEach(function (item) {
      var id  = item.dataset.sensorId;
      var dot = item.querySelector(".sensor-dot");
      if (dot && byId[id]) {
        dot.className = "sensor-dot status-" + byId[id];
      }
    });
  }

  // ---- Map (only initialised on pages that have #city-map) ----
  var map;
  var mapContainer = document.getElementById("city-map");

  var STATUS_COLORS = { ok: "#00D4AA", atencao: "#F59E0B", risco: "#F97316", critico: "#EF4444" };
  var STATUS_LABELS = { ok: "OK", atencao: "Atenção", risco: "Risco", critico: "Crítico" };

  /** Cria um elemento SVG em forma de pin de localização. */
  function buildPinElement(status) {
    var color  = STATUS_COLORS[status] || STATUS_COLORS.ok;
    var el     = document.createElement("div");
    el.className = "map-pin-marker";
    el.style.cssText = "cursor:pointer;width:28px;height:36px;position:relative";
    el.innerHTML = [
      '<svg width="28" height="36" viewBox="0 0 28 36" fill="none" xmlns="http://www.w3.org/2000/svg">',
        '<path d="M14 1C7.92 1 3 5.92 3 12c0 8.25 11 23 11 23s11-14.75 11-23c0-6.08-4.92-11-11-11Z"',
              'fill="' + color + '" stroke="rgba(0,0,0,0.35)" stroke-width="1.5"/>',
        '<circle cx="14" cy="12" r="4.5" fill="rgba(0,0,0,0.25)"/>',
        '<circle cx="14" cy="12" r="3" fill="white" fill-opacity="0.85"/>',
      '</svg>'
    ].join("");
    return el;
  }

  /** Constrói HTML do popup de um sensor. */
  function buildPopupHtml(s) {
    var color   = STATUS_COLORS[s.status] || STATUS_COLORS.ok;
    var reading = s.reading;
    return [
      '<div style="font-family:Inter,sans-serif;min-width:190px">',
        '<div style="font-weight:700;font-size:0.9rem;margin-bottom:2px">' + s.name + '</div>',
        '<div style="font-size:0.72rem;color:#aaa;margin-bottom:8px">' + s.code + ' · ' + s.address + '</div>',
        '<div style="display:inline-flex;align-items:center;gap:5px;font-size:0.75rem;font-weight:600;',
             'color:' + color + ';background:' + color + '1A;padding:2px 8px;border-radius:99px;margin-bottom:8px">',
          '<span style="width:7px;height:7px;border-radius:50%;background:' + color + '"></span>',
          STATUS_LABELS[s.status] || s.status,
        '</div>',
        reading
          ? [
              '<table style="width:100%;border-collapse:collapse;font-size:0.78rem">',
                '<tr><td style="color:#aaa;padding:2px 0">Obstrução</td>',
                    '<td style="text-align:right;font-weight:600">' + reading.obstruction_pct.toFixed(1) + ' %</td></tr>',
                '<tr><td style="color:#aaa;padding:2px 0">Precipitação</td>',
                    '<td style="text-align:right;font-weight:600">' + reading.rainfall_mm.toFixed(2) + ' mm</td></tr>',
                '<tr><td style="color:#aaa;padding:2px 0">Vazão</td>',
                    '<td style="text-align:right;font-weight:600">' + reading.flow_lps.toFixed(1) + ' L/s</td></tr>',
              '</table>'
            ].join("")
          : '<div style="font-size:0.75rem;color:#aaa">Sem leitura recente</div>',
      '</div>'
    ].join("");
  }

  if (mapContainer && typeof maplibregl !== "undefined") {
    map = new maplibregl.Map({
      container: "city-map",
      style: "https://basemaps.cartocdn.com/gl/dark-matter-gl-style/style.json",
      center: [-42.140, -19.790],
      zoom: 14,
      attributionControl: false
    });
    map.addControl(new maplibregl.NavigationControl(), "top-right");

    map.on("load", function () {
      var sensors = window.AQUASENSE_SENSORS || [];

      sensors.forEach(function (s) {
        var pinEl = buildPinElement(s.status);

        new maplibregl.Marker({ element: pinEl, anchor: "bottom" })
          .setLngLat([s.lng, s.lat])
          .setPopup(
            new maplibregl.Popup({ offset: [0, -28], maxWidth: "240px" })
              .setHTML(buildPopupHtml(s))
          )
          .addTo(map);
      });
    });
  }

  // ---- Sidebar click-to-fly (map page only) ----
  document.querySelectorAll(".js-sensor-item").forEach(function (item, idx) {
    item.addEventListener("click", function () {
      if (map) {
        var lat = parseFloat(item.dataset.lat);
        var lng = parseFloat(item.dataset.lng);
        map.flyTo({ center: [lng, lat], zoom: 16, duration: 1200 });
      }
      document.querySelectorAll(".js-sensor-item").forEach(function (i) {
        i.classList.remove("is-selected");
      });
      item.classList.add("is-selected");
    });
  });

  // ---- Map time pill ----
  var mapTime = document.getElementById("map-time");
  function tickMapTime() {
    if (!mapTime) return;
    mapTime.textContent = new Date().toLocaleTimeString("pt-BR", {
      hour: "2-digit", minute: "2-digit", hour12: false
    });
  }

  // ---- Init ----
  renderSparklines();
  tickClock();
  tickMapTime();
  fetchMetrics();

  setInterval(tickClock, 1000);
  setInterval(tickMapTime, 60000);
  setInterval(fetchMetrics, 30000);

})();
