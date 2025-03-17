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
                    <!-- Info boxes -->

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">

                                <div class="card-header">
                                    <h3 class="card-title">Data Paket PPPoE</h3>
                                    <div class="card-tools">
                                        
                                        <a href="{{route('addPppoe')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Paket</a>
                                    </div>
                                </div>

                                <!-- /.card-header -->

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="pppoeTable" class="table">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Paket ID</th>
                                                    <th>Nama Paket</th>
                                                    <th>Harga Paket</th>
                                                    <th>Site</th>
                                                    <th>Profil</th>
                                                    <th hidden>Username</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $no = 1; @endphp
                                                @foreach($onlinePackages as $pkt)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $pkt->kode_paket }}</td>
                                                    <td>{{ $pkt->nama_paket }}</td>
                                                    <td>Rp. {{ number_format($pkt->harga_paket, 0, ',', '.') }}</td>
                                                    <td>{{ $pkt->site }}</td>
                                                    <td>{{ $pkt->profile }}</td>
                                                    <td hidden>{{ $pkt->username }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <button class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- /.card-body -->
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
    <x-dhs.alert />
   
    <script>
        $(document).ready(function () {
            $('#pppoeTable').DataTable({
                responsive: true,
                autoWidth: false,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/Indonesian.json"
                }
            });
        });

    </script>



</body>

</html>
