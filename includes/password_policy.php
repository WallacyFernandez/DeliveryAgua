<?php
class PasswordPolicy {
    private $minLength = 8;
    private $requireUppercase = true;
    private $requireLowercase = true;
    private $requireNumbers = true;
    private $requireSpecialChars = true;
    private $maxLength = 72; // Limite máximo recomendado para bcrypt
    private $passwordHistory = 5; // Número de senhas antigas para verificar
    
    public function validate($password, $userId = null) {
        $errors = [];
        
        if (strlen($password) < $this->minLength) {
            $errors[] = "A senha deve ter no mínimo {$this->minLength} caracteres";
        }
        
        if (strlen($password) > $this->maxLength) {
            $errors[] = "A senha deve ter no máximo {$this->maxLength} caracteres";
        }
        
        if ($this->requireUppercase && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "A senha deve conter pelo menos uma letra maiúscula";
        }
        
        if ($this->requireLowercase && !preg_match('/[a-z]/', $password)) {
            $errors[] = "A senha deve conter pelo menos uma letra minúscula";
        }
        
        if ($this->requireNumbers && !preg_match('/[0-9]/', $password)) {
            $errors[] = "A senha deve conter pelo menos um número";
        }
        
        if ($this->requireSpecialChars && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "A senha deve conter pelo menos um caractere especial";
        }
        
        // Verificar se a senha é comum
        if ($this->isCommonPassword($password)) {
            $errors[] = "Esta senha é muito comum. Por favor, escolha uma senha mais segura";
        }
        
        return $errors;
    }
    
    private function isCommonPassword($password) {
        $commonPasswords = [
            '123456', 'password', '12345678', 'qwerty', '123456789',
            'abc123', '111111', '123123', 'admin', 'letmein'
        ];
        return in_array(strtolower($password), $commonPasswords);
    }
    
    public function checkPasswordHistory($password, $userId, $conn) {
        $sql = "SELECT password FROM password_history 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId, $this->passwordHistory]);
        
        $passwordManager = new PasswordManager();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($passwordManager->verifyPassword($password, $row['password'])) {
                return false;
            }
        }
        return true;
    }
} 