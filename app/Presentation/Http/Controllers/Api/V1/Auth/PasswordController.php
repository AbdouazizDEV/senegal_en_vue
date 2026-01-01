<?php

namespace App\Presentation\Http\Controllers\Api\V1\Auth;

use App\Infrastructure\Repositories\Contracts\UserRepositoryInterface;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class PasswordController extends Controller
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Demander la réinitialisation du mot de passe
     */
    #[OA\Post(
        path: '/api/v1/auth/password/forgot',
        summary: 'Demander la réinitialisation du mot de passe',
        description: 'Envoie un email de réinitialisation de mot de passe à l\'utilisateur',
        tags: ['Authentification'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'amadou.diallo@example.com'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Email de réinitialisation envoyé',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Un email de réinitialisation a été envoyé'),
                        new OA\Property(property: 'data', type: 'null'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Erreur de validation'),
            new OA\Response(response: 500, description: 'Erreur serveur'),
        ]
    )]
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Données invalides', 422, $validator->errors()->toArray());
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return ApiResponse::success(null, 'Un email de réinitialisation a été envoyé');
        }

        return ApiResponse::error('Impossible d\'envoyer l\'email de réinitialisation', 500);
    }

    /**
     * Réinitialiser le mot de passe
     */
    #[OA\Post(
        path: '/api/v1/auth/password/reset',
        summary: 'Réinitialiser le mot de passe',
        description: 'Réinitialise le mot de passe de l\'utilisateur avec le token reçu par email',
        tags: ['Authentification'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['token', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'token', type: 'string', example: 'reset_token_from_email'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'amadou.diallo@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'newpassword123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'newpassword123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mot de passe réinitialisé avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Mot de passe réinitialisé avec succès'),
                        new OA\Property(property: 'data', type: 'null'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Token invalide ou expiré'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Données invalides', 422, $validator->errors()->toArray());
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return ApiResponse::success(null, 'Mot de passe réinitialisé avec succès');
        }

        return ApiResponse::error('Impossible de réinitialiser le mot de passe', 400);
    }
}
