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
                                                    <p class="card-text ">
                                                        <table class="table">
                                                            <tbody>
                                                                <tr>
                                                                    <th>Nama Pelanggan</th>
                                                                    <td id="pelangganId">{{ $psb->nama_pelanggan }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Tgl. Ingin Dipasang</th>
                                                                    <td id="tanggalPasang">
                                                                        {{ $psb->tanggal_ingin_pasang }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Akun PPPoE</th>
                                                                    <td id="akunPppoe">{{ $psb->akun_pppoe }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Pass PPPoE</th>
                                                                    <td id="passwordPppoe">{{ $psb->password_pppoe }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Nama WiFi</th>
                                                                    <td id="namaSsid">{{ $psb->nama_ssid }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Pass WiFi</th>
                                                                    <td id="passwordSsid">{{ $psb->password_ssid }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Alamat</th>
                                                                    <td id="passwordSsid">
                                                                        {{ Str::limit($psb->alamat, 16, '...') }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>

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
                                            <div class="modal-dialog modal-lg" role="document">
                                                <!-- Tambahkan modal-lg agar lebih lebar -->
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="detailModalLabel{{$psb->no_tiket}}">
                                                            🔍 Detail Tiket - {{$psb->no_tiket}}
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="table-responsive">
                                                            <!-- Tambahkan div ini agar tabel bisa scroll di layar kecil -->
                                                            <table class="table table-bordered table-striped">
                                                                <tbody>
                                                                    <tr>
                                                                        <th>No Tiket</th>
                                                                        <td>{{ $psb->no_tiket }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Nama Pelanggan</th>
                                                                        <td>{{ $psb->nama_pelanggan }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Nomor Telepon</th>
                                                                        <td>{{ $psb->nomor_telepon }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Tanggal Ingin Dipasang</th>
                                                                        <td>{{ $psb->tanggal_ingin_pasang }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Akun PPPoE</th>
                                                                        <td>{{ $psb->akun_pppoe }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Password PPPoE</th>
                                                                        <td>{{ $psb->password_pppoe }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Nama WiFi</th>
                                                                        <td>{{ $psb->nama_ssid }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Password WiFi</th>
                                                                        <td>{{ $psb->password_ssid }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Alamat</th>
                                                                        <td class="text-wrap"
                                                                            style="max-width: 400px; word-break: break-word;">
                                                                            {{ $psb->alamat }}</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
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

                        <div class="col-md-12">








                            <div class="card">
                                <div class="card-header border-transparent">
                                    <h3 class="card-title">Riwayat Pemasangan Baru</h3>


                                </div>
                                <!-- /.card-header -->
                                <div class="card-body table-responsive">
                                    <table class="table" id="tabelRiwayat">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>No Tiket</th>
                                                <th>Nama Pelanggan</th>
                                                <th>Tgl. Ingin Dipasang</th>
                                                <th>Tgl. Terpasang</th>
                                                <th>Dipasang Oleh</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($riwayatPemasangan as $riwayat)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $riwayat->no_tiket }}</td>
                                                <td>{{ $riwayat->nama_pelanggan }}</td>
                                                <td>{{ $riwayat->tanggal_ingin_pasang }}</td>
                                                <td>{{ $riwayat->tanggal_terpasang }}</td>
                                                <td>{{ $riwayat->dipasang_oleh }}</td>
                                                <td>
                                                    <button class="btn btn-info btn-sm btnDetail"
                                                        data-tiket="{{ $riwayat->no_tiket }}">
                                                        Cek Detail
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    <!-- Modal untuk Detail Pemasangan -->


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
    <div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-labelledby="modalDetailLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailLabel">Detail Pemasangan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <th>No Tiket</th>
                                    <td id="detailTiket"></td>
                                </tr>
                                <tr>
                                    <th>Nama Pelanggan</th>
                                    <td id="detailNama"></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Ingin Dipasang</th>
                                    <td id="detailTgl"></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Terpasang</th>
                                    <td id="detailTglTerpasang"></td>
                                </tr>
                                <tr>
                                    <th>Dipasang Oleh</th>
                                    <td id="detailDipasang"></td>
                                </tr>
                                <tr>
                                    <th>Akun PPPoE</th>
                                    <td id="detailAkun"></td>
                                </tr>
                                <tr>
                                    <th>Password PPPoE</th>
                                    <td id="detailPass"></td>
                                </tr>
                                <tr>
                                    <th>Nama WiFi</th>
                                    <td id="detailSsid"></td>
                                </tr>
                                <tr>
                                    <th>Password WiFi</th>
                                    <td id="detailWifiPass"></td>
                                </tr>
                                <tr>
                                    <th>Alamat</th>
                                    <td class="text-wrap" style="max-width: 400px; word-break: break-word;">
                                        <span id="detailAlamat"></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </div>
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
                    window.location.href =
                        "{{ route('pemasangan.konfirmasi', ['tiket_id' => '__TIKET_ID__']) }}".replace(
                            '__TIKET_ID__', tiketId);
                }
            });
        }

    </script>

    <script>
        $(document).ready(function () {
            $('#tabelRiwayat').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "language": {

                    "paginate": {
                        "first": "Awal",
                        "last": "Akhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                },

            });
        });

        $(document).ready(function () {
            $('#tabelRiwayat').DataTable(); // Aktifkan DataTables


        });
        $(document).ready(function () {
            $('#tabelRiwayat').DataTable(); // Aktifkan DataTables

            $('.btnDetail').on('click', function () {
                var tiket = $(this).data('tiket'); // Ambil nomor tiket dari tombol
                var url = "{{ route('riwayat.pemasangan', ':tiket') }}".replace(':tiket',
                tiket); // Buat URL sesuai route

                $.ajax({
                    url: url, // Gunakan route Laravel
                    type: 'GET',
                    success: function (data) {
                        if (data.success) {
                            $('#detailTiket').text(data.riwayat.no_tiket);
                            $('#detailNama').text(data.riwayat.nama_pelanggan);
                            $('#detailTgl').text(data.riwayat.tanggal_ingin_pasang);
                            $('#detailTglTerpasang').text(data.riwayat.tanggal_terpasang);
                            $('#detailDipasang').text(data.riwayat.dipasang_oleh);
                            $('#detailAkun').text(data.riwayat.akun_pppoe);
                            $('#detailPass').text(data.riwayat.password_pppoe);
                            $('#detailSsid').text(data.riwayat.nama_ssid);
                            $('#detailWifiPass').text(data.riwayat.password_ssid);
                            $('#detailAlamat').text(data.riwayat.alamat);
                            $('#modalDetail').modal('show'); // Tampilkan modal
                        } else {
                            alert('Data tidak ditemukan!');
                        }
                    }
                });
            });
        });

    </script>
</body>

</html>
