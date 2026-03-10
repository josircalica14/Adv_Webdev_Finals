<?php
/**
 * PortfolioManager
 * 
 * Manages portfolio operations including CRUD for portfolio items,
 * visibility control, and file handling
 */

namespace Portfolio;

use PDO;
use FileStorageManager;

class PortfolioManager {
    private PDO $db;
    private PortfolioRepository $portfolioRepo;
    private PortfolioItemRepository $itemRepo;
    private FileStorageManager $fileManager;

    public function __construct(PDO $db, FileStorageManager $fileManager) {
        $this->db = $db;
        $this->portfolioRepo = new PortfolioRepository($db);
        $this->itemRepo = new PortfolioItemRepository($db);
        $this->fileManager = $fileManager;
    }

    /**
     * Get or create portfolio for a user
     */
    public function getPortfolio(int $userId): ?Portfolio {
        $portfolio = $this->portfolioRepo->findByUserId($userId);
        
        // Create portfolio if it doesn't exist
        if (!$portfolio) {
            $portfolio = new Portfolio($userId, false); // Default to private
            $portfolioId = $this->portfolioRepo->create($portfolio);
            if ($portfolioId) {
                $portfolio->setId($portfolioId);
            } else {
                return null;
            }
        }
        
        return $portfolio;
    }

    /**
     * Get public portfolio by username
     */
    public function getPublicPortfolio(string $username): ?array {
        // This would need UserRepository to find user by username
        // For now, returning null - will be implemented when needed
        return null;
    }

    /**
     * Update portfolio visibility (public/private)
     */
    public function updateVisibility(int $userId, bool $isPublic): array {
        $portfolio = $this->getPortfolio($userId);
        
        if (!$portfolio) {
            return [
                'success' => false,
                'error' => 'Portfolio not found'
            ];
        }

        $success = $this->portfolioRepo->updateVisibility($portfolio->getId(), $isPublic);
        
        return [
            'success' => $success,
            'error' => $success ? null : 'Failed to update visibility'
        ];
    }

    /**
     * Create a new portfolio item
     */
    public function createItem(int $userId, array $data, array $files = []): array {
        // Validate required fields
        $validation = $this->validateItemData($data);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }

        // Get or create portfolio
        $portfolio = $this->getPortfolio($userId);
        if (!$portfolio) {
            return [
                'success' => false,
                'error' => 'Failed to get or create portfolio'
            ];
        }

        try {
            $this->db->beginTransaction();

            // Get next display order
            $displayOrder = $this->itemRepo->getNextDisplayOrder($portfolio->getId());

            // Create portfolio item
            $item = new PortfolioItem(
                portfolioId: $portfolio->getId(),
                itemType: $data['item_type'],
                title: $data['title'],
                description: $data['description'],
                itemDate: $data['item_date'] ?? null,
                tags: $data['tags'] ?? [],
                links: $data['links'] ?? [],
                isVisible: $data['is_visible'] ?? true,
                displayOrder: $displayOrder
            );

            $itemId = $this->itemRepo->create($item);
            if (!$itemId) {
                throw new \Exception('Failed to create portfolio item');
            }

            $item->setId($itemId);

            // Handle file uploads
            $uploadedFiles = [];
            if (!empty($files['uploaded_file']) && !empty($files['uploaded_file']['name'])) {
                // Handle single file upload
                if (is_array($files['uploaded_file']['name'])) {
                    // Multiple files
                    $fileCount = count($files['uploaded_file']['name']);
                    for ($i = 0; $i < $fileCount; $i++) {
                        if ($files['uploaded_file']['error'][$i] === UPLOAD_ERR_OK) {
                            $file = [
                                'name' => $files['uploaded_file']['name'][$i],
                                'type' => $files['uploaded_file']['type'][$i],
                                'tmp_name' => $files['uploaded_file']['tmp_name'][$i],
                                'error' => $files['uploaded_file']['error'][$i],
                                'size' => $files['uploaded_file']['size'][$i]
                            ];
                            $result = $this->fileManager->uploadFile($file, $userId, $itemId);
                            if ($result['success']) {
                                $uploadedFiles[] = $result['file'];
                            } else {
                                error_log("File upload failed: " . ($result['error'] ?? 'Unknown error'));
                            }
                        }
                    }
                } else {
                    // Single file
                    if ($files['uploaded_file']['error'] === UPLOAD_ERR_OK) {
                        $result = $this->fileManager->uploadFile($files['uploaded_file'], $userId, $itemId);
                        if ($result['success']) {
                            $uploadedFiles[] = $result['file'];
                        } else {
                            error_log("File upload failed: " . ($result['error'] ?? 'Unknown error'));
                        }
                    }
                }
            }

            $this->db->commit();

            return [
                'success' => true,
                'item' => $item->toArray(),
                'files' => $uploadedFiles
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error creating portfolio item: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to create portfolio item'
            ];
        }
    }

    /**
     * Update an existing portfolio item
     */
    public function updateItem(int $itemId, int $userId, array $data, array $files = []): array {
        // Get the item
        $item = $this->itemRepo->findById($itemId);
        if (!$item) {
            return [
                'success' => false,
                'error' => 'Portfolio item not found'
            ];
        }

        // Verify ownership
        $portfolio = $this->portfolioRepo->findById($item->getPortfolioId());
        if (!$portfolio || $portfolio->getUserId() !== $userId) {
            return [
                'success' => false,
                'error' => 'Access denied'
            ];
        }

        // Validate data
        $validation = $this->validateItemData($data, false);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }

        try {
            $this->db->beginTransaction();

            // Update item fields
            if (isset($data['item_type'])) {
                $item->setItemType($data['item_type']);
            }
            if (isset($data['title'])) {
                $item->setTitle($data['title']);
            }
            if (isset($data['description'])) {
                $item->setDescription($data['description']);
            }
            if (isset($data['item_date'])) {
                $item->setItemDate($data['item_date']);
            }
            if (isset($data['tags'])) {
                $item->setTags($data['tags']);
            }
            if (isset($data['links'])) {
                $item->setLinks($data['links']);
            }
            if (isset($data['is_visible'])) {
                $item->setVisible($data['is_visible']);
            }

            $success = $this->itemRepo->update($item);
            if (!$success) {
                throw new \Exception('Failed to update portfolio item');
            }

            // Handle new file uploads
            $uploadedFiles = [];
            if (!empty($files)) {
                foreach ($files as $file) {
                    $result = $this->fileManager->uploadFile($file, $userId, $itemId);
                    if ($result['success']) {
                        $uploadedFiles[] = $result['file'];
                    }
                }
            }

            $this->db->commit();

            return [
                'success' => true,
                'item' => $item->toArray(),
                'files' => $uploadedFiles
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error updating portfolio item: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to update portfolio item'
            ];
        }
    }

    /**
     * Delete a portfolio item
     */
    public function deleteItem(int $itemId, int $userId): array {
        // Get the item
        $item = $this->itemRepo->findById($itemId);
        if (!$item) {
            return [
                'success' => false,
                'error' => 'Portfolio item not found'
            ];
        }

        // Verify ownership
        $portfolio = $this->portfolioRepo->findById($item->getPortfolioId());
        if (!$portfolio || $portfolio->getUserId() !== $userId) {
            return [
                'success' => false,
                'error' => 'Access denied'
            ];
        }

        try {
            $this->db->beginTransaction();

            // Delete associated files (cascade delete will handle DB records)
            $this->fileManager->deleteFilesForItem($itemId);

            // Delete the item
            $success = $this->itemRepo->delete($itemId);
            if (!$success) {
                throw new \Exception('Failed to delete portfolio item');
            }

            $this->db->commit();

            return [
                'success' => true
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error deleting portfolio item: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to delete portfolio item'
            ];
        }
    }

    /**
     * Get all items for a user's portfolio
     */
    public function getItems(int $userId, bool $visibleOnly = false): array {
        $portfolio = $this->getPortfolio($userId);
        if (!$portfolio) {
            return [];
        }

        $items = $this->itemRepo->findByPortfolioId($portfolio->getId(), $visibleOnly);
        
        // Convert to arrays
        return array_map(function($item) {
            return $item->toArray();
        }, $items);
    }

    /**
     * Reorder portfolio items
     */
    public function reorderItems(int $userId, array $itemIds): array {
        $portfolio = $this->getPortfolio($userId);
        if (!$portfolio) {
            return [
                'success' => false,
                'error' => 'Portfolio not found'
            ];
        }

        // Verify all items belong to this portfolio
        $itemOrders = [];
        foreach ($itemIds as $order => $itemId) {
            $item = $this->itemRepo->findById($itemId);
            if (!$item || $item->getPortfolioId() !== $portfolio->getId()) {
                return [
                    'success' => false,
                    'error' => 'Invalid item ID or access denied'
                ];
            }
            $itemOrders[$itemId] = $order;
        }

        $success = $this->itemRepo->updateDisplayOrders($itemOrders);

        return [
            'success' => $success,
            'error' => $success ? null : 'Failed to reorder items'
        ];
    }

    /**
     * Update item visibility (show/hide)
     */
    public function updateItemVisibility(int $itemId, int $userId, bool $isVisible): array {
        // Get the item
        $item = $this->itemRepo->findById($itemId);
        if (!$item) {
            return [
                'success' => false,
                'error' => 'Portfolio item not found'
            ];
        }

        // Verify ownership
        $portfolio = $this->portfolioRepo->findById($item->getPortfolioId());
        if (!$portfolio || $portfolio->getUserId() !== $userId) {
            return [
                'success' => false,
                'error' => 'Access denied'
            ];
        }

        $success = $this->itemRepo->updateVisibility($itemId, $isVisible);

        return [
            'success' => $success,
            'error' => $success ? null : 'Failed to update item visibility'
        ];
    }

    /**
     * Validate portfolio item data
     */
    private function validateItemData(array $data, bool $requireAll = true): array {
        $errors = [];

        // Required fields
        if ($requireAll || isset($data['item_type'])) {
            if (empty($data['item_type'])) {
                $errors[] = 'Item type is required';
            } elseif (!in_array($data['item_type'], ['project', 'achievement', 'milestone', 'skill'])) {
                $errors[] = 'Invalid item type';
            }
        }

        if ($requireAll || isset($data['title'])) {
            if (empty($data['title'])) {
                $errors[] = 'Title is required';
            } elseif (strlen($data['title']) > 255) {
                $errors[] = 'Title must be 255 characters or less';
            }
        }

        if ($requireAll || isset($data['description'])) {
            if (empty($data['description'])) {
                $errors[] = 'Description is required';
            }
        }

        // Optional fields validation
        if (isset($data['item_date']) && !empty($data['item_date'])) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['item_date'])) {
                $errors[] = 'Invalid date format (use YYYY-MM-DD)';
            }
        }

        if (isset($data['tags']) && !is_array($data['tags'])) {
            $errors[] = 'Tags must be an array';
        }

        if (isset($data['links']) && !is_array($data['links'])) {
            $errors[] = 'Links must be an array';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
