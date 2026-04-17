<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Concerns\RendersThemeViews;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use RendersThemeViews;

    public function showLoginForm()
    {
        return $this->renderTheme('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __('auth.failed')]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
