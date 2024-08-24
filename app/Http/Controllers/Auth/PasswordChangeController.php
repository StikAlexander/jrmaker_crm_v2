<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PasswordChangeController extends Controller
{
    public function verify(Request $request)
    {
        return view('auth.passwords.verify_password_change');
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'new_password' => 'required|confirmed|min:8',
        ]);

        $email = DB::table('password_reset_tokens')->where('token', $request->token)->value('email');

        if (!$email) {
            return redirect()->back()->withErrors(['token' => 'Código de verificación inválido o expirado.']);
        }

        // Actualizar la contraseña del usuario
        $user = \App\Models\User::where('email', $email)->first();
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Borrar el token usado
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Autenticar al usuario
        Auth::login($user);

        return redirect('/admin/my-profile')->with('status', 'Contraseña cambiada con éxito.');
    }
}
