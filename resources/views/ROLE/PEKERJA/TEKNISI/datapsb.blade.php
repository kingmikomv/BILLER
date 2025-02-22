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
                                    <h3 class="card-title">Pemasangan Baru</h3>


                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class="row">
                                        @forelse ($cocokPelanggan as $psb)
                                        <div class="col-md-4">
                                            <div class="card shadow-lg border-0">
                                                <div class="card-body">
                                                    <h5 class="card-title font-weight-bold text-primary">TIKET - <span
                                                            id="tiketId">{{$psb->no_tiket}}</span></h5>
                                                    <p class="card-text">
                                                        <strong>Nama Pelanggan:</strong> <span
                                                            id="pelangganId">{{$psb->nama_pelanggan}}</span><br>
                                                        <strong>Tgl. Ingin Dipasang:</strong> <span
                                                            id="tanggalPasang">{{$psb->tanggal_ingin_pasang}}</span><br>
                                                        <strong>Akun PPPoE:</strong> <span
                                                            id="akunPppoe">{{$psb->akun_pppoe}}</span><br>
                                                        <strong>Pass PPPoE:</strong> <span
                                                            id="passwordPppoe">{{$psb->password_pppoe}}</span><br>
                                                        <strong>Nama WiFi:</strong> <span
                                                            id="namaSsid">{{$psb->nama_ssid}}</span><br>
                                                        <strong>Pass WiFi:</strong> <span
                                                            id="passwordSsid">{{$psb->password_ssid}}</span>
                                                    </p>
                                                    <button class="btn btn-success btn-block font-weight-bold"
                                                    onclick="konfirmasiPemasangan('{{$psb->no_tiket}}')">
                                                    ✅ Sudah Dipasang
                                                </button>
                                                

                                                    <!-- Dropdown More Options -->
                                                    <div class="dropdown mt-2">
                                                        <button class="btn btn-secondary btn-block dropdown-toggle"
                                                            type="button" id="dropdownMenuButton{{$psb->no_tiket}}"
                                                            data-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                            ⚙️ More Options
                                                        </button>
                                                        <div class="dropdown-menu"
                                                            aria-labelledby="dropdownMenuButton{{$psb->no_tiket}}">
                                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                                data-target="#detailModal{{$psb->no_tiket}}">🔍 Lihat
                                                                Detail</a>
                                                            <a class="dropdown-item" href="#" data-toggle="modal"
                                                                data-target="#undurModal{{$psb->no_tiket}}">✏️ Tgl.
                                                                Pasang Diundur</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Lihat Detail -->
                                        <div class="modal fade" id="detailModal{{$psb->no_tiket}}" tabindex="-1"
                                            role="dialog" aria-labelledby="detailModalLabel{{$psb->no_tiket}}"
                                            aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="detailModalLabel{{$psb->no_tiket}}">
                                                            🔍 Detail Tiket - {{$psb->no_tiket}}</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>Nama Pelanggan:</strong> {{$psb->nama_pelanggan}}</p>
                                                        <p><strong>Nomor Telepon:</strong> {{$psb->nomor_telepon}}</p>
                                                        <p><strong>Alamat:</strong> {{$psb->alamat}}</p>
                                                        <p><strong>Akun PPPoE:</strong> {{$psb->akun_pppoe}}</p>
                                                        <p><strong>Nama WiFi:</strong> {{$psb->nama_ssid}}</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Tgl. Pasang Diundur -->
                                        <div class="modal fade" id="undurModal{{$psb->no_tiket}}" tabindex="-1"
                                            role="dialog" aria-labelledby="undurModalLabel{{$psb->no_tiket}}"
                                            aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="undurModalLabel{{$psb->no_tiket}}">
                                                            ✏️ Ubah Tgl. Pasang Tiket - {{$psb->no_tiket}}</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <label for="newTanggal{{$psb->no_tiket}}">Tanggal
                                                                Baru:</label>
                                                            <input type="date" class="form-control"
                                                                id="newTanggal{{$psb->no_tiket}}"
                                                                name="tanggal_ingin_pasang" required>
                                                        </div>
                                                        <div class="modal-body">
                                                            <label for="alasan{{$psb->no_tiket}}">Alasan
                                                                Perubahan:</label>
                                                            <textarea class="form-control" id="alasan{{$psb->no_tiket}}"
                                                                name="alasan_perubahan" rows="3"
                                                                placeholder="Masukkan alasan perubahan tanggal pemasangan..."
                                                                required></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Simpan
                                                                Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>


                                        @empty
                                        <div class="col-md-12">
                                            <div class="text-center" role="alert">
                                                Tidak ada data pemasangan baru.
                                            </div>
                                        </div>
                                        @endforelse



                                    </div>

                                </div>
                                <!-- /.table-responsive -->
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
    <script>
        function konfirmasiPemasangan(tiketId) {
            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Pastikan perangkat sudah benar-benar dipasang!",
                icon: "warning",
                showCancelButton: true,
               
                confirmButtonText: "✅ Ya, sudah dipasang",
                cancelButtonText: "❌ Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim permintaan ke backend jika dikonfirmasi
                    window.location.href = "{{ route('pemasangan.konfirmasi', ['tiket_id' => '__TIKET_ID__']) }}".replace('__TIKET_ID__', tiketId);
                }
            });
        }
    </script>
</body>

</html>
