<x-login.header />

 <div class="login-box">
   <!-- /.login-logo -->
   <div class="card card-outline card-primary">
     <div class="card-header text-center">
       <a href="{{ url('/login') }}" class="h1">
        <img src="{{ asset('assetlogin/re.png') }}" alt="BILLER Logo" class="img-fluid" style="max-height: 130px;">

      </a>
     </div>
     <div class="card-body">
       <p class="login-box-msg">Login Disini</p>
 
       <form method="POST" action="{{ route('login') }}">
         @csrf
 
         <div class="input-group mb-3">
          <input 
              type="text" 
              class="form-control @error('login') is-invalid @enderror" 
              placeholder="Email atau Username" 
              value="{{ old('login') }}" 
              name="login" 
              autocomplete="username" 
              autofocus
          >
          <div class="input-group-append">
              <div class="input-group-text">
                  <span class="fas fa-user"></span>
              </div>
          </div>
          @error('login')
              <div class="alert alert-danger mt-2">
                  <strong>{{ $message }}</strong>
              </div>
          @enderror
      </div>
      
 
         <div class="input-group mb-3">
           <input 
             type="password" 
             class="form-control @error('password') is-invalid @enderror" 
             placeholder="Password" 
             name="password" 
             required 
             autocomplete="current-password"
           >
           <div class="input-group-append">
             <div class="input-group-text">
               <span class="fas fa-lock"></span>
             </div>
           </div>
           @error('password')
             <div class="alert alert-danger mt-2">
               <strong>{{ $message }}</strong>
             </div>
           @enderror
         </div>
 
         <div class="row">
           <div class="col-8">
             <div class="icheck-primary">
               <input 
                 type="checkbox" 
                 name="remember" 
                 id="remember" 
                 {{ old('remember') ? 'checked' : '' }}
               >
               <label for="remember">
                 Ingat Saya
               </label>
             </div>
           </div>
           <!-- /.col -->
           <div class="col-4">
             <button type="submit" class="btn btn-primary btn-block">Login</button>
           </div>
           <!-- /.col -->
         </div>
       </form>
 
       <p class="mb-1">
         @if (Route::has('password.request'))
           <a href="{{ route('password.request') }}">Lupa Password</a>
         @endif
       </p>
       <p class="mb-0">
         {{-- <a href="" class="text-center">Belum Punya Akun ? Daftar Member</a> --}}
       </p>
     </div>
     <!-- /.card-body -->
   </div>
   <!-- /.card -->
 </div>
 <!-- /.login-box -->
 
 <x-login.script />
 