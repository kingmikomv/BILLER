<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Kemana pengguna akan diarahkan setelah login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Membuat instance baru dari controller.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Override metode username() agar bisa login dengan email atau username.
     *
     * @return string
     */
    public function username()
    {
        $login = request()->input('login');

        return filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    }

    /**
     * Override metode login untuk menyesuaikan request input.
     */
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'login' => 'required|string', // Bisa berupa email atau username
            'password' => 'required|string',
        ]);

        // Menentukan apakah yang dikirimkan email atau username
        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Coba login dengan kredensial yang diberikan
        if (Auth::attempt([$loginType => $request->login, 'password' => $request->password], $request->filled('remember'))) {
            return redirect()->intended($this->redirectTo);
        }

        // Jika gagal, kembali dengan pesan error
        return back()->withErrors(['login' => 'Email atau Username dan Password salah.'])->withInput($request->only('login'));
    }
}
