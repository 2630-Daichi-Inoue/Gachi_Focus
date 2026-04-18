document.addEventListener("DOMContentLoaded", () => {
  const views       = ["week", "month", "year"];
  let index         = 0;
  let currentView   = views[index];

  const monthMap = {
    1:"Jan",2:"Feb",3:"Mar",4:"Apr",5:"May",6:"Jun",
    7:"Jul",8:"Aug",9:"Sep",10:"Oct",11:"Nov",12:"Dec",
  };

  // ── Helpers ──────────────────────────────────────────────────────────────

  function prefColor(i, total) {
    const hue = Math.round((i / Math.max(total, 1)) * 300);
    return `hsl(${hue}, 55%, 62%)`;
  }

  // Aggregate prefecture totals for the current view (sum across the time axis)
  function prefectureAggregated(view) {
    const prefData = salesDataSets[view].prefectures;
    return Object.entries(prefData)
      .map(([pref, values]) => ({
        pref,
        total: Object.values(values).reduce((sum, v) => sum + (Number(v) || 0), 0),
      }))
      .sort((a, b) => a.pref.localeCompare(b.pref));
  }

  // ── Line chart (total sales over time) ───────────────────────────────────

  const lineCtx   = document.getElementById("salesChart").getContext("2d");
  const lineChart = new Chart(lineCtx, {
    type: "line",
    data: lineDataFor(currentView),
    options: {
      responsive:          true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => `¥${(ctx.parsed.y ?? 0).toLocaleString()}`,
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { color: "#666", callback: (v) => `¥${v.toLocaleString()}` },
          grid:  { color: "rgba(0,0,0,0.05)" },
        },
        x: {
          ticks: { color: "#666" },
          grid:  { color: "rgba(0,0,0,0.03)" },
        },
      },
    },
  });

  function lineDataFor(view) {
    const labels = salesDataSets[view].labels;
    const totals = salesDataSets[view].total.map((v) => Number(v) || 0);
    return {
      labels,
      datasets: [{
        data:            totals,
        borderColor:     "#8B7CF6",
        backgroundColor: "rgba(139,124,246,0.15)",
        fill:            true,
        tension:         0.4,
        borderWidth:     3,
        pointRadius:     4,
        pointBackgroundColor: "#8B7CF6",
      }],
    };
  }

  // ── Prefecture bar chart (alphabetical, horizontal) ───────────────────────

  const prefCtx = document.getElementById("prefectureChart").getContext("2d");

  function prefDataFor(view) {
    const entries = prefectureAggregated(view);
    const colors  = entries.map((_, i) => prefColor(i, entries.length));
    return {
      labels: entries.map((e) => e.pref),
      datasets: [{
        data:            entries.map((e) => e.total),
        backgroundColor: colors,
        borderRadius:    4,
      }],
    };
  }

  function prefChartHeight(view) {
    const count = Object.keys(salesDataSets[view].prefectures).length;
    return Math.max(count * 32, 120);
  }

  const prefChart = new Chart(prefCtx, {
    type: "bar",
    data: prefDataFor(currentView),
    options: {
      indexAxis:           "y",
      responsive:          true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => `¥${(ctx.parsed.x ?? 0).toLocaleString()}`,
          },
        },
      },
      scales: {
        x: {
          beginAtZero: true,
          ticks: { color: "#666", callback: (v) => `¥${v.toLocaleString()}` },
          grid:  { color: "rgba(0,0,0,0.05)" },
        },
        y: {
          ticks: { color: "#444", font: { size: 12 } },
          grid:  { display: false },
        },
      },
    },
  });

  // Set initial height
  document.getElementById("prefChartWrap").style.height = prefChartHeight(currentView) + "px";

  // ── Toggle logic ──────────────────────────────────────────────────────────

  function updateCharts() {
    currentView = views[index];
    const label = currentView.charAt(0).toUpperCase() + currentView.slice(1);
    document.getElementById("chartMode").innerText    = label;
    document.getElementById("prefChartMode").innerText = label;

    // Update line chart
    const ld = lineDataFor(currentView);
    lineChart.data.labels   = ld.labels;
    lineChart.data.datasets = ld.datasets;
    lineChart.update("none");

    // Update prefecture chart
    const pd = prefDataFor(currentView);
    prefChart.data.labels   = pd.labels;
    prefChart.data.datasets = pd.datasets;
    document.getElementById("prefChartWrap").style.height = prefChartHeight(currentView) + "px";
    prefChart.resize();
    prefChart.update("none");
  }

  document.getElementById("btnPrev").addEventListener("click", () => {
    index = (index - 1 + views.length) % views.length;
    updateCharts();
  });
  document.getElementById("btnNext").addEventListener("click", () => {
    index = (index + 1) % views.length;
    updateCharts();
  });
});
