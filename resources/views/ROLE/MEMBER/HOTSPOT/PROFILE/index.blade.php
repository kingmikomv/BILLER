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
                                    <h3 class="card-title">Profile Hotspot</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body table-responsive">
                                    <!-- Tombol New Profile -->
                                    <button class="btn btn-primary mb-3" data-toggle="modal"
                                        data-target="#newProfileModal">
                                        + New Profile
                                    </button>

                                    <!-- Tempatkan di sini nanti tabel data profile -->

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
        <!-- Modal -->
        <div class="modal fade" id="newProfileModal" tabindex="-1" role="dialog"
            aria-labelledby="newProfileModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <form method="POST" action="{{ route('hotspot.uploadProfile') }}">
                    @csrf
                    <div class="modal-content bg-dark text-white">
                        <div class="modal-header">
                            <h5 class="modal-title" id="newProfileModalLabel">New Hotspot Profile</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">

                                <!-- Left Column -->
                                <div class="col-md-12 border-end pe-md-4">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nama Profile</label>
                                       <input type="text" name="name" id="name" class="form-control" required>

                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="price" class="form-label">Harga (Rp)</label>
                                            <input type="number" name="price" id="price" class="form-control"
                                                required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="reseller_price" class="form-label">Harga Reseller</label>
                                            <input type="number" name="reseller_price" id="reseller_price"
                                                class="form-control">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="rate_up" class="form-label">Upload Rate Limit (Mbps)</label>
                                            <input type="number" name="rate_up" id="rate_up" class="form-control"
                                                required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="rate_down" class="form-label">Download Rate Limit (Mbps)</label>
                                            <input type="number" name="rate_down" id="rate_down" class="form-control"
                                                required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="uptime" class="form-label">Uptime (Jam)</label>
                                            <input type="number" name="uptime" id="uptime" class="form-control"
                                                placeholder="contoh: 12" min="0">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="validity" class="form-label">Validity (Hari)</label>
                                            <input type="number" name="validity" id="validity" class="form-control"
                                                placeholder="contoh: 1" min="0">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="groupname" class="form-label">Group Name (untuk
                                            radusergroup)</label>
                                        <input type="text" name="groupname" id="groupname" class="form-control"
                                            required>
                                    </div>


                                </div>



                            </div>
                        </div>

                        <div class="modal-footer border-top">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">Simpan Profile</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <x-dhs.footer />
    </div>
    <!-- ./wrapper -->

    <x-dhs.scripts />

</body>

</html>
