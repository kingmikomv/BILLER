<x-dhs.head />
<script src="https://cdn.jsdelivr.net/gh/zarocknz/javascript-winwheel/Winwheel.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .spinner-container {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    /* Panah menghadap ke bawah */
    .arrow {
        width: 0;
        height: 0;
        position: absolute;
        top: -35px;
        /* Posisikan lebih tinggi agar lebih jelas */
        left: 50%;
        transform: translateX(-50%);

        border-left: 30px solid transparent;
        /* Lebarkan panah */
        border-right: 30px solid transparent;
        border-top: 50px solid #ffcc00;
        /* Warna kuning terang */

        /* Tambahkan outline hitam agar tidak samar */
        filter: drop-shadow(2px 2px 2px black);

        /* Tambahkan efek bayangan agar tampak timbul */
        box-shadow: 0px 0px 0px rgba(0, 0, 0, 0.5);

        z-index: 10;
    }



    .spin-btn {
        margin-top: 20px;
        padding: 12px 24px;
        font-size: 20px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
    }

    .spin-btn:hover {
        background-color: #0056b3;
    }
</style>

<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">
        <x-dhs.preload />
        <x-dhs.sumin.nav />
        <x-dhs.sidebar />

        <div class="content-wrapper" style="margin-bottom: 50px">
            <x-dhs.sumin.content-header-sumin />

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header border-transparent">
                                    <h3 class="card-title">Spinner Undian {{ $kode_undian->kode_undian }} |
                                        {{ $kode_undian->nama_undian }}
                                    </h3>
                                </div>
                                <div class="card-body table-responsive text-center">
                                    <div class="spinner-container">
                                        <div class="arrow"></div> <!-- Panah di atas roda -->
                                        <canvas id="wheelCanvas" width="400" height="400"></canvas>
                                        <button class="spin-btn" id="spinButton">Putar</button>
                                    </div>
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
        document.addEventListener("DOMContentLoaded", function () {
            if (typeof Winwheel === "undefined") {
                console.error("Winwheel.js gagal dimuat! Periksa koneksi atau URL CDN.");
                return;
            }

            var usernames = @json($usernames);
            if (!usernames || usernames.length === 0) {
                console.error("Tidak ada data pengguna untuk ditampilkan di roda!");
                return;
            }

            var segments = usernames.map(name => ({
                text: name,
                fillStyle: getRandomColor(),
                textFillStyle: "white",
                textFontSize: 18
            }));

            function getRandomColor() {
                const colors = [
                    "#ff5733", "#33ff57", "#5733ff", "#ff33a8", "#33a8ff", "#a833ff",
                    "#ffcc33", "#ff6633", "#33ffcc", "#3366ff", "#cc33ff", "#ff3366",
                    "#00b894", "#fdcb6e", "#e17055", "#6c5ce7", "#0984e3", "#d63031",
                    "#f39c12", "#1abc9c", "#e74c3c", "#8e44ad", "#2ecc71", "#3498db"
                ];
                return colors[Math.floor(Math.random() * colors.length)];
            }

            var wheel = new Winwheel({
                'canvasId': 'wheelCanvas',
                'numSegments': segments.length,
                'segments': segments,
                'animation': {
                    'type': 'spinToStop',
                    'duration': 5,
                    'spins': 8,
                    'callbackFinished': alertWinner
                }
            });

            document.getElementById("spinButton").addEventListener("click", function () {
                wheel.startAnimation();
            });

            function alertWinner(indicatedSegment) {
                let winner = indicatedSegment.text;

                Swal.fire({
                    title: "Pemenang!",
                    text: "Selamat, " + winner + "!",
                    icon: "success",
                    confirmButtonText: "OK"
                }).then(() => {
                    updateWinnerInDatabase(winner);
                });
            }

            function updateWinnerInDatabase(winner) {
                let kodeUndian = "{{ $kode_undian->kode_undian }}"; // Ambil kode undian dari Blade

                $.ajax({
                    url: "{{ route('update.winner') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        winner: winner,
                        kode_undian: kodeUndian
                    },
                    success: function (response) {
                        console.log("Response dari server:", response);

                        // Redirect kembali ke halaman sebelumnya setelah sukses
                        window.location.href = "{{ route('undian.kocok') }}";
                    },
                    error: function (xhr, status, error) {
                        console.error("Gagal memperbarui pemenang:", error);
                    }
                });
            }

        });
    </script>


</body>

</html>