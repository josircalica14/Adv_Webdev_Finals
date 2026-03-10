<?php
/**
 * User Entity
 * 
 * Represents a user account in the portfolio platform.
 */

class User {
    public ?int $id;
    public string $email;
    public string $passwordHash;
    public string $fullName;
    public string $program; // 'BSIT' or 'CSE'
    public string $username;
    public ?string $bio;
    public ?array $contactInfo;
    public ?string $profilePhotoPath;
    public bool $isVerified;
    public bool $isAdmin;
    public string $createdAt;
    public string $updatedAt;
    public ?string $lastUsernameChange;

    /**
     * Create User from database row
     * 
     * @param array $data Database row data
     * @return User
     */
    public static function fromArray(array $data): User {
        $user = new self();
        $user->id = isset($data['id']) ? (int)$data['id'] : null;
        $user->email = $data['email'];
        $user->passwordHash = $data['password_hash'];
        $user->fullName = $data['full_name'];
        $user->program = $data['program'];
        $user->username = $data['username'];
        $user->bio = $data['bio'] ?? null;
        $user->contactInfo = isset($data['contact_info']) ? json_decode($data['contact_info'], true) : null;
        $user->profilePhotoPath = $data['profile_photo_path'] ?? null;
        $user->isVerified = (bool)($data['is_verified'] ?? false);
        $user->isAdmin = (bool)($data['is_admin'] ?? false);
        $user->createdAt = $data['created_at'];
        $user->updatedAt = $data['updated_at'];
        $user->lastUsernameChange = $data['last_username_change'] ?? null;
        
        return $user;
    }

    /**
     * Convert User to array for database storage
     * 
     * @return array
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password_hash' => $this->passwordHash,
            'full_name' => $this->fullName,
            'program' => $this->program,
            'username' => $this->username,
            'bio' => $this->bio,
            'contact_info' => $this->contactInfo ? json_encode($this->contactInfo) : null,
            'profile_photo_path' => $this->profilePhotoPath,
            'is_verified' => $this->isVerified,
            'is_admin' => $this->isAdmin,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'last_username_change' => $this->lastUsernameChange
        ];
    }
}
