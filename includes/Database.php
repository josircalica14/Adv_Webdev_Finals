<?php
/**
 * Database Connection Class
 * 
 * Provides PDO database connection with prepared statement support
 * for secure database operations.
 */

class Database {
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private Config $config;

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct() {
        $this->config = Config::getInstance();
        $this->connect();
    }

    /**
     * Get singleton instance of Database
     * 
     * @return Database
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establish database connection
     * 
     * @throws PDOException if connection fails
     */
    private function connect(): void {
        try {
            $host = $this->config->get('db.host');
            $port = $this->config->get('db.port', 3306);
            $dbname = $this->config->get('db.name');
            $charset = $this->config->get('db.charset', 'utf8mb4');
            
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false
            ];
            
            $this->connection = new PDO(
                $dsn,
                $this->config->get('db.username'),
                $this->config->get('db.password'),
                $options
            );
            
            Logger::getInstance()->info('Database connection established');
        } catch (PDOException $e) {
            Logger::getInstance()->error('Database connection failed: ' . $e->getMessage());
            throw new PDOException('Database connection failed');
        }
    }

    /**
     * Get PDO connection instance
     * 
     * @return PDO
     */
    public function getConnection(): PDO {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Execute a prepared statement with parameters
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return PDOStatement
     * @throws PDOException
     */
    public function query(string $sql, array $params = []): PDOStatement {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            Logger::getInstance()->error('Query execution failed: ' . $e->getMessage(), [
                'sql' => $sql,
                'params' => $params
            ]);
            throw $e;
        }
    }

    /**
     * Begin a database transaction
     * 
     * @return bool
     */
    public function beginTransaction(): bool {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit a database transaction
     * 
     * @return bool
     */
    public function commit(): bool {
        return $this->connection->commit();
    }

    /**
     * Rollback a database transaction
     * 
     * @return bool
     */
    public function rollback(): bool {
        return $this->connection->rollBack();
    }

    /**
     * Get the last inserted ID
     * 
     * @return string
     */
    public function lastInsertId(): string {
        return $this->connection->lastInsertId();
    }

    /**
     * Run database migrations
     * 
     * @param string $migrationsPath Path to migration files
     * @return array Results of migration execution
     */
    public function runMigrations(string $migrationsPath): array {
        $results = [];
        $files = glob($migrationsPath . '/*.sql');
        sort($files);
        
        foreach ($files as $file) {
            try {
                $sql = file_get_contents($file);
                $this->connection->exec($sql);
                $results[] = [
                    'file' => basename($file),
                    'status' => 'success'
                ];
                Logger::getInstance()->info('Migration executed: ' . basename($file));
            } catch (PDOException $e) {
                $results[] = [
                    'file' => basename($file),
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
                Logger::getInstance()->error('Migration failed: ' . basename($file), [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $results;
    }

    /**
     * Prevent cloning of singleton
     */
    private function __clone() {}

    /**
     * Prevent unserialization of singleton
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
