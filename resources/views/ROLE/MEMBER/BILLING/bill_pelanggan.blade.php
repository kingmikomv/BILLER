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
                    <h3 class="card-title">Data Pelanggan</h3>
                </div>
                <!-- /.card-header -->
                
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="exampleTable" class="table table-striped table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Pelanggan</th>
                                    <th>Nama Pelanggan</th>
                                    <th>Nomor Telp.</th>
                                    <th>Tgl Daftar</th>
                                    <th>Paket</th>
                                    <th>Akun PPPoE</th>
                                    <th>Pass PPPoE</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pelanggan as $index => $data)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $data->pelanggan_id }}</td>
                                        <td>{{ $data->nama_pelanggan }}</td>
                                        <td>{{ $data->nomor_telepon }}</td>
                                        <td>{{ $data->tanggal_daftar }}</td>
                                        <td>{{ $data->paket->nama_paket ?? 'Tidak Ada Paket' }}</td>
                                        <td>{{ $data->akun_pppoe }}</td>
                                        <td>{{ $data->password_pppoe }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-primary btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <a href="https://wa.me/{{ $data->nomor_telepon }}?text=Halo%20{{ urlencode($data->nama_pelanggan) }},%20kami%20ingin%20menghubungi%20Anda."
                                                    target="_blank"
                                                    class="btn btn-success btn-sm" title="Kirim WhatsApp">
                                                    <i class="fab fa-whatsapp"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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
   
<script>
    $(document).ready(function() {
        $('#exampleTable').DataTable({
            "responsive": true,
            "autoWidth": false
        });
    });
</script>
</body>
</html>
