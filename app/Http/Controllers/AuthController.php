<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (setting('navigation.use_captcha')) {
            $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => setting('auth.turnstile_secret_key'),
                'response' => $request->input('cf-turnstile-response'),
                'remoteip' => $request->ip(),
            ]);

            if (!$response->json('success')) {
                return back()->withErrors([
                    'turnstile' => 'Verificação de segurança falhou. Recarregue a página e tente novamente.'
                ])->withInput();
            }
        }

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // 2FA check
            if (setting('auth.2fa_enabled', false) && $user->hasTwoFactorEnabled()) {
                Auth::logout();
                session([
                    'mfa.user_id' => $user->id,
                    'mfa.started_at' => now(),
                ]);
                return redirect()->route('two-factor.challenge');
            }

            $request->session()->regenerate();

            log_admin("Login realizado com sucesso", "auth");

            if ($user->role === 'subscriber') {
                return redirect()->intended('/');
            }

            return redirect()->intended('/admin/dashboard');
        }

        log_admin("Tentativa de login falhou: {$credentials['email']}", "auth");

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
