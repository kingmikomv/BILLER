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
        <div class="content-wrapper">
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
                                    <h3 class="card-title">PPPoE Paket</h3>

                                    <div class="card-tools">
                                        <button type="button" class="btn btn-primary" data-toggle="modal"
                                            data-target="#tambahRouterModal">
                                            <i class="fas fa-plus"></i> Tambah Paket
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body p-0">
                                    <div class="d-md-flex">
                                        <div class="p-1 flex-fill" style="overflow: hidden">
                                            <!-- Tabel Responsif -->
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Nama Paket</th>
                                                            <th>Harga Paket</th>
                                                            <th>Site</th>
                                                            <th>Profil</th>
                                                            <th>Username</th>
                                                            <th>Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Contoh data tabel -->
                                                        @php $no = 1; @endphp
                                                        @foreach($paket as $pkt)
                                                        <tr>
                                                            <td>{{$no++}}</td>
                                                            <td>{{$pkt->nama_paket}}</td>
                                                            <td>Rp. {{ number_format($pkt->harga_paket, 0, ',', '.') }}</td>
                                                                                                                                                                    <td>{{$pkt->site}}</td>
                                                            <td>{{$pkt->profile}}</td>
                                                            <td>{{$pkt->username}}</td>
                                                            <td>
                                                                <button class="btn btn-primary">Edit</button>
                                                                <button class="btn btn-danger">Hapus</button>
                                                            </td>
                                                        </tr>
                                                       @endforeach
                                                        <!-- Tambahkan baris data lainnya di sini -->
                                                    </tbody>
                                                </table>
                                            </div>
                                            <!-- /Tabel Responsif -->
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
      
        <div class="modal fade" id="tambahRouterModal" tabindex="-1" aria-labelledby="tambahRouterModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tambahRouterModalLabel">Tambah Profil PPPoE</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{route('tambahpaket')}}" method="post">
                        @csrf
                        <div class="modal-body">
                            <!-- Pilihan MikroTik -->
                            <div class="form-group">
                                <label for="username">Pilih MikroTik ( Aktif )</label>
                                <select class="form-control" id="username" name="username" required>
                                    <option value="">Pilih MikroTik</option>
                                    @foreach($onlineRouters as $mikrotiks)
                                    <option value="{{ $mikrotiks->username }}">
                                        {{ $mikrotiks->site }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Profil MikroTik -->
                            <div class="form-group">
                                <label for="profile">Pilih Profil</label>
                                <select class="form-control" id="profile" name="profile" required>
                                    <option value="">Pilih Profil</option>
                                </select>
                                <div id="loadingIndicator" style="display:none;">Memuat profil MikroTik...</div>
                            </div>
                            <!-- Nama Paket -->
                            <div class="form-group">
                                <label for="namaPaket">Nama Paket</label>
                                <input type="text" class="form-control" id="namaPaket" name="namaPaket" required>
                            </div>
                            <!-- Harga Paket -->
                            <div class="form-group">
                                <label for="hargaPaket">Harga Paket</label>
                                <input type="text" class="form-control" id="hargaPaket" name="hargaPaket" maxlength="7" pattern="\d*" required oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 7)">
                            </div>
                            
                            
                            
                            
                            
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Profil</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>




        <x-dhs.footer />
    </div>
    <!-- ./wrapper -->

    <x-dhs.scripts />
    <x-dhs.alert />
    <script>
        $(document).ready(function () {
            $('#username').on('change', function () {
                const username = $(this).val();
                const profileDropdown = $('#profile');
                const loadingIndicator = $('#loadingIndicator');
                profileDropdown.empty();
                profileDropdown.append('<option value="">Pilih Profil</option>');
                loadingIndicator.show(); // Menampilkan loading
    
                if (username) {
                    $.ajax({
                        url: '{{route("getMikrotikProfiles")}}', // Endpoint untuk mendapatkan profil MikroTik
                        type: 'GET',
                        data: {
                            username: username
                        },
                        success: function (data) {
                            loadingIndicator.hide(); // Sembunyikan loading segera setelah data diterima
    
                            if (data.status === 'success' && data.profiles && data.profiles.length > 0) {
                                data.profiles.forEach(profile => {
                                    profileDropdown.append(
                                        `<option value="${profile.name}">${profile.name}</option>`
                                    );
                                });
                            } 
                        },
                        error: function () {
                            loadingIndicator.hide(); // Sembunyikan loading jika terjadi error
                            profileDropdown.append(
                                '<option value="">Gagal memuat data profil</option>'
                            );
                        }
                    });
                } else {
                    loadingIndicator.hide(); // Sembunyikan loading jika MikroTik tidak dipilih
                }
            });
        });
    </script>
    
    

</body>

</html>
