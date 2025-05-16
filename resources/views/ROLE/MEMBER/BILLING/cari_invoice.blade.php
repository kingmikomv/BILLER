<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice Pembayaran</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 4 -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
            padding: 30px 15px;
            color: #333;
        }

        .invoice-container {
            background: #fff;
            max-width: 900px;
            margin: auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }

        .invoice-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 40px 30px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            align-items: center;
        }

        .invoice-header .logo img {
            max-height: 80px;
            max-width: 200px;
            object-fit: contain;
        }

        .invoice-header .text-right small {
            color: #dbeafe;
        }

        .invoice-body {
            padding: 30px;
        }

        .badge-status {
            display: inline-block;
            padding: 0.8em 1.6em;
            font-size: 1.25rem;
            font-weight: 700;
            border-radius: 30px;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .badge-status.success {
            background-color: #03c12f;
        }

        .section-title {
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 18px;
            color: #111827;
        }

        .table th {
            background-color: #f9fafb;
        }

        .table td,
        .table th {
            border: none;
            border-bottom: 1px solid #e5e7eb;
        }

        .total-section {
            text-align: right;
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-top: 20px;
        }

        .badge-status:not(.success) {
            font-size: 13px;
            padding: 6px 14px;
            border-radius: 20px;
            background-color: #ffe58f;
            color: #856404;
            margin-top: 10px;
        }

        .btn-pay {
            margin-top: 40px;
            text-align: center;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 6px;
        }

        .btn-primary i {
            margin-right: 8px;
        }

        .customer-info-row {
            display: flex;
            margin-bottom: 8px;
        }

        .customer-info-row i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
            color: #007bff;
        }

        .customer-info-label {
            width: 130px;
            font-weight: 600;
            color: #111827;
        }

        .customer-info-value {
            flex: 1;
        }

        @media print {
            .btn-pay {
                display: none;
            }
        }

        /* 🔧 Tambahan: Responsif untuk Mobile */
        @media (max-width: 767.98px) {
            .invoice-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .invoice-header .text-right {
                margin-top: 20px;
                text-align: center !important;
            }
        }
    </style>
</head>
<body>

<div class="invoice-container">

    <!-- HEADER -->
    <div class="invoice-header">
        <div class="logo">
            <img src="{{ asset('usaha_logos/Logo.jpeg') }}" alt="Logo Perusahaan">
        </div>
        <div class="text-right">
            <h4 class="mb-1"><i class="fas fa-file-invoice"></i> INVOICE</h4>
            <small><i class="fas fa-hashtag"></i> {{$invoice->invoice_id}}</small><br>
            <small><i class="fas fa-calendar-alt"></i> Tanggal: {{ \Carbon\Carbon::now()->format('d M Y') }}</small><br>
            @if ($invoice->status == 'Lunas')
                <span class="badge-status success">
                    <i class="fas fa-check-circle"></i> LUNAS
                </span>
            @else
                <span class="badge-status">
                    <i class="fas fa-exclamation-circle"></i> Belum Dibayar
                </span>
            @endif
        </div>
    </div>

    <!-- BODY -->
    <div class="invoice-body">

        <!-- Informasi Pelanggan -->
        <div class="mb-4">
            <div class="section-title"><i class="fas fa-user-circle"></i> Informasi Pelanggan</div>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <div class="customer-info-row">
                        <i class="fas fa-user"></i>
                        <div class="customer-info-label">Nama:</div>
                        <div class="customer-info-value">{{ $invoice->pelanggan->nama_pelanggan ?? '-' }}</div>
                    </div>
                    <div class="customer-info-row">
                        <i class="fas fa-id-badge"></i>
                        <div class="customer-info-label">ID PEL:</div>
                        <div class="customer-info-value">{{ $invoice->pelanggan->pelanggan_id ?? '-' }}</div>
                    </div>
                    <div class="customer-info-row">
                        <i class="fas fa-phone"></i>
                        <div class="customer-info-label">Telepon:</div>
                        <div class="customer-info-value">{{ $invoice->pelanggan->nomor_telepon ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="customer-info-row">
                        <i class="fas fa-network-wired"></i>
                        <div class="customer-info-label">Akun Jaringan:</div>
                        <div class="customer-info-value">{{ $invoice->pelanggan->akun_pppoe ?? '-' }}</div>
                    </div>
                    <div class="customer-info-row">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="customer-info-label">Alamat:</div>
                        <div class="customer-info-value">{{ $invoice->pelanggan->alamat ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Pesanan -->
        <div class="mb-4">
            <div class="section-title"><i class="fas fa-list-alt"></i> Detail Pesanan</div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Deskripsi</th>
                            <th class="text-right">Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                Pembayaran Internet Bulan {{ \Carbon\Carbon::parse($invoice->tanggal_generate)->translatedFormat('F Y') }} <br>
Paket: {{ $invoice->pelanggan->paket->nama_paket }}
                            </td>
                            <td class="text-right">
                                Rp{{ number_format($invoice->jumlah_tagihan, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Total -->
        <div class="total-section">
            Total: Rp{{ number_format($invoice->jumlah_tagihan, 0, ',', '.') }}
        </div>

        <!-- Tombol Bayar atau Cetak -->
        <div class="btn-pay">
            @if($invoice->status == 'Lunas')
                <a class="btn btn-success btn-lg" href="javascript:window.print()">
                    <i class="fas fa-print"></i> Cetak Invoice
                </a>
            @else
                <a class="btn btn-primary btn-lg" href="{{ $invoice->link_pembayaran }}">
                    <i class="fas fa-credit-card"></i> Bayar Sekarang
                </a>
            @endif
        </div>

    </div>
</div>

</body>
</html>
