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
                                    <h3 class="card-title">Data Paket PPPoE</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                            data-target="#tambahRouterModal">
                                            <i class="fas fa-plus"></i> Tambah Paket
                                        </button>
                                    </div>
                                </div>

                                <!-- /.card-header -->

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="pppoeTable" class="table">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nama Paket</th>
                                                    <th>Harga Paket</th>
                                                    <th>Site</th>
                                                    <th>Profil</th>
                                                    <th hidden>Username</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $no = 1; @endphp
                                                @foreach($paket as $pkt)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $pkt->nama_paket }}</td>
                                                    <td>Rp. {{ number_format($pkt->harga_paket, 0, ',', '.') }}</td>
                                                    <td>{{ $pkt->site }}</td>
                                                    <td>{{ $pkt->profile }}</td>
                                                    <td hidden>{{ $pkt->username }}</td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <button class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- /.card-body -->
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- /.content -->
        </div>

        <!-- Modal Tambah Paket -->
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
                    <form action="{{ route('tambahpaket') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <!-- Pilihan MikroTik -->
                            <div class="form-group">
                                <label for="username">Pilih MikroTik ( Aktif )</label>
                                <select class="form-control" id="username" name="username" required>
                                    <option value="">Pilih MikroTik</option>
                                    @foreach($onlineRouters as $mikrotiks)
                                    <option value="{{ $mikrotiks->username }}" data-site="{{ $mikrotiks->site }}">
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
                                <label for="packageName">Nama Paket</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="packageName" name="namaPaket" placeholder="Nama Paket"
                                        aria-label="Nama Paket" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="selectedMikrotik"></span>
                                    </div>
                                </div>
                            </div>
                    
                            <!-- Hidden Input untuk Nama MikroTik -->
                            <input type="hidden" id="mikrotikSite" name="mikrotikSite">
                    
                            <!-- Harga Paket -->
                            <div class="form-group">
                                <label for="hargaPaket">Harga Paket</label>
                                <input type="text" class="form-control" id="hargaPaket" name="hargaPaket" maxlength="7" pattern="\d*" required
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 7)">
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
            const mikrotikDropdown = $('#username');
            const profileDropdown = $('#profile');
            const loadingIndicator = $('#loadingIndicator');
            const selectedMikrotikSpan = $('#selectedMikrotik');
            const mikrotikSiteInput = $('#mikrotikSite');
    
            // Saat MikroTik dipilih
            mikrotikDropdown.on('change', function () {
                const selectedOption = mikrotikDropdown.find(':selected');
                const siteName = selectedOption.data('site');
    
                // Tampilkan nama MikroTik pada span
                if (siteName) {
                    selectedMikrotikSpan.text(siteName);
                    mikrotikSiteInput.val(siteName); // Simpan ke input hidden
                } else {
                    selectedMikrotikSpan.text('');
                    mikrotikSiteInput.val('');
                }
    
                // Kosongkan dan muat ulang dropdown profil
                profileDropdown.empty();
                profileDropdown.append('<option value="">Pilih Profil</option>');
                loadingIndicator.show();
    
                const username = $(this).val();
                if (username) {
                    $.ajax({
                        url: '{{ route("getMikrotikProfiles") }}',
                        type: 'GET',
                        data: { username: username },
                        success: function (data) {
                            loadingIndicator.hide();
                            if (data.status === 'success' && data.profiles.length > 0) {
                                data.profiles.forEach(profile => {
                                    profileDropdown.append(
                                        `<option value="${profile.name}">${profile.name}</option>`
                                    );
                                });
                            }
                        },
                        error: function () {
                            loadingIndicator.hide();
                            profileDropdown.append(
                                '<option value="">Gagal memuat data profil</option>'
                            );
                        }
                    });
                } else {
                    loadingIndicator.hide();
                }
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            $('#pppoeTable').DataTable({
                responsive: true,
                autoWidth: false,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/Indonesian.json"
                }
            });
        });

    </script>



</body>

</html>
