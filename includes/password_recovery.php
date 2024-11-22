<?php
class PasswordRecovery {
    private $conn;
    private $table = 'password_reset_tokens';
    private $tokenExpiry = 3600; // 1 hora em segundos
    private $logger;
    private $passwordManager;
    
    public function __construct($conn, $logger, $passwordManager) {
        $this->conn = $conn;
        $this->logger = $logger;
        $this->passwordManager = $passwordManager;
        $this->createTokenTable();
    }
    
    private function createTokenTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,
            used TINYINT(1) DEFAULT 0,
            INDEX (token),
            INDEX (user_id)
        )";
        
        try {
            $this->conn->exec($sql);
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela de tokens: " . $e->getMessage());
        }
    }
    
    public function generateResetToken($userId, $email) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + $this->tokenExpiry);
        
        try {
            $this->conn->beginTransaction();
            
            // Invalidar tokens anteriores
            $invalidate = $this->conn->prepare("UPDATE {$this->table} SET used = 1 WHERE user_id = ?");
            $invalidate->execute([$userId]);
            
            // Criar novo token
            $insert = $this->conn->prepare("INSERT INTO {$this->table} (user_id, token, expires_at) VALUES (?, ?, ?)");
            $insert->execute([$userId, $token, $expiresAt]);
            
            $this->conn->commit();
            $this->logger->logSecurityEvent('reset_token_generated', $userId);
            
            return $token;
        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->logger->logSecurityEvent('reset_token_error', $userId, $e->getMessage());
            throw $e;
        }
    }
    
    public function validateToken($token) {
        $sql = "SELECT t.*, e.email FROM {$this->table} t 
                JOIN empresas e ON e.id = t.user_id
                WHERE t.token = ? AND t.used = 0 AND t.expires_at > NOW()
                LIMIT 1";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$token]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function resetPassword($token, $newPassword) {
        $tokenData = $this->validateToken($token);
        
        if (!$tokenData) {
            throw new Exception('Token inválido ou expirado');
        }
        
        try {
            $this->conn->beginTransaction();
            
            // Hash da nova senha
            $hashedPassword = $this->passwordManager->secureHash($newPassword);
            
            // Atualizar senha
            $updatePass = $this->conn->prepare("UPDATE empresas SET password = ? WHERE id = ?");
            $updatePass->execute([$hashedPassword, $tokenData['user_id']]);
            
            // Marcar token como usado
            $updateToken = $this->conn->prepare("UPDATE {$this->table} SET used = 1 WHERE token = ?");
            $updateToken->execute([$token]);
            
            // Registrar no histórico de senhas
            $insertHistory = $this->conn->prepare("INSERT INTO password_history (user_id, password) VALUES (?, ?)");
            $insertHistory->execute([$tokenData['user_id'], $hashedPassword]);
            
            $this->conn->commit();
            $this->logger->logSecurityEvent('password_reset_success', $tokenData['user_id']);
            
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            $this->logger->logSecurityEvent('password_reset_error', $tokenData['user_id'], $e->getMessage());
            throw $e;
        }
    }
} 