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
                                    <h3 class="card-title">Data Pelanggan</h3>
                                    <div class="card-tools">
                                        <a class="btn btn-primary btn-sm" href="{{ route('formulir') }}">
                                            <i class="fas fa-plus"></i> Tambah Pelanggan
                                        </a>
                                    </div>
                                </div>


                                <!-- /.card-header -->
    
                                <div class="card-body table-responsive">
                                    <table id="pelangganTable" class="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Status Koneksi</th>
                                                <th>Nama Pelanggan</th>
                                                <th>Paket</th>
                                                <th>Akun</th>
                                                <th>Informasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($plg as $index => $pelanggan)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="row">
                                                            <div class="col-md-6 d-flex justify-content-center align-items-center">
                                                                @if ($pelanggan->status === 'Online')
                                                                    <span class="badge badge-success">Online</span>
                                                                @else
                                                                    <span class="badge badge-danger">Offline</span>
                                                                @endif
                                                            </div>
                                                            <div class="col-md-6 d-flex justify-content-center align-items-center mt-2 mt-md-0">
                                                                <!-- Tombol untuk memicu modal -->
                                                                <button 
                                                                    class="btn btn-info btn-sm cek-modem-btn" 
                                                                    data-pppoe="{{ $pelanggan->akun_pppoe }}" 
                                                                    data-nama="{{ $pelanggan->nama_pelanggan }}"
                                                                    data-routerid="{{ $pelanggan->router_id }}">
                                                                    Cek Modem
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    
                                                    
                                                    <td>{{ $pelanggan->nama_pelanggan }}</td>
                                                    <td>{{ $pelanggan->paket->nama_paket ?? 'Tidak Ada Paket' }}</td>
                                                    <td>{{ $pelanggan->akun_pppoe }}</td>
                                                    <td class="text-center">
                                                       
                                                        <a href="{{ route('pelanggan.show', $pelanggan->id) }}" 
                                                           class="btn btn-primary btn-sm" 
                                                           data-toggle="tooltip" 
                                                           data-placement="top" 
                                                           title="Lihat Informasi">
                                                           <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.card-body -->
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
    
        <x-dhs.footer/>
        <x-dhs.alert />
    
    </div>
    
    <!-- Modal -->
    <div class="modal fade" id="cekModemModal" tabindex="-1" role="dialog" aria-labelledby="cekModemModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cekModemModalLabel">Cek Modem</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="cekModemForm" action="{{ route('cek.modem') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p><strong>Nama Pelanggan:</strong> <span id="modalNama"></span></p>
                        <div class="form-group">
                            <label for="inputPPPoE">Akun</label>
                            <input type="text" class="form-control" id="inputPPPoE" name="pppoe_akun" readonly>
                        </div>
                        <input type="hidden" class="form-control" id="inputRouterID" name="router_id" readonly>
                        <div class="form-group">
                            <label for="selectPort">Port Remote</label>
                            <select id="selectPort" name="remote_port" class="form-control" required>
                                <option disabled selected value>Pilih Port</option>
                                <option value="80">80</option>
                                <option value="8080">8080</option>
                                <option value="443">443</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Remote!</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- DataTables Script -->
  
    
    <!-- ./wrapper -->

    <x-dhs.scripts />

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cekModemButtons = document.querySelectorAll('.cek-modem-btn');
            cekModemButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const namaPelanggan = this.getAttribute('data-nama');
                    const akunPPPoE = this.getAttribute('data-pppoe');
                    const routerID = this.getAttribute('data-routerid');

                    document.getElementById('modalNama').textContent = namaPelanggan;
                    document.getElementById('inputPPPoE').value = akunPPPoE;
                    document.getElementById('inputRouterID').value = routerID;

                    $('#cekModemModal').modal('show');
                });
            });
        });
    </script>
    @if (session('success_script'))
    <script>
        {!! session('success_script') !!}
    </script>
@endif
<script>
    $(document).ready(function () {
        $('#pelangganTable').DataTable({
            responsive: true,
            autoWidth: false,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.20/i18n/Indonesian.json"
            }
        });
    });
</script>
</body>

</html>