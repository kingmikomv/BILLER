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
                                <div class="card-header border-transparent">
                                    <h3 class="card-title">User Hotspot</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body table-responsive">
                                    <!-- Tombol New Profile -->
                                    <button class="btn btn-primary mb-3" data-toggle="modal"
                                        data-target="#modalTambahVoucher">
                                        + New User
                                    </button>
                                    <div class="row mb-3">
                                        <div class="col-md-2 mt-2">
                                            <select class="form-control bg-dark text-white border-secondary"
                                                name="nas" id="nasFilter">
                                                <option value="">Nas</option>
                                                @foreach ($nasList as $nas)
                                                    <option value="{{ $nas->username }}">{{ $nas->username }} -
                                                        {{ $nas->remote_address }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 mt-2">
                                            <select class="form-control bg-dark text-white border-secondary"
                                                name="owner" id="ownerFilter">
                                                <option value="">Owner</option>
                                                {{-- Tambahkan opsi Owner disini jika ada --}}
                                            </select>
                                        </div>
                                        <div class="col-md-2 mt-2">
                                            <select class="form-control bg-dark text-white border-secondary"
                                                name="status" id="statusFilter">
                                                <option value="">Status</option>
                                                <option value="active">Active</option>
                                                <option value="used">Used</option>
                                                <option value="expired">Expired</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mt-2">
                                            <select class="form-control bg-dark text-white border-secondary"
                                                name="profile" id="profileFilter">
                                                <option value="">Profile</option>
                                                @foreach ($profiles as $profile)
                                                    <option value="{{ $profile->name }}">{{ $profile->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 mt-2">
                                            <input type="date"
                                                class="form-control bg-dark text-white border-secondary" name="created"
                                                id="createdFilter" />
                                        </div>
                                    </div>

                                    <table id="user-hotspot-table" class="table table-hover text-white">
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" id="selectAllUsers"
                                                        aria-label="Select all users" /></th>
                                                <th>#</th>
                                                <th>Username</th>
                                                <th>Password</th>
                                                <th>Profile</th>
                                                <th>Nas</th>
                                                <th>Server</th>
                                                <th>Created</th>
                                                <th>Owner</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($vouchers as $index => $voucher)
                                                <tr>
                                                    <td><input type="checkbox" class="user-checkbox"
                                                            aria-label="Select user {{ $voucher->username }}" /></td>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $voucher->username }}</td>
                                                    <td>{{ $voucher->password ?? '-' }}</td>
                                                    <td>{{ $voucher->hotspotProfile->name ?? '-' }}</td>
                                                    <td>{{ $voucher->nas ?? 'all' }}</td>
                                                    <td>{{ $voucher->server ?? 'all' }}</td>
                                                    <td>{{ $voucher->created_at->format('H:i:s d/m/Y') }}</td>
                                                    <td>{{ $voucher->owner ?? '-' }}</td>
                                                    <td>
                                                        @if ($voucher->status === 'active')
                                                            <span class="badge badge-primary">Active</span>
                                                        @elseif ($voucher->status === 'used')
                                                            <span class="badge badge-success">Used</span>
                                                        @else
                                                            <span
                                                                class="badge badge-danger">{{ $voucher->status }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                </div>
                                <!-- /.card-body -->
                            </div>

                        </div>

                    </div>

                </div>

            </section>
            <!-- /.content -->
        </div>

        <!-- Modal Generate Voucher Hotspot -->
       <!-- Modal Tambah Voucher -->
<div class="modal fade" id="modalTambahVoucher" tabindex="-1" role="dialog" aria-labelledby="modalTambahVoucherLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form action="{{route('hotspot.tambahVoucher')}}" method="POST">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Voucher</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        
        <div class="modal-body">
          {{-- NAS ID --}}
          <div class="form-group">
            <label for="nas_id">Pilih NAS</label>
            <select name="nas_id" class="form-control" required>
              <option value="all">Semua NAS</option>
              @foreach($nasList as $nas)
                <option value="{{ $nas->remote_address }}">{{ $nas->username }}</option>
              @endforeach
            </select>
          </div>

          {{-- Hotspot Profile ID --}}
          <div class="form-group">
            <label for="hotspot_profile_id">Hotspot Profile</label>
            <select name="hotspot_profile_id" class="form-control" required>
              @foreach($profiles as $profile)
                <option value="{{ $profile->id }}">{{ $profile->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- Quantity --}}
          <div class="form-group">
            <label for="quantity">Jumlah Voucher</label>
            <input type="number" name="quantity" class="form-control" min="1" value="1" required>
          </div>

          {{-- User Model --}}
          <div class="form-group">
            <label for="user_model">Model Username/Password</label>
            <select name="user_model" class="form-control" required>
              <option value="username_equals_password">Username = Password</option>
              <option value="username_plus_password">Username + Password (Random)</option>
            </select>
          </div>

          {{-- Character Type --}}
          <div class="form-group">
            <label for="char_type">Tipe Karakter</label>
            <select name="char_type" class="form-control" required>
              <option value="uppercase">Huruf Besar</option>
              <option value="lowercase">Huruf Kecil</option>
              <option value="numbers">Angka</option>
              <option value="uppercase_numbers">Huruf Besar + Angka</option>
              <option value="lowercase_numbers">Huruf Kecil + Angka</option>
              <option value="all">Semua Karakter</option>
            </select>
          </div>

          {{-- Prefix --}}
          <div class="form-group">
            <label for="prefix">Prefix (Opsional)</label>
            <input type="text" name="prefix" class="form-control" placeholder="Contoh: VCR_">
          </div>

          {{-- Length --}}
          <div class="form-group">
            <label for="length">Panjang Karakter</label>
            <input type="number" name="length" class="form-control" min="4" max="32" value="8" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Buat Voucher</button>
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
        $(document).ready(function() {
            // Init DataTable
            var table = $('#user-hotspot-table').DataTable({
                responsive: true,
                order: [
                    [1, 'asc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: 0
                }],
            });

            // Select/Deselect All checkboxes
            $('#selectAllUsers').on('click', function() {
                var checked = this.checked;
                $('.user-checkbox').each(function() {
                    this.checked = checked;
                });
            });

            // Update price and reseller price saat profile dipilih
            $('#hotspot_profile_id').change(function() {
                var selected = $(this).find('option:selected');
                var price = selected.data('price') || 0;
                var resellerPrice = selected.data('reseller') || 0;
                $('#price').val(price);
                $('#reseller_price').val(resellerPrice);
            });

            // Custom filtering function untuk DataTables
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var nasVal = $('#nasFilter').val();
                var ownerVal = $('#ownerFilter').val();
                var statusVal = $('#statusFilter').val().toLowerCase();
                var profileVal = $('#profileFilter').val();
                var createdVal = $('#createdFilter').val();

                var nas = data[5]; // Nas
                var owner = data[8]; // Owner
                var status = data[9].toLowerCase(); // Status
                var profile = data[4]; // Profile
                var created = data[7]; // Created (format: H:i:s d/m/Y)

                // Filter Nas
                if (nasVal && nas !== nasVal) {
                    return false;
                }
                // Filter Owner
                if (ownerVal && owner !== ownerVal) {
                    return false;
                }
                // Filter Status (partial match)
                if (statusVal && !status.includes(statusVal)) {
                    return false;
                }
                // Filter Profile
                if (profileVal && profile !== profileVal) {
                    return false;
                }
                // Filter Created by date (YYYY-MM-DD) - cek apakah tanggal di kolom created mengandung createdVal
                if (createdVal) {
                    // Ambil bagian tanggal: d/m/Y dari created string
                    var createdDateParts = created.split(' ')[1].split('/');
                    var createdDateFormatted = createdDateParts[2] + '-' + createdDateParts[1] + '-' +
                        createdDateParts[0];
                    if (createdDateFormatted !== createdVal) {
                        return false;
                    }
                }

                return true;
            });

            // Event filter untuk semua filter
            $('#nasFilter, #ownerFilter, #statusFilter, #profileFilter, #createdFilter').on('change', function() {
                table.draw();
            });
        });
    </script>

</body>

</html>
