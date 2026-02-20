<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\PhotoRepository;
use App\Repositories\AccessionRepository;
use App\Services\ImageService;
use App\Services\PhotoFilenameService;

class PhotoController extends Controller
{
    private PhotoRepository $photoRepository;
    private ImageService $imageService;
    private AccessionRepository $accessionRepository;

    public function __construct()
    {
        $this->photoRepository = new PhotoRepository();
        $this->imageService = new ImageService();
        $this->accessionRepository = new AccessionRepository();
    }

    /**
     * GET /photos
     * Get paginated list of photos
     */
    public function index(): string
    {
        try {
            $page = (int) $this->getQuery('page', 1);
            $perPage = (int) $this->getQuery('per_page', 50);
            $year = $this->getQuery('year');
            $search = $this->getQuery('search');
            
            // Validate pagination parameters
            if ($page < 1) $page = 1;
            if ($perPage < 1 || $perPage > 500) $perPage = 500;
            
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
            
            // Validate file type (images and videos)
            $allowedTypes = [
                'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
                'video/quicktime', 'video/mp4', 'video/avi', 'video/x-msvideo'
            ];
            $mimeType = null;
            
            // Try Fileinfo extension first (most reliable)
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            }
            // Fallback to mime_content_type() if available
            elseif (function_exists('mime_content_type')) {
                $mimeType = mime_content_type($file['tmp_name']);
            }
            // Final fallback: check file extension
            else {
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $extensionMap = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'mov' => 'video/quicktime',
                    'mp4' => 'video/mp4',
                    'avi' => 'video/avi'
                ];
                $mimeType = $extensionMap[$extension] ?? null;
            }
            
            if (!$mimeType || !in_array($mimeType, $allowedTypes)) {
                return $this->error('Invalid file type. Only images (JPG, PNG, GIF) and videos (MOV, MP4, AVI) are allowed', 400);
            }
            
            // Get additional data from POST
            $cplNum = trim((string) $this->getPost('cpl_num'));
            $year = trim((string) $this->getPost('year'));
            $login = $_SERVER['REMOTE_USER'] ?? 'system';

            // Validate required fields
            $error = $this->validateRequired(
                ['cpl_num' => $cplNum, 'year' => $year],
                ['cpl_num', 'year']
            );
            if ($error) {
                return $this->error($error, 400);
            }

            // Validate CPL number format (digits only, allow leading zeros)
            if (!ctype_digit($cplNum)) {
                return $this->error("Invalid CPL number '{$cplNum}'", 400);
            }

            // Validate year format and reasonable range
            if (!ctype_digit($year) || (int) $year < 1900 || (int) $year > 2100) {
                return $this->error("Invalid year '{$year}'. Expected a year between 1900 and 2100.", 400);
            }

            // Validate CPL accession exists and obtain suffix
            $accession = $this->accessionRepository->getAccessionByNumYear((int)$cplNum, (int)$year);
            if (!$accession) {
                return $this->error("Accession not found for CPL number '{$cplNum}' and year '{$year}'", 400);
            }
            $suffix = $accession['suffix'] ?? '';
            
            // Determine file extension; filename will be generated once the upload directory exists
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            
            // Create upload directory if it doesn't exist
            $uploadDir = $_ENV['UPLOAD_DIR'] ?? '../uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            // Create year subdirectory
            $yearDir = $uploadDir . '/' . substr($year, -2);
            if (!is_dir($yearDir)) {
                mkdir($yearDir, 0755, true);
            }
            // Create thumbnails directory
            if(!is_dir($yearDir . '/thumbnails')) {
                mkdir($yearDir . '/thumbnails', 0755, true);
            }
            
            // Generate unique filename (adds -a, -b, ... if needed)
            $filename = PhotoFilenameService::generateFilename($yearDir, $cplNum, $year, $suffix ?? '', $extension);
            
            $uploadPath = $yearDir . '/' . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                return $this->error('Failed to save uploaded file', 500);
            }
            
            // Generate thumbnail for images only (videos will use browser video element)
            if (strpos($mimeType, 'image/') === 0) {
                $thumbnailGenerated = $this->imageService->generateThumbnail($uploadPath, $yearDir . '/thumbnails/' . $filename, 350, 263);
                if (!$thumbnailGenerated) {
                    // Log warning but don't fail the upload
                    error_log("Warning: Failed to generate thumbnail for {$filename}");
                }
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
    // Todo: Implement authentication/authorization
    public function delete(string $id): string
    {
        try {
            $photo = $this->photoRepository->getById((int) $id);
            
            if (!$photo) {
                return $this->error('Photo not found', 404);
            }
            
            // Delete file from filesystem
            $uploadDir = $_ENV['UPLOAD_DIR'] ?? '../uploads';
            $filePath = $uploadDir . '/' . substr($photo['year'], -2) . '/' . $photo['filename'];
            
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            if(file_exists($uploadDir . '/' . substr($photo['year'], -2) . '/thumbnails/' . $photo['filename'])) {
                unlink($uploadDir . '/' . substr($photo['year'], -2) . '/thumbnails/' . $photo['filename']);
            }
            
            // Delete from database
            $this->photoRepository->delete((int)$id);
            
            return $this->success(null, 'Photo deleted successfully');
            
        } catch (\Exception $e) {
            return $this->error('Failed to delete photo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Serve an uploaded image file (original)
     */
    public function serveUpload(string $year, string $filename): string
    {
        return $this->serveFile($year, $filename, false);
    }

    /**
     * Serve a thumbnail image file
     */
    public function serveThumbnail(string $year, string $filename): string
    {
        return $this->serveFile($year, $filename, true);
    }

    /**
     * Common file serving helper
     */
    private function serveFile(string $year, string $filename, bool $thumbnail): string
    {
        // Normalize year to 2-digit folder
        $yearFolder = strlen($year) === 4 ? substr($year, -2) : $year;

        $uploadDir = realpath($_ENV['UPLOAD_DIR']);
        if (!$uploadDir) {
            return $this->error('Upload directory not configured', 500);
        }

        $safeYear = basename($yearFolder);
        $safeFile = basename($filename);

        $sub = $thumbnail ? '/thumbnails' : '';
        $path = $uploadDir . '/' . $safeYear . $sub . '/' . $safeFile;
        
        // Prevent path traversal
        $realPath = realpath($path);
        $realUploadDir = realpath($uploadDir . '/' . $safeYear . $sub);

        if (!$realPath || !$realUploadDir || strpos($realPath, $realUploadDir) !== 0 || !file_exists($realPath)) {
            http_response_code(404);
            return json_encode(['error' => 'File not found']);
        }

        // Determine mime type
        $mimeType = null;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $realPath);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($realPath);
        }
        
        // Fallback for common video extensions if MIME detection fails
        if (!$mimeType || $mimeType === 'application/octet-stream') {
            $extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
            $mimeMap = [
                'mov' => 'video/quicktime',
                'mp4' => 'video/mp4',
                'avi' => 'video/x-msvideo',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif'
            ];
            $mimeType = $mimeMap[$extension] ?? 'application/octet-stream';
        }

        // Override extension-based MIME for .mov files
        $extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION)); 
        if ($extension === 'mov') { $mimeType = 'video/quicktime'; }

        // Determine caching policy
        $thumbMaxAge = isset($_ENV['THUMB_MAX_AGE']) ? (int) $_ENV['THUMB_MAX_AGE'] : 2592000;
        $origMaxAge = isset($_ENV['ORIGINAL_MAX_AGE']) ? (int) $_ENV['ORIGINAL_MAX_AGE'] : 86400;
        $thumbImmutable = isset($_ENV['THUMB_IMMUTABLE']) ? filter_var($_ENV['THUMB_IMMUTABLE'], FILTER_VALIDATE_BOOLEAN) : true;

        $maxAge = $thumbnail ? $thumbMaxAge : $origMaxAge;
        $immutable = $thumbnail ? $thumbImmutable : false;

        // ETag and Last-Modified
        $mtime = filemtime($realPath);
        $size = filesize($realPath);
        $etag = '"' . sha1($mtime . '-' . $size) . '"';
        $lastModified = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';

        // Conditional requests
        $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? null;
        $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? null;

        if ($ifNoneMatch !== null && trim($ifNoneMatch) === $etag) {
            http_response_code(304);
            header('Cache-Control: public, max-age=' . $maxAge . ($immutable ? ', immutable' : ''));
            exit;
        }

        if ($ifModifiedSince !== null && strtotime($ifModifiedSince) >= $mtime) {
            http_response_code(304);
            header('Cache-Control: public, max-age=' . $maxAge . ($immutable ? ', immutable' : ''));
            exit;
        }

        // Send headers and file
        header('Content-Type: ' . $mimeType);
        header('Cache-Control: public, max-age=' . $maxAge . ($immutable ? ', immutable' : ''));
        header('ETag: ' . $etag);
        header('Last-Modified: ' . $lastModified);
        header('Accept-Ranges: bytes');
        header('Content-Disposition: inline; filename="' . $safeFile . '"');

        // Handle range requests (essential for video streaming)
        $rangeHeader = $_SERVER['HTTP_RANGE'] ?? null;
        
        if ($rangeHeader && preg_match('/bytes=(\d*)-(\d*)/', $rangeHeader, $matches)) {
            $start = $matches[1] !== '' ? (int)$matches[1] : 0;
            $end = $matches[2] !== '' ? (int)$matches[2] : $size - 1;
            
            // Ensure valid range
            if ($start >= $size || $end >= $size || $start > $end) {
                http_response_code(416); // Range Not Satisfiable
                header('Content-Range: bytes */' . $size);
                exit;
            }
            
            $length = $end - $start + 1;
            
            // Send partial content response
            while (ob_get_level()) { ob_end_clean(); }

            http_response_code(206);
            header('Content-Length: ' . $length);
            header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
            
            // Stream the requested range with better buffering
            $fp = fopen($realPath, 'rb');
            if ($fp) {
                fseek($fp, $start);
                $bytesRemaining = $length;
                
                while ($bytesRemaining > 0 && !feof($fp)) {
                    // Use larger chunks for better performance
                    $chunkSize = min(65536, $bytesRemaining); // 64KB chunks
                    $data = fread($fp, $chunkSize);
                    
                    if ($data === false) break;
                    
                    echo $data;
                    $bytesRemaining -= strlen($data);
                    
                    // Check if client disconnected
                    if (connection_aborted()) break;
                    
                    // Flush output to client
                    if (ob_get_level()) ob_flush();
                    flush();
                }
                fclose($fp);
            }
            exit;
        }
        
        // Standard full file response
        header('Content-Length: ' . $size);
        readfile($realPath);
        exit;
    }
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

    public function reassign(string $id): string
    {
        try {
            $photo = $this->photoRepository->getById((int) $id);
            
            if (!$photo) {
                return $this->error('Photo not found', 404);
            }

            $cplNum = trim((string) $this->getPost('cpl_num'));
            $year = trim((string) $this->getPost('year'));

            // Validate required fields
            $error = $this->validateRequired(
                ['cpl_num' => $cplNum, 'year' => $year],
                ['cpl_num', 'year']
            );
            if ($error) {
                return $this->error($error, 400);
            }

            // Validate CPL number format (digits only, allow leading zeros)
            if (!ctype_digit($cplNum)) {
                return $this->error("Invalid CPL number '{$cplNum}'", 400);
            }

            // Validate year format and reasonable range
            if (!ctype_digit($year) || (int) $year < 1900 || (int) $year > 2100) {
                return $this->error("Invalid year '{$year}'. Expected a year between 1900 and 2100.", 400);
            }

            // Validate CPL accession exists and obtain suffix
            $accession = $this->accessionRepository->getAccessionByNumYear((int)$cplNum, (int)$year);
            if (!$accession) {
                return $this->error("Accession not found for CPL number '{$cplNum}' and year '{$year}'", 400);
            }
            $suffix = $accession['suffix'] ?? '';

            // Update photo record
            $updatedPhoto = $this->photoRepository->update((int)$id, [
                'cpl_num' => $cplNum,
                'suffix' => $suffix,
                'year' => substr($year, -2)
            ]);

            if (!$updatedPhoto) {
                return $this->error('Failed to update photo record', 500);
            }

            return $this->success($updatedPhoto, 'Photo reassigned successfully');
            
        } catch (\Exception $e) {
            return $this->error('Failed to reassign photo: ' . $e->getMessage(), 500);
        }
    }
}
