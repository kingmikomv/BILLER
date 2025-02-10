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
                                    <h3 class="card-title">Pekerja</h3>

                                    <div class="card-tools">
                                        <!-- Tombol untuk membuka modal -->
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                            data-target="#modalTambahPekerja">
                                            <i class="fas fa-plus"></i> Tambah Pekerja
                                        </button>


                                    </div>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body p-0">
                                    <div class="d-md-flex">
                                        <div class="p-1 flex-fill" style="overflow: hidden">
                                            <!-- Map will be created here -->
                                            <div id="world-map-markers" style="height: 325px; overflow: hidden">

                                                data
                                            </div>
                                        </div>

                                    </div><!-- /.d-md-flex -->
                                </div>
                                <!-- /.card-body -->
                            </div>






                        </div>


                    </div>


                </div>

            </section>
            <!-- /.content -->
        </div>
        <!-- Modal Tambah Pekerja -->
        <div class="modal fade" id="modalTambahPekerja" tabindex="-1" role="dialog"
            aria-labelledby="modalTambahPekerjaLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahPekerjaLabel">Tambah Pekerja</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="formTambahPekerja" method="POST" action="{{route('addPekerja')}}">
                        @csrf
                        <div class="modal-body">

                            <div class="form-group">
                                <label for="namaPekerja">Nama Pekerja</label>
                                <input type="text" class="form-control" id="namaPekerja" name="namaPekerja"
                                    placeholder="Masukkan nama pekerja" required>
                            </div>
                            <div class="form-group">
                                <label for="emailPekerja">Email Login</label>
                                <input type="email" class="form-control" id="emailPekerja" name="emailPekerja"
                                    placeholder="Masukkan email pekerja" required>
                            </div>
                            <div class="form-group">
                                <label for="emailPekerja">Password Login</label>
                                <input type="text" class="form-control" id="emailPekerja" name="passwordPekerja"
                                    placeholder="Masukkan password pekerja" required>
                            </div>
                            <div class="form-group">
                                <label for="posisiPekerja">Posisi</label>
                                <select name="posisiPekerja" class="form-control" id="">
                                    <option disabled selected value>Pilih Posisi</option>
                                    <option value="teknisi">Teknisi</option>
                                    <option value="cs">Customer Service</option>
                                    <option value="penagih">Penagih</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="noTeleponPekerja">Nomor Telepon</label>
                                <input type="text" class="form-control" id="noTeleponPekerja" name="noTeleponPekerja"
                                    placeholder="Masukkan nomor telepon pekerja" required>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary" form="formTambahPekerja">Simpan</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
        <x-dhs.footer />
    </div>
    <!-- ./wrapper -->

    <x-dhs.scripts />

</body>

</html>
