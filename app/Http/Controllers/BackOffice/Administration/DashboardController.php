<?php

namespace App\Http\Controllers\BackOffice\Administration;

use App\Http\Controllers\Controller;
use App\Models\Niveau;
use App\Models\Programme;
use App\Models\TypeDocument;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        return view('back-office.administration.dashboard', [
            'utilisateursInternes' => User::query()->whereNotNull('role')->count(),
            'utilisateursActifs' => User::query()->whereNotNull('role')->where('actif', true)->count(),
            'invitationsEnAttente' => User::query()
                ->whereNotNull('role')
                ->whereNotNull('invitation_sent_at')
                ->whereNull('email_verified_at')
                ->count(),
            'programmesActifs' => Programme::query()->where('actif', true)->count(),
            'niveauxConfigures' => Niveau::query()->count(),
            'typesDocumentsActifs' => TypeDocument::query()->where('actif', true)->count(),
        ]);
    }
}
