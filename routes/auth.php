<?php

/*
|--------------------------------------------------------------------------
| Routes d'authentification des clients
|--------------------------------------------------------------------------
|
| Ce fichier regroupe les routes qui permettent à un client de créer un
| compte, de se connecter, de se déconnecter et de récupérer son mot de passe.
|
| Il est inclus par routes/web.php et bénéficie donc du groupe de middlewares
| « web ». Les pages React correspondantes sont dans resources/js/pages/auth.
|
*/

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

// Le middleware « guest » réserve ces routes aux visiteurs NON connectés : un
// client déjà connecté qui visite /login est redirigé vers l'accueil.
Route::middleware('guest')->group(function () {
    // Inscription : GET affiche le formulaire, POST enregistre le client.
    // `throttle:6,1` limite à 6 tentatives par minute (et par IP) pour freiner
    // la création de comptes en masse.
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:6,1');

    // Connexion : la limitation des tentatives est gérée dans LoginRequest
    // (5 essais par couple email + IP), pas par un middleware.
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Mot de passe oublié, étape 1 : le client saisit son email et reçoit un lien.
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    // Mot de passe oublié, étape 2 : le lien reçu par email contient un jeton
    // ({token}) qui prouve que la demande vient bien du propriétaire du compte.
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.store');
});

// Le middleware « auth » réserve ces routes aux clients connectés. Déconnexion
// en POST (et non en GET) pour qu'un simple lien ne puisse pas déconnecter
// quelqu'un à son insu.
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
