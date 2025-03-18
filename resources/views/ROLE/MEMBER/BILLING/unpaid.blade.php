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
                                    <table id="belumBayarTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Number</th>
                    <th>Full Name</th>
                    <th>Site</th>
                    <th>Invoice Date</th>
                    <th>Due Date</th>
                    <th>Subscription Period</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($belumBayar as $index => $p)
                <tr>
    <td>{{ $index + 1 }}</td>
    <td>{{ $p->nomor_pelanggan }}</td>
    <td>{{ $p->nama_pelanggan }}</td>
    <td>{{ $p->mikrotik->site ?? '-' }}</td>
    <td>
        {{ $p->pembayaran_selanjutnya ? \Carbon\Carbon::parse($p->pembayaran_selanjutnya)->subDays(7)->format('d/m/Y') : '-' }}
    </td>
    <td>
        {{ $p->pembayaran_selanjutnya ? \Carbon\Carbon::parse($p->pembayaran_selanjutnya)->format('d/m/Y') : '-' }}
    </td>
    <td>
        {{ $p->tanggal_daftar ? \Carbon\Carbon::parse($p->tanggal_daftar)->format('d/m/Y') : '-' }} 
        s.d 
        {{ $p->pembayaran_selanjutnya ? \Carbon\Carbon::parse($p->pembayaran_selanjutnya)->format('d/m/Y') : '-' }}
    </td>
    <td>Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}</td>
    <td>
        <a href="" class="btn btn-primary">PAY</a>
        <button class="btn btn-danger">✖</button>
        <button class="btn btn-warning">📋</button>
        <button class="btn btn-success">📲</button>
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
