<?php

use Illuminate\Support\Facades\Route;
use App\Presentation\Http\Controllers\Api\V1\Auth\LoginController;
use App\Presentation\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Presentation\Http\Controllers\Api\V1\Auth\PasswordController;
use App\Presentation\Http\Controllers\Api\V1\Admin\ExperienceController;
use App\Presentation\Http\Controllers\Api\V1\Admin\UserController;
use App\Presentation\Http\Controllers\Api\V1\Admin\BookingController as AdminBookingController;
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

Route::prefix('v1')->middleware('auth:api')->group(function () {
    
    // ========================================================================
    // ROUTES VOYAGEUR (Traveler)
    // ========================================================================
    Route::prefix('traveler')->middleware('role:traveler')->group(function () {
        // Mode découverte
        Route::get('/discovery/suggestions', [App\Presentation\Http\Controllers\Api\V1\Traveler\DiscoveryController::class, 'suggestions']);
        Route::post('/discovery/preferences', [App\Presentation\Http\Controllers\Api\V1\Traveler\DiscoveryController::class, 'preferences']);
        Route::get('/discovery/trending', [App\Presentation\Http\Controllers\Api\V1\Traveler\DiscoveryController::class, 'trending']);
        Route::get('/discovery/hidden-gems', [App\Presentation\Http\Controllers\Api\V1\Traveler\DiscoveryController::class, 'hiddenGems']);
        
        // Gestion des favoris
        Route::get('/favorites', [App\Presentation\Http\Controllers\Api\V1\Traveler\FavoriteController::class, 'index']);
        Route::post('/favorites/{experienceId}', [App\Presentation\Http\Controllers\Api\V1\Traveler\FavoriteController::class, 'store']);
        Route::delete('/favorites/{experienceId}', [App\Presentation\Http\Controllers\Api\V1\Traveler\FavoriteController::class, 'destroy']);
        Route::get('/favorites/alerts', [App\Presentation\Http\Controllers\Api\V1\Traveler\FavoriteController::class, 'alerts']);
    });
    
    // ========================================================================
    // ROUTES PRESTATAIRE (Provider) - TODO: Créer les contrôleurs
    // ========================================================================
    /*
    Route::prefix('provider')->middleware('role:provider')->group(function () {
        // Routes prestataire à implémenter
    });
    */
    
    // ========================================================================
    // ROUTES ADMINISTRATEUR (Admin)
    // ========================================================================
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        
        // Gestion des utilisateurs
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/statistics', [UserController::class, 'statistics']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}/activate', [UserController::class, 'activate']);
        Route::put('/users/{id}/suspend', [UserController::class, 'suspend']);
        Route::put('/users/{id}/validate', [UserController::class, 'validate']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        
        // Gestion des expériences
        Route::get('/experiences', [ExperienceController::class, 'index']);
        Route::get('/experiences/pending', [ExperienceController::class, 'pending']);
        Route::get('/experiences/reports', [ExperienceController::class, 'reports']);
        Route::get('/experiences/{id}', [ExperienceController::class, 'show']);
        Route::put('/experiences/{id}', [ExperienceController::class, 'update']);
        Route::put('/experiences/{id}/moderate', [ExperienceController::class, 'moderate']);
        Route::delete('/experiences/{id}', [ExperienceController::class, 'destroy']);
        
        // Gestion des réservations
        Route::get('/bookings', [AdminBookingController::class, 'index']);
        Route::get('/bookings/statistics', [AdminBookingController::class, 'statistics']);
        Route::get('/bookings/disputes', [AdminBookingController::class, 'disputes']);
        Route::get('/bookings/{id}', [AdminBookingController::class, 'show']);
        Route::put('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus']);
        Route::put('/bookings/{id}/cancel', [AdminBookingController::class, 'cancel']);
        
        // Gestion des paiements
        Route::get('/payments', [App\Presentation\Http\Controllers\Api\V1\Admin\PaymentController::class, 'index']);
        Route::get('/payments/statistics', [App\Presentation\Http\Controllers\Api\V1\Admin\PaymentController::class, 'statistics']);
        Route::get('/payments/disputes', [App\Presentation\Http\Controllers\Api\V1\Admin\PaymentController::class, 'disputes']);
        Route::get('/payments/commissions', [App\Presentation\Http\Controllers\Api\V1\Admin\PaymentController::class, 'commissions']);
        Route::get('/payments/{id}', [App\Presentation\Http\Controllers\Api\V1\Admin\PaymentController::class, 'show']);
        Route::post('/payments/refund', [App\Presentation\Http\Controllers\Api\V1\Admin\PaymentController::class, 'refund']);
        Route::put('/payments/{id}/transfer', [App\Presentation\Http\Controllers\Api\V1\Admin\PaymentController::class, 'transfer']);
        
        // Gestion des avis et notations
        Route::get('/reviews', [App\Presentation\Http\Controllers\Api\V1\Admin\ReviewController::class, 'index']);
        Route::get('/reviews/reported', [App\Presentation\Http\Controllers\Api\V1\Admin\ReviewController::class, 'reported']);
        Route::get('/reviews/statistics', [App\Presentation\Http\Controllers\Api\V1\Admin\ReviewController::class, 'statistics']);
        Route::put('/reviews/{id}/moderate', [App\Presentation\Http\Controllers\Api\V1\Admin\ReviewController::class, 'moderate']);
        Route::delete('/reviews/{id}', [App\Presentation\Http\Controllers\Api\V1\Admin\ReviewController::class, 'destroy']);
        
        // Gestion du contenu
        Route::get('/content/heritage-stories', [App\Presentation\Http\Controllers\Api\V1\Admin\ContentController::class, 'heritageStories']);
        Route::post('/content/heritage-stories', [App\Presentation\Http\Controllers\Api\V1\Admin\ContentController::class, 'storeHeritageStory']);
        Route::put('/content/heritage-stories/{id}', [App\Presentation\Http\Controllers\Api\V1\Admin\ContentController::class, 'updateHeritageStory']);
        Route::delete('/content/heritage-stories/{id}', [App\Presentation\Http\Controllers\Api\V1\Admin\ContentController::class, 'destroyHeritageStory']);
        Route::get('/content/blog', [App\Presentation\Http\Controllers\Api\V1\Admin\ContentController::class, 'blog']);
        Route::post('/content/blog', [App\Presentation\Http\Controllers\Api\V1\Admin\ContentController::class, 'storeBlogPost']);
        
        // Gestion des certifications
        Route::get('/certifications', [App\Presentation\Http\Controllers\Api\V1\Admin\CertificationController::class, 'index']);
        Route::post('/certifications', [App\Presentation\Http\Controllers\Api\V1\Admin\CertificationController::class, 'store']);
        Route::put('/certifications/{id}', [App\Presentation\Http\Controllers\Api\V1\Admin\CertificationController::class, 'update']);
        Route::put('/providers/{id}/certify', [App\Presentation\Http\Controllers\Api\V1\Admin\CertificationController::class, 'certifyProvider']);
        Route::delete('/providers/{id}/certification', [App\Presentation\Http\Controllers\Api\V1\Admin\CertificationController::class, 'revokeCertification']);
        
        // Statistiques globales - TODO: Créer les contrôleurs
        // Route::get('/statistics', [App\Presentation\Http\Controllers\Api\V1\Admin\StatisticsController::class, 'index']);
        // Route::get('/statistics/dashboard', [App\Presentation\Http\Controllers\Api\V1\Admin\StatisticsController::class, 'dashboard']);
    });
    
    // ========================================================================
    // ROUTES PARTAGÉES (Tous les utilisateurs authentifiés) - TODO: Créer les contrôleurs
    // ========================================================================
    /*
    Route::prefix('messages')->group(function () {
        Route::get('/conversations', [App\Presentation\Http\Controllers\Api\V1\Shared\MessageController::class, 'conversations']);
        Route::get('/conversations/{id}', [App\Presentation\Http\Controllers\Api\V1\Shared\MessageController::class, 'show']);
        Route::post('/conversations/{id}/messages', [App\Presentation\Http\Controllers\Api\V1\Shared\MessageController::class, 'send']);
    });
    
    Route::prefix('notifications')->group(function () {
        Route::get('/', [App\Presentation\Http\Controllers\Api\V1\Shared\NotificationController::class, 'index']);
        Route::get('/unread', [App\Presentation\Http\Controllers\Api\V1\Shared\NotificationController::class, 'unread']);
        Route::post('/{id}/read', [App\Presentation\Http\Controllers\Api\V1\Shared\NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [App\Presentation\Http\Controllers\Api\V1\Shared\NotificationController::class, 'markAllAsRead']);
    });
    */
});

