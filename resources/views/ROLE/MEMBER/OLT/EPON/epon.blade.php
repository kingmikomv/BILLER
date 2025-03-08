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
                                    <h3 class="card-title">Data OLT EPON</h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambahOLT">
                                            <i class="fas fa-plus"></i> Tambah OLT EPON
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body table-responsive">
                                    <table id="dataOLTTable" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Akses</th>
                                                <th>Site</th>
                                                <th>Url OLT</th>
                                                <th>Option</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $no = 1; @endphp
                                            @foreach ($dataOLT as $olt)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>
                                                        <a href="http://id-1.aqtnetwork.my.id:{{ $olt->portvpn }}" target="_blank" class="btn btn-sm btn-primary">
                                                            Akses OLT
                                                        </a>
                                                    </td>
                                                    <td>{{ $olt->namaolt }}</td>
                                                    <td>
                                                        <span id="urlOlt-{{ $olt->id }}">id-1.aqtnetwork.my.id:{{ $olt->portvpn }}</span>
                                                        <button class="btn btn-sm btn-secondary copy-url-btn" data-id="{{ $olt->id }}">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                                                Actions
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item copy-script-btn" href="#" 
                                                                data-ipolt="{{ $olt->ipolt }}" 
                                                                data-portvpn="{{ $olt->portvpn }}" 
                                                                data-portolt="{{ $olt->portolt }}"
                                                                data-namaolt="{{ $olt->namaolt }}">
                                                                Copy Script
                                                            </a>
                                                                <a class="dropdown-item edit-btn" href="#" data-id="{{ $olt->id }}">Edit</a>
                                                                <a class="dropdown-item delete-btn" href="#" data-id="{{ $olt->id }}">Delete</a>
                                                              
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
            </section>
        </div>

        <div class="modal fade" id="modalTambahOLT" tabindex="-1" role="dialog" aria-labelledby="modalTambahOLTLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahOLTLabel">Tambah OLT EPON</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formTambahOLT" method="POST" action="{{ route('tambah.olt.epon') }}">
                            @csrf                            
                            <div class="form-group">
                                <label for="namaOLT">Nama OLT</label>
                                <input type="text" class="form-control" id="namaOLT" name="namaOLT" placeholder="Nama OLT / Site OLT" required>
                            </div>
                            <div class="form-group">
                                <label for="ipOLT">IP OLT</label>
                                <input type="text" class="form-control" id="ipOLT" name="ipOLT" placeholder="IP Local OLT" required>
                            </div>
                            <div class="form-group">
                                <label for="portOLT">Port OLT</label>
                                <input type="text" class="form-control" id="portOLT" name="portOLT" placeholder="Port OLT" required>
                            </div>
                            <div class="form-group">
                                <label for="siteMikrotik">Site Mikrotik</label>
                                <select name="site" class="form-control">
                                    <option disable selected value>Pilih Mikrotik</option>
                                    @foreach($dataSite as $mikrotik)
                                    <option value="{{$mikrotik->remote_ip}}">{{$mikrotik->site}}</option>
                                    @endforeach
                                </select>
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
    <x-dhs.scripts />
    <script>
        $(document).ready(function() {
            if (!$.fn.DataTable.isDataTable('#dataOLTTable')) {
                $('#dataOLTTable').DataTable();
            }

            function generateScript(ipolt, portvpn, portolt, namaolt) {
                const script = `/ip firewall nat add chain=dstnat comment="Generated by Biller - ${namaolt}" dst-port=${portvpn} protocol=tcp action=dst-nat to-addresses=${ipolt} to-ports=${portolt}`;
                navigator.clipboard.writeText(script).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Script berhasil disalin ke clipboard.',
                        timer: 2000
                    });
                });
            }

            $(document).on('click', '.copy-script-btn', function(event) {
                event.preventDefault();
                let ipolt = $(this).data('ipolt');
                let portvpn = $(this).data('portvpn');
                let portolt = $(this).data('portolt');
                let namaolt = $(this).data('namaolt');
                generateScript(ipolt, portvpn, portolt, namaolt);
            });
            

            $(document).on('click', '.copy-url-btn', function() {
            var id = $(this).data('id');
            var textToCopy = document.getElementById("urlOlt-" + id).innerText;

            // Membuat elemen textarea sementara untuk menyalin teks
            var tempInput = document.createElement("textarea");
            tempInput.value = textToCopy;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand("copy");
            document.body.removeChild(tempInput);

        });
        });
    </script>
</body>
</html>
