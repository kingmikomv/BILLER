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
        <div class="content-wrapper">
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
                                    <h3 class="card-title">Data Pelanggan</h3>
                                    <div class="card-tools">
                                        <a class="btn btn-primary" href="{{ route('formulir') }}">
                                            <i class="fas fa-plus"></i> Tambah Pelanggan
                                        </a>
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body table-responsive">
                                    <table class="table table-striped table-bordered table-hover">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Status Koneksi</th>
                                                <th>Nama Pelanggan</th>
                                                <th>Paket</th>
                                                <th>Akun</th>
                                                <th>Informasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($plg as $index => $pelanggan)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        @if ($pelanggan->status === 'Online')
                                                            <span class="badge badge-success">Online</span>
                                                        @else
                                                            <span class="badge badge-danger">Offline</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $pelanggan->nama_pelanggan }}</td>
                                                    <td>{{ $pelanggan->paket->nama_paket ?? 'Tidak Ada Paket' }}</td>
                                                    <td>{{ $pelanggan->akun_pppoe }}</td>
                                                    <td>
                                                        <!-- Tombol untuk membuka modal -->
                                                        <a href="{{ route('pelanggan.show', $pelanggan->id) }}" 
                                                           class="btn btn-primary btn-sm" 
                                                           data-toggle="tooltip" 
                                                           data-placement="top" 
                                                           title="Lihat Informasi">
                                                           <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
        <x-dhs.alert />

    </div>
    <!-- ./wrapper -->

    <x-dhs.scripts />
</body>

</html>
