<?php
/**
 * TechText - Database Layer
 * SQLite Database Operations with PDO
 */

require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $pdo;
    private $error;

    private function __construct() {
        try {
            // Ensure data directory exists
            $dbDir = dirname(DB_PATH);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }

            $this->pdo = new PDO('sqlite:' . DB_PATH);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->exec('PRAGMA foreign_keys = ON');
            $this->pdo->exec('PRAGMA journal_mode = WAL');
            
            $this->initializeTables();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeTables() {
        // Conversions table
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS conversions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            input_content TEXT NOT NULL,
            markup_type VARCHAR(50) NOT NULL,
            output_format VARCHAR(50) NOT NULL,
            output_content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45),
            user_agent TEXT,
            session_id VARCHAR(255)
        )");

        // Create indexes
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_created_at ON conversions(created_at)");
        $this->pdo->exec("CREATE INDEX IF NOT EXISTS idx_session ON conversions(session_id)");
    }

    public function saveConversion($input, $markupType, $outputFormat, $output, $sessionId) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO conversions 
                (input_content, markup_type, output_format, output_content, ip_address, user_agent, session_id) 
                VALUES (:input, :markup, :format, :output, :ip, :agent, :session)");
            
            $stmt->execute([
                ':input' => $input,
                ':markup' => $markupType,
                ':format' => $outputFormat,
                ':output' => $output,
                ':ip' => $this->getClientIP(),
                ':agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                ':session' => $sessionId
            ]);
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Save conversion failed: " . $e->getMessage());
            return false;
        }
    }

    public function getConversionHistory($sessionId, $limit = 20) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, markup_type, output_format, 
                SUBSTR(input_content, 1, 100) as preview, created_at 
                FROM conversions 
                WHERE session_id = :session 
                ORDER BY created_at DESC 
                LIMIT :limit");
            
            $stmt->bindValue(':session', $sessionId);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get history failed: " . $e->getMessage());
            return [];
        }
    }

    public function getConversion($id, $sessionId) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM conversions 
                WHERE id = :id AND session_id = :session");
            $stmt->execute([':id' => $id, ':session' => $sessionId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get conversion failed: " . $e->getMessage());
            return false;
        }
    }

    public function deleteConversion($id, $sessionId) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM conversions 
                WHERE id = :id AND session_id = :session");
            $stmt->execute([':id' => $id, ':session' => $sessionId]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Delete conversion failed: " . $e->getMessage());
            return false;
        }
    }

    public function clearHistory($sessionId) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM conversions WHERE session_id = :session");
            $stmt->execute([':session' => $sessionId]);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Clear history failed: " . $e->getMessage());
            return false;
        }
    }

    private function getClientIP() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                return filter_var($_SERVER[$key], FILTER_VALIDATE_IP) ?: '0.0.0.0';
            }
        }
        return '0.0.0.0';
    }

    public function getError() {
        return $this->error;
    }
}
?>