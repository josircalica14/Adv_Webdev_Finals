<?php
/**
 * PortfolioItem Entity
 * 
 * Represents an individual portfolio item (project, achievement, milestone, skill)
 */

namespace Portfolio;

class PortfolioItem {
    private ?int $id;
    private int $portfolioId;
    private string $itemType;
    private string $title;
    private string $description;
    private ?string $itemDate;
    private array $tags;
    private array $links;
    private bool $isVisible;
    private int $displayOrder;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(
        int $portfolioId,
        string $itemType,
        string $title,
        string $description,
        ?string $itemDate = null,
        array $tags = [],
        array $links = [],
        bool $isVisible = true,
        int $displayOrder = 0,
        ?int $id = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->portfolioId = $portfolioId;
        $this->itemType = $itemType;
        $this->title = $title;
        $this->description = $description;
        $this->itemDate = $itemDate;
        $this->tags = $tags;
        $this->links = $links;
        $this->isVisible = $isVisible;
        $this->displayOrder = $displayOrder;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->updatedAt = $updatedAt ?? date('Y-m-d H:i:s');
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getPortfolioId(): int {
        return $this->portfolioId;
    }

    public function getItemType(): string {
        return $this->itemType;
    }

    public function setItemType(string $itemType): void {
        $this->itemType = $itemType;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

    public function getItemDate(): ?string {
        return $this->itemDate;
    }

    public function setItemDate(?string $itemDate): void {
        $this->itemDate = $itemDate;
    }

    public function getTags(): array {
        return $this->tags;
    }

    public function setTags(array $tags): void {
        $this->tags = $tags;
    }

    public function getLinks(): array {
        return $this->links;
    }

    public function setLinks(array $links): void {
        $this->links = $links;
    }

    public function isVisible(): bool {
        return $this->isVisible;
    }

    public function setVisible(bool $isVisible): void {
        $this->isVisible = $isVisible;
    }

    public function getDisplayOrder(): int {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): void {
        $this->displayOrder = $displayOrder;
    }

    public function getCreatedAt(): string {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string {
        return $this->updatedAt;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'portfolio_id' => $this->portfolioId,
            'item_type' => $this->itemType,
            'title' => $this->title,
            'description' => $this->description,
            'item_date' => $this->itemDate,
            'tags' => $this->tags,
            'links' => $this->links,
            'is_visible' => $this->isVisible,
            'display_order' => $this->displayOrder,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    public static function fromArray(array $data): self {
        return new self(
            portfolioId: $data['portfolio_id'],
            itemType: $data['item_type'],
            title: $data['title'],
            description: $data['description'],
            itemDate: $data['item_date'] ?? null,
            tags: is_string($data['tags'] ?? null) ? json_decode($data['tags'], true) ?? [] : ($data['tags'] ?? []),
            links: is_string($data['links'] ?? null) ? json_decode($data['links'], true) ?? [] : ($data['links'] ?? []),
            isVisible: (bool)($data['is_visible'] ?? true),
            displayOrder: $data['display_order'] ?? 0,
            id: $data['id'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }
}
