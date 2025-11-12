// 1.Load Chart.js
// 2.Add <canvas>
// 3.Get context (getContext("2d"))
// 4.Define data (labels & datasets)
// 5.Set options (legend, tooltip, scales)
// 6.Create chart with new Chart(ctx, { ... })
// 7.Use chart.update() to refresh data
// 8.Customize design with colors and options

// Run this after the entire DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  // Get canvas context for Chart.js
  const ctx = document.getElementById("salesChart").getContext("2d");
  const chartMode = document.getElementById("chartMode");

  // Define colors for each region
  const regionColors = {
    Asia: "#6FD6D6",
    Europe: "#A48CF2",
    "North America": "#6EA8FF",
    "South America": "#EBCB7F",
    Africa: "#B9845A",
    Oceania: "#8CD9B2",
  };

  const lineColor = "#E2D8FF";
  // Modes for chart view 
  const views = ["year", "month", "week"];
  let index = 0;
  let currentView = views[index];

  // Mapping between month number and short English name
  const monthMap = {
    1: "Jan", 2: "Feb", 3: "Mar", 4: "Apr", 5: "May", 6: "Jun",
    7: "Jul", 8: "Aug", 9: "Sep", 10: "Oct", 11: "Nov", 12: "Dec"
  };

  //  Build bar datasets by region 
  function buildRegionDatasets(view) {
    const regionData = salesDataSets[view].regions;
    const labels = salesDataSets[view].labels;
    const datasets = {};

    // Initialize all regions with zero values
    Object.keys(regionColors).forEach(r => {
      datasets[r] = { total: Array(labels.length).fill(0) };
    });

    // Fill in data for each region
    Object.keys(regionData).forEach(region => {
      const values = regionData[region];

      labels.forEach((lbl, i) => {
        let val = 0;
        // ---- Weekly mode ----
        if (view === "week") {
          val = parseFloat(values[lbl]) || 0;
          // ---- Monthly mode ----
        } else if (view === "month") {
          const monthIndex = Number(Object.entries(monthMap).find(([num, m]) => m === lbl)?.[0]);
          for (let m = 1; m <= 12; m++) {
            if (values[m] === undefined) {
              values[m] = 0;
            }
          }
          val = parseFloat(values[monthIndex]) || 0;
        } else {
          // ---- Yearly mode ----
          val = values[lbl] ?? values[Number(lbl)] ?? 0;
        }

        if (datasets[region]) {
          datasets[region].total[i] += val;
        }
      });
    });

    // Return bar datasets for Chart.js
    return Object.keys(regionColors).map(region => ({
      type: "bar",
      label: region,
      data: datasets[region].total,
      backgroundColor: regionColors[region],
      borderRadius: 6,
      yAxisID: "y1",  // right axis
      barPercentage: 0.7,
      categoryPercentage: 0.8
    }));
  }

  // Build full chart datasets
  function buildDatasets(view) {
    const labels = salesDataSets[view].labels;
    const totals = salesDataSets[view].total.map(v => Number(v) || 0);
    const barDatasets = buildRegionDatasets(view);

    // Combine total sales line + region bars
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

  // Create initial chart
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
              const label = context.label; // Year/Month/Week label
              const value = context.parsed.y ?? 0;
              const countries = salesDataSets[currentView].countries?.[region] || {};
              let details = [];

              //  country-wise detail in tooltip
              if (currentView === "month") {
                const monthIndex = Object.entries(monthMap).find(([num, m]) => m === label)?.[0];
                Object.entries(countries).forEach(([country, data]) => {
                  const val = parseFloat(data[monthIndex]) || 0;
                  if (val > 0) details.push(`${country}: $${val}`);
                });
              } else {
                Object.entries(countries).forEach(([country, data]) => {
                  const val = parseFloat(data[label]) || 0;
                  if (val > 0) details.push(`${country}: $${val}`);
                });
              }

              return details.length
                ? `${region} ▶ ${details.join(" | ")} | Total: $${value.toLocaleString()}`
                : `${region} Total: $${value.toLocaleString()}`;
            }
          }
        }
      },
      scales: {
        // Left Y-axis (line)
        y: {
          beginAtZero: true,
          grid: { color: "rgba(0,0,0,0.05)" },
          ticks: { color: "#666" }
        },
        // Right Y-axis (bars)
        y1: {
          beginAtZero: true,
          position: "right",
          grid: { drawOnChartArea: false },
          ticks: { color: "#666" }
        },
        // X-axis
        x: {
          type: "category",
          offset: true,
          ticks: { color: "#666" },
          grid: { color: "rgba(0,0,0,0.03)" },
          align: "center"
        }
      }
    }
  });

  // Chart mode switch
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
