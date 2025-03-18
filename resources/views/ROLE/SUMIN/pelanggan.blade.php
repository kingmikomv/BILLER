<x-dhs.head />

<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">

        <x-dhs.preload />

        <!-- Navbar -->
        <x-dhs.sumin.nav />
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <x-dhs.sidebar />

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper" style="margin-bottom: 50px">
            <!-- Content Header (Page header) -->
            <x-dhs.sumin.content-header-sumin />
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Info boxes -->

                    <div class="row">

                        <div class="col-md-12">

                            <div class="card">
                                <div class="card-header border-transparent">
                                    <h3 class="card-title">Data Pelanggan Member</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Nama User</th>
                                                <th>Total MikroTik</th>
                                                <th>Total Pelanggan</th>
                                                <th>Opsi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($dataMikrotik as $index => $user)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->mikrotik->count() }} MikroTik</td>
                                                <td>{{ $user->mikrotik->sum('pelanggan_count') }} Pelanggan</td>
                                                <!-- Total semua pelanggan -->
                                                <td>
                                                    <button class="btn btn-primary btn-sm" data-toggle="modal"
                                                        data-target="#detailModal{{ $user->id }}">
                                                        Selengkapnya
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Modal Detail MikroTik -->
                                            <div class="modal fade" id="detailModal{{ $user->id }}" tabindex="-1"
                                                role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Detail MikroTik - {{ $user->name }}
                                                            </h5>
                                                            <button type="button" class="close"
                                                                data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <ul>
                                                                @foreach ($user->mikrotik as $mikrotik)
                                                                <li>{{ $mikrotik->site }} -
                                                                    {{ $mikrotik->pelanggan_count }} Pelanggan</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </tbody>
                                    </table>


                                </div>
                                <!-- /.card-body -->

                                <!-- /.card-footer -->
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

</body>

</html>
