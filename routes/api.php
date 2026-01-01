<?php

use Illuminate\Support\Facades\Route;
use App\Presentation\Http\Controllers\Api\V1\Auth\LoginController;
use App\Presentation\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Presentation\Http\Controllers\Api\V1\Auth\PasswordController;
use App\Presentation\Http\Controllers\Api\V1\Traveler\BookingController;


/*
|--------------------------------------------------------------------------
| API Routes - Sénégal en Vue
|--------------------------------------------------------------------------
|
| Routes API versionnées (v1) pour la plateforme de tourisme local
| Architecture RESTful avec séparation par rôles
|
*/

// ============================================================================
// ROUTES PUBLIQUES (Sans authentification)
// ============================================================================

Route::prefix('v1')->group(function () {
    
    // Expériences publiques (TODO: Créer les contrôleurs)
    // Route::get('/experiences', [App\Presentation\Http\Controllers\Api\V1\Public\ExperienceController::class, 'index']);
    // Route::get('/experiences/{id}', [App\Presentation\Http\Controllers\Api\V1\Public\ExperienceController::class, 'show']);
    
    // Recherche publique (TODO: Créer les contrôleurs)
    // Route::get('/search', [App\Presentation\Http\Controllers\Api\V1\Public\SearchController::class, 'index']);
    
    // Authentification
    Route::prefix('auth')->group(function () {
        // Inscription
        Route::post('/register/traveler', [RegisterController::class, 'registerTraveler']); // inscription pour les voyageurs
        Route::post('/register/provider', [RegisterController::class, 'registerProvider']); // inscription pour les prestataires
        
        // Connexion/Déconnexion
        Route::post('/login', [LoginController::class, 'login']); // connexion pour tous les utilisateurs
        Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:api'); // déconnexion pour tous les utilisateurs
        Route::post('/refresh', [LoginController::class, 'refresh'])->middleware('auth:api'); // rafraîchissement du token pour tous les utilisateurs
        
        // Mot de passe oublié
        Route::post('/password/forgot', [PasswordController::class, 'forgotPassword']); // mot de passe oublié pour tous les utilisateurs
        Route::post('/password/reset', [PasswordController::class, 'resetPassword']); // réinitialisation du mot de passe pour tous les utilisateurs
    });
});

// ============================================================================
// ROUTES PROTÉGÉES (Authentification requise)
// ============================================================================
// TODO: Décommenter ces routes une fois les contrôleurs créés

/*
Route::prefix('v1')->middleware('auth:api')->group(function () {
    
    // ========================================================================
    // ROUTES VOYAGEUR (Traveler)
    // ========================================================================
    Route::prefix('traveler')->middleware('role:traveler')->group(function () {
        
        // Réservations
        Route::apiResource('bookings', App\Presentation\Http\Controllers\Api\V1\Traveler\BookingController::class);
        Route::post('/bookings/{id}/cancel', [App\Presentation\Http\Controllers\Api\V1\Traveler\BookingController::class, 'cancel']);
        
        // Avis/Reviews
        Route::apiResource('reviews', App\Presentation\Http\Controllers\Api\V1\Traveler\ReviewController::class);
        
        // Paiements
        Route::post('/payments/process', [App\Presentation\Http\Controllers\Api\V1\Traveler\PaymentController::class, 'process']);
        Route::get('/payments/{id}', [App\Presentation\Http\Controllers\Api\V1\Traveler\PaymentController::class, 'show']);
        
        // Expériences (consultation)
        Route::get('/experiences', [App\Presentation\Http\Controllers\Api\V1\Traveler\ExperienceController::class, 'index']);
        Route::get('/experiences/{id}', [App\Presentation\Http\Controllers\Api\V1\Traveler\ExperienceController::class, 'show']);
        
        // Profil
        Route::get('/profile', [App\Presentation\Http\Controllers\Api\V1\Traveler\ProfileController::class, 'show']);
        Route::put('/profile', [App\Presentation\Http\Controllers\Api\V1\Traveler\ProfileController::class, 'update']);
    });
    
    // ========================================================================
    // ROUTES PRESTATAIRE (Provider)
    // ========================================================================
    Route::prefix('provider')->middleware('role:provider')->group(function () {
        
        // Expériences (CRUD complet)
        Route::apiResource('experiences', App\Presentation\Http\Controllers\Api\V1\Provider\ExperienceController::class);
        Route::post('/experiences/{id}/publish', [App\Presentation\Http\Controllers\Api\V1\Provider\ExperienceController::class, 'publish']);
        Route::post('/experiences/{id}/unpublish', [App\Presentation\Http\Controllers\Api\V1\Provider\ExperienceController::class, 'unpublish']);
        
        // Réservations (gestion)
        Route::get('/bookings', [App\Presentation\Http\Controllers\Api\V1\Provider\BookingController::class, 'index']);
        Route::get('/bookings/{id}', [App\Presentation\Http\Controllers\Api\V1\Provider\BookingController::class, 'show']);
        Route::post('/bookings/{id}/confirm', [App\Presentation\Http\Controllers\Api\V1\Provider\BookingController::class, 'confirm']);
        Route::post('/bookings/{id}/reject', [App\Presentation\Http\Controllers\Api\V1\Provider\BookingController::class, 'reject']);
        
        // Calendrier
        Route::get('/calendar', [App\Presentation\Http\Controllers\Api\V1\Provider\CalendarController::class, 'index']);
        Route::get('/calendar/availability', [App\Presentation\Http\Controllers\Api\V1\Provider\CalendarController::class, 'availability']);
        
        // Statistiques
        Route::get('/statistics', [App\Presentation\Http\Controllers\Api\V1\Provider\StatisticsController::class, 'index']);
        Route::get('/statistics/revenue', [App\Presentation\Http\Controllers\Api\V1\Provider\StatisticsController::class, 'revenue']);
        
        // Profil
        Route::get('/profile', [App\Presentation\Http\Controllers\Api\V1\Provider\ProfileController::class, 'show']);
        Route::put('/profile', [App\Presentation\Http\Controllers\Api\V1\Provider\ProfileController::class, 'update']);
    });
    
    // ========================================================================
    // ROUTES ADMINISTRATEUR (Admin)
    // ========================================================================
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        
        // Gestion des utilisateurs
        Route::apiResource('users', App\Presentation\Http\Controllers\Api\V1\Admin\UserController::class);
        Route::post('/users/{id}/suspend', [App\Presentation\Http\Controllers\Api\V1\Admin\UserController::class, 'suspend']);
        Route::post('/users/{id}/activate', [App\Presentation\Http\Controllers\Api\V1\Admin\UserController::class, 'activate']);
        
        // Gestion des expériences (modération)
        Route::get('/experiences', [App\Presentation\Http\Controllers\Api\V1\Admin\ExperienceController::class, 'index']);
        Route::get('/experiences/{id}', [App\Presentation\Http\Controllers\Api\V1\Admin\ExperienceController::class, 'show']);
        Route::post('/experiences/{id}/approve', [App\Presentation\Http\Controllers\Api\V1\Admin\ExperienceController::class, 'approve']);
        Route::post('/experiences/{id}/reject', [App\Presentation\Http\Controllers\Api\V1\Admin\ExperienceController::class, 'reject']);
        
        // Gestion des réservations
        Route::get('/bookings', [App\Presentation\Http\Controllers\Api\V1\Admin\BookingController::class, 'index']);
        Route::get('/bookings/{id}', [App\Presentation\Http\Controllers\Api\V1\Admin\BookingController::class, 'show']);
        
        // Gestion des paiements
        Route::get('/payments', [App\Presentation\Http\Controllers\Api\V1\Admin\PaymentController::class, 'index']);
        Route::get('/payments/{id}', [App\Presentation\Http\Controllers\Api\V1\Admin\PaymentController::class, 'show']);
        Route::post('/payments/{id}/refund', [App\Presentation\Http\Controllers\Api\V1\Admin\PaymentController::class, 'refund']);
        
        // Statistiques globales
        Route::get('/statistics', [App\Presentation\Http\Controllers\Api\V1\Admin\StatisticsController::class, 'index']);
        Route::get('/statistics/dashboard', [App\Presentation\Http\Controllers\Api\V1\Admin\StatisticsController::class, 'dashboard']);
    });
    
    // ========================================================================
    // ROUTES PARTAGÉES (Tous les utilisateurs authentifiés)
    // ========================================================================
    
    // Messages/Conversations
    Route::prefix('messages')->group(function () {
        Route::get('/conversations', [App\Presentation\Http\Controllers\Api\V1\Shared\MessageController::class, 'conversations']);
        Route::get('/conversations/{id}', [App\Presentation\Http\Controllers\Api\V1\Shared\MessageController::class, 'show']);
        Route::post('/conversations/{id}/messages', [App\Presentation\Http\Controllers\Api\V1\Shared\MessageController::class, 'send']);
    });
    
    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [App\Presentation\Http\Controllers\Api\V1\Shared\NotificationController::class, 'index']);
        Route::get('/unread', [App\Presentation\Http\Controllers\Api\V1\Shared\NotificationController::class, 'unread']);
        Route::post('/{id}/read', [App\Presentation\Http\Controllers\Api\V1\Shared\NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [App\Presentation\Http\Controllers\Api\V1\Shared\NotificationController::class, 'markAllAsRead']);
    });
});
*/

