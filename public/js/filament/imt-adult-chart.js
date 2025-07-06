document.addEventListener("DOMContentLoaded", function () {
    const canvas = document.getElementById("imtAdultChart");
    if (!canvas) return;

    const ctx = canvas.getContext("2d");
    const memberName = canvas.dataset.memberName || "Data Anggota";
    const category = canvas.dataset.category || "dewasa";

    let points = [];
    try {
        points = JSON.parse(canvas.dataset.points || "[]");
    } catch (e) {
        console.error("JSON parse error:", e.message);
    }

    const imtData = points.map((dp) => ({
        x: parseFloat(dp.x),
        y: parseFloat(dp.y),
    }));

    function formatUsia(val) {
        const tahun = Math.floor(val);
        const bulan = Math.round((val - tahun) * 12);
        return bulan === 0 ? `${tahun} th` : `${tahun} th ${bulan} bln`;
    }

    function generateUsiaDesimal(start, end) {
        const result = [];
        for (let t = start; t <= end; t++) {
            for (let b = 0; b < 12; b += 3) {
                result.push(parseFloat((t + b / 12).toFixed(2)));
            }
        }
        return result;
    }

    let usiaRange;
    switch (category) {
        case "lansia":
            usiaRange = [61, 100];
            break;
        case "ibu-hamil":
            usiaRange = [15, 55];
            break;
        case "dewasa":
        default:
            usiaRange = [19, 60];
    }

    const usiaList = generateUsiaDesimal(usiaRange[0], usiaRange[1]);
    const jumlahTitik = usiaList.length;

    const whoCurves = {
        Kurus: Array(jumlahTitik).fill(18.5),
        Normal: Array(jumlahTitik).fill(25.0),
        Gemuk: Array(jumlahTitik).fill(30.0),
        Obesitas_Kelas_I: Array(jumlahTitik).fill(35.0),
        Obesitas_Kelas_II: Array(jumlahTitik).fill(40.0),
    };

    const labelMap = {
        Kurus: "Kurus",
        Normal: "Normal",
        Gemuk: "Gemuk (Pra-Obesitas)",
        Obesitas_Kelas_I: "Obesitas Kelas I",
        Obesitas_Kelas_II: "Obesitas Kelas II",
    };

    const colorMap = {
        Kurus: "rgba(0, 29, 255, 0.8)",
        Normal: "rgba(0, 255, 72, 0.8)",
        Gemuk: "rgba(255, 206, 86, 0.8)",
        Obesitas_Kelas_I: "rgba(255, 87, 0, 0.8)",
        Obesitas_Kelas_II: "rgba(255, 0, 0, 0.8)",
    };

    const datasets = Object.entries(whoCurves).map(([key, values]) => ({
        label: labelMap[key] || key,
        data: usiaList.map((u, i) => ({ x: u, y: values[i] })),
        borderColor: colorMap[key],
        backgroundColor: colorMap[key],
        pointRadius: 0,
        borderWidth: 2,
        tension: 0.4,
        fill: false,
    }));

    if (imtData.length > 0) {
        datasets.push({
            label: "IMT (kg/m²)",
            data: imtData,
            borderColor: "black",
            backgroundColor: "black",
            pointRadius: 4,
            borderWidth: 2,
            tension: 0.4,
        });
    }

    new Chart(ctx, {
        type: "line",
        data: {
            datasets,
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    type: "linear",
                    title: {
                        display: true,
                        text: "Usia",
                    },
                    min: usiaList[0],
                    max: usiaList[usiaList.length - 1],
                    ticks: {
                        stepSize: 0.25,
                        autoSkip: false,
                        maxRotation: 0,
                        minRotation: 0,
                        callback: (val) => {
                            const tahun = Math.floor(val);
                            const bulan = Math.round((val - tahun) * 12);
                            return bulan === 0 ? `${tahun}` : "";
                        },
                    },
                },
                y: {
                    title: {
                        display: true,
                        text: "IMT (kg/m²)",
                    },
                    min: 10,
                    max: 50,
                    ticks: {
                        stepSize: 2,
                    },
                },
            },
            plugins: {
                title: {
                    display: true,
                    text: `Grafik IMT ${
                        category === "dewasa"
                            ? "Dewasa"
                            : category === "lansia"
                            ? "Lansia"
                            : "Ibu Hamil"
                    } - ${memberName}`,
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
                            const imt = context[0].parsed.y;
                            let kategori = "";

                            if (imt < 18.5) kategori = "Kurus";
                            else if (imt < 25) kategori = "Normal";
                            else if (imt < 30)
                                kategori = "Gemuk (Pra-Obesitas)";
                            else if (imt < 35) kategori = "Obesitas Kelas I";
                            else if (imt < 40) kategori = "Obesitas Kelas II";
                            else kategori = "Obesitas Kelas III";

                            return kategori;
                        },
                        label: function (context) {
                            const imt = context.parsed.y;
                            const usia = context.parsed.x;
                            return `Usia: ${formatUsia(
                                usia
                            )}, IMT: ${imt.toFixed(1)} kg/m²`;
                        },
                    },
                },
            },
        },
    });
});
