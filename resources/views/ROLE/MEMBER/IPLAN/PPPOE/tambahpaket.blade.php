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
                                    
                                </div>

                                <!-- /.card-header -->

                                <div class="card-body">
                                    <form action="{{ route('tambahpaket') }}" method="post">
                                        @csrf
                                        <!-- Pilihan MikroTik -->
                                        <!-- Pilihan MikroTik -->
                                        <div class="form-group">
                                            <label for="username">Pilih MikroTik ( Aktif )</label>
                                            <select class="form-control" id="username" name="username" required>
                                                <option value="">Pilih MikroTik</option>
                                                @foreach($mikrotikList as $router)
                                                <option value="{{ $router->id }}" data-site="{{ $router->site }}">
                                                    {{ $router->site }}
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
                                            <small id="loadingIndicator" style="display:none;">Memuat profil
                                                MikroTik...</small>
                                        </div>

                                        <!-- Nama Paket -->
                                        <div class="form-group">
                                            <label for="packageName">Nama Paket</label>
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control" id="packageName"
                                                    name="namaPaket" placeholder="Nama Paket" required>
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
                                            <input type="text" class="form-control" id="hargaPaket" name="hargaPaket"
                                                required
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 7)">
                                        </div>

                                        
                                        <button type="submit" class="btn btn-primary">Simpan Profil</button>
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

                // Tampilkan nama MikroTik di sebelah input paket
                selectedMikrotikSpan.text(siteName || '');
                mikrotikSiteInput.val(siteName || '');

                // Kosongkan dropdown profil sebelum menambahkan data baru
                profileDropdown.empty().append('<option value="">Pilih Profil</option>');

                // Jika tidak ada pilihan MikroTik, hentikan eksekusi
                const username = $(this).val();
                if (!username) return;

                // Tampilkan loading indikator
                loadingIndicator.show();

                $.ajax({
                    url: '{{ route("getMikrotikProfiles") }}',
                    type: 'GET',
                    data: {
                        username: username
                    },
                    dataType: 'json',
                    success: function (response) {
                        loadingIndicator.hide();
                        if (response.status === 'success' && response.profiles.length > 0) {
                            response.profiles.forEach(profile => {
                                profileDropdown.append(
                                    `<option value="${profile.name}">${profile.name}</option>`
                                );
                            });
                        } else {
                            profileDropdown.append(
                                '<option value="">Tidak ada profil tersedia</option>');
                        }
                    },
                    error: function (xhr) {
                        loadingIndicator.hide();
                        let errorMessage = "Gagal memuat data profil";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage += ": " + xhr.responseJSON.message;
                        }
                        profileDropdown.append(`<option value="">${errorMessage}</option>`);
                    }
                });
            });
        });

    </script>

sesuaikan 
    
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
