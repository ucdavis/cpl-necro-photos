<?php

namespace App\Repositories;

use App\Core\Repository;
use App\Services\PhotoFilenameService;

class PhotoRepository extends Repository
{
    /**
     * Get paginated photos
     * 
     * @param int $page Page number (1-indexed)
     * @param int $perPage Number of items per page
     * @param int|null $year Filter by year (2-digit format)
     * @return array
     */
    public function getPaginated(int $page = 1, int $perPage = 20, ?int $year = null): array
    {
        $offset = ($page - 1) * $perPage;
        
        $where = $year ? 'WHERE year = ?' : '';
        $params = $year ? [$year] : [];
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM photos $where";
        $countResult = $this->queryOne($countSql, $params);
        $total = (int) $countResult['total'];
        
        // Get paginated data
        $sql = "SELECT id, cpl_num, suffix, year, filename, size, date_uploaded, login
                FROM photos
                $where
                ORDER BY id DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $photos = $this->query($sql, $params);
        
        return [
            'data' => $photos,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ]
        ];
    }

    /**
     * Get a single photo by ID
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT id, cpl_num, suffix, year, filename, size, date_uploaded, login
                FROM photos
                WHERE id = ?";
        
        return $this->queryOne($sql, [$id]);
    }

    /**
     * Get photos by year
     */
    public function getByYear(int $year): array
    {
        $sql = "SELECT id, cpl_num, suffix, year, filename, size, date_uploaded, login
                FROM photos
                WHERE year = ?
                ORDER BY id DESC";
        
        return $this->query($sql, [$year]);
    }

    /**
     * Create a new photo record
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO photos (cpl_num, suffix, year, filename, size, date_uploaded, login)
                VALUES (?, ?, ?, ?, ?, NOW(), ?)";
        
        $params = [
            $data['cpl_num'],
            $data['suffix'],
            $data['year'],
            $data['filename'],
            $data['size'],
            $data['login'] ?? 'system'
        ];
        
        $this->execute($sql, $params);
        return $this->lastInsertId();
    }

    /**
     * Update an existing photo's accession information and filename.
     *
     * This will:
     * - Move/rename the underlying file (and thumbnail if present) into the
     *   correct year directory with a new CPL number / suffix.
     * - Choose the next available alphabetic sequence (-a, -b, ...) for the
     *   new accession based on existing files in the target directory.
     * - Update the database row with new cpl_num, suffix, year (2‑digit) and filename.
     *
     * @param int   $id   Photo ID
     * @param array $data ['cpl_num' => string|int, 'suffix' => string, 'year' => string|int]
     * @return array|null The updated photo row, or null on failure
     */
    public function update(int $id, array $data): ?array
    {
        // Fetch current photo data
        $photo = $this->getById($id);
        if (!$photo) {
            return null;
        }

        $cplNum = (string)($data['cpl_num'] ?? $photo['cpl_num']);
        $suffix = (string)($data['suffix'] ?? $photo['suffix']);
        // Year is stored as 2‑digit string in the photos table
        $yearTwoDigit = substr((string)($data['year'] ?? $photo['year']), -2);

        // Determine upload directory (same as in PhotoController)
        $uploadDir = $_ENV['UPLOAD_DIR'] ?? '../uploads';

        // Build current file paths
        $oldYearDir = rtrim($uploadDir, '/\\') . '/' . $photo['year'];
        $oldPath = $oldYearDir . '/' . $photo['filename'];
        $oldThumbPath = $oldYearDir . '/thumbnails/' . $photo['filename'];

        // Ensure destination year directory (and thumbnails) exists
        $newYearDir = rtrim($uploadDir, '/\\') . '/' . $yearTwoDigit;
        if (!is_dir($newYearDir)) {
            mkdir($newYearDir, 0755, true);
        }
        $newThumbDir = $newYearDir . '/thumbnails';
        if (!is_dir($newThumbDir)) {
            mkdir($newThumbDir, 0755, true);
        }

        // Derive new filename based on target accession and existing files
        $extension = pathinfo($photo['filename'], PATHINFO_EXTENSION);
        $newFilename = PhotoFilenameService::generateFilename(
            $newYearDir,
            $cplNum,
            $yearTwoDigit,
            $suffix,
            $extension
        );

        $newPath = $newYearDir . '/' . $newFilename;
        $newThumbPath = $newThumbDir . '/' . $newFilename;

        // Move original file
        if (is_file($oldPath)) {
            if (!rename($oldPath, $newPath)) {
                return null;
            }
        } else {
            // If the original file doesn't exist, abort to avoid corrupting DB
            return null;
        }

        // Move thumbnail if it exists (ignore failure if thumbnail missing)
        if (is_file($oldThumbPath)) {
            @rename($oldThumbPath, $newThumbPath);
        }

        // Update database row
        $sql = "UPDATE photos
                SET cpl_num = ?, suffix = ?, year = ?, filename = ?
                WHERE id = ?";

        $ok = $this->execute($sql, [
            $cplNum,
            $suffix,
            $yearTwoDigit,
            $newFilename,
            $id,
        ]);

        if (!$ok) {
            return null;
        }

        return $this->getById($id);
    }

    /**
     * Delete a photo
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM photos WHERE id = ?";
        return $this->execute($sql, [$id]);
    }


    /**
     * Search photos by various criteria
     */
    public function search(string $query, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $searchTerm = "%$query%";
        
        // Count total results
        $countSql = "SELECT COUNT(*) as total FROM photos 
                     WHERE cpl_num LIKE ? OR filename LIKE ? OR login LIKE ?";
        $countResult = $this->queryOne($countSql, [$searchTerm, $searchTerm, $searchTerm]);
        $total = (int) $countResult['total'];
        
        // Get paginated search results
        $sql = "SELECT id, cpl_num, suffix, year, filename, size, date_uploaded, login
                FROM photos
                WHERE cpl_num LIKE ? OR filename LIKE ? OR login LIKE ?
                ORDER BY id DESC
                LIMIT ? OFFSET ?";
        
        $photos = $this->query($sql, [$searchTerm, $searchTerm, $searchTerm, $perPage, $offset]);
        
        return [
            'data' => $photos,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total)
            ]
        ];
    }
}
