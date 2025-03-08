<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{route('home')}}" class="brand-link" style="margin-bottom: 200px">
      <img src="{{asset('assetlogin/icon.png')}}" alt="AdminLTE Logo" class="brand-image">
      <span class="brand-text font-weight">BILLER</span>

  </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://ui-avatars.com/api/?name={{Auth()->user()->name}}" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block">{{Auth()->user()->name}} - {{Auth()->user()->role}}</a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="true">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
         
            <!-- DASHBOARD -->
          <li class="nav-item">
            <a href="{{route('home')}}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
              </p>
            </a>
          </li>


          <li class="nav-item">
            <a href="{{route('pelanggan')}}" class="nav-link {{ request()->routeIs('pelanggan') ? 'active' : '' }}">
              <i class="nav-icon fas fa-user-plus"></i>
              <p>
                Data Pelanggan
              </p>
            </a>
          </li>
          @if(auth()->user()->role == 'teknisi')

          <li class="nav-item">
            <a href="{{route('datapsb')}}" class="nav-link {{ request()->routeIs('datapsb') ? 'active' : '' }}">
              <i class="nav-icon fas fa-binoculars"></i>
              <p>
                Data PSB
              </p>
            </a>
          </li>
          @endif
          @if(in_array(auth()->user()->role, ['member', 'cs', 'penagih']))
          <!-- TAGIHAN -->
          <li class="nav-item {{ request()->routeIs('bil_pelanggan') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('bil_pelanggan') ? 'active' : '' }}">
              <i class="nav-icon fas fa-cash-register"></i>
              <p>
                Billing
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('bil_pelanggan')}}" class="nav-link {{ request()->routeIs('bil_pelanggan') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Pelanggan</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/charts/chartjs.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Belum Lunas</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/charts/flot.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Lunas</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/charts/inline.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Riwayat Tagihan</p>
                </a>
              </li>
            
            </ul>
          </li>
          @endif
          
          @if(in_array(auth()->user()->role, ['member']))
          <!-- INTERNET PLAN -->
          <li class="nav-item {{ request()->routeIs('member.pppoe') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('member.pppoe') ? 'active' : '' }}">
              <i class="nav-icon fas fa-tasks"></i>
              <p>
                Internet Plan
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('member.pppoe')}}" class="nav-link {{ request()->routeIs('member.pppoe') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>PPPoE Profile</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/UI/icons.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Hotspot Profile</p>
                </a>
              </li>
              
            </ul>
          </li>
          @endif
          <!-- NETWORK -->
          @if(in_array(auth()->user()->role, ['member', 'teknisi']))
          <li class="nav-item {{ request()->routeIs('member.router') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('member.router') ? 'active' : '' }}">
              <i class="nav-icon fas fa-network-wired"></i>
              <p>
                Network
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('member.router')}}" class="nav-link {{ request()->routeIs('member.router') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Router MikroTik</p>
                </a>
              </li>
             
              @if(in_array(auth()->user()->role, ['member']))

              <li class="nav-item">
                <a href="pages/forms/editors.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>RADIUS</p>
                </a>
              </li>
              
              <li class="nav-item">
                <a href="pages/UI/icons.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>VPN</p>
                </a>
              </li>
              @endif
            </ul>
          </li>

          <li class="nav-item {{ request()->routeIs('olt.epon') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('olt.epon') ? 'active' : '' }}">
              <i class="nav-icon fas fa-chart-line"></i>
              <p>
                OLT
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('olt.epon')}}" class="nav-link {{ request()->routeIs('olt.epon') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>EPON</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/forms/advanced.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>GPON</p>
                </a>
              </li>
              
            </ul>
          </li>
          
          @endif
          <!-- PENGATURAN -->
          <li class="nav-header">Pengaturan</li>
          @if(auth()->user()->role == 'member')
          <li class="nav-item {{ request()->routeIs('pekerja') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('pekerja') ? 'active' : '' }}">
              <i class="nav-icon fas fa-users"></i>
              <p>
                Pekerja
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="{{route('pekerja')}}" class="nav-link {{ request()->routeIs('pekerja') ? 'active' : '' }}">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Daftar Pekerja</p>
                </a>
              </li>
            </ul>
          </li>
          @endif
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-user"></i>
              <p>
                Profil
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Akun Saya</p>
                </a>
              </li>
              <li class="nav-item">
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                  @csrf
              </form>
              <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  <i class="fas fa-sign-out-alt nav-icon"></i>
                  <p>Logout</p>
              </a>
              </li>
            </ul>
          </li>


         
         
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>