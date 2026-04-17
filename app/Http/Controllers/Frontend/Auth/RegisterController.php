<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Concerns\RendersThemeViews;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    use RendersThemeViews;

    public function showRegistrationForm()
    {
        return $this->renderTheme('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:191'],
            'email'                 => ['required', 'email', 'max:191', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'first_name'            => ['required', 'string', 'max:100'],
            'last_name'             => ['required', 'string', 'max:100'],
            'phone'                 => ['nullable', 'string', 'max:32'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Always create a retail Customer profile on registration
        Customer::create([
            'user_id'    => $user->id,
            'type'       => 'retail',
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'is_active'  => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', __('messages.registration_success'));
    }
}
