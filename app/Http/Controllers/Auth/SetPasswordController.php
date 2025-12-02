<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class SetPasswordController extends Controller
{
    public function show(string $token)
    {
        $user = User::where('password_setup_token', $token)->first();

        if (! $user || $user->password_setup_expires_at?->isPast()) {
            return view('auth.passwords.token-expired');
        }

        return view('auth.passwords.setup', ['token' => $token, 'email' => $user->email]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::where('password_setup_token', $request->token)->first();

        if (! $user || $user->password_setup_expires_at?->isPast()) {
            return back()->withErrors(['token' => 'Link wygasł lub jest nieprawidłowy.']);
        }

        $user->completePasswordSetup($request->token, $request->password);

        return redirect()->route('login')
            ->with('status', 'Hasło ustawione pomyślnie. Możesz się teraz zalogować.');
    }
}
