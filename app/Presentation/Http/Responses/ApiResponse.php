<?php

namespace App\Presentation\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    /**
     * Retourner une réponse de succès
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Retourner une réponse d'erreur
     */
    public static function error(
        string $message,
        int $code = 400,
        array $errors = []
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Retourner une réponse paginée
     */
    public static function paginated(
        LengthAwarePaginator $paginator,
        string $message = 'Success',
        ?callable $transformer = null
    ): JsonResponse {
        $items = $paginator->items();
        
        // Appliquer le transformer si fourni
        if ($transformer !== null) {
            $items = array_map($transformer, $items);
        }
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }
}


