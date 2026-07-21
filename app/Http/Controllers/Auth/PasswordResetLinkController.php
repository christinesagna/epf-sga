<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('back-office.auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $userIsActive = User::query()
            ->where('email', $request->string('email')->toString())
            ->where('actif', true)
            ->whereNotNull('role')
            ->exists();

        if ($userIsActive) {
            Password::sendResetLink($request->only('email'));
        }

        return back()->with(
            'status',
            'Si un compte actif correspond à cette adresse, un lien de réinitialisation a été envoyé.',
        );
    }
}
