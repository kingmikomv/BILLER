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
        <x-dhs.info-box />
       
        <div class="row">
        
          <div class="col-md-12">
           


            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Menu Cepat</h3>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body ">
               
                        <div class="row">
                            <div class="col-md-6 mt-2">
                                <a href="{{route('tambah_psb_sales')}}" class="btn btn-success btn-block">Tambah Data PSB</a>
                            </div>
                            <div class="col-md-6 mt-2">
                                <a href="{{route('data_psbsales')}}" class="btn btn-secondary btn-block"> Data PSB</a>

                            </div>
                        
                 
                </div><!-- /.d-md-flex -->
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

</body>
</html>
