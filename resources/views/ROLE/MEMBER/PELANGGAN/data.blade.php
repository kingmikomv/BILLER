<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<x-dhs.head />

<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">
        <x-dhs.preload />
        <x-dhs.nav />
        <x-dhs.sidebar />

        <div class="content-wrapper">
            <x-dhs.content-header title="Data Pelanggan" />

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Data Pelanggan</h3>
                                    <div class="card-tools">
                                        <a class="btn btn-primary" href="{{ route('formulir') }}">
                                            <i class="fas fa-plus"></i> Tambah Pelanggan
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h4>Detail Pelanggan</h4>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Nama</th>
                                            <td>{{ $pelanggan->nama_pelanggan }}</td>
                                        </tr>
                                        <tr>
                                            <th>Alamat</th>
                                            <td>{{ $pelanggan->alamat }}</td>
                                        </tr>
                                        <tr>
                                            <th>Paket</th>
                                            <td>{{ $pelanggan->paket->nama_paket }}</td>
                                        </tr>
                                        <tr>
                                            <th>Akun PPPoE</th>
                                            <td>{{ $pelanggan->akun_pppoe }}</td>
                                        </tr>
                                        <tr>
                                            <th>Traffic Upload</th>
                                            <td id="traffic-tx">Memuat...</td>
                                        </tr>
                                        <tr>
                                            <th>Traffix Download</th>
                                            <td id="traffic-rx">Memuat...</td>
                                        </tr>
                                    </table>
                                    
                                    <h4 class="mt-4">Traffic Monitoring</h4>
                                    <div id="traffic-chart"></div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <x-dhs.footer />
    </div>

    <x-dhs.scripts />
    <script>
        document.addEventListener('DOMContentLoaded', function () {
    const routerId = "{{ $pelanggan->router_id }}";
    const akunPppoe = "{{ $pelanggan->akun_pppoe }}";
    const apiUrl = `{{ route('traffic.data', ['id' => $pelanggan->id]) }}?router_id=${routerId}&akun_pppoe=${akunPppoe}`;

    let downloadData = [];
    let uploadData = [];

    const chartOptions = {
        chart: {
            type: 'line',
            height: 300,
            animations: {
                enabled: true,
                easing: 'easeinout',
                dynamicAnimation: { speed: 800 } // Lebih smooth
            },
            background: '#ffffff',
            toolbar: { show: false }
        },
        series: [
            {
                name: "DOWNLOAD",
                data: downloadData
            },
            {
                name: "UPLOAD",
                data: uploadData
            }
        ],
        xaxis: {
            type: 'datetime',
            labels: { datetimeUTC: false },
            title: { text: "Waktu", style: { color: '#000', fontSize: '12px' } }
        },
        yaxis: {
            labels: {
                formatter: function (value) {
                    return formatSpeed(value);
                }
            },
            title: { text: "Traffic", style: { color: '#000', fontSize: '12px' } },
            min: 0,
            tickAmount: 6 // Batasi jumlah garis y-axis untuk stabilitas
        },
        colors: ['#FF4560', '#00E396'],
        stroke: {
            curve: 'smooth', // Garis mulus
            width: 2
        },
        markers: {
            size: 6,
            colors: ['#FF4560', '#00E396'],
            strokeColors: '#fff',
            strokeWidth: 2,
            hover: { size: 8 }
        },
        legend: {
            position: 'bottom',
            markers: {
                width: 14,
                height: 14,
                radius: 7
            }
        },
        tooltip: {
            shared: true,
            intersect: false,
            x: { format: 'HH:mm:ss' },
            y: {
                formatter: function (value) {
                    return formatSpeed(value);
                }
            }
        },
        noData: {
            text: 'Memuat data...',
            align: 'center',
            verticalAlign: 'middle',
            style: { fontSize: '14px' }
        }
    };

    const chart = new ApexCharts(document.querySelector('#traffic-chart'), chartOptions);
    chart.render();

    async function fetchTrafficData() {
        try {
            const response = await fetch(apiUrl);
            const data = await response.json();

            if (data.error) {
                console.error(data.error);
                return;
            }

            const txInBps = data.rx; // Download
            const rxInBps = data.tx; // Upload
            const timestamp = new Date().getTime();

            if (txInBps >= 0 && rxInBps >= 0) {
                downloadData.push({ x: timestamp, y: rxInBps });
                uploadData.push({ x: timestamp, y: txInBps });
            }

            // Batasi data hingga 50 titik, hapus data paling lama
            if (downloadData.length > 50) downloadData.shift();
            if (uploadData.length > 50) uploadData.shift();

            // Perbarui grafik
            chart.updateSeries([
                { name: "DOWNLOAD", data: downloadData },
                { name: "UPLOAD", data: uploadData }
            ]);

            // Update tabel
            document.getElementById('traffic-tx').textContent = formatSpeed(txInBps);
            document.getElementById('traffic-rx').textContent = formatSpeed(rxInBps);
        } catch (error) {
            console.error("Gagal mengambil data:", error);
        }
    }

    function formatSpeed(value) {
        if (value >= 1e9) {
            return (value / 1e9).toFixed(2) + " Gbps";
        } else if (value >= 1e6) {
            return (value / 1e6).toFixed(2) + " Mbps";
        } else if (value >= 1e3) {
            return (value / 1e3).toFixed(2) + " Kbps";
        } else {
            return value.toFixed(2) + " bps";
        }
    }

    // Panggil fungsi fetchTrafficData setiap detik
    setInterval(fetchTrafficData, 1000);
});

    </script>
    
    
    <div id="traffic-chart"></div>
    
    
    
    
    
</body>
</html>
