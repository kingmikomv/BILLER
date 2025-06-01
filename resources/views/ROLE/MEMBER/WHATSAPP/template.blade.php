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
                                <div class="card-header border-transparent">
                                    <h3 class="card-title">Templates Messages</h3>
                                </div>

                                <div class="row">
                                    <!-- Form Input Per Template -->
                                    <div class="col-md-6">
                                        <div class="card-body">
                                            <form method="POST" action="{{ route('whatsapp.store') }}">
                                                @csrf
                                                <div class="form-group">
                                                    <label for="template_type">Type</label>
                                                    <select name="name" class="form-control" required>
                                                        <option disabled selected value>-- PILIH TYPE --</option>
                                                        <option value="Invoice Terbit">Invoice Terbit</option>
                                                        <option value="Invoice Reminder">Invoice Reminder</option>
                                                        <option value="Invoice Overdue">Invoice Overdue</option>
                                                        <option value="Payment Paid">Payment Paid</option>
                                                        <option value="Payment Cancel">Payment Cancel</option>
                                                        <option value="Account Suspend">Account Suspend</option>
                                                        <option value="Account Active">Account Active</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="template_text">Text</label>
                                                    <textarea name="content" class="form-control" rows="15" required></textarea>
                                                </div>

                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                    <button type="reset" class="btn btn-secondary">Reset Default</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Form Simpan Semua Template Sekaligus -->
                                    <div class="col-md-6">
                                        <div class="card-header">
                                            <strong>Simpan Semua Template Sekaligus</strong>
                                        </div>
                                        <div class="card-body">
                                            @php
                                                $defaultTemplates = [
                                                    'Invoice Terbit' => "Yth. Pelanggan [full_name]

Kami informasikan Invoice Pembayaran Internet / Wi-Fi anda telah terbit dan dapat dibayarkan, berikut rinciannya:
------------------------
ID Pelanggan: [uid]
Nomor Invoice: [no_invoice]
Amount: Rp [amount]
PPN: [ppn]
Discount: [discount]
Total: Rp [total]
Item: Internet [pppoe_user] - [pppoe_profile]
Jatuh tempo: [due_date]
------------------------
Silakan melakukan pembayaran melalui [payment_gateway] atau metode lainnya.

[footer]",
                                                    'Invoice Reminder' => "Yth. Pelanggan [full_name]

Ini adalah pengingat bahwa invoice berikut belum dibayar:
------------------------
Nomor Invoice: [no_invoice]
Jumlah Tagihan: Rp [total]
Jatuh Tempo: [due_date]
------------------------
Segera lakukan pembayaran untuk menghindari denda atau pemutusan layanan.

[footer]",
                                                    'Invoice Overdue' => "Yth. Pelanggan [full_name]

Invoice anda telah melewati tanggal jatuh tempo:
------------------------
Nomor Invoice: [no_invoice]
Jumlah: Rp [total]
Jatuh Tempo: [due_date]
------------------------
Harap segera melakukan pembayaran untuk menghindari pemutusan layanan.

[footer]",
                                                    'Payment Paid' => "Yth. Pelanggan [full_name]

Pembayaran untuk invoice berikut telah berhasil diterima:
------------------------
Nomor Invoice: [no_invoice]
Jumlah Dibayar: Rp [total]
Tanggal: [invoice_date]
------------------------
Terima kasih atas pembayaran anda.

[footer]",
                                                    'Payment Cancel' => "Yth. Pelanggan [full_name]

Pembayaran anda dengan rincian berikut telah dibatalkan:
------------------------
Nomor Invoice: [no_invoice]
Jumlah: Rp [total]
------------------------
Silakan hubungi layanan pelanggan jika ini tidak sesuai.

[footer]",
                                                    'Account Suspend' => "Yth. Pelanggan [full_name]

Akun anda telah ditangguhkan karena alasan berikut:
------------------------
Status: Suspend
Nomor Invoice: [no_invoice]
------------------------
Silakan selesaikan tagihan anda agar layanan kembali aktif.

[footer]",
                                                    'Account Active' => "Yth. Pelanggan [full_name]

Selamat! Akun anda telah aktif kembali.
------------------------
Status: Aktif
------------------------
Terima kasih telah melakukan pembayaran.

[footer]",
                                                ];
                                            @endphp

                                            <form method="POST" action="{{ route('whatsapp.bulkStore') }}">
                                                @csrf
                                                @foreach (array_keys($defaultTemplates) as $templateName)
                                                    <div class="form-group">
                                                        <label>{{ $templateName }}</label>
                                                        <textarea name="templates[{{ $templateName }}]" class="form-control" rows="6">{{ old("templates.$templateName", $templates[$templateName]->content ?? $defaultTemplates[$templateName]) }}</textarea>
                                                    </div>
                                                @endforeach
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-success">Simpan Semua Template</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Placeholder Reference -->
                                <div class="col-md-12 mt-4">
                                    <div class="card bg-dark p-3 text-white">
                                        <h5>📌 Variabel Placeholder yang Bisa Digunakan</h5>
                                        <p>Kamu bisa menggunakan tag di bawah ini dalam isi template untuk menggantikan nilai secara otomatis:</p>
                                        <table class="table table-sm table-bordered table-striped table-dark">
                                            <thead>
                                                <tr>
                                                    <th>Placeholder</th>
                                                    <th>Contoh Nilai</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr><td>[full_name]</td><td>Nama Lengkap</td></tr>
                                                <tr><td>[uid]</td><td>12345678</td></tr>
                                                <tr><td>[no_invoice]</td><td>INV-20240518-001</td></tr>
                                                <tr><td>[amount]</td><td>200.000</td></tr>
                                                <tr><td>[ppn]</td><td>20.000</td></tr>
                                                <tr><td>[discount]</td><td>10.000</td></tr>
                                                <tr><td>[total]</td><td>210.000</td></tr>
                                                <tr><td>[pppoe_user]</td><td>johndoe@isp</td></tr>
                                                <tr><td>[pppoe_profile]</td><td>10 Mbps</td></tr>
                                                <tr><td>[due_date]</td><td>2025-05-31</td></tr>
                                                <tr><td>[invoice_date]</td><td>2025-05-01</td></tr>
                                                <tr><td>[payment_gateway]</td><td>Link Pembayaran</td></tr>
                                                <tr><td>[footer]</td><td>PT. ISP Kita - 08123456789</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- End Reference -->
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <x-dhs.footer />
    </div>

    <x-dhs.scripts />

    <script>
        const savedTemplates = @json($templates);

        const defaultTemplates = {
            "Invoice Terbit": savedTemplates["Invoice Terbit"]?.content || `Yth. Pelanggan [full_name]

Kami informasikan Invoice Pembayaran Internet / Wi-Fi anda telah terbit dan dapat dibayarkan, berikut rinciannya:
------------------------
ID Pelanggan: [uid]
Nomor Invoice: [no_invoice]
Amount: Rp [amount]
PPN: [ppn]
Discount: [discount]
Total: Rp [total]
Item: Internet [pppoe_user] - [pppoe_profile]
Jatuh tempo: [due_date]
------------------------
Silakan melakukan pembayaran melalui [payment_gateway] atau metode lainnya.

[footer]`,
            "Invoice Reminder": savedTemplates["Invoice Reminder"]?.content || `Yth. Pelanggan [full_name]

Ini adalah pengingat bahwa invoice berikut belum dibayar:
------------------------
Nomor Invoice: [no_invoice]
Jumlah Tagihan: Rp [total]
Jatuh Tempo: [due_date]
------------------------
Segera lakukan pembayaran untuk menghindari denda atau pemutusan layanan.

[footer]`,
            "Invoice Overdue": savedTemplates["Invoice Overdue"]?.content || `Yth. Pelanggan [full_name]

Invoice anda telah melewati tanggal jatuh tempo:
------------------------
Nomor Invoice: [no_invoice]
Jumlah: Rp [total]
Jatuh Tempo: [due_date]
------------------------
Harap segera melakukan pembayaran untuk menghindari pemutusan layanan.

[footer]`,
            "Payment Paid": savedTemplates["Payment Paid"]?.content || `Yth. Pelanggan [full_name]

Pembayaran untuk invoice berikut telah berhasil diterima:
------------------------
Nomor Invoice: [no_invoice]
Jumlah Dibayar: Rp [total]
Tanggal: [invoice_date]
------------------------
Terima kasih atas pembayaran anda.

[footer]`,
            "Payment Cancel": savedTemplates["Payment Cancel"]?.content || `Yth. Pelanggan [full_name]

Pembayaran anda dengan rincian berikut telah dibatalkan:
------------------------
Nomor Invoice: [no_invoice]
Jumlah: Rp [total]
------------------------
Silakan hubungi layanan pelanggan jika ini tidak sesuai.

[footer]`,
            "Account Suspend": savedTemplates["Account Suspend"]?.content || `Yth. Pelanggan [full_name]

Akun anda telah ditangguhkan karena alasan berikut:
------------------------
Status: Suspend
Nomor Invoice: [no_invoice]
------------------------
Silakan selesaikan tagihan anda agar layanan kembali aktif.

[footer]`,
            "Account Active": savedTemplates["Account Active"]?.content || `Yth. Pelanggan [full_name]

Selamat! Akun anda telah aktif kembali.
------------------------
Status: Aktif
------------------------
Terima kasih telah melakukan pembayaran.

[footer]`
        };

        document.querySelector('select[name="name"]').addEventListener('change', function () {
            const selected = this.value;
            document.querySelector('textarea[name="content"]').value = defaultTemplates[selected] || '';
        });

        document.querySelector('button[type="reset"]').addEventListener('click', function (e) {
            e.preventDefault();
            const selected = document.querySelector('select[name="name"]').value;
            document.querySelector('textarea[name="content"]').value = defaultTemplates[selected] || '';
        });

        window.addEventListener('DOMContentLoaded', () => {
            const select = document.querySelector('select[name="name"]');
            const selected = select.value;
            if (selected && defaultTemplates[selected]) {
                document.querySelector('textarea[name="content"]').value = defaultTemplates[selected];
            }
        });
    </script>
</body>

</html>
