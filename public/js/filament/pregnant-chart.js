document.addEventListener("DOMContentLoaded", function () {
    const canvas = document.getElementById("pregnantChart");
    if (!canvas) {
        console.error("Canvas pregnantChart tidak ditemukan");
        return;
    }

    const ctx = canvas.getContext("2d");
    const { memberName, dataPoints } = window.PregnantChartConfig;

    console.log("Cek dataPoints:", dataPoints);

    // Validasi data
    const points = (dataPoints || []).map((p) => {
        const week = parseInt(p.week);
        const value = parseFloat(p.value);
        return {
            x: week,
            y: value,
            status: p.status || "Tidak diketahui",
        };
    });

    // Batas KEK 23.5
    const batasKEK = {
        label: "Batas KEK (23.5 cm)",
        data: [
            { x: 1, y: 23.5 },
            { x: 45, y: 23.5 },
        ],
        borderColor: "red",
        borderDash: [6, 4],
        borderWidth: 2,
        pointRadius: 0,
        fill: false,
        tension: 0,
    };

    const lilaDataset = {
        label: "LiLA Ibu Hamil",
        data: points,
        borderColor: "#0066cc",
        backgroundColor: "#0066cc",
        borderWidth: 2,
        showLine: false,
        pointRadius: 6,
        pointHoverRadius: 8,
        pointBorderColor: "#fff",
        pointBorderWidth: 2,
    };

    new Chart(ctx, {
        type: "line",
        data: {
            datasets: [batasKEK, lilaDataset],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    type: "linear",
                    min: 1,
                    max: 45,
                    title: {
                        display: true,
                        text: "Usia Kehamilan (minggu)",
                        font: { size: 14, weight: "bold" },
                    },
                    ticks: {
                        stepSize: 2,
                        callback: (v) => `${v} mg`,
                    },
                    grid: {
                        color: "#eee",
                    },
                },
                y: {
                    type: "linear",
                    suggestedMin: 20, // bukan min fix, tapi saran batas bawah
                    suggestedMax: 35, // bukan max fix, tapi saran batas atas
                    title: {
                        display: true,
                        text: "Lingkar Lengan Atas (cm)",
                        font: { size: 14, weight: "bold" },
                    },
                    ticks: {
                        stepSize: 1,
                        precision: 1,
                    },
                    grid: {
                        color: "#eee",
                    },
                },
            },
            plugins: {
                title: {
                    display: true,
                    text: `Grafik KEK Ibu Hamil - ${memberName}`,
                    font: { size: 18 },
                },
                tooltip: {
                    callbacks: {
                        title: (context) => context[0].raw.status || "Status",
                        label: (context) => {
                            const p = context.raw;
                            return `Minggu: ${p.x} | LiLA: ${p.y} cm`;
                        },
                    },
                },
                legend: {
                    position: "top",
                    labels: {
                        font: { size: 12 },
                    },
                },
            },
        },
    });
});
