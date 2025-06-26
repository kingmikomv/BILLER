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
  {!! file_get_contents(public_path('assetlogin/pdf.css')) !!}
  </style></head>
<body>

<div class="invoice-container">

  <!-- HEADER -->
  <div class="invoice-header">
    <div class="logo">
      @if(isset($usaha->logo_usaha))
        <img src="{{ asset('usaha_logos/' . $usaha->logo_usaha) }}" alt="Logo Perusahaan">
      @else
        Gambar Tidak ada
      @endif
    </div>
    <div class="text-right">
      <h4 class="mb-1"><i class="fas fa-file-invoice"></i> INVOICE</h4>
      <small><i class="fas fa-hashtag"></i> INV-{{ now()->format('YmdHis') }}</small><br>
      <small><i class="fas fa-calendar-alt"></i> Tanggal: {{ \Carbon\Carbon::now()->format('d M Y') }}</small><br>
      @if($invoice->status == 'Lunas')
        <span class="badge-status bg-success text-white">
            <i class="fas fa-check-circle"></i> Lunas
        </span>
      @else
        <span class="badge-status bg-danger text-white">
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
                  Internet Paket: {{ $invoice->pelanggan->profile_paket }} | 
                  Bulan: {{ \Carbon\Carbon::parse($invoice->tanggal_generate)->translatedFormat('F Y') }}
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

    <!-- Tombol Bayar / Cetak -->
   

  </div>
</div>

</body>
</html>
