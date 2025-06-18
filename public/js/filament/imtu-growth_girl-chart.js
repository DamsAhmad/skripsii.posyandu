document.addEventListener("DOMContentLoaded", function () {
    const canvas = document.getElementById("imtuGirlChart");
    if (!canvas) return;

    const ctx = canvas.getContext("2d");
    const memberName = canvas.dataset.memberName || "Data Anak-Remaja";

    const raw = document.getElementById("imtuGirlChart").dataset.points;
    console.log("RAW:", raw);

    let points = [];
    try {
        points = JSON.parse(raw);
        console.log("Parsed points:", points);
    } catch (e) {
        console.error("JSON parse error:", e.message);
    }

    const imtData = points.map((dp) => ({
        x: parseFloat(dp.x),
        y: parseFloat(dp.y),
    }));

    const whoAges = Array.from(
        { length: (19 - 5) * 4 + 1 },
        (_, i) => 5 + i * 0.25
    );

    const whoCurves = {
        "-3": [
            11.77, 11.757, 11.742, 11.73, 11.723, 11.721, 11.725, 11.735,
            11.751, 11.774, 11.803, 11.838, 11.879, 11.927, 11.98, 12.037,
            12.099, 12.163, 12.231, 12.302, 12.378, 12.458, 12.542, 12.632,
            12.727, 12.827, 12.931, 13.04, 13.151, 13.265, 13.379, 13.494,
            13.606, 13.717, 13.824, 13.927, 14.026, 14.119, 14.207, 14.288,
            14.362, 14.429, 14.488, 14.541, 14.586, 14.625, 14.656, 14.682,
            14.701, 14.716, 14.725, 14.731, 14.734, 14.735, 14.733, 14.729,
            14.724,
        ],
        "-2": [
            12.748, 12.734, 12.718, 12.706, 12.7, 12.699, 12.704, 12.716,
            12.735, 12.762, 12.795, 12.836, 12.884, 12.94, 13.001, 13.068,
            13.14, 13.216, 13.296, 13.38, 13.47, 13.565, 13.666, 13.772, 13.885,
            14.004, 14.129, 14.258, 14.391, 14.526, 14.663, 14.8, 14.936, 15.07,
            15.2, 15.327, 15.448, 15.564, 15.674, 15.776, 15.871, 15.958,
            16.037, 16.108, 16.172, 16.228, 16.277, 16.318, 16.354, 16.384,
            16.409, 16.431, 16.448, 16.463, 16.477, 16.488, 16.497,
        ],
        "-1": [
            13.891, 13.881, 13.869, 13.863, 13.862, 13.867, 13.879, 13.899,
            13.927, 13.963, 14.007, 14.059, 14.12, 14.188, 14.264, 14.346,
            14.434, 14.527, 14.625, 14.729, 14.838, 14.954, 15.076, 15.206,
            15.343, 15.487, 15.637, 15.793, 15.953, 16.117, 16.282, 16.448,
            16.612, 16.775, 16.934, 17.088, 17.238, 17.38, 17.516, 17.644,
            17.764, 17.874, 17.976, 18.069, 18.153, 18.229, 18.297, 18.357,
            18.411, 18.458, 18.499, 18.537, 18.571, 18.601, 18.63, 18.657,
            18.681,
        ],
        Median: [
            15.244, 15.243, 15.246, 15.255, 15.27, 15.291, 15.32, 15.357,
            15.404, 15.459, 15.524, 15.598, 15.681, 15.773, 15.874, 15.982,
            16.096, 16.217, 16.343, 16.475, 16.613, 16.76, 16.914, 17.076,
            17.246, 17.424, 17.609, 17.8, 17.997, 18.197, 18.399, 18.601,
            18.801, 18.999, 19.193, 19.382, 19.565, 19.74, 19.907, 20.065,
            20.212, 20.35, 20.477, 20.594, 20.701, 20.798, 20.886, 20.966,
            21.037, 21.101, 21.159, 21.212, 21.26, 21.306, 21.348, 21.388,
            21.427,
        ],
        "+1": [
            16.87, 16.889, 16.923, 16.964, 17.011, 17.067, 17.131, 17.204,
            17.289, 17.383, 17.488, 17.604, 17.73, 17.866, 18.012, 18.166,
            18.326, 18.493, 18.666, 18.846, 19.032, 19.226, 19.429, 19.639,
            19.859, 20.086, 20.32, 20.561, 20.806, 21.055, 21.305, 21.554, 21.8,
            22.042, 22.279, 22.509, 22.731, 22.943, 23.145, 23.336, 23.514,
            23.679, 23.832, 23.972, 24.101, 24.218, 24.324, 24.418, 24.503,
            24.58, 24.649, 24.712, 24.769, 24.823, 24.873, 24.92, 24.965,
        ],
        "+2": [
            18.858, 18.915, 19.009, 19.112, 19.224, 19.347, 19.482, 19.628,
            19.789, 19.963, 20.149, 20.349, 20.561, 20.784, 21.019, 21.263,
            21.513, 21.77, 22.031, 22.298, 22.57, 22.849, 23.134, 23.426,
            23.725, 24.029, 24.338, 24.651, 24.967, 25.282, 25.596, 25.905,
            26.207, 26.501, 26.786, 27.06, 27.321, 27.57, 27.804, 28.023,
            28.224, 28.411, 28.58, 28.734, 28.873, 28.996, 29.105, 29.201,
            29.283, 29.355, 29.418, 29.472, 29.52, 29.564, 29.602, 29.637,
            29.67,
        ],
        "+3": [
            21.34, 21.468, 21.673, 21.895, 22.133, 22.391, 22.668, 22.966,
            23.287, 23.629, 23.994, 24.377, 24.781, 25.2, 25.638, 26.085,
            26.539, 26.998, 27.459, 27.918, 28.378, 28.834, 29.29, 29.742,
            30.189, 30.63, 31.064, 31.493, 31.91, 32.316, 32.708, 33.083,
            33.439, 33.775, 34.092, 34.387, 34.66, 34.914, 35.145, 35.354,
            35.538, 35.703, 35.844, 35.964, 36.066, 36.146, 36.209, 36.254,
            36.281, 36.296, 36.299, 36.293, 36.279, 36.261, 36.235, 36.209,
            36.179,
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
        data: whoAges.map((age, i) => ({ x: age, y: values[i] })),
        borderColor: colorMap[key],
        backgroundColor: colorMap[key],
        pointRadius: 0,
        fill: false,
        tension: 0.4,
        borderWidth: 2,
    }));

    datasets.push({
        label: "IMT Anak (kg/m²)",
        data: imtData,
        borderColor: "black",
        backgroundColor: "black",
        pointRadius: 4,
        tension: 0.4,
        borderWidth: 2,
    });
    console.log("IMT Data:", imtData);

    function findClosestIndex(array, target) {
        let closestIndex = 0;
        let minDiff = Infinity;

        array.forEach((val, i) => {
            const diff = Math.abs(val - target);
            if (diff < minDiff) {
                minDiff = diff;
                closestIndex = i;
            }
        });

        return closestIndex;
    }

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
                    min: 5,
                    max: 19,
                    title: {
                        display: true,
                        text: "Usia (tahun)",
                    },
                    ticks: {
                        stepSize: 0.25,
                        callback: function (val) {
                            const tahun = Math.floor(val);
                            const bulan = Math.round((val - tahun) * 12);
                            return bulan === 0
                                ? `${tahun} th`
                                : `${tahun} th ${bulan} bln`;
                        },
                    },
                },
                y: {
                    title: { display: true, text: "IMT (kg/m²)" },
                    min: 10,
                    max: 38,
                    ticks: {
                        stepSize: 2,
                    },
                },
            },

            plugins: {
                title: {
                    display: true,
                    text: `Grafik IMT/U - ${memberName}`,
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

                            if (datasetLabel === "IMT Anak (kg/m²)") {
                                const ageIndex = findClosestIndex(whoAges, x);

                                const zScores = {
                                    "-3": whoCurves["-3"]?.[ageIndex],
                                    "-2": whoCurves["-2"]?.[ageIndex],
                                    "-1": whoCurves["-1"]?.[ageIndex],
                                    0: whoCurves["Median"]?.[ageIndex],
                                    "+1": whoCurves["+1"]?.[ageIndex],
                                    "+2": whoCurves["+2"]?.[ageIndex],
                                    "+3": whoCurves["+3"]?.[ageIndex],
                                };

                                if (
                                    !Object.values(zScores).every(
                                        (v) => v !== undefined
                                    )
                                ) {
                                    return "Data tidak tersedia";
                                }

                                if (y < zScores["-3"]) return "Gizi Buruk";
                                if (y < zScores["-2"]) return "Gizi Kurang";
                                if (y < zScores["-1"]) return "Normal (bawah)";
                                if (y < zScores["+1"]) return "Normal";
                                if (y < zScores["+2"])
                                    return "Berisiko Gizi Lebih";
                                if (y < zScores["+3"]) return "Gizi Lebih";
                                return "Obesitas";
                            }

                            return datasetLabel;
                        },
                        label: function (context) {
                            const label = context.dataset.label || "";
                            const x = context.parsed.x;
                            const y = context.parsed.y;

                            if (label === "IMT Anak (kg/m²)") {
                                const tahun = Math.floor(x);
                                const bulan = Math.round((x - tahun) * 12);
                                const usia =
                                    bulan === 0
                                        ? `${tahun} th`
                                        : `${tahun} th ${bulan} bln`;
                                return `Usia: ${usia}, IMT: ${y.toFixed(
                                    1
                                )} kg/m²`;
                            }
                            return null;
                        },
                    },
                },
            },
        },
    });
});
