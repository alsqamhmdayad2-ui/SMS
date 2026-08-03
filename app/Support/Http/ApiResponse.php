<?php

namespace App\Support\Http;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Send a standardized success response.
     */
    protected function successResponse(string $message, $data = [], string $code = 'SUCCESS', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'code'    => $code,
            'data'    => $data,
            'errors'  => []
        ], $status);
    }

    /**
     * Send a standardized error response.
     */
    protected function errorResponse(string $message, string $code = 'ERROR', array $errors = [], int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code'    => $code,
            'data'    => null,
            'errors'  => $errors
        ], $status);
    }
    
    /**
     * Send a standardized validation error response.
     */
    protected function validationErrorResponse(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->errorResponse($message, 'VALIDATION_FAILED', $errors, 422);
    }

    /**
     * Send an unauthorized response.
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized action.'): JsonResponse
    {
        return $this->errorResponse($message, 'UNAUTHORIZED', [], 401);
    }

    /**
     * Send a forbidden response.
     */
    protected function forbiddenResponse(string $message = 'Access denied.'): JsonResponse
    {
        return $this->errorResponse($message, 'FORBIDDEN', [], 403);
    }

    /**
     * Send a not found response.
     */
    protected function notFoundResponse(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->errorResponse($message, 'NOT_FOUND', [], 404);
    }
}
