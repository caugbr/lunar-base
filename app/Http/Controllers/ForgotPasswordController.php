<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    // 1. Tela para digitar o e-mail
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    // 2. Envia o e-mail com o token
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // Por segurança, se o e-mail não existir, mostramos a mesma mensagem de sucesso (evita user enumeration)
        if (!$user) {
            return back()->with('status', 'Se o e-mail constar em nossa base, enviaremos um link de redefinição.');
        }

        // Gera token seguro e salva na tabela password_reset_tokens
        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

        Mail::to($user->email)->send(new ResetPasswordMail($resetUrl, $user->name));

        if (function_exists('log_admin')) {
            log_admin("Solicitação de redefinição de senha para: {$user->email}", "auth");
        }

        return back()->with('status', 'Enviamos o link de redefinição de senha para o seu e-mail!');
    }

    // 3. Tela onde o usuário digita a NOVA senha vindo do e-mail
    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email')
        ]);
    }

    // 4. Salva a nova senha
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return back()->withErrors(['email' => 'Solicitação de troca inválida ou expirada.']);
        }

        // Valida expiração (padrão de 60 minutos do config/auth.php)
        $expires = config('auth.passwords.users.expire', 60);
        if (Carbon::parse($record->created_at)->addMinutes($expires)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Este link expirou. Por favor, solicite um novo.']);
        }

        // Valida se o hash do token bate
        if (!Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Token de redefinição inválido.']);
        }

        // Atualiza a senha do usuário
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Usuário não encontrado.']);
        }

        $user->password = $request->password; // O Cast 'hashed' do User model faz o hash automático
        $user->setRememberToken(Str::random(60));
        $user->save();

        // Apaga o token usado
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        if (function_exists('log_admin')) {
            log_admin("Senha redefinida com sucesso para: {$user->email}", "auth");
        }

        return redirect()->route('login')->with('status', 'Sua senha foi redefinida com sucesso! Faça login com a nova senha.');
    }
}
