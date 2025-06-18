document.addEventListener("DOMContentLoaded", function () {
    const canvas = document.getElementById("bbuChart");
    if (!canvas) return;

    const ctx = canvas.getContext("2d");
    const memberName = canvas.dataset.memberName || "Data Anak";
    const weights = JSON.parse(canvas.dataset.weights || "[]");
    const weightData = weights.map((dp) => ({ x: dp.age, y: dp.weight }));

    const whoAges = Array.from({ length: 61 }, (_, i) => i);

    const whoCurves = {
        "-3": [
            2.1, 2.9, 3.8, 4.4, 4.9, 5.3, 5.7, 5.9, 6.2, 6.4, 6.6, 6.8, 6.9,
            7.1, 7.2, 7.4, 7.5, 7.7, 7.8, 8.0, 8.1, 8.2, 8.4, 8.5, 8.6, 8.8,
            8.9, 9.0, 9.1, 9.2, 9.4, 9.5, 9.6, 9.7, 9.8, 9.9, 10.0, 10.1, 10.2,
            10.3, 10.4, 10.5, 10.6, 10.7, 10.8, 10.9, 11.0, 11.1, 11.2, 11.3,
            11.4, 11.5, 11.6, 11.7, 11.8, 11.9, 12.0, 12.1, 12.2, 12.3, 12.4,
        ],
        "-2": [
            2.5, 3.4, 4.3, 5.0, 5.6, 6.0, 6.4, 6.7, 6.9, 7.1, 7.4, 7.6, 7.7,
            7.9, 8.1, 8.3, 8.4, 8.6, 8.8, 8.9, 9.1, 9.2, 9.4, 9.5, 9.7, 9.8,
            10.0, 10.1, 10.2, 10.4, 10.5, 10.7, 10.8, 10.9, 11.0, 11.2, 11.3,
            11.4, 11.5, 11.6, 11.8, 11.9, 12.0, 12.1, 12.2, 12.4, 12.5, 12.6,
            12.7, 12.8, 12.9, 13.1, 13.2, 13.3, 13.4, 13.5, 13.6, 13.7, 13.8,
            14.0, 14.1,
        ],
        "-1": [
            2.9, 3.9, 4.9, 5.7, 6.2, 6.7, 7.1, 7.4, 7.7, 8.0, 8.2, 8.4, 8.6,
            8.8, 9.0, 9.2, 9.4, 9.6, 9.8, 10.0, 10.1, 10.3, 10.5, 10.7, 10.8,
            11.0, 11.2, 11.3, 11.5, 11.7, 11.8, 12.0, 12.1, 12.3, 12.4, 12.6,
            12.7, 12.9, 13.0, 13.1, 13.3, 13.4, 13.6, 13.7, 13.8, 14.0, 14.1,
            14.3, 14.4, 14.5, 14.7, 14.8, 15.0, 15.1, 15.2, 15.4, 15.5, 15.6,
            15.8, 15.9, 16.0,
        ],
        Median: [
            3.3, 4.5, 5.6, 6.4, 7.0, 7.5, 7.9, 8.3, 8.6, 8.9, 9.2, 9.4, 9.6,
            9.9, 10.1, 10.3, 10.5, 10.7, 10.9, 11.1, 11.3, 11.5, 11.8, 12.0,
            12.2, 12.4, 12.5, 12.7, 12.9, 13.1, 13.3, 13.5, 13.7, 13.8, 14.0,
            14.2, 14.3, 14.5, 14.7, 14.8, 15.0, 15.2, 15.3, 15.5, 15.7, 15.8,
            16.0, 16.2, 16.3, 16.5, 16.7, 16.8, 17.0, 17.2, 17.3, 17.5, 17.7,
            17.8, 18.0, 18.2, 18.3,
        ],
        "+1": [
            3.9, 5.1, 6.3, 7.2, 7.8, 8.4, 8.8, 9.2, 9.6, 9.9, 10.2, 10.5, 10.8,
            11.0, 11.3, 11.5, 11.7, 12.0, 12.2, 12.5, 12.7, 12.9, 13.2, 13.4,
            13.6, 13.9, 14.1, 14.3, 14.5, 14.8, 15.0, 15.2, 15.4, 15.6, 15.8,
            16.0, 16.2, 16.4, 16.6, 16.8, 17.0, 17.2, 17.4, 17.6, 17.8, 18.0,
            18.2, 18.4, 18.6, 18.8, 19.0, 19.2, 19.4, 19.6, 19.8, 20.0, 20.2,
            20.4, 20.6, 20.8, 21.0,
        ],
        "+2": [
            4.4, 5.8, 7.1, 8.0, 8.7, 9.3, 9.8, 10.3, 10.7, 11.0, 11.4, 11.7,
            12.0, 12.3, 12.6, 12.8, 13.1, 13.4, 13.7, 13.9, 14.2, 14.5, 14.7,
            15.0, 15.3, 15.5, 15.8, 16.1, 16.3, 16.6, 16.9, 17.1, 17.4, 17.6,
            17.8, 18.1, 18.3, 18.6, 18.8, 19.0, 19.3, 19.5, 19.7, 20.0, 20.2,
            20.5, 20.7, 20.9, 21.2, 21.4, 21.7, 21.9, 22.2, 22.4, 22.7, 22.9,
            23.2, 23.4, 23.7, 23.9, 24.2,
        ],
        "+3": [
            5.0, 6.6, 8.0, 9.0, 9.7, 10.4, 10.9, 11.4, 11.9, 12.3, 12.7, 13.0,
            13.3, 13.7, 14.0, 14.3, 14.6, 14.9, 15.3, 15.6, 15.9, 16.2, 16.5,
            16.8, 17.1, 17.5, 17.8, 18.1, 18.4, 18.7, 19.0, 19.3, 19.6, 19.9,
            20.2, 20.4, 20.7, 21.0, 21.3, 21.6, 21.9, 22.1, 22.4, 22.7, 23.0,
            23.3, 23.6, 23.9, 24.2, 24.5, 24.8, 25.1, 25.4, 25.7, 26.0, 26.3,
            26.6, 26.9, 27.2, 27.6, 27.9,
        ],
    };

    const colorMap = {
        "-3": "rgba(191, 0, 0, 0.4)",
        "-2": "rgba(255, 146, 0, 0.6)",
        "-1": "rgba(146, 209, 79, 1)",
        Median: "rgba(0, 255, 0, 1)",
        "+1": "rgba(255, 255, 0, 1)",
        "+2": "rgba(255, 146, 0, 1)",
        "+3": "rgba(191, 0, 0, 1)",
    };

    const labelMap = {
        "-3": "Gizi Buruk",
        "-2": "Gizi Kurang",
        "-1": "Normal (bawah)",
        Median: "Normal",
        "+1": "Berisiko Gizi Lebih",
        "+2": "Gizi Lebih",
        "+3": "Obesitas",
    };

    const datasets = Object.entries(whoCurves).map(([key, values]) => ({
        label: labelMap[key] || `${key} SD`,
        data: values.map((y, x) => ({ x, y })),
        borderColor: colorMap[key],
        backgroundColor: colorMap[key],
        pointRadius: 0,
        fill: false,
        tension: 0.4,
        borderWidth: 2,
    }));

    datasets.push({
        label: "Berat Anak (Kg)",
        data: weightData,
        borderColor: "black",
        backgroundColor: "black",
        pointRadius: 4,
        tension: 0.4,
        borderWidth: 2,
    });

    new Chart(ctx, {
        type: "line",
        data: {
            labels: whoAges,
            datasets: datasets,
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    type: "linear",
                    title: { display: true, text: "Usia (bulan)" },
                    font: {
                        size: 15,
                        weight: "bold",
                    },
                },
                y: {
                    title: { display: true, text: "Berat Badan (kg)" },
                    min: 2,
                    max: 35,
                    font: {
                        size: 15,
                        weight: "bold",
                    },
                },
            },
            plugins: {
                title: {
                    display: true,
                    text: `Grafik KMS: BB/U - ${memberName}`,
                    font: {
                        size: 20,
                        weight: "bold",
                    },
                },
                legend: {
                    display: true,
                    position: "top",
                },
                tooltip: {
                    callbacks: {
                        title: function (context) {
                            const datasetLabel = context[0].dataset.label;
                            const x = context[0].parsed.x;
                            const y = context[0].parsed.y;

                            if (datasetLabel === "Berat Anak (Kg)") {
                                const age = Math.round(x); // usia dalam bulan dibulatkan
                                const weight = y;

                                // Ambil semua batas Z-score untuk usia tersebut
                                const zScores = {
                                    "-3": whoCurves["-3"]?.[age],
                                    "-2": whoCurves["-2"]?.[age],
                                    "-1": whoCurves["-1"]?.[age],
                                    0: whoCurves["Median"]?.[age],
                                    "+1": whoCurves["+1"]?.[age],
                                    "+2": whoCurves["+2"]?.[age],
                                    "+3": whoCurves["+3"]?.[age],
                                };

                                if (
                                    !Object.values(zScores).every(
                                        (v) => v !== undefined
                                    )
                                ) {
                                    return "Data tidak tersedia";
                                }

                                // Tentukan kategori
                                if (weight < zScores["-3"]) return "Gizi Buruk";
                                if (weight < zScores["-2"])
                                    return "Gizi Kurang";
                                if (weight < zScores["-1"])
                                    return "Normal (bawah)";
                                if (weight < zScores["+1"]) return "Normal";
                                if (weight < zScores["+2"])
                                    return "Berisiko Gizi Lebih";
                                if (weight < zScores["+3"]) return "Gizi Lebih";
                                return "Obesitas";
                            }

                            return datasetLabel;
                        },
                        label: function (context) {
                            const label = context.dataset.label || "";
                            const x = context.parsed.x;
                            const y = context.parsed.y;

                            if (label === "Berat Anak (Kg)") {
                                return `Usia: ${x} bulan, Berat: ${y} Kg`;
                            } else {
                                return null;
                            }
                        },
                    },
                },
            },
        },
    });
});
