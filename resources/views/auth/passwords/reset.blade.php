<x-login.header />
<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="{{ url('/login') }}" class="h1"><b>Admin</b>LTE</a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Reset your password</p>

      <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="input-group mb-3">
          <input 
            type="email" 
            class="form-control @error('email') is-invalid @enderror" 
            name="email" 
            value="{{ old('email', $email) }}" 
            placeholder="Email Address" 
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

        <div class="input-group mb-3">
          <input 
            type="password" 
            class="form-control @error('password') is-invalid @enderror" 
            name="password" 
            placeholder="New Password" 
            required 
            autocomplete="new-password"
          >
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

        <div class="input-group mb-3">
          <input 
            type="password" 
            class="form-control @error('password_confirmation') is-invalid @enderror" 
            name="password_confirmation" 
            placeholder="Confirm Password" 
            required 
            autocomplete="new-password"
          >
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>

        @error('password_confirmation')
          <div class="alert alert-danger mt-2">
            <strong>{{ $message }}</strong>
          </div>
        @enderror

        <div class="row">
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">Reset</button>
          </div>
        </div>
      </form>

    </div>
  </div>
</div>

<x-login.script />
