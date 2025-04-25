<x-dhs.head />

<body class="hold-transition dark-mode sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
    <div class="wrapper">

        <x-dhs.preload />
        <x-dhs.nav />
        <x-dhs.sidebar />

        <div class="content-wrapper" style="margin-bottom: 50px">
            <x-dhs.content-header />

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Data PSB Sales</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Table for desktop -->
                                    <div class="d-none d-md-block">
                                        <table id="example1" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama Sales</th>
                                                    <th>Nama Lengkap</th>
                                                    <th>Alamat</th>
                                                    <th>Foto Lokasi</th>
                                                    <th>Paket PSB</th>
                                                    <th>Status Sales</th>
                                                    <th>Status Pemasangan</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($data as $key => $psb)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ $psb->sales }}</td>
                                                    <td>{{ $psb->nama_psb }}</td>
                                                    <td>{{ $psb->alamat_psb }}</td>
                                                    <td>
                                                        <img src="{{ asset($psb->foto_lokasi_psb) }}" alt="Foto Lokasi" width="100" class="img-thumbnail popup-image" data-img="{{ asset($psb->foto_lokasi_psb) }}" style="cursor:pointer;">
                                                    </td>
                                                    <td>{{ $psb->paket_psb }}</td>
                                                    <td>
                                                        @if ($psb->status_sales == 1)
                                                            <span class="badge badge-success">Disetujui</span>
                                                        @else
                                                            <span class="badge badge-secondary">Menunggu</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php $status = strtolower($psb->status_pemasangan); @endphp
                                                        @if ($status == 'belum dikonfirmasi')
                                                            <span class="badge badge-warning">Belum Dikonfirmasi</span>
                                                        @elseif ($status == 'cancel')
                                                            <span class="badge badge-danger">Cancel</span>
                                                        @elseif ($status == 'ok')
                                                            <span class="badge badge-success">OK</span>
                                                        @else
                                                            <span class="badge badge-secondary">Tidak Diketahui</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if (in_array(auth()->user()->role, ['member', 'cs']))
                                                        <button class="btn btn-success btn-sm btn-acc" data-id="{{ $psb->id }}">ACC</button>
                                                        <button class="btn btn-danger btn-sm btn-cancel" data-id="{{ $psb->id }}" data-toggle="modal" data-target="#cancelModal">Cancel</button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Card for mobile -->
                                    <div class="d-md-none">
                                        @foreach ($data as $key => $psb)
                                            <div class="card mb-3 shadow-sm">
                                                <div class="card-body">
                                                    <p><strong>Sales:</strong> {{ $psb->sales }}</p>
                                                    <p><strong>Nama :</strong> {{ $psb->nama_psb }}</p>
                                                    <p><strong>Alamat:</strong> {{ $psb->alamat_psb }}</p>
                                                    <p><strong>Paket:</strong> {{ $psb->paket_psb }}</p>
                                                    <p><strong>Status Sales:</strong> 
                                                        @if ($psb->status_sales == 1)
                                                            <span class="badge badge-success">Disetujui</span>
                                                        @else
                                                            <span class="badge badge-secondary">Menunggu</span>
                                                        @endif
                                                    </p>
                                                    <p><strong>Status Pemasangan:</strong> 
                                                        @php $status = strtolower($psb->status_pemasangan); @endphp
                                                        @if ($status == 'belum dikonfirmasi')
                                                            <span class="badge badge-warning">Belum Dikonfirmasi</span>
                                                        @elseif ($status == 'cancel')
                                                            <span class="badge badge-danger">Cancel</span>
                                                        @elseif ($status == 'ok')
                                                            <span class="badge badge-success">OK</span>
                                                        @else
                                                            <span class="badge badge-secondary">Tidak Diketahui</span>
                                                        @endif
                                                    </p>
                                                    <p><strong>Foto Lokasi:</strong><br>
                                                        <img src="{{ asset($psb->foto_lokasi_psb) }}" width="100" class="img-thumbnail popup-image" data-img="{{ asset($psb->foto_lokasi_psb) }}" style="cursor:pointer;">
                                                    </p>
                                                    @if (in_array(auth()->user()->role, ['member', 'cs']))
                                                    <div class="mt-2">
                                                        <button class="btn btn-success btn-sm btn-acc" data-id="{{ $psb->id }}">ACC</button>
                                                        <button class="btn btn-danger btn-sm btn-cancel" data-id="{{ $psb->id }}" data-toggle="modal" data-target="#cancelModal">Cancel</button>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Image Modal -->
        <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-body text-center">
                        <img id="modalImage" src="" class="img-fluid" alt="Popup Image">
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Modal -->
        <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="cancelForm" method="POST" action="">
                    @csrf
                    <input type="hidden" name="id" id="cancel_id">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Alasan Pembatalan</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <textarea name="alasan" class="form-control" rows="4" placeholder="Tulis alasan pembatalan..." required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger">Kirim</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <x-dhs.footer />
    </div>

    <x-dhs.scripts />
    <script>
        $(document).ready(function () {
            $('#example1').DataTable();

            $('.popup-image').on('click', function () {
                const imgSrc = $(this).data('img');
                $('#modalImage').attr('src', imgSrc);
                $('#imageModal').modal('show');
            });

            $('.btn-cancel').on('click', function () {
                $('#cancel_id').val($(this).data('id'));
            });
        });
    </script>
</body>
</html>
