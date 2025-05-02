<x-dhs.head />

<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">

        <x-dhs.preload />
        <x-dhs.nav />
        <x-dhs.sidebar />

        <div class="content-wrapper" style="margin-bottom: 50px">
            <x-dhs.content-header />

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="info-box bg-dark shadow-sm">
                                <span class="info-box-icon bg-purple elevation-1"><i class="fab fa-whatsapp"></i></span>

                                <div class="info-box-content">
                                    <span class="info-box-text font-weight-bold">WhatsApp Messages</span>
                                    <span>Gunakan Account WhatsApp Sendiri</span>

                                    <div class="mt-2 d-flex align-items-center">
                                        <label class="mr-2">Status</label> : <span id="wa-status" class="ml-2">No Instance</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <label class="mr-2">ID</label> : <span id="wa-id" class="ml-2">-</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <label class="mr-2">Name</label> : <span id="wa-name" class="ml-2">-</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <label class="mr-2">Battery</label> : <span id="wa-battery" class="ml-2">-</span>
                                    </div>

                                    <div class="mt-3">
                                        <button class="btn btn-primary btn-sm" id="generateQR">Generate QR Code</button>
                                    </div>

                                    <div class="mt-3" id="qr-code"></div>
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
        const sessionId = "{{ auth()->user()->unique_member }}";
        let pollingQRInterval = null;
        let pollingStatusInterval = null;

        document.getElementById('generateQR').addEventListener('click', function () {
            fetch(`http://localhost:3000/api/start?session_id=${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('wa-status').innerText = 'Menunggu QR...';
                    fetchQRCode(); // Tampilkan pertama kali
                    if (pollingQRInterval) clearInterval(pollingQRInterval);
                    pollingQRInterval = setInterval(fetchQRCode, 15000); // QR diperbarui tiap 15 detik

                    if (pollingStatusInterval) clearInterval(pollingStatusInterval);
                    pollingStatusInterval = setInterval(fetchStatus, 5000);
                })
                .catch(error => {
                    console.error('Gagal memulai session:', error);
                    alert('Gagal generate QR');
                });
        });

        function fetchQRCode() {
            fetch(`http://localhost:3000/api/qr?session_id=${sessionId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.qrImage) {
                        document.getElementById('qr-code').innerHTML = `<img src="${data.qrImage}" alt="QR Code" />`;
                        document.getElementById('wa-status').innerText = 'Menunggu Scan...';
                    } else {
                        document.getElementById('wa-status').innerText = data.message || 'QR tidak tersedia';
                        document.getElementById('qr-code').innerHTML = '';
                    }
                })
                .catch(err => {
                    console.error('Gagal ambil QR:', err);
                });
        }

        function fetchStatus() {
            fetch(`http://localhost:3000/api/status?session_id=${sessionId}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.status === true) {
                        document.getElementById('wa-status').innerText = 'Terhubung';
                        document.getElementById('wa-id').innerText = data.user.id || '-';
                        document.getElementById('wa-name').innerText = data.user.name || '-';
                        document.getElementById('wa-battery').innerText = data.user.battery || '-';

                        clearInterval(pollingQRInterval);
                        clearInterval(pollingStatusInterval);
                        document.getElementById('qr-code').innerHTML = '';
                    }
                })
                .catch(err => {
                    console.error('Gagal ambil status:', err);
                });
        }
    </script>
</body>
</html>
