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
                                    <!-- Form Input -->
                                    <div class="col-md-6">
                                        <div class="card-body">
                                            <form method="POST" action="{{ route('whatsapp.store') }}">
                                                @csrf
                                                <div class="form-group">
                                                    <label for="template_type">Type</label>
                                                    <select name="name" class="form-control" required>
                                                        <option disabled selected value>-- PILIH TYPE --</option>
                                                        <option value="Invoice Terbit" >Invoice Terbit</option>
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

                                    <!-- Kode Variabel -->
                                    <div class="col-md-6">
                                        <div class="card-header">
                                            <strong>Kode Variable Invoice Terbit</strong>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled">
                                                <li><code>[full_name]</code> Nama lengkap</li>
                                                <li><code>[uid]</code> ID Pelanggan</li>
                                                <li><code>[pppoe_user]</code> Username PPPoE</li>
                                                <li><code>[pppoe_pass]</code> Password PPPoE</li>
                                                <li><code>[pppoe_profile]</code> Profile PPPoE</li>
                                                <li><code>[no_invoice]</code> Nomor Invoice</li>
                                                <li><code>[invoice_date]</code> Tanggal Invoice</li>
                                                <li><code>[amount]</code> Jumlah</li>
                                                <li><code>[ppn]</code> PPN</li>
                                                <li><code>[discount]</code> Diskon</li>
                                                <li><code>[total]</code> Total</li>
                                                <li><code>[period]</code> Periode</li>
                                                <li><code>[due_date]</code> Jatuh tempo</li>
                                                <li><code>[payment_gateway]</code> Link metode pembayaran</li>
                                                <li><code>[payment_mutasi]</code> Metode mutasi bank</li>
                                                <li><code>[footer]</code> Signature</li>
                                            </ul>
                                            <small>
                                                Gunakan <code>*contoh*</code> untuk <strong>bold</strong>, dan
                                                <code>_contoh_</code> untuk <em>italic</em>.
                                            </small>
                                        </div>
                                    </div>
                                </div> <!-- .row -->
                            </div> <!-- .card -->
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
    
        // ✅ Set default text saat pertama kali halaman dimuat (jika ada opsi default terpilih)
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
