<x-dhs.head />

<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">

        <x-dhs.preload />

        <!-- Navbar -->
        <x-dhs.nav />
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <x-dhs.sidebar />

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper" style="margin-bottom: 50px">
            <!-- Content Header (Page header) -->
            <x-dhs.content-header />
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    Riwayat Tagihan
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-small-font" id="tagihanTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Invoice ID</th>
                                                    <th>Data Plg.</th>
                                                    <th>Tgl. Tagihan</th>
                                                    <th>Bulan</th>
                                                    <th>Jml. Tagihan</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($invoices as $index => $invoice)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $invoice->invoice_id }}</td>
                                                        <td>{{ "ID : ". $invoice->pelanggan->pelanggan_id ?? '-' }} | Site : {{ optional($invoice->pelanggan->mikrotik)->site ?? '-' }} | {{ $invoice->pelanggan->nama_pelanggan ?? '-' }} | {{ $invoice->pelanggan->akun_pppoe ?? '-'}}</td>
                                                        <td>{{ \Carbon\Carbon::parse($invoice->tanggal_pembayaran)->format('d/m/Y') }}</td>
<td>{{ \Carbon\Carbon::parse($invoice->tanggal_pembayaran)->translatedFormat('F Y') }}
                                                        </td>                                                        <td>Rp {{ number_format(optional($invoice->pelanggan->paket)->harga_paket ?? 0, 0, ',', '.') }}</td>
                                                        <td>
                                                            @if ($invoice->status == 'Lunas')
                                                                <span class="badge badge-success">Lunas</span>
                                                            @elseif ($invoice->status == 'Tertunggak' || $invoice->status == 'Belum Lunas')
                                                                <span class="badge badge-warning">Belum Lunas</span>
                                                            @endif
                                                        </td>
                                                       
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- /.content -->
        </div>

        <x-dhs.footer />
    </div>
    <!-- ./wrapper -->

    <x-dhs.scripts />

    <!-- Load SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- DataTables + SweetAlert2 Script -->
    <script>
        $(document).ready(function () {
            // Initialize DataTable
            $('#tagihanTable').DataTable({
                responsive: true,
                paging: true,
                lengthChange: false,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false
            });

            // SweetAlert konfirmasi pembayaran
            $(document).on('click', '.confirm-bayar', function (e) {
                e.preventDefault();
                const url = $(this).data('url');

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Tagihan ini akan ditandai sebagai LUNAS!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, tandai lunas!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });
        });
    </script>

</body>

</html>
