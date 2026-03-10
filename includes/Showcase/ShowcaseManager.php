<?php

namespace Showcase;

use PDO;
use Cache\CacheManager;

/**
 * ShowcaseManager - Manages public portfolio showcase and search functionality
 * 
 * Provides methods for retrieving, searching, filtering, and sorting public portfolios
 * for display in the showcase interface.
 */
class ShowcaseManager
{
    private PDO $db;
    private ?CacheManager $cache;
    private const ITEMS_PER_PAGE = 20;
    private const CACHE_TTL = 300; // 5 minutes

    public function __construct(PDO $db, ?CacheManager $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache;
    }

    /**
     * Get public portfolios with pagination
     * Only returns portfolios that have at least one portfolio item
     * 
     * @param int $page Page number (1-indexed)
     * @param int $perPage Number of items per page (default: 20)
     * @return array Paginated result with portfolios and metadata
     */
    public function getPublicPortfolios(int $page = 1, int $perPage = self::ITEMS_PER_PAGE): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100); // Cap at 100 per page
        
        // Try to get from cache
        if ($this->cache) {
            $cacheKey = "portfolios_public_p{$page}_pp{$perPage}";
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }
        
        $offset = ($page - 1) * $perPage;

        // Get total count (cached separately for better performance)
        $total = $this->getCachedPublicPortfolioCount();

        // Get paginated portfolios with optimized query
        // Only include portfolios that have at least one portfolio item
        $stmt = $this->db->prepare("
            SELECT p.id, p.user_id, p.is_public, p.view_count, p.created_at, p.updated_at,
                   u.id as user_id, u.full_name, u.username, u.program, 
                   u.bio, u.profile_photo_path,
                   COUNT(pi.id) as item_count
            FROM portfolios p
            INNER JOIN users u ON p.user_id = u.id
            LEFT JOIN portfolio_items pi ON p.id = pi.portfolio_id
            WHERE p.is_public = 1
            GROUP BY p.id, p.user_id, p.is_public, p.view_count, p.created_at, p.updated_at,
                     u.id, u.full_name, u.username, u.program, u.bio, u.profile_photo_path
            HAVING item_count > 0
            ORDER BY p.updated_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $portfolios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $portfolios[] = $this->buildPortfolioWithUser($row);
        }

        $result = [
            'items' => $portfolios,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int) ceil($total / $perPage)
        ];
        
        // Cache the result
        if ($this->cache) {
            $this->cache->set($cacheKey, $result, self::CACHE_TTL);
        }
        
        return $result;
    }

    /**
     * Search portfolios by query matching name, bio, or tags
     * 
     * @param string $query Search query
     * @param int $page Page number (1-indexed)
     * @param int $perPage Number of items per page
     * @return array Paginated result with matching portfolios
     */
    public function searchPortfolios(string $query, int $page = 1, int $perPage = self::ITEMS_PER_PAGE): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $offset = ($page - 1) * $perPage;
        $searchTerm = '%' . $query . '%';

        // Get total count
        $countStmt = $this->db->prepare("
            SELECT COUNT(DISTINCT p.id) as total
            FROM portfolios p
            INNER JOIN users u ON p.user_id = u.id
            LEFT JOIN portfolio_items pi ON p.id = pi.portfolio_id
            WHERE p.is_public = 1
            AND (
                u.full_name LIKE :search1
                OR u.bio LIKE :search2
                OR JSON_SEARCH(pi.tags, 'one', :search3) IS NOT NULL
            )
        ");
        $countStmt->bindValue(':search1', $searchTerm, PDO::PARAM_STR);
        $countStmt->bindValue(':search2', $searchTerm, PDO::PARAM_STR);
        $countStmt->bindValue(':search3', $query, PDO::PARAM_STR);
        $countStmt->execute();
        $total = (int) $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get paginated search results
        $stmt = $this->db->prepare("
            SELECT DISTINCT p.*, u.id as user_id, u.full_name, u.username, u.program, 
                   u.bio, u.profile_photo_path
            FROM portfolios p
            INNER JOIN users u ON p.user_id = u.id
            LEFT JOIN portfolio_items pi ON p.id = pi.portfolio_id
            WHERE p.is_public = 1
            AND (
                u.full_name LIKE :search1
                OR u.bio LIKE :search2
                OR JSON_SEARCH(pi.tags, 'one', :search3) IS NOT NULL
            )
            ORDER BY p.updated_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':search1', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':search2', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':search3', $query, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $portfolios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $portfolios[] = $this->buildPortfolioWithUser($row);
        }

        return [
            'items' => $portfolios,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int) ceil($total / $perPage),
            'query' => $query
        ];
    }

    /**
     * Filter portfolios by program (BSIT or CSE)
     * 
     * @param string $program Program to filter by ('BSIT', 'CSE', or 'All')
     * @param int $page Page number (1-indexed)
     * @param int $perPage Number of items per page
     * @return array Paginated result with filtered portfolios
     */
    public function filterByProgram(string $program, int $page = 1, int $perPage = self::ITEMS_PER_PAGE): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $offset = ($page - 1) * $perPage;

        // If 'All' is selected, return all public portfolios
        if (strtolower($program) === 'all') {
            return $this->getPublicPortfolios($page, $perPage);
        }

        // Validate program
        if (!in_array(strtoupper($program), ['BSIT', 'CSE'])) {
            return [
                'items' => [],
                'total' => 0,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => 0,
                'program' => $program
            ];
        }

        // Get total count
        $countStmt = $this->db->prepare("
            SELECT COUNT(DISTINCT p.id) as total
            FROM portfolios p
            INNER JOIN users u ON p.user_id = u.id
            INNER JOIN portfolio_items pi ON p.id = pi.portfolio_id
            WHERE p.is_public = 1 AND u.program = :program
        ");
        $countStmt->bindValue(':program', strtoupper($program), PDO::PARAM_STR);
        $countStmt->execute();
        $total = (int) $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get paginated filtered portfolios
        $stmt = $this->db->prepare("
            SELECT p.*, u.id as user_id, u.full_name, u.username, u.program, 
                   u.bio, u.profile_photo_path
            FROM portfolios p
            INNER JOIN users u ON p.user_id = u.id
            INNER JOIN portfolio_items pi ON p.id = pi.portfolio_id
            WHERE p.is_public = 1 AND u.program = :program
            GROUP BY p.id, p.user_id, p.is_public, p.view_count, p.created_at, p.updated_at,
                     u.id, u.full_name, u.username, u.program, u.bio, u.profile_photo_path
            ORDER BY p.updated_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':program', strtoupper($program), PDO::PARAM_STR);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $portfolios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $portfolios[] = $this->buildPortfolioWithUser($row);
        }

        return [
            'items' => $portfolios,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int) ceil($total / $perPage),
            'program' => $program
        ];
    }

    /**
     * Sort portfolios by specified criteria
     * 
     * @param array $portfolios Array of portfolio data
     * @param string $sortBy Sort criteria ('updated' or 'name')
     * @return array Sorted portfolios
     */
    public function sortPortfolios(array $portfolios, string $sortBy): array
    {
        $sortBy = strtolower($sortBy);

        switch ($sortBy) {
            case 'name':
                usort($portfolios, function ($a, $b) {
                    return strcasecmp($a['user']['full_name'], $b['user']['full_name']);
                });
                break;

            case 'updated':
            default:
                usort($portfolios, function ($a, $b) {
                    return strtotime($b['updated_at']) - strtotime($a['updated_at']);
                });
                break;
        }

        return $portfolios;
    }

    /**
     * Search and filter portfolios with combined criteria
     * 
     * @param array $criteria Search criteria (query, program, sortBy)
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Paginated result with filtered and sorted portfolios
     */
    public function searchAndFilter(array $criteria, int $page = 1, int $perPage = self::ITEMS_PER_PAGE): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $offset = ($page - 1) * $perPage;

        $query = $criteria['query'] ?? '';
        $program = $criteria['program'] ?? 'All';
        $sortBy = $criteria['sortBy'] ?? 'updated';

        // Build WHERE clause
        $whereConditions = ['p.is_public = 1'];
        $params = [];

        // Add program filter
        if (strtolower($program) !== 'all' && in_array(strtoupper($program), ['BSIT', 'CSE'])) {
            $whereConditions[] = 'u.program = :program';
            $params[':program'] = strtoupper($program);
        }

        // Add search query
        if (!empty($query)) {
            $whereConditions[] = '(
                u.full_name LIKE :search1
                OR u.bio LIKE :search2
                OR JSON_SEARCH(pi.tags, \'one\', :search3) IS NOT NULL
            )';
            $searchTerm = '%' . $query . '%';
            $params[':search1'] = $searchTerm;
            $params[':search2'] = $searchTerm;
            $params[':search3'] = $query;
        }

        $whereClause = implode(' AND ', $whereConditions);

        // Determine ORDER BY clause
        $orderBy = match (strtolower($sortBy)) {
            'name' => 'u.full_name ASC',
            default => 'p.updated_at DESC'
        };

        // Get total count
        $countSql = "
            SELECT COUNT(DISTINCT p.id) as total
            FROM portfolios p
            INNER JOIN users u ON p.user_id = u.id
            " . (!empty($query) ? "LEFT JOIN portfolio_items pi ON p.id = pi.portfolio_id" : "") . "
            WHERE {$whereClause}
        ";
        $countStmt = $this->db->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get paginated results
        $sql = "
            SELECT DISTINCT p.*, u.id as user_id, u.full_name, u.username, u.program, 
                   u.bio, u.profile_photo_path
            FROM portfolios p
            INNER JOIN users u ON p.user_id = u.id
            " . (!empty($query) ? "LEFT JOIN portfolio_items pi ON p.id = pi.portfolio_id" : "") . "
            WHERE {$whereClause}
            ORDER BY {$orderBy}
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $portfolios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $portfolios[] = $this->buildPortfolioWithUser($row);
        }

        return [
            'items' => $portfolios,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int) ceil($total / $perPage),
            'criteria' => $criteria
        ];
    }

    /**
     * Build portfolio array with user information
     * 
     * @param array $row Database row with portfolio and user data
     * @return array Portfolio data with nested user information
     */
    private function buildPortfolioWithUser(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'user_id' => (int) $row['user_id'],
            'is_public' => (bool) $row['is_public'],
            'view_count' => (int) $row['view_count'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
            'user' => [
                'id' => (int) $row['user_id'],
                'full_name' => $row['full_name'],
                'username' => $row['username'],
                'program' => $row['program'],
                'bio' => $row['bio'],
                'profile_photo_path' => $row['profile_photo_path']
            ]
        ];
    }
    
    /**
     * Get cached public portfolio count
     * Only counts portfolios that have at least one portfolio item
     * 
     * @return int Total count of public portfolios with content
     */
    private function getCachedPublicPortfolioCount(): int
    {
        if ($this->cache) {
            return $this->cache->remember('portfolios_public_count', function() {
                $stmt = $this->db->prepare("
                    SELECT COUNT(DISTINCT p.id) as total 
                    FROM portfolios p
                    INNER JOIN portfolio_items pi ON p.id = pi.portfolio_id
                    WHERE p.is_public = 1
                ");
                $stmt->execute();
                return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            }, self::CACHE_TTL);
        }
        
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT p.id) as total 
            FROM portfolios p
            INNER JOIN portfolio_items pi ON p.id = pi.portfolio_id
            WHERE p.is_public = 1
        ");
        $stmt->execute();
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    /**
     * Invalidate cache for portfolio updates
     * 
     * @param int|null $portfolioId Portfolio ID (null = clear all)
     */
    public function invalidateCache(?int $portfolioId = null): void
    {
        if (!$this->cache) {
            return;
        }
        
        // Clear all portfolio-related cache
        $this->cache->delete('portfolios_public_count');
        
        // Clear paginated results (clear first 10 pages)
        for ($page = 1; $page <= 10; $page++) {
            $this->cache->delete("portfolios_public_p{$page}_pp" . self::ITEMS_PER_PAGE);
        }
    }
}
