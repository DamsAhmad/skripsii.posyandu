document.addEventListener("DOMContentLoaded", function () {
    const canvas = document.getElementById("imtuBoyChart");
    if (!canvas) return;

    const ctx = canvas.getContext("2d");
    const memberName = canvas.dataset.memberName || "Anak";
    const points = JSON.parse(canvas.dataset.points || "[]");
    const whoCurves = JSON.parse(canvas.dataset.whoCurves || "{}");

    const whoAges = Object.keys(whoCurves)
        .map(Number)
        .sort((a, b) => a - b);

    const curveDatasets = ["-3", "-2", "-1", "0", "+1", "+2", "+3"].map(
        (key) => {
            const colorMap = {
                "-3": "#ff0000",
                "-2": "#ffa500",
                "-1": "#90ee90",
                0: "#008000",
                "+1": "#32cd32",
                "+2": "#ffd700",
                "+3": "#ff4500",
            };

            return {
                label: `${key} SD`,
                data: whoAges.map((age) => ({
                    x: age,
                    y: whoCurves[age][key],
                })),
                borderColor: colorMap[key],
                borderWidth: 2,
                pointRadius: 0,
                tension: 0.4,
                fill: false,
            };
        }
    );

    const imtDataset = {
        label: "IMT Anak (kg/m²)",
        data: points.map((p) => ({
            x: parseFloat(p.x),
            y: parseFloat(p.y),
            status: p.status || "",
            z_score: p.z_score || null,
        })),
        borderColor: "#0000cc",
        backgroundColor: "#0000cc",
        showLine: false,
        pointRadius: 5,
        pointHoverRadius: 7,
        pointBackgroundColor: "#fff",
        pointBorderColor: "#0000cc",
        pointBorderWidth: 2,
    };

    new Chart(ctx, {
        type: "line",
        data: {
            datasets: [...curveDatasets, imtDataset],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    type: "linear",
                    min: Math.floor(Math.min(...whoAges)),
                    max: Math.ceil(Math.max(...whoAges)),
                    title: {
                        display: true,
                        text: "Usia (Tahun)",
                        font: { size: 14, weight: "bold" },
                    },
                    ticks: {
                        callback: (value) => `${value.toFixed(1)} th`,
                        stepSize: 1,
                        font: { size: 12 },
                    },
                },
                y: {
                    title: {
                        display: true,
                        text: "Indeks Massa Tubuh (kg/m²)",
                        font: { size: 14, weight: "bold" },
                    },
                    min: 10,
                    max: 40,
                    ticks: { stepSize: 2 },
                },
            },
            plugins: {
                title: {
                    display: true,
                    text: `Grafik IMT/U - ${memberName}`,
                    font: { size: 18, weight: "bold" },
                },
                legend: {
                    position: "top",
                },
                tooltip: {
                    callbacks: {
                        title: function (context) {
                            if (
                                context[0].dataset.label === "IMT Anak (kg/m²)"
                            ) {
                                return (
                                    context[0].raw.status ||
                                    "Status tidak tersedia"
                                );
                            }
                            return context[0].dataset.label;
                        },
                        label: function (context) {
                            const point = context.raw;
                            const age = point.x;
                            const imt = point.y;
                            return `Usia: ${age.toFixed(
                                2
                            )} th | IMT: ${imt.toFixed(1)} kg/m²`;
                        },
                    },
                },
            },
        },
    });
});
