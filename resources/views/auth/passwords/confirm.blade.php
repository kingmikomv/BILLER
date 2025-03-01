<x-login.header />
<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="{{ url('/login') }}" class="h1">
        <img src="{{ asset('assetlogin/re.png') }}" alt="BILLER Logo" class="img-fluid" style="max-height: 130px;">

      </a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Please confirm your password before continuing.</p>

      <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="input-group mb-3">
          <input id="password" 
                 type="password" 
                 class="form-control @error('password') is-invalid @enderror" 
                 name="password" 
                 required 
                 autocomplete="current-password" 
                 placeholder="Password">

          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>

        @error('password')
          <div class="alert alert-danger mt-2">
            <strong>{{ $message }}</strong>
          </div>
        @enderror

        <div class="row">
          <div class="col-8">
            @if (Route::has('password.request'))
              <a class="btn btn-link" href="{{ route('password.request') }}">
                Forgot Your Password?
              </a>
            @endif
          </div>
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Confirm Password</button>
          </div>
        </div>
      </form>

    </div>
  </div>
</div>

<x-login.script />
