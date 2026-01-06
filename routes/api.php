<?php

use Illuminate\Support\Facades\Route;
use App\Presentation\Http\Controllers\Api\V1\Auth\LoginController;
use App\Presentation\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Presentation\Http\Controllers\Api\V1\Auth\PasswordController;
use App\Presentation\Http\Controllers\Api\V1\Admin\ExperienceController;
use App\Presentation\Http\Controllers\Api\V1\Admin\UserController;
use App\Presentation\Http\Controllers\Api\V1\Admin\PaymentController;
use App\Presentation\Http\Controllers\Api\V1\Admin\BookingController as AdminBookingController;
use App\Presentation\Http\Controllers\Api\V1\Heritage\HeritageController;
use App\Presentation\Http\Controllers\Api\V1\Traveler\ReviewController;
use App\Presentation\Http\Controllers\Api\V1\Traveler\DiscoveryController;
use App\Presentation\Http\Controllers\Api\V1\Traveler\FavoriteController;
use App\Presentation\Http\Controllers\Api\V1\Traveler\BookingController;
use App\Presentation\Http\Controllers\Api\V1\Traveler\TravelBookController;
use App\Presentation\Http\Controllers\Api\V1\Traveler\ProfileController;
use App\Presentation\Http\Controllers\Api\V1\Traveler\MessageController;
use App\Presentation\Http\Controllers\Api\V1\Traveler\NotificationController;
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
    
    // ========================================================================
    // EXPÉRIENCES PUBLIQUES (Accessibles à tous sans authentification)
    // ========================================================================
    Route::prefix('experiences')->group(function () {
        Route::get('/', [ExperienceController::class, 'index']);
        Route::post('/search', [ExperienceController::class, 'search']);
        Route::get('/featured', [ExperienceController::class, 'featured']);
        Route::get('/recent', [ExperienceController::class, 'recent']);
        Route::get('/by-region', [ExperienceController::class, 'byRegion']);
        Route::get('/by-theme', [ExperienceController::class, 'byTheme']);
        Route::get('/by-price', [ExperienceController::class, 'byPrice']);
        Route::get('/{id}', [ExperienceController::class, 'show']);
        Route::get('/{id}/availability', [ExperienceController::class, 'availability']);
        Route::get('/{id}/photos', [ExperienceController::class, 'photos']);
        Route::get('/{id}/similar', [ExperienceController::class, 'similar']);
        Route::get('/{id}/reviews', [ReviewController::class, 'getExperienceReviews']);
    });
    
    // ========================================================================
    // HISTOIRES DU PATRIMOINE (Accessibles à tous)
    // ========================================================================
    Route::prefix('heritage')->group(function () {
        Route::get('/stories', [HeritageController::class, 'index']);
        Route::get('/stories/by-region', [HeritageController::class, 'byRegion']);
        Route::get('/stories/{id}', [HeritageController::class, 'show']);
        Route::post('/stories/{id}/favorite', [HeritageController::class, 'favorite'])->middleware('auth:api');
    });
    
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
        Route::get('/discovery/suggestions', [DiscoveryController::class, 'suggestions']);
        Route::post('/discovery/preferences', [DiscoveryController::class, 'preferences']);
        Route::get('/discovery/trending', [DiscoveryController::class, 'trending']);
        Route::get('/discovery/hidden-gems', [DiscoveryController::class, 'hiddenGems']);
        
        // Gestion des favoris
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/favorites/{experienceId}', [FavoriteController::class, 'store']);
        Route::delete('/favorites/{experienceId}', [FavoriteController::class, 'destroy']);
        Route::get('/favorites/alerts', [FavoriteController::class, 'alerts']);
        
        // Gestion des réservations
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/upcoming', [BookingController::class, 'upcoming']);
        Route::get('/bookings/pending', [BookingController::class, 'pending']);
        Route::get('/bookings/confirmed', [BookingController::class, 'confirmed']);
        Route::get('/bookings/history', [BookingController::class, 'history']);
        Route::get('/bookings/{id}', [BookingController::class, 'show']);
        Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
        Route::get('/bookings/{id}/voucher', [BookingController::class, 'voucher']);
        
        // Carnet de voyage
        Route::get('/travelbook', [TravelBookController::class, 'index']);
        Route::post('/travelbook/entries', [TravelBookController::class, 'store']);
        Route::get('/travelbook/entries/{id}', [TravelBookController::class, 'show']);
        Route::put('/travelbook/entries/{id}', [TravelBookController::class, 'update']);
        Route::delete('/travelbook/entries/{id}', [TravelBookController::class, 'destroy']);
        Route::post('/travelbook/entries/{id}/photos', [TravelBookController::class, 'addPhotos']);
        Route::post('/travelbook/share', [TravelBookController::class, 'share']);
        Route::get('/travelbook/export', [TravelBookController::class, 'export']);
        
        // Avis et évaluations
        Route::get('/reviews', [ReviewController::class, 'index']);
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::put('/reviews/{id}', [ReviewController::class, 'update']);
        Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
        Route::post('/reviews/{id}/helpful', [ReviewController::class, 'markHelpful']);
        
        // Communication
        Route::get('/messages', [MessageController::class, 'index']);
        Route::post('/messages/providers/{providerId}', [MessageController::class, 'contactProvider']);
        Route::get('/messages/{conversationId}', [MessageController::class, 'show']);
        Route::post('/messages/{conversationId}/reply', [MessageController::class, 'reply']);
        Route::get('/messages/unread', [MessageController::class, 'unread']);
        
        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::get('/notifications/unread', [NotificationController::class, 'unread']);
        Route::put('/notifications/settings', [NotificationController::class, 'updateSettings']);
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
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::get('/payments/statistics', [PaymentController::class, 'statistics']);
        Route::get('/payments/disputes', [PaymentController::class, 'disputes']);
        Route::get('/payments/commissions', [PaymentController::class, 'commissions']);
        Route::get('/payments/{id}', [PaymentController::class, 'show']);
        Route::post('/payments/refund', [PaymentController::class, 'refund']);
        Route::put('/payments/{id}/transfer', [PaymentController::class, 'transfer']);
        
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
    // ROUTES VOYAGEUR (Authentifiés avec rôle traveler)
    // ========================================================================
    Route::prefix('traveler')->middleware('role:traveler')->group(function () {
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/photo', [ProfileController::class, 'updatePhoto']);
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

