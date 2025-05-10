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
                                    Data Pelanggan Sudah Bayar
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-small-font" id="sudahBayarTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Invoice ID</th>
                                                    <th>Data Plg.</th>
                                                    <th>Tgl. Bayar</th>
                                                    <th>Bulan</th>
                                                    <th>Jml. Bayar</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($paidInvoices as $index => $invoice)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $invoice->invoice_id }}</td>
                                                        <td>
                                                            {{ 'ID : ' . $invoice->pelanggan->pelanggan_id ?? '-' }} |
                                                            Site :
                                                            {{ optional($invoice->pelanggan->mikrotik)->site ?? '-' }} |
                                                            {{ $invoice->pelanggan->nama_pelanggan ?? '-' }} |
                                                            {{ $invoice->pelanggan->akun_pppoe ?? '-' }}
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($invoice->tanggal_pembayaran)->format('d/m/Y') }}
                                                        </td>
                                                        <td>{{ \Carbon\Carbon::parse($invoice->tanggal_pembayaran)->translatedFormat('F Y') }}
                                                        </td>
                                                        <td>Rp
                                                            {{ number_format(optional($invoice->pelanggan->paket)->harga_paket ?? 0, 0, ',', '.') }}
                                                        </td>
                                                        <td><span class="badge badge-success">Lunas</span></td>
                                                       <td>
    <div class="d-flex gap-1 flex-wrap">
        <a href=""
           target="_blank"
           class="btn btn-sm btn-success">
            <i class="fab fa-whatsapp"></i> WA
        </a>

        <a href=""
           target="_blank"
           class="btn btn-sm btn-primary">
            <i class="fas fa-print"></i> Cetak
        </a>
    </div>
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

    <!-- DataTables -->
    <script>
        $(document).ready(function() {
            $('#sudahBayarTable').DataTable({
                responsive: true,
                paging: true,
                lengthChange: false,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false
            });
        });
    </script>

</body>

</html>
