document.addEventListener("DOMContentLoaded", function () {
    const canvas = document.getElementById("imtuBoyChart");
    if (!canvas) return;

    const ctx = canvas.getContext("2d");
    const memberName = canvas.dataset.memberName || "Data Anak-Remaja";

    const raw = document.getElementById("imtuBoyChart").dataset.points;
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
            12.118, 12.114, 12.115, 12.125, 12.141, 12.163, 12.189, 12.218,
            12.25, 12.283, 12.319, 12.356, 12.394, 12.434, 12.475, 12.518,
            12.562, 12.61, 12.661, 12.716, 12.775, 12.838, 12.905, 12.976,
            13.051, 13.13, 13.213, 13.3, 13.391, 13.487, 13.588, 13.693, 13.802,
            13.915, 14.029, 14.145, 14.261, 14.375, 14.489, 14.6, 14.708,
            14.814, 14.916, 15.015, 15.109, 15.199, 15.285, 15.365, 15.441,
            15.512, 15.577, 15.637, 15.692, 15.741, 15.784, 15.823, 15.855,
        ],
        "-2": [
            13.031, 13.024, 13.021, 13.026, 13.04, 13.061, 13.086, 13.115,
            13.148, 13.183, 13.221, 13.26, 13.302, 13.346, 13.392, 13.44,
            13.491, 13.545, 13.603, 13.667, 13.735, 13.808, 13.886, 13.969,
            14.056, 14.148, 14.245, 14.347, 14.453, 14.566, 14.684, 14.807,
            14.935, 15.067, 15.202, 15.338, 15.475, 15.611, 15.747, 15.88,
            16.011, 16.14, 16.265, 16.387, 16.505, 16.619, 16.728, 16.833,
            16.933, 17.028, 17.118, 17.204, 17.284, 17.36, 17.429, 17.495,
            17.554,
        ],
        "-1": [
            14.071, 14.063, 14.06, 14.067, 14.083, 14.107, 14.136, 14.17,
            14.209, 14.25, 14.295, 14.343, 14.394, 14.447, 14.503, 14.562,
            14.624, 14.691, 14.763, 14.84, 14.923, 15.012, 15.106, 15.206,
            15.312, 15.422, 15.539, 15.66, 15.788, 15.923, 16.063, 16.21,
            16.362, 16.519, 16.679, 16.841, 17.004, 17.167, 17.329, 17.489,
            17.647, 17.802, 17.954, 18.103, 18.247, 18.388, 18.524, 18.655,
            18.782, 18.904, 19.022, 19.134, 19.242, 19.344, 19.442, 19.535,
            19.622,
        ],
        Median: [
            15.264, 15.26, 15.264, 15.28, 15.306, 15.341, 15.382, 15.43, 15.483,
            15.541, 15.602, 15.668, 15.737, 15.809, 15.886, 15.965, 16.049,
            16.138, 16.233, 16.335, 16.443, 16.558, 16.679, 16.806, 16.939,
            17.078, 17.224, 17.375, 17.533, 17.698, 17.87, 18.049, 18.233,
            18.422, 18.615, 18.81, 19.005, 19.2, 19.394, 19.585, 19.774, 19.96,
            20.143, 20.321, 20.495, 20.664, 20.829, 20.988, 21.142, 21.291,
            21.435, 21.574, 21.708, 21.836, 21.958, 22.076, 22.188,
        ],
        "+1": [
            16.645, 16.653, 16.676, 16.712, 16.761, 16.82, 16.888, 16.964,
            17.047, 17.136, 17.231, 17.331, 17.437, 17.548, 17.663, 17.783,
            17.908, 18.04, 18.179, 18.326, 18.48, 18.64, 18.808, 18.982, 19.163,
            19.349, 19.542, 19.741, 19.946, 20.157, 20.375, 20.599, 20.829,
            21.062, 21.298, 21.534, 21.77, 22.004, 22.235, 22.462, 22.685,
            22.903, 23.116, 23.324, 23.525, 23.721, 23.91, 24.093, 24.269,
            24.439, 24.603, 24.76, 24.911, 25.055, 25.193, 25.324, 25.449,
        ],
        "+2": [
            18.259, 18.29, 18.35, 18.427, 18.52, 18.626, 18.745, 18.876, 19.017,
            19.168, 19.328, 19.497, 19.675, 19.862, 20.056, 20.258, 20.468,
            20.687, 20.916, 21.154, 21.4, 21.653, 21.914, 22.18, 22.452, 22.729,
            23.009, 23.293, 23.581, 23.871, 24.165, 24.46, 24.757, 25.053,
            25.347, 25.635, 25.918, 26.194, 26.462, 26.72, 26.969, 27.21,
            27.441, 27.662, 27.875, 28.078, 28.271, 28.456, 28.63, 28.797,
            28.954, 29.103, 29.243, 29.373, 29.496, 29.609, 29.716,
        ],
        "+3": [
            20.166, 20.238, 20.365, 20.515, 20.689, 20.883, 21.097, 21.331,
            21.584, 21.856, 22.147, 22.457, 22.785, 23.134, 23.5, 23.885,
            24.288, 24.709, 25.149, 25.605, 26.073, 26.552, 27.04, 27.533,
            28.027, 28.52, 29.008, 29.487, 29.957, 30.412, 30.854, 31.278,
            31.686, 32.073, 32.436, 32.772, 33.084, 33.371, 33.631, 33.866,
            34.081, 34.275, 34.452, 34.61, 34.754, 34.881, 34.997, 35.098,
            35.187, 35.264, 35.331, 35.387, 35.432, 35.465, 35.492, 35.507,
            35.516,
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
                                const ageIndex = whoAges.findIndex(
                                    (age) => age === x
                                );

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
