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
                    <x-dhs.info-box />

                    <div class="row">

                        <div class="col-md-12">



                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Tambah Data PSB</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body ">
                                    <form action="{{ route('upload_psb_sales') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                           
                                            <div class="col-md-12 mt-2">
                                                <label for="nama_psb">Nama Lengkap</label>
                                                <input type="text" name="nama_psb" class="form-control" placeholder="Nama Lengkap" required>
                                            </div>
                                    
                                            <div class="col-md-12 mt-2">
                                                <label for="alamat_psb">Alamat</label>
                                                <textarea name="alamat_psb" class="form-control" rows="3" placeholder="Alamat lengkap" required></textarea>
                                            </div>
                                    
                                            <div class="col-md-6 mt-2">
                                                <label for="foto_lokasi_psb">Foto Lokasi</label>
                                                <input type="file" name="foto_lokasi_psb" class="form-control-file" accept="image/*" required>
                                            </div>
                                    
                                            <div class="col-md-6 mt-2">
                                                <label for="paket_psb">Paket PSB</label>
                                                <input type="text" name="paket_psb" class="form-control" placeholder="Nama Paket" required>
                                            </div>
                                    
                                            <div class="col-md-6 mt-2">
                                                <label for="tanggal_ingin_pasang">Tanggal Ingin Pasang</label>
                                                <input type="date" name="tanggal_ingin_pasang" class="form-control" required>
                                            </div>
                                    
                                            <div class="col-md-6 mt-2">
                                                <label for="telepon">No. Telepon</label>
                                                <input type="text" name="telepon" class="form-control" placeholder="No HP" required>
                                            </div>
                                    
                                            <div class="col-md-12 mt-2">
                                                <button type="submit" class="btn btn-primary btn-block">Simpan</button>
                                            </div>
                                        </div>
                                    </form>
                                    
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

</body>

</html>
