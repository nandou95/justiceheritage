(function () {
  'use strict';

  var dataEl = document.getElementById('jh-dashboard-data');
  if (!dataEl || typeof Chart === 'undefined') {
    return;
  }

  var payload;
  try {
    payload = JSON.parse(dataEl.textContent || '{}');
  } catch (e) {
    return;
  }

  var charts = payload.charts || [];
  var i18n = payload.i18n || {};
  var palette = ['#176b4f', '#c9a227', '#0ea5e9', '#7c3aed', '#dc2626', '#ea580c', '#0891b2', '#4f46e5', '#059669', '#be185d'];

  function colors(n) {
    var out = [];
    for (var i = 0; i < Math.max(n, 1); i++) {
      out.push(palette[i % palette.length]);
    }
    return out;
  }

  function emptyNote(el) {
    if (!el) return;
    el.innerHTML = '<p class="bo-dash-empty">' + (i18n.noData || i18n.empty || 'No data') + '</p>';
  }

  function hasValues(arr) {
    return Array.isArray(arr) && arr.some(function (v) { return Number(v) > 0; });
  }

  function baseOptions() {
    return {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 12, font: { family: 'Outfit, sans-serif', size: 11 } } },
        tooltip: { mode: 'nearest', intersect: true }
      }
    };
  }

  function renderChartJs(canvas, cfg) {
    var type = cfg.type;
    var labels = cfg.labels || [];
    var data = cfg.data || [];
    var meta = cfg.meta || {};
    var cols = (cfg.colors && cfg.colors.length) ? cfg.colors : colors(labels.length || 1);
    var opts = baseOptions();

    if (type === 'bar-horizontal') {
      type = 'bar';
      opts.indexAxis = 'y';
    }

    if (type === 'gauge') {
      var value = Number(meta.value || 0);
      var max = Math.max(Number(meta.max || 100), value, 1);
      var remaining = Math.max(max - value, 0);
      var labelEl = canvas.parentElement && canvas.parentElement.querySelector('[data-role="gauge-label"]');
      if (labelEl) {
        labelEl.textContent = value + (meta.unit ? ' ' + meta.unit : '');
      }
      return new Chart(canvas, {
        type: 'doughnut',
        data: {
          labels: ['', ''],
          datasets: [{
            data: [value, remaining],
            backgroundColor: [cols[0], '#e8eef1'],
            borderWidth: 0,
            circumference: 180,
            rotation: 270
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '72%',
          plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
      });
    }

    if (type === 'multi-line') {
      var datasets = (meta.datasets || []).map(function (ds, idx) {
        return {
          label: ds.label || '',
          data: ds.data || [],
          borderColor: palette[idx % palette.length],
          backgroundColor: palette[idx % palette.length] + '33',
          tension: 0.3,
          fill: false,
          pointRadius: 3
        };
      });
      return new Chart(canvas, {
        type: 'line',
        data: { labels: labels, datasets: datasets },
        options: Object.assign({}, opts, {
          scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { precision: 0 } }
          }
        })
      });
    }

    if (type === 'doughnut' || type === 'pie') {
      if (!hasValues(data)) {
        emptyNote(canvas.parentElement);
        return null;
      }
      return new Chart(canvas, {
        type: type,
        data: {
          labels: labels,
          datasets: [{ data: data, backgroundColor: cols, borderWidth: 0 }]
        },
        options: opts
      });
    }

    if (type === 'line') {
      return new Chart(canvas, {
        type: 'line',
        data: {
          labels: labels.length ? labels : ['—'],
          datasets: [{
            label: cfg.title || '',
            data: data.length ? data : [0],
            borderColor: cols[0],
            backgroundColor: cols[0] + '33',
            tension: 0.35,
            fill: true,
            pointRadius: 3
          }]
        },
        options: Object.assign({}, opts, {
          plugins: Object.assign({}, opts.plugins, { legend: { display: false } }),
          scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: true, ticks: { precision: 0 } }
          }
        })
      });
    }

    // bar (vertical or horizontal)
    return new Chart(canvas, {
      type: 'bar',
      data: {
        labels: labels.length ? labels : ['—'],
        datasets: [{
          label: cfg.title || '',
          data: data.length ? data : [0],
          backgroundColor: cols,
          borderRadius: 6,
          maxBarThickness: 42
        }]
      },
      options: Object.assign({}, opts, {
        plugins: Object.assign({}, opts.plugins, { legend: { display: false } }),
        scales: {
          x: { grid: { display: false } },
          y: { beginAtZero: true, ticks: { precision: 0 } }
        }
      })
    });
  }

  function renderFunnel(el, cfg) {
    var labels = cfg.labels || [];
    var data = cfg.data || [];
    var max = Math.max.apply(null, data.concat([1]));
    if (!labels.length) {
      emptyNote(el);
      return;
    }
    el.innerHTML = labels.map(function (label, i) {
      var val = Number(data[i] || 0);
      var width = Math.max(18, Math.round((val / max) * 100));
      return '<div class="bo-dash-funnel-row">' +
        '<div class="bo-dash-funnel-meta"><span>' + escapeHtml(label) + '</span><strong>' + val + '</strong></div>' +
        '<div class="bo-dash-funnel-bar" style="width:' + width + '%;background:' + palette[i % palette.length] + '"></div>' +
        '</div>';
    }).join('');
  }

  function renderMap(card, cfg) {
    var mapEl = card.querySelector('[data-role="map"]');
    var legendEl = card.querySelector('[data-role="map-legend"]');
    var points = (cfg.meta && cfg.meta.points) || [];
    if (!mapEl) return;

    if (!points.length) {
      emptyNote(mapEl);
      if (legendEl) legendEl.innerHTML = '';
      return;
    }

    var lats = points.map(function (p) { return Number(p.lat) || 0; });
    var lngs = points.map(function (p) { return Number(p.lng) || 0; });
    var minLat = Math.min.apply(null, lats);
    var maxLat = Math.max.apply(null, lats);
    var minLng = Math.min.apply(null, lngs);
    var maxLng = Math.max.apply(null, lngs);
    var values = points.map(function (p) { return Number(p.value) || 0; });
    var maxVal = Math.max.apply(null, values.concat([1]));

    mapEl.innerHTML = points.map(function (p) {
      var lat = Number(p.lat) || 0;
      var lng = Number(p.lng) || 0;
      var val = Number(p.value) || 0;
      var x = maxLng === minLng ? 50 : ((lng - minLng) / (maxLng - minLng)) * 80 + 10;
      var y = maxLat === minLat ? 50 : (1 - (lat - minLat) / (maxLat - minLat)) * 70 + 15;
      var size = 18 + Math.round((val / maxVal) * 28);
      var opacity = 0.35 + (val / maxVal) * 0.55;
      return '<button type="button" class="bo-dash-map-point" style="left:' + x + '%;top:' + y + '%;width:' + size +
        'px;height:' + size + 'px;opacity:' + opacity + '" title="' + escapeAttr(p.name + ': ' + val) + '">' +
        '<span>' + escapeHtml(String(val)) + '</span></button>';
    }).join('');

    if (legendEl) {
      legendEl.innerHTML = points.map(function (p) {
        return '<li><span class="bo-dash-map-dot"></span><span>' + escapeHtml(p.name || '—') +
          '</span><strong>' + (Number(p.value) || 0) + '</strong></li>';
      }).join('');
    }
  }

  function renderCalendar(el, cfg) {
    var events = (cfg.meta && cfg.meta.events) || [];
    var byDate = {};
    events.forEach(function (e) {
      byDate[e.date] = Number(e.total) || 0;
    });

    var now = new Date();
    var year = now.getFullYear();
    var month = now.getMonth();
    var first = new Date(year, month, 1);
    var startDow = (first.getDay() + 6) % 7; // Monday first
    var daysInMonth = new Date(year, month + 1, 0).getDate();
    var weekdays = i18n.weekdays || ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    var html = '<div class="bo-dash-cal-head">' + first.toLocaleString(undefined, { month: 'long', year: 'numeric' }) + '</div>';
    html += '<div class="bo-dash-cal-grid">';
    weekdays.forEach(function (d) {
      html += '<div class="bo-dash-cal-dow">' + escapeHtml(d) + '</div>';
    });
    for (var i = 0; i < startDow; i++) {
      html += '<div class="bo-dash-cal-day is-empty"></div>';
    }
    for (var day = 1; day <= daysInMonth; day++) {
      var key = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
      var count = byDate[key] || 0;
      var isToday = day === now.getDate();
      html += '<div class="bo-dash-cal-day' + (isToday ? ' is-today' : '') + (count ? ' has-events' : '') + '">' +
        '<span class="bo-dash-cal-num">' + day + '</span>' +
        (count ? '<span class="bo-dash-cal-count">' + count + '</span>' : '') +
        '</div>';
    }
    html += '</div>';
    el.innerHTML = html;
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function escapeAttr(str) {
    return escapeHtml(str).replace(/'/g, '&#39;');
  }

  charts.forEach(function (cfg) {
    var card = document.querySelector('[data-chart-id="' + cfg.id + '"]');
    if (!card) return;
    var body = card.querySelector('.bo-dash-chart-body');
    var type = cfg.type;

    if (type === 'province-map') {
      renderMap(card, cfg);
      return;
    }
    if (type === 'calendar') {
      renderCalendar(card.querySelector('[data-role="calendar"]'), cfg);
      return;
    }
    if (type === 'funnel') {
      renderFunnel(card.querySelector('[data-role="funnel"]'), cfg);
      return;
    }

    var canvas = card.querySelector('[data-role="canvas"]');
    if (!canvas) return;
    renderChartJs(canvas, cfg);
  });
})();
