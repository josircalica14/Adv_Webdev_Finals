<?php
/**
 * PortfolioItemRepository
 * 
 * Handles database operations for PortfolioItem entities
 */

namespace Portfolio;

use PDO;
use PDOException;

class PortfolioItemRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Create a new portfolio item
     */
    public function create(PortfolioItem $item): ?int {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO portfolio_items 
                 (portfolio_id, item_type, title, description, item_date, tags, links, is_visible, display_order) 
                 VALUES (:portfolio_id, :item_type, :title, :description, :item_date, :tags, :links, :is_visible, :display_order)"
            );

            $stmt->execute([
                ':portfolio_id' => $item->getPortfolioId(),
                ':item_type' => $item->getItemType(),
                ':title' => $item->getTitle(),
                ':description' => $item->getDescription(),
                ':item_date' => $item->getItemDate(),
                ':tags' => json_encode($item->getTags()),
                ':links' => json_encode($item->getLinks()),
                ':is_visible' => $item->isVisible() ? 1 : 0,
                ':display_order' => $item->getDisplayOrder()
            ]);

            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating portfolio item: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find portfolio item by ID
     */
    public function findById(int $id): ?PortfolioItem {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM portfolio_items WHERE id = :id"
            );
            $stmt->execute([':id' => $id]);
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? PortfolioItem::fromArray($data) : null;
        } catch (PDOException $e) {
            error_log("Error finding portfolio item by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find all items for a portfolio
     */
    public function findByPortfolioId(int $portfolioId, bool $visibleOnly = false): array {
        try {
            $sql = "SELECT * FROM portfolio_items WHERE portfolio_id = :portfolio_id";
            if ($visibleOnly) {
                $sql .= " AND is_visible = 1";
            }
            $sql .= " ORDER BY display_order ASC, created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':portfolio_id' => $portfolioId]);
            
            $items = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $items[] = PortfolioItem::fromArray($data);
            }
            return $items;
        } catch (PDOException $e) {
            error_log("Error finding portfolio items: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update portfolio item
     */
    public function update(PortfolioItem $item): bool {
        try {
            $stmt = $this->db->prepare(
                "UPDATE portfolio_items 
                 SET item_type = :item_type, 
                     title = :title, 
                     description = :description, 
                     item_date = :item_date, 
                     tags = :tags, 
                     links = :links, 
                     is_visible = :is_visible, 
                     display_order = :display_order 
                 WHERE id = :id"
            );

            return $stmt->execute([
                ':id' => $item->getId(),
                ':item_type' => $item->getItemType(),
                ':title' => $item->getTitle(),
                ':description' => $item->getDescription(),
                ':item_date' => $item->getItemDate(),
                ':tags' => json_encode($item->getTags()),
                ':links' => json_encode($item->getLinks()),
                ':is_visible' => $item->isVisible() ? 1 : 0,
                ':display_order' => $item->getDisplayOrder()
            ]);
        } catch (PDOException $e) {
            error_log("Error updating portfolio item: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete portfolio item
     */
    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM portfolio_items WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error deleting portfolio item: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update item visibility
     */
    public function updateVisibility(int $itemId, bool $isVisible): bool {
        try {
            $stmt = $this->db->prepare(
                "UPDATE portfolio_items SET is_visible = :is_visible WHERE id = :id"
            );
            return $stmt->execute([
                ':id' => $itemId,
                ':is_visible' => $isVisible ? 1 : 0
            ]);
        } catch (PDOException $e) {
            error_log("Error updating item visibility: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update display order for multiple items
     */
    public function updateDisplayOrders(array $itemOrders): bool {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "UPDATE portfolio_items SET display_order = :display_order WHERE id = :id"
            );

            foreach ($itemOrders as $itemId => $order) {
                $stmt->execute([
                    ':id' => $itemId,
                    ':display_order' => $order
                ]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error updating display orders: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the next display order for a portfolio
     */
    public function getNextDisplayOrder(int $portfolioId): int {
        try {
            $stmt = $this->db->prepare(
                "SELECT MAX(display_order) as max_order FROM portfolio_items WHERE portfolio_id = :portfolio_id"
            );
            $stmt->execute([':portfolio_id' => $portfolioId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result['max_order'] ?? -1) + 1;
        } catch (PDOException $e) {
            error_log("Error getting next display order: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Count items for a portfolio
     */
    public function countByPortfolioId(int $portfolioId): int {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM portfolio_items WHERE portfolio_id = :portfolio_id"
            );
            $stmt->execute([':portfolio_id' => $portfolioId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error counting portfolio items: " . $e->getMessage());
            return 0;
        }
    }
}
