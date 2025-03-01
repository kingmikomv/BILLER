<x-login.header />
<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="{{ url('/login') }}" class="h1">
        <img src="{{ asset('assetlogin/re.png') }}" alt="BILLER Logo" class="img-fluid" style="max-height: 130px;">

      </a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Verifikasi Email</p>

      @if (session('resent'))
        <div class="alert alert-success" role="alert">
          Link Verifikasi baru telah dikirim ke alamat email Anda.
        </div>
      @endif

      <p class="mb-1"> 
        Sebelum Di Proses, Silahkan Cek Email Anda Untuk Link Verifikasi. Jika Anda Tidak Menerima Email,
        <a href="{{ route('verification.send') }}">Klik Disini Untuk Mengirim Link Verifikasi Baru</a>.
      </p>

      <p class="mb-0">
        <a href="{{ route('login') }}" class="text-center">Login</a>
      </p>
    </div>
  </div>
</div>

<x-login.script />
