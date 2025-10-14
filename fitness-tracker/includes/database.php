<?php
/**
 * Database Configuration and Connection
 * Secure database connection with PDO
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'fittrack');
define('DB_USER', 'root');
define('DB_PASS', 'jaykum1210');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {}
}

/**
 * Get database connection
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * Execute a prepared statement with parameters
 */
function executeQuery($sql, $params = []) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query execution failed: " . $e->getMessage());
        throw new Exception("Database query failed");
    }
}

/**
 * Fetch a single row
 */
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

/**
 * Fetch all rows
 */
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Get the last inserted ID
 */
function getLastInsertId() {
    return getDB()->lastInsertId();
}

/**
 * Begin a transaction
 */
function beginTransaction() {
    return getDB()->beginTransaction();
}

/**
 * Commit a transaction
 */
function commit() {
    return getDB()->commit();
}

/**
 * Rollback a transaction
 */
function rollback() {
    return getDB()->rollback();
}

/**
 * Check if database connection is working
 */
function testConnection() {
    try {
        $pdo = getDB();
        $stmt = $pdo->query("SELECT 1");
        return $stmt !== false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Initialize database tables
 */
function initializeDatabase() {
    try {
        $sql = file_get_contents(__DIR__ . '/../database/init.sql');
        $pdo = getDB();
        $pdo->exec($sql);
        return true;
    } catch (Exception $e) {
        error_log("Database initialization failed: " . $e->getMessage());
        return false;
    }
}
?>