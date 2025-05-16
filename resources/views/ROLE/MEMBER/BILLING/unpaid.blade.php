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
            <x-dhs.content-header />

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    Data Pelanggan Belum Bayar
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-small-font" id="belumBayarTable">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Invoice ID</th>
                                                    <th>Data Plg.</th>
                                                    <th>Tgl. Tagihan</th>
                                                    <th>Bulan</th>
                                                    <th>Jml. Tagihan</th>
                                                    <th>Option</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($unpaidInvoices as $index => $invoice)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
  <td>
                                                            <a href="#" class="text-primary show-unpaid-detail"
                                                                data-id="{{ $invoice->invoice_id }}" data-toggle="modal"
                                                                data-target="#invoiceDetailModal">
                                                                {{ $invoice->invoice_id }}
                                                            </a>
                                                        </td>                                                 
                                                        <td>{{ $invoice->pelanggan->nama_pelanggan ?? '-' }} | {{ "ID : ". $invoice->pelanggan->pelanggan_id ?? '-' }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($invoice->jatuh_tempo)->format('d/m/Y') }}</td>
<td>{{ \Carbon\Carbon::parse($invoice->tanggal_pembayaran)->translatedFormat('F Y') }}
                                                        </td>                                                    <td>Rp {{ number_format(optional($invoice->pelanggan->paket)->harga_paket ?? 0, 0, ',', '.') }}</td>
                                                    <td>
                                                        <div class="row">
                                                            <div class="col-md-4 mt-1">
                                                                <a href="#"
                                                                   class="btn btn-success btn-sm confirm-bayar"
                                                                   data-id="{{ $invoice->id }}"
                                                                   data-nama="{{ $invoice->pelanggan->nama_pelanggan ?? '-' }}"
                                                                   data-invoice="{{ $invoice->invoice_id }}"
                                                                   data-jumlah="{{ number_format(optional($invoice->pelanggan->paket)->harga_paket ?? 0, 0, ',', '.') }}">
                                                                    <i class="fas fa-check"></i> Bayar
                                                                </a>
                                                            </div>
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
        </div>
  <div class="modal fade" id="invoiceDetailModal" tabindex="-1" role="dialog"
            aria-labelledby="invoiceDetailLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Invoice</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="invoiceDetailContent">
                        <p class="text-center">Memuat data...</p>
                    </div>
                </div>
            </div>
        </div>
        <x-dhs.footer />
    </div>

    <x-dhs.scripts />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

   <script>
    $(document).ready(function () {
        $('#belumBayarTable').DataTable({
            responsive: true,
            paging: true,
            lengthChange: false,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false
        });

        $(document).on('click', '.confirm-bayar', function (e) {
            e.preventDefault();

            const invoiceId = $(this).data('id');
            const nama = $(this).data('nama');
            const invoice = $(this).data('invoice');
            const jumlah = $(this).data('jumlah');

            Swal.fire({
                title: 'Konfirmasi Pembayaran',
         html: `
    <div class="text-start small">
        <div class="mb-2"><strong>Nama:</strong> ${nama}</div>
        <div class="mb-2"><strong>Invoice ID:</strong> ${invoice}</div>
        <div class="mb-3"><strong>Jumlah Tagihan:</strong> Rp ${jumlah}</div>

        <div class="form-group mb-0">
            <label for="swal-metode" class="form-label"><strong>Pilih Metode Pembayaran:</strong></label>
            <select id="swal-metode" class="form-control form-control-sm mt-1">
                <option value="">-- Pilih Metode --</option>
                <option value="Cash">Cash</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>
        </div>
    </div>
`,

                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Bayar Sekarang',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    const metode = document.getElementById('swal-metode').value;
                    if (!metode) {
                        Swal.showValidationMessage('Silakan pilih metode pembayaran');
                    }
                    return metode;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const metode = result.value;

                    $.ajax({
                        url: "", // <-- sesuaikan dengan route Anda
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: invoiceId,
                            metode: metode
                        },
                        success: function (res) {
                            Swal.fire('Berhasil!', res.message, 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function () {
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat memproses pembayaran.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
 <script>
        $(document).on('click', '.show-unpaid-detail', function() {
            const invoiceId = $(this).data('id');
            $('#invoiceDetailContent').html('<p class="text-center">Memuat data...</p>');

            $.ajax({
                url: '/home/billing/unpaid/detail/' + invoiceId,
                type: 'GET',
                success: function(response) {
                    $('#invoiceDetailContent').html(response);
                },
                error: function() {
                    $('#invoiceDetailContent').html(
                        '<p class="text-danger text-center">Gagal memuat data.</p>');
                }
            });
        });
    </script>
</body>

</html>
