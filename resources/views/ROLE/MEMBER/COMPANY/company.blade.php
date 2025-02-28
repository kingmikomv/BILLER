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
                                    <h3 class="card-title">Profil Perusahaan</h3>

                                    <div class="card-tools">

                                    </div>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">

                                    <form method="POST" action="{{ route('updateCompany', $profil->id ?? 0) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Nama Perusahaan / PT 🏢</label>
                                                    <input type="text" name="nama_perusahaan" class="form-control"
                                                        value="{{ $profil->nama_perusahaan ?? '' }}" placeholder="Masukkan nama perusahaan" required>
                                                </div>
                                            </div>
                                        
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Brand 🔖</label>
                                                    <input type="text" name="brand" class="form-control"
                                                        value="{{ $profil->brand ?? '' }}" placeholder="Masukkan brand perusahaan">
                                                </div>
                                            </div>
                                        
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Nomor Izin ISP 📜</label>
                                                    <input type="text" name="nomor_izin_isp" class="form-control"
                                                        value="{{ $profil->nomor_izin_isp ?? '' }}" placeholder="Masukkan nomor izin ISP">
                                                </div>
                                            </div>
                                        
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Nomor Izin JARTAPLOK / JARTAUP 📜</label>
                                                    <input type="text" name="nomor_izin_jartaplok" class="form-control"
                                                        value="{{ $profil->nomor_izin_jartaplok ?? '' }}" placeholder="Masukkan nomor izin JARTAPLOK / JARTAUP">
                                                </div>
                                            </div>
                                        
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>NPWP Perusahaan 🧾</label>
                                                    <input type="text" name="npwp" class="form-control"
                                                        value="{{ $profil->npwp ?? '' }}" placeholder="Masukkan NPWP perusahaan">
                                                </div>
                                            </div>
                                        
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Alamat Perusahaan 📍</label>
                                                    <textarea name="alamat" class="form-control" placeholder="Masukkan alamat perusahaan">{{ $profil->alamat ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Website Perusahaan 🌐</label>
                                                    <input type="text" name="website" class="form-control"
                                                        value="{{ $profil->website ?? '' }}" placeholder="Masukkan URL website">
                                                </div>
                                            </div>
                                        
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Email Perusahaan 📧</label>
                                                    <input type="email" name="email_perusahaan" class="form-control"
                                                        value="{{ $profil->email_perusahaan ?? '' }}" placeholder="Masukkan email perusahaan" required>
                                                </div>
                                            </div>
                                        
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Nomor Telepon Perusahaan ☎️</label>
                                                    <input type="text" name="nomor_telepon" class="form-control"
                                                        value="{{ $profil->nomor_telepon ?? '' }}" placeholder="Masukkan nomor telepon perusahaan">
                                                </div>
                                            </div>
                                        
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Nomor WhatsApp (Jika berbeda dengan telepon perusahaan) 📱</label>
                                                    <input type="text" name="nomor_whatsapp" class="form-control"
                                                        value="{{ $profil->nomor_whatsapp ?? '' }}" placeholder="Masukkan nomor WhatsApp">
                                                </div>
                                            </div>
                                        
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Nama Owner 👤</label>
                                                    <input type="text" name="nama_owner" class="form-control"
                                                        value="{{ $profil->nama_owner ?? '' }}" placeholder="Masukkan nama owner">
                                                </div>
                                            </div>
                                        
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Nama Finance 💰</label>
                                                    <input type="text" name="nama_finance" class="form-control"
                                                        value="{{ $profil->nama_finance ?? '' }}" placeholder="Masukkan nama finance">
                                                </div>
                                            </div>
                                        
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Nomor Telepon / HP Finance 📞</label>
                                                    <input type="text" name="nomor_telepon_finance" class="form-control"
                                                        value="{{ $profil->nomor_telepon_finance ?? '' }}" placeholder="Masukkan nomor telepon finance">
                                                </div>
                                            </div>
                                        
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-block btn-primary">Simpan</button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                        
                                       

                                    </form>

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

    <x-dhs.footer />
    </div>
    <!-- ./wrapper -->

    <x-dhs.scripts />
    <x-dhs.alert />
</body>

</html>
