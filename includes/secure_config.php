<?php
class SecureConfig {
    private $encryptionKey;
    private $configPath;
    private static $instance = null;
    private $cachedConfig = null;
    
    private function __construct() {
        $this->encryptionKey = $this->getEncryptionKeyFromEnv();
        $this->configPath = $this->getConfigPath();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function getEncryptionKeyFromEnv() {
        $key = getenv('APP_ENCRYPTION_KEY');
        if (!$key) {
            throw new Exception('Chave de criptografia não encontrada nas variáveis de ambiente');
        }
        return base64_decode($key);
    }
    
    private function getConfigPath() {
        // Armazenar arquivo de configuração fora do diretório web
        return dirname(__DIR__, 2) . '/config/secure/config.enc';
    }
    
    public function encrypt($data) {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $encrypted = sodium_crypto_secretbox(
            json_encode($data),
            $nonce,
            $this->encryptionKey
        );
        return base64_encode($nonce . $encrypted);
    }
    
    public function decrypt($encrypted) {
        $decoded = base64_decode($encrypted);
        $nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
        
        $decrypted = sodium_crypto_secretbox_open(
            $ciphertext,
            $nonce,
            $this->encryptionKey
        );
        
        if ($decrypted === false) {
            throw new Exception('Falha ao descriptografar configuração');
        }
        
        return json_decode($decrypted, true);
    }
    
    public function saveConfig($config) {
        $encrypted = $this->encrypt($config);
        if (file_put_contents($this->configPath, $encrypted) === false) {
            throw new Exception('Falha ao salvar configuração');
        }
        $this->cachedConfig = $config;
    }
    
    public function getConfig() {
        if ($this->cachedConfig !== null) {
            return $this->cachedConfig;
        }
        
        if (!file_exists($this->configPath)) {
            throw new Exception('Arquivo de configuração não encontrado');
        }
        
        $encrypted = file_get_contents($this->configPath);
        $this->cachedConfig = $this->decrypt($encrypted);
        return $this->cachedConfig;
    }
    
    public function getDatabaseConfig() {
        $config = $this->getConfig();
        return [
            'host' => $config['db']['host'],
            'name' => $config['db']['name'],
            'user' => $config['db']['user'],
            'pass' => $config['db']['pass']
        ];
    }
} 