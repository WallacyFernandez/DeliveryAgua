<?php
class InputValidator {
    private $errors = [];
    
    // Validar email
    public function validateEmail($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Email inválido!';
            return false;
        }
        return $email;
    }
    
    // Validar senha
    public function validatePassword($password, $min_length = 8) {
        if (strlen($password) < $min_length) {
            $this->errors['password'] = "A senha deve ter no mínimo {$min_length} caracteres!";
            return false;
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $this->errors['password'] = 'A senha deve conter pelo menos uma letra maiúscula!';
            return false;
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $this->errors['password'] = 'A senha deve conter pelo menos um número!';
            return false;
        }
        
        return $password;
    }
    
    // Validar nome
    public function validateName($name, $min_length = 2) {
        $name = trim(filter_var($name, FILTER_SANITIZE_STRING));
        if (strlen($name) < $min_length) {
            $this->errors['name'] = "O nome deve ter no mínimo {$min_length} caracteres!";
            return false;
        }
        return $name;
    }
    
    // Validar número de telefone
    public function validatePhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) < 10 || strlen($phone) > 11) {
            $this->errors['phone'] = 'Número de telefone inválido!';
            return false;
        }
        return $phone;
    }
    
    // Validar CNPJ
    public function validateCNPJ($cnpj) {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) != 14) {
            $this->errors['cnpj'] = 'CNPJ inválido!';
            return false;
        }
        
        // Validação mais complexa do CNPJ pode ser adicionada aqui
        return $cnpj;
    }
    
    // Validar endereço
    public function validateAddress($address) {
        if (empty($address)) {
            $this->errors[] = "O endereço não pode estar vazio";
            return null;
        }
        
        // Remove caracteres especiais exceto vírgula, ponto e hífen
        $address = preg_replace('/[^a-zA-Z0-9\s,.-]/', '', $address);
        
        // Limita o tamanho do endereço
        if (strlen($address) > 100) {
            $this->errors[] = "O endereço não pode ter mais de 100 caracteres";
            return null;
        }
        
        return $address;
    }
    
    // Retornar erros
    public function getErrors() {
        return $this->errors;
    }
    
    // Verificar se há erros
    public function hasErrors() {
        return !empty($this->errors);
    }
} 