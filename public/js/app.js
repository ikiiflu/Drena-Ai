/**
 * ============================================================
 * Drena Ai — Dashboard Controller
 * Handles: map init, alert feed, live counter simulation
 * ============================================================ */

(function () {
  "use strict";

  // ---- Sensor mock data (representing real city topology) ----
  var SENSORS = [
    { id: "DRA-001", name: "Bueiro Central", address: "Av. Principal, 1500", lat: -19.788, lng: -42.140, status: "ok", obstruction: 12, rainfall: 4.2, flow: 320 },
    { id: "DRA-002", name: "Galeria Norte", address: "Rua das Flores, 420", lat: -19.785, lng: -42.142, status: "ok", obstruction: 8, rainfall: 2.1, flow: 480 },
    { id: "DRA-003", name: "Bueiro Leste", address: "Av. Brasil, 2300", lat: -19.790, lng: -42.136, status: "ok", obstruction: 15, rainfall: 3.5, flow: 260 },
    { id: "DRA-004", name: "Galeria Sul", address: "Rua Comércio, 88", lat: -19.794, lng: -42.141, status: "atencao", obstruction: 42, rainfall: 8.7, flow: 180 },
    { id: "DRA-005", name: "Bueiro Oeste", address: "Av. Perimetral, 100", lat: -19.783, lng: -42.148, status: "ok", obstruction: 5, rainfall: 1.0, flow: 510 },
    { id: "DRA-006", name: "Galeria Centro", address: "Praça da Matriz", lat: -19.791, lng: -42.139, status: "atencao", obstruction: 35, rainfall: 6.3, flow: 210 },
    { id: "DRA-007", name: "Bueiro Nordeste", address: "Rua Amazonas, 55", lat: -19.781, lng: -42.137, status: "risco", obstruction: 68, rainfall: 14.2, flow: 95 },
    { id: "DRA-008", name: "Galeria Sudeste", address: "Av. Independência, 700", lat: -19.797, lng: -42.133, status: "ok", obstruction: 10, rainfall: 2.8, flow: 440 },
    { id: "DRA-009", name: "Bueiro Rodoviária", address: "Terminal Urbano", lat: -19.786, lng: -42.145, status: "risco", obstruction: 72, rainfall: 16.5, flow: 72 },
    { id: "DRA-010", name: "Galeria Marginal", address: "Av. Beira Rio, 3200", lat: -19.793, lng: -42.144, status: "atencao", obstruction: 28, rainfall: 5.1, flow: 340 }
  ];

  var STATUS_MAP = { ok: 0, atencao: 1, risco: 2, critico: 3 };

  // ---- DOM refs ----
  var sensorList = document.getElementById("sensor-list");
  var alertList = document.getElementById("alert-list");
  var alertBadge = document.getElementById("alert-badge");
  var clockEl = document.getElementById("statusbar-clock");
  var metricObs = document.getElementById("metric-obstruction");
  var metricChuva = document.getElementById("metric-rainfall");
  var metricVazao = document.getElementById("metric-flow");
  var cityBand = document.getElementById("city-band");

  // ---- Map ----
  var map;
  try {
    map = new maplibregl.Map({
      container: "city-map",
      style: "https://basemaps.cartocdn.com/gl/dark-matter-gl-style/style.json",
      center: [-42.140, -19.790],
      zoom: 14,
      attributionControl: false
    });
    map.addControl(new maplibregl.NavigationControl(), "top-right");
  } catch (e) { /* map not available */ }

  // ---- Sensor list rendering ----
  function renderSensorList() {
    if (!sensorList) return;
    sensorList.innerHTML = "";
    SENSORS.forEach(function (s, i) {
      var li = document.createElement("li");
      li.className = "sensor-list-item";
      li.tabIndex = 0;
      li.setAttribute("role", "option");
      li.setAttribute("aria-label", s.name + " — " + s.address + ", status " + s.status);
      li.innerHTML = [
        "<div class=\"sensor-dot status-" + s.status + "\" aria-hidden=\"true\"></div>",
        "<div class=\"sensor-info\">",
        "  <div class=\"sensor-name\">" + s.name + "</div>",
        "  <div class=\"sensor-address\">" + s.address + "</div>",
        "</div>"
      ].join("");
      li.addEventListener("click", function () { flyToSensor(i); });
      sensorList.appendChild(li);
    });
  }

  function flyToSensor(index) {
    if (!map) return;
    var s = SENSORS[index];
    map.flyTo({ center: [s.lng, s.lat], zoom: 16, duration: 1200 });
    var prev = document.querySelector(".sensor-list-item.is-selected");
    if (prev) prev.classList.remove("is-selected");
    var items = sensorList.querySelectorAll(".sensor-list-item");
    if (items[index]) items[index].classList.add("is-selected");
  }

  // ---- Alert feed ----
  function renderAlertFeed(alerts) {
    if (!alertList || !alertBadge) return;
    alertList.innerHTML = "";
    if (alerts.length === 0) {
      alertList.innerHTML = [
        "<li class=\"empty-state\">",
        "  <div class=\"empty-state-icon\">✓</div>",
        "  <div class=\"empty-state-title\">Nenhum alerta ativo</div>",
        "  <div class=\"empty-state-desc\">Todos os sensores operam dentro dos parâmetros normais.</div>",
        "</li>"
      ].join("");
      alertBadge.style.display = "none";
      return;
    }
    alertBadge.style.display = "";
    alertBadge.textContent = alerts.length;
    alerts.forEach(function (a) {
      var li = document.createElement("li");
      li.className = "alert-item";
      li.innerHTML = [
        "<div class=\"alert-item-bar " + a.status + "\" aria-hidden=\"true\"></div>",
        "<div class=\"alert-item-body\">",
        "  <div class=\"alert-item-location\">" + a.location + "</div>",
        "  <div class=\"alert-item-detail\">" + a.message + "</div>",
        "  <div class=\"alert-item-time\">" + a.time + "</div>",
        "</div>",
        "<button class=\"alert-item-action\" aria-label=\"Ver detalhes de " + a.location + "\">Ver</button>"
      ].join("");
      alertList.appendChild(li);
    });
  }

  // ---- Metric cards ----
  function renderMetrics(aggregate) {
    if (metricObs) metricObs.innerHTML = aggregate.obstruction + "%<span class=\"unit\">obstrução</span>";
    if (metricChuva) metricChuva.innerHTML = aggregate.rainfall.toFixed(1) + "<span class=\"unit\">mm</span>";
    if (metricVazao) metricVazao.innerHTML = aggregate.flow + "<span class=\"unit\">L/s</span>";
  }

  function renderMetricsSparklines(sensors) {
    var containers = document.querySelectorAll(".metric-card-spark");
    containers.forEach(function (el) {
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

  // ---- City Status Band ----
  function renderCityBand(sensors) {
    if (!cityBand) return;
    cityBand.innerHTML = [
      "<div class=\"city-band-label\">Regiões</div>"
    ].join("");
    var regions = ["Zona Norte", "Zona Sul", "Central", "Zona Leste", "Zona Oeste"];
    regions.forEach(function (r, i) {
      var seg = document.createElement("div");
      var worst = sensors[Math.floor(i * sensors.length / regions.length)];
      var s = worst ? worst.status : "ok";
      seg.className = "city-band-segment status-" + s;
      seg.title = r + " — status " + s;
      seg.setAttribute("aria-label", r + " — " + s);
      seg.innerHTML = "<div class=\"city-band-segment-highlight\" aria-hidden=\"true\"></div>";
      cityBand.appendChild(seg);
    });
  }

  // ---- Clock ----
  function tickClock() {
    if (!clockEl) return;
    var now = new Date();
    clockEl.textContent = now.toLocaleTimeString("pt-BR", { hour: "2-digit", minute: "2-digit", second: "2-digit", hour12: false });
  }

  // ---- Simulate live data ----
  function simulateData() {
    var hour = new Date().getHours();
    var rain = hour >= 14 && hour <= 18 ? 12 + Math.random() * 8 : 1 + Math.random() * 4;
    SENSORS.forEach(function (s, i) {
      s.rainfall = rain * (0.5 + Math.random());
      s.obstruction = Math.min(100, Math.max(0, s.obstruction + (Math.random() - 0.45) * 5));
      if (s.obstruction < 10) s.status = "ok";
      else if (s.obstruction < 40) s.status = "atencao";
      else if (s.obstruction < 70) s.status = "risco";
      else s.status = "critico";
    });

    var avgObs = SENSORS.reduce(function (a, b) { return a + b.obstruction; }, 0) / SENSORS.length;
    var avgRain = SENSORS.reduce(function (a, b) { return a + b.rainfall; }, 0) / SENSORS.length;
    var avgFlow = SENSORS.reduce(function (a, b) { return a + b.flow; }, 0) / SENSORS.length;

    renderMetrics({ obstruction: Math.round(avgObs), rainfall: avgRain, flow: Math.round(avgFlow) });
    renderSensorList();
    renderCityBand(SENSORS);

    var alerts = SENSORS.filter(function (s) { return s.status === "risco" || s.status === "critico"; })
      .map(function (s) {
        var label = s.status === "critico" ? "Emergência" : "Risco de alagamento";
        var msg = s.status === "critico"
          ? "Obstrução acima de 70%. Galeria com risco de transbordamento iminente."
          : "Obstrução elevada. Recomendada inspeção em até 2 hours.";
        return { location: s.name, message: msg, time: new Date().toLocaleTimeString("pt-BR", { hour: "2-digit", minute: "2-digit" }), status: s.status };
      })
      .sort(function (a, b) { return STATUS_MAP[b.status] - STATUS_MAP[a.status]; });
    renderAlertFeed(alerts);
  }

  // ---- Init ----
  renderSensorList();
  renderCityBand(SENSORS);
  renderMetricsSparklines();
  renderMetrics({ obstruction: 18, rainfall: 4.2, flow: 378 });
  renderAlertFeed([]);
  setInterval(tickClock, 1000);
  setInterval(simulateData, 8000);
  tickClock();
  simulateData();

})();
