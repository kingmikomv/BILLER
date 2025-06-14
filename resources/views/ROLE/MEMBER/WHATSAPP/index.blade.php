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
                                    <div class="row">
                                        <div class="col-md-6">
                                            <span class="info-box-text font-weight-bold">WhatsApp Messages</span>
                                            <span>Gunakan Account WhatsApp Sendiri</span>

                                            <div class="mt-2 d-flex align-items-center">
                                                <label class="mr-2">Status</label> : <span id="wa-status" class="ml-2">Memuat...</span>
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
                                                <button class="btn btn-primary btn-sm" id="wa-action-btn">Loading...</button>
                                            </div>

                                            <div class="mt-3" id="qr-code"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="{{ route('whatsapp.template') }}" class="btn btn-primary btn-block">
                                                <i class="fab fa-whatsapp"></i> Template WA Invoice
                                            </a>
                                             <a href="{{ route('whatsapp.template') }}" class="btn btn-success btn-block">
                                                <i class="fab fa-whatsapp"></i> Template WA CS
                                            </a>
                                        </div>
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
        const sessionId = "{{ auth()->user()->unique_member }}";
        const API_BASE_URL = "http://103.160.63.163:3000";

        let pollingQRInterval = null;
        let pollingStatusInterval = null;

        document.addEventListener('DOMContentLoaded', () => {
            cekStatusAwal();

            document.getElementById('wa-action-btn').addEventListener('click', () => {
                const currentAction = document.getElementById('wa-action-btn').dataset.action;

                if (currentAction === 'start') {
                    mulaiSesi();
                } else if (currentAction === 'disconnect') {
                    disconnectSesi();
                }
            });
        });

        function mulaiSesi() {
            updateStatus('Memulai sesi...');
            updateButton('Menunggu QR...', 'wait');

            fetch(`${API_BASE_URL}/api/start?session_id=${sessionId}`)
                .then(res => res.json())
                .then(() => {
                    pollingQRInterval = setInterval(ambilQRCode, 5000);
                    pollingStatusInterval = setInterval(ambilStatus, 5000);
                    ambilQRCode();
                })
                .catch(err => {
                    console.error(err);
                    alert('Gagal memulai sesi.');
                    resetButton();
                });
        }

        function ambilQRCode() {
            fetch(`${API_BASE_URL}/api/qr?session_id=${sessionId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'scan' && data.qrImage) {
                        updateStatus('Menunggu Scan...');
                        document.getElementById('qr-code').innerHTML = `<img src="${data.qrImage}" style="max-width:200px;" />`;
                    } else if (data.status === 'connected') {
                        ambilStatus();
                        stopPolling();
                    } else {
                        updateStatus(data.message || 'QR tidak tersedia');
                        document.getElementById('qr-code').innerHTML = '';
                    }
                })
                .catch(err => {
                    console.error(err);
                    updateStatus('Gagal ambil QR');
                });
        }

        function ambilStatus() {
            fetch(`${API_BASE_URL}/api/status?session_id=${sessionId}`)
                .then(res => res.json())
                .then(data => {
                    if (data?.status) {
                        tampilkanStatusTerhubung(data);
                        stopPolling();
                    }
                })
                .catch(err => {
                    console.error(err);
                });
        }

        function disconnectSesi() {
            fetch(`${API_BASE_URL}/api/disconnect?session_id=${sessionId}`)
                .then(res => res.json())
                .then(() => {
                    updateStatus('Disconnected');
                    resetButton();
                })
                .catch(err => {
                    console.error(err);
                    alert('Gagal disconnect.');
                });
        }

        function cekStatusAwal() {
            fetch(`${API_BASE_URL}/api/status?session_id=${sessionId}`)
                .then(res => res.json())
                .then(data => {
                    if (data?.status) {
                        tampilkanStatusTerhubung(data);
                    } else {
                        updateStatus('No Instance');
                        resetButton();
                    }
                })
                .catch(err => {
                    console.error(err);
                    updateStatus('Gagal cek status');
                    resetButton();
                });
        }

        function tampilkanStatusTerhubung(data) {
            updateStatus('✅ Terhubung');
            document.getElementById('wa-id').innerText = data.user?.id || '-';
            document.getElementById('wa-name').innerText = data.user?.name || '-';
            document.getElementById('wa-battery').innerText = data.user?.battery || '-';
            document.getElementById('qr-code').innerHTML = '';

            updateButton('Disconnect', 'disconnect');
        }

        function updateStatus(text) {
            document.getElementById('wa-status').innerText = text;
        }

        function updateButton(text, action) {
            const btn = document.getElementById('wa-action-btn');
            btn.innerText = text;
            btn.dataset.action = action;
        }

        function resetButton() {
            updateButton('Generate QR Code', 'start');
            document.getElementById('qr-code').innerHTML = '';
            document.getElementById('wa-id').innerText = '-';
            document.getElementById('wa-name').innerText = '-';
            document.getElementById('wa-battery').innerText = '-';
        }

        function stopPolling() {
            clearInterval(pollingQRInterval);
            clearInterval(pollingStatusInterval);
        }
    </script>
</body>
</html>
