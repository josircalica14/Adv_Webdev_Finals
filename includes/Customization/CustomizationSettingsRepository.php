<?php

namespace Customization;

use PDO;
use PDOException;

/**
 * CustomizationSettingsRepository
 * 
 * Handles database operations for customization settings.
 * Validates: Requirements 6.1, 6.2, 6.3, 6.4
 */
class CustomizationSettingsRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Find customization settings by portfolio ID
     */
    public function findByPortfolioId(int $portfolioId): ?CustomizationSettings {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM customization_settings WHERE portfolio_id = :portfolio_id"
            );
            $stmt->execute(['portfolio_id' => $portfolioId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }

            return CustomizationSettings::fromArray($data);
        } catch (PDOException $e) {
            error_log("Error finding customization settings: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new customization settings
     */
    public function create(CustomizationSettings $settings): ?int {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO customization_settings 
                (portfolio_id, theme, layout, primary_color, accent_color, heading_font, body_font)
                VALUES (:portfolio_id, :theme, :layout, :primary_color, :accent_color, :heading_font, :body_font)"
            );

            $stmt->execute([
                'portfolio_id' => $settings->portfolioId,
                'theme' => $settings->theme,
                'layout' => $settings->layout,
                'primary_color' => $settings->primaryColor,
                'accent_color' => $settings->accentColor,
                'heading_font' => $settings->headingFont,
                'body_font' => $settings->bodyFont
            ]);

            return (int)$this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating customization settings: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update existing customization settings
     */
    public function update(CustomizationSettings $settings): bool {
        try {
            $stmt = $this->db->prepare(
                "UPDATE customization_settings 
                SET theme = :theme,
                    layout = :layout,
                    primary_color = :primary_color,
                    accent_color = :accent_color,
                    heading_font = :heading_font,
                    body_font = :body_font
                WHERE portfolio_id = :portfolio_id"
            );

            return $stmt->execute([
                'portfolio_id' => $settings->portfolioId,
                'theme' => $settings->theme,
                'layout' => $settings->layout,
                'primary_color' => $settings->primaryColor,
                'accent_color' => $settings->accentColor,
                'heading_font' => $settings->headingFont,
                'body_font' => $settings->bodyFont
            ]);
        } catch (PDOException $e) {
            error_log("Error updating customization settings: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete customization settings by portfolio ID
     */
    public function deleteByPortfolioId(int $portfolioId): bool {
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM customization_settings WHERE portfolio_id = :portfolio_id"
            );
            return $stmt->execute(['portfolio_id' => $portfolioId]);
        } catch (PDOException $e) {
            error_log("Error deleting customization settings: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if customization settings exist for a portfolio
     */
    public function existsForPortfolio(int $portfolioId): bool {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM customization_settings WHERE portfolio_id = :portfolio_id"
            );
            $stmt->execute(['portfolio_id' => $portfolioId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking customization settings existence: " . $e->getMessage());
            return false;
        }
    }
}
