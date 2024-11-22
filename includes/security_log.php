<?php
class SecurityLogger {
    private $conn;
    private $table = 'security_logs';
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->createLogTable();
    }
    
    // Criar tabela de logs se não existir
    private function createLogTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(50) NOT NULL,
            user_id INT,
            ip_address VARCHAR(45),
            user_agent VARCHAR(255),
            details TEXT,
            status VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        try {
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela de logs: " . $e->getMessage());
        }
    }
    
    // Registrar evento de segurança
    public function logSecurityEvent($eventType, $userId = null, $details = '', $status = 'success') {
        $ip = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $sql = "INSERT INTO {$this->table} (event_type, user_id, ip_address, user_agent, details, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
                
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$eventType, $userId, $ip, $userAgent, $details, $status]);
        } catch (PDOException $e) {
            error_log("Erro ao registrar log de segurança: " . $e->getMessage());
        }
    }
    
    // Obter IP real do cliente
    private function getClientIP() {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return 'Unknown';
    }
    
    // Verificar tentativas de login
    public function checkLoginAttempts($ip, $email, $maxAttempts = 5, $timeWindow = 900) {
        $sql = "SELECT COUNT(*) FROM {$this->table} 
                WHERE ip_address = ? 
                AND event_type = 'login_failed'
                AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)";
                
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$ip, $timeWindow]);
            $attempts = $stmt->fetchColumn();
            
            if ($attempts >= $maxAttempts) {
                $this->logSecurityEvent(
                    'login_blocked',
                    null,
                    "Muitas tentativas para o email: $email",
                    'blocked'
                );
                return false;
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao verificar tentativas de login: " . $e->getMessage());
            return false;
        }
    }
} 