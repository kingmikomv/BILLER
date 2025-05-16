<div class="container">
    <div class="row mb-2">
        <div class="col-md-6">
            <p><strong>Invoice ID:</strong> {{ $invoice->invoice_id }}</p>
            <p><strong>Nama Pelanggan:</strong> {{ $invoice->pelanggan->nama_pelanggan ?? '-' }}</p>
            <p><strong>PPPoE:</strong> {{ $invoice->pelanggan->akun_pppoe ?? '-' }}</p>
            <p><strong>
                {{ $invoice->tanggal_pembayaran ? 'Tanggal Bayar:' : 'Jatuh Tempo:' }}
            </strong>
                {{ \Carbon\Carbon::parse($invoice->tanggal_pembayaran ?? $invoice->jatuh_tempo)->format('d/m/Y') }}
            </p>
        </div>
        <div class="col-md-6">
            <p><strong>Bulan:</strong>
                {{ \Carbon\Carbon::parse($invoice->tanggal_pembayaran ?? $invoice->jatuh_tempo)->translatedFormat('F Y') }}
            </p>
            <p><strong>{{ $invoice->tanggal_pembayaran ? 'Jumlah Bayar' : 'Jumlah Tagihan' }}:</strong>
                Rp {{ number_format(optional($invoice->pelanggan->paket)->harga_paket ?? 0, 0, ',', '.') }}
            </p>
            <p><strong>Status:</strong>
                @if ($invoice->status == 'Lunas')
                    <span class="badge badge-success">Lunas</span>
                @else
                    <span class="badge badge-danger">Belum Lunas</span>
                @endif
            </p>
            <p><strong>Router/Site:</strong> {{ optional($invoice->pelanggan->mikrotik)->site ?? '-' }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <p><strong>Alamat:</strong> {{ $invoice->pelanggan->alamat ?? '-' }}</p>
        </div>
    </div>
</div>

