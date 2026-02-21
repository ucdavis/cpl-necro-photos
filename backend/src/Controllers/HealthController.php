<?php

namespace App\Controllers;

use App\Core\Controller;

class HealthController extends Controller
{
    /**
     * Allows frontend to do health check of backend. CAS will redirect if session
     * has become "stale". Either a refresh will renew the CAS session or a redirect
     * to CAS login page.     
     */
    public function index(): string
    {
        try {
            return $this->json([
                'success' => true,
                'timestamp' => date('c'),
            ]);
        } catch (\Exception $e) {
            return $this->error('Health check failed: ' . $e->getMessage(), 500);
        }
    }
}
