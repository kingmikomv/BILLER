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

        // Auto mulai sesi jika belum tersambung
        setTimeout(() => {
            fetch(`{{ route('wa.status') }}`)
                .then(res => res.json())
                .then(data => {
                    if (!data?.status) {
                        mulaiSesi();
                    }
                });
        }, 1000);
    });

    function mulaiSesi() {
        updateStatus('Cek status...');
        fetch(`{{ route('wa.status') }}`)
            .then(res => res.json())
            .then(data => {
                if (data?.status) {
                    tampilkanStatusTerhubung(data);
                    return; // Sudah connect, tidak perlu lanjut
                }

                updateStatus('Memulai sesi...');
                updateButton('Menunggu QR...', 'wait');

                fetch(`{{ route('wa.start') }}`)
                    .then(res => res.json())
                    .then(() => {
                        pollingQRInterval = setInterval(ambilQRCode, 3000);
                        pollingStatusInterval = setInterval(ambilStatus, 3000);
                        ambilQRCode();
                    })
                    .catch(() => {
                        alert('Gagal memulai sesi.');
                        resetButton();
                    });
            });
    }

    function ambilQRCode() {
        fetch(`{{ route('wa.qr') }}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'scan' && data.qrImage) {
                    updateStatus('Menunggu Scan...');
                    document.getElementById('qr-code').innerHTML = `<img src="${data.qrImage}" style="max-width:200px;" />`;
                } else if (data.status === 'connected') {
                    ambilStatus();
                    stopPolling();
                } else if (data.status === 'initializing') {
                    updateStatus('Sedang menyiapkan QR...');
                    document.getElementById('qr-code').innerHTML = '<div class="spinner-border text-primary" role="status"></div>';
                } else {
                    updateStatus(data.message || 'QR tidak tersedia');
                    document.getElementById('qr-code').innerHTML = '';
                }
            });
    }

    function ambilStatus() {
        fetch(`{{ route('wa.status') }}`)
            .then(res => res.json())
            .then(data => {
                if (data?.status) {
                    tampilkanStatusTerhubung(data);
                    stopPolling();
                }
            });
    }

    function disconnectSesi() {
        fetch(`{{ route('wa.disconnect') }}`)
            .then(res => res.json())
            .then(() => {
                updateStatus('Disconnected');
                resetButton();
            });
    }

    function cekStatusAwal() {
        fetch(`{{ route('wa.status') }}`)
            .then(res => res.json())
            .then(data => {
                if (data?.status) {
                    tampilkanStatusTerhubung(data);
                } else {
                    updateStatus('No Instance');
                    resetButton();
                }
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
