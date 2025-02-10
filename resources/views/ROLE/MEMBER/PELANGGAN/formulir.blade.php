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
                                    <h3 class="card-title">Formulir Penambahan Pelanggan</h3>

                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <form method="POST" action="{{route('addPelanggan')}}">
                                        @csrf
                                        <div class="form-group">
                                            <label for="pilihPaket">Pilih Paket</label>
                                            <select class="form-control" name="kodePaket" required>
                                                <option value="">Pilih Paket</option>
                                                @foreach($paketPPPoEs as $paket)
                                                    <option value="{{ $paket->kode_paket }}">
                                                        {{ $paket->nama_paket }} - Rp. {{ number_format($paket->harga_paket, 0, ',', '.') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="namaPelanggan">Nama Pelanggan</label>
                                            <input type="text" class="form-control" id="namaPelanggan" name="namaPelanggan" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="namaPelanggan">Akun PPPoE</label>
                                            <input type="text" class="form-control" id="akunPppoe" name="akunPppoe" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="namaPelanggan">Password PPPoE</label>
                                            <input type="text" class="form-control" id="passwordPppoe" name="passwordPppoe" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="alamat">Alamat</label>
                                            <textarea class="form-control" id="alamat" name="alamat" required></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="telepon">Nomor Telepon</label>
                                            <input type="text" class="form-control" id="telepon" name="telepon" required>
                                        </div>
                                        
                                        
                                        <button type="submit" class="btn btn-primary">Tambah Pelanggan</button>
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

</body>

</html>
