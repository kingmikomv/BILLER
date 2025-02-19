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
                                    Data Pelanggan
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-auto">
                                            <a href="{{ asset('contoh-format.xlsx') }}" class="btn btn-success btn-sm">
                                                <i class="fas fa-download"></i> Download Contoh File
                                            </a>
                                        </div>
                                        <div class="col-auto">
                                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#importModal">
                                                <i class="fas fa-file-import"></i> Import Excel
                                            </button>
                                        </div>
                                        <div class="col-auto">
                                            <a href="{{ route('export.excel') }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-file-export"></i> Export Excel
                                            </a>
                                        </div>
                                    </div>
                            
                                    <div class="table-responsive">
                                        <table id="exampleTable" class="table">
                                            <thead class="text-center">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Kode Pelanggan</th>
                                                    <th>Nama Pelanggan</th>
                                                    <th>Nomor Telp.</th>
                                                    <th>Tgl Daftar</th>
                                                    <th>Paket</th>
                                                    <th>Akun PPPoE</th>
                                                    <th>Pass PPPoE</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($pelanggan as $index => $data)
                                                    <tr class="text-center align-middle">
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $data->pelanggan_id }}</td>
                                                        <td>{{ $data->nama_pelanggan }}</td>
                                                        <td>{{ $data->nomor_telepon }}</td>
                                                        <td>{{ $data->tanggal_daftar }}</td>
                                                        <td>{{ $data->paket->nama_paket ?? 'Tidak Ada Paket' }}</td>
                                                        <td>{{ $data->akun_pppoe }}</td>
                                                        <td>{{ $data->password_pppoe }}</td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <button class="btn btn-warning btn-sm" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-danger btn-sm" title="Hapus">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                                <button class="btn btn-success btn-sm btn-whatsapp" 
                                                                    data-nama="{{ $data->nama_pelanggan }}" 
                                                                    data-nomor="{{ $data->nomor_telepon }}" 
                                                                    data-toggle="modal" 
                                                                    data-target="#whatsappModal">
                                                                    <i class="fab fa-whatsapp"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            







                        </div>


                    </div>


                </div>

            </section>
            <!-- /.content -->
        </div>
        <div class="modal fade" id="whatsappModal" tabindex="-1" aria-labelledby="whatsappModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="whatsappModalLabel">Kirim Pesan WhatsApp</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="whatsappForm">
                            <div class="form-group">
                                <label for="waNama">Nama Pelanggan</label>
                                <input type="text" id="waNama" class="form-control" readonly>
                            </div>
                            <div class="form-group">
                                <label for="waNomor">Nomor WhatsApp</label>
                                <input type="text" id="waNomor" class="form-control" readonly>
                            </div>
                            <div class="form-group">
                                <label for="waPesan">Pesan</label>
                                <textarea id="waPesan" class="form-control" rows="4"
                                    placeholder="Tulis pesan di sini..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-success" id="btnKirimWA">
                            <i class="fab fa-whatsapp"></i> Kirim WhatsApp
                        </button>
                    </div>
                </div>
            </div>
        </div>
          <!-- Modal Import Excel -->
          <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Data Pelanggan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('import.excel') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="file">Pilih File Excel</label>
                                <input type="file" name="file" class="form-control-file" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-file-import"></i> Import
                            </button>
                        </form>
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
            $('#exampleTable').DataTable({
                "responsive": true,
                "autoWidth": false,
                "lengthChange": true,
                "pageLength": 10,
               
            });
        });

    </script>
    <!-- Modal WhatsApp -->


    <!-- JavaScript -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let waNomor = "";

            // Saat tombol WA diklik, isi modal dengan data pelanggan
            document.querySelectorAll(".btn-whatsapp").forEach(button => {
                button.addEventListener("click", function () {
                    let nama = this.getAttribute("data-nama");
                    waNomor = this.getAttribute("data-nomor");

                    document.getElementById("waNama").value = nama;
                    document.getElementById("waNomor").value = waNomor;
                    document.getElementById("waPesan").value = ``;
                });
            });

            // Saat tombol Kirim WhatsApp diklik
            document.getElementById("btnKirimWA").addEventListener("click", function () {
                let nomor = document.getElementById("waNomor").value;
                let pesan = document.getElementById("waPesan").value;

                // Tampilkan loading sebelum mengirim
                Swal.fire({
                    title: "Mengirim Pesan...",
                    text: "Harap tunggu beberapa saat.",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Kirim request ke server Laravel
                fetch("{{ route('send.whatsapp') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            nomor,
                            pesan
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: "success",
                                title: "Berhasil!",
                                text: "Pesan berhasil dikirim.",
                                timer: 2000,
                                showConfirmButton: false
                            });

                            // Tutup modal setelah pesan dikirim
                            $("#whatsappModal").modal("hide");
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Gagal!",
                                text: "Pesan tidak dapat dikirim. Silakan coba lagi.",
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: "error",
                            title: "Terjadi Kesalahan!",
                            text: "Pastikan koneksi internet stabil dan coba lagi.",
                        });
                        console.error("Error:", error);
                    });
            });
        });

    </script>

    </html>
