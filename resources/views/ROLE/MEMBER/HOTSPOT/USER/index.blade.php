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
                                    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#newUser">
                                        + New User
                                    </button>

                                    <!-- Tempatkan di sini nanti tabel data profile -->
                                    <table id="user-hotspot-table" class="table table-bordered table-hover text-white">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Username</th>
                                                <th>Password</th>
                                                <th>Profile</th>
                                                <th>Masa Aktif</th>
                                                <th>Batas Waktu</th>
                                                <th>Status</th>
                                                <th>Dibuat</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($vouchers as $index => $voucher)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $voucher->username }}</td>
                                                    <td>{{ $voucher->password }}</td>
                                                    <td>{{ $voucher->hotspotProfile->name ?? '-' }}</td>
                                                    <!-- Tampilkan Session Timeout dalam format HH:MM:SS -->
                                                    <td>
                                                        @if ($voucher->hotspotProfile && $voucher->hotspotProfile->validity && $voucher->created_at)
                                                            {{ \Carbon\Carbon::parse($voucher->created_at)->addDays($voucher->hotspotProfile->validity)->format('d M Y H:i') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $uptime = $voucher->hotspotProfile->uptime ?? 0;
                                                            $days = floor($uptime / 24);
                                                            $hours = $uptime % 24;
                                                            $result = '';

                                                            if ($days > 0) {
                                                                $result .= $days . ' hari';
                                                            }
                                                            if ($hours > 0) {
                                                                if ($days > 0) {
                                                                    $result .= ' ';
                                                                }
                                                                $result .= $hours . ' jam';
                                                            }

                                                            if ($result === '') {
                                                                $result = '-';
                                                            }
                                                        @endphp

                                                        {{ $result }}
                                                    </td>


                                                    <!-- Tampilkan Expiration Date dari validitas (misalnya dari created_at + validity hari) -->


                                                    <td>
                                                        @if ($voucher->status === 'used')
                                                            <span class="badge badge-danger">Terpakai</span>
                                                        @else
                                                            <span class="badge badge-success">Aktif</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $voucher->created_at->format('d M Y H:i') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                </div>
                                <!-- /.card-body -->

                                <!-- /.card-footer -->
                            </div>


                        </div>


                    </div>


                </div>

            </section>
            <!-- /.content -->
        </div>
        <!-- Modal -->

        <!-- Modal Generate Voucher Hotspot -->
        <div class="modal fade" id="newUser" tabindex="-1" role="dialog" aria-labelledby="newUserLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <form method="POST" action="{{ route('hotspot.tambahVoucher') }}">
                    @csrf
                    <div class="modal-content bg-dark text-white">
                        <div class="modal-header">
                            <h5 class="modal-title" id="newUserLabel">Generate Hotspot Voucher</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">

                            <div class="form-group mb-3">
                                <label for="quantity">Jumlah Voucher</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" min="1"
                                    max="1000" value="50" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="user_model">User Model</label>
                                <select name="user_model" id="user_model" class="form-control" required>
                                    <option value="username_equals_password">Username = Password</option>
                                    <option value="username_plus_password">Username + Password</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="char_type">Karakter Username</label>
                                <select name="char_type" id="char_type" class="form-control" required>
                                    <option value="uppercase">Huruf Besar (A-Z)</option>
                                    <option value="lowercase">Huruf Kecil (a-z)</option>
                                    <option value="numbers">Angka (0-9)</option>
                                    <option value="uppercase_numbers">Huruf Besar + Angka</option>
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="hotspot_profile_id">Pilih Profile</label>
                                <select name="hotspot_profile_id" id="hotspot_profile_id" class="form-control" required>
                                    <option value="" disabled selected>-- Pilih Hotspot Profile --</option>
                                    @foreach ($profiles as $profile)
                                        <option value="{{ $profile->id }}">
                                            {{ $profile->name }} - Rp {{ number_format($profile->price, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mb-3">
                                <label for="prefix">Prefix Username (opsional)</label>
                                <input type="text" name="prefix" id="prefix" class="form-control"
                                    placeholder="Misal: CMI">
                            </div>
                            <div class="form-group mb-3">
                                <label for="length">Panjang Karakter</label>
                                <input type="number" name="length" id="length" class="form-control" value="6"
                                    min="4" max="12" required>
                            </div>

                        </div>
                        <div class="modal-footer border-top">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">Generate Voucher</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <x-dhs.footer />
    </div>
    <!-- ./wrapper -->

    <x-dhs.scripts />
    <x-dhs.alert />
    <script>
        $(document).ready(function() {
            $('#user-hotspot-table').DataTable({
                "responsive": true,
                "autoWidth": false,
                "order": [
                    [0, "asc"]
                ]
            });
        });
    </script>
</body>

</html>
