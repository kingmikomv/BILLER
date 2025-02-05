<x-login.header />
<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="{{ url('/login') }}" class="h1"><b>Admin</b>LTE</a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Verify your email address</p>

      @if (session('resent'))
        <div class="alert alert-success" role="alert">
          A fresh verification link has been sent to your email address.
        </div>
      @endif

      <p class="mb-1">
        Before proceeding, please check your email for a verification link. If you did not receive the email, 
        <a href="{{ route('verification.send') }}">click here to request another</a>.
      </p>

      <p class="mb-0">
        <a href="{{ route('login') }}" class="text-center">Login</a>
      </p>
    </div>
  </div>
</div>

<x-login.script />
