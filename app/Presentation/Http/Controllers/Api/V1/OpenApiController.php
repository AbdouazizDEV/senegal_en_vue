<?php

namespace App\Presentation\Http\Controllers\Api\V1;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Sénégal en Vue API",
 *     description="API REST pour la plateforme de tourisme local Sénégal en Vue. Documentation complète des endpoints d'authentification, gestion des expériences, réservations et paiements."
 * )
 * 
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Serveur de développement local"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Entrez votre token JWT obtenu lors de la connexion. Format: Bearer {token}"
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     required={"id", "uuid", "name", "email", "role", "status"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="uuid", type="string", example="7514c633-716e-4d88-9ef5-46f8fc6dc714"),
 *     @OA\Property(property="name", type="string", example="Amadou Diallo"),
 *     @OA\Property(property="email", type="string", format="email", example="amadou.diallo@example.com"),
 *     @OA\Property(property="phone", type="string", nullable=true, example="+221771234567"),
 *     @OA\Property(property="role", type="string", enum={"admin", "traveler", "provider", "institution"}, example="traveler"),
 *     @OA\Property(property="role_label", type="string", example="Voyageur"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "suspended", "pending_verification", "verified"}, example="active"),
 *     @OA\Property(property="status_label", type="string", example="Actif"),
 *     @OA\Property(property="avatar", type="string", nullable=true),
 *     @OA\Property(property="bio", type="string", nullable=true),
 *     @OA\Property(property="preferences", type="object", nullable=true),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="phone_verified_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="last_login_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Tag(
 *     name="Authentification",
 *     description="Endpoints d'authentification et de gestion des utilisateurs"
 * )
 * 
 * @OA\Tag(
 *     name="Administration",
 *     description="Endpoints d'administration pour la gestion de la plateforme"
 * )
 * 
 * @OA\Tag(
 *     name="Voyageur",
 *     description="Endpoints pour les fonctionnalités voyageur"
 * )
 * 
 * @OA\Schema(
 *     schema="Experience",
 *     type="object",
 *     required={"id", "uuid", "title", "description", "type", "status", "price"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="uuid", type="string", example="7514c633-716e-4d88-9ef5-46f8fc6dc714"),
 *     @OA\Property(property="title", type="string", example="Visite guidée de Dakar"),
 *     @OA\Property(property="description", type="string", example="Découvrez les merveilles de Dakar..."),
 *     @OA\Property(property="short_description", type="string", nullable=true),
 *     @OA\Property(property="slug", type="string", example="visite-guidee-dakar"),
 *     @OA\Property(property="type", type="string", enum={"activity", "tour", "workshop", "event", "accommodation", "restaurant"}, example="tour"),
 *     @OA\Property(property="type_label", type="string", example="Visite guidée"),
 *     @OA\Property(property="status", type="string", enum={"draft", "pending", "approved", "rejected", "suspended", "reported"}, example="pending"),
 *     @OA\Property(property="status_label", type="string", example="En attente"),
 *     @OA\Property(property="price", type="number", format="float", example=15000.00),
 *     @OA\Property(property="currency", type="string", example="XOF"),
 *     @OA\Property(property="duration_minutes", type="integer", nullable=true, example=120),
 *     @OA\Property(property="max_participants", type="integer", nullable=true, example=20),
 *     @OA\Property(property="min_participants", type="integer", example=1),
 *     @OA\Property(property="images", type="array", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="location", type="object", nullable=true),
 *     @OA\Property(property="schedule", type="object", nullable=true),
 *     @OA\Property(property="tags", type="array", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="amenities", type="array", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="is_featured", type="boolean", example=false),
 *     @OA\Property(property="views_count", type="integer", example=0),
 *     @OA\Property(property="bookings_count", type="integer", example=0),
 *     @OA\Property(property="rating", type="number", format="float", nullable=true),
 *     @OA\Property(property="reviews_count", type="integer", example=0),
 *     @OA\Property(property="rejection_reason", type="string", nullable=true),
 *     @OA\Property(property="published_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="approved_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="rejected_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="Booking",
 *     type="object",
 *     required={"id", "uuid", "status", "booking_date", "total_amount", "currency"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="uuid", type="string", example="7514c633-716e-4d88-9ef5-46f8fc6dc714"),
 *     @OA\Property(property="status", type="string", enum={"pending", "confirmed", "cancelled", "completed", "disputed", "refunded"}, example="pending"),
 *     @OA\Property(property="status_label", type="string", example="En attente"),
 *     @OA\Property(property="booking_date", type="string", format="date", example="2026-03-15"),
 *     @OA\Property(property="booking_time", type="string", format="time", nullable=true, example="14:00"),
 *     @OA\Property(property="participants_count", type="integer", example=2),
 *     @OA\Property(property="total_amount", type="number", format="float", example=30000.00),
 *     @OA\Property(property="currency", type="string", example="XOF"),
 *     @OA\Property(property="payment_status", type="string", enum={"pending", "paid", "failed", "refunded"}, example="pending"),
 *     @OA\Property(property="payment_status_label", type="string", example="En attente"),
 *     @OA\Property(property="payment_method", type="string", nullable=true, example="mobile_money"),
 *     @OA\Property(property="payment_reference", type="string", nullable=true),
 *     @OA\Property(property="payment_date", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="special_requests", type="string", nullable=true),
 *     @OA\Property(property="cancellation_reason", type="string", nullable=true),
 *     @OA\Property(property="cancelled_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="confirmed_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="completed_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="metadata", type="object", nullable=true),
 *     @OA\Property(property="experience", ref="#/components/schemas/Experience", nullable=true),
 *     @OA\Property(property="traveler", ref="#/components/schemas/User", nullable=true),
 *     @OA\Property(property="provider", ref="#/components/schemas/User", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="TravelBookEntry",
 *     type="object",
 *     required={"id", "uuid", "title", "content", "entry_date", "visibility"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="uuid", type="string", example="7514c633-716e-4d88-9ef5-46f8fc6dc714"),
 *     @OA\Property(property="title", type="string", example="Ma journée à Dakar"),
 *     @OA\Property(property="content", type="string", example="J'ai passé une merveilleuse journée..."),
 *     @OA\Property(property="entry_date", type="string", format="date", example="2026-03-15"),
 *     @OA\Property(property="location", type="string", nullable=true, example="Dakar, Sénégal"),
 *     @OA\Property(property="location_details", type="object", nullable=true),
 *     @OA\Property(property="photos", type="array", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="tags", type="array", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="visibility", type="string", enum={"private", "friends", "public"}, example="private"),
 *     @OA\Property(property="visibility_label", type="string", example="Privé"),
 *     @OA\Property(property="is_featured", type="boolean", example=false),
 *     @OA\Property(property="views_count", type="integer", example=0),
 *     @OA\Property(property="metadata", type="object", nullable=true),
 *     @OA\Property(property="experience", ref="#/components/schemas/Experience", nullable=true),
 *     @OA\Property(property="booking", ref="#/components/schemas/Booking", nullable=true),
 *     @OA\Property(property="traveler", ref="#/components/schemas/User", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="Review",
 *     type="object",
 *     required={"id", "uuid", "rating", "comment", "status"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="uuid", type="string", example="7514c633-716e-4d88-9ef5-46f8fc6dc714"),
 *     @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5),
 *     @OA\Property(property="title", type="string", nullable=true, example="Expérience exceptionnelle"),
 *     @OA\Property(property="comment", type="string", example="J'ai passé un moment inoubliable..."),
 *     @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected", "reported"}, example="pending"),
 *     @OA\Property(property="status_label", type="string", example="En attente"),
 *     @OA\Property(property="is_verified", type="boolean", example=true),
 *     @OA\Property(property="is_featured", type="boolean", example=false),
 *     @OA\Property(property="helpful_count", type="integer", example=0),
 *     @OA\Property(property="rejection_reason", type="string", nullable=true),
 *     @OA\Property(property="approved_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="rejected_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="images", type="array", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="experience", ref="#/components/schemas/Experience", nullable=true),
 *     @OA\Property(property="traveler", ref="#/components/schemas/User", nullable=true),
 *     @OA\Property(property="provider", ref="#/components/schemas/User", nullable=true),
 *     @OA\Property(property="booking", ref="#/components/schemas/Booking", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class OpenApiController
{
    //
}


