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
        const API_BASE_URL = "http://wa.aqtnetwork.my.id:3000";
    
        let pollingQRInterval = null;
        let pollingStatusInterval = null;
    
        document.addEventListener('DOMContentLoaded', function () {
            cekStatusAwal(); // Cek status saat halaman pertama kali dimuat
    
            document.getElementById('generateQR').addEventListener('click', mulaiSesi);
        });
    
        function mulaiSesi() {
            document.getElementById('wa-status').innerText = 'Memulai sesi...';
    
            fetch(`${API_BASE_URL}/api/start?session_id=${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('wa-status').innerText = 'Menunggu QR...';
    
                    ambilQRCode(); // QR pertama kali
    
                    clearInterval(pollingQRInterval);
                    pollingQRInterval = setInterval(ambilQRCode, 5000);
    
                    clearInterval(pollingStatusInterval);
                    pollingStatusInterval = setInterval(ambilStatus, 5000);
    
                    // Ubah tombol Generate QR menjadi tombol Disconnect hanya setelah terhubung
                    document.getElementById('generateQR').innerText = 'Menunggu Scan...';
                    document.getElementById('generateQR').removeEventListener('click', mulaiSesi);
                    document.getElementById('generateQR').addEventListener('click', disconnectSesi);
                })
                .catch(error => {
                    console.error('Gagal memulai sesi:', error);
                    alert('Gagal generate QR');
                });
        }
    
        function ambilQRCode() {
            fetch(`${API_BASE_URL}/api/qr?session_id=${sessionId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'scan' && data.qrImage) {
                        document.getElementById('wa-status').innerText = 'Menunggu Scan...';
                        document.getElementById('qr-code').innerHTML = `<img src="${data.qrImage}" alt="QR Code" style="max-width:200px;" />`;
                    } else if (data.status === 'connected') {
                        ambilStatus(); // Langsung ambil data lengkap
                        clearInterval(pollingQRInterval);
                        clearInterval(pollingStatusInterval);
                    } else {
                        document.getElementById('wa-status').innerText = data.message || 'QR tidak tersedia';
                        document.getElementById('qr-code').innerHTML = '';
                    }
                })
                .catch(err => {
                    console.error('Gagal ambil QR:', err);
                    document.getElementById('wa-status').innerText = 'Gagal ambil QR';
                });
        }
    
        function ambilStatus() {
            fetch(`${API_BASE_URL}/api/status?session_id=${sessionId}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.status === true) {
                        perbaruiUIYangTerhubung(data);
                        clearInterval(pollingQRInterval);
                        clearInterval(pollingStatusInterval);
                    }
                })
                .catch(err => {
                    console.error('Gagal ambil status:', err);
                });
        }
    
        function cekStatusAwal() {
            fetch(`${API_BASE_URL}/api/status?session_id=${sessionId}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.status === true) {
                        perbaruiUIYangTerhubung(data);
                    } else {
                        document.getElementById('wa-status').innerText = 'No Instance';
                        document.getElementById('generateQR').innerText = 'Generate QR Code';
                        document.getElementById('generateQR').removeEventListener('click', disconnectSesi);
                        document.getElementById('generateQR').addEventListener('click', mulaiSesi);
                    }
                })
                .catch(err => {
                    console.error('Gagal cek status awal:', err);
                    document.getElementById('wa-status').innerText = 'Gagal cek status';
                });
        }
    
        function perbaruiUIYangTerhubung(data) {
            document.getElementById('wa-status').innerText = '✅ Terhubung';
            document.getElementById('wa-id').innerText = data.user?.id || '-';
            document.getElementById('wa-name').innerText = data.user?.name || '-';
            document.getElementById('wa-battery').innerText = data.user?.battery || '-';
            document.getElementById('qr-code').innerHTML = '';
    
            // Ubah tombol menjadi Disconnect
            document.getElementById('generateQR').innerText = 'Disconnect';
            document.getElementById('generateQR').removeEventListener('click', mulaiSesi);
            document.getElementById('generateQR').addEventListener('click', disconnectSesi);
        }
    
        function disconnectSesi() {
            fetch(`${API_BASE_URL}/api/disconnect?session_id=${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('wa-status').innerText = 'Disconnected';
                    document.getElementById('qr-code').innerHTML = '';
                    document.getElementById('generateQR').innerText = 'Generate QR Code';
                    document.getElementById('generateQR').removeEventListener('click', disconnectSesi);
                    document.getElementById('generateQR').addEventListener('click', mulaiSesi);
                })
                .catch(error => {
                    console.error('Gagal disconnect sesi:', error);
                    alert('Gagal disconnect');
                });
        }
    </script>

</body>
</html>
