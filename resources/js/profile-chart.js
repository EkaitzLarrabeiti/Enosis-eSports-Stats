(() => {
  const chartWrapper = document.getElementById('performanceChart');
  if (!chartWrapper) return;

  const empty = document.getElementById('historyEmpty');
  const chartContainer = document.getElementById('historyChart');
  const debugEl = document.getElementById('historyDebug');
  const buttons = document.querySelectorAll('[data-series-btn]');

  const setDebug = (text) => {
    if (debugEl) debugEl.textContent = text;
  };

  if (!chartContainer) return;
  setDebug('JS activo...');

  if (typeof ApexCharts === 'undefined') {
    if (empty) {
      empty.classList.remove('hidden');
      empty.textContent = 'No se pudo cargar ApexCharts.';
    }
    setDebug('ApexCharts: no cargado');
    return;
  }

  let seriesData = {};
  try {
    seriesData = JSON.parse(chartWrapper.dataset.series || '{}');
  } catch (err) {
    setDebug('JSON inválido en data-series');
  }

  let seriesColors = {};
  try {
    seriesColors = JSON.parse(chartWrapper.dataset.colors || '{}');
  } catch (err) {
    seriesColors = {};
  }

  const hexToRgba = (hex, alpha) => {
    if (!hex || typeof hex !== 'string') return '';
    const cleaned = hex.replace('#', '').trim();
    if (cleaned.length !== 6) return '';
    const r = parseInt(cleaned.slice(0, 2), 16);
    const g = parseInt(cleaned.slice(2, 4), 16);
    const b = parseInt(cleaned.slice(4, 6), 16);
    if (Number.isNaN(r) || Number.isNaN(g) || Number.isNaN(b)) return '';
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
  };

  const getSeriesColor = (key) => {
    const color = seriesColors[key];
    if (color && typeof color === 'string') {
      return color;
    }
    if (key === 'road') return '#d1d5db';
    return '#f2b310';
  };

  const baseOptions = {
    chart: {
      type: 'area',
      height: 260,
      toolbar: { show: false },
      zoom: { enabled: false },
      animations: { speed: 450 },
      foreColor: '#a1a1aa',
    },
    stroke: { curve: 'smooth', width: 3 },
    fill: {
      type: 'gradient',
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.35,
        opacityTo: 0.0,
        stops: [0, 100],
      },
    },
    colors: ['#f2b310'],
    grid: {
      borderColor: 'rgba(255,255,255,0.06)',
      strokeDashArray: 4,
      xaxis: { lines: { show: true } },
      yaxis: { lines: { show: true } },
    },
    xaxis: {
      type: 'datetime',
      labels: { datetimeUTC: false, format: 'MMM d' },
      tickAmount: 10,
    },
    yaxis: {
      tickAmount: 5,
      labels: { formatter: (value) => Math.round(value) },
    },
    tooltip: {
      theme: 'dark',
      x: { format: 'dd MMM yyyy' },
      style: { fontSize: '12px' },
      marker: { show: false },
    },
    dataLabels: { enabled: false },
  };

  const initialSeries = (seriesData && seriesData.sports_car && seriesData.sports_car.series)
    ? seriesData.sports_car.series
    : [];

  const chart = new ApexCharts(chartContainer, {
    ...baseOptions,
    series: [{ name: 'iRating', data: initialSeries }],
  });
  chart.render();

  const setActive = (key) => {
    const data = seriesData[key];
    if (!data) return;
    const points = data.series || [];
    const hasData = points.length > 0;
    if (empty) {
      empty.classList.toggle('hidden', hasData);
      empty.textContent = hasData ? '' : 'No hay suficientes carreras para mostrar historial.';
    }
    chartContainer.classList.toggle('hidden', !hasData);
    const color = getSeriesColor(key);
    chart.updateOptions({ colors: [color] }, false, true);
    chart.updateSeries([{ name: 'iRating', data: points }], true);
    setDebug(`ApexCharts: ok | Serie: ${key} | Puntos: ${points.length}`);

    buttons.forEach((btn) => {
      const isActive = btn.dataset.seriesBtn === key;
      if (isActive) {
        const activeColor = getSeriesColor(key);
        const activeBg = hexToRgba(activeColor, key === 'road' ? 0.14 : 0.2);
        btn.classList.remove('border-zinc-600', 'text-zinc-400');
        btn.style.borderColor = activeColor;
        btn.style.color = activeColor;
        btn.style.backgroundColor = activeBg || '';
      } else {
        btn.classList.add('border-zinc-600', 'text-zinc-400');
        btn.style.borderColor = '';
        btn.style.color = '';
        btn.style.backgroundColor = '';
      }
    });
  };

  buttons.forEach((btn) => {
    btn.addEventListener('click', () => setActive(btn.dataset.seriesBtn));
  });

  const preferredOrder = ['sports_car', 'formula_car', 'oval', 'dirt_oval', 'dirt_road', 'road'];
  const initialKey = preferredOrder.find((key) => (seriesData[key]?.series || []).length > 0) || 'sports_car';
  setActive(initialKey);
})();
