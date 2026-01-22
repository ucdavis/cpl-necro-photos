<?php

namespace App\Repositories;

use App\Core\Repository;

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
