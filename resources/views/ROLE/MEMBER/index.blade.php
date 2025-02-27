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
        <x-dhs.info-box />
       
        <div class="row">
        
          <div class="col-md-12">
           


            <div class="card">
              <div class="card-header">
                <h3 class="card-title">US-Visitors Report</h3>

                <div class="card-tools">
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
                    <!-- Map will be created here -->
                    <div id="world-map-markers" style="height: 325px; overflow: hidden">

                        data
                    </div>
                  </div>
                 
                </div><!-- /.d-md-flex -->
              </div>
              <!-- /.card-body -->
            </div>
         



            <div class="card">
              <div class="card-header border-transparent">
                <h3 class="card-title">Riwayat Pemasangan Baru</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
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
