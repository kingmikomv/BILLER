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
                                    Pengaturan Billing
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('setting-billing.store') }}" method="POST">
                                        @csrf
                                    
                                        <!-- Prorata -->
                                        <div class="form-group">
                                            <label>
                                                <input type="checkbox" name="prorata_enable" value="1" {{ old('prorata_enable', true) ? 'checked' : '' }}>
                                                Aktifkan Prorata
                                            </label>
                                        </div>
                                    
                                        <!-- Mode Generate Invoice -->
                                        <div class="form-group">
                                            <label>Mode Generate Invoice</label>
                                            <select name="generate_invoice_mode" class="form-control" required>
                                                <option value="tanggal_pembayaran" {{ old('generate_invoice_mode') == 'tanggal_pembayaran' ? 'selected' : '' }}>Sesuai Tanggal Pembayaran</option>
                                                <option value="dimajukan" {{ old('generate_invoice_mode') == 'dimajukan' ? 'selected' : '' }}>Dimajukan</option>
                                            </select>
                                        </div>
                                    
                                        <!-- Berapa Hari Dimajukan -->
                                        <div class="form-group">
                                            <label>Dimajukan Berapa Hari (opsional)</label>
                                            <input type="number" name="dimajukan_hari" class="form-control" placeholder="Contoh: 5" value="{{ old('dimajukan_hari') }}">
                                        </div>
                                    
                                        <!-- Default Jatuh Tempo -->
                                        <div class="form-group">
                                            <label>Default Jatuh Tempo (Hari)</label>
                                            <input type="number" name="default_jatuh_tempo_hari" class="form-control" value="{{ old('default_jatuh_tempo_hari', 7) }}" required>
                                        </div>
                                    
                                        <!-- Submit -->
                                        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                                    </form>
                                    
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

   

</body>

</html>
