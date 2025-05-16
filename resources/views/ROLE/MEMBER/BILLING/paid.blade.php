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
                                                        <td>
                                                            <a href="#" class="text-primary show-invoice-detail"
                                                                data-id="{{ $invoice->invoice_id }}" data-toggle="modal"
                                                                data-target="#invoiceDetailModal">
                                                                {{ $invoice->invoice_id }}
                                                            </a>
                                                        </td>
                                                        <td>
                                                            {{ 'ID : ' . $invoice->pelanggan->pelanggan_id ?? '-' }} |
                                                            {{ $invoice->pelanggan->nama_pelanggan ?? '-' }}

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
                                                                <a href="" target="_blank"
                                                                    class="btn btn-sm btn-success">
                                                                    <i class="fab fa-whatsapp"></i> WA
                                                                </a>

                                                                <a href="" target="_blank"
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
        <!-- Modal Global -->
        <div class="modal fade" id="invoiceDetailModal" tabindex="-1" role="dialog"
            aria-labelledby="invoiceDetailLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Invoice</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="invoiceDetailContent">
                        <p class="text-center">Memuat data...</p>
                    </div>
                </div>
            </div>
        </div>

        <x-dhs.footer />
    </div>
    <!-- ./wrapper -->

    <x-dhs.scripts />
    <script>
        $(document).on('click', '.show-invoice-detail', function() {
            const invoiceId = $(this).data('id');
            $('#invoiceDetailContent').html('<p class="text-center">Memuat data...</p>');

            $.ajax({
                url: '/home/billing/paid/detail/' + invoiceId,
                type: 'GET',
                success: function(response) {
                    $('#invoiceDetailContent').html(response);
                },
                error: function() {
                    $('#invoiceDetailContent').html(
                        '<p class="text-danger text-center">Gagal memuat data.</p>');
                }
            });
        });
    </script>

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
