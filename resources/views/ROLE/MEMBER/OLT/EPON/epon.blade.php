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
                                    <h3 class="card-title">Data OLT EPON</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahOLT">
                                            <i class="fas fa-plus"></i> Tambah OLT EPON
                                        </button>
                                        
                                    </div>
                                </div>

                                <!-- /.card-header -->
                                <div class="card-body table-responsive">
                                   
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
        <div class="modal fade" id="modalTambahOLT" tabindex="-1" role="dialog" aria-labelledby="modalTambahOLTLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahOLTLabel">Tambah OLT EPON</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formTambahOLT" method="POST" action="{{ route('tambah.olt.epon') }}">
                            @csrf                            
                            <div class="form-group">
                                <label for="namaOLT">Nama OLT</label>
                                <input type="text" class="form-control" id="namaOLT" name="namaOLT" placeholder="Nama OLT / Site OLT" required>
                            </div>
                            <div class="form-group">
                                <label for="ipOLT">IP OLT</label>
                                <input type="text" class="form-control" id="ipOLT" name="ipOLT" placeholder="IP Local OLT" required>
                            </div>
                            <div class="form-group">
                                <label for="ipOLT">Port OLT</label>
                                <input type="text" class="form-control" id="portOlt" name="portOLT" placeholder="Port OLT" required>
                            </div>
                            <div class="form-group">
                                <label for="siteMikrotik">Site Mikrotik</label>
                                <select name="site" class="form-control">
                                    <option disable selected value>Pilih Mikrotik</option>
                                    @foreach($dataSite as $mikrotik)
                                    <option value="{{$mikrotik->remote_ip}}">{{$mikrotik->site}}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" form="formTambahOLT">Simpan</button>
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
