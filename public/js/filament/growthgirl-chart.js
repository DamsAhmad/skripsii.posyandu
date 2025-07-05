document.addEventListener("DOMContentLoaded", function () {
    const canvas = document.getElementById("bbuChart");
    if (!canvas) return;

    const ctx = canvas.getContext("2d");
    const memberName = canvas.dataset.memberName || "Data Anak";
    const dataPoints = JSON.parse(canvas.dataset.weights || "[]");
    const whoRaw = JSON.parse(canvas.dataset.whoCurves || "[]");

    // Proses data kurva WHO
    const whoCurves = {
        "-3": [],
        "-2": [],
        "-1": [],
        0: [],
        "+1": [],
        "+2": [],
        "+3": [],
    };

    whoRaw.forEach((row) => {
        whoCurves["-3"].push({ x: row.age, y: row["-3"] });
        whoCurves["-2"].push({ x: row.age, y: row["-2"] });
        whoCurves["-1"].push({ x: row.age, y: row["-1"] });
        whoCurves["0"].push({ x: row.age, y: row["0"] });
        whoCurves["+1"].push({ x: row.age, y: row["+1"] });
        whoCurves["+2"].push({ x: row.age, y: row["+2"] });
        whoCurves["+3"].push({ x: row.age, y: row["+3"] });
    });

    const datasets = [
        {
            label: "Kurva -3 SD",
            data: whoCurves["-3"],
            borderColor: "#dc143c",
            borderWidth: 1,
            fill: false,
            pointRadius: 0,
            tension: 0.4,
        },
        {
            label: "Kurva -2 SD",
            data: whoCurves["-2"],
            borderColor: "#ff8c00",
            borderWidth: 1,
            fill: false,
            pointRadius: 0,
            tension: 0.4,
        },
        {
            label: "Kurva +1 SD",
            data: whoCurves["+1"],
            borderColor: "#32cd32",
            borderWidth: 1,
            fill: false,
            pointRadius: 0,
            tension: 0.4,
        },
        {
            label: "Kurva +3 SD",
            data: whoCurves["+3"],
            borderColor: "#ffd700",
            borderWidth: 1,
            fill: false,
            pointRadius: 0,
            tension: 0.4,
        },
        {
            label: "Berat Anak (Kg)",
            data: dataPoints.map((dp) => ({
                x: dp.age,
                y: dp.weight,
                status: dp.status,
            })),
            borderColor: "#0066cc",
            backgroundColor: "#0066cc",
            borderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
            tension: 0.4,
        },
    ];

    const chart = new Chart(ctx, {
        type: "line",
        data: { datasets },
        options: {
            responsive: true,
            scales: {
                x: {
                    type: "linear",
                    title: {
                        display: true,
                        text: "Usia (bulan)",
                        font: { size: 14, weight: "bold" },
                    },
                    min: 0,
                    max: 60,
                    ticks: {
                        stepSize: 5,
                        callback: function (value) {
                            return value;
                        },
                        font: { size: 12 },
                    },
                    grid: { color: "#f0f0f0" },
                },
                y: {
                    title: {
                        display: true,
                        text: "Berat Badan (Kg)",
                        font: { size: 14, weight: "bold" },
                    },
                    min: 2,
                    max: 35,
                    ticks: {
                        stepSize: 5,
                        callback: function (value) {
                            return value;
                        },
                        font: { size: 12 },
                    },
                    grid: { color: "#f0f0f0" },
                },
            },
            plugins: {
                title: {
                    display: true,
                    text: `Grafik KMS: BB/U - ${memberName}`,
                    font: { size: 20, weight: "bold" },
                    padding: 20,
                },
                legend: {
                    position: "top",
                    labels: {
                        boxWidth: 15,
                        padding: 15,
                        usePointStyle: true,
                        font: { size: 12 },
                        generateLabels: (chart) => {
                            return chart.data.datasets.map((dataset, i) => {
                                if (dataset.label.includes("Kurva")) {
                                    return {
                                        text: dataset.label,
                                        fillStyle: dataset.borderColor,
                                        strokeStyle: dataset.borderColor,
                                        lineWidth: 2,
                                        pointStyle: "line",
                                    };
                                }
                                return {
                                    text: dataset.label,
                                    fillStyle: dataset.backgroundColor,
                                    strokeStyle: dataset.borderColor,
                                    lineWidth: 0,
                                    pointStyle: "circle",
                                };
                            });
                        },
                    },
                },
                tooltip: {
                    callbacks: {
                        title: (context) => {
                            const datasetLabel = context[0].dataset.label;
                            const point = context[0].raw;
                            return datasetLabel === "Berat Anak (Kg)"
                                ? point.status || "Status tidak tersedia"
                                : datasetLabel;
                        },
                        label: (context) => {
                            const point = context.raw;
                            return `Usia: ${point.x} bulan, Berat: ${point.y} Kg`;
                        },
                    },
                    bodyFont: { size: 12 },
                    titleFont: { size: 14 },
                },
            },
        },
        plugins: [
            {
                id: "customLabels",
                afterDraw: (chart) => {
                    const ctx = chart.ctx;
                    const xAxis = chart.scales.x;
                    const yAxis = chart.scales.y;

                    // Posisi X di tengah grafik (30 bulan)
                    const midX = xAxis.getPixelForValue(30);

                    // Tentukan posisi Y secara manual sesuai permintaan
                    const manualPositions = {
                        sangatKurang: 4, // Di bawah -3 SD
                        kurang: 10, // PERUBAHAN: BB Kurang di 10 kg
                        normal: 13, // BB Normal di 13 kg
                        risiko: 16, // Antara +1 SD dan +3 SD
                        obesitas: 25, // Di atas +3 SD
                    };

                    // Tambahkan label dengan posisi manual
                    ctx.save();
                    ctx.textAlign = "center";
                    ctx.textBaseline = "middle";
                    ctx.font = "bold 14px Arial";

                    // BB Sangat Kurang - di bawah -3 SD
                    ctx.fillStyle = "#dc143c";
                    ctx.fillText(
                        "BB Sangat Kurang",
                        midX,
                        yAxis.getPixelForValue(manualPositions.sangatKurang)
                    );

                    // BB Kurang - antara -3 SD dan -2 SD
                    ctx.fillStyle = "#ff8c00";
                    ctx.fillText(
                        "BB Kurang",
                        midX,
                        yAxis.getPixelForValue(manualPositions.kurang)
                    );

                    // BB Normal - antara -2 SD dan +1 SD
                    ctx.fillStyle = "#32cd32";
                    ctx.fillText(
                        "BB Normal",
                        midX,
                        yAxis.getPixelForValue(manualPositions.normal)
                    );

                    // Risiko BB Lebih - antara +1 SD dan +3 SD
                    ctx.fillStyle = "#ffd700";
                    ctx.fillText(
                        "Risiko BB Lebih",
                        midX,
                        yAxis.getPixelForValue(manualPositions.risiko)
                    );

                    // Obesitas - di atas +3 SD
                    ctx.fillStyle = "#8b0000";
                    ctx.fillText(
                        "Obesitas",
                        midX,
                        yAxis.getPixelForValue(manualPositions.obesitas)
                    );

                    ctx.restore();
                },
            },
        ],
    });
});
