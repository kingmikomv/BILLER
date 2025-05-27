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
                                <div class="card-header border-transparent">
                                    <h3 class="card-title">VPN RADIUS</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body table-responsive">
                                    <!-- Tombol Tambah Akun VPN -->
                                    <button type="button" class="btn btn-primary mb-3" data-toggle="modal"
                                        data-target="#modalTambahVPN">
                                        Tambah Akun VPN
                                    </button>

                                    <!-- Tabel Data Akun VPN -->
                                    <table class="table table-bordered table-hover">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>NO</th>
                                                <th>Username</th>
                                                <th>Password</th>
                                                <th>IP Address</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $no = 1; @endphp
                                            @forelse($vpnUsers as $user)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $user->username }}</td>
                                                    <td>{{ $user->password }}</td>
                                                    <td>{{ $user->remote_address }}</td>
                                                    <td>
                                                        <form action="{{ route('radius.hapusVpnRadius', $user->id) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Yakin ingin menghapus user {{ $user->username }}?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-danger btn-sm">Hapus</button>
                                                        </form>
                                                    </td>

                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">Belum ada data VPN</td>
                                                </tr>
                                            @endforelse
                                        </tbody>

                                    </table>

                                    <!-- Modal Tambah Akun VPN -->
                                    <div class="modal fade" id="modalTambahVPN" tabindex="-1" role="dialog"
                                        aria-labelledby="modalTambahVPNLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <form method="POST" action="{{ route('radius.tambahVpnRadius') }}">
                                                @csrf
                                                <!-- Ganti action sesuai kebutuhan -->
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Tambah Akun VPN</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Tutup">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <!-- CSRF jika pakai Laravel -->

                                                        <div class="form-group">
                                                            <label for="username">Username</label>
                                                            <input type="text" name="username" class="form-control"
                                                                required>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="password">Password</label>
                                                            <input type="text" name="password" class="form-control"
                                                                required>
                                                        </div>


                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Batal</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                </div>
                                <!-- /.card-body -->

                                <!-- /.card-footer -->
                            </div>


                        </div>

                        <div class="col-md-12">

                            <div class="card">
                                <div class="card-header border-transparent">
                                    <h3 class="card-title">RADIUS</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body table-responsive">

                                    <!-- Tombol Tambah NAS -->
                                    <button type="button" class="btn btn-success mb-3" data-toggle="modal"
                                        data-target="#modalTambahNAS">
                                        Tambah NAS RADIUS
                                    </button>

                                    <!-- Tabel NAS -->
                                    <table class="table table-bordered table-hover">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>NO</th>
                                                <th>NAS Name (IP)</th>
                                                <th>Short Name</th>
                                                <th>Type</th>
                                                <th>Secret</th>
                                                <th>Deskripsi</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $no = 1; @endphp
                                            @forelse($nasList as $nas)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $nas->nasname }}</td>
                                                    <td>{{ $nas->shortname }}</td>
                                                    <td>{{ $nas->type }}</td>
                                                    <td>{{ $nas->secret }}</td>
                                                    <td>{{ $nas->description }}</td>
                                                   <td>
    <form action="{{ route('radius.hapusNasRadius', $nas->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus NAS {{ $nas->nasname }}?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
    </form>
</td>

                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center">Belum ada NAS terdaftar</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>

                                    <!-- Modal Tambah NAS -->
                                    <div class="modal fade" id="modalTambahNAS" tabindex="-1" role="dialog"
                                        aria-labelledby="modalTambahNASLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <form method="POST" action="{{ route('radius.tambahNasRadius') }}">
                                                @csrf
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Tambah NAS RADIUS</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Tutup">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <!-- Field NAS -->
                                                        <div class="form-group">
                                                            <label for="nasname">NAS Name (IP)</label>
                                                            <input type="text" name="nasname" class="form-control"
                                                                required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="shortname">Short Name</label>
                                                            <input type="text" name="shortname"
                                                                class="form-control">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="type">Type</label>
                                                            <input type="text" name="type" class="form-control"
                                                                value="other">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="secret">Secret</label>
                                                            <input type="text" name="secret" class="form-control"
                                                                value="secret">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="description">Deskripsi</label>
                                                            <textarea name="description" class="form-control"></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Batal</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

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
        <x-dhs.alert />
    </div>
    <!-- ./wrapper -->

    <x-dhs.scripts />

</body>

</html>
