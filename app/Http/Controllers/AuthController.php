<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    // public function login(Request $request)
    // {
    //     $credentials = $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required',
    //     ]);

    //     if (Auth::attempt($credentials)) {
    //         $request->session()->regenerate();

    //         $user = Auth::user();

    //         if ($user->role === 'admin') {
    //             return redirect()->intended('/admin/dashboard');
    //         }

    //         if ($user->role === 'partner') {
    //             return redirect()->intended('/partner/dashboard');
    //         }

    //         return redirect()->intended('/');
    //     }

    //     return back()->withErrors([
    //         'email' => 'As credenciais informadas não correspondem aos nossos registros.',
    //     ])->onlyInput('email');
    // }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // 🔧 Agora acessa a role através do relacionamento
            $roleSlug = $user->role?->slug ?? 'viewer'; // admin, editor, viewer

            // if ($roleSlug === 'admin') {
            //     return redirect()->intended('/admin/dashboard');
            // }

            if ($roleSlug === 'viewer') {
                return redirect()->intended('/');
            }

            // Para editor ou visualizador, vai para o dashboard também
            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors([
            'email' => 'As credenciais informadas não correspondem aos nossos registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
