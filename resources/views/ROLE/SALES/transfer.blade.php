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
                                    <h3 class="card-title">Formulir Penambahan Pelanggan</h3>

                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <form method="POST" action="{{ route('transfer', $psb->id) }}">
                                        @csrf
                                    
                                        <!-- Pilihan Paket -->
                                        <fieldset class="border p-3 mb-3">
                                            <legend class="w-auto px-2">Pilih Paket</legend>
                                            <div class="form-group">
                                                <label for="pilihPaket">Pilih Paket</label>
                                                <select class="form-control" name="kodePaket" required>
                                                    <option value="">Pilih Paket</option>
                                                    @foreach($paketPPPoEs as $paket)
                                                        <option value="{{ $paket->kode_paket }}">
                                                            {{ $paket->nama_paket }} - Rp. {{ number_format($paket->harga_paket, 0, ',', '.') }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="akunPppoe">Akun PPPoE</label>
                                                <input type="text" class="form-control" id="akunPppoe" name="akunPppoe" required placeholder="contoh:akun@123">
                                            </div>
                                            <div class="form-group">
                                                <label for="passwordPppoe">Password PPPoE</label>
                                                <input type="text" class="form-control" id="passwordPppoe" name="passwordPppoe" required placeholder="Password PPPoE">
                                            </div>
                                            <div class="form-group">
                                                <label for="passwordPppoe">SSID WiFI</label>
                                                <input type="text" class="form-control" id="ssid" name="ssid" required placeholder="SSID WiFi">
                                            </div>
                                            <div class="form-group">
                                                <label for="passwordPppoe">Password WiFI</label>
                                                <input type="text" class="form-control" id="passwifi" name="passwifi" required placeholder="Password WiFi">
                                            </div>
                                            
                                            
                                            <button type="button" class="btn btn-primary btn-block" id="toggleAdvance">Advance Form</button>
                                            
                                            <div id="advanceForm" style="display: none;">
                                                <div class="form-group">
                                                    <label for="passwordPppoe">Serial Number (Optional)</label>
                                                    <input type="text" class="form-control" id="serial" name="serialnumber" placeholder="Serial Number">
                                                </div>
                                                <div class="form-group">
                                                    <label for="passwordPppoe">MAC Address (Optional)</label>
                                                    <input type="text" class="form-control" id="macaddress" name="macaddress" placeholder="Mac Address">
                                                </div>
                                                <div class="form-group">
                                                    <label for="passwordPppoe">ODP (Optional)</label>
                                                    <input type="text" class="form-control" id="odp" name="odp" placeholder="ODP">
                                                </div>
                                                <div class="form-group">
                                                    <label for="passwordPppoe">OLT (Optional)</label>
                                                    <input type="text" class="form-control" id="olt" name="olt" placeholder="OLT">
                                                </div>
                                            </div>
                                            
                                        

                                        </fieldset>
                                    
                                        <!-- Informasi Pelanggan -->
                                        <fieldset class="border p-3 mb-3">
                                            <legend class="w-auto px-2">Informasi Pelanggan</legend>
                                            <div class="form-group">
                                                <label for="namaPelanggan">Nama Pelanggan</label>
                                                <input type="text" class="form-control" id="namaPelanggan" name="namaPelanggan" required placeholder="Nama Pelanggan" value="{{$psb->nama_psb}}">
                                            </div>

                                            <div class="form-group">
                                                <label for="telepon">Nomor Telepon</label>
                                                <input type="text" class="form-control" id="telepon" name="telepon" required placeholder="Nomor Telepon" value="{{$psb->telepon}}">
                                            </div>
                                            <div class="form-group">
                                                <label for="telepon">Tanggal Ingin Pasang</label>
                                                <input type="date" class="form-control" id="tanggalinginpasang" name="tip" required placeholder="Tanggal Ingin Pasang" value="{{$psb->tanggal_ingin_pasang}}">
                                            </div>
                                            <div class="form-group">
                                                <label for="alamat">Alamat</label>
                                                <textarea class="form-control" id="alamat" name="alamat" required placeholder="Alamat">{{$psb->alamat_psb}}</textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="tipePembayaran">Tipe Pembayaran</label>
                                                <select class="form-control" id="tipePembayaran" name="metode_pembayaran" required onchange="toggleInvoice()">
                                                    <option value="">Pilih Tipe Pembayaran</option>
                                                    <option value="Prabayar">Prabayar</option>
                                                    <option value="Pascabayar">Pascabayar</option>
                                                </select>
                                            </div>
                                        </fieldset>
                                    
                                        <!-- Invoice Section (Tampil Jika Prabayar) -->
                                        <fieldset class="border p-3 mb-3 d-none" id="invoiceSection">
                                            <legend class="w-auto px-2">Invoice</legend>
                                            <div class="form-group">
                                                <label for="invoiceAmount">Jumlah Tagihan</label>
                                                <input type="text" class="form-control" id="invoiceAmount" name="invoiceAmount" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label for="invoiceDate">Tanggal Invoice</label>
                                                <input type="date" class="form-control" id="invoiceDate" name="invoiceDate" value="{{ date('Y-m-d') }}" readonly>
                                            </div>
                                        </fieldset>
                                    
                                        <button type="submit" class="btn btn-success">Tambah Pelanggan</button>
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

        <x-dhs.footer />
    </div>
    <!-- ./wrapper -->

    <x-dhs.scripts />
    <x-dhs.alert />

    <script>
        function toggleInvoice() {
            let tipe = document.getElementById("tipePembayaran").value;
            let invoiceSection = document.getElementById("invoiceSection");
    
            if (tipe === "prabayar") {
                invoiceSection.classList.remove("d-none");
                
                // Ambil harga dari paket yang dipilih
                let paketSelect = document.querySelector("[name='kodePaket']");
                let selectedOption = paketSelect.options[paketSelect.selectedIndex].text;
                let harga = selectedOption.match(/Rp\. ([\d.]+)/);
                
                if (harga) {
                    document.getElementById("invoiceAmount").value = harga[1].replace('.', '');
                }
            } else {
                invoiceSection.classList.add("d-none");
            }
        }
    </script>
     <script>
        $(document).ready(function() {
            $("#toggleAdvance").click(function() {
                $("#advanceForm").slideToggle();
            });
        });
    </script>
</body>

</html>
