<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ((bool) $request->user()?->is_admin) return redirect()->route('admin.dashboard');
        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:72'],
        ]);
        $credentials['email'] = strtolower(trim($credentials['email']));
        // bcrypt's limit is in bytes, not characters.
        if (strlen($credentials['password']) > 72) {
            return back()->withErrors(['email' => 'Credenciales incorrectas.'])->onlyInput('email');
        }
        $key = 'pace-admin:'.hash('sha256', $credentials['email'].'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withErrors(['email' => 'Demasiados intentos. Espera '.RateLimiter::availableIn($key).' segundos.'])->onlyInput('email');
        }
        if (!Auth::attempt($credentials + ['is_admin' => true])) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['email' => 'Credenciales incorrectas.'])->onlyInput('email');
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();
        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
