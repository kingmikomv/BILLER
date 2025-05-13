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
                                    Data Pelanggan Belum Bayar
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-small-font" id="belumBayarTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Invoice ID</th>
                                                    <th>Data Plg.</th>
                                                    <th>Tgl. Tagihan</th>
                                                    <th>Jml. Tagihan</th>
                                                    <th>Option</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($unpaidInvoices as $index => $invoice)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $invoice->invoice_id }}</td>
                                                    <td>{{ "ID : ". $invoice->pelanggan->pelanggan_id ?? '-' }} | Site : {{ optional($invoice->pelanggan->mikrotik)->site ?? '-' }} | {{ $invoice->pelanggan->nama_pelanggan ?? '-' }} | {{ $invoice->pelanggan->akun_pppoe ?? '-'}}</td>
                                                    <td>{{ \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('d/m/Y') }}
                                                    </td>
                                                    <td>Rp
                                                        {{ number_format(optional($invoice->pelanggan->paket)->harga_paket ?? 0, 0, ',', '.') }}
                                                    </td>
                                                    <td>
                                                        <div class="row">
                                                            <div class="col-md-4 mt-1">

                                                                <a href="" class="btn btn-success btn-sm"><i
                                                                        class="fas fa-check"></i> Bayar</a>

                                                            </div>
                                                            <div class="col-md-4 mt-1">
                                                                <a href="" class="btn btn-primary btn-sm"><i
                                                                        class="fas fa-eye"></i> Detail</a>
                                                            </div>
                                                            <div class="col-md-4  mt-1">
                                                                <a href="" class="btn btn-secondary btn-sm"><i
                                                                        class="fas fa-print"></i> Cetak</a>

                                                            </div>

                                                        </div>

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

    <!-- DataTables Initialization -->
    <script>
        $(document).ready(function () {
            $('#belumBayarTable').DataTable({
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
