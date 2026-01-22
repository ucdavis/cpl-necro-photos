<?php

namespace App\Core;

abstract class Controller
{
    /**
     * Return JSON response
     */
    protected function json($data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Return error response
     */
    protected function error(string $message, int $statusCode = 400): string
    {
        return $this->json(['error' => $message], $statusCode);
    }

    /**
     * Return success response
     */
    protected function success($data, string $message = null): string
    {
        $response = ['success' => true];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return $this->json($response);
    }

    /**
     * Get query parameter
     */
    protected function getQuery(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Get POST data
     */
    protected function getPost(string $key = null, $default = null)
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        if ($key === null) {
            return $data;
        }
        
        return $data[$key] ?? $default;
    }

    /**
     * Get uploaded file
     */
    protected function getFile(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Validate required fields
     */
    protected function validateRequired(array $data, array $required): ?string
    {
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return "Field '$field' is required";
            }
        }
        return null;
    }
}
