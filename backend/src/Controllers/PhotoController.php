<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\PhotoRepository;

class PhotoController extends Controller
{
    private PhotoRepository $photoRepository;

    public function __construct()
    {
        $this->photoRepository = new PhotoRepository();
    }

    /**
     * GET /photos
     * Get paginated list of photos
     */
    public function index(): string
    {
        try {
            $page = (int) $this->getQuery('page', 1);
            $perPage = (int) $this->getQuery('per_page', 20);
            $year = $this->getQuery('year');
            $search = $this->getQuery('search');
            
            // Validate pagination parameters
            if ($page < 1) $page = 1;
            if ($perPage < 1 || $perPage > 100) $perPage = 20;
            
            // Convert year to 2-digit format if provided
            if ($year) {
                $year = (int) substr($year, -2);
            }
            
            // Search or paginate
            if ($search) {
                $result = $this->photoRepository->search($search, $page, $perPage);
            } else {
                $result = $this->photoRepository->getPaginated($page, $perPage, $year);
            }
            
            return $this->json($result);
            
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve photos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /photos/{id}
     * Get a single photo by ID
     */
    public function show(string $id): string
    {
        try {
            $photo = $this->photoRepository->getById((int) $id);
            
            if (!$photo) {
                return $this->error('Photo not found', 404);
            }
            
            return $this->json($photo);
            
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve photo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /photos/upload
     * Upload a new photo
     */
    public function upload(): string
    {
        try {
            // Get uploaded file
            $file = $this->getFile('photo');
            
            if (!$file) {
                return $this->error('No photo file uploaded', 400);
            }
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return $this->error('Upload failed: ' . $this->getUploadErrorMessage($file['error']), 400);
            }
            
            // Validate file size
            $maxSize = (int) $_ENV['MAX_UPLOAD_SIZE'];
            if ($file['size'] > $maxSize) {
                return $this->error('File size exceeds maximum allowed size', 400);
            }
            
            // Validate file type (images only)
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                return $this->error('Invalid file type. Only images are allowed', 400);
            }
            
            // Get additional data from POST
            $cplNum = $this->getPost('cpl_num');
            $suffix = $this->getPost('suffix');
            $year = $this->getPost('year');
            $login = $this->getPost('login', 'system');
            
            // Validate required fields
            $error = $this->validateRequired(
                ['cpl_num' => $cplNum, 'year' => $year],
                ['cpl_num', 'year']
            );
            
            if ($error) {
                return $this->error($error, 400);
            }
            
            // Generate filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = sprintf(
                '%s-%s%s.%s',
                $cplNum,
                substr($year, -2),
                $suffix ? $suffix : '',
                $extension
            );
            
            // Create upload directory if it doesn't exist
            $uploadDir = $_ENV['UPLOAD_DIR'] ?? '../uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadPath = $uploadDir . '/' . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                return $this->error('Failed to save uploaded file', 500);
            }
            
            // Save to database
            $photoId = $this->photoRepository->create([
                'cpl_num' => $cplNum,
                'suffix' => $suffix ?? '',
                'year' => substr($year, -2),
                'filename' => $filename,
                'size' => $file['size'],
                'login' => $login
            ]);
            
            return $this->success([
                'id' => $photoId,
                'filename' => $filename
            ], 'Photo uploaded successfully');
            
        } catch (\Exception $e) {
            return $this->error('Upload failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * DELETE /photos/{id}
     * Delete a photo
     */
    public function delete(string $id): string
    {
        try {
            $photo = $this->photoRepository->getById((int) $id);
            
            if (!$photo) {
                return $this->error('Photo not found', 404);
            }
            
            // Delete file from filesystem
            $uploadDir = $_ENV['UPLOAD_DIR'] ?? '../uploads';
            $filePath = $uploadDir . '/' . $photo['filename'];
            
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Delete from database
            $this->photoRepository->delete((int) $id);
            
            return $this->success(null, 'Photo deleted successfully');
            
        } catch (\Exception $e) {
            return $this->error('Failed to delete photo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File too large',
            UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension',
            default => 'Unknown upload error'
        };
    }
}
