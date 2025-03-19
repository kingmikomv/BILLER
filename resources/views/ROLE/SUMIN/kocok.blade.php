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
                                    <h3 class="card-title">Pilih Server</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body table-responsive">
                                    <form action="{{ route('undian.spinner') }}" method="get">
                                        <div class="form-group">
                                            <label for="server">Server</label>
                                            <select class="form-control" name="server" id="server" required>
                                                <option value="">-- Pilih Server --</option>
                                                @foreach($mikrotik as $server)
                                                    <option value="{{ $server->mikrotik->id }}">{{ $server->mikrotik->site }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Pilih</button>
                                    </form>
                                    
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
