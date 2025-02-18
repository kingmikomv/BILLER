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
                                    <form id="pelangganForm" method="POST">
                                        @csrf
                                        <meta name="csrf-token" content="{{ csrf_token() }}">
                                        <div class="mb-4">
                                            <div class="row">
                                                <div class="col-md-3 mt-2">
                                                    <button type="button" id="restartUser"
                                                        class="btn btn-success w-100">
                                                        <i class="fas fa-redo-alt"></i> Restart User
                                                    </button>
                                                </div>
                                                <div class="col-md-3 mt-2">
                                                    <button type="button" id="kirimTagihan"
                                                        class="btn btn-warning w-100">
                                                        <i class="fas fa-file-invoice"></i> Kirim Tagihan
                                                    </button>
                                                </div>
                                                <div class="col-md-3 mt-2">
                                                    <button type="button" id="isolir" class="btn btn-danger w-100">
                                                        <i class="fas fa-ban"></i> Isolir
                                                    </button>
                                                </div>
                                                <div class="col-md-3 mt-2">
                                                    <button type="button" id="bukaIsolir" class="btn btn-primary w-100">
                                                        <i class="fas fa-check-circle"></i> Buka Isolir
                                                    </button>
                                                </div>
                                                
                                            </div>
                                        </div>

                                        <!-- Hidden input to store selected IDs -->
                                        <input type="hidden" name="selected_ids" id="selected_ids" value="[]">

                                        <table id="pelangganTable" class="table">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" id="pilihData"></th>
                                                    <th>#</th>
                                                    <th>Status Koneksi</th>
                                                    <th>Info</th>
                                                    <th>Nama Pelanggan</th>
                                                    <th>Paket</th>
                                                    <th>Akun</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($plg as $index => $pelanggan)
                                                <tr>
                                                    <td><input type="checkbox" class="select-row"
                                                            value="{{ $pelanggan->id }}"></td>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="row">
                                                            <div class="col-md-6 d-flex justify-content-center align-items-center">
                                                                @if ($pelanggan->status === 'Online')
                                                                    <span class="badge badge-success">Online</span>
                                                                @elseif ($pelanggan->status === 'Offline')
                                                                    <span class="badge badge-danger">Offline</span>
                                                                @elseif ($pelanggan->status === 'Isolir')
                                                                    <span class="badge badge-warning">Isolir</span>
                                                                @endif
                                                            </div>
                                                            
                                                            <div
                                                                class="col-md-6 d-flex justify-content-center align-items-center mt-2 mt-md-0">
                                                                <button class="btn btn-info btn-sm cek-modem-btn"
                                                                    data-pppoe="{{ $pelanggan->akun_pppoe }}"
                                                                    data-nama="{{ $pelanggan->nama_pelanggan }}"
                                                                    data-routerid="{{ $pelanggan->router_id }}">
                                                                    Cek Modem
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('pelanggan.show', $pelanggan->id) }}"
                                                            class="btn btn-primary btn-sm" data-toggle="tooltip"
                                                            data-placement="top" title="Lihat Informasi">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                    <td>{{ $pelanggan->nama_pelanggan }}</td>
                                                    <td>{{ $pelanggan->paket->nama_paket ?? 'Tidak Ada Paket' }}</td>
                                                    <td>{{ $pelanggan->akun_pppoe }}</td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="fas fa-cog"></i> 
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <!-- Tombol Edit -->
                                                        
                                                                <!-- Tombol Hapus -->
                                                                <button class="dropdown-item delete-btn" data-id="{{ $pelanggan->id }}" data-nama="{{ $pelanggan->nama_pelanggan }}">
                                                                    <i class="fas fa-trash-alt text-danger"></i> Hapus
                                                                </button>
                                                    
                                                                <!-- Tombol Isolir -->
                                                              
                                                            </div>
                                                        </div>
                                                    </td>
                                                    
                                                    
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
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
        <!-- /.content-wrapper -->

        <x-dhs.footer />
        <x-dhs.alert />

    </div>

    <!-- Modal -->
    <div class="modal fade" id="cekModemModal" tabindex="-1" role="dialog" aria-labelledby="cekModemModalLabel"
        aria-hidden="true">
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
        {
            !!session('success_script') !!
        }

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



<script>
    document.addEventListener("DOMContentLoaded", () => {
        const selectedIdsInput = document.getElementById("selected_ids");
        const checkboxes = document.querySelectorAll(".select-row");

        // Handle "Select All" checkbox
        document.getElementById("pilihData").addEventListener("change", function () {
            const allChecked = this.checked;
            checkboxes.forEach(checkbox => {
                checkbox.checked = allChecked;
            });
            updateSelectedIds();
        });

        // Update selected IDs when individual checkboxes are toggled
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener("change", updateSelectedIds);
        });

        function updateSelectedIds() {
            const selectedIds = Array.from(checkboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);
            selectedIdsInput.value = JSON.stringify(selectedIds);
        }

        // Function to send AJAX request
        const sendRequest = (url) => {
            const selectedIds = JSON.parse(selectedIdsInput.value || "[]");
            if (!selectedIds.length) {
                Swal.fire({
                    icon: "warning",
                    title: "Peringatan",
                    text: "Pilih pengguna terlebih dahulu.",
                });
                return;
            }

            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Aksi ini tidak dapat dibatalkan.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, lanjutkan!",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(url, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                ids: selectedIds
                            }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire({
                                icon: data.message ? "success" : "error",
                                title: data.message || "Aksi berhasil.",
                                text: data.message || "Proses selesai.",
                            }).then(() => {
                                location.reload(); // Reload halaman
                            });
                        })
                        .catch(error => {
                            console.error("Fetch error:", error);
                            Swal.fire({
                                icon: "error",
                                title: "Terjadi kesalahan",
                                text: "Periksa konsol untuk detail.",
                            });
                        });
                }
            });
        };

        // Attach click events to buttons
        document.getElementById("restartUser").addEventListener("click", () => sendRequest(
            "{{ route('pelanggan.restart') }}"));
        document.getElementById("kirimTagihan").addEventListener("click", () => sendRequest(
            "{{ route('pelanggan.kirimTagihan') }}"));
        document.getElementById("isolir").addEventListener("click", () => sendRequest(
            "{{ route('pelanggan.isolir') }}"));
        document.getElementById("bukaIsolir").addEventListener("click", () => sendRequest(
            "{{ route('pelanggan.bukaIsolir') }}"));
        document.getElementById("broadcastWA").addEventListener("click", () => sendRequest(
            "{{ route('pelanggan.broadcastWA') }}"));
    });
</script>


</body>

</html>
