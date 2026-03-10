<?php
/**
 * PortfolioRepository
 * 
 * Handles database operations for Portfolio entities
 */

namespace Portfolio;

use PDO;
use PDOException;

class PortfolioRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Create a new portfolio
     */
    public function create(Portfolio $portfolio): ?int {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO portfolios (user_id, is_public, view_count) 
                 VALUES (:user_id, :is_public, :view_count)"
            );

            $stmt->execute([
                ':user_id' => $portfolio->getUserId(),
                ':is_public' => $portfolio->isPublic() ? 1 : 0,
                ':view_count' => $portfolio->getViewCount()
            ]);

            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating portfolio: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find portfolio by ID
     */
    public function findById(int $id): ?Portfolio {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM portfolios WHERE id = :id"
            );
            $stmt->execute([':id' => $id]);
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? Portfolio::fromArray($data) : null;
        } catch (PDOException $e) {
            error_log("Error finding portfolio by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find portfolio by user ID
     */
    public function findByUserId(int $userId): ?Portfolio {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM portfolios WHERE user_id = :user_id"
            );
            $stmt->execute([':user_id' => $userId]);
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? Portfolio::fromArray($data) : null;
        } catch (PDOException $e) {
            error_log("Error finding portfolio by user ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update portfolio
     */
    public function update(Portfolio $portfolio): bool {
        try {
            $stmt = $this->db->prepare(
                "UPDATE portfolios 
                 SET is_public = :is_public, view_count = :view_count 
                 WHERE id = :id"
            );

            return $stmt->execute([
                ':id' => $portfolio->getId(),
                ':is_public' => $portfolio->isPublic() ? 1 : 0,
                ':view_count' => $portfolio->getViewCount()
            ]);
        } catch (PDOException $e) {
            error_log("Error updating portfolio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete portfolio
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM portfolios WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error deleting portfolio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all public portfolios
     */
    public function findAllPublic(int $limit = 20, int $offset = 0): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM portfolios 
                 WHERE is_public = 1 
                 ORDER BY updated_at DESC 
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $portfolios = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $portfolios[] = Portfolio::fromArray($data);
            }
            return $portfolios;
        } catch (PDOException $e) {
            error_log("Error finding public portfolios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Count all public portfolios
     */
    public function countPublic(): int {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM portfolios WHERE is_public = 1");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting public portfolios: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update portfolio visibility
     */
    public function updateVisibility(int $portfolioId, bool $isPublic): bool {
        try {
            $stmt = $this->db->prepare(
                "UPDATE portfolios SET is_public = :is_public WHERE id = :id"
            );
            return $stmt->execute([
                ':id' => $portfolioId,
                ':is_public' => $isPublic ? 1 : 0
            ]);
        } catch (PDOException $e) {
            error_log("Error updating portfolio visibility: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(int $portfolioId): bool {
        try {
            $stmt = $this->db->prepare(
                "UPDATE portfolios SET view_count = view_count + 1 WHERE id = :id"
            );
            return $stmt->execute([':id' => $portfolioId]);
        } catch (PDOException $e) {
            error_log("Error incrementing view count: " . $e->getMessage());
            return false;
        }
    }
}
