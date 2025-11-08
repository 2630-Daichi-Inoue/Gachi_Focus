document.addEventListener("DOMContentLoaded", () => {
  const ctx = document.getElementById("salesChart").getContext("2d");
  const chartMode = document.getElementById("chartMode");

//   country code mapping ex)
  const regionMapping = {
    JP: "Asia", PH: "Asia", CN: "Asia", IN: "Asia",
    US: "North America", CA: "North America",
    BR: "South America", AR: "South America",
    FR: "Europe", DE: "Europe", GB: "Europe",
    AU: "Oceania", NZ: "Oceania",
    ZA: "Africa", KE: "Africa", EG: "Africa"
  };

  const regionColors = {
    Asia: "#6FD6D6",
    Europe: "#A48CF2",
    "North America": "#6EA8FF",
    "South America": "#EBCB7F",
    Africa: "#B9845A",
    Oceania: "#8CD9B2"
  };

  const lineColor = "#E2D8FF";
  const views = ["year", "month", "week"];
  let index = 0;
  let currentView = views[index];

  const monthMap = {
    1: "Jan", 2: "Feb", 3: "Mar", 4: "Apr", 5: "May", 6: "Jun",
    7: "Jul", 8: "Aug", 9: "Sep", 10: "Oct", 11: "Nov", 12: "Dec"
  };

  function buildCountryDatasets(view) {
    const regionData = salesDataSets[view].regions;
    const labels = salesDataSets[view].labels;
    const datasets = {};

    Object.keys(regionColors).forEach(r => {
      datasets[r] = { total: Array(labels.length).fill(0), countries: {} };
    });

    Object.keys(regionData).forEach(country => {
      const region = regionMapping[country] || "Others";
      const values = regionData[country];

      labels.forEach((lbl, i) => {
        let val = 0;
        if (view === "week") {
          const match = Object.entries(values).find(([date]) => {
            const day = new Date(date).toLocaleString("en-US", { weekday: "short" });
            return day === lbl;
          });
          val = match ? match[1] : 0;
        } else if (view === "month") {
          const monthIndex = Object.entries(monthMap).find(([num, m]) => m === lbl)?.[0];
          val = values[monthIndex] ?? values[String(monthIndex)] ?? 0;
        } else {
          val = values[lbl] ?? values[String(lbl)] ?? 0;
        }

        datasets[region].total[i] += val;
        datasets[region].countries[country] = datasets[region].countries[country] || [];
        datasets[region].countries[country][i] = val;
      });
    });

    return Object.keys(regionColors).map(region => ({
      type: "bar",
      label: region,
      data: datasets[region].total,
      backgroundColor: regionColors[region],
      borderRadius: 6,
      yAxisID: "y1",
      barPercentage: 0.7,
      categoryPercentage: 0.8,
      countries: datasets[region].countries
    }));
  }

  function buildDatasets(view) {
    const labels = salesDataSets[view].labels;
    const totals = salesDataSets[view].total.map(v => Number(v) || 0);
    const barDatasets = buildCountryDatasets(view);

    const datasets = [
      {
        type: "line",
        label: "Total Sales ($)",
        data: totals,
        borderColor: lineColor,
        backgroundColor: lineColor,
        tension: 0.4,
        borderWidth: 3,
        pointRadius: 0,
        yAxisID: "y",
        xAxisID: "x"
      },
      ...barDatasets
    ];

    return { labels, datasets };
  }

  const chart = new Chart(ctx, {
    data: buildDatasets(currentView),
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: "bottom",
          labels: { color: "#555", boxWidth: 12 }
        },
        tooltip: {
          backgroundColor: "rgba(20,20,30,0.9)",
          titleColor: "#fff",
          bodyColor: "#eee",
          borderColor: "rgba(150,150,255,0.3)",
          borderWidth: 1,
          callbacks: {
            title: (context) => `Region: ${context[0].dataset.label}`,
            label: (context) => {
              const region = context.dataset.label;
              const idx = context.dataIndex;
              const countries = context.dataset.countries || {};
              const details = Object.entries(countries)
                .map(([c, vals]) => `${c}: $${(vals[idx] || 0).toLocaleString()}`)
                .filter(t => !t.endsWith("$0"))
                .join("  ");
              const total = context.parsed.y ?? 0;
              return `${details ? details + " | " : ""}Total: $${total.toLocaleString()}`;
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: { color: "rgba(0,0,0,0.05)" },
          ticks: { color: "#666" }
        },
        y1: {
          beginAtZero: true,
          position: "right",
          grid: { drawOnChartArea: false },
          ticks: { color: "#666" }
        },
        x: {
          type: "category",
          offset: true,
          ticks: { color: "#666" },
          grid: { color: "rgba(0,0,0,0.03)" },
          align: "center" 
        }
      }
    },
    plugins: [{
      id: "centerAlignFix",
      afterLayout(chart) {
        const metaBars = chart.getDatasetMeta(1);
        const metaLine = chart.getDatasetMeta(0);
        if (metaBars?.data && metaLine?.data) {
          const bars = metaBars.data;
          const linePoints = metaLine.data;
          bars.forEach((bar, i) => {
            if (linePoints[i]) {
              linePoints[i].x = bar.x; 
            }
          });
        }
      }
    }]
  });

  function updateChart() {
    currentView = views[index];
    const { labels, datasets } = buildDatasets(currentView);
    chart.data.labels = labels;
    chart.data.datasets = datasets;
    chart.update("none");
    chartMode.innerText = currentView.charAt(0).toUpperCase() + currentView.slice(1);
  }

  document.getElementById("leftBar").addEventListener("click", () => {
    index = (index - 1 + views.length) % views.length;
    updateChart();
  });
  document.getElementById("rightBar").addEventListener("click", () => {
    index = (index + 1) % views.length;
    updateChart();
  });
});
