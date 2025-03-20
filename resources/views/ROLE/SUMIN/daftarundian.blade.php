<x-dhs.head />

<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">

        <x-dhs.preload />

        <!-- Navbar -->
        <x-dhs.sumin.nav />
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <x-dhs.sidebar />

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper" style="margin-bottom: 50px">
            <!-- Content Header (Page header) -->
            <x-dhs.sumin.content-header-sumin />
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Info boxes -->

                    <div class="row">

                        <div class="col-md-12">

                            <div class="card">
                                <div class="card-header border-transparent">
                                    <h3 class="card-title">Daftar Undian</h3>
                                    <div class="card-tools">
                                        <button class="btn btn-primary btn-sm" data-toggle="modal"
                                            data-target="#modalTambahUndian">
                                            <i class="fas fa-plus"></i> Tambah Undian
                                        </button>

                                    </div>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="tabelUndian">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Status Diundi</th>
                                                    <th>Kode Undian</th>
                                                    <th>Nama Undian</th>
                                                    <th>MikroTik</th>
                                                    <th>Tanggal Kocok</th>
                                                    <th>Pemenang</th>
                                                    <th>Foto Pemenang</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dftrundian as $undian)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        @if($undian->pemenang != null)
                                                        <span class="badge badge-success">Sudah Diundi</span>
                                                        @else
                                                        <span class="badge badge-danger">Belum Diundi</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $undian->kode_undian }}</td>
                                                    <td>{{ $undian->nama_undian }}</td>
                                                    <td>{{ $undian->mikrotik->site ?? 'Tidak Ada' }}</td>
                                                    <td>{{ $undian->tanggal_kocok }}</td>
                                                    <td>{{ $undian->pemenang ?? 'Belum Ada' }} -
                                                        {{ $pelanggan[$undian->pemenang]->akun_pppoe ?? 'Tidak Ada' }}

                                                    </td>
                                                    <td>
                                                        @if($undian->foto_pemenang)
                                                        <img src="{{ asset('/undian/pemenang/' . $undian->foto_pemenang) }}"
                                                            alt="Foto Pemenang" class="img-thumbnail"
                                                            style="width: 100px; height: 100px;">
                                                        @else
                                                        <button class="btn btn-primary btn-sm" data-toggle="modal"
                                                            data-target="#uploadModal"
                                                            data-id="{{ $undian->id }}">Upload Foto</button>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="" class="btn btn-warning btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="" class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                        <!-- Modal Upload Foto -->
                                        <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog"
                                            aria-labelledby="uploadModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="uploadModalLabel">Upload Foto
                                                            Pemenang</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ route('upload.foto.pemenang') }}" method="POST"
                                                        enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <input type="hidden" name="undian_id" id="undian_id">
                                                            <div class="form-group">
                                                                <label for="foto_pemenang">Pilih Foto</label>
                                                                <input type="file" class="form-control-file"
                                                                    name="foto_pemenang" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">Tutup</button>
                                                            <button type="submit"
                                                                class="btn btn-primary">Upload</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>



                                    </div>
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
        <!-- Modal Tambah Undian -->
        <div class="modal fade" id="modalTambahUndian" tabindex="-1" role="dialog"
            aria-labelledby="modalTambahUndianLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahUndianLabel">Tambah Undian Baru</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{route('undian.tambahundian')}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="mikrotik_id">Pilih MikroTik</label>
                                <select class="form-control" name="mikrotik_id" required>
                                    <option disabled selected value>Pilih Site</option>
                                    @foreach($mikrotiks as $mikrotik)
                                    <option value="{{ $mikrotik->id }}">{{ $mikrotik->site }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="nama_undian">Nama Undian</label>
                                <input type="text" class="form-control" name="nama_undian" placeholder="Nama Undian"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="foto_undian">Foto Undian</label>
                                <input type="file" class="form-control-file" name="foto_undian">
                            </div>

                            <div class="form-group">
                                <label for="tanggal_kocok">Tanggal Di Kocok</label>
                                <input type="datetime-local" class="form-control" name="tanggal_kocok" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
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
    <x-dhs.scripts />
    <script>
        $(document).ready(function () {
            $('#tabelUndian').DataTable();
        });

    </script>
    <script>
        $('#uploadModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var undianId = button.data('id');
            var modal = $(this);
            modal.find('#undian_id').val(undianId);
        });

    </script>
</body>

</html>
