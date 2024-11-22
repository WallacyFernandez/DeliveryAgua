<?php
class PasswordManager {
    // Configurações do Argon2id
    private $options = [
        'memory_cost' => 65536,  // 64MB
        'time_cost' => 4,        // 4 iterações
        'threads' => 2           // 2 threads
    ];
    
    // Criar hash da senha
    public function hashPassword($password) {
        if (defined('PASSWORD_ARGON2ID')) {
            // Usar Argon2id se disponível (PHP 7.3+)
            return password_hash($password, PASSWORD_ARGON2ID, $this->options);
        } elseif (defined('PASSWORD_ARGON2I')) {
            // Fallback para Argon2i (PHP 7.2+)
            return password_hash($password, PASSWORD_ARGON2I, $this->options);
        } else {
            // Fallback para Bcrypt
            return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
        }
    }
    
    // Verificar senha
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    // Verificar se o hash precisa ser atualizado
    public function needsRehash($hash) {
        if (defined('PASSWORD_ARGON2ID')) {
            return password_needs_rehash($hash, PASSWORD_ARGON2ID, $this->options);
        } elseif (defined('PASSWORD_ARGON2I')) {
            return password_needs_rehash($hash, PASSWORD_ARGON2I, $this->options);
        }
        return false;
    }
    
    // Adicionar pepper à senha (camada extra de segurança)
    private function pepperPassword($password) {
        if (!defined('PASSWORD_PEPPER')) {
            throw new Exception('PASSWORD_PEPPER não está definido no ambiente');
        }
        return hash_hmac('sha256', $password, PASSWORD_PEPPER);
    }
    
    // Hash seguro com pepper
    public function secureHash($password) {
        $pepperedPassword = $this->pepperPassword($password);
        return password_hash($pepperedPassword, PASSWORD_ALGO, PASSWORD_OPTIONS);
    }
    
    // Verificação segura com pepper
    public function secureVerify($password, $hash) {
        $pepperedPassword = $this->pepperPassword($password);
        return password_verify($pepperedPassword, $hash);
    }
} 