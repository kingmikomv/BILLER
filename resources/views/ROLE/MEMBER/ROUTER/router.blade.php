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
                                    <h3 class="card-title">Daftar Router</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                            data-target="#tambahRouterModal">
                                            <i class="fas fa-plus"></i> Tambah Router
                                        </button>
                                      
                                    </div>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class="d-md-flex">
                                        <div class="p-1 flex-fill" style="overflow: hidden">
                                            <div class="table-responsive">
                                                <table id="routerTable" class="table">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 10px">#</th>
                                                            <th>Status</th>
                                                            <th>Nama Router</th>
                                                            <th>Port API</th>
                                                            <th>Port Winbox</th>
                                                            <th>Username</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($mikrotik as $router)
                                                            <tr>
                                                                <td>{{ $loop->iteration }}</td>
                                                                <td>
                                                                    @if (isset($routerStatuses[$router->id]) && $routerStatuses[$router->id] == 'Online')
                                                                        <span class="badge badge-success">Online</span>
                                                                    @else
                                                                        <span class="badge badge-danger">Offline</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $router->site }}</td>
                                                                <td>{{ "id-1.aqtnetwork.my.id:".$router->port_api }}</td>
                                                                <td>{{ "id-1.aqtnetwork.my.id:".$router->port_winbox }}</td>
                                                                <td>{{ $router->username }}</td>
                                                                <td>
                                                                    <div class="dropdown">
                                                                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                                                                            id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                                                            aria-expanded="false">
                                                                            Actions
                                                                        </button>
                                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                                            <!-- Cek Koneksi -->
                                                                            <a class="dropdown-item" href="{{ route('cek-koneksi', $router->id) }}">
                                                                                <i class="fas fa-plug mr-2"></i> Cek Koneksi
                                                                            </a>
                                                                            <!-- Copy Script -->
                                                                            <a class="dropdown-item copy-script" href="#"
                                                                                data-router="{{ $router->id }}">
                                                                                <i class="fas fa-copy mr-2"></i> Copy Script
                                                                            </a>
                                                                            <!-- Reset Koneksi -->
                                                                            <a class="dropdown-item" href="#">
                                                                                <i class="fas fa-redo mr-2"></i> Reset Koneksi
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <!-- Router Table -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
    
        <!-- Modal to Add Router -->
        <div class="modal fade" id="tambahRouterModal" tabindex="-1" aria-labelledby="tambahRouterModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tambahRouterModalLabel">Tambah Router</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="tambahRouterForm" method="post" action="{{ route('member.router.tambah') }}">
                        @csrf
                        <div class="modal-body">
                            <!-- Nama Router -->
                            <div class="form-group">
                                <label for="namaRouter">Nama Router</label>
                                <input type="text" class="form-control" id="namaRouter" name="site"
                                    placeholder="Masukkan nama router" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    
        <x-dhs.footer />
    </div>
    <!-- ./wrapper -->

    <x-dhs.scripts />
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Ambil semua tombol copy script
            const copyButtons = document.querySelectorAll('.copy-script');

            // Tambahkan event listener pada setiap tombol copy script
            copyButtons.forEach(button => {
                button.addEventListener('click', function () {
                    // Ambil id router yang terkait dengan tombol ini
                    const routerId = this.getAttribute('data-router');

                    // Ambil data router menggunakan AJAX atau data yang sudah ada
                    const routerData = @json(
                        $mikrotik); // Menyertakan data router dari server ke dalam JavaScript

                    // Cari data router yang sesuai dengan id
                    const router = routerData.find(r => r.id == routerId);

                    if (router) {
                        const scriptContent =
                            `
/ip service set disable=no port=${router.port_api} [find name=api]; /ip service set disable=no port=${router.port_winbox} [find name=winbox]; /ip firewall nat remove [find comment="Biller_Remod"]; /ip firewall nat add chain=dstnat dst-port=${router.port_remoteweb} protocol=tcp action=dst-nat to-address=0.0.0.0 to-port=80 comment="Biller_Remod"; /interface l2tp-client add name="${router.vpn_name}" comment="VPN Billing" connect-to=id-1.aqtnetwork.my.id user=${router.vpn_username} password=${router.vpn_password} disable=no; /user remove [find group=BILLER]; /user group remove [find name="BILLER"]; /user group add name=BILLER policy=write,read,api,test; /user add name=${router.username} password=${router.password} group=BILLER; /ip pool add name=Isolir ranges=172.16.255.1-172.16.255.254; /ppp profile add name=isolir-biller local-address=172.16.255.1 remote-address=Isolir; /ip firewall filter add chain=forward src-address-list=isolir-pelanggan action=drop comment="Generate By Biller - Isolir"; /system script add name=auto-isolir source=":foreach i in=[/ppp active find where profile=isolir-biller] do={/ip firewall address-list add list=isolir-pelanggan address=[/ppp active get $i address]}"; /system scheduler add name=cek-isolir interval=1m on-event=auto-isolir; :foreach i in=[/ppp active find where profile=isolir-biller] do={ :local user [/ppp active get $i name]; /ppp active remove $i; /ip firewall address-list add list=isolir-pelanggan address=[/ppp secret get [find name=$user] remote-address]; };`;

                        // Buat elemen textarea sementara untuk menyalin script
                        const textarea = document.createElement('textarea');
                        textarea.value = scriptContent;
                        document.body.appendChild(textarea);

                        // Pilih dan salin isi script
                        textarea.select();
                        document.execCommand('copy');

                        // Hapus elemen textarea
                        document.body.removeChild(textarea);

                        // Tampilkan pesan berhasil
                        alert('Script berhasil disalin!');
                    }
                });
            });
        });

    </script>
    <!-- DataTables Initialization -->
<script>
    $(document).ready(function () {
        $('#routerTable').DataTable({
            responsive: true,
            autoWidth: false,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/id.json" // Bahasa Indonesia (optional)
            }
        });
    });
</script>

    <x-dhs.alert />
</body>

</html>
