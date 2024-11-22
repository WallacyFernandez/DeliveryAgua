<?php
// Configurações de Segurança
define('PASSWORD_PEPPER', 'sua_chave_secreta_muito_longa_aqui_123!@#');  // Altere para uma string aleatória forte
define('PASSWORD_ALGO', PASSWORD_ARGON2ID); // Algoritmo de hash
define('PASSWORD_OPTIONS', [
    'memory_cost' => 65536,    // 64MB
    'time_cost'   => 4,        // 4 iterações
    'threads'     => 3         // 3 threads paralelas
]);

// Outras configurações de segurança
define('SESSION_TIMEOUT', 1800);  // 30 minutos em segundos
define('MAX_LOGIN_ATTEMPTS', 5);  // Máximo de tentativas de login
define('BLOCK_TIME', 900);        // 15 minutos de bloqueio em segundos 