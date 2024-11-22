<?php
class BruteForceProtection {
    private $conn;
    private $table = 'login_attempts';
    private $blockDuration = 900; // 15 minutos em segundos
    private $maxAttempts = 5;
    
    // Definir constante no nível da classe
    private const MIN_RESPONSE_TIME = 500; // em milissegundos
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->createAttemptsTable();
    }
    
    private function createAttemptsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            email VARCHAR(255) NOT NULL,
            attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_blocked TINYINT(1) DEFAULT 0,
            blocked_until TIMESTAMP NULL,
            INDEX (ip_address, email)
        )";
        
        try {
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela de tentativas: " . $e->getMessage());
        }
    }
    
    public function isBlocked($ip, $email) {
        $sql = "SELECT COUNT(*) as attempts, MAX(blocked_until) as blocked_until 
                FROM {$this->table} 
                WHERE (ip_address = ? OR email = ?) 
                AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)";
                
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$ip, $email, $this->blockDuration]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar se está bloqueado
            if ($result['blocked_until'] && strtotime($result['blocked_until']) > time()) {
                return true;
            }
            
            // Verificar número de tentativas
            if ($result['attempts'] >= $this->maxAttempts) {
                $this->blockAccount($ip, $email);
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Erro ao verificar bloqueio: " . $e->getMessage());
            return false;
        }
    }
    
    public function recordAttempt($ip, $email, $success = false) {
        if ($success) {
            // Limpar tentativas anteriores em caso de sucesso
            $this->clearAttempts($ip, $email);
            return;
        }
        
        $sql = "INSERT INTO {$this->table} (ip_address, email) VALUES (?, ?)";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$ip, $email]);
        } catch (PDOException $e) {
            error_log("Erro ao registrar tentativa: " . $e->getMessage());
        }
    }
    
    private function blockAccount($ip, $email) {
        $blockedUntil = date('Y-m-d H:i:s', time() + $this->blockDuration);
        $sql = "UPDATE {$this->table} 
                SET is_blocked = 1, blocked_until = ? 
                WHERE ip_address = ? OR email = ?";
                
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$blockedUntil, $ip, $email]);
        } catch (PDOException $e) {
            error_log("Erro ao bloquear conta: " . $e->getMessage());
        }
    }
    
    private function clearAttempts($ip, $email) {
        $sql = "DELETE FROM {$this->table} WHERE ip_address = ? OR email = ?";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$ip, $email]);
        } catch (PDOException $e) {
            error_log("Erro ao limpar tentativas: " . $e->getMessage());
        }
    }
    
    // Proteção contra timing attacks
    public function consistentTimeResponse($callback) {
        $startTime = microtime(true);
        $result = $callback();
        $endTime = microtime(true);
        
        // Garantir tempo mínimo de resposta
        $executionTime = ($endTime - $startTime) * 1000;
        if ($executionTime < self::MIN_RESPONSE_TIME) {
            usleep((self::MIN_RESPONSE_TIME - $executionTime) * 1000);
        }
        
        return $result;
    }
} 