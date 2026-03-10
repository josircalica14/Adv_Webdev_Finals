<?php
/**
 * User Repository
 * 
 * Handles database operations for User entities using prepared statements.
 */

require_once __DIR__ . '/User.php';
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../Logger.php';

class UserRepository {
    private PDO $db;
    private Logger $logger;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->logger = Logger::getInstance();
    }

    /**
     * Create a new user
     * 
     * @param string $email User email
     * @param string $passwordHash Hashed password
     * @param string $fullName User's full name
     * @param string $program User's program (BSIT or CSE)
     * @param string $username User's username
     * @return User|null Created user or null on failure
     */
    public function create(string $email, string $passwordHash, string $fullName, string $program, string $username): ?User {
        try {
            $sql = "INSERT INTO users (email, password_hash, full_name, program, username, is_verified, is_admin) 
                    VALUES (:email, :password_hash, :full_name, :program, :username, 0, 0)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':email' => $email,
                ':password_hash' => $passwordHash,
                ':full_name' => $fullName,
                ':program' => $program,
                ':username' => $username
            ]);
            
            $userId = (int)$this->db->lastInsertId();
            $this->logger->info("User created successfully", ['user_id' => $userId, 'email' => $email]);
            
            return $this->findById($userId);
        } catch (PDOException $e) {
            $this->logger->error("Failed to create user", [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Find user by ID
     * 
     * @param int $id User ID
     * @return User|null
     */
    public function findById(int $id): ?User {
        try {
            $sql = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data ? User::fromArray($data) : null;
        } catch (PDOException $e) {
            $this->logger->error("Failed to find user by ID", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Find user by email
     * 
     * @param string $email User email
     * @return User|null
     */
    public function findByEmail(string $email): ?User {
        try {
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data ? User::fromArray($data) : null;
        } catch (PDOException $e) {
            $this->logger->error("Failed to find user by email", [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Find user by username
     * 
     * @param string $username Username
     * @return User|null
     */
    public function findByUsername(string $username): ?User {
        try {
            $sql = "SELECT * FROM users WHERE username = :username";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':username' => $username]);
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $data ? User::fromArray($data) : null;
        } catch (PDOException $e) {
            $this->logger->error("Failed to find user by username", [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Update user
     * 
     * @param User $user User to update
     * @return bool Success status
     */
    public function update(User $user): bool {
        try {
            $sql = "UPDATE users SET 
                    email = :email,
                    password_hash = :password_hash,
                    full_name = :full_name,
                    program = :program,
                    username = :username,
                    bio = :bio,
                    contact_info = :contact_info,
                    profile_photo_path = :profile_photo_path,
                    is_verified = :is_verified,
                    is_admin = :is_admin,
                    last_username_change = :last_username_change
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':id' => $user->id,
                ':email' => $user->email,
                ':password_hash' => $user->passwordHash,
                ':full_name' => $user->fullName,
                ':program' => $user->program,
                ':username' => $user->username,
                ':bio' => $user->bio,
                ':contact_info' => $user->contactInfo ? json_encode($user->contactInfo) : null,
                ':profile_photo_path' => $user->profilePhotoPath,
                ':is_verified' => $user->isVerified ? 1 : 0,
                ':is_admin' => $user->isAdmin ? 1 : 0,
                ':last_username_change' => $user->lastUsernameChange
            ]);
            
            if ($result) {
                $this->logger->info("User updated successfully", ['user_id' => $user->id]);
            }
            
            return $result;
        } catch (PDOException $e) {
            $this->logger->error("Failed to update user", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete user
     * 
     * @param int $id User ID
     * @return bool Success status
     */
    public function delete(int $id): bool {
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':id' => $id]);
            
            if ($result) {
                $this->logger->info("User deleted successfully", ['user_id' => $id]);
            }
            
            return $result;
        } catch (PDOException $e) {
            $this->logger->error("Failed to delete user", [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if email exists
     * 
     * @param string $email Email to check
     * @return bool
     */
    public function emailExists(string $email): bool {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            $this->logger->error("Failed to check email existence", [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if username exists
     * 
     * @param string $username Username to check
     * @return bool
     */
    public function usernameExists(string $username): bool {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE username = :username";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':username' => $username]);
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            $this->logger->error("Failed to check username existence", [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get all users
     * 
     * @return array Array of User objects
     */
    public function findAll(): array {
        try {
            $sql = "SELECT * FROM users ORDER BY created_at DESC";
            $stmt = $this->db->query($sql);
            
            $users = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $users[] = User::fromArray($data);
            }
            
            return $users;
        } catch (PDOException $e) {
            $this->logger->error("Failed to fetch all users", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
