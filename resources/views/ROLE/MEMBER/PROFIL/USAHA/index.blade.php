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

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-3">

                            <!-- Profile Image -->
                            <div class="card card-primary card-outline">
                                <div class="card-body box-profile">
                                <div class="text-center">
    @if(isset($usaha->logo_usaha))
        <img class="profile-user-img img-fluid img-circle"
            src="{{ asset('usaha_logos/' . $usaha->logo_usaha) }}" 
            alt="Logo Usaha" style="width: 128px; height: 128px; object-fit: cover;">
    @else
        <!-- Gambar Default Jika Tidak Ada Logo -->
        <img class="profile-user-img img-fluid img-circle"
            src="{{ asset('dist/img/default-profile.png') }}" 
            alt="Default Profile" style="width: 128px; height: 128px; object-fit: cover;">
    @endif
</div>


                                    <h3 class="profile-username text-center">{{$usaha->nama_usaha ?? '-'}}</h3>

                                    <ul class="list-group list-group-unbordered mb-3">
                                        <li class="list-group-item">
                                            <b>Router</b> <a class="float-right">1,322</a>
                                        </li>
                                        <li class="list-group-item">
                                            <b>Pelanggan</b> <a class="float-right">543</a>
                                        </li>

                                    </ul>

                                </div>
                                <!-- /.card-body -->
                            </div>
                            <!-- /.card -->

                            <!-- About Me Box -->
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">About Me</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">

                                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Nomor Telepon</strong>

                                    <p class="text-muted">{{$usaha->telepon_usaha ?? '-'}}</p>

                                    <hr>
                                    <strong><i class="fas fa-map-marker-alt mr-1"></i> Location</strong>

                                    <p class="text-muted">{{$usaha->alamat_usaha ?? '-'}}</p>

                                    <hr>

                                    <strong><i class="far fa-file-alt mr-1"></i> Deskripsi</strong>

                                    <p class="text-muted">{{$usaha->deskripsi_usaha ?? '-'}}</p>
                                </div>
                                <!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>
                        <!-- /.col -->
                        <div class="col-md-9">
                            <div class="card">
                                <div class="card-header p-2">
                                    <ul class="nav nav-pills">
                                        <li class="nav-item"><a class="nav-link active" href="#activity"
                                                data-toggle="tab">Activity</a></li>
                                        <li class="nav-item"><a class="nav-link" href="#timeline"
                                                data-toggle="tab">Timeline</a></li>
                                        <li class="nav-item"><a class="nav-link" href="#settings"
                                                data-toggle="tab">Settings</a></li>
                                    </ul>
                                </div><!-- /.card-header -->


                                <div class="card-body">
                                    <div class="tab-content">


                                        <div class="active tab-pane" id="activity">
                                        <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Waktu</th>
                <th>Username</th>
                <th>Role</th>
                <th>Aktivitas</th>
                <th>Target</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $key => $log)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $log->created_at }}</td>
                <td>{{ $log->user->name }}</td>
                <td>{{ $log->role }}</td>
                <td>{{ $log->activity }}</td>
                <td>{{ $log->target ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
                                        </div>
                                        <!-- /.tab-pane -->














                                        <div class="tab-pane" id="timeline">
                                            <!-- The timeline -->
                                            <div class="timeline timeline-inverse">
                                                <!-- timeline time label -->
                                                <div class="time-label">
                                                    <span class="bg-danger">
                                                        10 Feb. 2014
                                                    </span>
                                                </div>
                                                <!-- /.timeline-label -->
                                                <!-- timeline item -->
                                                <div>
                                                    <i class="fas fa-envelope bg-primary"></i>

                                                    <div class="timeline-item">
                                                        <span class="time"><i class="far fa-clock"></i> 12:05</span>

                                                        <h3 class="timeline-header"><a href="#">Support Team</a> sent
                                                            you an email</h3>

                                                        <div class="timeline-body">
                                                            Etsy doostang zoodles disqus groupon greplin oooj voxy
                                                            zoodles,
                                                            weebly ning heekya handango imeem plugg dopplr jibjab,
                                                            movity
                                                            jajah plickers sifteo edmodo ifttt zimbra. Babblely odeo
                                                            kaboodle
                                                            quora plaxo ideeli hulu weebly balihoo...
                                                        </div>
                                                        <div class="timeline-footer">
                                                            <a href="#" class="btn btn-primary btn-sm">Read more</a>
                                                            <a href="#" class="btn btn-danger btn-sm">Delete</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- END timeline item -->
                                                <!-- timeline item -->
                                                <div>
                                                    <i class="fas fa-user bg-info"></i>

                                                    <div class="timeline-item">
                                                        <span class="time"><i class="far fa-clock"></i> 5 mins
                                                            ago</span>

                                                        <h3 class="timeline-header border-0"><a href="#">Sarah Young</a>
                                                            accepted your friend request
                                                        </h3>
                                                    </div>
                                                </div>
                                                <!-- END timeline item -->
                                                <!-- timeline item -->
                                                <div>
                                                    <i class="fas fa-comments bg-warning"></i>

                                                    <div class="timeline-item">
                                                        <span class="time"><i class="far fa-clock"></i> 27 mins
                                                            ago</span>

                                                        <h3 class="timeline-header"><a href="#">Jay White</a> commented
                                                            on your post</h3>

                                                        <div class="timeline-body">
                                                            Take me to your leader!
                                                            Switzerland is small and neutral!
                                                            We are more like Germany, ambitious and misunderstood!
                                                        </div>
                                                        <div class="timeline-footer">
                                                            <a href="#" class="btn btn-warning btn-flat btn-sm">View
                                                                comment</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- END timeline item -->
                                                <!-- timeline time label -->
                                                <div class="time-label">
                                                    <span class="bg-success">
                                                        3 Jan. 2014
                                                    </span>
                                                </div>
                                                <!-- /.timeline-label -->
                                                <!-- timeline item -->
                                                <div>
                                                    <i class="fas fa-camera bg-purple"></i>

                                                    <div class="timeline-item">
                                                        <span class="time"><i class="far fa-clock"></i> 2 days
                                                            ago</span>

                                                        <h3 class="timeline-header"><a href="#">Mina Lee</a> uploaded
                                                            new photos</h3>

                                                        <div class="timeline-body">
                                                            <!-- <img src="https://placehold.it/150x100" alt="...">
                                                            <img src="https://placehold.it/150x100" alt="...">
                                                            <img src="https://placehold.it/150x100" alt="...">
                                                            <img src="https://placehold.it/150x100" alt="..."> -->
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- END timeline item -->
                                                <div>
                                                    <i class="far fa-clock bg-gray"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- /.tab-pane -->












                                        <div class="tab-pane" id="settings">
                                        <form class="form-horizontal"
    action="{{ route('profil.usaha.storeOrUpdate') }}" 
    method="POST"
    enctype="multipart/form-data">
    @csrf


                                                <div class="form-group row">
                                                    <label for="logo_usaha" class="col-sm-2 col-form-label">Logo
                                                        Usaha</label>
                                                    <div class="col-sm-10">
                                                        <input type="file" class="form-control-file" name="logo_usaha">
                                                        @if(isset($usaha->logo_usaha))
                                                        <br>
                                                        <img src="{{ asset('usaha_logos/' . $usaha->logo_usaha) }}"
                                                            alt="Logo Usaha" width="100">
                                                        @endif

                                                        
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="nama_usaha" class="col-sm-2 col-form-label">Nama
                                                        Usaha</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" class="form-control" name="nama_usaha"
                                                            placeholder="Nama Usaha"
                                                            value="{{ old('nama_usaha', $usaha->nama_usaha ?? '') }}"
                                                            required>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="telepon" class="col-sm-2 col-form-label">Telepon
                                                        Usaha</label>
                                                    <div class="col-sm-10">
                                                        <input type="number" class="form-control" name="telepon_usaha"
                                                            placeholder="Telepon Usaha"
                                                            value="{{ old('telepon_usaha', $usaha->telepon_usaha ?? '') }}">
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="alamat" class="col-sm-2 col-form-label">Alamat
                                                        Usaha</label>
                                                    <div class="col-sm-10">
                                                        <textarea class="form-control" name="alamat_usaha"
                                                            placeholder="Alamat Usaha">{{ old('alamat_usaha', $usaha->alamat_usaha ?? '') }}</textarea>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <label for="deskripsi" class="col-sm-2 col-form-label">Deskripsi
                                                        Usaha</label>
                                                    <div class="col-sm-10">
                                                        <textarea class="form-control" name="deskripsi_usaha"
                                                            placeholder="Deskripsi Usaha">{{ old('deskripsi_usaha', $usaha->deskripsi_usaha ?? '') }}</textarea>
                                                    </div>
                                                </div>

                                                <div class="form-group row">
                                                    <div class="offset-sm-2 col-sm-10">
                                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                        <!-- /.tab-pane -->
                                    </div>
                                    <!-- /.tab-content -->
                                </div><!-- /.card-body -->
                            </div>
                            <!-- /.card -->
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div><!-- /.container-fluid -->
            </section>


        </div>


        <x-dhs.footer />
    </div>
    <!-- ./wrapper -->

    <x-dhs.scripts />

</body>

</html>
