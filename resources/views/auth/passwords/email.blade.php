<x-login.header />
<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="{{ url('/login') }}" class="h1"><b>Admin</b>LTE</a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Forgot your password? Reset it here.</p>

      @if (session('status'))
        <div class="alert alert-success" role="alert">
          {{ session('status') }}
        </div>
      @endif

      <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="input-group mb-3">
          <input 
            type="email" 
            class="form-control @error('email') is-invalid @enderror" 
            placeholder="Email Address" 
            name="email" 
            value="{{ old('email') }}" 
            required 
            autocomplete="email" 
            autofocus
          >
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>

        @error('email')
          <div class="alert alert-danger mt-2">
            <strong>{{ $message }}</strong>
          </div>
        @enderror

        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Send Password Reset Link</button>
          </div>
        </div>
      </form>

      <p class="mb-0">
        <a href="{{ route('login') }}" class="text-center">Remember your password? Login</a>
      </p>

    </div>
  </div>
</div>

<x-login.script />
