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
                                        <a href="{{route('pelanggan')}}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Nama Pelanggan</th>
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
                                            <th>Traffic Data Up / Down</th>
                                            <td id="traffic-combined">Memuat...</td>
                                        </tr>
                                        <tr>
                                            <th>Waktu Aktif</th>
                                            <td>{{$formattedUptime}}</td>
                                        </tr>
                                        <tr>
                                            <th>Total BW Up / Down</th>
                                            <td id="total-bw-upload-download">Memuat...</td>
                                        </tr>
                                        <tr>
                                            <th>Cek Ping</th>
                                            <td>
                                                <a href="javascript:void(0);" class="btn btn-primary"
                                                    onclick="cekPing('{{ $pelanggan->akun_pppoe }}')">Cek Ping !</a>
                                            </td>
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
            const apiUrl =
                `{{ route('traffic.data', ['id' => $pelanggan->id]) }}?router_id=${routerId}&akun_pppoe=${akunPppoe}`;

            let downloadData = [];
            let uploadData = [];

            const chartOptions = {
                chart: {
                    type: 'line',
                    height: 300,
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        dynamicAnimation: {
                            speed: 800
                        }
                    },
                    background: '#ffffff',
                    toolbar: {
                        show: false
                    }
                },
                series: [{
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
                    labels: {
                        datetimeUTC: false
                    },
                    title: {
                        text: "Waktu",
                        style: {
                            color: '#000',
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return formatSpeed(value);
                        }
                    },
                    title: {
                        text: "Traffic",
                        style: {
                            color: '#000',
                            fontSize: '12px'
                        }
                    },
                    min: 0,
                    tickAmount: 6
                },
                colors: ['#FF4560', '#00E396'],
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                markers: {
                    size: 0,
                    colors: ['#FF4560', '#00E396'],
                    strokeColors: '#fff',
                    strokeWidth: 2,
                    hover: {
                        size: 8
                    }
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
                    x: {
                        format: 'HH:mm:ss'
                    },
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
                    style: {
                        fontSize: '14px'
                    }
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
                        downloadData.push({
                            x: timestamp,
                            y: rxInBps
                        });
                        uploadData.push({
                            x: timestamp,
                            y: txInBps
                        });
                    }

                    if (downloadData.length > 50) downloadData.shift();
                    if (uploadData.length > 50) uploadData.shift();

                    chart.updateSeries([{
                            name: "DOWNLOAD",
                            data: downloadData
                        },
                        {
                            name: "UPLOAD",
                            data: uploadData
                        }
                    ]);

                    // Update tabel untuk upload/download
                    document.getElementById('traffic-combined').textContent =
                        `${formatSpeed(rxInBps)} / ${formatSpeed(txInBps)}`;
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

            setInterval(fetchTrafficData, 1000);
        });

    </script>
    <script>
        function updateBandwidth() {
            const pelangganId = "{{ $pelanggan->id }}"; // ID Pelanggan

            $.ajax({
                url: "{{ route('getBandwidth', ':id') }}".replace(':id',
                    pelangganId), // Mengganti :id dengan pelangganId
                method: 'GET',
                success: function (response) {
                    // Update Total Bandwidth
                    $('#total-bw-upload-download').text(`${response.totTx} / ${response.totRx}`);
                },
                error: function (error) {
                    console.log("Gagal mengambil data bandwidth:", error);
                    $('#total-bw-upload-download').text('Gagal memuat data');
                }
            });
        }

        // Update setiap 2 detik
        setInterval(updateBandwidth, 2000);

    </script>
 <script>
    const akunPppoe = "{{ $pelanggan->akun_pppoe }}";

    // Fungsi untuk mengecek ping
    function cekPing(akunPppoe) {
        Swal.fire({
            title: 'Sedang melakukan Cek Ping...',
            text: 'Mohon tunggu sebentar...',
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            },
            customClass: {
                popup: 'swal-popup-small'
            }
        });

        $.ajax({
            url: `{{ route('cekPing', ['akun' => '__akun_pppoe__']) }}`.replace('__akun_pppoe__', akunPppoe),
            method: 'GET',
            success: function (response) {
                Swal.close(); // Menutup loading indicator

                if (response.success) {
                    // Tampilkan hasil ping
                    let pingResults = response.pingResults;
                    let ipAddress = response.ip;  // Ambil IP address dari response

                    // Fungsi untuk format waktu (jam:menit:detik)
                    function formatTime(date) {
                        let hours = date.getHours().toString().padStart(2, '0');
                        let minutes = date.getMinutes().toString().padStart(2, '0');
                        let seconds = date.getSeconds().toString().padStart(2, '0');
                        return `${hours}:${minutes}:${seconds}`;
                    }

                    // Membuat HTML untuk hasil ping dengan tabel
                    let pingText = '<table style="width:100%; text-align: center; border-collapse: collapse; table-border: 1px;">';
                    pingText += '<tr><th>Test </th><th>Waktu</th><th>Hasil</th></tr>';  // Menambahkan header tabel
                    pingResults.forEach(function (result, index) {
                        // Mendapatkan waktu saat ping dilakukan
                        let pingTime = formatTime(new Date());

                        // Cek apakah hasil ping adalah timeout
                        if (result.includes("Timeout")) {
                            pingText += `<tr><td>Tes ${index + 1}</td><td>${pingTime}</td><td style="color: red;">Timeout</td></tr>`;
                        } else {
                            pingText += `<tr><td>Tes ${index + 1}</td><td>${pingTime}</td><td style="color: green;">${result}</td></tr>`;
                        }
                    });
                    pingText += '</table>';

                    Swal.fire({
                        title: `Hasil Ping Ke Ip  ${ipAddress}`,  // Menampilkan IP address di title
                        html: pingText, // Menampilkan hasil ping dalam format HTML
                        icon: 'info',
                        showConfirmButton: true,
                        customClass: {
                            popup: 'swal-popup-small'
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'Terjadi kesalahan saat melakukan ping.',
                        icon: 'error',
                        showConfirmButton: true,
                        customClass: {
                            popup: 'swal-popup-small'
                        }
                    });
                }
            },
            error: function (error) {
                Swal.close();
                Swal.fire({
                    title: 'Error',
                    text: 'Tidak dapat menghubungi server untuk melakukan ping.',
                    icon: 'error',
                    showConfirmButton: true,
                    customClass: {
                        popup: 'swal-popup-small'
                    }
                });
            }
        });
    }

    // Panggil fungsi cekPing saat tombol diklik
    $(document).ready(function () {
        $("a[data-action='cekPing']").on('click', function () {
            cekPing(akunPppoe);
        });
    });
</script>



</body>

</html>
