<?php
/**
 * Portfolio Entity
 * 
 * Represents a student's portfolio with visibility and metadata
 */

namespace Portfolio;

class Portfolio {
    private ?int $id;
    private int $userId;
    private bool $isPublic;
    private int $viewCount;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(
        int $userId,
        bool $isPublic = false,
        int $viewCount = 0,
        ?int $id = null,
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->isPublic = $isPublic;
        $this->viewCount = $viewCount;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->updatedAt = $updatedAt ?? date('Y-m-d H:i:s');
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getUserId(): int {
        return $this->userId;
    }

    public function isPublic(): bool {
        return $this->isPublic;
    }

    public function setPublic(bool $isPublic): void {
        $this->isPublic = $isPublic;
    }

    public function getViewCount(): int {
        return $this->viewCount;
    }

    public function incrementViewCount(): void {
        $this->viewCount++;
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
            'user_id' => $this->userId,
            'is_public' => $this->isPublic,
            'view_count' => $this->viewCount,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    public static function fromArray(array $data): self {
        return new self(
            userId: $data['user_id'],
            isPublic: (bool)$data['is_public'],
            viewCount: $data['view_count'] ?? 0,
            id: $data['id'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null
        );
    }
}
